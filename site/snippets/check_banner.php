<?php if($site->banner_manager()->isNotEmpty()): ?>
    <?php foreach($site->banner_manager()->toStructure() as $item): ?>
        <?php foreach($item->pagine()->toPages() as $pagina_attiva): ?>
            <?php if($page == $pagina_attiva AND $item->on_off() == "true"): ?>
                <?php if($item->posizione() == $posizione): ?>
                    <?php snippet('layouts', [
                        'layout_content' => $item->contenuto(),
                        'class' => 'banner',
                        'custom_style' => $item->custom_css()
                    ]); ?>
                <?php endif; ?> 
            <?php endif; ?>           
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php endif; ?>