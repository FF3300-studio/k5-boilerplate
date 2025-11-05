<?php

use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Http\Remote;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuid;

class SediPage extends Page
{
    /* ---------------------------------------
       Rilevamento contesto: Panel vs Frontend
    --------------------------------------- */
    protected function isPanelRequest(): bool
    {
        // 1) path richiesto
        $path = $this->kirby()->path();
        if (is_string($path) && ($path === 'panel' || Str::startsWith($path, 'panel/') || Str::contains($path, '/panel/'))) {
            return true;
        }

        // 2) referer che proviene dal panel
        $ref = $_SERVER['HTTP_REFERER'] ?? '';
        if (is_string($ref) && Str::contains($ref, '/panel')) {
            return true;
        }

        // 3) richieste XHR tipiche del panel
        $xhr = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        if (is_string($xhr) && strtolower($xhr) === 'xmlhttprequest') {
            // se l’URL richiesto o il referer includono "panel", assumiamo contesto Panel
            if ((is_string($path) && Str::contains($path, 'panel')) || (is_string($ref) && Str::contains($ref, '/panel'))) {
                return true;
            }
        }

        return false;
        // Nota: niente userAgent; evitiamo metodi non disponibili in Kirby\Request
    }

    /* -------------------------------
       Normalizzazione header CSV
    ------------------------------- */
    protected function normalizeHeader(string $h): string
    {
        $h = Str::lower(Str::slug($h, ' '));
        $map = [
            'nome esteso della lega' => 'nome',
            'nome lega'              => 'nome',
            'nome'                   => 'nome',
            'indirizzo della lega'   => 'indirizzo',
            'indirizzo'              => 'indirizzo',
            'citta'                  => 'citta',
            'città'                  => 'citta',
            'cap'                    => 'cap',
            'lat'                    => 'lat',
            'latitudine'             => 'lat',
            'latitude'               => 'lat',
            'lng'                    => 'lng',
            'lon'                    => 'lng',
            'longitudine'            => 'lng',
            'longitude'              => 'lng',
            'prov'                   => 'provincia',
            'provincia'              => 'provincia',
            'email lega'             => 'email',
            'email'                  => 'email',
            'telefono lega'          => 'telefono',
            'telefono'               => 'telefono',
        ];
        return $map[$h] ?? $h;
    }

    protected function stripBom(string $s): string
    {
        return (substr($s, 0, 3) === "\xEF\xBB\xBF") ? substr($s, 3) : $s;
    }

    protected function detectSeparator(string $csv): string
    {
        $firstLine = strtok($csv, "\r\n");
        if ($firstLine === false) return ',';
        $candidates = [",", ";", "\t"];
        $max = -1; $best = ',';
        foreach ($candidates as $sep) {
            $cnt = substr_count($firstLine, $sep);
            if ($cnt > $max) { $max = $cnt; $best = $sep; }
        }
        return $best ?: ',';
    }

    protected function parseCsvString(string $csv, ?string $separator = null): array
    {
        $csv = $this->stripBom($csv);
        $lines = preg_split('/\R/u', trim($csv));
        if (!$lines || count($lines) === 0) return [];

        $sep = $separator ?? $this->detectSeparator($csv);
        $headers = str_getcsv(array_shift($lines), $sep);
        $headers = array_map('trim', $headers);
        $headers = array_map(fn($h) => $this->normalizeHeader($h), $headers);

        $rows = [];
        foreach ($lines as $line) {
            if (trim($line) === '') continue;
            $cols = str_getcsv($line, $sep);
            if (count($cols) < count($headers)) $cols = array_pad($cols, count($headers), '');
            $row = [];
            foreach ($headers as $i => $h) {
                $val = $cols[$i] ?? '';
                $row[$h] = is_string($val) ? trim($val) : $val;
            }
            $rows[] = $row;
        }
        return $rows;
    }

    /* ---------------------------------------
       Cache TTL: più generosa in Panel
    --------------------------------------- */
    protected function cacheTtlSeconds(): int
    {
        if ($this->isPanelRequest()) {
            // Evitiamo hammering durante l’editing
            return 300; // 5 minuti
        }
        if (option('debug') === true) {
            // In dev: TTL breve ma non 1s per non martellare
            return 60;
        }
        $minutes = (int)$this->cache_ttl_minutes()->or(10)->value();
        return max(1, $minutes) * 60;
    }

    /* ----------------------
       Google Sheet → righe
    ---------------------- */
    protected function rowsFromGoogleSheet(): array
    {
        $explicitUrl = trim((string)$this->gsheet_url());
        $sheetId     = trim((string)$this->gsheet_id());
        $gid         = trim((string)$this->gsheet_gid()->or('0'));

        $url = $explicitUrl !== ''
            ? $explicitUrl
            : ($sheetId !== '' ? 'https://docs.google.com/spreadsheets/d/' . rawurlencode($sheetId) . '/export?format=csv&gid=' . rawurlencode($gid) : '');

        if ($url === '') return [];

        $cache = kirby()->cache('sedi');
        $key   = 'rows.gsheet.' . $this->id() . '.' . sha1($url);

        if ($cached = $cache->get($key)) return $cached;

        try {
            $res = Remote::get($url, ['timeout' => 12, 'headers' => ['Cache-Control' => 'no-cache']]);
            $csv = $res->code() === 200 ? $res->content() : @file_get_contents($url);
        } catch (\Throwable $e) {
            $csv = @file_get_contents($url);
        }
        if (!$csv) return [];

        $rows = $this->parseCsvString($csv, ','); // export Google = virgola
        $cache->set($key, $rows, $this->cacheTtlSeconds());
        return $rows;
    }

    /* -------------------
       CSV locale → righe
    ------------------- */
    protected function rowsFromCsvFile(): array
    {
        if ($this->csv_data()->isEmpty()) return [];
        $file = $this->csv_data()->toFile();
        if (!$file) return [];

        $cache = kirby()->cache('sedi');
        $mod   = (int)($file->modified() ?? 0);
        $key   = 'rows.csvfile.' . $this->id() . '.' . sha1($file->filename() . '|' . $mod);

        if ($cached = $cache->get($key)) return $cached;

        try { $csv = $file->read(); } catch (\Throwable $e) { $csv = null; }
        if (!$csv) return [];

        $rows = $this->parseCsvString($csv, ','); // forziamo virgola
        $cache->set($key, $rows, $this->cacheTtlSeconds());
        return $rows;
    }

    /* ---------------------------
       Factory: righe → pagine
    --------------------------- */
    protected function rowsToPages(array $rows): Pages
    {
        $seen = [];
        $children = array_map(function ($r) use (&$seen) {
            $nome      = $r['nome']       ?? ($r['nome esteso della lega'] ?? '');
            $indirizzo = $r['indirizzo']  ?? '';
            $cap       = $r['cap']        ?? '';
            $lat       = $r['lat']        ?? '';
            $lng       = $r['lng']        ?? '';
            $prov      = $r['provincia']  ?? ($r['prov'] ?? '');
            $mail      = $r['email']      ?? ($r['email lega'] ?? '');
            $tel       = $r['telefono']   ?? ($r['telefono lega'] ?? '');
            $citta     = $r['citta']      ?? ($r['città'] ?? '');

            $baseSlug = Str::slug($nome ?: 'sede');
            $slug     = $baseSlug;
            $i        = 1;
            while (isset($seen[$slug])) $slug = $baseSlug . '-' . (++$i);
            $seen[$slug] = true;

            // coerciamo lat/lng
            $lat = str_replace(',', '.', (string)$lat);
            $lng = str_replace(',', '.', (string)$lng);

            return [
                'slug'     => $slug,
                'template' => 'sede',
                'model'    => 'sede',
                'num'      => 0,
                'content'  => [
                    'title'      => $nome ?: 'Sede',
                    'nome'       => $nome,
                    'indirizzo'  => $indirizzo,
                    'citta'      => $citta,
                    'cap'        => $cap,
                    'lat'        => is_numeric($lat) ? (string)(float)$lat : (string)$lat,
                    'lng'        => is_numeric($lng) ? (string)(float)$lng : (string)$lng,
                    'prov'       => $prov,
                    'mail'       => $mail,
                    'tel'        => $tel,
                    'uuid'       => Uuid::generate(),
                ]
            ];
        }, $rows);

        return Pages::factory($children, $this);
    }

    /* ---------------------------------------
       API pubblica (solo Frontend)
    --------------------------------------- */
    public function sediItems(): Pages
    {
        // In Panel NON generiamo i virtual children (evita spinner/errori)
        if ($this->isPanelRequest()) {
            return new Pages([], $this);
        }

        static $cacheLocal = null;
        if ($cacheLocal instanceof Pages) return $cacheLocal;

        $source = (string)$this->data_source()->or('gsheet');
        $rows = [];

        if ($source === 'csvfile') {
            $rows = $this->rowsFromCsvFile();
            if (empty($rows)) $rows = $this->rowsFromGoogleSheet();
        } else {
            $rows = $this->rowsFromGoogleSheet();
            if (empty($rows)) $rows = $this->rowsFromCsvFile();
        }

        return $cacheLocal = $this->rowsToPages($rows);
    }

    /* ---------------------------------------
       Routing dei virtual children
       - In Panel torniamo ai children “reali”
    --------------------------------------- */
    public function children(): Pages
    {
        if ($this->isPanelRequest()) {
            return parent::children();
        }
        return $this->sediItems();
    }

    public function child(string $slug): ?Page
    {
        if ($this->isPanelRequest()) {
            return parent::children()->findBy('slug', $slug);
        }
        return $this->children()->findBy('slug', $slug);
    }
}
