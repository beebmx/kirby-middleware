<?php

use Kirby\Cms\App as Kirby;

Kirby::plugin('beebmx/middleware', [
    'hooks' => require_once __DIR__.'/extensions/hooks.php',
    'options' => require_once __DIR__.'/extensions/options.php',
]);
