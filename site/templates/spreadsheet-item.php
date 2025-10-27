<?php snippet('header') ?>
<?php snippet('menu') ?>
<?php snippet('check_banner', ['posizione' => 'sopra']) ?>

<?php
use Kirby\Toolkit\Str;

// ================== CONFIG & HELPERS ==================

// Accessor semplice dei campi
$val = function (string $key) use ($page) {
  return $page->content()->get($key);
};

// Parent e colori filtri (mappa alias => colore)
$parent       = $page->parent(); // Kirby\Cms\Page|null
$filterColors = $page->parent()?->filterColors() ?? [];

// Limite del parent (coerente con la lista)
$parentLimitDefault = max(1, (int)($parent?->page_size()?->toInt() ?: 20));

// URL del parent
$parentUrl = $parent?->url() ?? '#';

/**
 * Restituisce l'alias corretto da usare nei filtri dell'URL,
 * cercandolo nel parent->alias_map() tra i campi marcati come filter=true.
 */
$getAliasKey = function (string $label) use ($parent): string {
  $fallback = Str::slug($label);
  if (!$parent || $parent->alias_map()->isEmpty()) return $fallback;

  $want = Str::slug($label);
  foreach ($parent->alias_map()->toStructure() as $row) {
    $header   = Str::slug((string)$row->header()->value());
    $alias    = Str::slug((string)$row->alias()->value());
    $isFilter = $row->filter()->toBool();

    if (!$isFilter) continue;
    if ($header === $want || $alias === $want) {
      return $alias;
    }
  }
  return $fallback;
};

/**
 * Costruisce un URL completo per il filtro, slugificando il valore per evitare problemi con accenti.
 * Usa lo stesso algoritmo di Kirby\Toolkit\Str::slug() del model.
 */
$parentFilterUrl = function (string $fieldLabel, string $value) use ($parentUrl, $parentLimitDefault, $getAliasKey) {
  if ($parentUrl === '#') return '#';
  $aliasKey = $getAliasKey($fieldLabel);
  $slugValue = Str::slug(trim($value)); // ✅ SLUGIFY (garantisce coerenza con model)
  $qs = [
    'filter' => [ $aliasKey => $slugValue ],
    'offset' => 0,
    'limit'  => $parentLimitDefault,
  ];
  return $parentUrl . '?' . http_build_query($qs);
};

// Titolo fallback dal parent
$titleFallback = ($parent && $parent->title_fallback()->isNotEmpty())
  ? $parent->title_fallback()->value()
  : 'Provvisoriamente senza titolo';

// ================== COSTRUZIONE DELL’ELENCO CAMPI ==================
$excluded = ['title', 'titolo', 'riguarda', 'obiettivo', 'problema', 'nodo', 'tag','faq'];

// 1) Leggo la mappa alias dal parent
$mappedAliases = [];
if ($parent && $parent->alias_map()->isNotEmpty()) {
  foreach ($parent->alias_map()->toStructure() as $row) {
    $alias = trim($row->alias()->value());
    if ($alias !== '') {
      $mappedAliases[] = $alias;
    }
  }
}
$mappedAliases = array_values(array_diff($mappedAliases, $excluded));

// 2) Toggle "include_only"
$includeOnly = $parent ? $parent->include_only()->toBool() : false;

// 3) Tutti i campi presenti nell’item
$allFields = array_keys($page->content()->toArray());

// 4-5) Calcolo ordine finale
if ($includeOnly) {
  $fieldsOrder = $mappedAliases;
} else {
  $unmapped    = array_values(array_diff($allFields, array_merge($mappedAliases, $excluded)));
  $fieldsOrder = array_merge($mappedAliases, $unmapped);
}

// 6) Filtro + dedup
$fieldsOrder = array_values(array_diff($fieldsOrder, $excluded));
if (empty($fieldsOrder)) {
  $fieldsOrder = array_values(array_diff($allFields, $excluded));
}
$fieldsOrder = array_values(array_unique($fieldsOrder));
?>

<article class="wrap">
  <header class="spread-result nohover" style="margin-bottom:0; font-size: 200%;">
    <!-- Titolo + nodo -->
    <div class="spread-block spread-block--uno">
      <p class="titolo"><?= esc($page->titolo()->or($titleFallback)) ?></p>
      <?php $colorNodo = $filterColors['nodo'] ?? '#666'; ?>
      <p class="nodo" style="color:<?= $colorNodo ?>;"><?= esc($page->nodo()) ?></p>
    </div>

    <!-- Obiettivo -->
    <div class="spread-block spread-block--tre">
      <?php if ($page->obiettivo()->isNotEmpty()): ?>
        <p class="obiettivo"><?= esc($page->obiettivo()) ?></p>
      <?php endif; ?>
    </div>
    
    <!-- Chip: riguarda + problema -->
    <div class="spread-block spread-block--due">
      <?php if ($page->riguarda()->isNotEmpty()): ?>
        <?php foreach ($page->riguarda()->split(',') as $riguarda): ?>
          <?php
            $riguarda = trim($riguarda);
            if ($riguarda === '') continue;
            $color    = $filterColors['riguarda'] ?? '#666';
            $color_bg = lightenHex($color, 66);
            $href     = $parentFilterUrl('riguarda', $riguarda);
          ?>
          <a class="filter-chips"
             href="<?= $href ?>"
             style="line-height:135%!important; display:inline-block; text-decoration:none; font-size:14px; background-color:<?= $color_bg ?>; color:<?= $color ?>;">
            <?= esc($riguarda) ?>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if ($page->problema()->isNotEmpty()): ?>
        <?php foreach ($page->problema()->split(',') as $problema): ?>
          <?php
            $problema = trim($problema);
            if ($problema === '') continue;
            $color    = $filterColors['problema'] ?? '#666';
            $color_bg = lightenHex($color, 66);
            $href     = $parentFilterUrl('problema', $problema);
          ?>
          <a class="filter-chips"
             href="<?= $href ?>"
             style="line-height:135%!important; display:inline-block; text-decoration:none; font-size:14px; background-color:<?= $color_bg ?>; color:<?= $color ?>;">
            <?= esc($problema) ?>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </header>

  <section class="csv-content-page">
    <?php $filed_count = 0; ?>
    <?php foreach ($fieldsOrder as $key): ?>
      <?php if ($filed_count++ > 0): ?>
        <hr style="margin:12px auto;">
      <?php endif; ?>
      
      <?php $field = $val($key); ?>
      
      <?php if ($field && $field->isNotEmpty()): ?>
        <div class="field field--<?= esc($key) ?>">
          <h3 class="field-name" style="margin-bottom:.25em; font-weight:600;">
            <?= str_replace('_', ' ', ucfirst(esc($key))) ?>
          </h3>
          <div class="field-content">
            <?= $field->kt() ?>
          </div>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </section>

  <!-- Tag -->
  <div class="spread-result">
    <div class="spread-block spread-block--due">
      <?php if ($page->tag()->isNotEmpty()): ?>
        <?php foreach ($page->tag()->split(',') as $tag): ?>
          <?php
            $tag = trim($tag);
            if ($tag === '') continue;
            $color    = $filterColors['tag'] ?? '#666';
            $color_bg = lightenHex($color, 66);
            $href     = $parentFilterUrl('tag', $tag);
          ?>
          <a class="filter-chips"
             href="<?= $href ?>"
             style="line-height:135%!important; display:inline-block; text-decoration:none; font-size:14px; color:<?= $color ?>; border:1px solid <?= $color ?>!important;">
            <?= esc($tag) ?>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- FAQ (opzionale, nascoste) -->
  <div class="spread-result" style="display:none;">
    <div class="spread-block spread-block--due">
      <?php if ($page->faq()->isNotEmpty()): ?>
        <?php foreach ($page->faq()->split(',') as $faq): ?>
          <?php
            $faq = trim($faq);
            if ($faq === '') continue;
            $color    = $filterColors['faq'] ?? '#666';
            $color_bg = lightenHex($color, 66);
            $href     = $parentFilterUrl('faq', $faq);
          ?>
          <a class="filter-chips"
             href="<?= $href ?>"
             style="line-height:135%!important; display:inline-block; text-decoration:none; font-size:14px; color:<?= $color ?>; border:1px solid <?= $color ?>!important;">
            <?= esc($faq) ?>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</article>

<?php if ($parent && $parent->collection_toggle()->toBool()): ?>
  <?php snippet('page_related_list') ?>
<?php endif; ?>

<?php snippet('check_banner', ['posizione' => 'sotto']) ?>
<?php snippet('layouts', ['layout_content' => $site->footer(), 'class' => 'footer']) ?>
<?php snippet('footer') ?>
