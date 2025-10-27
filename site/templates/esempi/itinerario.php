
<?php snippet('header') ?>

<?php snippet('ecosystem') ?>

<?php snippet('menu') ?>

<?php snippet('breadcrumbs') ?>

<?php snippet('title') ?>

<div class="single-itinerario-info">
    <?php $durata = $page->durata_itinerario()->value(); ?>
    <?php if ($durata): ?>
        <div class="single-info">
            <div class="info-label">
                <p>Durata</p>
            </div>
            <div class="info-value">
                <p><?php echo esc($durata); ?> gg.</p>
            </div>
        </div>
    <?php endif; ?>
    <?php $assignedCategories = $page->categorie_itinerari()->split();
    $allCategories = site()->categorie_itinerari()->toStructure();
    if (!empty($assignedCategories)) { ?>
        <div class="single-info">
            <div class="info-label">
                <p>Tipologia</p>
            </div>
            <div class="info-value">
                <?php foreach ($allCategories as $category) { ?>
                    <?php if (in_array($category->id(), $assignedCategories)) { ?>
                        <p><?php echo $category->nome()->html(); ?></p>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    <?php } else { ?>
    <?php } ?>
</div>

<?php snippet('layouts', [
    'layout_content' => $page->contenuto(),
]); ?>

<div class="single-page-container itinerario-content">
    <div class="itinerario-tappe-preview">
        <div class="row">
            <div class="col-lg-5 col-12 itinerario-description">
                <?= $page->descrizione_itinerario()->kt() ?>
            </div>
            <div class="col-lg-7 col-12 itinerario-map">
                <div class="itinerario-mappa">
                <?php
// Fetch the structure field
$tappe = $page->tappe()->toStructure();
$locations_array = [];

// Fetch the selected center and zoom values
$centro_mappa_itinerario = $page->centro_mappa_itinerario()->value();
$zoom_mappa_itinerario = $page->zoom_mappa_itinerario()->value();

// Initialize the center coordinates
$centerLat = null;
$centerLon = null;

foreach ($tappe as $tappa) {
    $tipologiaTappa = $tappa->tipologia_tappa()->value();

    if ($tipologiaTappa === 'interno') {
        // For 'interno', get related pages
        $luoghiInterni = $tappa->luoghitappe()->toPages();
        foreach ($luoghiInterni as $luogo) {
            $location = $luogo->locator()->toLocation();
            if ($location && $location->lat()->isNotEmpty() && $location->lon()->isNotEmpty()) {
                array_push($locations_array, [
                    'title' => $luogo->title()->value(),
                    'lat' => $location->lat()->value(),
                    'lon' => $location->lon()->value(),
                    'url' => $luogo->url()
                ]);

                // Check if this location is the selected center
                if ($luogo->title()->value() === $centro_mappa_itinerario) {
                    $centerLat = $location->lat()->value();
                    $centerLon = $location->lon()->value();
                }
            }
        }
    } elseif ($tipologiaTappa === 'esterno') {
        // For 'esterno', get lat/lon from 'tappa' directly
        $lat = $tappa->latitudine_luogo_esterno()->value();
        $lon = $tappa->longitudine_luogo_esterno()->value();
        $url = $tappa->luogo_esterno()->value();
        if (!empty($lat) && !empty($lon)) {
            array_push($locations_array, [
                'title' => $tappa->nome_luogo_esterno()->value(),
                'lat' => $lat,
                'lon' => $lon,
                'url' => $url
            ]);

            // Check if this location is the selected center
            if ($tappa->nome_luogo_esterno()->value() === $centro_mappa_itinerario) {
                $centerLat = $lat;
                $centerLon = $lon;
            }
        }
    }
}
?>

<?php if(!empty($locations_array)): ?>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.7.0/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.7.0/mapbox-gl.js"></script>

    <div class="map-container" style="position: relative;">
        <div id="map" style="width: 100%; height: 600px"></div>

        <script>
            // Replace with your own Mapbox access token
            mapboxgl.accessToken = 'pk.eyJ1IjoiZGF2aWRlZ2lvcmdldHRhIiwiYSI6ImNtMjk1MmFubTAyMXQycXF1ZXVyaTYyMjYifQ.d87xqkbSyGWG8s0xJFfIKg';

            // Create a new Mapbox map instance with dynamic center and zoom
            var map = new mapboxgl.Map({
                container: 'map',
                style: 'mapbox://styles/mapbox/light-v11',
                center: [<?= htmlspecialchars($centerLon) ?>, <?= htmlspecialchars($centerLat) ?>], // Longitude, Latitude
                zoom: <?= htmlspecialchars($zoom_mappa_itinerario) ?> // Adjust zoom level
            });

            var geojson = {
                type: 'FeatureCollection',
                features: [
                    <?php foreach($locations_array as $location): ?>
                        <?php if (!empty(htmlspecialchars($location['lon'])) && !empty(htmlspecialchars($location['lat']))) { ?>
                            {
                                type: 'Feature',
                                geometry: {
                                    type: 'Point',
                                    coordinates: [<?= htmlspecialchars($location['lon']) ?>, <?= htmlspecialchars($location['lat']) ?>]
                                },
                                properties: {
                                    title: '<?= htmlspecialchars($location['title']) ?>',
                                    description: 'Map',
                                    text: 'test',
                                    url: '<?= htmlspecialchars($location['url']) ?>',
                                }
                            },
                        <?php } ?>
                    <?php endforeach; ?>
                ]
            };

            // add markers to map
            geojson.features.forEach(function (marker) {
                var el = document.createElement('div');
                el.className = 'marker';

                var mapMarker = new mapboxgl.Marker(el)
                    .setLngLat(marker.geometry.coordinates)
                    .addTo(map);

                var tooltip = new mapboxgl.Popup({
                    closeButton: false,
                    closeOnClick: false
                });

                var tooltipContent = `
                    <p><a href="${marker.properties.url}">${marker.properties.title}</a></p>
                `;

                el.addEventListener('mouseenter', function () {
                    tooltip.setLngLat(marker.geometry.coordinates)
                        .setHTML(tooltipContent)
                        .addTo(map);
                });

                el.addEventListener('mouseleave', function () {
                    tooltip.remove();
                });

                el.addEventListener('click', function () {
                    window.open(marker.properties.url, '');
                });
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
<?php endif; ?>

                </div>
            </div>
        </div>
    </div>
    <div class="itinerario-tappe">
        <?php $tappe = $page->tappe()->toStructure(); ?>
        <?php $index = 1; ?>
        <?php foreach ($tappe as $tappa): ?>
            <?php if ($index % 2 == 0) { ?>
                <div class="single-tappa layout-left">
            <?php } else { ?>
                <div class="single-tappa layout-right">
            <?php } ?>
                <div class="tappa-image">
                    <?php if ($tappa->tipologia_tappa() == 'esterno'): ?>
                        <?php if ($tappa->luogo_esterno()->isNotEmpty()): ?>
                            <a href="<?= $tappa->luogo_esterno()->toUrl() ?>" target="_blank">
                                <?php if ($image = $tappa->immagine_luogo_esterno()->toFile()): ?>
                                    <img src="<?= $image->url() ?>" alt="<?= $image->alt() ?>">
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>
                    <?php elseif ($tappa->tipologia_tappa() == 'interno'): ?>
                        <?php 
                        $luogoInterno = $tappa->luoghitappe()->toPages();
                        if ($luogoInterno->isNotEmpty()): ?>
                            <?php $luogo = $luogoInterno->first(); ?>
                            <a href="<?php echo esc($luogoInterno->first()->url()); ?>">
                                <?php if ($immagine = $luogo->immagine()->toFile()): ?>
                                    <img src="<?php echo esc($immagine->url()); ?>" alt="<?php echo esc($immagine->alt()); ?>">
                                <?php endif; ?>
                            </a>
                        <?php else: ?>
                            <span>Nessun luogo interno selezionato.</span>
                        <?php endif; 
                    endif; ?>
                </div>
                <div class="tappa-text">
                    <?php if ($tappa->tipologia_tappa() == 'esterno'): ?>
                        <div class="tappa-number">
                            <p><?php echo $index; ?></p>
                        </div>
                        <div class="tappa-title">
                            <p><?= $tappa->nome_luogo_esterno()->html() ?></p>
                        </div>
                        <div class="tappa-description">
                            <p><?= $tappa->descrizione_luogo_esterno()->kt() ?></p>
                        </div>
                        <div class="tappa-details">
                            <p class="label">Dettagli</p>
                            <?php echo $tappa->dettagli()->kt(); ?>
                        </div>
                    <?php elseif ($tappa->tipologia_tappa() == 'interno'): ?>
                        <div class="tappa-number">
                            <p><?php echo $index; ?></p>
                        </div>
                        <?php 
                        $luogoInterno = $tappa->luoghitappe()->toPages();
                        if ($luogoInterno->isNotEmpty()): ?>
                            <?php $luogo = $luogoInterno->first(); ?>
                            <div class="tappa-title">
                                <p><?php echo esc($luogoInterno->first()->title()); ?></p>
                            </div>
                            <?php if ($descrizione = $luogo->descrizione()->value()): ?>
                                <div class="tappa-description">
                                    <p><?php echo esc($descrizione); ?></p>
                                </div>
                            <?php endif; ?>
                            <div class="tappa-details">
                                <p class="label">Dettagli</p>
                                <?php echo $tappa->dettagli()->kt(); ?>
                            </div>
                        <?php else: ?>
                            <span>Nessun luogo interno selezionato.</span>
                        <?php endif; 
                    endif; ?>
                </div>
            </div>
            <?php $index++; ?>
        <?php endforeach; ?>
    </div>
</div>

<?php snippet('layouts', [
    'layout_content' => $site->footer(),
    'class' => 'footer',
]); ?>

<?php snippet('footer') ?>
