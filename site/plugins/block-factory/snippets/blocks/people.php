<?php $persone = $site->persone()->toStructure(); // Fetch people ?>
<div class="container-persone">
<?php foreach($persone->shuffle() as $persona): ?>
    <?php if($persona->socio() == "true"): ?>
        <div class="tag"><?= $persona->nome() ?></div>
    <?php endif; ?>
<?endforeach; ?>
</div>