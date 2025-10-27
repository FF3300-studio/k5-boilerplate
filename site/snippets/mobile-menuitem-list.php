  <?php foreach ($items as $item) : ?>
    <?php $subMenuItems = $item->subMenu()->toStructure(); ?>
    <?php if($accordion__item == true): ?>
        <div class="accordion__item">
    <?php endif; ?>
        <?php if ($item->hasSubmenu()->isTrue() && $subMenuItems->isNotempty()) : ?>
            <div class="accordion__title">
                <?php snippet('menuitem-fake-anchor', [
                    'items' => $items,
                    'item' => $item,        
                ]) ?>
            </div>
            <?php else: ?>
            <?php snippet('menuitem-anchor', [
                'items' => $items,
                'item' => $item,        
            ]) ?>
            <?php endif ?>
        <?php if ($item->hasSubmenu()->isTrue() && $subMenuItems->isNotempty()) : ?>
            <div class="accordion__content">
                <?php snippet('menuitem-list', ['items' => $subMenuItems, 'accordion__item' => false]) ?>
            </div>
        <?php endif ?>

    
 
    <?php if($accordion__item == true): ?>
        </div>
    <?php endif; ?>

<?php endforeach ?>



