<?php if ($pageLink = $item->pageLink()->toPage()) : ?>
    <a class="<?php if($item->isMobile() == "true"): ?>ismobile<?php endif; ?> title_link <?php if($page->id() == $pageLink->id()): ?>active<?php endif ?>" href="<?= $pageLink->url() ?>"><?= $item->linkTitle()->or($pageLink->title()) ?></a>
<?php elseif ($item->externalLink()->isNotEmpty()) : ?>
    <a class="<?php if($item->isMobile() == "true"): ?>ismobile<?php endif; ?> title_link <?php if($page->url() == $item->externalLink()->value()): ?>active<?php endif ?>" href="<?= $item->externalLink() ?>"><?= $item->linkTitle()->or(Url::short($item->externalLink()->value())) ?></a>
<?php endif ?> 