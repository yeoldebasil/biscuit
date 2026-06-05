<?php

declare (strict_types = 1);

use Biscuit\Err;
use Biscuit\Util;

register_primitive_type_handler('string', Str::class);

/**
 * Функция инициализации, проверки окружения
 */
function kick(arr $allowed_hosts, ?string $root = '..'): void
{
    define('BISCUIT_STARTS', microtime(true));
    date_default_timezone_set('UTC');

    if (BISCUIT_VER->endsWith('-dev')) {
        util::dev();
    }

    if (! mb_check_encoding('', BISCUIT_EXCEPTION)) {
        throw new InvalidArgumentException(sprintf('Unsupported encoding "%s"', $encoding));
    }

    if ($root !== null) {
        chdir($root);
    }

    define('CONF', yaml_parse_file('config/biscuit.yaml'));
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
