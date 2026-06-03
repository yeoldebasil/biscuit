<?php

declare (strict_types = 1);

namespace Yeoldebasil\Biscuit;

class Env
{
    /**
     * Возвращает значение одной или всех переменных окружения.
     */
    public static function get(string | str $name): Str | false
    {
        return is_string($var = getenv($name, false))
            ? str($var)
            : false;
    }

    /**
     * Возвращает только локальные переменные окружения, которые установила операционная система или команда putenv
     */
    public static function local(string | str $name): Str | false
    {
        return is_string($var = getenv($name, true))
            ? str($var)
            : false;
    }
}
