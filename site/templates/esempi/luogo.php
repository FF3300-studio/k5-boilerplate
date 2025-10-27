
<?php snippet('header') ?>

<?php snippet('ecosystem') ?>

<?php snippet('menu') ?>

<?php snippet('breadcrumbs') ?>

<div class="single-page-container luogo-content">
    <div class="luogo-cover">
        <?php if ($image = $page->immagine()->toFile()): ?>
            <img src="<?= $image->url() ?>" alt="Immagine di copertina">
        <?php endif; ?>
        <div class="cover-title">
            <h1><?= $page->title() ?></h1>
        </div>
    </div>
    <div class="luogo-categories">
        <?php $allCategories = site()->categorie_luoghi()->toStructure();
        $categories = $page->categorie_luoghi()->toArray();
        $filteredCategories = $allCategories->filter(function($item) use ($categories) {
            return in_array($item->id(), $categories);
        });  ?>
        <?php foreach ($filteredCategories as $category): ?>
            <p class="single-category"><?php echo esc($category->nome()); ?></p>
        <?php endforeach; ?>
    </div>
    <div class="luogo-title">
        <h1><?= $page->title() ?></h1>
    </div>
</div>

<div class="luogo-content">
    <?php snippet('layouts', [
        'layout_content' => $page->contenuto(),
    ]); ?>
</div>

<div class="luogo-info">
    <div class="row">
        <div class="col-lg-4 col-12">

            <div class="luogo-mappa">

                <?php 
                $locationdata = $page->locator()->toArray(); 
                $locatorString = $locationdata['locator']; // Get the string containing lat, lon, etc.
                preg_match('/lon:\s([-\d.]+)/', $locatorString, $matches); // Use regex to extract the longitude
                $longitude = $matches[1] ?? 'N/A'; // If the match is found, use it, otherwise fallback to 'N/A'
                ?>

                <?php 
                $locationdata = $page->locator()->toArray(); 
                $locatorString = $locationdata['locator']; // Get the string containing lat, lon, etc.
                preg_match('/lat:\s([-\d.]+)/', $locatorString, $matches); // Use regex to extract the longitude
                $latitude = $matches[1] ?? 'N/A'; // If the match is found, use it, otherwise fallback to 'N/A'
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
                
        </div>
        <div class="col-lg-8 col-12">
            <?php $indirizzoluogo = $page->indirizzo_luogo()->kt(); ?>
            <?php if ($page->indirizzo_luogo_toggle()->isTrue() && !empty($indirizzoluogo)): ?>
                <div class="single-info-row">
                    <div class="row">
                        <div class="col-lg-5 col-12 info-label">
                            <p>Indirizzo</p>
                        </div>
                        <div class="col-lg-7 col-12 info-value">
                            <?php echo $indirizzoluogo; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php $orariapertura = $page->orariapertura()->kt(); ?>
            <?php if ($page->orariapertura_toggle()->isTrue() && !empty($orariapertura)): ?>
                <div class="single-info-row">
                    <div class="row">
                        <div class="col-lg-5 col-12 info-label">
                            <p>Orari di apertura</p>
                        </div>
                        <div class="col-lg-7 col-12 info-value">
                            <?php echo $orariapertura; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php $contattiLuogo = $page->contatti_luogo()->kt(); ?>
            <?php if ($page->contatti_luogo_toggle()->isTrue() && !empty($contattiLuogo)): ?>
                <div class="single-info-row">
                    <div class="row">
                        <div class="col-lg-5 col-12 info-label">
                            <p>Contatti</p>
                        </div>
                        <div class="col-lg-7 col-12 info-value">
                            <?php echo $contattiLuogo; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php $sitoweb = $page->sitoweb()->kt(); ?>
            <?php if ($page->sitoweb_toggle()->isTrue() && !empty($sitoweb)): ?>
                <div class="single-info-row">
                    <div class="row">
                        <div class="col-lg-5 col-12 info-label">
                            <p>Sito web</p>
                        </div>
                        <div class="col-lg-7 col-12 info-value">
                            <p><?php echo $sitoweb; ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php $prezzoLuogo = $page->prezzo_luogo()->value(); ?>
            <?php if ($page->prezzo_luogo_toggle()->isTrue() && !empty($prezzoLuogo)): ?>
                <div class="single-info-row">
                    <div class="row">
                        <div class="col-lg-5 col-12 info-label">
                            <p>Costo ingresso</p>
                        </div>
                        <div class="col-lg-7 col-12 info-value">
                            <p><?php echo esc($prezzoLuogo); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="luogo-pass-list-container">
    <div class="passes-title">
        <p>Utilizzando i nostri pass</p>
    </div>
    <div class="luogo-pass-list grid row">
        <?php $inclusi = $page->inclusi()->toStructure();
        if ($inclusi->isNotEmpty()): ?>   
            <?php foreach ($inclusi as $incluso): ?>
                <?php $passPages = $incluso->scontopass()->toPages(); ?>
                <?php if ($passPages->isNotEmpty()): ?>
                    <?php foreach ($passPages as $passPage): ?>
                        <?php 
                        $trasporti = $passPage->trasporti()->value();
                        $prezzo_fisico = $passPage->prezzo_fisico()->value();
                        $prezzo_digitale = $passPage->prezzo_digitale()->value();
                        $data_inizio = $passPage->data_inizio()->toDate('d/m/Y');
                        $thumbnail = $passPage->thumbnail()->toFile();
                        $colore_pass = $passPage->colore_pass()->value();
                        ?>
                        <div class="single-pass col-lg-3 col-12">
                            <a href="<?php echo $passPage->url(); ?>" class="<?php if ($colore_pass) { ?>pass-color-<?php echo $colore_pass; ?><?php } else { ?>pass-color-yellow<?php } ?>">
                                <div class="pass-thumbnail">
                                <?php if ($thumbnail): // Check if thumbnail exists ?>
                                    <img src="<?php echo $thumbnail->url(); ?>" alt="<?php echo $passPage->title(); ?>">
                                <?php else: // Fallback if no thumbnail is set ?>
                                    <img src="https://placehold.co/400x500/png" alt="No image available">
                                <?php endif; ?>
                                </div>
                                <div class="pass-info">
                                    <div class="pass-title">
                                        <p><?php echo $passPage->title()->html(); ?></p>
                                    </div>
                                    <div class="pass-text">
                                        <?php if ($trasporti): ?>
                                            <p>Trasporti: <?php echo $trasporti ? $trasporti : 'N/A'; ?></p>
                                        <?php endif; ?>
                                        <?php if ($prezzo_fisico): ?>
                                            <p>Prezzo Fisico: €<?php echo $prezzo_fisico ? $prezzo_fisico : 'N/A'; ?></p>
                                        <?php endif; ?>
                                        <?php if ($prezzo_digitale): ?>
                                            <p>Prezzo Digitale: €<?php echo $prezzo_digitale ? $prezzo_digitale : 'N/A'; ?></p>
                                        <?php endif; ?>
                                        <?php if ($data_inizio): ?>
                                            <p>Data Inizio: <?php echo $data_inizio ? $data_inizio : 'N/A'; ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="luogo-images">

    <?php
    // Fetch the 'immagini_luogo' structure field
    $immaginiLuogo = $page->immagini_luogo()->toStructure();

    foreach ($immaginiLuogo as $immagineLuogo) {
        // Initialize a counter for non-empty columns
        $nonEmptyColumnsCount = 0;
        
        // Check each column and increment the counter if it's not empty
        if ($immagineLuogo->colonna_1()->isNotEmpty()) {
            $nonEmptyColumnsCount++;
        }
        if ($immagineLuogo->colonna_2()->isNotEmpty()) {
            $nonEmptyColumnsCount++;
        }
        if ($immagineLuogo->colonna_3()->isNotEmpty()) {
            $nonEmptyColumnsCount++;
        } ?>

        <?php if ($nonEmptyColumnsCount == 1) { ?>
            <div class="row luogo-images-row">
                <div class="col-lg-12 col-12">
                    <?php if ($image = $immagineLuogo->colonna_1()->toFile()) { ?>
                        <img src="<?= $image->url() ?>" alt="<?= $image->alt() ?>">
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

        <?php if ($nonEmptyColumnsCount == 2) { ?>
            <div class="row luogo-images-row">
                <div class="col-lg-6 col-12">
                    <?php if ($image = $immagineLuogo->colonna_1()->toFile()) { ?>
                        <img src="<?= $image->url() ?>" alt="<?= $image->alt() ?>">
                    <?php } ?>
                </div>
                <div class="col-lg-6 col-12">
                    <?php if ($image = $immagineLuogo->colonna_2()->toFile()) { ?>
                        <img src="<?= $image->url() ?>" alt="<?= $image->alt() ?>">
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

        <?php if ($nonEmptyColumnsCount == 3) { ?>
            <div class="row luogo-images-row">
                <div class="col-lg-4 col-12">
                    <?php if ($image = $immagineLuogo->colonna_1()->toFile()) { ?>
                        <img src="<?= $image->url() ?>" alt="<?= $image->alt() ?>">
                    <?php } ?>
                </div>
                <div class="col-lg-4 col-12">
                    <?php if ($image = $immagineLuogo->colonna_2()->toFile()) { ?>
                        <img src="<?= $image->url() ?>" alt="<?= $image->alt() ?>">
                    <?php } ?>
                </div>
                <div class="col-lg-4 col-12">
                    <?php if ($image = $immagineLuogo->colonna_3()->toFile()) { ?>
                        <img src="<?= $image->url() ?>" alt="<?= $image->alt() ?>">
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

    <?php } ?>

</div>

<?php snippet('layouts', [
    'layout_content' => $site->footer(),
    'class' => 'footer',
]); ?>

<?php snippet('footer') ?>
