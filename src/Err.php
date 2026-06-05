<?php

declare (strict_types = 1);

namespace Biscuit;

use ErrorException;

class Err
{
    // Обработчик обычных ошибок
    public static function handle(int $errno, string $errstr, string $errfile, int $errline): void
    {
        throw new ErrorException($errstr, $errno, E_ERROR, $errfile, $errline, null);
    }

    public static function pretty(Throwable $t): void
    {

    }

    public static function json(Throwable $t): string
    {
        $data = [
            'class' => get_class($t),
            'msg'   => $t->getMessage(),
            'file'  => $t->getFile(),
            'line'  => $t->getLine(),
        ];

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
