

<?php if($block->isNotEmpty()): ?>

<?php
$structure = $block->slider();
$counter   = 0;
$suffisso = generateRandomString();
$string = generateRandomString();
$gallery_pagination = true;
$gallery_arrows = true;
?>
<div class="block-slider">
    <!-- Slider main container -->
    <div class="swiper swiper<?php if(isset($suffisso)): ?><?= $suffisso ?><?php endif; ?>">
        <!-- Additional required wrapper -->
        <div class="swiper-wrapper">
        <?php $counter = 0; ?>
            <?php foreach( $structure->toStructure() as $item ): ?>
                <?php $crop = $item->crop()->toBool(); ?>
                <!-- Slides -->
                <?php $counter++; ?>
                <div class="swiper-slide carosel">

                    <?php if($item->pics()->isNotEmpty() AND $item->video()->isEmpty()): ?>
                        <?php snippet('block-slide-image',[
                            'suffisso' => $suffisso,
                            'string' => $string,
                            'structure' => $structure,
                            'counter' => $counter,
                            'crop' => $crop,
                            'item' => $item,
                        ]); ?>

                    <?php elseif($item->pics()->isEmpty() AND $item->video()->isNotEmpty()): ?>
                        <?php snippet('block-slide-video',[
                            'suffisso' => $suffisso,
                            'string' => $string,
                            'structure' => $structure,
                            'counter' => $counter,
                            'crop' => $crop,
                            'item' => $item,
                        ]); ?>
                    <?php endif; ?>

                    <?php if( $item->descrizione()->isNotEmpty() OR $item->title()->isNotEmpty() ): ?>
                        <div class="slide-informations">
                            <?php snippet('block-slide-info',[
                                'item' => $item,
                                'block' => $block,
                            ])?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- If we need pagination -->
        <?php if(isset($gallery_pagination) AND  $gallery_pagination == true): ?>
            <div class="swiper-pagination swiper<?php if(isset($suffisso)): ?><?= $suffisso ?><?php endif; ?>"></div>
        <?php endif; ?>

        <!-- If we need navigation buttons -->
        <?php if(isset($gallery_arrows) AND $gallery_arrows == true): ?>
            <?php if($counter > 1): ?>
                <?php $prev = ".".$suffisso."-button-prev" ?>
                <div class="swiper-button-prev <?php if(isset($suffisso)): ?><?= $suffisso ?><?php endif; ?>-button-prev"></div>

                <?php $next = ".".$suffisso."-button-next" ?>
                <div class="swiper-button-next <?php if(isset($suffisso)): ?><?= $suffisso ?><?php endif; ?>-button-next"></div>
            <?php endif; ?>

        <?php endif; ?>

        </div>


        <?php if($counter > 1): ?>
            <script>
                const swiper<?php if(isset($suffisso)): ?><?= $suffisso ?><?php endif; ?> = new Swiper('.swiper<?php if(isset($suffisso)): ?><?= $suffisso ?><?php endif; ?>', {
                    stopOnLastSlide: false,
                    autoplay: {
                        delay: 8000,
                    },
                    // Optional parameters
                    navigation: {
                        nextEl: '<?= $next ?>',
                        prevEl: '<?= $prev ?>',
                    },
                    preloadImages: false,
                    lazy: true,
                    watchSlidesVisibility: true,
                });
            </script>
        <?php else: ?>
            <script>
                const swiper<?php if(isset($suffisso)): ?><?= $suffisso ?><?php endif; ?> = new Swiper('.swiper<?php if(isset($suffisso)): ?><?= $suffisso ?><?php endif; ?>', {
                    stopOnLastSlide: true,
                    autoplay: false,
                    preloadImages: false,
                    lazy: false,
                    watchSlidesVisibility: true,
                    // Optional parameters
                });
            </script>
        <?php endif; ?>

    </div>

<?php endif; ?> 

