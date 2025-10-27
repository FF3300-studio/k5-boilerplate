<?php
use Kirby\Toolkit\Str;

$filterLogic = $logic ?? 'or'; // Default 'or' se non viene passato dallo snippet
$check_reset = false;
if ($allCategories->isNotEmpty() && $collection->isNotEmpty()): ?>
<div class="container-collection">
    <div class="collection-filters" id="filters-bar">
        <div class="container-categories">
            <?php foreach ($gruppi as $gruppo): ?>
                <fieldset id="<?= $gruppo ?>" class="filters-container-fieldset control-group" data-filter-group="categories" data-logic="<?= $filterLogic ?>">
                    
                    <?php if (!empty($activeCategories) AND $check_reset == false): ?>
                        <a href="<?= $page->url() ?>" class="all-filter single-filter control white">TUTTI</a>
                        <?php $check_reset = true; ?>
                    <?php endif; ?>

                    <?php foreach ($filteredCategories as $category): ?>
                        <?php if ($gruppo == $category->gruppo()): ?>
                            <?php
                                $slug = Str::slug($category->nome());
                                $newActive = $activeCategories;

                                // Toggle logica: aggiungi o rimuovi categoria
                                if (in_array($slug, $activeCategories)) {
                                    $newActive = array_diff($activeCategories, [$slug]);
                                } else {
                                    $newActive[] = $slug;
                                }

                                $newParam = implode('+', $newActive);
                                $url = $page->url();
                                if ($newParam) {
                                    $url .= '/category:' . $newParam . '/logic:' . $filterLogic;
                                }
                            ?>
                            <a href="<?= $url ?>"
                               class="single-filter control <?= $slug ?> <?php if (in_array($slug, $activeCategories)): ?>active<?php endif; ?>">
                                <?= $category->nome(); ?>
                            </a>
                            <?php $filter_counter++; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </fieldset>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
$categories = $page->parent_category_manager()->toStructure();

echo "<style>";
foreach ($categories as $category) {
    $nome = Str::slug($category->nome());
    $colore = $category->colore_categoria();

    echo "
    .single-filter.{$nome} {
        background-color: {$colore}!important;
        transition: none;
    }

    .single-filter:hover.{$nome} {
        background-color: {$colore}!important;
        transition: none;
        outline: 2px solid black!important;
    }
    ";
}
echo "</style>";
?>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const active = document.querySelector('.single-filter.active');
    const filterBar = document.getElementById('filters-bar');

    // Scroll verticale alla barra filtri
    if (active && filterBar) {
      filterBar.scrollIntoView({ behavior: "smooth", block: "start" });
    }

    // Scroll orizzontale del gruppo contenente il filtro attivo
    if (active) {
      const parentGroup = active.closest('.control-group');
      if (parentGroup && parentGroup.scrollLeft !== undefined) {
        const offsetLeft = active.offsetLeft - parentGroup.offsetWidth / 2 + active.offsetWidth / 2;
        parentGroup.scrollTo({ left: offsetLeft, behavior: 'smooth' });
      }
    }
  });
</script>

<?php endif; ?>
