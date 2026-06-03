<?php

declare (strict_types = 1);

namespace Yeoldebasil\Biscuit;

use Countable;
use Iterator;
use ValueError;

class Arr implements Iterator, Countable
{
    private array $value = [];

    public function __construct(...$value)
    {
        if ($value != []) {
            $value = $value[0];
        }

        if (! array_is_list($value)) {
            throw new ValueError("Don't use Arr for hash arrays, instead use Map");
        }

        $this->value = $value;
    }

    // necessary for countable
    public function count(): int
    {
        return count($this->value);
    }

    // necessary for iterator
    public function rewind(): void
    {
        $this->index = 0;
    }

    public function current(): mixed
    {
        return $this->value[$this->index];
    }

    public function key(): mixed
    {
        return $this->value[$this->index];
    }

    public function next(): void
    {
        ++$this->index;
    }

    public function valid(): bool
    {
        return isset($this->value[$this->index]);
    }

    // necessary for property access
    public function &__get($key)
    {
        return $this->value[$key];
    }

    public function __set($key, $value)
    {
        if (is_string($value)) {
            $this->value[$key] = str($value);
        }

        $this->value[$key] = $value;
    }

    public function __isset($key)
    {
        return isset($this->value[$key]);
    }

    public function __unset($key)
    {
        unset($this->value[$key]);
    }

    public function add($element): void
    {
        $this->value[] = $element;
    }

    /**
     * @return int Количество элементов
     */
    public function len(): int
    {
        return count($this->value);
    }

    public function contains($element): bool
    {
        // in_array медленнее isset
        return in_array($element, $this->value);
    }
}
