<?php 

declare(strict_types=1);

namespace Yeoldebasil\Biscuit;

/**
 * Задание (Job)
 */
abstract class Job
{
    function __construct(
        protected Request $httpRequest,
        protected array   $jobRoster,
        protected int     $position
    )
    {}

    /**
     * Выполнить задание
     */
    abstract function run(): Response;

    /**
     * Перейти к выполнению следующего задания
     */
    function next()
    {
        $total = sizeof($this->jobRoster) - 1;
        $nextpos = $this->position + 1;

        if ($this->position < $total) {
            $class = $this->jobRoster[$nextpos];

            $instance = new $class($this->httpRequest, $this->jobRoster, $nextpos);
            return $instance->run();
        }
    }
}
