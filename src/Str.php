<?php

declare (strict_types = 1);

namespace Yeoldebasil\Biscuit;

use Countable;
use InvalidArgumentException;
use Iterator;
use RuntimeException;
use Stringable;

class Str implements Stringable, Iterator, Countable
{
    protected int $index = 0;

    final public function __construct(
        public string $value,
        protected ?string $encoding = null
    ) {
        if (! mb_check_encoding('', $encoding)) {
            throw new InvalidArgumentException(
                sprintf('Unsupported encoding "%s"', $encoding)
            );
        }
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    public function current(): int
    {
        return mb_substr($this->value, $this->index, null, $this->encoding);
    }

    public function key(): int
    {
        return $this->index;
    }

    public function next(): void
    {
        ++$this->index;
    }

    public function valid(): bool
    {
        return mb_str_split($this->value, 1, $this->encoding)[$this->index];
    }

    public function upper(): static
    {
        $result = mb_strtoupper($this->value, $this->encoding);
        $this->assertMbstringResult($result, 'mb_strtoupper');

        return new static($result, $this->encoding);
    }

    public function lower(): static
    {
        $result = mb_strtolower($this->value, $this->encoding);
        $this->assertMbstringResult($result, 'mb_strtoupper');

        return new static($result, $this->encoding);
    }

    public function capitalize(): static
    {
        $result = mb_ucfirst($this->value, $this->encoding);
        $this->assertMbstringResult($result, 'mb_ucfirst');

        return new static($result, $this->encoding);
    }

    public function capitalizeEachWord(): static
    {
        $result = '';
        mb_regex_encoding($this->encoding);
        mb_ereg_search_init($this->value, '(\S)(\S*\s*)|(\s+)');

        while ($match = mb_ereg_search_regs()) {
            $result .= $match[3]
                ? $match[3]
                : str($match[1])->upper() . $match[2];
        }

        $this->assertMbstringResult(
            $result,
            'mb_ereg_search_regs'
        );

        return new static($result, $this->encoding);
    }

    /**
     * Удаляет пробелы (или другие символы) из начала и конца строки,
     * также в середине строки превращает множество пробелов в один.
     */
    public function strip(): static
    {
        $result = preg_replace('/\s+/', ' ', $this->value);
        $result = trim($result);

        return new static($result);
    }

    public function cut(int $offset, ?int $length = null): static
    {
        $result = mb_substr($this->value, $offset, $length, $this->encoding);
        $this->assertMbstringResult($result, 'mb_substr');

        return new static($result, $this->encoding);
    }

    public function split(string | str $delimiter): Arr
    {
        $result = new Arr;
        $split  = explode($delimiter, $this->value);

        foreach ($split as $element) {
            $result->add(str($element, $this->encoding));
        }

        return $result;
    }

    public function replace($search, $replace): static
    {
        $result = str_replace($search, $replace, $this->value);
        return new static($result, $this->encoding);
    }

    public function encoding(string | str | null $encoding): static
    {
        if (! mb_check_encoding('', $encoding)) {
            throw new InvalidArgumentException(
                sprintf('Unsupported encoding "%s"', $encoding)
            );
        }

        $this->encoding = $encoding;
        return $this;
    }

    /**
     * Требуется для Countable
     */
    public function count(): int
    {
        return $this->len();
    }

    public function len(): int
    {
        return mb_strlen($this->value, $this->encoding);
    }

    public function contains(string $substring): bool
    {
        if ($this->value === '') {
            return false;
        }

        return mb_substr_count($this->value, $substring, $this->encoding) > 0;
    }

    public function startsWith(string $prefix): bool
    {
        $prefix_len = mb_strlen($prefix, $this->encoding);
        return mb_substr($this->value, 0, $prefix_len, $this->encoding) === $prefix;
    }

    public function endsWith(string $suffix): bool
    {
        $suffix_len = mb_strlen($suffix, $this->encoding);
        return mb_substr($this->value, -$suffix_len, null, $this->encoding) === $suffix;
    }

    public function blank(): bool
    {
        return $this->value === null || trim($this->value) === '';
    }

    public function present(): bool
    {
        return ! $this->blank();
    }

    public function isNumeric(): bool
    {
        return is_numeric($this->value);
    }

    public function __toString(): string
    {
        return $this->value;
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
                    $this->encoding
                )
            );
        }
    }
}
