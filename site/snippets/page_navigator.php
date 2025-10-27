<?php if(strtolower($page->title()) !== 'home'):?>
  <nav class="breadcrumbs">
  <ul>
    <!-- Home link -->
    <li>
      <a href="<?= $site->url() ?>">Home</a><span class="arrow">→</span>
    </li>
    
    <!-- Loop through parents -->
    <?php foreach($page->parents()->flip() as $parent): ?>
      <li>
        <a href="<?= $parent->url() ?>"><?= $parent->title() ?></a><span class="arrow">→</span>
      </li>
    <?php endforeach ?>
    
    <!-- Current page -->
    <li>
    <?php 
      $formatter = new IntlDateFormatter('it_IT', IntlDateFormatter::NONE, IntlDateFormatter::NONE);
      $formatter->setPattern('d MMM Y'); // Modello simile a %d – %b – %Y;
      ?>
      
      <span class="current"><?php if(strtolower($page->title()) !== 'home'):?><?= $page->title() ?><?php endif; ?>
    </li>
    <?php if ($page->modified() >= strtotime('-3 days')): ?>
    <li style="min-width: 100%; color: silver; text-transform:uppercase"><strong>MODIFICA RECENTE</strong> il <strong><?= $formatter->format($page->modified()) ?></strong></li>
    <?php endif; ?>

  </ul>
</nav>
<?php endif; ?>