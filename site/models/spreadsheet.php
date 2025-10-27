<?php

use Kirby\Cms\Pages;
use Kirby\Http\Remote;
use Kirby\Toolkit\Str;

class SpreadsheetPage extends DefaultPage
{
    /** per-request caches */
    protected static ?string $csvBodyCache = null;
    protected static ?int    $rowCountCache = null;      // conteggio risultati (post-filtri) per la richiesta corrente
    protected static ?array  $rowMapCache = null;        // slug => content (per item O(1))
    protected static ?array  $filtersIndexCache = null;  // alias => [values] tokenizzati per la richiesta corrente
    protected static $searchPoolCache = null;            // Pages di TUTTE le righe (senza limiti/filtri) per la search
    protected array          $filters = [];              // alias marcati come filtrabili nel Panel

    /* ===== Utils di percorso ===== */
    protected function requestPath(): string
    {
        return trim($this->kirby()->request()->url()->path()->toString(), '/');
    }
    protected function parentPath(): string
    {
        return trim(parse_url($this->url(), PHP_URL_PATH), '/');
    }
    /** Se l'URL è /parent/child ritorna "child", altrimenti null */
    protected function requestedChildSlug(): ?string
    {
        $req = $this->requestPath();
        $parent = $this->parentPath();
        if ($req === $parent || $req === '') return null;
        if (Str::startsWith($req, $parent . '/')) {
            $rest = substr($req, strlen($parent) + 1);
            $child = strtok($rest, '/');
            return $child ?: null;
        }
        return null;
    }

    /** Colori associati agli alias filtrabili */
public function filterColors(): array
{
    $colors = [];
    $struct = $this->alias_map()->isNotEmpty() ? $this->alias_map()->toStructure() : [];
    foreach ($struct as $row) {
        $alias = Str::slug(trim($row->alias()->value() ?? ''));
        if ($row->filter()->toBool() && $alias !== '') {
            $colore = trim($row->colore()->value() ?? '');
            if ($colore !== '') {
                $colors[$alias] = $colore;
            }
        }
    }
    return $colors;
}
    /* ===== Tokenizer per campi con liste "A, B, C" ===== */
    protected function tokenize(?string $raw): array
    {
        if ($raw === null) return [];
        $parts = array_map('trim', explode(',', $raw));
        $parts = array_filter($parts, fn($v) => $v !== '');
        // dedup case-insensitive mantenendo il testo “bello”
        $seen = [];
        $out  = [];
        foreach ($parts as $p) {
            $k = Str::slug($p);
            if (!isset($seen[$k])) {
                $seen[$k] = true;
                $out[] = $p;
            }
        }
        return array_values($out);
    }

    /* ===== Default dal Panel ===== */
    protected function sheetDefaults(): array
    {
        $ttlField   = (int)($this->cache_ttl()->toInt() ?? 0);
        $ttl        = $ttlField > 0 ? $ttlField : (int) option('sheet.cacheTtl', 300);

        $psField    = (int)($this->page_size()->toInt() ?? 0);
        $pageSize   = $psField > 0 ? $psField : (int) option('sheet.pageSize', 20);

        // fallback generico per TUTTI i campi vuoti
        $fallback   = trim($this->fallback()->or('—')->value());
        $fallback   = $fallback !== '' ? $fallback : '—';

        $includeOnly = $this->include_only()->toBool();

        return [
            'ttl'         => $ttl,
            'pageSize'    => $pageSize,
            'fallback'    => $fallback,
            'includeOnly' => $includeOnly,
        ];
    }

    /* ===== Fetch CSV con Conditional GET + Anti-stampede Lock ===== */
    protected function fetchCsvBody(): ?string
    {
        // blocca SOLO richieste del Panel
        $path = $this->requestPath();
        if ($path === 'panel' || Str::startsWith($path, 'panel') || Str::contains($path, '/panel')) {
            return null;
        }

        if (self::$csvBodyCache !== null) {
            return self::$csvBodyCache;
        }

        $csvUrl = $this->content()->get('csv_url')->value();
        if (!$csvUrl) return null;

        $defaults    = $this->sheetDefaults();
        $kirbyCache  = $this->kirby()->cache('sheet');
        $ttl         = (int) $defaults['ttl'];
        $keyBody     = 'csv:' . md5($csvUrl);
        $keyMeta     = 'csvmeta:' . md5($csvUrl);

        // Cache calda
        if (get('refresh') !== '1') {
            if ($csv = $kirbyCache->get($keyBody)) {
                self::$csvBodyCache = $csv;
                return $csv;
            }
        }

        // Anti-stampede lock
        $lockPath = kirby()->roots()->cache() . '/sheet.lock';
        @is_dir(dirname($lockPath)) || @mkdir(dirname($lockPath), 0775, true);
        $lock = @fopen($lockPath, 'c');
        if ($lock) @flock($lock, LOCK_EX);

        try {
            $meta    = $kirbyCache->get($keyMeta) ?: [];
            $headers = ['Accept-Encoding' => 'gzip'];
            if (!empty($meta['etag']))          $headers['If-None-Match']     = $meta['etag'];
            if (!empty($meta['last_modified'])) $headers['If-Modified-Since'] = $meta['last_modified'];

            $res = Remote::get($csvUrl, ['timeout' => 3, 'headers' => $headers]);

            if ($res->code() === 304 && ($stale = $kirbyCache->get($keyBody))) {
                $csvBody = $stale;
            } elseif ($res->code() === 200) {
                $csvBody = $res->content();
                if ($ttl > 0) $kirbyCache->set($keyBody, $csvBody, $ttl);
                $h = $res->headers();
                $kirbyCache->set($keyMeta, [
                    'etag'          => $h['etag'][0]          ?? null,
                    'last_modified' => $h['last-modified'][0] ?? null,
                ], $ttl);
            } else {
                if ($stale = $kirbyCache->get($keyBody)) {
                    $csvBody = $stale;
                } else {
                    return null;
                }
            }
        } catch (\Throwable $e) {
            if ($stale = $kirbyCache->get($keyBody)) {
                $csvBody = $stale;
            } else {
                return null;
            }
        } finally {
            if ($lock) { @flock($lock, LOCK_UN); @fclose($lock); }
        }

        self::$csvBodyCache = $csvBody;
        return $csvBody;
    }

    /* ===== Alias (Panel override > base) + elenco campi filtrabili ===== */
    protected function buildAliases(): array
    {
        $baseAliases = [
            'slug'     => 'slug',
            'template' => 'template',
            'title'    => 'title',
            'name'     => 'name',
        ];

        $aliasMap = [];
        $filters  = [];
        $struct = $this->alias_map()->isNotEmpty() ? $this->alias_map()->toStructure() : [];
        foreach ($struct as $row) {
            $h = Str::slug(trim($row->header()->value() ?? ''));
            $a = Str::slug(trim($row->alias()->value() ?? ''));
            if ($h !== '' && $a !== '') {
                $aliasMap[$h] = $a;
                if ($row->filter()->toBool()) {
                    $filters[] = $a;
                }
            }
        }
        $this->filters = $filters;

        return array_merge($baseAliases, $aliasMap);
    }

    /** Campi filtrabili (alias) da usare nel template */
    public function filterableFields(): array
    {
        if (empty($this->filters)) {
            $this->buildAliases(); // assicura popolazione
        }
        return $this->filters;
    }

    /** Valori UNICI tokenizzati per un alias filtrabile (ordinati) */
    public function filterValues(string $field): array
    {
        if (self::$filtersIndexCache !== null && isset(self::$filtersIndexCache[$field])) {
            return self::$filtersIndexCache[$field];
        }

        $values = [];
        foreach ($this->parseCsvRows() as $row) {
            foreach ($this->tokenize($row[$field] ?? '') as $token) {
                $values[] = $token;
            }
        }
        // dedup + sort naturale case-insensitive
        $values = array_values(array_unique($values));
        sort($values, SORT_NATURAL | SORT_FLAG_CASE);

        self::$filtersIndexCache[$field] = $values;
        return $values;
    }

    /* ===== Parser generator (senza fallback; whitelist opzionale) ===== */
    protected function parseCsvRows(callable $onHeaders = null): \Generator
    {
        $csvBody = $this->fetchCsvBody();
        if (!$csvBody) {
            if ($onHeaders) $onHeaders([]);
            if (false) yield [];
            return;
        }

        $defaults       = $this->sheetDefaults();
        $aliases        = $this->buildAliases();
        $includeOnly    = (bool) $defaults['includeOnly'];
        $allowedAliases = $includeOnly ? array_values($aliases) : [];
        $mustKeep       = ['slug','template','titolo','nodo','title','name'];

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $csvBody);
        rewind($stream);

        $headersRaw = fgetcsv($stream, 0, ',');
        if (!$headersRaw || count($headersRaw) === 0) {
            fclose($stream);
            if ($onHeaders) $onHeaders([]);
            if (false) yield [];
            return;
        }

        $headersSlug = array_map(fn($h) => Str::slug(trim((string)$h)), $headersRaw);
        $keys = array_map(fn($k) => $aliases[$k] ?? $k, $headersSlug);

        if ($onHeaders) $onHeaders($keys);

        while (($row = fgetcsv($stream, 0, ',')) !== false) {
            if ($row === [null] || $row === [] || (count($row) === 1 && trim((string)$row[0]) === '')) continue;

            if (count($row) < count($keys))      $row = array_pad($row, count($keys), '');
            elseif (count($row) > count($keys))  $row = array_slice($row, 0, count($keys));

            // mappa chiave => valore (post-alias), nessun fallback qui
            $assoc = [];
            foreach ($keys as $i => $key) {
                $assoc[$key] = trim((string)($row[$i] ?? ''));
            }

            // whitelist per ridurre payload
            if ($includeOnly && !empty($allowedAliases)) {
                $filtered = array_intersect_key($assoc, array_flip($allowedAliases));
                foreach ($mustKeep as $mk) {
                    if (array_key_exists($mk, $assoc)) $filtered[$mk] = $assoc[$mk];
                }
                $assoc = $filtered;
            }

            yield $assoc;
        }

        fclose($stream);
    }

    /* ===== Conteggio totale righe (post-filtri se già calcolato) ===== */
public function totalRows(): int
{
    if (self::$rowCountCache !== null) {
        return self::$rowCountCache;
    }

    // Leggi i filtri attivi dal querystring, limitandoti agli alias marcati filtrabili
    $filterParam = $_GET['filter'] ?? [];
    $activeFilters = [];
    if (is_array($filterParam)) {
        foreach ($filterParam as $alias => $valueCsv) {
            $alias = \Kirby\Toolkit\Str::slug((string)$alias);
            if (!in_array($alias, $this->filterableFields(), true)) {
                continue;
            }
            $vals = array_values(array_unique(array_filter(array_map('trim', explode(',', (string)$valueCsv)))));
            $vals = array_map(fn($v) => \Kirby\Toolkit\Str::slug($v), $vals);
            if (!empty($vals)) {
                $activeFilters[$alias] = $vals;
            }
        }
    }
    $hasActiveFilters = !empty($activeFilters);

    // Conta le righe del CSV applicando gli stessi criteri di filtro di children()
    $count = 0;
    foreach ($this->parseCsvRows() as $assoc) {
        if ($hasActiveFilters) {
            $ok = true;
            foreach ($activeFilters as $alias => $requiredSlugs) {
                $rowTokensSlugs = array_map(
                    fn($t) => \Kirby\Toolkit\Str::slug($t),
                    $this->tokenize($assoc[$alias] ?? '')
                );
                if (!empty(array_diff($requiredSlugs, $rowTokensSlugs))) {
                    $ok = false;
                    break;
                }
            }
            if (!$ok) continue;
        }
        $count++;
    }

    self::$rowCountCache = $count;
    return $count;
}

    /* ===== Children: item O(1) + listing single-scan con multi-filtri (AND) ===== */
    public function children(): Pages
    {
        // helper: primo non vuoto (NON usa fallback)
        $firstNonEmpty = function (...$vals) {
            foreach ($vals as $v) {
                if (isset($v) && trim((string)$v) !== '') return $v;
            }
            return null;
        };

        // helper: crea child applicando fallback ai campi DOPO lo slug
        $makeChild = function(array $assoc, string $fallback) use ($firstNonEmpty) {
            // slug SOLO da valori reali
            $base = $firstNonEmpty(
                $assoc['slug']  ?? null,
                $assoc['titolo']?? $assoc['title'] ?? null,
                $assoc['nodo']  ?? null,
                $assoc['name']  ?? null
            );
            if ($base === null) $base = substr(md5(json_encode($assoc)), 0, 10);
            $slug = Str::slug((string)$base);

            // fallback generico sui campi
            $content = $assoc;
            foreach ($content as $k => $v) {
                if (trim((string)$v) === '') $content[$k] = $fallback;
            }
            if (!isset($content['titolo']) || trim((string)$content['titolo']) === '') {
                $content['titolo'] = $fallback;
            }

            $tpl = $content['template'] ?? 'spreadsheet-item';
            return [
                'slug'     => $slug,
                'num'      => 0,
                'template' => $tpl,
                'model'    => $tpl,
                'content'  => $content,
            ];
        };

        $defaults = $this->sheetDefaults();
        $fallback = $defaults['fallback'];

        /* --- Lettura filtri multipli dal querystring ---
           Schema: ?filter[alias1]=v1,v2&filter[alias2]=w1
           - AND tra alias
           - AND dentro ogni alias (tutti i valori richiesti devono essere presenti nella riga)
        */
        $filterParam = $_GET['filter'] ?? [];
        $activeFilters = []; // alias => [slugged values]
        if (is_array($filterParam)) {
            foreach ($filterParam as $alias => $valueCsv) {
                $alias = Str::slug((string)$alias);
                // considera solo alias marcati filtrabili
                if (!in_array($alias, $this->filterableFields(), true)) {
                    continue;
                }
                $vals = array_values(array_unique(array_filter(array_map('trim', explode(',', (string)$valueCsv)))));
                $vals = array_map(fn($v) => Str::slug($v), $vals);
                if (!empty($vals)) {
                    $activeFilters[$alias] = $vals;
                }
            }
        }
        $hasActiveFilters = !empty($activeFilters);

        // === Modalità ITEM: usa row-map dalla cache, fallback a scan ===
        if ($childSlug = $this->requestedChildSlug()) {
            $csvBody = $this->fetchCsvBody();
            if (!$csvBody) return new Pages([]);
            $hash   = md5($csvBody);
            $mapKey = 'csvmap:' . $hash;
            $map    = self::$rowMapCache ?? $this->kirby()->cache('sheet')->get($mapKey) ?? [];

            if (isset($map[$childSlug])) {
                $content = $map[$childSlug];
                foreach ($content as $k => $v) {
                    if (trim((string)$v) === '') $content[$k] = $fallback;
                }
                if (!isset($content['titolo']) || trim((string)$content['titolo']) === '') {
                    $content['titolo'] = $fallback;
                }
                $tpl = $content['template'] ?? 'spreadsheet-item';
                return Pages::factory([[
                    'slug'     => $childSlug,
                    'num'      => 0,
                    'template' => $tpl,
                    'model'    => $tpl,
                    'content'  => $content,
                ]], $this);
            }

            // fallback: prima visita diretta → scan
            foreach ($this->parseCsvRows() as $assoc) {
                $child = $makeChild($assoc, $fallback);
                if ($child['slug'] === $childSlug) {
                    return Pages::factory([ $child ], $this);
                }
            }
            return new Pages([]);
        }

        // === Listing: single-scan = children + total + row-map + indice filtri ===
        $limit  = max(1, (int)(get('limit')  ?? $defaults['pageSize']));
        $offset = max(0, (int)(get('offset') ?? 0));

        $children = [];
        $total    = 0;

        // prepara row-map e filters-index key
        $csvBody = $this->fetchCsvBody();
        $hash    = $csvBody ? md5($csvBody) : null;
        $cache   = $this->kirby()->cache('sheet');
        $mapKey  = $hash ? 'csvmap:' . $hash : null;

        $map  = ($hash && self::$rowMapCache !== null) ? self::$rowMapCache : (($hash && $cache->get($mapKey)) ?: []);
        $fidx = self::$filtersIndexCache ?? [];

        foreach ($this->parseCsvRows() as $assoc) {
            // indice filtri (tokenizzato) per tutti i campi filtrabili
            foreach ($this->filterableFields() as $ff) {
                foreach ($this->tokenize($assoc[$ff] ?? '') as $token) {
                    $fidx[$ff][] = $token;
                }
            }

            // applica filtri attivi (AND tra campi, AND tra valori del singolo campo)
            if ($hasActiveFilters) {
                $ok = true;
                foreach ($activeFilters as $alias => $requiredSlugs) {
                    $rowTokensSlugs = array_map(
                        fn($t) => Str::slug($t),
                        $this->tokenize($assoc[$alias] ?? '')
                    );
                    // tutti i required devono esistere nei token della riga
                    $missing = array_diff($requiredSlugs, $rowTokensSlugs);
                    if (!empty($missing)) {
                        $ok = false;
                        break;
                    }
                }
                if (!$ok) continue;
            }

            $total++; // conteggio risultati (post-filtri)

            // crea child e slug
            $child = $makeChild($assoc, $fallback);
            $slug  = $child['slug'];

            // aggiorna row-map
            if ($hash && !isset($map[$slug])) {
                $map[$slug] = $child['content'];
            }

            // accumula solo il blocco richiesto
            if ($total <= $offset) continue;
            if (count($children) < $limit) {
                $children[] = $child;
            }
            // continua per completare indice filtri e mappa
        }

        // normalizza filtri (unici + ordinati) e salva per-request cache
        foreach ($fidx as $k => $arr) {
            $arr = array_values(array_unique($arr));
            sort($arr, SORT_NATURAL | SORT_FLAG_CASE);
            $fidx[$k] = $arr;
        }
        self::$filtersIndexCache = $fidx;

        // salva caches persistenti
        self::$rowCountCache = $total;
        if ($hash && $mapKey) {
            $cache->set($mapKey, $map, $defaults['ttl']);
            self::$rowMapCache = $map;
        }

        return empty($children) ? new Pages([]) : Pages::factory($children, $this);
    }

    /* ===== Pool COMPLETO per la ricerca (senza limiti/filtri) ===== */
    public function searchPool(): Pages
    {
        // per-request cache: se già creato in questa richiesta, riusa
        if (self::$searchPoolCache instanceof Pages) {
            return self::$searchPoolCache;
        }

        $defaults = $this->sheetDefaults();
        $fallback = $defaults['fallback'];

        // helper locali (replicano logica children, ma senza limit/filtri)
        $firstNonEmpty = function (...$vals) {
            foreach ($vals as $v) {
                if (isset($v) && trim((string)$v) !== '') return $v;
            }
            return null;
        };

        $makeChild = function(array $assoc) use ($firstNonEmpty, $fallback) {
            $base = $firstNonEmpty(
                $assoc['slug']  ?? null,
                $assoc['titolo']?? $assoc['title'] ?? null,
                $assoc['nodo']  ?? null,
                $assoc['name']  ?? null
            );
            if ($base === null) $base = substr(md5(json_encode($assoc)), 0, 10);
            $slug = Str::slug((string)$base);

            // fallback sui campi: evita vuoti che penalizzano la search
            $content = $assoc;
            foreach ($content as $k => $v) {
                if (trim((string)$v) === '') $content[$k] = $fallback;
            }
            if (!isset($content['titolo']) || trim((string)$content['titolo']) === '') {
                $content['titolo'] = $fallback;
            }

            $tpl = $content['template'] ?? 'spreadsheet-item';
            return [
                'slug'     => $slug,
                'num'      => 0,
                'template' => $tpl,
                'model'    => $tpl,
                'content'  => $content,
            ];
        };

        $children = [];
        foreach ($this->parseCsvRows() as $assoc) {
            $children[] = $makeChild($assoc);
        }

        self::$searchPoolCache = Pages::factory($children, $this);
        return self::$searchPoolCache;
    }
}
