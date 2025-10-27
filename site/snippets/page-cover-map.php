<!-- MAPPA -->
<?php
/** 
 * Snippet per mappa con marker specifici basati sulle categorie
 * Parametri:
 * - $collection_parent: pagina parent della collection
 */
if ($collection_parent && $collection_parent->hasChildren()):
    // Ottieni lo zoom e il centro della mappa dal blueprint del parent
    $zoom = 17;
    $center_page = $page;
    $latitude = $page->locator()->toLocation()->lat();
    $longitude = $page->locator()->toLocation()->lon();
    $parent = $page->parent();
?>

<?php if (param('category') != '' OR param('category') != NULL): ?>
    <!-- CHECK PARAM -->
    <?php $collection = $collection->filterBy('child_category_selector', str_replace("-", " ", param('category')), ","); ?>
<?php else: ?>
    <?php    $collection = $collection->flip(); ?>
<?php endif; ?>


<?php 
    $locations_array = [];
    foreach($collection as $item): 
        $location = $item->locator()->toLocation();
        $child_categories = $item->child_category_selector()->split(','); // Categorie associate al child

        // Default marker
        $item_marker = $parent->default_marker()->toFile()?->url() ?? '';

        // Trova il marker specifico per la prima categoria valida
        foreach($child_categories as $child_category):
            foreach($categories as $category):
                if ($category->nome()->value() == $child_category && $category->marker()->isNotEmpty()):
                    $item_marker = $category->marker()->toFile()->url();
                    break 2; // Esci dai cicli
                endif;
            endforeach;
        endforeach;

        if ($location && $location->lat() && $location->lon()) {
            array_push($locations_array, [
                'title' => $item->title()->value() ?? '',
                'lat' => $location->lat(),
                'lon' => $location->lon(),
                'url' => $item->url() ?? '',
                'marker' => $item_marker ?? 'MISSING'
            ]);
        }
    endforeach;
    ?>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.7.0/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.7.0/mapbox-gl.css" rel="stylesheet">

    <div id="map" style="width: 100%; height: 500px;"></div>
    
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
            el.style.backgroundSize = 'contain';
            el.style.backgroundRepeat = 'no-repeat';
            el.style.backgroundPosition = 'center';
            el.style.display = 'block'; // importantissimo!

            new mapboxgl.Marker(el)
                .setLngLat(marker.geometry.coordinates)
                // .setPopup(new mapboxgl.Popup().setHTML(`<p><a href="${marker.properties.url}" target="_self">${marker.properties.title}</a></p>`))
                .addTo(map);
        });

        map.addControl(new mapboxgl.NavigationControl());
    </script>
<?php endif; ?>
