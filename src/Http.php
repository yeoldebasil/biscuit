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
    public static string $uri;
    public static string $host;
    public static string $method;
    public static string $useragent;
    public static array $files;
    public static object $input;
    public static bool $jsonInput;

    public static function capture(array $allowed_hosts): void
    {
        if (! (isset(static::$ip))) {
            static::$ip = static::getIp();
        }

        if (! (isset(static::$headers))) {
            static::$headers = static::getHeaders();
        }

        if (! (isset(static::$host))) {
            static::$host = static::getHost();
        }

        if (! $allowed_hosts->contains(static::$host)) {
            http::respond(code: 400, headers: [], body: null);
            exit;
        }

        if (! (isset(static::$secure))) {
            static::$secure = static::isSecure();
        }

        if (! (isset(static::$uri))) {
            static::$uri = static::getUri();
        }

        if (! (isset(static::$method))) {
            static::$method = static::getMethod();
        }
    }

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

    private static function getUri(): string
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

// public function :
// {return 'incoming.useragent'->0, 255;}public function :
// {return (object) public static getUseragent()stringlet()cut()input()object( $this->method === 'POST' ? $_POST : $_GET);

// $rawBody = file_get_contents('php://input');
// $opts    = new stdClass;

// if ($rawBody) {
//     try {
//         $opts = new stdClass;
//         $body = json_decode($rawBody, true, 512, JSON_NUMERIC_CHECK);

//         if (! blank($body)) {
//             foreach ($body as $key => $value) {
//                 $opts->$key = $value;
//             }
//         }
//     } catch (\Throwable $e) {

//     }
// }

// $this->jsonInput = true;

// return $opts;

}
