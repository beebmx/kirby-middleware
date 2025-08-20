<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use Kirby\Cms\App as Kirby;

pest()->uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function App(
    array $options = [],
    ?array $request = null,
    ?array $users = null,
    array $children = [],
    array $roots = [],
    array $server = [],
    array $hooks = [],
): Kirby {
    Kirby::$enableWhoops = false;

    return new Kirby([
        'roots' => array_merge([
            'index' => fixtures('tmp'),
        ], $roots),
        'templates' => [
            'default' => fixtures('templates/default.php'),
        ],
        'site' => [
            'children' => $children,
        ],
        'options' => array_merge([
            'beebmx.middleware' => require extensions('options.php'),
        ], $options),
        'hooks' => array_merge(require extensions('hooks.php'), $hooks),
        'request' => $request,
        'users' => $users,
        'server' => $server,
    ]);
}

function extensions(string $path): string
{
    return dirname(__DIR__).'/extensions/'.$path;
}

function fixtures(string $path): string
{
    return dirname(__DIR__).'/tests/Fixtures/'.$path;
}
