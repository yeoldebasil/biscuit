<?php

declare (strict_types = 1);

function url(string $pattern, &$matches): bool
{
    $regex = preg_replace('/\{(\w+)\}/', '([^/]+)', $pattern);
    $regex = '#^' . $regex . '$#';

    if (preg_match($regex, http::$uri->value, $matches)) {
        $matches = array_slice($matches, 1);

        return true;
    }

    return false;
}

function get($pattern, $closure)
{
    if (http::$method != 'GET') {
        return;
    }

    if (url($pattern, $matches)) {
        call_user_func_array($closure, $matches);
    }
}

function post($pattern, $closure)
{
    if (http::$method != 'POST') {
        return;
    }

    if (url($pattern, $matches)) {
        call_user_func_array($closure, $matches);
    }
}

function dispatch($closure = null, $params = [])
{
    if (defined('BISCUIT_ENDS')) {
        return;
    }

    if (! $closure) {
        $closure = fn() => http::respond(404, [], 'Not Found');
    }

    call_user_func_array($closure, $params);
}
