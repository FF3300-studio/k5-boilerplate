<?php $item_video = $item->video()->toFile(); ?>
<link rel="preload" as="video" href="<?= $item_video->url() ?>" type="video/mp4" />
<div class="swiper-video-container" style="width: 100%; height: 100vh; overflow: hidden;">

<?php if($item->extra_layer() == "true"): ?>
    <div class="layer"></div>
<?php endif; ?>
    <video
        preload="metadata"
        id="vid<?= $string; ?>"
        class="lazyload"
        autoplay
        loop
        muted
        playsinline
        style="width: 100%; height: 100%; object-fit: cover;"
    >
        <?php if(isset($item_video)): ?>
            <source src="<?= $item_video->url() ?>" type="video/mp4">
        <?php endif; ?>
    </video>
</div>
