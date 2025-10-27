<?php if ($block->cards()->isNotEmpty()): ?>
  <?php $layout = $block->layout()->value(); // Fetch layout value ?>
  <div class="block-cards">
    <div class="block-cards-title">
      <h1><?php echo $block->title()->html(); ?></h1>
    </div>
    <?php if ($layout === 'slider') { ?>
      <?php snippet('collection-slider', [
        'collection' => $block->collection()->toPages(),
      ]) ?>
    <?php } else { ?>
      <?php snippet('collection-grid',[
        'collection' => $block->collection()->toPages(),
        'category_color' => false,
      ]) ?>
    <?php } ?>
  </div>
<?php endif; ?>