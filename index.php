<?php

use Kirby\Cms\App as Kirby;

Kirby::plugin('beebmx/kirby-middleware', [
    'hooks' => require_once __DIR__.'/extensions/hooks.php',
]);
