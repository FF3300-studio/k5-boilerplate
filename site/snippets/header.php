<!DOCTYPE html>
<?php setlocale(LC_TIME, 'it_IT.utf8', 'it_IT'); ?>
<html class="no-js" lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Preconnect CDN -->
  <link rel="preconnect" href="https://unpkg.com">
  <link rel="preconnect" href="https://ajax.googleapis.com">

  <!-- Preload dei font principali -->
  <!-- <link rel="preload" href="<?= url('/assets/build/fonts/FREAKGroteskNext-Bold.ttf') ?>" as="font" type="font/ttf" crossorigin="anonymous">
  <link rel="preload" href="<?= url('/assets/build/fonts/InstrumentSans.ttf') ?>" as="font" type="font/ttf" crossorigin="anonymous"> -->

  <?php if($site->fb_domain_verification()->isNotEmpty()): ?>
    <meta name="facebook-domain-verification" content="<?= htmlspecialchars($site->fb_domain_verification()) ?>" />
  <?php endif; ?>

  <script type="application/ld+json">
  {
    "@type": "Organization",
    "legalName": "<?= $site->title() ?>",
    "alternateName": "<?= $site->alt_name() ?>",
    "url": "<?= $site->url() ?>",
    "description": "<?= str_replace('#','',str_replace('"',"'",str_replace("*","",$site->descrizione()))) ?>",
    "streetAddress": "<?= $site->address() ?>",
    "addressLocality": "<?= $site->city() ?>",
    "addressRegion": "<?= $site->region() ?>",
    "addressCountry": "<?= $site->country() ?>",
    "PostalCode": "<?= $site->cap() ?>",
    "telephone": "<?= $site->tel() ?>",
    "contactType": "administration"<?= $site->logo()->isNotEmpty() ? ', "logo": "' . $site->logo()->toFile()->url() . '"' : '' ?>
  }
  </script>

  <?php if($page->id() === 'home'): ?>
    <title><?= $site->title() ?></title>
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?= $site->title() ?>" />
    <meta name="twitter:title" content="<?= $site->title() ?>" />
    <meta name="description" content="<?= $site->descrizione()->cleanText() ?>">
    <meta name="twitter:description" content="<?= $site->descrizione()->cleanText() ?>">
    <meta property="og:description" content="<?= $site->descrizione()->cleanText() ?>" />
    <meta name="keywords" content="<?= $site->tags() ?>">
    <meta property="og:url" content="<?= $site->url() ?>">
    <?php if($site->seo_image()->isNotEmpty()): ?>
       <meta property="og:image" content="<?= $site->seo_image()->toFile()->url() ?>" />
       <meta name="twitter:image" content="<?= $site->seo_image()->toFile()->url() ?>">
    <?php elseif($page->immagine()->isNotEmpty()): ?>
       <meta property="og:image" content="<?= $page->immagine()->toFile()->url() ?>" />
       <meta name="twitter:image" content="<?= $page->immagine()->toFile()->url() ?>">
    <?php endif; ?>
  <?php else: ?>
    <title>SOS — <?= $page->title() ?></title>
    <meta property="og:type" content="article" />
    <meta property="og:title" content="<?= $page->title() ?>" />
    <meta name="twitter:title" content="<?= $page->title() ?>" />
    <meta name="description" content="<?= $page->descrizione()->cleanText() ?>">
    <meta name="twitter:description" content="<?= $page->descrizione()->cleanText() ?>">
    <meta property="og:description" content="<?= $page->descrizione()->cleanText() ?>" />
    <meta name="keywords" content="<?= $page->tags() ?>">
    <meta property="og:url" content="<?= $page->url() ?>">
    <?php if($page->immagine()->isNotEmpty()): ?>
      <meta property="og:image" content="<?= $page->immagine()->toFile()->url() ?>" />
      <meta name="twitter:image" content="<?= $page->immagine()->toFile()->url() ?>">
    <?php endif; ?>
  <?php endif; ?>

  <?php if($site->account_x()->isNotEmpty()): ?>
    <meta name="twitter:creator" content="<?= $site->account_x() ?>" />
  <?php endif; ?>
  <?php if($site->fb_app_id()->isNotEmpty()): ?>
    <meta property="fb:app_id" content="<?= $site->fb_app_id() ?>" />
  <?php endif; ?>

  <meta property="og:site_name" content="<?= $site->title() ?>">
  <meta name="twitter:card" content="summary" />

  <link rel="shortcut icon" type="image/x-icon" href="<?= url('favicon.ico') ?>">

  <!-- Styles -->
  <?php if($page->hasChildren() && $page->collection_options() === "map"): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin="" />
  <?php endif; ?>

  <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
  <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

  <link rel="stylesheet" href="<?= url('node_modules/bootstrap/dist/css/bootstrap.css') ?>">
  <link rel="stylesheet" href="<?= url('assets/build/css/css.css?v=1.0.0') ?>"> <!-- usa versionamento manuale -->

  <!-- JS in HEAD solo se necessario -->
  <?= js('assets/js/lazysizes.min.js') ?>

  <!-- Analytics -->
  <?php if (isFeatureAllowed('analytics')): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-9BY4J15RYX"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-9BY4J15RYX');
    </script>
  <?php endif; ?>
</head>

<body>
