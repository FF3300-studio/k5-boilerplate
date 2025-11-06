<?php

// Importa le utility di Kirby, es. per creare slug
use Kirby\Toolkit\Str;
use function Site\Helpers\Collection\buildCategoryMarkerMap;
use function Site\Helpers\Collection\filterByCategories;
use function Site\Helpers\Collection\getFilteredCategories;
use function Site\Helpers\Collection\formDataFor;
use function Site\Helpers\Collection\getGroupsFromCategories;
use function Site\Helpers\Collection\getLastValidDate;
use function Site\Helpers\Collection\getLocationsArray;

// ====== CONTROLLER PRINCIPALE ======

return function ($page, $site, $kirby) {

    // ====== INIZIALIZZAZIONE ======
    $collection = $page->children()->listed(); // Prende le pagine figlie visibili
    $allCategories = $page->parent_category_manager()->toStructure(); // Tutte le categorie disponibili
    $categoryMarkerMap = buildCategoryMarkerMap($allCategories); // Mappa slug => markerUrl
    $activeCategories = param('category') ? array_map('Str::slug', explode('+', param('category'))) : []; // Legge le categorie attive dall'URL
    $filterLogic = param('logic') === 'and' ? 'and' : 'or'; // Logica di filtro (default OR)

    // ====== FILTRI E GRUPPI ======
    $filteredCategories = getFilteredCategories($collection, $allCategories); // Categorie effettivamente usate
    $gruppi = getGroupsFromCategories($filteredCategories); // Gruppi unici associati

    // ====== FILTRO SULLA COLLEZIONE ======
    $filteredCollection = filterByCategories($collection, $activeCategories, $filterLogic); // Collezione filtrata per categoria

    // ====== EVENTI FUTURI / PASSATI ======
    $today = strtotime(date('Y-m-d')); // Data di oggi in formato timestamp

    // Eventi con data futura
    $futureEvents = filterByCategories(
        $collection->filter(fn($e) => ($d = getLastValidDate($e)) && $d >= $today),
        $activeCategories,
        $filterLogic
    );

    // Eventi con data passata
    $pastEvents = filterByCategories(
        $collection->filter(fn($e) => ($d = getLastValidDate($e)) && $d < $today),
        $activeCategories,
        $filterLogic
    );

    // ====== MAPPA ======
    $zoom = $page->zoom_mappa()->or(10); // Zoom di default per la mappa
    $center_page = $page->children()->find($page->centro_mappa()->value()); // Elemento da usare come centro mappa
    $latitude = $center_page ? $center_page->locator()->toLocation()->lat() : '0'; // Latitudine del centro
    $longitude = $center_page ? $center_page->locator()->toLocation()->lon() : '0'; // Longitudine del centro
    $default_marker = $page->default_marker()->toFiles()->first(); // Marker di default
    $locations_array = getLocationsArray($collection, $categoryMarkerMap, $default_marker, $activeCategories, $filterLogic); // Marker mappa

    // ====== OUTPUT VERSO IL TEMPLATE ======
    return compact(
        'allCategories',
        'collection',
        'filteredCollection',
        'filteredCategories',
        'activeCategories',
        'filterLogic',
        'gruppi',
        'futureEvents',
        'pastEvents',
        'zoom',
        'latitude',
        'longitude',
        'locations_array'
    ) + ['filter_counter' => 0] + formDataFor($page); // Aggiunta manuale del contatore filtri (placeholder)
};
