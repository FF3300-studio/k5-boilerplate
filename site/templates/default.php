
<?php snippet('header') ?>
<?php snippet('menu') ?>

<?php snippet('check_banner',['posizione' => 'sopra',]); ?>

<!--?php snippet('search') ?--> 
<!-- ?php snippet('page_navigator') ? -->

<?php snippet('page_cover') ?>

<?php snippet('layouts', ['layout_content' => $page->contenuto(),]); ?>

<?php snippet('check_collection') ?>

<?php if($page->parent() !== NULL) :?>
    <?php $parent = $page->parent()->toPage() ?>
    <?php if($parent->collection_toggle() == true): ?>
        <?php snippet('page_related_list'); ?>
    <?php endif; ?>
<?php endif; ?>

<?php snippet('check_banner',['posizione' => 'sotto',]); ?>

<?php snippet('layouts', ['layout_content' => $site->footer(), 'class' => 'footer',]); ?>
<?php snippet('footer') ?>
 