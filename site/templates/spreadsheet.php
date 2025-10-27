<?php snippet('header') ?>
<?php snippet('menu') ?>
<?php snippet('check_banner',['posizione' => 'sopra']) ?>

<?php snippet(name: 'csv-search')?>
<?php snippet('page_cover') ?>
<?php snippet('layouts', ['layout_content' => $page->contenuto()]) ?>

<?php
use Kirby\Toolkit\Str;

// Defaults dal Panel
$limitDefault = max(1, (int)($page->page_size()->toInt() ?: 20));
$limit  = max(1, (int)(get('limit')  ?? $limitDefault));
$offset = max(0, (int)(get('offset') ?? 0));

$isSearch = isset($results) && $results instanceof Kirby\Cms\Pages && $results->isNotEmpty();

if ($isSearch) {
  $items = $results;
  if ($pagination = $results->pagination()) {
    $totalRows = (int) $pagination->total();
    $limit     = (int) $pagination->limit();
    $current   = (int) $pagination->page();
    $offset    = ($current - 1) * $limit;
  } else {
    $totalRows = (int) $results->count();
    $current   = 1 + (int) floor($offset / $limit);
  }
} else {
  $items     = $page->children();
  $totalRows = (int) $page->totalRows();
  $current   = 1 + (int) floor($offset / $limit);
}

$filterColors = $page->filterColors();

$totalPages = max(1, (int) ceil($totalRows / $limit));
$prevOffset = max(0, $offset - $limit);
$nextOffset = $offset + $limit;

$hasPrev = $offset > 0;
$hasNext = $current < $totalPages;

$window = 2;
$pages = [];
for ($p = 1; $p <= $totalPages; $p++) {
  if ($p === 1 || $p === $totalPages || ($p >= $current - $window && $p <= $current + $window)) {
    $pages[] = $p;
  }
}
$pages = array_values(array_unique($pages));

$activeFilters = $_GET['filter'] ?? [];
if (!is_array($activeFilters)) $activeFilters = [];

// ============= URL BUILDER =============
function build_url($base, $merge = []) {
  $qs = $_GET;
  if (!array_key_exists('refresh', $merge)) unset($qs['refresh']);
  if (array_key_exists('filter', $merge)) {
    $qs['filter'] = $merge['filter'];
    unset($merge['filter']);
  }
  $qs = array_replace_recursive($qs, $merge);

  $filter = function (&$arr) use (&$filter) {
    foreach ($arr as $k => $v) {
      if (is_array($v)) {
        $filter($arr[$k]);
        if ($arr[$k] === [] || $arr[$k] === null) unset($arr[$k]);
      } else {
        if ($v === null || $v === '') unset($arr[$k]);
      }
    }
  };
  $filter($qs);
  $query = http_build_query($qs);
  return $base . (empty($query) ? '' : '?' . $query);
}
?>

<main class="wrap">
  <?php
  $filterable = $page->filterableFields();
  if (!empty($filterable)):
  ?>
  <aside class="spreadsheet">
    <ul class="filter-group">
      <li>
        <a class="tag"
          style="border-radius:0.25vw;font-size:14px;font-family:'xanti';padding:3px 6px;margin-right:3px;border:1px solid black;background-color:black;color:white; padding-bottom: 2px!important;"
          href="<?= $page->url() ?>" title="tutti">Tutti</a>
      </li>

      <?php foreach ($filterable as $field): ?>
        <?php
          $values = $page->filterValues($field);
          if (empty($values)) continue;

          $color = $filterColors[$field] ?? null;
          $currentCsv = $activeFilters[$field] ?? '';
          $currentArr = array_values(array_unique(array_filter(array_map('trim', explode(',', (string)$currentCsv)))));
        ?>

        <?php foreach ($values as $val): ?>
          <?php
            $slugVal   = Str::slug($val);
            $slugArr   = array_map(fn($v) => Str::slug($v), $currentArr);
            $isActive  = in_array($slugVal, $slugArr, true);

            $newArr = $slugArr;
            if ($isActive) {
              $newArr = array_values(array_diff($newArr, [$slugVal]));
            } else {
              $newArr[] = $slugVal;
            }

            $newArr = array_values(array_unique(array_filter($newArr)));
            $newFilters = $activeFilters;
            if (empty($newArr)) {
              unset($newFilters[$field]);
            } else {
              $newFilters[$field] = implode(',', $newArr);
            }

            $params = [
              'filter' => $newFilters,
              'offset' => 0,
              'limit'  => $limit,
            ];

            $color_bg = lightenHex($color, 66);
            $baseStyle = "padding-bottom:1px!important;font-size:14px;border-radius:0.25vw;border:1px solid $color_bg;color:$color;background-color:$color_bg;";
            $activeStyle = "padding-bottom:1px!important;font-size:14px;color:black!important;background-color:#ffe000!important;border:1px solid black!important;border-radius:0.25vw;";
            $style = $isActive ? $activeStyle : $baseStyle;
          ?>
          <li>
            <a class="filter-chips" style="<?= $style ?>" href="<?= build_url($page->url(), $params) ?>">
              <?= esc($val) ?>
            </a>
          </li>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </ul>
  </aside>
  <?php endif; ?>

  <?php if ($items->isEmpty()): ?>
    <p>Nessuna voce trovata.</p>
  <?php else: ?>
    <ul class="spread-results">
      <?php foreach ($items as $child): ?>
        <li>
          <a class="spread-result" href="<?= $child->url() ?>">
            <div class="spread-block spread-block--uno">
              <p class="titolo"><?= esc($child->titolo()) ?></p>
              <?php $color = $filterColors['nodo'] ?? '#666'; ?>
              <p class="nodo" style="color:<?= $color ?>;"><?= esc($child->nodo()) ?></p>
            </div>

            <div class="spread-block spread-block--due">
              <?php if ($child->riguarda()->isNotEmpty()): ?>
                <?php foreach ($child->riguarda()->split(',') as $riguarda): ?>
                  <?php $color = $filterColors['riguarda'] ?? '#666'; $color_bg = lightenHex($color, 66); ?>
                  <p class="bg-back" style="font-size:14px;background-color:<?= $color_bg ?>;color:<?= $color ?>;">
                    <?= esc(trim($riguarda)) ?>
                  </p>
                <?php endforeach; ?>
              <?php endif; ?>

              <?php if ($child->problema()->isNotEmpty()): ?>
                <?php foreach ($child->problema()->split(',') as $problema): ?>
                  <?php $color = $filterColors['problema'] ?? '#666'; $color_bg = lightenHex($color, 66); ?>
                  <p class="bg-back" style="font-size:14px;background-color:<?= $color_bg ?>;color:<?= $color ?>;">
                    <?= esc(trim($problema)) ?>
                  </p>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <div class="spread-block spread-block--tre">
              <?php if ($child->obiettivo()->isNotEmpty()): ?>
                <p class="obiettivo"><?= esc($child->obiettivo()) ?></p>
              <?php endif; ?>
            </div>
          </a>
        </li>
      <?php endforeach ?>
    </ul>

    <?php if ($totalPages > 1): ?>
      <nav class="pagination" aria-label="Paginazione"
        style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
        <?php if ($hasPrev): ?>
          <a href="<?= build_url($page->url(), ['offset' => $prevOffset, 'limit' => $limit]) ?>">&laquo; Precedenti</a>
        <?php else: ?><span style="opacity:.4;">&laquo; Precedenti</span><?php endif; ?>

        <?php
          $lastPrinted = 0;
          foreach ($pages as $p) {
            if ($lastPrinted && $p > $lastPrinted + 1) echo '<span style="opacity:.6;">…</span>';
            $lastPrinted = $p;
            $pOffset = ($p - 1) * $limit;
            if ($p === $current) {
              echo '<strong style="padding:0 .25rem;">' . $p . '</strong>';
            } else {
              echo '<a href="' . build_url($page->url(), ['offset' => $pOffset, 'limit' => $limit]) . '">' . $p . '</a>';
            }
          }
        ?>

        <?php if ($hasNext): ?>
          <a href="<?= build_url($page->url(), ['offset' => $nextOffset, 'limit' => $limit]) ?>">Successivi &raquo;</a>
        <?php else: ?><span style="opacity:.4;">Successivi &raquo;</span><?php endif; ?>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</main>

<?php if($page->parent() !== null): ?>
  <?php $parent = $page->parent()->toPage() ?>
  <?php if ($parent && $parent->collection_toggle()->toBool()): ?>
    <?php snippet('page_related_list') ?>
  <?php endif; ?>
<?php endif; ?>

<?php snippet('check_banner',['posizione' => 'sotto']) ?>
<?php snippet('layouts', ['layout_content' => $site->footer(), 'class' => 'footer']) ?>
<?php snippet('footer') ?>
