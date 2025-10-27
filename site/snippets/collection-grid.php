<?php
use Kirby\Toolkit\Str;

// Attiva filtri se presenti
$activeCategories = param('category') ? array_map('Str::slug', explode('+', param('category'))) : [];

// Determina la collection da usare: se è definita `$collection`, usala; altrimenti fallback su `$filteredCollection` dal controller
$baseCollection = $collection ?? $filteredCollection ?? new Collection([]);

if (!empty($activeCategories)) {
    $filtered = $baseCollection->filter(function ($item) use ($activeCategories) {
        $itemCategories = array_map('Str::slug', $item->child_category_selector()->split());
        return count(array_intersect($activeCategories, $itemCategories)) > 0; // logica OR
    });
} else {
    $filtered = $baseCollection;
}
?>
<?php if ($filtered->isNotEmpty()): ?>
    <?php $card_counter = 0; ?>
    <?php foreach ($filtered->sortBy('appuntamenti', 'desc') as $child): ?>
        <?php $card_counter++; ?>
    <?php endforeach; ?>
    <?php if($card_counter <= 2 AND $card_counter != 0): ?>
        <style>
            .single-cards {
                margin: 15px auto!important;
            }
        </style>
    <?php endif; ?>

    <div class="block-grid-a-list">        
        <?php foreach ($filtered->sortBy('appuntamenti', 'desc') as $child): ?>
            <?php snippet('card-grid', [
                'item' => $child,
                'thumb_toggle' => false, 
                'tag_toggle' => true,
                'direction' => 'column',
                'category_color' => $category_color ?? false,
            ]) ?> 
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <h2 style="text-align: center; width: 100%; margin: 90px;">
        <strong>Siamo al lavoro per preparare nuovi workshop ed eventi,<br> tornate a trovarci tra qualche giorno <br>e seguiteci sui social per restare informat3.</strong>
    </h2>
<?php endif; ?>

<!-- PAGINATOR -->

<?php
if (($category_color ?? false) === true && $page->parent_category_manager()->isNotEmpty()) {
    $categories = $page->parent_category_manager()->toStructure();

    echo "<style>";
    foreach ($categories as $category) {
        $nome = Str::slug($category->nome());
        $colore = $category->colore_categoria();

        echo "
        .card-master.{$nome} {
            border-color: {$colore};
            transition: none;
        }
        .card-master.{$nome} * {
            color: {$colore};
            transition: none;
        }
        .card-master.{$nome} span.tag {
            color: white;
            transition: none;
        }
        .card-master:hover.{$nome} {
            border-color: inherit;
            transition: none;
        }
        .card-master:hover.{$nome} * {
            color: inherit;
            transition: none;
        }
        .card-master.{$nome} span.tag {
            background-color: {$colore};
            transition: none;
        }
        ";
    }
    echo "</style>";
}
?>
