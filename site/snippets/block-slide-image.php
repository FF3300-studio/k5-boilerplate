
                <?php if(isset($crop) AND  $crop == true AND $item->pics()->toFile() !== NULL ): ?>
                    <?php if($item->extra_layer() == "true"): ?>
                    <div class="layer"></div>
                    <?php endif; ?>

                    <div class="crop classe<?= $counter ?>_<?= $string ?>">
                    <style>
                        .crop{
                            width: 100%;
                            min-height: 90vh;
                            background-position: center center;
                            background-repeat: no-repeat;
                            background-size: cover;
                            z-index: 1;
                        }
                        <?php $classe = ".classe".$counter."_".$string ?>
                        <?= $classe ?>{
                            <?php $cover_image = $item->pics()->toFile() ?>
                            background-image: url('<?= $cover_image->thumb(['format'  => 'webp'])->url() ?>');
                        }

                        .text{
                            top: 125px!important;
                        }
                    </style>
                    </div>
                <?php else: ?>
                    <?php if( $item->pics()->isNotEmpty() ): ?>
                    <?php if($item->extra_layer() == "true"): ?>
                    <div class="layer"></div>
                    <?php endif; ?>
                    <div class="image">
                        <?php $image = $item->pics()->toFile(); ?>
                        <?php snippet('image',[
                            'image' => $image, 
                        ]) ?>
                    </div>
                    <?php elseif( $item->thumbnail()->isNotEmpty()): ?>
                        <?php if($item->extra_layer() == "true"): ?>
                        <div class="layer"></div>
                        <?php endif; ?>
                        <div class="image">
                        <?php $image = $item->thumbnail()->toFile(); ?>
                        <?php snippet('image',[
                            'image' => $image, 
                        ]) ?>                            
                        </div>
                    <?php endif; ?>
                    <?php if($item->crop() !== "true"): ?>
                        <style>
                            .swiper-slide .image img{
                                min-width: 100%!important;
                                min-height: 100%!important;
                            }
                        </style>
                    <?php endif; ?>
                <?php endif; ?>