
    <div id="footer" class="footer_nav">
    <?php
    $timestamp = time(); $currentDate = gmdate('Y', $timestamp);
    ?>
    </div>

    <?php snippet('cookie-modal', [
        'assets' => true,
        'showOnFirst' => true,
        'features' => [
          'analytics' => 'Analytics',
        ]
    ]) ?>
    
<!-- JavaScript deferiti -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js" defer></script>
<script src="https://unpkg.com/swiper/swiper-bundle.min.js" defer></script>
<?= js('node_modules/bootstrap/dist/js/bootstrap.js', ['defer' => true]) ?>
<?= js('assets/build/js/js.js', ['defer' => true]) ?>

  </body>
</html>
  