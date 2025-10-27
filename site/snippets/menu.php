<?php $items = $site->menu()->toStructure() ?>
  <nav class="site-header">
    <?php if ($items->isNotEmpty()) : ?>
    <div class="site-header-inner">
      <?php snippet('logo-object',[]); ?>
      <div class="navigation navigation-desktop">
        <?php snippet('menuitem-list', ['items' => $items, 'accordion__item' => true]) ?>
      </div>
      <div class="navbar-toggler closed">
			  <span class="icon-bar"></span>
			  <span class="icon-bar"></span>
			  <span class="icon-bar"></span>
		  </div>
    </div>
    <div class="navigation navigation-mobile">
      <div class="navigation-mobile-flexbox">
        <?php snippet('mobile-menuitem-list', ['items' => $items, 'accordion__item' => true]) ?>
      </div>
      <h2 class="title" font-size><span class="zero_uno">®</span></h2>
    </div>
    <?php endif ?>
</nav>


