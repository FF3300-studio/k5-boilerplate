
<?php snippet('header') ?>

<?php snippet('ecosystem') ?>

<?php snippet('menu') ?>

<?php snippet('breadcrumbs') ?>

<div class="single-punto-vendita">
    <div class="punto-vendita-mappa">
        
        <?php 
        $locationdata = $page->locator()->toArray(); 
        $locatorString = $locationdata['locator']; // Get the string containing lat, lon, etc.

        // Use a single regex to extract both latitude and longitude
        preg_match("/lat:\s'([-\d.]+)'\s+lon:\s'([-\d.]+)'/", $locatorString, $matches);

        $latitude = $matches[1] ?? 'N/A'; // Latitude value
        $longitude = $matches[2] ?? 'N/A'; // Longitude value
        ?>

        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <!-- <script src="https://unpkg.com/mapbox-gl-js-tooltip@0.4.0/umd/mapbox-gl-tooltip.js"></script> -->
        <link href="https://api.mapbox.com/mapbox-gl-js/v3.7.0/mapbox-gl.css" rel="stylesheet">
        <script src="https://api.mapbox.com/mapbox-gl-js/v3.7.0/mapbox-gl.js"></script>

        <div class="map-container" style="position: relative;">
            <div id="map" style="width: 100%; height: 100%; aspect-ratio: 5 / 3;"></div>

            <script>
                // Replace with your own Mapbox access token
                mapboxgl.accessToken = 'pk.eyJ1IjoiZGF2aWRlZ2lvcmdldHRhIiwiYSI6ImNtMjk1MmFubTAyMXQycXF1ZXVyaTYyMjYifQ.d87xqkbSyGWG8s0xJFfIKg';

                // Create a new Mapbox map instance
                var map = new mapboxgl.Map({
                    container: 'map', // ID of the container element
                    style: 'mapbox://styles/mapbox/light-v11',
                    center: [<?= htmlspecialchars($longitude) ?>,<?= htmlspecialchars($latitude) ?>], // Longitude, Latitude (Naples, Italy)
                    zoom: 12 // Adjust zoom level as needed
                });

                var geojson = {
                type: 'FeatureCollection',
                features: [
                    {
                        type: 'Feature',
                            geometry: {
                            type: 'Point',
                            coordinates: [<?= htmlspecialchars($longitude) ?>,<?= htmlspecialchars($latitude) ?>]
                        },
                        properties: {
                            title: '<?= $page->title(); ?>',
                            description: 'Map',
                            text: 'test',
                            url: '',
                        }
                    },
                ]
            };
            map.scrollZoom.disable();
            // add markers to map
            geojson.features.forEach(function (marker) {
                // create a HTML element for each feature
                var el = document.createElement('div');
                el.className = 'marker';

                // make a marker for each feature and add to the map
                var mapMarker = new mapboxgl.Marker(el)
                    .setLngLat(marker.geometry.coordinates)
                    .addTo(map);

                // Create a tooltip on hover
                var tooltip = new mapboxgl.Popup({
                    closeButton: false,
                    closeOnClick: false
                });

                var tooltipContent = `
                    <p>${marker.properties.title}</p>
                `;

                el.addEventListener('mouseenter', function () {
                    tooltip.setLngLat(marker.geometry.coordinates)
                        .setHTML(tooltipContent)
                        .addTo(map);
                });

                el.addEventListener('mouseleave', function () {
                    tooltip.remove();
                });

                // el.addEventListener('click', function () {
                //     tooltip.toggle();
                // });
            });
            map.addControl(new mapboxgl.NavigationControl());
                
            </script>

            <?php $marker = $site->marker()->toFiles()->first()->url(); ?>
            <style>
                .marker {
                    background-image: url('<?php echo $marker; ?>');
                    background-size: contain;
                    background-repeat: no-repeat;
                    background-position: center;
                    width: 30px;
                    height: 30px;
                    border-radius: 50%;
                }
            </style>

        </div>
    </div>
    <div class="punto-vendita-info">
        <div class="punto-vendita-title">
            <h1><?php echo $page->title()->html(); ?></h1>
        </div>
        <?php if ($page->orariapertura_toggle()->toBool()): ?>
            <div class="punto-vendita-orariapertura">
                <p><?php echo $page->orariapertura()->kt(); ?></p>
            </div>
        <?php endif; ?>
        <?php if ($page->contatti_punto_vendita_toggle()->toBool()): ?>
            <div class="punto-vendita-contatti">
                <?php foreach ($page->contatti_punto_vendita()->toStructure() as $contatto): ?>
                    <div class="contatto">
                        <div class="row">
                            <div class="col-lg-4 col-12">
                                <p><?php echo $contatto->nome_contatto(); ?></p>
                            </div>
                            <div class="col-lg-8 col-12">
                                <?php echo $contatto->valore_contatto()->html(); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="luogo-content">
    <?php snippet('layouts', [
        'layout_content' => $page->contenuto(),
    ]); ?>
</div>

<?php snippet('layouts', [
    'layout_content' => $site->footer(),
    'class' => 'footer',
]); ?>

<?php snippet('footer') ?>
