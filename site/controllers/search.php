<?php

use Kirby\Cms\Collection;

return function ($site, $pages, $page) {

  // ====== RICERCA ======
  $query   = get('q');
  $results = $site->search($query, [
      'words'       => false,
      'minlength'   => 2,
      'fields'      => ['title', 'keywords', 'descrizione', 'child_category_selector', 'appuntamenti', 'locator', 'contenuto'],
      'score'       => ['title' => 50, 'descrizione' => 30, 'child_category_selector' => 20, 'contenuto' => 1],
      'stopwords'   => ['di','a','da','in','con','su','per','tra','fra','il','lo','la','gli','le']
  ]);

  // ====== FALLBACK PER IL FORM (NESSUN FORM SULLA PAGINA DI SEARCH) ======
  $formData = function ($formPage = null) {
      return [
          'responses'     => new Collection(),  // nessuna risposta
          'responsesRead' => new Collection(),  // nessuna risposta letta
          'count'         => 0,
          'max'           => null,
          'available'     => null,
          'percent'       => 0,
      ];
  };

  return [
    'query'    => $query,
    'results'  => $results,
    'formData' => $formData,
  ];

};
