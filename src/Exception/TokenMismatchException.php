<?php

namespace Beebmx\KirbyMiddleware\Exception;

use Kirby\Exception\Exception;

class TokenMismatchException extends Exception
{
    protected static string $defaultKey = 'tokenMismatch';

    protected static string $defaultFallback = 'CSRF token mismatch.';

    protected static int $defaultHttpCode = 400;
}
