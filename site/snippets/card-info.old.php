<div class="cards-details" style="<?php if($page->parent() !== NULL): ?>padding: 45px; max-width: 1080px; margin: 0 auto!important;<?php else: ?>padding: 15px;<?php endif; ?> margin: 0;" class="cards-info" <?php  if($direction == "row"): ?>style="margin-left: 15px"<?php endif; ?>>

    <div class="cards-title">
        <?php snippet('freaky-title',[
            'input' => $item->title(),
            'big' => true,
        ]); ?>
    </div>
    <?php if($item->locator()->isNotEmpty()) :?>
        <div class="cards-locator">
            <?= $item->locator()->toLocation()->address() ?>, <?= $item->locator()->toLocation()->number() ?>
            <?= $item->locator()->toLocation()->postcode() ?> – <?= $item->locator()->toLocation()->city() ?>
        </div>
    <?php endif; ?>
    <?php if($item->appuntamenti()->isNotEmpty()): ?>
        <hr style="margin: 0; margin-top: 15px; border: none; border-bottom: 2px solid;">
        <?php $appuntamenti = $item->appuntamenti()->toStructure() ?>
        <div class="cards-dates" style="display: flex; width: 100%; justify-content: space-between; flex-wrap:wrap;">
            <?php foreach($appuntamenti as $appuntamento): ?>
            <?php 
            $formatter = new IntlDateFormatter('it_IT', IntlDateFormatter::NONE, IntlDateFormatter::NONE);
            $formatter->setPattern('d MMM Y'); // Modello simile a %d – %b – %Y;
            ?>
                <span style="border: none; border-bottom: 2px solid; text-transform: uppercase; padding-top: 15px; padding-bottom: 15px; text-align: center;" class="time"><?= $formatter->format($appuntamento->giorno()->toDate()) ?></span>  <span class="time" style="border: none; border-bottom: 2px solid; text-align: center; border-left: 2px solid; padding-top: 15px; padding-bottom: 15px;"><?= $appuntamento->orario_inizio()->toDate('h:i') ?> → <?= $appuntamento->orario_fine()->toDate('h:i') ?></span>
                 
                <?php $dove = $appuntamento->dove()->toPage(); ?>
                <?php if ($appuntamento->dove()->isNotEmpty()): ?>
                    <span class="time location" style="border: none; border-bottom: 2px solid; text-align: center; border-left: 2px solid; padding-top: 15px; padding-bottom: 15px;"><object><a href="<?= $dove->url() ?>" title="<?= $dove->title() ?>" target="_blank">📍 <?= $dove->title() ?></a></object></span>

                <?php else: ?>
                <?php endif;?>
            <?php endforeach; ?>
        </div>
        <hr style="margin: 0; margin-bottom: 15px; border: none!important;">
    <?php endif; ?>

    <div class="cards-text">
        <?php echo $item->descrizione()->kirbytext(); ?>
    </div>

    <?php if ($item->deadline()->isNotEmpty() && strtotime($item->deadline()) >= strtotime('today')): ?>
        <hr style="margin: 0; margin-top: 15px; border: none; border-bottom: 2px solid;">
        <?php $deadline = $item->deadline() ?>
        <div class="cards-dates" style="display: flex; width: 100%; justify-content: center; flex-wrap:wrap; text-align: center;">
            <?php 
            $formatter = new IntlDateFormatter('it_IT', IntlDateFormatter::NONE, IntlDateFormatter::NONE);
            $formatter->setPattern('d MMM Y'); // Modello simile a %d – %b – %Y;
            ?>
                <span id="deadline" style="min-width: fit-content; max-width: 33.333%; text-transform: uppercase; padding-top: 15px; padding-bottom: 15px; text-align: center;" class="time">DEADLINE ISCRIZIONI</span> 
                <span id="deadline" style="min-width: fit-content; max-width: 33.333%; text-transform: uppercase; padding-top: 15px; padding-bottom: 15px; text-align: center;" class="time">→</span> 
                <span id="deadline" style="min-width: fit-content; max-width: 33.333%; text-transform: uppercase; padding-top: 15px; padding-bottom: 15px; text-align: center;" class="time"><strong><?= $formatter->format($deadline->toDate()) ?></strong></span>
        </div>
        <hr style="margin: 0; margin-bottom: 15px; border: none; border-top: 2px solid;">
    <?php endif; ?>

    <?php if($tag_toggle == true AND $item->child_category_selector()->isNotEmpty()): ?>
    <div class="cards-categories">
        <?php foreach($item->child_category_selector()->split() as $category): ?>
            <span class="tag"><?= $category ?></span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php
        // Replace 'YYYY-MM-DD' with the given date
        $givenDate = $item->deadline()->toDate('Y-m-d');

        // Calculate the date minus three days
        $targetDate = date('Y-m-d', strtotime($givenDate . ' -3 days'));
        $deadline_bool = date('Y-m-d') >= $targetDate; 
    ?>
    <?php if ($item->deadline()->isNotEmpty()): ?>
        <?php if ($deadline_bool && strtotime($item->deadline()) >= strtotime('today')): ?>
            <span class="bollino_iscriviti">DEADLINE IS COMING</span>
        <?php endif; ?>
    <?php elseif ($item->modified() >= strtotime('-3 days')): ?>
        <span class="bollino_update">FRESH UPDATE</span>
    <?php endif; ?>
</div>





