<?php if($item->title()->isNotEmpty() ): ?>

        <?php snippet('freaky-title',[
            'input' => $item->title(),
            'big' => true,
        ]); ?>
        
<?php endif; ?>
<?php if($item->descrizione()->isNotEmpty() ): ?>
    <div class="slide-bodycopy"><?= $item->descrizione()->kirbytext() ?></div>
<?php endif; ?>
<?php if($item->cta()->isNotEmpty()): ?>
    <?php snippet('cta',[
        'block' => $block,
        'cta_items' => $item->cta(),
    ]) ?>
<?php endif; ?>
