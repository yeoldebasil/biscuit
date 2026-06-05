<?php

declare (strict_types = 1);

namespace Yeoldebasil\Biscuit;

class Arr
{
    // necessary for countable
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

    public function json($self, int $flags = JSON_UNESCAPED_UNICODE): Str
    {
        return json_encode($this->value, $flags);
    }
}
