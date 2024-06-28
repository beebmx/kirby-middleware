<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

use Kirby\Cms\App as Kirby;

uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

//expect()->extend('toBeOne', fn() => $this->toBe(1));

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
): Kirby {
    return new Kirby([
        'roots' => array_merge([
            'index' => '/dev/null',
            'base' => $base = dirname(__DIR__),
        ], $roots),
        'templates' => [
            'default' => fixtures('templates/default.php'),
        ],
        'site' => [
            'children' => $children,
        ],
        'options' => $options,
        'hooks' => require extensions('hooks.php'),
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