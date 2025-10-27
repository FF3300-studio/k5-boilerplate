<?php if($results->isNotEmpty()): ?>
<div class="search-header">
    <h2 style="text-align: center!important; min-width: 100%!important; margin-bottom: 15px">Hai cercato "<?= $query ?>"</h2>
</div>
<?php else: ?>
<div class="search-header">
    <h2 style="text-align: center!important; min-width: 100%!important; margin-bottom: 15px">Cosa cerchi?</h2>
</div>
<?php endif; ?>

<div class="search-bar" 
<?php if(!$results->isNotEmpty()): ?>
    style=""
<?php endif; ?>
>
    <form>
    <input id="bar" type="search" 
    <?php if($site->ricerche_frequenti()->toStructure()->isNotEmpty()): ?>
        <?php foreach($site->ricerche_frequenti()->toStructure()->shuffle()->limit(1) as $frase): ?>
            placeholder="<?= $frase->testo() ?>…"  
        <?php endforeach; ?>
    <?php endif; ?>
    aria-label="Search" name="q" value="<?= html($query) ?>">
    <input id="button" type="submit" value="CERCA">
    <div class="relative">
        <?php if($query): ?>
            <a class="close_search" href="<?= $page->url() ?>" title="reset">X</a>
        <?php endif; ?>
    </div>
    </form>
</div>

<div class="blocks related-content-container" style="padding:0!important; margin:0!important; border: none;">
    <?php if($results->isNotEmpty()): ?>

        <?php snippet('collection-grid',[
            'collection' => $results,
            'category_color' => false,
        ]) ?>

    <?php elseif($query): ?>

        <a class="result" href="<?= $page->url() ?>" title="non ci sono risultati">
            <h2 style="text-align: center!important; min-width: 100%!important; margin-bottom: 15px">Hai cercato "<?= $query ?>", ma non abbiamo contenuti per questa chiave di ricerca.</h2>
        </a>

    <?php endif; ?>
</div>
