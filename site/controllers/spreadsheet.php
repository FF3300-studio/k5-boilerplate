<?php

use Kirby\Cms\Pages;
use function Site\Helpers\Collection\formDataFor;

return function ($site, $pages, $page) {

  // ====== RICERCA ======
  $query = get('q');

  // 1) pool base: tutto il sito
  /** @var Pages $basePool */
  $basePool = $site->index();

  // 2) aggiungo TUTTI i figli virtuali degli spreadsheet (ignorando la paginazione)
  $sheetChildren = new Pages();
  foreach ($site->index()->filterBy('template', 'spreadsheet') as $sheetPage) {
    /** @var \SpreadsheetPage $sheetPage */
    $sheetChildren = $sheetChildren->add($sheetPage->searchPool());
  }

  // 3) MERGE + DEDUP esplicito per id (niente chain ambigue)
  $merged = new Pages();
  $seen   = [];

  foreach ($basePool as $p) {
    $id = $p->id();
    if (!isset($seen[$id])) {
      $merged->add($p);
      $seen[$id] = true;
    }
  }
  foreach ($sheetChildren as $p) {
    $id = $p->id();
    if (!isset($seen[$id])) {
      $merged->add($p);
      $seen[$id] = true;
    }
  }

  // 4) Search solo se c’è una query valida, altrimenti Pages vuota
  if ($query !== null && trim($query) !== '') {
    $results = $merged->search($query, [
      'words'     => false,
      'minlength' => 2,
      'fields'    => [
        'nodo','titolo','a cosa serve','obiettivo','riguarda','tag',
        'title','problema','descrizione','link','faq'
      ],
      'stopwords' => ['di','a','da','in','con','su','per','tra','fra','il','lo','la','gli','le']
    ]);
  } else {
    $results = new Pages();
  }

  return [
    'query'    => $query,
    'results'  => $results,
  ] + formDataFor();
};
