<?php
use Kirby\Toolkit\Str;
/** @var \Kirby\Cms\Page $page */
header('Content-Type: application/json; charset=UTF-8');

$items = method_exists($page, 'sediItems') ? $page->sediItems() : $page->children();

$paramRaw = get('provincia');
$param    = $paramRaw ?? '';
$norm = fn($v) => Str::slug(Str::lower(trim((string)$v)), '');

if ($param && strtolower($param) !== 'tutte') {
    $needle = $norm($param);
    $items = $items->filter(fn($p) => $norm($p->prov()) === $needle);
}

$features = [];
foreach ($items as $item) {
    $latStr = str_replace(',', '.', trim((string)$item->lat()));
    $lngStr = str_replace(',', '.', trim((string)$item->lng()));
    if ($latStr === '' || $lngStr === '' || !is_numeric($latStr) || !is_numeric($lngStr)) continue;

    $lat = (float)$latStr; $lng = (float)$lngStr;
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
        'properties' => ['title' => $title, 'text' => $textHtml, 'url' => $url],
    ];
}

echo json_encode(['type' => 'FeatureCollection', 'features' => $features], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
