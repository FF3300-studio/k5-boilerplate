<?php
/**
 * Config modulare per Kirby 5
 * Unisce in ordine: options.php, panel.php, hooks.php, routes.php
 * Puoi aggiungere anche _local.php per override in dev
 */
$root   = __DIR__;
$config = [];

foreach (['options.php','panel.php','hooks.php','routes.php'] as $file) {
  $path = $root . '/' . $file;
  if (file_exists($path)) {
    $part = require $path;
    if (is_array($part)) {
      $config = array_replace_recursive($config, $part);
    }
  }
}

// Override locali (git-ignored)
if (file_exists($root . '/_local.php')) {
  $local = require $root . '/_local.php';
  if (is_array($local)) {
    $config = array_replace_recursive($config, $local);
  }
}

return $config;
