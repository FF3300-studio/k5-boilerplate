
<?php snippet('header') ?>
<?php snippet('menu') ?>

<?php snippet('check_banner',['posizione' => 'sopra',]); ?>

<!--?php snippet('search') ?--> 

<div class="sede_cover">
    <?php snippet('map') ?>

    <div class="meta_informations">
        <h2><?= str_replace("-"," ",$page->title()) ?></h2>
        <p>
        <?= $page->indirizzo() ?>
        <?php if($page->mail()->isNotEmpty()): ?>
            <br><br>
            <strong>Email:</strong> <?= $page->mail() ?>
        <?php endif; ?>
        <?php if($page->tel()->isNotEmpty()): ?>
            <br>
            <strong>Tel:</strong> <?= str_replace("'", "’", str_replace("-"," ", str_replace("tel.","", str_replace("_", " ", strtolower($page->tel()))))) ?>
        <?php endif; ?>
        </p>
        <?php if($page->tel()->isNotEmpty() OR $page->mail()->isNotEmpty() ): ?>
        <div class="cta" style="margin-top: 15px;">
            <?php if($page->tel()->isNotEmpty()): ?>
            <a style="display: inline-block;" class="tags" href="tel:<?= str_replace("'", "’", str_replace("-"," ", str_replace("tel.","", str_replace("_", " ", strtolower($page->tel()))))) ?>" target="_blank" title="tel">CHIAMA LA LEGA</a>
            <?php endif; ?>

            <?php if($page->mail()->isNotEmpty()): ?>
            <a style="display: inline-block;" class="tags" href="mailto:<?= $page->mail() ?>" target="_blank" title="email">SCRIVI ALLA LEGA</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php snippet('page-sede') ?>

<?php snippet('check_banner',['posizione' => 'sotto',]); ?>

<?php snippet('layouts', ['layout_content' => $site->footer(), 'class' => 'footer',]); ?>
<?php snippet('footer') ?>
 