
<?php snippet('header') ?>

<?php snippet('ecosystem') ?>

<?php snippet('menu') ?>

<?php snippet('breadcrumbs') ?>

<?php snippet('title') ?>

<div class="single-page-container pass-content">
    <?php if ($page->colore_pass()->isNotEmpty()) {
        $class = 'pass-color-' . $page->colore_pass()->value();
    } else {
        $class = 'no-pass-color';
    } ?>
    <div class="row">
        <div class="col-lg-4 col-12 info-column">
            <?php if ($image = $page->immagine()->toFile()): ?>
                <div class="pass-image <?= $class ?>">
                    <img src="<?= $image->url() ?>" alt="Immagine di copertina">
                </div>
            <?php endif; ?>
            <div class="pass-meta pass-meta-desktop">
                <?php $data_inizio = $page->data_inizio(); ?>
                <?php if ($data_inizio->isNotEmpty()): ?>
                    <div class="pass-info">
                        <?php $data_fine = $page->data_fine(); ?>
                        <div class="single-info">
                            <div class="info-title">
                                <p>Data inizio/fine</p>
                            </div>
                            <div class="info-content">
                                <p><?php if ($data_fine->isNotEmpty()): ?>Dal <?php endif; ?><?= $data_inizio->toDate('d/m/Y') ?><?php if ($data_fine->isNotEmpty()): ?> al <?= $data_fine->toDate('d/m/Y') ?><?php endif; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php $ingressi = $page->ingressi(); ?>
                <?php if ($ingressi->isNotEmpty()): ?>
                    <div class="pass-info">
                        <div class="single-info">
                            <div class="info-title">
                                <p>Numero ingressi</p>
                            </div>
                            <div class="info-content">
                                <p><?= $ingressi ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php $prezzo_digitale = $page->prezzo_digitale(); ?>
                <?php if ($prezzo_digitale->isNotEmpty()): ?>
                    <div class="pass-info">
                        <div class="single-info">
                            <div class="info-title">
                                <p>Prezzo pass digitale</p>
                            </div>
                            <div class="info-content">
                                <p>€<?= $prezzo_digitale ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php $prezzo_fisico = $page->prezzo_fisico(); ?>
                <?php if ($prezzo_fisico->isNotEmpty()): ?>
                    <div class="pass-info">
                        <div class="single-info">
                            <div class="info-title">
                                <p>Prezzo pass fisico</p>
                            </div>
                            <div class="info-content">
                                <p>€<?= $prezzo_fisico ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php $durata = $page->durata(); ?>
                <?php if ($durata->isNotEmpty()): ?>
                    <div class="pass-info">
                        <div class="single-info">
                            <div class="info-title">
                                <p>Durata</p>
                            </div>
                            <div class="info-content">
                                <p><?= $durata ?> gg.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php $trasporti = $page->trasporti(); ?>
                <?php if ($trasporti->isNotEmpty()): ?>
                    <div class="pass-info">
                        <div class="single-info">
                            <div class="info-title">
                                <p>Trasporti</p>
                            </div>
                            <div class="info-content">
                                <p><?= $trasporti ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php $app_required = $page->app()->isTrue(); ?>
                <?php if ($app_required): ?>
                    <div class="pass-info">
                        <div class="single-info">
                            <div class="info-title">
                                <p>App</p>
                            </div>
                            <div class="info-content">
                                <p>Sì</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-8 col-12">
            <div class="descrizione-pass">
                <?= $page->descrizione_pass()->kt() ?>
            </div>
            <?php snippet('layouts', [
                'layout_content' => $page->contenuto(),
            ]); ?>
            <?php $luoghi = $page->luoghi()->toPages();
            if ($luoghi->isNotEmpty()): ?>
                <div class="luoghi-inclusi">
                    <a class="collapse-link <?= $class ?>" data-toggle="collapse" href="#collapse-luoghi-inclusi" role="button" aria-expanded="false" aria-controls="collapse-luoghi-inclusi">Luoghi inclusi</a>
                    <div class="collapse" id="collapse-luoghi-inclusi">
                        <div class="collapse-inner">
                            <?php foreach ($luoghi as $luogo): ?>
                                <div class="single-luogo <?= $class ?>">
                                    <a href="<?= $luogo->url() ?>"><p><?= $luogo->title() ?></p></a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php $luoghi_sconto = $page->luoghi_sconto()->toPages();
            if ($luoghi_sconto->isNotEmpty()): ?>
                <div class="luoghi-sconto">
                    <a class="collapse-link <?= $class ?>" data-toggle="collapse" href="#collapse-luoghi-sconto" role="button" aria-expanded="false" aria-controls="collapse-luoghi-sconto">Luoghi in sconto</a>
                    <div class="collapse" id="collapse-luoghi-sconto">
                        <div class="collapse-inner">
                            <?php foreach ($luoghi_sconto as $luogo_sconto): ?>
                                <div class="single-luogo <?= $class ?>">
                                    <a href="<?= $luogo_sconto->url() ?>"><p><?= $luogo->title() ?></p></a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-lg-12 col-12">
            <div class="pass-meta pass-meta-mobile">
                <?php $data_inizio = $page->data_inizio(); ?>
                <?php if ($data_inizio->isNotEmpty()): ?>
                    <div class="pass-info">
                        <?php $data_fine = $page->data_fine(); ?>
                        <div class="single-info">
                            <div class="info-title">
                                <p>Data inizio/fine</p>
                            </div>
                            <div class="info-content">
                                <p><?php if ($data_fine->isNotEmpty()): ?>Dal <?php endif; ?><?= $data_inizio->toDate('d/m/Y') ?><?php if ($data_fine->isNotEmpty()): ?> al <?= $data_fine->toDate('d/m/Y') ?><?php endif; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php $ingressi = $page->ingressi(); ?>
                <?php if ($ingressi->isNotEmpty()): ?>
                    <div class="pass-info">
                        <div class="single-info">
                            <div class="info-title">
                                <p>Numero ingressi</p>
                            </div>
                            <div class="info-content">
                                <p><?= $ingressi ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php $prezzo_digitale = $page->prezzo_digitale(); ?>
                <?php if ($prezzo_digitale->isNotEmpty()): ?>
                    <div class="pass-info">
                        <div class="single-info">
                            <div class="info-title">
                                <p>Prezzo pass digitale</p>
                            </div>
                            <div class="info-content">
                                <p>€<?= $prezzo_digitale ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php $prezzo_fisico = $page->prezzo_fisico(); ?>
                <?php if ($prezzo_fisico->isNotEmpty()): ?>
                    <div class="pass-info">
                        <div class="single-info">
                            <div class="info-title">
                                <p>Prezzo pass fisico</p>
                            </div>
                            <div class="info-content">
                                <p>€<?= $prezzo_fisico ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php $durata = $page->durata(); ?>
                <?php if ($durata->isNotEmpty()): ?>
                    <div class="pass-info">
                        <div class="single-info">
                            <div class="info-title">
                                <p>Durata</p>
                            </div>
                            <div class="info-content">
                                <p><?= $durata ?> gg.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php $trasporti = $page->trasporti(); ?>
                <?php if ($trasporti->isNotEmpty()): ?>
                    <div class="pass-info">
                        <div class="single-info">
                            <div class="info-title">
                                <p>Trasporti</p>
                            </div>
                            <div class="info-content">
                                <p><?= $trasporti ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php $app_required = $page->app()->isTrue(); ?>
                <?php if ($app_required): ?>
                    <div class="pass-info">
                        <div class="single-info">
                            <div class="info-title">
                                <p>App</p>
                            </div>
                            <div class="info-content">
                                <p>Sì</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php snippet('layouts', [
    'layout_content' => $site->footer(),
    'class' => 'footer',
]); ?>

<?php snippet('footer') ?>
