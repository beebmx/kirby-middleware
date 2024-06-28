<?php

use Beebmx\KirbyMiddleware\Request;

describe('instance', function () {
    beforeEach(function () {
        Request::destroy();
    });

    it('set the instance', function () {
        $request = Request::instance();

        expect($request)
            ->toBeInstanceOf(Request::class);
    });

    it('can initialize the request from instance', function () {
        Request::instance([
            'body' => ['foo' => 'bar'],
        ]);

        expect(Request::instance())
            ->body()->get('foo')
            ->toEqual('bar');
    });

    it('can replace the instance', function () {
        Request::instance([
            'body' => ['foo' => 'bar'],
        ]);

        Request::replaceInstance(new Request([
            'body' => ['bar' => 'baz'],
        ]));

        expect(Request::instance())
            ->body()->data()
            ->not->toHaveKey('foo')
            ->toHaveKey('bar', 'baz');
    });

    it('can destroy the instance', function () {
        Request::instance([
            'body' => ['foo' => 'bar'],
        ]);

        Request::destroy();

        expect(Request::instance()->data())
            ->toBeArray()
            ->toBeEmpty();
    });
});

describe('features', function () {

    it('can return a user', function () {
        $kirby = App();

        $kirby->impersonate('kirby', function ($kirby) {
            expect(new Request)
                ->user()
                ->not->toBeNull();
        });
    });

    it('returns null if no user is authenticated', function () {
        App();

        expect(new Request)
            ->user()
            ->toBeNull();
    });

    it('returns body content', function () {
        $request = new Request([
            'body' => [
                'foo' => 'bar',
            ],
        ]);

        expect($request->body())
            ->get('foo')
            ->toEqual('bar');
    });

    it('can replace the current body with another', function () {
        $request = new Request([
            'body' => [
                'foo' => 'bar',
            ],
        ]);

        $request->replaceBody([
            'foo' => 'baz',
        ]);

        expect($request->body())
            ->get('foo')
            ->toEqual('baz');
    });

    it('returns query content', function () {
        $request = new Request([
            'query' => [
                'foo' => 'bar',
            ],
        ]);

        expect($request->query())
            ->get('foo')
            ->toEqual('bar');
    });

    it('can replace the current query with another', function () {
        $request = new Request([
            'body' => [
                'foo' => 'bar',
            ],
        ]);

        $request->replaceQuery([
            'foo' => 'baz',
        ]);

        expect($request->query())
            ->get('foo')
            ->toEqual('baz');
    });
});
