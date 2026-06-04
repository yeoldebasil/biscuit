<?php

declare (strict_types = 1);

function unless(bool $condition, callable $callback): void
{
    if ($condition) {
        $callback();
    }
}
