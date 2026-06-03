<?php

declare (strict_types = 1);

namespace Yeoldebasil\Biscuit;

use stdClass;

/**
 * Входящий HTTP-запрос
 */
class Http
{
    public static str $ip;
    public static object $headers;
    public static bool $secure;
    public static str $hostname;
    public static str $uri;
    public static str $host;
    public static str $method;
    public static str $useragent;
    public static array $files;
    public static object $input;
    public static bool $jsonInput;

    public static function capture(arr $allowed_hosts): void
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

    public static function respond(int $code, array | arr $headers, str | string | null $body): void
    {
        if (is_string($body)) {
            $body = str($body);
        }

        // bytesize
        $len = $body == nil ? 0 : $body->encoding('8bit')->len();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        http_response_code($code);

        header("X-Powered-By: Biscuit/" . BISCUIT_VER);
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

    private static function getHeaders(): object
    {
        // todo: replace stdclass with map
        $headers = new stdClass;

        foreach ($_SERVER as $name => $value) {
            if (($name = str($name))->startsWith('HTTP_')) {
                $name = $name
                    ->cut(5, null)
                    ->replace('_', '-')
                    ->lower(); # http2

                $headers->{$name} = $value;
            }
        }

        return $headers;
    }

    public static function getIp(): str
    {
        $ip      = str('127.0.0.1');
        $envvars = arr(
            'REMOTE_ADDR',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED',
        );

        // foreach ($envvars as $var) {
        //     (env::get($var)->blank())
        //         ?: $ip = str($var);
        // }

        return $ip->split(',')->{0};
    }

    public static function isSecure(): bool
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

    public static function getHost(): str
    {
        return str(rawurldecode(static::$headers->{'host'}));
    }

    private static function getUri(): str
    {
        $uri = rawurldecode($_SERVER['REQUEST_URI']);

        if (strstr($uri, '?')) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        return str('/' . trim($uri, '/'), 'ascii');
    }

    private static function getMethod(): str
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
            if (static::$headers?->{'X-HTTP-Method-Override'}
                && in_array($headers['X-HTTP-Method-Override'], ['PUT', 'DELETE', 'PATCH'])) {
                $method = $headers['X-HTTP-Method-Override'];
            }
        }

        return str($method);
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
