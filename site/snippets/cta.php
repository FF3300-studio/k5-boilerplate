
<div class="block-cta" style="width: 100%; display: flex; <?php if($block): ?><?= $block->container_custom_css() ?><?php endif; ?>">
<?php if (isset($cta_items)): ?>
  <?php foreach ($cta_items->cta()->toStructure() as $cta): ?>
    <a  class="cta-block__item" style="padding: 5px 15px; <?= $cta->cta_custom_css() ?>" 
        <?php if($cta->switch() == "download"): ?>
            <?php if($cta->download()->isNotEmpty()): ?>
                href="<?= $cta->download()->toFile()->url() ?>" 
                target="_blank"
            <?php endif; ?>
        <?php elseif($cta->switch() == "pagina"): ?>
            <?php if($cta->page()->isNotEmpty()): ?>
                href="<?= $cta->page()->toPage()->url() ?>" 
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