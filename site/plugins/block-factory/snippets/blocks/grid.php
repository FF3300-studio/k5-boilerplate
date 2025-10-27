<?php if ($block->grid_a()->toStructure()): ?>
  <div class="block-grid-a">
    <div class="block-grid-a-title" style="text-align: center; width: 100%;">
      <h1><?php echo $block->title(); ?></h1>
    </div>

    <?php if ($block->grid_a()->toStructure()): ?>

      <?php snippet('collection-grid',[
        'collection' => $block->collection()->toPages(),
        'category_color' => false,
      ]) ?>

    <?php endif; ?>
  </div>
<?php endif; ?>