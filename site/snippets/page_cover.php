<?php if (!$page->parents()->toArray()): ?>
  <!-- Nessuna azione se non ci sono genitori -->
<?php else: ?>
  <div class="cover-container" style="flex-direction: row-reverse"> <!-- Apertura div cover-container -->
      <?php if ($page->parent()->collection_options() == 'map'): ?>
        <div class="cover-first-element"> <!-- Apertura div cover-first-element -->
          <?php snippet('page-cover-map', [
            'collection_parent' => $page->parent(),
            'collection' => $page->parent()->children()->filterBy('id', $page->id())
          ]) ?>
        </div>
      <?php elseif ($thumbnail = $page->thumbnail()->toFile()): ?>
        <div class="cover-first-element"> <!-- Apertura div cover-first-element -->
          <img src="<?= $thumbnail->url() ?>" alt="<?= $page->title() ?>">
          <?php $image = $thumbnail->toFile(); ?>
          <?php snippet('image',[
              'image' => $image, 
          ]) ?>
        </div> <!-- Chiusura div cover-first-element -->
      <?php endif; ?>

    <div <?php if(!$page->thumbnail()->isNotEmpty()): ?> style="flex-grow: 1"<?php endif; ?> class="page-informations <?= strtolower($page->child_category_selector()) ?>"> <!-- Apertura div page-informations -->
      <?php snippet('card-info',[
              'item' => $page,
              'direction' => 'column',
              'tag_toggle' => true,
          ])?>
    </div> <!-- Chiusura div page-informations -->

  </div> <!-- Chiusura div cover-container -->

  <?php

  // Controlla se il parent ha il field `parent_category_manager`
  if ($page->parent() !== NULL AND $page->parent()->parent_category_manager()->isNotEmpty()) {
    // Recupera la struttura delle categorie
    $categories = $page->parent()->parent_category_manager()->toStructure();

    echo "<style>";
    foreach ($categories as $category) {
        $nome = Str::slug($category->nome()); // Crea una classe valida CSS-friendly
        $colore = $category->colore_categoria();

        // Stampa la classe CSS
        echo "
        .page-informations.{$nome} {
            background-color: {$colore};
            transition: none;
        }
        ";
    }
    echo "</style>";
  }
  ?>
<?php endif; ?>

