<?php
return [
  'site.charset' => 'UTF-8',
  'debug'        => true,   // false in produzione
  'cache'        => true,
  'panel.install'=> false,
  'languages'    => true,
  'locale'       => 'it_IT.utf8',

  // Thumbs (K5) — ripulito da residui K4
  'thumbs' => [
    'driver'  => 'gd',     // usa GD; se vuoi Imagick: 'imagick'
    'format'  => 'webp',
    'srcsets' => [
      'default' => [
        '800w'  => ['width' => 800,  'quality' => 66],
        '1280w' => ['width' => 1280, 'quality' => 75],
      ]
    ],
  ],

  // Opzioni mappa sito (usate dai routes)
  'sitemap.ignore' => ['error'],
];
