<!-- FILTRI -->
<?php snippet('collection-filters',[
      'logic' => 'and' // oppure 'or'
]); ?>
<!-- GRIGLIA -->
<?php snippet('collection-grid',[
    'collection' => $collection,
    'category_color' => false,
]) ?>


