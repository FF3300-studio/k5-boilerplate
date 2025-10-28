<?php if ($block->grid()->toStructure()): ?>
  <div class="block-grid-a">
    <?php if($block->title()->isNotEmpty()): ?>
      <div class="block-grid-a-title" style="text-align: center; width: 100%;">
        <h1><?php echo $block->title(); ?></h1>
      </div>
    <?php endif; ?>
    <?php if ($block->grid()->toStructure()): ?>
      <?php snippet('collection-grid',[
        'collection' => $block->collection()->toPages(),
        'category_color' => false,
      ]) ?>
    <?php endif; ?>
  </div>
<?php endif; ?>