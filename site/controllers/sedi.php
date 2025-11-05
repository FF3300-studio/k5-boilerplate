<?php

use Kirby\Toolkit\Str;

return function ($page, $site, $kirby) {

    // Avvisi UI su sorgente
    $alerts = [];
    if ((string)$page->data_source()->or('gsheet') === 'csvfile' && $page->csv_data()->isEmpty()) {
        $alerts[] = 'Sorgente selezionata: CSV locale. Nessun file caricato. Carica un file .csv nel campo “CSV locale”.';
    }

    // Virtual children (dal Model)
    $all = $page->sediItems();

    // --- FILTRO PROVINCIA (robusto: normalizza entrambi i lati) ---
    $paramRaw = get('provincia');
    $param    = $paramRaw ?? '';
    $norm = function ($v) {
        $v = (string)$v;
        $v = trim($v);
        $v = str_replace([' ’ ', '  '], ' ', $v);
        $v = Str::slug(Str::lower($v), ''); // rimuove spazi e segni, tutto minuscolo
        return $v;
    };

    if ($param && strtolower($param) !== 'tutte') {
        $needle = $norm($param);
        $collection = $all->filter(function ($p) use ($needle, $norm) {
            $prov = $norm($p->prov());
            return $prov === $needle;
        });
    } else {
        $collection = $all;
        $param = $param ?? '';
    }

    // --- PROVINCE SELECT ---
    $provinceMapPath = kirby()->root('site') . '/config/province.php';
    if (is_file($provinceMapPath)) {
        /** @var array $province */
        $province = require $provinceMapPath; // CODICE => Nome
    } else {
        $keys = $all->pluck('prov', null);
        $keys = array_map(static fn($v) => (string)$v, $keys);
        $keys = array_values(array_unique(array_filter($keys)));
        sort($keys, SORT_NATURAL | SORT_FLAG_CASE);
        $province = array_combine($keys, $keys);
    }

    // --- GEOJSON FEATURES calcolate lato server (nessun fetch necessario) ---
    $features = [];
    foreach ($collection as $item) {
        $latStr = str_replace(',', '.', trim((string)$item->lat()));
        $lngStr = str_replace(',', '.', trim((string)$item->lng()));
        if ($latStr === '' || $lngStr === '' || !is_numeric($latStr) || !is_numeric($lngStr)) {
            continue;
        }
        $lat = (float)$latStr;
        $lng = (float)$lngStr;
        if ($lat === 0.0 && $lng === 0.0) continue;

        $title     = (string)$item->nome()->or($item->title());
        $indirizzo = (string)$item->indirizzo();
        $url       = $item->url();

        $titleHtml     = str_replace("'", "’", $title);
        $indirizzoHtml = str_replace("'", "’", $indirizzo);
        $textHtml      = "<strong>{$titleHtml}</strong><br>{$indirizzoHtml}";

        $features[] = [
            'type'       => 'Feature',
            'geometry'   => ['type' => 'Point', 'coordinates' => [$lng, $lat]],
            'properties' => [
                'title' => $title,
                'text'  => $textHtml,
                'url'   => $url,
            ],
        ];
    }

    // --- CENTRO & ZOOM ---
    $center = ['lng' => 12.5065419, 'lat' => 41.9005635]; // Roma
    $zoom   = ($param && $param !== '' && $param !== 'tutte') ? 8 : 5;
    if (!empty($features) && $param && $param !== '' && $param !== 'tutte') {
        $p = $features[array_rand($features)];
        $center = ['lng' => (float)$p['geometry']['coordinates'][0], 'lat' => (float)$p['geometry']['coordinates'][1]];
    }

    // --- MAPBOX token & style ---
    $mapboxToken = (string)(option('mapbox.token') ?: $site->mapbox_token()->or(''));
    $styleRaw    = trim((string)$page->mapbox_style_url()->value());
    $styleUrl    = null;

    if ($styleRaw !== '') {
        if (preg_match('~^mapbox://styles/[^/]+/[^/]+$~i', $styleRaw)) {
            $styleUrl = $styleRaw;
        } elseif (preg_match('~^https?://api\.mapbox\.com/styles/v1/[^/]+/[^/?#]+~i', $styleRaw)) {
            $styleUrl = $styleRaw;
        } elseif (preg_match('~^https?://studio\.mapbox\.com/styles/([^/]+)/([^/]+)/?~i', $styleRaw, $m)) {
            $styleUrl = 'mapbox://styles/' . $m[1] . '/' . $m[2];
        } else {
            $styleUrl = $styleRaw;
        }
    }
    if (!$styleUrl) $styleUrl = 'mapbox://styles/mapbox/light-v11';

    $showControls = $page->mapbox_show_controls()->toBool();

    // --- VERSION per .json (rimane disponibile) ---
    $ttlMinutes    = (int)$page->cache_ttl_minutes()->or(10)->value();
    $ttlSeconds    = max(60, $ttlMinutes * 60);
    $versionBucket = (int) floor(time() / $ttlSeconds);
    $featuresUrl   = $page->url() . '.json?v=' . $versionBucket;

    // --- MARKER dal blueprint pagina (obbligatorio) ---
    $markerUrl = null;
    if ($page->marker()->isNotEmpty() && ($f = $page->marker()->toFile())) {
        $markerUrl = $f->url();
    } else {
        $alerts[] = 'Nessun marker impostato: carica un file nel campo “marker” della pagina Sedi.';
    }

    $mapData = [
        'center'       => $center,
        'zoom'         => $zoom,
        'token'        => $mapboxToken,
        'style'        => $styleUrl,
        'showControls' => $showControls,
    ];

    // Passo le features già pronte al template
    return compact(
        'collection',
        'province',
        'param',
        'mapData',
        'alerts',
        'featuresUrl',
        'markerUrl',
        'features'     // <— PRONTE
    );
};
