<?php

declare (strict_types = 1);

namespace Biscuit;

use ErrorException;

/**
 * Fulfill – это специальный объект, который содержит
 * информацию о выполненном состоянии метода.
 */
class Fulfill
{
    use Traits\Vector;

    public $result;

    public $error = false;

    private $dispatched = false;

    public function __construct($result)
    {
        if (is_string($result)) {
            $result = str($result);
        }

        if (is_array($result)) {
            $result = array_is_list($result)
                ? arr($result)
                : map($result);
        }

        if ($result instanceof \Throwable) {
            $this->error = true;
        }

        $this->result = $result;
    }

    public function then(callable $result,  ? callable $error)
    {
        if ($this->dispatched) {
            throw new ErrorException("Cannot dispatch Fulfill more than once");
        }

        $this->dispatched = true;
        return $this->error
            ? (($error === null) ?: $error($this->result))
            : $result($this->result);
    }

}
