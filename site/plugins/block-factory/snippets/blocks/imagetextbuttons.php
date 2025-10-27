<?php if($block->isNotEmpty()): ?>

<?php
  /** @var \Kirby\Cms\File|null $image */
  $image = $block->image()->toFile();
  $title = $block->title()->value();
  $text  = $block->text()->kt(); // KirbyText

  // Colore di sfondo
  $class = $block->color()->isNotEmpty()
    ? 'app-color-' . $block->color()->value()
    : 'no-app-color';

  // Calcola aspect-ratio
  $ratio = $image
    ? ($image->width() / $image->height())
    : null;
?>

<div class="block-app <?= $class ?>">
  <div
    class="block-app-inner"
    style="<?php if($block->custom_css()->isNotEmpty()): ?><?= $block->custom_css() ?><?php endif ?>"
  >
    <div class="image">
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
    <div class="info">
      <div class="text">
        <h2><?= $title ?></h2>
        <?= $text ?>
      </div>
      <div class="buttons">
        <?php snippet('cta', [
          'block'     => $block,
          'cta_items' => $block->cta(),
        ]) ?>
      </div>
    </div>
  </div>
</div>

<?php endif ?>
