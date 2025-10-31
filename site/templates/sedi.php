<?php snippet('header') ?>
<?php snippet('menu') ?>
<?php snippet('checkbanner', ['posizione' => 'sopra']) ?>

<?php
// Marker: prima quello caricato nella pagina (field 'marker'), altrimenti fallback locale
if ($page->marker()->isNotEmpty() && ($f = $page->marker()->toFile())) {
  $markerUrl = $f->url();
} else {
  $markerUrl = url('assets/fallback/marker-default.png'); // assicurati che esista
}

// TTL minuti dal blueprint
$ttlMinutes    = (int)$page->cache_ttl_minutes()->or(10)->value();
$ttlSeconds    = max(60, $ttlMinutes * 60);
$versionBucket = (int) floor(time() / $ttlSeconds); // cache-busting prevedibile

$featuresUrl = $page->url() . '.json?v=' . $versionBucket;
?>

<link href="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css" rel="stylesheet">
<script src="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js"></script>

<div class="map-container" style="position: relative;">
  <form class="sedi" action="<?= $page->url() ?>/" method="get">
    <label class="label" for="provincia">Cerca la sede più vicina a te</label>
    <div class="selector">
      <select name="provincia" id="provincia">
        <option value="tutte" <?= (!$param || $param === '' || $param === 'tutte') ? 'selected' : '' ?>>Vedi tutte</option>
        <?php foreach ($province as $code => $name): ?>
          <option value="<?= esc($code) ?>" <?= ($param === $code) ? 'selected' : '' ?>>
            <?= esc($name) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <input id="button" type="submit" value="CERCA">
    </div>
  </form>

  <div id="map1" style="width:100%; min-height:77vh;"></div>
</div>

<script>
  const MAP_DATA     = <?= json_encode($mapData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  const FEATURES_URL = <?= json_encode($featuresUrl, JSON_UNESCAPED_SLASHES) ?>;
  const MARKER_URL   = <?= json_encode($markerUrl, JSON_UNESCAPED_SLASHES) ?>;
  const FILTERED     = <?= json_encode($param && $param !== '' && $param !== 'tutte') ?>;

  mapboxgl.accessToken = MAP_DATA.token;

  // Fallback chain per gli stili
  const STYLE_PRIMARY   = MAP_DATA.style || 'mapbox://styles/mapbox/light-v11';
  const STYLE_FALLBACK1 = 'mapbox://styles/mapbox/light-v11';
  const STYLE_FALLBACK2 = 'mapbox://styles/mapbox/navigation-day-v1';

  const map = new mapboxgl.Map({
    container: 'map1',
    style: STYLE_PRIMARY,
    center: [MAP_DATA.center.lng, MAP_DATA.center.lat],
    zoom: MAP_DATA.zoom,
    minZoom: 4,
    maxZoom: 18
  });

  if (MAP_DATA.showControls) {
    map.addControl(new mapboxgl.NavigationControl({ showCompass: false }), 'top-right');
  }
  map.scrollZoom.disable();

  // Se lo style non carica, fallback automatico
  let triedFallback1 = false;
  let triedFallback2 = false;
  map.on('error', (e) => {
    if (!triedFallback1) {
      triedFallback1 = true;
      try { map.setStyle(STYLE_FALLBACK1); } catch(_) {}
      return;
    }
    if (!triedFallback2) {
      triedFallback2 = true;
      try { map.setStyle(STYLE_FALLBACK2); } catch(_) {}
      return;
    }
    console.error('Mapbox style error:', e && e.error);
  });

  // Re-aggiungi sorgenti/layers quando cambia lo style (es. dopo fallback)
  map.on('style.load', () => {
    initDataAndLayers();
  });

  async function initDataAndLayers() {
    // Carica features via HTTP (cacheabile grazie al bucket v=)
    let features = [];
    try {
      const res = await fetch(FEATURES_URL, { cache: 'force-cache' });
      const geo = await res.json();
      features = Array.isArray(geo.features) ? geo.features : [];
    } catch (e) {
      console.warn('Impossibile caricare le features:', e);
      features = [];
    }

    // Aggiungi/riaggiungi la source
    if (map.getSource('sedi')) {
      map.getSource('sedi').setData({ type: 'FeatureCollection', features });
    } else {
      map.addSource('sedi', { type: 'geojson', data: { type: 'FeatureCollection', features } });
    }

    // Prova ad usare SEMPRE icona (page->marker o fallback). Se loadImage fallisce, DOM markers.
    map.loadImage(MARKER_URL, (err, image) => {
      if (!err && image) {
        if (!map.hasImage('sedi-marker')) map.addImage('sedi-marker', image);
        addSymbolLayer('sedi-marker', features);
      } else {
        addDomMarkers(features, MARKER_URL);
      }
    });
  }

  function addSymbolLayer(iconName, features) {
    if (!map.getLayer('sedi-symbol')) {
      map.addLayer({
        id: 'sedi-symbol',
        type: 'symbol',
        source: 'sedi',
        layout: {
          'icon-image': iconName,
          'icon-size': 0.6,
          'icon-allow-overlap': true
        }
      });
    }
    bindInteractions('sedi-symbol');
    fitIfNoFilter(features);
  }

  function addDomMarkers(features, iconUrl) {
    // Elimina eventuali marker DOM esistenti
    document.querySelectorAll('.marker-html').forEach(el => el.remove());

    features.forEach(f => {
      const el = document.createElement('div');
      el.className = 'marker-html';
      el.style.width = '30px';
      el.style.height = '30px';
      el.style.backgroundImage = `url('${iconUrl}')`;
      el.style.backgroundSize = 'cover';
      el.style.transform = 'translate(-50%, -50%)';
      el.style.cursor = 'pointer';

      new mapboxgl.Marker(el).setLngLat(f.geometry.coordinates).addTo(map);

      const popup = new mapboxgl.Popup({ closeButton: false, closeOnClick: false });
      el.addEventListener('mouseenter', () => {
        popup.setLngLat(f.geometry.coordinates).setHTML(f.properties.text).addTo(map);
      });
      el.addEventListener('mouseleave', () => popup.remove());
      el.addEventListener('click', () => { if (f.properties.url) window.location.href = f.properties.url; });
    });
    fitIfNoFilter(features);
  }

  function bindInteractions(layerId) {
    const popup = new mapboxgl.Popup({ closeButton: false, closeOnClick: false });
    map.on('mouseenter', layerId, e => {
      map.getCanvas().style.cursor = 'pointer';
      const f = e.features && e.features[0];
      if (!f) return;
      popup.setLngLat(f.geometry.coordinates).setHTML(f.properties.text).addTo(map);
    });
    map.on('mouseleave', layerId, () => {
      map.getCanvas().style.cursor = '';
      popup.remove();
    });
    map.on('click', layerId, e => {
      const f = e.features && e.features[0];
      if (f && f.properties && f.properties.url) window.location.href = f.properties.url;
    });
  }

  function fitIfNoFilter(features) {
    if (!FILTERED && features.length) {
      const bounds = new mapboxgl.LngLatBounds();
      features.forEach(f => bounds.extend(f.geometry.coordinates));
      map.fitBounds(bounds, { padding: 60, maxZoom: 8 });
    }
  }
</script>

<style>
  /* valuta le policy Mapbox prima di nascondere attribuzioni */
  .mapboxgl-ctrl-logo, .mapboxgl-ctrl-attrib { display: none !important; }
  .marker-html { will-change: transform; }
</style>

<?php snippet('newsletter') ?>
<?php snippet('checkbanner', ['posizione' => 'sotto']) ?>
<?php snippet('footer') ?>
