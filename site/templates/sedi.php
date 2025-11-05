<?php snippet('header') ?>
<?php snippet('menu') ?>
<?php snippet('checkbanner', ['posizione' => 'sopra']) ?>

<?php if (!empty($alerts ?? [])): ?>
  <div class="notice notice--warning" style="margin:1rem 0;padding:.8rem 1rem;border:1px solid #f0c36d;background:#fff8e5;border-radius:.5rem;">
    <?php foreach ($alerts as $msg): ?>
      <p style="margin:.2rem 0;line-height:1.4;"><?= esc($msg) ?></p>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<link href="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css" rel="stylesheet">
<script src="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js"></script>

<div class="map-container" style="position: relative;">
  <form class="sedi" action="<?= $page->url() ?>/" method="get" style="margin-bottom:1rem;">
    <label class="label" for="provincia">Cerca la sede più vicina a te</label>
    <div class="selector" style="display:flex;gap:.5rem;align-items:center;">
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

  <div id="map1" style="width:100%; min-height:77vh; border-radius:.5rem; overflow:hidden;"></div>
</div>

<script>
  const MAP_DATA     = <?= json_encode($mapData,   JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  const FEATURES_JS  = <?= json_encode($features ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  const FEATURES_URL = <?= json_encode($featuresUrl, JSON_UNESCAPED_SLASHES) ?>;
  const MARKER_URL   = <?= json_encode($markerUrl,   JSON_UNESCAPED_SLASHES) ?>;
  const FILTERED     = <?= json_encode($param && $param !== '' && $param !== 'tutte') ?>;

  if (!MAP_DATA || !MAP_DATA.token) {
    console.error('Mapbox token mancante.');
    document.getElementById('map1').innerHTML = '<div style="padding:1rem;border:1px solid #eee;border-radius:.5rem;background:#fff">Token Mapbox mancante: configura il token.</div>';
  } else if (!MARKER_URL) {
    console.error('Marker non impostato (campo "marker" sulla pagina Sedi).');
    document.getElementById('map1').innerHTML = '<div style="padding:1rem;border:1px solid #eee;border-radius:.5rem;background:#fff">Nessun marker configurato: carica il file nel campo “marker”.</div>';
  } else {
    mapboxgl.accessToken = MAP_DATA.token;

    const STYLE_PRIMARY   = MAP_DATA.style || 'mapbox://styles/mapbox/light-v11';
    const STYLE_FALLBACK1 = 'mapbox://styles/mapbox/light-v11';
    const STYLE_FALLBACK2 = 'mapbox://styles/mapbox/navigation-day-v1';

    const map = new mapboxgl.Map({
      container: 'map1',
      style: STYLE_PRIMARY,
      center: [MAP_DATA.center.lng, MAP_DATA.center.lat],
      zoom: MAP_DATA.zoom,
      minZoom: 4,
      maxZoom: 18,
      attributionControl: false
    });

    if (MAP_DATA.showControls) {
      map.addControl(new mapboxgl.NavigationControl({ showCompass: false }), 'top-right');
    }
    map.scrollZoom.disable();

    let triedFallback1 = false, triedFallback2 = false;
    map.on('error', (e) => {
      const msg = (e && e.error && (e.error.message || e.error.toString())) || e.toString();
      console.warn('Mapbox style error:', msg);
      if (!triedFallback1) { triedFallback1 = true; try { map.setStyle(STYLE_FALLBACK1); } catch(_) {} return; }
      if (!triedFallback2) { triedFallback2 = true; try { map.setStyle(STYLE_FALLBACK2); } catch(_) {} return; }
    });

    map.on('style.load', async () => {
      let features = Array.isArray(FEATURES_JS) ? FEATURES_JS.slice() : [];

      // Forzo SEMPRE un tentativo dalla .json
      try {
        const res = await fetch(FEATURES_URL, { cache: 'no-store' });
        if (res.ok) {
          const geo = await res.json();
          const fromJson = Array.isArray(geo.features) ? geo.features : [];
          // usa la prima sorgente non-vuota
          if (!features.length && fromJson.length) features = fromJson;
        }
      } catch (e) {
        console.warn('Fetch .json fallito:', e);
      }

      console.log('Sedi features (final):', features.length);

      await ensureSource('sedi', { type: 'geojson', data: { type: 'FeatureCollection', features } });

      const isSvg = /\.svg(?:\?|#|$)/i.test(MARKER_URL);
      if (isSvg) {
        addDomMarkers(features, MARKER_URL);
        fitIfNoFilter(features);
        return;
      }

      let iconAdded = await tryAddRasterIcon('sedi-marker', MARKER_URL);
      if (iconAdded) {
        addSymbolLayer('sedi', 'sedi-symbol', 'sedi-marker');
        bindInteractions('sedi-symbol');
      } else {
        addDomMarkers(features, MARKER_URL);
      }
      fitIfNoFilter(features);
    });

    async function ensureSource(id, def) {
      if (map.getSource(id)) map.getSource(id).setData(def.data);
      else map.addSource(id, def);
    }

    async function tryAddRasterIcon(name, url) {
      try {
        const resp = await fetch(url, { cache: 'force-cache' });
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        const blob = await resp.blob();
        const bmp  = await createImageBitmap(blob);
        if (!map.hasImage(name)) map.addImage(name, bmp, { sdf: false, pixelRatio: 2 });
        return true;
      } catch (e1) {
        return new Promise((resolve) => {
          map.loadImage(url, (err, image) => {
            if (!err && image) {
              if (!map.hasImage(name)) map.addImage(name, image);
              resolve(true);
            } else {
              console.warn('Impossibile caricare il marker come sprite, uso DOM markers.', err || '');
              resolve(false);
            }
          });
        });
      }
    }

    function addSymbolLayer(sourceId, layerId, iconName) {
      if (!map.getLayer(layerId)) {
        map.addLayer({
          id: layerId,
          type: 'symbol',
          source: sourceId,
          layout: {
            'icon-image': iconName,
            'icon-size': 0.7,
            'icon-allow-overlap': true
          }
        });
      }
    }

    function addDomMarkers(features, iconUrl) {
      document.querySelectorAll('.marker-html').forEach(el => el.remove());
      features.forEach(f => {
        const el = document.createElement('div');
        el.className = 'marker-html';
        el.style.width = '36px';
        el.style.height = '36px';
        el.style.backgroundImage = `url('${iconUrl}')`;
        el.style.backgroundRepeat = 'no-repeat';
        el.style.backgroundPosition = 'center';
        el.style.backgroundSize = 'contain';
        el.style.cursor = 'pointer';

        new mapboxgl.Marker({ element: el, anchor: 'bottom' })
          .setLngLat(f.geometry.coordinates)
          .addTo(map);

        const popup = new mapboxgl.Popup({ closeButton: false, closeOnClick: false });
        el.addEventListener('mouseenter', () => {
          popup.setLngLat(f.geometry.coordinates).setHTML(f.properties.text).addTo(map);
        });
        el.addEventListener('mouseleave', () => popup.remove());
        el.addEventListener('click', () => { if (f.properties.url) window.location.href = f.properties.url; });
      });
    }

    function bindInteractions(layerId) {
      const popup = new mapboxgl.Popup({ closeButton: false, closeOnClick: false });
      map.on('mouseenter', layerId, (e) => {
        map.getCanvas().style.cursor = 'pointer';
        const f = e.features && e.features[0];
        if (!f) return;
        popup.setLngLat(f.geometry.coordinates).setHTML(f.properties.text).addTo(map);
      });
      map.on('mouseleave', layerId, () => {
        map.getCanvas().style.cursor = '';
        popup.remove();
      });
      map.on('click', layerId, (e) => {
        const f = e.features && e.features[0];
        if (f && f.properties && f.properties.url) window.location.href = f.properties.url;
      });
    }

    function fitIfNoFilter(features) {
      if (!FILTERED && features && features.length) {
        const b = new mapboxgl.LngLatBounds();
        features.forEach(f => b.extend(f.geometry.coordinates));
        try { map.fitBounds(b, { padding: 60, maxZoom: 8 }); } catch (_) {}
      }
    }
  }
</script>

<style>
  .mapboxgl-ctrl-logo, .mapboxgl-ctrl-attrib { display: none !important; }
  .marker-html { will-change: transform; }
</style>

<?php snippet('newsletter') ?>
<?php snippet('checkbanner', ['posizione' => 'sotto']) ?>
<?php snippet('footer') ?>
