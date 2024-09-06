<?php

namespace Beebmx\KirbyMiddleware\Concerns;

use Beebmx\KirbyMiddleware\Request;
use Closure;
use Kirby\Cms\App;
use Kirby\Http\Response;

trait HasRedirection
{
    protected function redirectTo(Request $request, string $redirection = 'auth'): Response
    {
        if (isset(static::$redirectTo)) {
            return match (true) {
                is_string(static::$redirectTo) => Response::redirect(static::$redirectTo),
                static::$redirectTo instanceof Closure => Response::redirect(call_user_func(static::$redirectTo, $request)),
            };
        }

        if ($default = $this->getDefaultFor($redirection)) {
            return Response::redirect($default);
        }

        return Response::redirect();
    }

    protected function getDefaultFor(string $guard)
    {
        return App::instance()->option("beebmx.kirby-middleware.redirections.{$guard}");
    }

    public static function redirectUsing(Closure|string $redirectTo): void
    {
        static::$redirectTo = $redirectTo;
    }
}
