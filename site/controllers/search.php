<?php

use function Site\Helpers\Collection\formDataFor;

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

  return [
    'query'    => $query,
    'results'  => $results,
  ] + formDataFor();

};
