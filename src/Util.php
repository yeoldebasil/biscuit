<?php

declare (strict_types = 1);

namespace Biscuit;

use Biscuit\Http as http;
use Throwable;

class Util
{
    /**
     * Render response body
     * @param array $env
     * @param Throwable $t
     * @return string
     */
    public static function err(Throwable $t): void
    {
        $title   = 'Biscuit Application Error';
        $kind    = get_class($t);
        $code    = $t->getCode();
        $message = $t->getMessage();
        $file    = $t->getFile();
        $line    = $t->getLine();
        $trace   = $t->getTraceAsString();

        if (defined('BISCUIT_PATH_BEFORE_ROOT')) {
            $trace->replace(BISCUIT_PATH_BEFORE_ROOT, '');
        }

        $html  = sprintf('<h1>%s</h1>', $title);
        $html .= '<p>Приложение не смогло продолжить работу из-за следующей ошибки:</p>';
        $html .= '<h2>Details</h2>';

        if ($kind) {
            $html .= sprintf('<div><strong>Kind:</strong> %s</div>', $kind);
        }
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

        $css = 'body {margin:0;padding:32px;font:16px/1.5 Helvetica,Arial,Verdana,sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{display:inline-block;width:80px;}';

        $render = sprintf("<html><head><meta charset=\"utf-8\"><title>%s</title><style>%s</style></head><body>%s</body></html>", $title, $css, $html);

        // fallback
        // http_response_code(500);
        // header('Cache-Control: no-store');
        // echo $render;
        // fastcgi_finish_request();

        http::respond(
            code: 500,
            headers: [
                "Cache-Control: no-store",
            ],
            body: $render
        );
    }

    public static function dev(): void
    {
        ini_set('max_execution_time', '30');
        ini_set('memory_limit', '40M');
        ini_set('upload_max_filesize', '10M');
        ini_set('post_max_size', '10M');
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        ini_set('log_errors', 'on');
        ini_set('error_log', getcwd() . '/runtime/logs/php-error.log');
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
        //error_reporting(E_ALL);
    }
}
