<?php

declare(strict_types=1);

namespace Yeoldebasil\Biscuit;

use ReflectionClass;
use ValueError;

abstract class App
{
    public $name;
    
    protected array $jobs = [];

    public function run(Request $httpRequest): void 
    {
        $response = $this->startJobsCycle($httpRequest);

        if ($response instanceof Response) {
            $response->send();
        }        
    }

    protected function startJobsCycle(Request $httpRequest): Response
    {
        $class = $this->jobs[0];
        
        if (class_exists($class)) 
        {
            if (!(new ReflectionClass($class))->isSubclassOf('\Yeoldebasil\Biscuit\Job')) {
                throw new ValueError("That is not a Job class.");
            }

            $next = (isset($this->jobs[1])) ? $this->jobs[1] : null;
            $instance = new $class($httpRequest, $this->jobs, 0);

            return $instance->run();
        }
    }
}