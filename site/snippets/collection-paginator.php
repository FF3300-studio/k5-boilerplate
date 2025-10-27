<?php if ($collection->pagination()->hasPages()): ?>
    <div class="container-collection paginator">
        <div class="collection-filters">
            <div class="container-categories">
                <?php if ($collection->pagination()->hasNextPage()): ?>
                <a class="all-filter single-filter control" href="<?= $collection->pagination()->nextPageURL() ?>">
                    <?= $page->collection_pagination_prev()->or('contenuti precedenti'); ?>
                </a>
                <?php endif ?>
                <?php if ($collection->pagination()->hasPrevPage()): ?>
                <a class="all-filter single-filter control" href="<?= $collection->pagination()->prevPageURL() ?>">
                    <?= $page->collection_pagination_next()->or('altri contenuti');?>
                </a>
                <?php endif ?>
            </div>
        </div>
    </div>
<?php endif ?>