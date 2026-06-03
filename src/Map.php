<?php

declare (strict_types = 1);

namespace Yeoldebasil\Biscuit;

use Countable;
use Iterator;
use ValueError;

class Map implements Iterator, Countable
{
    private array $value;
    private int $index = 0;

    public function __construct(...$value)
    {
        if (array_is_list($value[0])) {
            throw new ValueError("Don't use Map for list arrays");
        }

        $this->value[0] = $value;
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
        assert(array_is_list($element));
        assert(count($element) == 1);

        $this->value[] = $element;
    }

    public function json(): Str
    {
        return new Str(json_encode($this->value));
    }
}
