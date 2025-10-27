<?php

/** @var \Kirby\Cms\Block $block */
$alt     = $block->alt();
$caption = $block->caption();
$crop    = $block->crop()->isTrue();
$link    = $block->link();
$ratio   = $block->ratio()->or('auto');
$src     = null;
$max_width = $block->max_width();

if ($block->location() == 'web') {
    $src = $block->src()->esc();
} elseif ($image = $block->image()->toFile()) {
    $alt = $alt ?? $image->alt();
    $src = $image->url();
} 

?>
<?php if ($src): ?>
<figure style="aspect-ratio: <?= $ratio ?>" <?= Html::attr(['data-ratio' => $ratio, 'data-crop' => $crop], null, ' ') ?>>
    <?php if ($link->isNotEmpty()): ?>
        <a href="<?= $link->url() ?>" title="<?= $block->id() ?>">
        <?php if($image->extension() == 'gif'): ?>
            <img    class="lazyload"
                    style="aspect-ratio: <?= $ratio ?>; object-fit: cover; max-width: <?= $max_width ?>px;"
                    src="<?= $src ?>"
                    data-src="<?= $src ?>" 
                    alt="<?= $block->alt()->or($image->alt()) ?>">
        <?php else: ?>
            <img    class="lazyload"
                    style="aspect-ratio: <?= $ratio ?>; object-fit: cover; max-width: <?= $max_width ?>px;"
                    data-src="<?= $image->thumb()->url() ?>" 
                    alt="<?= $block->alt()->or($image->alt()) ?>">
        <?php endif; ?>
        </a>
    <?php else: ?>
        <?php if($image->extension() == 'gif'): ?>
            <img    class="lazyload"
                    src="<?= $src ?>"
                    data-src="<?= $src ?>" 
                    style="aspect-ratio: <?= $ratio ?>; object-fit: cover; max-width: <?= $max_width ?>px;"
                    alt="<?= $block->alt()->or($image->alt()) ?>">
        <?php else: ?>
            <img    class="lazyload"
                    src="<?= $src ?>"
                    data-src="<?= $src ?>" 
                    style="aspect-ratio: <?= $ratio ?>; object-fit: cover; max-width: <?= $max_width ?>px;"
                    alt="<?= $block->alt()->or($image->alt()) ?>">
        <?php endif; ?>
    <?php endif ?>

  <?php if ($caption->isNotEmpty()): ?>
  <figcaption>
    <?= $caption ?>
  </figcaption>
  <?php endif ?>
</figure>
<?php endif ?>
