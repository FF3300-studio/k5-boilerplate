<!-- GRIGLIA EVENTI FUTURI -->
<?php if ($futureEvents->isNotEmpty()): ?>
    <h5 class="label-grid new"><strong>FUTURE</strong></h5>
    <div class="block-grid-a-list" style="justify-content: space-evenly; display: flex;">
        <?php $card_counter = 0; ?>
        <?php foreach ($futureEvents as $child): ?>
            <?php $card_counter++; ?>
        <?php endforeach; ?>
        <?php if($card_counter <= 2 AND $card_counter != 0): ?>
            <style>
                .single-cards {
                    margin: 15px auto!important;
                }
            </style>
        <?php endif; ?>
        <?php foreach ($futureEvents->sortBy('appuntamenti.giorno', 'asc') as $child): ?>
            <?php snippet('card-grid', [
                'item' => $child,
                'thumb_toggle' => false,
                'tag_toggle' => true,
                'direction' => 'column',
                'category_color' => null,
            ]) ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- GRIGLIA EVENTI PASSATI -->
<?php if ($pastEvents->isNotEmpty()): ?>
    <?php if ($futureEvents->isNotEmpty()): ?>
        <hr style="margin-bottom: 15px">
    <?php endif; ?>
    <h5 class="label-grid old"><strong>PASSATE</strong></h5>
    <div class="block-grid-a-list" style="justify-content: space-evenly; display: flex;">
        <?php foreach ($pastEvents->sortBy('appuntamenti.giorno', 'desc') as $child): ?>
            <?php snippet('card-grid', [
                'item' => $child,
                'thumb_toggle' => false,
                'tag_toggle' => true,
                'direction' => 'column',
                'category_color' => null,
            ]) ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- MESSAGGIO: NESSUN RISULTATO -->
<?php if ($futureEvents->isEmpty() && $pastEvents->isEmpty()): ?>
    <h2 style="text-align: center; width: 100%; margin: 90px;">
        <strong>Non ci sono risultati</strong>
    </h2>
<?php endif; ?>
