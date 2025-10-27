
<?php snippet('header') ?>
<?php snippet('menu') ?>

<?php snippet('check_banner',['posizione' => 'sopra',]); ?>

<?php snippet('search',[]) ?> 
<!-- ?php snippet('page_navigator') ? -->

<?php snippet('check_banner',['posizione' => 'sotto',]); ?>

<?php snippet('layouts', ['layout_content' => $site->footer(), 'class' => 'footer',]); ?>
<?php snippet('footer') ?>
 