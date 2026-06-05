<?php

declare (strict_types = 1);

namespace Biscuit;

use Closure;

class Fmt
{
    public static Closure $onlog;

    public static function log(...$values): str
    {
        $result = '-> ';
        $len    = count($values);

        foreach ($values as $idx => $value) {
            if (is_bool($value)) {
                $result .= $value ? 'true' : 'false';
            } elseif (is_string($value) or is_subclass_of($value, 'Stringable')) {
                $result .= "'$value'";
            } elseif (is_array($value) or is_object($value)) {
                $result .= json_encode($value, JSON_UNESCAPED_UNICODE);
            } elseif (is_null($value)) {
                $result .= 'null';
            } else {
                $result .= $value;
            }

            if ($idx < $len - 1) {
                $result .= ", ";
            }
        }

        $result .= PHP_EOL;

        if (isset(static::$onlog)) {
            call_user_func(static::$onlog, $result);
        } else {
            echo($result);
        }

        return str($result);
    }

    public static function html($html): string
    {
        $search = [
            '/(\n|^)(\x20+|\t)/',
            '/(\n|^)\/\/(.*?)(\n|$)/',
            '/\n/',
            '/\<\!--.*?-->/',
            '/(\x20+|\t)/',   # Delete multispace (Without \n)
            '/\>\s+\</',      # strip whitespaces between tags
            '/(\"|\')\s+\>/', # strip whitespaces between quotation ("') and end tags
            '/=\s+(\"|\')/',  # strip whitespaces between = "'
        ];

        $replace = [
            "\n",
            "\n",
            " ",
            "",
            " ",
            "><",
            "$1>",
            "=$1",
        ];

        return preg_replace($search, $replace, $html);
    }
}
