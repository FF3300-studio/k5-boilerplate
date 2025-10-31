<?php
// site/templates/sedi.json.php
// Restituisce un FeatureCollection GeoJSON con cache HTTP forte

// Il controller 'sedi.php' prepara già $mapData['features'].
// Se arrivi qui senza controller, calcola un fallback minimo.

$features = $mapData['features'] ?? [];

$geo = [
  'type' => 'FeatureCollection',
  'features' => $features
];

$json = json_encode($geo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// ETag per evitare trasferimenti inutili
$etag = '"' . md5($json) . '"';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=300, stale-while-revalidate=60');
header('ETag: ' . $etag);

// 304 Not Modified se coincide
if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
  http_response_code(304);
  exit;
}

echo $json;
