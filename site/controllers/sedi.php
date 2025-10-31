<?php

use Kirby\Toolkit\Str;

return function ($page, $site, $kirby) {

    // Children virtuali dal model (Google Sheet)
    $all = $page->children();

    // Filtro provincia
    $param = get('provincia'); // null | 'tutte' | codice/nome
    if ($param && strtolower($param) !== 'tutte') {
        $collection = $all->filterBy('prov', $param);
    } else {
        $collection = $all;
        $param = $param ?? '';
    }

    // Province per la select (config/province.php oppure derivate)
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

    // Features GeoJSON — popup SENZA provincia (solo Nome + Indirizzo)
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

        $title     = (string)$item->nome();
        $indirizzo = (string)$item->indirizzo();
        $url       = $item->url();

        $titleHtml     = str_replace("'", "’", $title);
        $indirizzoHtml = str_replace("'", "’", $indirizzo);

        $textHtml = "<strong>{$titleHtml}</strong><br>{$indirizzoHtml}";

        $features[] = [
            'type'       => 'Feature',
            'geometry'   => [
                'type'        => 'Point',
                'coordinates' => [$lng, $lat],
            ],
            'properties' => [
                'title' => $title,
                'text'  => $textHtml,
                'url'   => $url,
            ],
        ];
    }

    // Fallback se filtro svuota tutto
    if (empty($features) && $param && strtolower($param) !== 'tutte') {
        foreach ($all as $item) {
            $latStr = str_replace(',', '.', trim((string)$item->lat()));
            $lngStr = str_replace(',', '.', trim((string)$item->lng()));
            if ($latStr === '' || $lngStr === '' || !is_numeric($latStr) || !is_numeric($lngStr)) continue;
            $lat = (float)$latStr; $lng = (float)$lngStr;
            if ($lat === 0.0 && $lng === 0.0) continue;

            $title     = (string)$item->nome();
            $indirizzo = (string)$item->indirizzo();
            $url       = $item->url();

            $titleHtml     = str_replace("'", "’", $title);
            $indirizzoHtml = str_replace("'", "’", $indirizzo);
            $textHtml      = "<strong>{$titleHtml}</strong><br>{$indirizzoHtml}";

            $features[] = [
                'type'       => 'Feature',
                'geometry'   => ['type' => 'Point', 'coordinates' => [$lng, $lat]],
                'properties' => ['title' => $title, 'text' => $textHtml, 'url' => $url],
            ];
        }
        $param = '';
        $collection = $all;
    }

    // Centro & zoom
    $center = ['lng' => 12.5065419, 'lat' => 41.9005635]; // Roma
    $zoom   = ($param && $param !== '' && $param !== 'tutte') ? 8 : 5;
    if (!empty($features) && $param && $param !== '' && $param !== 'tutte') {
        $p = $features[array_rand($features)];
        $center = ['lng' => (float)$p['geometry']['coordinates'][0], 'lat' => (float)$p['geometry']['coordinates'][1]];
    }

    // Mapbox: token
    $mapboxToken = (string)$site->mapbox_token()->or('pk.eyJ1IjoiZmYzMzAwIiwiYSI6ImNsdWFhdzJqeTBmaTEya21tdXJ2bmJhaTMifQ.kOq0BAo-oKwgv2Do0rgG7A');

    // Normalizzazione stile dal Panel
    $styleRaw = trim((string)$page->mapbox_style_url()->value());
    $styleUrl = null;

    if ($styleRaw !== '') {
        // 1) mapbox://styles/{user}/{style}
        if (preg_match('~^mapbox://styles/[^/]+/[^/]+$~i', $styleRaw)) {
            $styleUrl = $styleRaw;
        }
        // 2) https://api.mapbox.com/styles/v1/{user}/{style}
        elseif (preg_match('~^https?://api\.mapbox\.com/styles/v1/[^/]+/[^/?#]+~i', $styleRaw)) {
            $styleUrl = $styleRaw;
        }
        // 3) https://studio.mapbox.com/styles/{user}/{style}/  -> converti
        elseif (preg_match('~^https?://studio\.mapbox\.com/styles/([^/]+)/([^/]+)/?~i', $styleRaw, $m)) {
            $styleUrl = 'mapbox://styles/' . $m[1] . '/' . $m[2];
        }
        // 4) qualsiasi altra cosa (es. JSON custom) — lascio passare così com’è
        else {
            $styleUrl = $styleRaw;
        }
    }

    // Fallback piacevole se nulla o non riconosciuto
    if (!$styleUrl) {
        $styleUrl = 'mapbox://styles/mapbox/light-v11';
    }

    $showControls = $page->mapbox_show_controls()->toBool();

    // Pacchetto template
    $mapData = [
        'features'     => $features,
        'center'       => $center,
        'zoom'         => $zoom,
        'token'        => $mapboxToken,
        'style'        => $styleUrl,
        'showControls' => $showControls,
    ];

    // Fallback $formData per snippet esterni
    $formData = static fn($formPage = null) => [
        'responses'     => [],
        'responsesRead' => [],
        'count'         => 0,
        'max'           => null,
        'available'     => null,
        'percent'       => 0,
    ];

    return compact('collection', 'province', 'param', 'mapData', 'formData');
};
