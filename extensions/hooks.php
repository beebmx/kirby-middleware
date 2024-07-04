<?php

use Beebmx\KirbyMiddleware\Facades\Middleware;
use Beebmx\KirbyMiddleware\Request;
use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Exception\Exception;
use Kirby\Http\Response;
use Kirby\Http\Route;

return [
    'route:before' => function (Route $route, string $path, string $method) {
        if ($route->env() === 'site') {
            $request = App::instance()->request();

            Request::instance([
                'body' => $request->body(),
                'files' => $request->files(),
                'method' => $request->method(),
                'query' => $request->query(),
                'url' => $request->url(),
            ]);

            $response = Middleware::handle(
                Request::instance()
            );

            if ($response instanceof Response) {
                $response->send();
            }
        }
    },
    'page.render:before' => function (string $contentType, array $data, Page $page) {
        if (Middleware::response() instanceof Exception && App::instance()->site()->page()->intendedTemplate()->name() !== 'error') {
            throw Middleware::response();
        }

        if (Middleware::response() instanceof Response) {
            Middleware::response()->send();
        }

        return $data;
    },
];
