<?php
use Kirby\Cms\Response;

return [
  'routes' => [
    [
      'pattern' => 'sitemap.xml',
      'action'  => function () {
        $pages   = site()->pages()->index();
        $ignore  = kirby()->option('sitemap.ignore', ['error']);
        $content = snippet('sitemap', compact('pages','ignore'), true);
        return new Response($content, 'application/xml');
      }
    ],
    [
      'pattern' => 'sitemap',
      'action'  => function () {
        return go('sitemap.xml', 301);
      }
    ],
  ]
];
