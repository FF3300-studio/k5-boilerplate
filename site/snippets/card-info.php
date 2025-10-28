<?php $deadline_exist = "off"; ?>
<?php $deadline_toggle = "off"; ?>
<?php $deadline = $item->deadline() OR NULL ?>
<?php $facilitato = false ?>
<div class="cards-details" style="<?php if($page->parent() !== NULL): ?>padding: 30px; max-width: 1280px; min-width: fit-content; margin: 0 auto!important;<?php else: ?>padding: 15px;<?php endif; ?> margin: 0;" class="cards-info" <?php  if($direction == "row"): ?>style="margin-left: 15px"<?php endif; ?>>

<?php if($tag_toggle == true AND $item->child_category_selector()->isNotEmpty()): ?>
<div class="cards-categories">
    <span class="tag parent"><?= $item->parent()->title() ?></span>
    <?php foreach($item->child_category_selector()->split() as $category): ?>
        <span class="tag"><?= $category ?></span>
        <?php if(strtolower($category) == "workshop"): ?>
            <?php $facilitato = true ?>
        <?php endif; ?>
    <?php endforeach; ?>    
</div>
<?php endif; ?>

<div class="cards-title" style="margin:0!important;">
    <?php snippet('freaky-title',[
        'input' => $item->title(),
        'big' => $big ?? true,
    ]); ?>
</div>

<?php if ($item->deadline()->isNotEmpty()): ?>
    <?php $deadline_exist = "on" ?>
<?php endif; ?>
<?php if ($item->deadline()->isNotEmpty() && strtotime($item->deadline()) >= strtotime('today')): ?>
    <?php $deadline_toggle = "on" ?>
    <hr style="margin: 0; margin-top: 30px; border: none; border-bottom: 1px solid gray;">
    <?php $deadline = $item->deadline() ?>
    <div class="cards-dates" style="display: flex; width: 100%; justify-content: center; flex-wrap:wrap; text-align: center;">
        <?php 
        $formatter = new IntlDateFormatter('it_IT', IntlDateFormatter::NONE, IntlDateFormatter::NONE);
        $formatter->setPattern('d MMM Y'); // Modello simile a %d – %b – %Y;
        ?>
        <span id="deadline" class="center" style="min-width: fit-content; max-width: 100%; text-transform: uppercase; text-align: center; padding-top: 15px; padding-bottom: 15px;" class="time"><strong>ISCRIVITI ENTRO</strong> → <strong><?= $formatter->format($deadline->toDate()) ?></strong></span>
    </div>
    <hr style="margin: 0; margin-bottom: 15px; border: none; border-top: 1px solid gray;">
<?php else: ?>
    <hr style="margin: 0; border: none;">
<?php endif; ?>

<div class="cards-text">
    <?php echo $item->descrizione()->kirbytext(); ?>
</div>

<?php if ($item->team()->isNotEmpty()): ?>

    <?php if($facilitato == false): ?>
        <div class="team-label"><p style="margin: 0; margin-top: 15px; margin-bottom: 15px;">Con la partecipazione di:</p></div>
    <?php else: ?>
        <div class="team-label"><p style="margin: 0; margin-top: 15px; margin-bottom: 7.5px;">Attività facilitata da:</p></div>
    <?php endif; ?>
    
    <?php foreach($item->team()->toStructure() as $team_member): ?>
        <p class="team" style="margin-top:0; margin-bottom: 5px;">✨ <strong><?= $team_member->persona() ?></strong> / <?= $team_member->ruolo() ?></p>
    <?php endforeach; ?>

    <div class="cards-team" style="display: flex; width: 100%; justify-content: center; flex-wrap:wrap; text-align: center;">
    
    </div>

<?php endif; ?>

<?php if($item->appuntamenti()->isNotEmpty()): ?>
    <div>
        <div class="team-label"><p style="margin: 0 auto; margin-top: 15px; margin-bottom: 0;"><strong>Appuntamenti:</strong></p></div>
        <hr style="margin: 0; margin-top: 15px; border: none; border-bottom: 1px solid black;">
        <?php $appuntamenti = $item->appuntamenti()->toStructure() ?>
        <div class="cards-dates" style="display: flex; width: 100%; justify-content: space-between; flex-wrap:wrap;">
            <?php foreach($appuntamenti as $appuntamento): ?>
            <?php 
            $formatter = new IntlDateFormatter('it_IT', IntlDateFormatter::NONE, IntlDateFormatter::NONE);
            $formatter->setPattern('d MMM Y'); // Modello simile a %d – %b – %Y;
            ?>
            <span style="border: none; border-bottom: 1px solid gray; text-transform: uppercase; padding-top: 15px; padding-bottom: 15px; text-align: center;" class="time"><strong><?= $formatter->format($appuntamento->giorno()->toDate()) ?></strong></span>  <span class="time" style="border: none; border-bottom: 1px solid gray; text-align: center; border-left: 1px solid gray; padding-top: 15px; padding-bottom: 15px;"><?= $appuntamento->orario_inizio()->toDate('H:i') ?> → <?= $appuntamento->orario_fine()->toDate('H:i') ?></span>
            <?php endforeach; ?>
        </div>
        <hr style="margin: 0; margin-bottom: 0; border: none!important;">
    </div>
<?php endif; ?>

<?php if($item->dove()->isNotEmpty()): ?>
<div class="location">
    <?php foreach($item->dove()->toPages() as $luogo): ?>
        📍 <?= $luogo->title() ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>

    <?php 
    $formatter = new IntlDateFormatter('it_IT', IntlDateFormatter::NONE, IntlDateFormatter::NONE);
    $formatter->setPattern('d MMM Y'); // Modello simile a %d – %b – %Y;
    ?>

        <?php if($page->parent() !== NULL AND $page->parent()->collection_options() == "calendar"): ?>
            <?php if(strtotime($page->deadline()) >= strtotime('today')): ?>
            <hr style="margin: 0; margin-top: 15px; margin-bottom: 15px; border: none; border-bottom: 1px solid gray;">
            <?php snippet('form-request-counter',[
            'page' => $page,
            ])?>
            <?php endif; ?>
        <?php else: ?>
            <?php if(strtotime($item->deadline()) >= strtotime('today')): ?>
            <hr style="margin: 0; margin-top: 15px; margin-bottom: 15px; border: none; border-bottom: 1px solid gray;">
            <?php snippet('form-request-counter',[
            'page' => $item,
            ])?>
            <?php endif; ?>
        <?php endif; ?>

        <?php

// Data di oggi
$today = date('Y-m-d', strtotime('today'));

// Deadline in formato corretto (Y-m-d)
$deadline = $item->deadline()->isNotEmpty() ? $item->deadline()->toDate('Y-m-d') : null;

// deadline è definita e successiva o uguale a oggi?
$deadline_bool = $deadline && ($deadline >= $today);

// Data tra tre giorni
$next_three_days = date('Y-m-d', strtotime('+3 days'));

// deadline è entro i prossimi 3 giorni e non nel passato?
$incoming_deadline_bool = $deadline && ($deadline >= $today && $deadline <= $next_three_days);

$current = $item ?? $page;
$formData = $formData($current);
$hasAvailableSeats = !isset($formData['available']) || $formData['available'] > 0;

// Controlla appuntamenti imminenti solo se non c'è deadline
$incoming_appointment_bool = false;
if (!$deadline && $current->appuntamenti()->isNotEmpty()) {
    foreach ($current->appuntamenti()->toStructure() as $appuntamento) {
        $giorno_appuntamento = $appuntamento->giorno()->toDate('Y-m-d');
        if ($giorno_appuntamento >= $today && $giorno_appuntamento <= $next_three_days) {
            $incoming_appointment_bool = true;
            break;
        }
    }
}

?>
<?php if (($incoming_deadline_bool || $incoming_appointment_bool) && $hasAvailableSeats): ?>
    <span class="bollino_manca_poco" style="z-index: 4; padding: 24px 17px; border-radius: 100vw; text-align: center">MANCA<br>POCO!</span>
<?php elseif ($deadline_toggle == "on" && $deadline_bool && $hasAvailableSeats): ?>
    <span class="bollino_iscriviti" style="z-index: 3; padding: 24px 8px; border-radius: 100vw; text-align: center">ISCRIZIONI<br>APERTE</span>
<?php elseif ($deadline_exist == "on"): ?>
    <span class="bollino_chiuse" style="z-index: 3; padding: 24px 8px; border-radius: 100vw; text-align: center">ISCRIZIONI<br>CHIUSE</span>
<?php endif; ?>
</div>