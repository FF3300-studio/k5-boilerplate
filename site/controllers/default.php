<?php

// Importa le utility di Kirby, es. per creare slug
use Kirby\Toolkit\Str;

// ====== FUNZIONI DI SUPPORTO LOCALI ======

// Restituisce la data dell'ultimo appuntamento valido da un evento
function getLastValidDate($event) {
    $ultimo = $event->appuntamenti()->toStructure()->last(); // Prende l'ultimo appuntamento nella struttura
    return ($ultimo && $ultimo->giorno()->isNotEmpty()) ? $ultimo->giorno()->toDate() : null; // Converte in data se disponibile
}

// Filtra e restituisce solo le categorie effettivamente usate dagli elementi della collezione
function getFilteredCategories($collection, $allCategories) {
    $selected = [];

    // Scorre tutti gli elementi della collezione
    foreach ($collection as $child) {
        // Divide le categorie associate a ciascun elemento
        foreach ($child->child_category_selector()->split() as $cat) {
            $slug = Str::slug($cat);
            // Aggiunge allo stack solo slug unici
            if (!in_array($slug, $selected)) {
                $selected[] = $slug;
            }
        }
    }

    // Ritorna solo le categorie effettivamente usate
    return $allCategories->filter(fn($cat) => in_array(Str::slug($cat->nome()), $selected));
}

// Filtra la collezione in base alle categorie attive, con logica AND o OR
function filterByCategories($collection, $activeCategories, $logic) {
    return $collection->filter(function ($item) use ($activeCategories, $logic) {
        if (empty($activeCategories)) return true; // Nessun filtro: mostra tutto
        $itemCategories = array_map([Str::class, 'slug'], $item->child_category_selector()->split()); // Slug delle categorie dell'elemento
        return $logic === 'and'
            ? !array_diff($activeCategories, $itemCategories) // AND: tutte le categorie devono essere presenti
            : count(array_intersect($activeCategories, $itemCategories)) > 0; // OR: almeno una deve combaciare
    });
}

// Estrae tutti i gruppi (es. sezioni) unici dalle categorie filtrate
function getGroupsFromCategories($categories) {
    return array_values(array_unique(array_map(fn($cat) => $cat->gruppo()->value(), iterator_to_array($categories))));
}

// Genera l'array delle location da visualizzare su mappa, inclusi i marker personalizzati
function getLocationsArray($collection, $categories, $defaultMarker, $activeCategories, $filterLogic) {
    $filtered = filterByCategories($collection, $activeCategories, $filterLogic); // Filtra gli elementi per categoria

    $locations = [];

    foreach ($filtered as $item) {
        $location = $item->locator()->toLocation(); // Ottiene coordinate geografiche dell’elemento
        $marker = $defaultMarker ? $defaultMarker->url() : null; // Usa marker di default

        $itemCategories = $item->child_category_selector()->split(','); // Divide le categorie associate all'elemento

        // Cerca un marker personalizzato in base alla categoria
        foreach ($itemCategories as $catName) {
            foreach ($categories as $category) {
                if ($category->nome()->value() == $catName && $category->marker()->isNotEmpty()) {
                    $marker = $category->marker()->toFile()->url(); // Usa marker personalizzato
                    break 2; // Esce da entrambi i cicli annidati
                }
            }
        }

        // Aggiunge solo se lat/lon e marker sono validi
        if ($location && $location->lat() && $location->lon() && $marker) {
            $locations[] = [
                'title' => $item->title()->value(),
                'lat' => $location->lat(),
                'lon' => $location->lon(),
                'url' => $item->url(),
                'marker' => $marker
            ];
        }
    }

    return $locations;
}

// Calcola dati utili per la gestione di un form (es. iscrizioni, percentuale, posti disponibili)
function getFormData($formPage, $site) {
    // Trova tutte le risposte al form collegate alla pagina
    $responses = $site->index(true)->filter(fn($p) =>
        $p->intendedTemplate()->name() === 'formrequest' &&
        Str::startsWith($p->id(), $formPage->id())
    );

    // Filtra solo quelle che sono state lette
    $responsesRead = $responses->filter(fn($p) => $p->content()->get('read')->isNotEmpty());

    $count = $responsesRead->count(); // Totale risposte lette
    $max = $formPage->num_max()->isNotEmpty() ? (int)$formPage->num_max()->value() : null; // Numero massimo iscritte (se esiste)
    $available = ($max !== null) ? max(0, $max - $count) : null; // Calcola posti disponibili

    // Calcola percentuale di completamento
    $percent = 0;
    if ($max && $max > 0) {
        $raw = ($count / $max) * 100;
        $percent = $count > 0 ? max(5, min(100, $raw)) : 0; // Impone un minimo del 5%
    }

    return compact('responses', 'responsesRead', 'count', 'max', 'available', 'percent');
}

// ====== CONTROLLER PRINCIPALE ======

return function ($page, $site, $kirby) {

    // ====== INIZIALIZZAZIONE ======
    $collection = $page->children()->listed(); // Prende le pagine figlie visibili
    $allCategories = $page->parent_category_manager()->toStructure(); // Tutte le categorie disponibili
    $activeCategories = param('category') ? array_map([Str::class, 'slug'], explode('+', param('category'))) : []; // Legge le categorie attive dall'URL
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
    $locations_array = getLocationsArray($collection, $allCategories, $default_marker, $activeCategories, $filterLogic); // Marker mappa

    // ====== FORM ======
    $formData = fn($formPage = null) => getFormData($formPage ?? $page, $site); // Funzione chiusa per gestire form per ogni pagina

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
        'formData',
        'zoom',
        'latitude',
        'longitude',
        'locations_array'
    ) + ['filter_counter' => 0]; // Aggiunta manuale del contatore filtri (placeholder)
};
