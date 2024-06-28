<?php

use Beebmx\KirbyMiddleware\Route;

it('returns an array if a path matches', function () {
    $route = new Route('tests/(:num?)');

    expect($route)
        ->resolve('tests')
        ->toBeArray()
        ->toBeEmpty()
        ->resolve('tests/1')
        ->toBeArray()
        ->not->toBeEmpty();
});

it('returns false if a path doesnt match', function () {
    $route = new Route('tests/(:num?)');

    expect($route)
        ->resolve('other')
        ->toBeBool()
        ->toBeFalse();
});

it('returns a boolean if a path matches or not', function () {
    $route = new Route('tests/(:num?)');

    expect($route)
        ->matches('tests')
        ->toBeBool()
        ->toBeTrue()
        ->matches('tests/1')
        ->toBeBool()
        ->toBeTrue()
        ->matches('other')
        ->toBeBool()
        ->toBeFalse();

});
