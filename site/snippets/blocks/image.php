<?php

/** @var \Kirby\Cms\Block $block */
$alt     = $block->alt();
$caption = $block->caption();
$crop    = $block->crop()->isTrue();
$link    = $block->link();
$ratio   = $block->ratio()->or('auto');
$src     = null;
$max_width = $block->max_width();
$max_height = $block->max_height();
$min_width = $block->min_width();
$min_height = $block->min_height();

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
                    style="
                    <?php if($ratio !== NULL OR $ratio !== ''): ?>aspect-ratio: <?= $ratio ?>;<?php else: ?><?php endif; ?> 
                    <?php if($max_width !== NULL OR $max_width !== ''): ?>object-fit: cover; max-width: <?= $max_width ?>; <?php else: ?>max-width: fit-content;<?php endif; ?> 
                    <?php if($min_width !== NULL OR $min_width !== ''): ?>min-width: <?= $min_width ?>;<?php else: ?>min-width: fit-content;<?php endif; ?> 
                    <?php if($max_height !== NULL OR $max_height !== ''): ?>max-height: <?= $max_height ?>;<?php else: ?>max-height: fit-content;<?php endif; ?>
                    <?php if($min_height !== NULL OR $min_height !== ''): ?>min-height: <?= $min_height ?>;<?php else: ?>min-height: fit-content;<?php endif; ?> 
                    "
                    src="<?= $src ?>"
                    data-src="<?= $src ?>" 
                    alt="<?= $block->alt()->or($image->alt()) ?>">
        <?php else: ?>
            <img    class="lazyload"
                    style="
                    <?php if($ratio !== NULL OR $ratio !== ''): ?>aspect-ratio: <?= $ratio ?>;<?php else: ?><?php endif; ?> 
                    <?php if($max_width !== NULL OR $max_width !== ''): ?>object-fit: cover; max-width: <?= $max_width ?>; <?php else: ?>max-width: fit-content;<?php endif; ?> 
                    <?php if($min_width !== NULL OR $min_width !== ''): ?>min-width: <?= $min_width ?>;<?php else: ?>min-width: fit-content;<?php endif; ?> 
                    <?php if($max_height !== NULL OR $max_height !== ''): ?>max-height: <?= $max_height ?>;<?php else: ?>max-height: fit-content;<?php endif; ?>
                    <?php if($min_height !== NULL OR $min_height !== ''): ?>min-height: <?= $min_height ?>;<?php else: ?>min-height: fit-content;<?php endif; ?> 
                    "
                    data-src="<?= $image->thumb()->url() ?>" 
                    alt="<?= $block->alt()->or($image->alt()) ?>">
        <?php endif; ?>
        </a>
    <?php else: ?>
        <?php if($image->extension() == 'gif'): ?>
            <img    class="lazyload"
                    src="<?= $src ?>"
                    data-src="<?= $src ?>" 
                    style="
                    <?php if($ratio !== NULL OR $ratio !== ''): ?>aspect-ratio: <?= $ratio ?>;<?php else: ?><?php endif; ?> 
                    <?php if($max_width !== NULL OR $max_width !== ''): ?>object-fit: cover; max-width: <?= $max_width ?>; <?php else: ?>max-width: fit-content;<?php endif; ?> 
                    <?php if($min_width !== NULL OR $min_width !== ''): ?>min-width: <?= $min_width ?>;<?php else: ?>min-width: fit-content;<?php endif; ?> 
                    <?php if($max_height !== NULL OR $max_height !== ''): ?>max-height: <?= $max_height ?>;<?php else: ?>max-height: fit-content;<?php endif; ?>
                    <?php if($min_height !== NULL OR $min_height !== ''): ?>min-height: <?= $min_height ?>;<?php else: ?>min-height: fit-content;<?php endif; ?> 
                    "
                    alt="<?= $block->alt()->or($image->alt()) ?>">
        <?php else: ?>
            <img    class="lazyload"
                    src="<?= $src ?>"
                    data-src="<?= $src ?>" 
                    style="
                    <?php if($ratio !== NULL OR $ratio !== ''): ?>aspect-ratio: <?= $ratio ?>;<?php else: ?><?php endif; ?> 
                    <?php if($max_width !== NULL OR $max_width !== ''): ?>object-fit: cover; max-width: <?= $max_width ?>; <?php else: ?>max-width: fit-content;<?php endif; ?> 
                    <?php if($min_width !== NULL OR $min_width !== ''): ?>min-width: <?= $min_width ?>;<?php else: ?>min-width: fit-content;<?php endif; ?> 
                    <?php if($max_height !== NULL OR $max_height !== ''): ?>max-height: <?= $max_height ?>;<?php else: ?>max-height: fit-content;<?php endif; ?>
                    <?php if($min_height !== NULL OR $min_height !== ''): ?>min-height: <?= $min_height ?>;<?php else: ?>min-height: fit-content;<?php endif; ?> 
                    "
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
