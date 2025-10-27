<?php

use Kirby\Cms\Page;

return [
  'hooks' => [

    /**
     * Alla creazione: se c'è un parent, copia subito nel child
     * i flag derivati usati dai tuoi "when"/query del Panel.
     * Firma ufficiale: function (Kirby\Cms\Page $page)
     */
    'page.create:after' => function (Page $page) {
      if ($parent = $page->parent()) {
        $parentOptions = $parent->collection_options()->value() ?? '';
        // Normalizziamo a stringhe "true"/"false" perché i blueprint confrontano stringhe
        $parentCats = $parent->collection_categories_manager_toggle()->toBool() ? 'true' : 'false';

        // Aggiorna solo se cambia davvero
        if ($page->parent_collection_options()->value() !== $parentOptions ||
            $page->parent_categories_toggle()->value()  !== $parentCats) {
          // K5: update($content, ?$languageCode = null, bool $validate = true)
          $page->update([
            'parent_collection_options' => $parentOptions,
            'parent_categories_toggle'  => $parentCats,
          ], null, false);
        }
      }
    },

    /**
     * Cambio status del parent: propaga SEMPRE ai figli (robusto contro edge-case).
     * Firma ufficiale: ($newPage, $oldPage)
     */
    'page.changeStatus:after' => function (Page $newPage, Page $oldPage) {
      $opt = $newPage->collection_options()->value() ?? '';
      $tog = $newPage->collection_categories_manager_toggle()->toBool() ? 'true' : 'false';

      foreach ($newPage->children() as $child) {
        // aggiorna solo se necessario (evita scritture inutili)
        if ($child->parent_collection_options()->value() !== $opt ||
            $child->parent_categories_toggle()->value()  !== $tog) {
          $child->update([
            'parent_collection_options' => $opt,
            'parent_categories_toggle'  => $tog,
          ], null, false);
        }
      }
    },

    /**
     * Update contenuti del parent: propaga SEMPRE ai figli (no confronto old/new).
     * Firma ufficiale: ($newPage, $oldPage)
     * IMPORTANTE: non fare update sul $newPage qui dentro (evita rientri).
     */
    'page.update:after' => function (Page $newPage, Page $oldPage) {
      $opt = $newPage->collection_options()->value() ?? '';
      $tog = $newPage->collection_categories_manager_toggle()->toBool() ? 'true' : 'false';

      foreach ($newPage->children() as $child) {
        if ($child->parent_collection_options()->value() !== $opt ||
            $child->parent_categories_toggle()->value()  !== $tog) {
          $child->update([
            'parent_collection_options' => $opt,
            'parent_categories_toggle'  => $tog,
          ], null, false);
        }
      }
    },

  ],
];
