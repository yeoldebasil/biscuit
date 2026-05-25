<?php

declare(strict_types=1);

namespace Yeoldebasil\Biscuit\Jobs;

use Yeoldebasil\Biscuit\{Job, Request, Response};
use Throwable;

class HandleErrors extends Job
{
    function run(): Response
    {
        try {
            return $this->next();
        } catch (Throwable $e) {
            exit($this->renderMessage($e));
        }
    }

    /**
     * Render response body
     * @param array $env
     * @param Throwable $throwable
     * @return string
     */
    protected function renderMessage(Throwable $throwable)
    {
        $title   = 'Biscuit Application Error';
        $code    = $throwable->getCode();
        $message = $throwable->getMessage();
        $file    = $throwable->getFile();
        $line    = $throwable->getLine();
        $trace   = $throwable->getTraceAsString();

        $html    = sprintf('<h1>%s</h1>', $title);
        $html   .= '<p>Приложение не смогло продолжить работу из-за следующей ошибки:</p>';
        $html   .= '<h2>Details</h2>';

        if ($code) {
            $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        }
        if ($message) {
            $html .= sprintf('<div><strong>Message:</strong> %s</div>', $message);
        }
        if ($file) {
            $html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
        }
        if ($line) {
            $html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
        }
        if ($trace) {
            $html .= '<h2>Trace</h2>';
            $html .= sprintf('<pre>%s</pre>', $trace);
        }

        return sprintf("<html><head><meta charset=\"utf-8\"><title>%s</title><style>body{margin:0;padding:30px;font:13px/1.5 Helvetica,Arial,Verdana,sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{display:inline-block;width:65px;}</style></head><body>%s</body></html>", $title, $html);
    }
}
