<?php if($block->isNotEmpty()): ?>

<?php
  $layout = $block->layout()->value();    // Fetch layout value
  $image  = $block->image()->toFile();     // Fetch the image file object
  $title  = $block->title()->html();
  $text   = $block->text()->kti();         // Fetch the text with KirbyText

  // Calcola aspect-ratio (width/height) oppure 'auto'
  $ratio = $image
    ? ($image->width() / $image->height())
    : null;
?>

<div class="block-image-text layout-<?= $layout ?>">
  <div class="image-column">
    <?php if ($image): ?>
      <img
        class="lazyload"
        data-sizes="auto"
        data-src="<?= $image->resize(1280)
                        ->thumb(['format' => 'webp', 'quality' => 75])
                        ->url() ?>"
        data-srcset="<?= $image->srcset(
                          [320, 640, 960, 1280, 1600, 1920],
                          ['format' => 'webp', 'quality' => 75]
                        ) ?>"
        width="<?= $image->width() ?>"
        height="<?= $image->height() ?>"
        alt="<?= html($image->alt() ?? $image->filename()) ?>"
        style="
          aspect-ratio: <?= $ratio ?? 'auto' ?>;
          object-fit: cover;
          width: 100%;
        "
      >
    <?php endif ?>
  </div>
  <div class="text-column">
    <div class="text-column-title">
      <p><?= $title ?></p>
    </div>
    <div class="text-column-text">
      <?= $text ?>
    </div>
  </div>
</div>

<?php endif ?>
