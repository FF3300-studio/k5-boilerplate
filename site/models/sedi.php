<?php

use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Toolkit\Str;
use Kirby\Http\Remote;
use Kirby\Uuid\Uuid;

class SediPage extends Page
{
    /** =========================
     *  Helpers CSV / Normalizzazione
     *  ========================= */
    protected function normalizeHeader(string $h): string
    {
        // normalizza: minuscole, slug con spazi
        $h = Str::lower(Str::slug($h, ' '));
        $map = [
            'nome esteso della lega' => 'nome',
            'nome lega'              => 'nome',
            'nome'                   => 'nome',

            'indirizzo della lega'   => 'indirizzo',
            'indirizzo'              => 'indirizzo',

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

    protected function parseCsvString(string $csv, string $separator): array
    {
        $rows = [];
        $lines = preg_split('/\R/u', trim($csv));
        if (!$lines || count($lines) === 0) return [];

        $headers = str_getcsv(array_shift($lines), $separator);
        $headers = array_map('trim', $headers);
        $headers = array_map(fn($h) => $this->normalizeHeader($h), $headers);

        foreach ($lines as $line) {
            if (trim($line) === '') continue;
            $cols = str_getcsv($line, $separator);
            if (count($cols) < count($headers)) $cols = array_pad($cols, count($headers), '');
            $row = [];
            foreach ($headers as $i => $h) {
                $row[$h] = isset($cols[$i]) ? trim($cols[$i]) : '';
            }
            $rows[] = $row;
        }
        return $rows;
    }

    /** =========================
     *  Cache TTL (da blueprint)
     *  ========================= */
    protected function cacheTtlSeconds(): int
    {
        $minutes = (int)$this->content()->get('cache_ttl_minutes')->or(10)->value();
        return max(1, $minutes) * 60;
    }

    /** =========================
     *  Sorgenti dati (con cache)
     *  ========================= */
    protected function rowsFromLocalCsv(string $separator = ';'): array
    {
        $file = $this->content()->get('data_file')->toFile();
        if (!$file) return [];

        $cache = kirby()->cache('sedi');
        $key   = 'rows.local.' . $this->id() . '.' . $file->filename() . '.' . $file->modified();
        if ($cached = $cache->get($key)) return $cached;

        $rows = $this->parseCsvString($file->read(), $separator);
        $cache->set($key, $rows, $this->cacheTtlSeconds());
        return $rows;
    }

    protected function rowsFromGoogleSheet(?string $sheetId, string $gid = '0'): array
    {
        $url = trim((string)$this->content()->get('gsheet_url')->value());
        if ($url === '') {
            if (!$sheetId) return [];
            $url = 'https://docs.google.com/spreadsheets/d/' . rawurlencode($sheetId)
                 . '/export?format=csv&gid=' . rawurlencode($gid);
        }

        $cache = kirby()->cache('sedi');
        $key   = 'rows.gsheet.' . $this->id() . '.' . sha1($url);
        if ($cached = $cache->get($key)) return $cached;

        try {
            $res = Remote::get($url, ['method' => 'GET', 'timeout' => 12, 'headers' => ['Cache-Control' => 'no-cache']]);
            $csv = $res->code() === 200 ? $res->content() : @file_get_contents($url);
        } catch (\Throwable $e) {
            $csv = @file_get_contents($url);
        }
        if ($csv === false || $csv === null) return [];

        $rows = $this->parseCsvString($csv, ','); // export Google = virgola
        $cache->set($key, $rows, $this->cacheTtlSeconds());
        return $rows;
    }

    /** =========================
     *  Rows -> virtual children
     *  ========================= */
    protected function rowsToPages(array $rows): Pages
    {
        $children = array_map(function ($r) {
            $nome      = $r['nome']       ?? ($r['nome esteso della lega'] ?? '');
            $indirizzo = $r['indirizzo']  ?? '';
            $cap       = $r['cap']        ?? '';
            $lat       = $r['lat']        ?? '';
            $lng       = $r['lng']        ?? '';
            $prov      = $r['provincia']  ?? ($r['prov'] ?? '');
            $mail      = $r['email']      ?? ($r['email lega'] ?? '');
            $tel       = $r['telefono']   ?? ($r['telefono lega'] ?? '');

            return [
                'slug'     => Str::slug($nome),
                'template' => 'sede',
                'model'    => 'sede',
                'num'      => 0,
                'content'  => [
                    'title'      => $nome,
                    'nome'       => $nome,
                    'indirizzo'  => $indirizzo,
                    'cap'        => $cap,
                    'lat'        => $lat,
                    'lng'        => $lng,
                    'prov'       => $prov,
                    'mail'       => $mail,
                    'tel'        => $tel,
                    'uuid'       => Uuid::generate(),
                ]
            ];
        }, $rows);

        return Pages::factory($children, $this);
    }

    /** =========================
     *  children()
     *  ========================= */
    public function children(): Pages
    {
        if ($this->children instanceof Pages) {
            return $this->children;
        }

        $type      = $this->content()->get('data_source')->or('gsheet')->value(); // gsheet|csv
        $separator = $this->content()->get('csv_separator')->or(';')->value();

        $rows = ($type === 'gsheet')
            ? $this->rowsFromGoogleSheet(
                trim((string)$this->content()->get('gsheet_id')->value()) ?: null,
                trim((string)$this->content()->get('gsheet_gid')->or('0')->value()) ?: '0'
              )
            : $this->rowsFromLocalCsv($separator);

        return $this->children = $this->rowsToPages($rows);
    }
}
