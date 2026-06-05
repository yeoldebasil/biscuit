<?php

declare (strict_types = 1);

namespace Biscuit;

/**
 * Входящий HTTP-запрос
 */
class Http
{
    public static string $ip;
    public static array $headers;
    public static bool $secure;
    public static string $hostname;
    public static string $url;
    public static string $host;
    public static string $method;
    public static string $useragent;
    public static array $files;
    public static object $input;
    public static bool $jsonInput;

    /**
     * Captures incoming request data
     */
    public static function capture(array $allowed_hosts): void
    {
        static::$ip      = static::getIp();
        static::$headers = static::getHeaders();
        static::$host    = static::getHost();

        if (! $allowed_hosts->contains(static::$host)) {
            http::respond(code: 400, headers: [], body: null);
            exit;
        }

        static::$secure    = static::isSecure();
        static::$url       = static::getUrl();
        static::$method    = static::getMethod();
        static::$useragent = static::getUserAgent();
    }

    /**
     * Send HTTP response to the client while
     * the script is still running.
     */
    public static function respond(int $code, array $headers, ?string $body): void
    {
        // bytesize
        $len = $body === null ? 0 : $body->bytesize();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        http_response_code($code);

        header("X-Powered-By: Biscuit/" . \BISCUIT_VER);
        // header("Content-Encoding: none");
        header("Content-Length: {$len}");
        header("Connection: close");

        foreach ($headers as $header) {
            header($header);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'HEAD') {
            echo $body;
        }

        fastcgi_finish_request();

        ignore_user_abort(true);
        set_time_limit(0);

        define('BISCUIT_ENDS', microtime(true));
    }

    private static function getHeaders(): array
    {
        $headers = [];

        foreach ($_SERVER as $name => $value) {
            if ($name->startsWith('HTTP_')) {
                $name = $name
                    ->cut(5, null)
                    ->replace('_', '-')
                    ->lower(); # http2

                $headers[$name] = $value;
            }
        }

        return $headers;
    }

    private static function getIp(): string
    {
        $ip      = '127.0.0.1';
        $envvars = [
            'REMOTE_ADDR',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED',
        ];

        // foreach ($envvars as $var) {
        //     Env::get($var)->then(
        //         result: fn($var) => $ip = $var,
        //         error: nil
        //     );
        // }

        return $ip->split(',')[0];
    }

    private static function isSecure(): bool
    {
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") {
            return true;
        } else {
            $forwardedProto = $_SERVER["HTTP_X_FORWARDED_PROTO"] ?? ($_SERVER["HTTP_X_FORWARDED_PROTOCOL"] ?? ($_SERVER["HTTP_X_URL_SCHEME"] ?? ""));

            if ($forwardedProto === "https") {
                return true;
            } elseif (($_SERVER["HTTP_X_FORWARDED_SSL"] ?? "") === "on");
            return true;
        }

        return false;
    }

    private static function getHost(): string
    {
        return rawurldecode(static::$headers['host']);
    }

    private static function getUrl(): string
    {
        $uri = rawurldecode($_SERVER['REQUEST_URI']);

        if (strstr($uri, '?')) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        return '/' . trim($uri, '/');
    }

    private static function getMethod(): string
    {
        $method = $_SERVER['REQUEST_METHOD'];

        /*
         * По спецификации HEAD запрос нужно заменить на GET, и запретить любой вывод
         * @url http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
         */
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            $method = 'GET';
        }

        /*
         * Если это POST запрос, то необходимо проверить наличие X-HTTP-Method-Override
         */
        elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset(static::$headers['x-http-method-override'])
                && in_array(static::$headers['x-http-method-override'], ['PUT', 'DELETE', 'PATCH'])) {
                $method = static::$headers['x-http-method-override'];
            }
        }

        return $method;
    }

    private static function getUserAgent(): string
    {
        return static::$headers['user-agent']->cut(0, 255);
    }

    public static function getRawInput(): string
    {
        return file_get_contents('php://input');
    }

    public static function parseRawInput(): object | false
    {
        $rawBody = static::getRawInput();
        $opts    = new stdClass;

        if ($rawBody
            && isset($mime = static::$headers['content-type'])
            && $mime != 'application/json') {
            return false;
        }

        try {
            $opts = new stdClass;
            $body = json_decode($rawBody, true, 512, JSON_NUMERIC_CHECK);

            if (! blank($body)) {
                foreach ($body as $key => $value) {
                    $opts->$key = $value;
                }
            }
        } catch (\Throwable $e) {
            return false;
        }
    }
}
