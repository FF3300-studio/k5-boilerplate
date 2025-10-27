
<?php snippet('header') ?>

<?php snippet('ecosystem') ?>

<?php snippet('menu') ?>

<?php snippet('breadcrumbs') ?>

<?php snippet('title') ?>

<?php snippet('layouts', [
    'layout_content' => $page->contenuto(),
]); ?>

<?php snippet('luoghi') ?>

<?php snippet('layouts', [
    'layout_content' => $site->footer(),
    'class' => 'footer',
]); ?>

<?php snippet('footer') ?>
