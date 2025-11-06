
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
<?= js('assets/build/js/js.js', ['defer' => true]) ?>

  </body>
</html>
  