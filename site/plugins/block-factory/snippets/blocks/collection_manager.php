<?php
/** @var Kirby\Cms\Block $block */

use Kirby\Toolkit\Str;
use Kirby\Cms\Pages;

/* ===================================
   Helpers
=================================== */

$slugify = fn($v) => Str::slug(Str::lower(trim((string)$v ?? '')));

/**
 * Costruisce registro categorie (parent_category_manager)
 */
$getCategoryRegistry = function (?Kirby\Cms\Page $collectionPage) use ($slugify) {
  $registry = [];
  if (!$collectionPage || !$collectionPage->content()->has('parent_category_manager')) {
    return [$registry, []];
  }
  $struct = $collectionPage->parent_category_manager()->toStructure();
  foreach ($struct as $row) {
    $name  = $row->nome()?->value() ?? '';
    if ($name === '') continue;
    $slug  = $slugify($name);
    $color = $row->colore_categoria()?->value() ?? null;
    $registry[$slug] = [
      'name' => $name,
      'color' => $color,
    ];
  }
  return [$registry, array_keys($registry)];
};

/**
 * Verifica se la pagina ha la categoria attiva
 */
$childHasActiveCategory = function (Kirby\Cms\Page $page, string $activeSlug) use ($slugify) {
  if ($activeSlug === '') return true;
  if (!$page->content()->has('child_category_selector')) return false;
  $values = $page->child_category_selector()->split();
  foreach ($values as $v) {
    if ($slugify($v) === $activeSlug) return true;
  }
  return false;
};

/* ===================================
   Lettura campi block
=================================== */

$title      = $block->title()->value();
$typology   = $block->typology()->or('manuale')->value();
$layout     = $block->layout()->or('griglia')->value();
$activeSlug = $slugify($block->active_category()->value() ?? '');
$max        = (int)($block->max_number()->value() ?? 0);

$selected = $block->collection()->toPages();

/* ===================================
   Selezione items
=================================== */

$items = new Pages();
$collectionPage = null;
[$categoryRegistry, $validCatSlugs] = [[], []];

if ($typology === 'manuale') {
  $items = $selected;
} else {
  $collectionPage = $selected->first();
  if ($collectionPage) {
    [$categoryRegistry, $validCatSlugs] = $getCategoryRegistry($collectionPage);
    $pool = $collectionPage->children()->listed();
    if ($activeSlug && in_array($activeSlug, $validCatSlugs, true)) {
      $pool = $pool->filter(fn($p) => $childHasActiveCategory($p, $activeSlug));
    }
    $items = $pool;
  }
}

if ($max > 0) {
  $items = $items->limit($max);
}

if ($items->isEmpty()) return;

/* ===================================
   Render
=================================== */

$activeLabel = $categoryRegistry[$activeSlug]['name'] ?? null;
$activeColor = $categoryRegistry[$activeSlug]['color'] ?? null;
?>

<section class="cm-block cm-block--<?= esc($layout) ?>">
  <?php if (!empty($title) || $activeLabel): ?>
    <header class="cm-block__header">
      <?php if (!empty($title)): ?>
        <h3 class="cm-block__title"><?= esc($title) ?></h3>
      <?php endif; ?>
      <?php if ($activeLabel): ?>
        <span class="cm-chip"<?= $activeColor ? ' style="--chip:' . esc($activeColor) . ';"' : '' ?>>
          <?= esc($activeLabel) ?>
        </span>
      <?php endif; ?>
    </header>
  <?php endif; ?>

  <?php if ($layout === 'carosel'): ?>
    <div class="cm-carousel" role="region" aria-label="<?= esc($title ?: 'Carousel') ?>">
      <div class="cm-carousel__track" tabindex="0">
        <?php foreach ($items as $item): ?>
          <?php snippet('card-grid', [
            'item'            => $item,
            'direction'       => 'column',
            'thumb_toggle'    => true,
            'tag_toggle'      => true,
            'big'             => false,
            'category_color'  => true,
          ]) ?>
        <?php endforeach; ?>
      </div>
    </div>

  <?php else: ?>
    <div class="cm-grid" role="list">
      <?php foreach ($items as $item): ?>
        <?php snippet('card-grid', [
          'item'            => $item,
          'direction'       => 'column',
          'thumb_toggle'    => true,
          'tag_toggle'      => true,
          'big'             => false,
          'category_color'  => true,
        ]) ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
