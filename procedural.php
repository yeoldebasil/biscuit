<?php

declare (strict_types = 1);

use Yeoldebasil\Biscuit\Arr;
use Yeoldebasil\Biscuit\Err;
use Yeoldebasil\Biscuit\Http;
use Yeoldebasil\Biscuit\Map;
use Yeoldebasil\Biscuit\Str;
use Yeoldebasil\Biscuit\Util;
use Yeoldebasil\Biscuit\Val;

/**
 * Функция инициализации, проверки окружения
 */
function kick(arr $allowed_hosts, ?string $root = '..'): void
{
    define('BISCUIT_STARTS', microtime(true));
    define('BISCUIT_VER', '0.1-dev');

    define('nil', null);

    if (str(BISCUIT_VER)->endsWith('-dev')) {
        util::dev();
    }

    if ($root !== null) {
        chdir($root);
    }

    set_error_handler([err::class, 'handle']);

    if (! function_exists('fastcgi_finish_request')) {
        throw new ErrorException("Biscuit requires FastCGI to run");
    }

    if (! is_dir('runtime')) {
        mkdir('runtime', 755);
    }

    if (! is_dir('runtime/logs')) {
        mkdir('runtime/logs', 755);
    }

    http::capture($allowed_hosts);
}

function dev($anything)
{
    // ...
}

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

function post($uri_pattern, $closure)
{
    if (http::$method != 'POST') {
        return;
    }

    if (url($pattern, $matches)) {
        call_user_func_array($closure, $matches);
    }
}

function serve($closure = null, $params = [])
{
    if (defined('BISCUIT_ENDS')) {
        return;
    }

    if (! $closure) {
        $closure = fn() => http::respond(404, [], (string) http::$uri);
    }

    call_user_func_array($closure, $params);
}

function str(string $value, string $encoding = 'UTF-8'): Str
{
    return new Str($value, $encoding);
}

function arr(...$list): Arr
{
    return new Arr($list);
}

function map(...$hash): Map
{
    return new Map($hash);
}

/**
 * Проверяет строку на соответсвие регулярному выражению
 */
function val(string $string, string $regex): bool
{
    return (bool) filter_var($string, FILTER_VALIDATE_REGEXP,
        ['options' => ['regexp' => $regex]]
    );
}

/**
 * Улучшенная версия empty()
 */
function blank(mixed &  ...$vars): bool
{
    // no callback is supplied, all empty values will be removed
    return array_filter($vars) === [];
}

/**
 * Склоняет число $num в нужный падеж и возвращает строку
 * Пример массива $words: <яблоко, яблока, яблок>
 */
function incline_number(int | float $num, array $words): string
{
    $num = $num % 100;

    if ($num > 19) {
        $num = $num % 10;
    }

    switch ($num) {
        case 1:{
                return ($words[0]);
            }
        case 2:case 3:case 4:{
                return ($words[1]);
            }
        default: {
                return ($words[2]);
            }
    }
}
