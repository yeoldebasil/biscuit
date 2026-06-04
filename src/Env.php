<?php

declare (strict_types = 1);

namespace Yeoldebasil\Biscuit;

use ErrorException;

class Env
{
    /**
     * Возвращает значение одной или всех переменных окружения.
     */
    public static function get(string | str $name): Fulfill
    {
        $var = getenv($name, false);

        return is_string($var)
            ? new Fulfill($var)
            : new Fulfill(new ErrorException);
    }

    /**
     * Возвращает только локальные переменные окружения, которые установила операционная система или команда putenv
     */
    public static function local(string | str $name): Fulfill
    {
        $var = getenv($name, true);

        return is_string($var)
            ? new Fulfill($var)
            : new Fulfill(new ErrorException);
    }
}
