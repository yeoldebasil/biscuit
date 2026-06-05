<?php

declare (strict_types = 1);

namespace Biscuit;

use RuntimeException;

/**
 * Unicode safe (kinda) string operations class
 */
class Str
{
    public function is($self, $comparing_to): bool
    {
        return $self === $comparing_to;
    }

    public function upper($self): string
    {
        $result = mb_strtoupper($self, BISCUIT_ENCODING);
        //$this->assertMbstringResult($result, 'mb_strtoupper');

        return $result;
    }

    public function lower($self): string
    {
        $result = mb_strtolower($self, BISCUIT_ENCODING);
        //$this->assertMbstringResult($result, 'mb_strtoupper');

        return $result;
    }

    public function capitalize($self): string
    {
        $result = mb_ucfirst($self, BISCUIT_ENCODING);
        //$this->assertMbstringResult($result, 'mb_ucfirst');

        return $result;
    }

    public function capitalizeEachWord($self): string
    {
        $result = '';
        mb_regex_encoding(BISCUIT_ENCODING);
        mb_ereg_search_init($self, '(\S)(\S*\s*)|(\s+)');

        while ($match = mb_ereg_search_regs()) {
            $result .= $match[3]
                ? $match[3]
                : $match[1]->upper() . $match[2];
        }

        //$this->assertMbstringResult($result,'mb_ereg_search_regs');

        return $result;
    }

    /**
     * Удаляет пробелы (или другие символы) из начала и конца строки,
     * также в середине строки превращает множество пробелов в один.
     */
    public function strip($self): string
    {
        $result = preg_replace('/\s+/', ' ', $self);
        $result = trim($result);

        return $result;
    }

    public function cut($self, int $offset, ?int $length = null): string
    {
        $result = mb_substr($self, $offset, $length, BISCUIT_ENCODING);
        //$this->assertMbstringResult($result, 'mb_substr');

        return $result;
    }

    public function split($self, string $delimiter): array
    {
        return explode($delimiter, $self);
    }

    public function replace($self, $search, $replace): string
    {
        return str_replace($search, $replace, $self);
    }

    public function len($self): int
    {
        $result = mb_strlen($self, BISCUIT_ENCODING);
        //$this->assertMbstringResult($result, 'mb_strlen');

        return $result;
    }

    public function bytesize($self): int
    {
        $result = mb_strlen($self, '8bit');
        //$this->assertMbstringResult($result, 'mb_strlen');

        return $result;
    }

    public function contains($self, $selfubstring): bool
    {
        if ($self === '') {
            return false;
        }

        return mb_substr_count($self, $substring, BISCUIT_ENCODING) > 0;
    }

    public function startsWith($self, string $prefix): bool
    {
        $prefix_len = mb_strlen($prefix, BISCUIT_ENCODING);
        return mb_substr($self, 0, $prefix_len, BISCUIT_ENCODING) === $prefix;
    }

    public function endsWith($self, $suffix): bool
    {
        $suffix_len = mb_strlen($suffix, BISCUIT_ENCODING);
        return mb_substr($self, -$suffix_len, null, BISCUIT_ENCODING) === $suffix;
    }

    public function blank($self): bool
    {
        return $self === null || trim($self) === '';
    }

    public function present($self): bool
    {
        return ! $this->blank($self);
    }

    public function isNumeric($self): bool
    {
        return is_numeric($self);
    }

/**
 * Проверяет, что вызов mbstring-функции не вернул false (ошибку).
 *
 * @param mixed  $result   Результат mbstring-функции.
 * @param string $function Имя метода.
 *
 * @throws RuntimeException
 */
    private function assertMbstringResult(mixed $result, string $function): void
    {
        if ($result === false) {
            throw new \RuntimeException(
                sprintf(
                    'Error calling %s with "%s" encoding.',
                    $function,
                    BISCUIT_ENCODING
                )
            );
        }
    }
}
