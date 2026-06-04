<?php

declare (strict_types = 1);

namespace Yeoldebasil\Biscuit\Traits;

use BadMethodCallException;
use Yeoldebasil\Biscuit\Str;

trait Vector
{
    public function __call(string $method, array $args)
    {
        echo("Calling object method '$method' " . implode(', ', $args) . "\n");

        if (is_string($this->result) && method_exists(Str::class, $method)) {
            return new Str($this->result)->{$method}(...$args);
        } elseif (($this->result instanceof Str) && method_exists(Str::class, $method)) {
            return $this->result->{$method}(...$args);
        }

        throw new BadMethodCallException("Unknown method $method");
        // if (method_exists($this, $method)) {
        //     return call_user_method_array($method, $this, $args);
        // }

        // if (is_string($this->return)) {
        //     return str($this->return);
        // }

        // return str($this->return ?? '')->{$method}(...$args);
    }
}
