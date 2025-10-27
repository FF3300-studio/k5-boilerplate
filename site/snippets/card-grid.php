<div class="single-cards col-lg-4 col-sm-12 col-12">
  <a
    href="<?= $item->url() ?>"
    class="card-master <?php if ($category_color): ?><?= strtolower($item->child_category_selector()) ?><?php endif ?>"
    style="flex-direction: <?= $direction ?>"
  >
    <div class="cards-thumbnail">
      <?php if ($thumb_toggle && $item->thumbnail()->isNotEmpty()): ?>
        <?php
          $image = $item->thumbnail()->toFile();
          $ratio = $image->width() / $image->height();
        ?>
        <div class="card-image-container">
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
              <?php if (isset($min_height) && $min_height !== ''): ?>min-height: <?= $min_height ?>;<?php endif ?>
              <?php if (isset($min_width)  && $min_width  !== ''): ?>min-width: <?= $min_width  ?>;<?php endif ?>
              <?php if (isset($max_height) && $max_height !== ''): ?>max-height: <?= $max_height ?>;<?php endif ?>
              <?php if (isset($max_width)  && $max_width  !== ''): ?>max-width: <?= $max_width  ?>;<?php endif ?>
              aspect-ratio: <?= $ratio ?>;
              object-fit: <?= ($contain ?? false) ? 'contain' : 'cover' ?>;
              width: 100%;
            "
          >
        </div>
      <?php endif ?>
    </div>

    <?php snippet('card-info', [
      'item'       => $item,
      'direction'  => $direction,
      'tag_toggle' => $tag_toggle,
      'big'        => $big ?? true,
    ]) ?>
  </a>
</div>
