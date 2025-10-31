<?php
// site/templates/sedi.json.php
// FeatureCollection GeoJSON con cache HTTP parametrizzata dal blueprint

$features = $mapData['features'] ?? [];

$geo = [
  'type' => 'FeatureCollection',
  'features' => $features
];

$json = json_encode($geo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// TTL da blueprint (min 60s)
$ttlMinutes = (int)$page->cache_ttl_minutes()->or(10)->value();
$ttlSeconds = max(60, $ttlMinutes * 60);

// ETag + Cache-Control coerenti col TTL
$etag = '"' . md5($json) . '"';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=' . $ttlSeconds . ', stale-while-revalidate=' . (int)min($ttlSeconds, 120));
header('ETag: ' . $etag);

// 304 Not Modified se coincide
if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
  http_response_code(304);
  exit;
}

echo $json;
