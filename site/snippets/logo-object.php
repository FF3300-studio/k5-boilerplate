<?php if($site->logo_switch() == "logo"): ?>
  <div class="logo">
    <?php if($site->logo()->isNotEmpty()): ?>
    <a href="<?= $site->url() ?>" title="<?= $site->title() ?>">
      <?= svg($site->logo()->toFile()) ?>
    </a>
    <?php else: ?>
    Carica un logo
    <?php endif; ?>
  </div>
<?php elseif($site->logo_switch() == "logotype"): ?>
  <div class="logotype_container">
    <a href="<?= $site->url() ?>" title="<?= $site->logotype() ?>">
      <?php snippet('freaky-logo',[
        'input' => $site->logotype(),
      ]); ?>
    </a>
  </div>
<?php endif; ?>
 