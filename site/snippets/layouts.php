<?php 
// Calcola dati del form sulla pagina corrente
$formData = $formData($page);
?>
<?php if(isset($layout_content) AND $layout_content->isNotEmpty()): ?>
    <div class="blocks-container <?php if(isset($class)): ?><?= $class ?><?php endif; ?>">
        <?php $index = 1; ?>
        <?php foreach ($layout_content->toLayouts() as $layout): ?>
                <?php $anchorEnabled = $layout->anchor()->isTrue(); ?>
            <?php endforeach ?>
            <?php if ($anchorEnabled): ?>
            <div class="anchors-navigation">
                <div class="single-anchor">
                    <p style="text-transform: uppercase;"><strong>Contenuti</strong></p>
                </div>
            <?php endif; ?>
            <?php foreach ($layout_content->toLayouts() as $layout): ?>
                <?php if($layout->sticky() == "true"): ?>
                    <?php $id_string = generateRandomString(); ?>
                    <style>
                        <?= '#sticky_'?><?= $id_string ?> {
                            position: sticky;
                            top: <?= $layout->sticky_offset() ?>px!important;
                        }
                        * {
                            overflow: visible!important;
                        }
                    </style>
                <?php endif; ?>
                <?php $anchorEnabled = $layout->anchor()->isTrue();
                $anchorName = $layout->anchor_name()->value();
                $slugifiedAnchorName = Str::slug($anchorName); ?>
                <?php if ($anchorEnabled): ?>
                    <div class="single-anchor">
                        <a href="#<?= $slugifiedAnchorName; ?>">📑 <?= $anchorName; ?></a>
                    </div>
                <?php endif; ?>
                <?php $anchorEnabled = $layout->anchor()->isTrue();
                $anchorName = $layout->anchor_name()->value();
                $slugifiedAnchorName = Str::slug($anchorName); ?>
            <?php endforeach ?>
            <?php if ($anchorEnabled): ?>
            </div>
            <?php endif; ?>

        <div class="blocks-container-inner">
            <?php foreach ($layout_content->toLayouts() as $layout): ?>
                <?php 
                $anchorEnabled = $layout->anchor()->isTrue();
                $CustomIDEnabled = $layout->custom_id()->isNotEmpty();
                $anchorName = $layout->anchor_name()->value();
                $slugifiedAnchorName = Str::slug($anchorName); 
                $scadenza = $layout->scadenza();
                $raw_customID = $layout->custom_id()->value();
                $custom_ID = Str::slug($raw_customID); 
                ?>

                <?php if($scadenza == "true" ): ?>
                    <?php if($page->deadline() !== NULL && ($formData['available'] === null || $formData['available'] > 0)): ?>
                    <?php 
                    $formatter = new IntlDateFormatter('it_IT', IntlDateFormatter::NONE, IntlDateFormatter::NONE);
                    $formatter->setPattern('d MMM Y'); // Modello simile a %d – %b – %Y;
                    ?> 
                        <?php if(strtotime($page->deadline()) >= strtotime('today')): ?>

                            <?php if ($anchorEnabled): ?>
                                <div class="anchor-block" id="<?= $slugifiedAnchorName; ?>">
                                    <div class="anchor-block-inner">
                                        <p><?= $anchorName; ?></p>
                                    </div>
                                </div>
                                <div <?php if($CustomIDEnabled):?>id="<?= $custom_ID ?>"<?php endif; ?> class="row anchor-row" style="<?php if(isset($custom_style)): ?><?= $custom_style ?><?php endif; ?><?php if($layout->custom_css()->isNotEmpty()):?><?= $layout->custom_css() ?><?php endif; ?>">
                                    <?php foreach ($layout->columns() as $column): ?>
                                        <div class="column col-lg-<?= $column->span() ?> <?php if($column->blocks()->isEmpty()): ?>mobile_display_none<?php endif; ?>">
                                            <div <?php if($layout->sticky() == "true"): ?>id="sticky_<?= $id_string ?>"<?php endif; ?> class="blocks">
                                                <?= $column->blocks() ?>
                                            </div>
                                        </div>
                                    <?php endforeach ?>
                                </div>
                            <?php else: ?>
                                <div <?php if($CustomIDEnabled):?>id="<?= $custom_ID ?>"<?php endif; ?> class="row" style="<?php if(isset($custom_style)): ?><?= $custom_style ?><?php endif; ?><?php if($layout->custom_css()->isNotEmpty()):?><?= $layout->custom_css() ?><?php endif; ?>">
                                    <?php foreach ($layout->columns() as $column): ?>
                                        <div class="column col-lg-<?= $column->span() ?> <?php if($column->blocks()->isEmpty()): ?>mobile_display_none<?php endif; ?>">
                                            <div <?php if($layout->sticky() == "true"): ?>id="sticky_<?= $id_string ?>"<?php endif; ?> class="blocks">
                                                <?= $column->blocks() ?>
                                            </div>
                                        </div>
                                    <?php endforeach ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if ($anchorEnabled): ?>
                        <div class="anchor-block" id="<?= $slugifiedAnchorName; ?>">
                            <div class="anchor-block-inner">
                                <p><?= $anchorName; ?></p>
                            </div>
                        </div>
                        <div <?php if($CustomIDEnabled):?>id="<?= $custom_ID ?>"<?php endif; ?> class="row anchor-row" style="<?php if(isset($custom_style)): ?><?= $custom_style ?><?php endif; ?><?php if($layout->custom_css()->isNotEmpty()):?><?= $layout->custom_css() ?><?php endif; ?>">
                            <?php foreach ($layout->columns() as $column): ?>
                                <div class="column col-lg-<?= $column->span() ?> <?php if($column->blocks()->isEmpty()): ?>mobile_display_none<?php endif; ?>">
                                    <div <?php if($layout->sticky() == "true"): ?>id="sticky_<?= $id_string ?>"<?php endif; ?> class="blocks">
                                        <?= $column->blocks() ?>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                    <?php else: ?>
                        <div <?php if($CustomIDEnabled):?>id="<?= $custom_ID ?>"<?php endif; ?> class="row" style="<?php if(isset($custom_style)): ?><?= $custom_style ?><?php endif; ?><?php if($layout->custom_css()->isNotEmpty()):?><?= $layout->custom_css() ?><?php endif; ?>">
                            <?php foreach ($layout->columns() as $column): ?>
                                <div class="column col-lg-<?= $column->span() ?> <?php if($column->blocks()->isEmpty()): ?>mobile_display_none<?php endif; ?>">
                                    <div <?php if($layout->sticky() == "true"): ?>id="sticky_<?= $id_string ?>"<?php endif; ?> class="blocks">
                                        <?= $column->blocks() ?>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach ?>
        </div>
    </div>
<?php endif; ?>