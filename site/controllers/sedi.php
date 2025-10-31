<?php

use Kirby\Toolkit\Str;

return function ($page, $site, $kirby) {

    // =========================
    // Dataset (virtual children dal model)
    // =========================
    $all = $page->children();

    // =========================
    // Filtro provincia da querystring
    // =========================
    $param = get('provincia'); // null | 'tutte' | codice/nome
    if ($param && strtolower($param) !== 'tutte') {
        $collection = $all->filterBy('prov', $param);
    } else {
        $collection = $all;
        $param = $param ?? '';
    }

    // =========================
    // Province per la select
    //   - se esiste site/config/province.php (CODICE => Nome), la usiamo
    //   - altrimenti ricaviamo l'elenco dai dati
    // =========================
    $provinceMapPath = kirby()->root('site') . '/config/province.php';
    if (is_file($provinceMapPath)) {
        /** @var array $province */
        $province = require $provinceMapPath; // mappa CODICE => Nome
    } else {
        $keys = $all->pluck('prov', null);
        $keys = array_map(static fn($v) => (string)$v, $keys);
        $keys = array_values(array_unique(array_filter($keys)));
        sort($keys, SORT_NATURAL | SORT_FLAG_CASE);
        $province = array_combine($keys, $keys);
    }

    // =========================
    // Costruzione robusta delle features GeoJSON
    // =========================
    $features = [];
    foreach ($collection as $item) {
        // normalizza lat/lng (accetta "41,90" e "41.90")
        $latStr = str_replace(',', '.', trim((string)$item->lat()));
        $lngStr = str_replace(',', '.', trim((string)$item->lng()));

        // salta record non numerici
        if ($latStr === '' || $lngStr === '' || !is_numeric($latStr) || !is_numeric($lngStr)) {
            continue;
        }

        $lat = (float)$latStr;
        $lng = (float)$lngStr;

        // scarta coordinate (0,0) eventualmente presenti come placeholder
        if ($lat === 0.0 && $lng === 0.0) {
            continue;
        }

        // campi testuali per popup
        $title     = (string)$item->nome();
        $indirizzo = (string)$item->indirizzo();
        $prov      = (string)$item->prov();
        $url       = $item->url();

        // sostituisci apostrofo dritto per evitare rotture nell'HTML del popup
        $titleHtml     = str_replace("'", "’", $title);
        $indirizzoHtml = str_replace("'", "’", $indirizzo);
        $provHtml      = str_replace("'", "’", $prov);

        $textHtml = "<strong>{$titleHtml}</strong><br>{$indirizzoHtml}<br>{$provHtml}";

        $features[] = [
            'type'       => 'Feature',
            'geometry'   => [
                'type'        => 'Point',
                'coordinates' => [$lng, $lat],
            ],
            'properties' => [
                'title' => $title,     // testo "pulito"
                'text'  => $textHtml,  // HTML pronto per popup
                'url'   => $url,
            ],
        ];
    }

    // Fallback: se il filtro ha svuotato tutto, ripiega su tutte le sedi
    if (empty($features) && $param && strtolower($param) !== 'tutte') {
        foreach ($all as $item) {
            $latStr = str_replace(',', '.', trim((string)$item->lat()));
            $lngStr = str_replace(',', '.', trim((string)$item->lng()));
            if ($latStr === '' || $lngStr === '' || !is_numeric($latStr) || !is_numeric($lngStr)) {
                continue;
            }
            $lat = (float)$latStr;
            $lng = (float)$lngStr;
            if ($lat === 0.0 && $lng === 0.0) {
                continue;
            }

            $title     = (string)$item->nome();
            $indirizzo = (string)$item->indirizzo();
            $prov      = (string)$item->prov();
            $url       = $item->url();

            $titleHtml     = str_replace("'", "’", $title);
            $indirizzoHtml = str_replace("'", "’", $indirizzo);
            $provHtml      = str_replace("'", "’", $prov);
            $textHtml      = "<strong>{$titleHtml}</strong><br>{$indirizzoHtml}<br>{$provHtml}";

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
        // azzera il filtro per coerenza UI
        $param = '';
        $collection = $all;
    }

    // =========================
    // Centro & zoom
    // =========================
    $center = ['lng' => 12.5065419, 'lat' => 41.9005635]; // Roma
    $zoom   = ($param && $param !== '' && $param !== 'tutte') ? 8 : 5;

    if (!empty($features) && $param && $param !== '' && $param !== 'tutte') {
        // centra su un punto qualsiasi della provincia filtrata
        $p = $features[array_rand($features)];
        $center = ['lng' => (float)$p['geometry']['coordinates'][0], 'lat' => (float)$p['geometry']['coordinates'][1]];
    }

    // =========================
    // Opzioni Mapbox (token/stile/controlli)
    // =========================
    $mapboxToken  = (string)$site->mapbox_token()->or('pk.eyJ1IjoiZmYzMzAwIiwiYSI6ImNsdWFhdzJqeTBmaTEya21tdXJ2bmJhaTMifQ.kOq0BAo-oKwgv2Do0rgG7A');
    $styleUrl     = (string)$page->mapbox_style_url()->or('mapbox://styles/ff3300/cliyax15s00hv01qy7c409hcv');
    $showControls = $page->mapbox_show_controls()->toBool();
    $siteMarker   = $site->marker()->isNotEmpty() ? $site->marker()->toFile()->url() : null;

    // =========================
    // Pacchetto per il template
    // =========================
    $mapData = [
        'features'     => $features,
        'center'       => $center,
        'zoom'         => $zoom,
        'token'        => $mapboxToken,
        'style'        => $styleUrl,
        'marker'       => $siteMarker,
        'showControls' => $showControls,
    ];

    // =========================
    // Fallback $formData per snippet esterni (layouts.php, ecc.)
    // =========================
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
