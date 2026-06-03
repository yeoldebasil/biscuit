<?php

declare (strict_types = 1);

namespace Yeoldebasil\Biscuit;

use ErrorException;

class Err
{
    // Обработчик обычных ошибок
    public static function handle(int $errno, string $errstr, string $errfile, int $errline): void
    {
        throw new ErrorException($errstr, $errno, E_ERROR, $errfile, $errline, null);
    }
}
