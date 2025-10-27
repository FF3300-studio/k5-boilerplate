
<div class="block-cta" style="width: 100%; display: flex; <?= $block->container_custom_css() ?>">
<?php if (isset($block)): ?>
  <?php foreach ($block->cta()->toStructure() as $cta): ?>
    <a  class="cta-block__item <?= $cta->shape() ?> <?= $cta->contrast() ?>" style="<?= $cta->cta_custom_css() ?>" 
        <?php if($cta->switch() == "download"): ?>
            <?php if($cta->download()->isNotEmpty()): ?>
                href="<?= $cta->download()->toFile()->url() ?>" 
                target="_blank"
            <?php endif; ?>
        <?php elseif($cta->switch() == "pagina"): ?>
            <?php if($cta->page()->isNotEmpty()): ?>
                href="<?= $cta->page()->toPage()->url() ?><?php if($cta->slug()->isNotEmpty()): ?>/#<?= $cta->slug() ?><?php endif; ?>" 
                target="_self"
            <?php else: ?>
                href="<?= $page->url() ?><?php if($cta->slug()->isNotEmpty()): ?>/#<?= $cta->slug() ?><?php endif; ?>" 
                target="_self"
            <?php endif; ?>
        <?php elseif($cta->switch() == "url"): ?>
            href="<?= $cta->url() ?>" 
            target="_blank"
        <?php else: ?>
        <?php endif; ?>
        title="<?= $cta->anteprima() ?>"
        >
            <?= $cta->anteprima()->text() ?>
    </a>
  <?php endforeach; ?>
<?php endif ?>
</div> 
