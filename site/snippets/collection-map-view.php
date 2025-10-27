<!-- FILTRI -->
<?php snippet('collection-filters',[
      'logic' => 'and' // oppure 'or'
]); ?>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.7.0/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.7.0/mapbox-gl.css" rel="stylesheet">

    <div id="map" style="width: 100%; height: 600px;"></div>
    
    <script>
        mapboxgl.accessToken = '<?= $collection_parent->map_key() ?>';
        var map = new mapboxgl.Map({
            container: 'map',
            style: '<?= $collection_parent->map_style() ?>',
            center: [<?= $longitude ?>, <?= $latitude ?>],
            zoom: <?= $zoom ?>
        });
        map.scrollZoom.disable();

        var geojson = {
            type: 'FeatureCollection',
            features: [
                <?php foreach ($locations_array as $index => $location): ?>
                {
                    type: 'Feature',
                    geometry: { type: 'Point', coordinates: [<?= $location['lon'] ?>, <?= $location['lat'] ?>] },
                    properties: {
                        title: '<?= htmlspecialchars($location['title']) ?>',
                        url: '<?= htmlspecialchars($location['url']) ?>',
                        marker: '<?= htmlspecialchars($location['marker']) ?>'
                    }
                }<?= $index < count($locations_array) - 1 ? ',' : '' ?>
                <?php endforeach; ?>
            ]
        };

        geojson.features.forEach(function (marker) {
            var el = document.createElement('div');
            el.className = 'marker';
            el.style.backgroundImage = `url('${marker.properties.marker}')`;
            el.style.width = '30px';
            el.style.height = '45px';

            new mapboxgl.Marker(el)
                .setLngLat(marker.geometry.coordinates)
                .setPopup(new mapboxgl.Popup().setHTML(`<p><a href="${marker.properties.url}" target="_self">${marker.properties.title}</a></p>`))
                .addTo(map);
        });

        map.addControl(new mapboxgl.NavigationControl());
    </script>
