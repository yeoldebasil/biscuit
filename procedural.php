<?php

declare(strict_types=1);

function escape(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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
function blank(mixed & ...$vars): bool
{
    // no callback is supplied, all empty values will be removed
    return array_filter($vars) === [];
}


/**
 * Получение случайного значения в формате HEX (a-f0-9)
 */
function random_hex(int $length): string
{
    return bin2hex(
        openssl_random_pseudo_bytes($length / 2)
    );
}

/**
 * Получение случайного значения в формате
 * Англ. алфавит, цифры, нижнее дочеркивание (A-z0-9_)
 */
function random_alnumu(int $length): string
{
    $dictionary = 'A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4Y5Z6a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6_';
    $size       = strlen($dictionary);
    $key        = '';

    for ($i = 0; $i < $length; $i++) {
        $key .= $dictionary[mt_rand(0, $size - 1)];
    }

    return $key;
}

/**
 * Склоняет число $num в нужный падеж и возвращает строку
 * Пример массива $words: <яблоко, яблока, яблок>
 */
function incline_number(int|float $num, array $words): string
{
    $num = $num % 100;

    if ($num > 19) {
        $num = $num % 10;
    }

    switch ($num) {
        case 1: {
            return($words[0]);
        }
        case 2: case 3: case 4: {
            return($words[1]);
        }
        default: {
            return($words[2]);
        }
    }
}

/**
 * Изменяет числовые значения типа string в массиве
 * на числовые значения типа int.
 */
function specify_types(array $array): array
{
    foreach ($array as $key => $value) {
        if (is_string($value)) {
            if ($value == 'on') {
                $array[$key] = true;
            }

            if ($value == 'off') {
                $array[$key] = false;
            }

            if (is_numeric($value)) {
                $array[$key] = (int) $value;
            }
        }
    }

    return $array;
}