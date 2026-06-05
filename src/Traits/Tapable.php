<?php

declare (strict_types = 1);

namespace Biscuit\Traits;

trait Tapable
{
    public function tap(callable $block): static
    {
        $block($this);
        return $this;
    }
}
