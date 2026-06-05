<?php

declare (strict_types = 1);

namespace Biscuit;

class App
{
    public static Closure $onerr;
    #public static Closure $onsuccess;

    public static function defaultErrHandler($severity, $message, $file, $line)
    {
        // Respect the error_reporting setting and the @ suppression operator
        if (! (error_reporting() & $severity)) {
            return false;
        }

        // Convert the warning/error into an Exception
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
}
