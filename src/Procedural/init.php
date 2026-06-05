<?php

declare (strict_types = 1);

use Biscuit\Arr;
use Biscuit\Err;
use Biscuit\Http;
use Biscuit\Str;
use Biscuit\Util;

register_primitive_type_handler('string', Str::class);
register_primitive_type_handler('array', Arr::class);

/**
 * Функция инициализации, проверки окружения
 */
function kick(array $allowed_hosts, ?string $root = '..'): void
{
    define('BISCUIT_STARTS', microtime(true));
    set_error_handler([err::class, 'handle']);
    date_default_timezone_set('UTC');

    if (BISCUIT_VER->endsWith('-dev')) {
        util::dev();
    }

    if (! mb_check_encoding('', BISCUIT_ENCODING)) {
        throw new InvalidArgumentException(sprintf('Unsupported encoding "%s"', $encoding));
    }

    if ($root !== null) {
        chdir($root);
    }

    $requiredFolders = [
        'runtime/',
        'runtime/logs/',
        'runtime/cache/',
        'config/',
    ];

    define('BISCUIT_PATH_BEFORE_ROOT', realpath(getcwd() . '/..'));
    define('CONF', yaml_parse_file('config/biscuit.yml'));

    if (! function_exists('fastcgi_finish_request')) {
        throw new ErrorException("Biscuit requires FastCGI to run");
    }

    $requiredFolders->each(function ($idx, $path) {
        if (! is_dir($path)) {
            mkdir($path, 755);
        }
    });

    http::capture($allowed_hosts);
}
