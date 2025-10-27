<img
  class="lazyload"
  data-sizes="auto"
  data-src="<?= $image->thumb([
      'width'   => 1280,
      'format'  => 'webp',
      'quality' => 75
    ])->url() ?>"
  data-srcset="<?= $image->srcset(
      [320, 640, 960, 1280, 1600, 1920],
      ['format' => 'webp', 'quality' => 75]
    ) ?>"
  width="<?= $image->width() ?>"
  height="<?= $image->height() ?>"
  alt="<?= html($image->alt() ?? $image->filename()) ?>"
  style="
    <?= !empty($min_height) ? 'min-height: ' . $min_height . ';' : '' ?>
    <?= !empty($min_width)  ? 'min-width: '  . $min_width  . ';' : '' ?>
    aspect-ratio: <?= $ratio ?? 'auto' ?>;
    object-fit: <?= ($contain ?? false) ? 'contain' : 'cover' ?>;
    width: 100%;
  "
>
