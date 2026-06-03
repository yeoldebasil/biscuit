<?php

declare (strict_types = 1);

namespace Yeoldebasil\Biscuit;

class Model
{
    public function __construct(
        private str $kind,
        private map $values,
        private map $scheme
    ) {}

    public function dispence()
    {

    }

    public function validate(): int
    {
        // Проверка на соответствие схеме..
    }

    public function json(): str
    {

    }
}
