<?php

use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Http\Remote;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuid;

class SediPage extends Page
{
    /** =========================
     *  Intestazioni -> chiavi normalizzate
     *  ========================= */
    protected function normalizeHeader(string $h): string
    {
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

    protected function parseCsvString(string $csv, string $separator = ','): array
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
     *  Cache TTL (minuti dal blueprint)
     *  ========================= */
    protected function cacheTtlSeconds(): int
    {
        $minutes = (int)$this->cache_ttl_minutes()->or(10)->value();
        return max(1, $minutes) * 60;
    }

    /** =========================
     *  Google Sheet -> righe (con cache Kirby)
     *  ========================= */
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

        if ($cached = $cache->get($key)) {
            return $cached;
        }

        try {
            $res = Remote::get($url, ['timeout' => 12, 'headers' => ['Cache-Control' => 'no-cache']]);
            $csv = $res->code() === 200 ? $res->content() : @file_get_contents($url);
        } catch (\Throwable $e) {
            $csv = @file_get_contents($url);
        }
        if ($csv === false || $csv === null) return [];

        $rows = $this->parseCsvString($csv, ','); // Google export = virgola
        $cache->set($key, $rows, $this->cacheTtlSeconds());
        return $rows;
    }

    /** =========================
     *  Rows -> children virtuali
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

        // Unica sorgente supportata: Google Sheet
        $rows = $this->rowsFromGoogleSheet();
        return $this->children = $this->rowsToPages($rows);
    }
}
