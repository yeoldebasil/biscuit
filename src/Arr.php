<?php

declare (strict_types = 1);

namespace Biscuit;

class Arr
{
    public function count(): int
    {
        return count($this->value);
    }

    /**
     * @return int Количество элементов
     */
    public function len(): int
    {
        return count($this->value);
    }

    public function contains($self, $element): bool
    {
        return in_array($element, $self);
    }

    public function isList($self): bool
    {
        return array_is_list($self);
    }

    public function isMap($self): bool
    {
        return ! $this->isList($self);
    }

    public function each($self, $callable): void
    {
        foreach ($self as $key => $value) {
            $callable($key, $value);
        }
    }

    public function json($self, int $flags = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($self, $flags);
    }
}
