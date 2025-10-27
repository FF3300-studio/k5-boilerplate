<?php
$correlati = $page->correlati()->toPages();
if ($correlati->count() > 0):
?>
<div class="blocks related-content-container" style="margin-top: 60px;">
<h5 class="label-grid correlati" style="color: white"><strong>Contenuti correlati:</strong></h5>
  

<div class="block-grid-a-list" style="justify-content: space-evenly; display: flex;">        
        <?php foreach ($correlati as $child): ?>
            <!-- CARD -->
            <?php snippet('card-grid',[
                'item' => $child,
                'thumb_toggle' => true, 
                'tag_toggle' => false,
                'correlati' => true,
                'direction' => 'column',
                'category_color' => false,
                'big' => false,
            ]) ?> 
        <?php endforeach; ?>
    </div>

 

</div>
<?php endif ?>
