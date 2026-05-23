<?php declare(strict_types=1);

namespace Yeoldebasil\Biscuit;

class Http
{
    public string $ip;
    public string $protocol;
    public string $hostname;
    public string $url;
    public string $method;
    public string $useragent;
    public array  $files;
    public object $input;

    // Singleton
    private static ?Http $i = null;

    function __construct()
    {
        if (!function_exists('getallheaders')) {
            throw new \RuntimeException("Please use an Apache2 web-server");
        }

        $this->ip        = $this->parse_ip();
        $this->protocol  = $this->parse_protocol();
        $this->hostname  = $this->parse_hostname();
        $this->url       = $this->parse_url();
        $this->method    = $this->parse_method();
        $this->useragent = $this->parse_useragent();
        $this->files     = $_FILES;
        $this->input     = $this->parse_input();
    }

    public static function i(): Http
    {
        if (!self::$i)
            self::$i = new Http();

        return self::$i;
    }

    private function parse_ip(): string
    {
        $ip = '127.0.0.1';
        $envvars = [
            'REMOTE_ADDR',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED'
        ];

        foreach ($envvars as $var) {
            if (getenv($var)) {
                $ip = getenv($var);
                break;
            }
        }

        return explode(',', $ip)[0];
    }

    private function parse_protocol(): string
    {
        $protocol = 'http';

        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") {
            $protocol = 'https';
        } else {
            $forwardedProto = $_SERVER["HTTP_X_FORWARDED_PROTO"] ?? ($_SERVER["HTTP_X_FORWARDED_PROTOCOL"] ?? ($_SERVER["HTTP_X_URL_SCHEME"] ?? ""));

            if ($forwardedProto === "https")
                $protocol = 'https';
            elseif (($_SERVER["HTTP_X_FORWARDED_SSL"] ?? "") === "on")
                $protocol = 'https';
        }

        return $protocol;
    }

    private function parse_hostname(): string
    {
        // Говорят, что небезопасно.. но мне как-то похуй
        return rawurldecode($_SERVER['HTTP_HOST']);
    }

    private function parse_url(): string
    {
        $uri = rawurldecode($_SERVER['REQUEST_URI']);

        if (strstr($uri, '?'))
            $uri = substr($uri, 0, strpos($uri, '?'));

        return '/' . trim($uri, '/');
    }

    private function parse_method(): string
    {
        $method = $_SERVER['REQUEST_METHOD'];

        /*
         * По спецификации HEAD запрос нужно заменить на GET, и запретить любой вывод
         * @url http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
         */
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_start();
            $method = 'GET';
        }

        /*
         * Если это POST запрос, то необходимо проверить наличие X-HTTP-Method-Override
         */
        elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $headers = getallheaders();

            if (isset($headers['X-HTTP-Method-Override']) && in_array($headers['X-HTTP-Method-Override'], ['PUT', 'DELETE', 'PATCH'])) {
                $method = $headers['X-HTTP-Method-Override'];
            }
        }

        return str($method)->lower()->__toString();
    }

    private function parse_useragent(): string
    {
        return str($_SERVER['HTTP_USER_AGENT'])->cut(0, 255)->__toString();
    }

    private function parse_input(): object
    {
        return (object) (
            ($this->method === 'POST') ? $_POST : $_GET
        );
    }
}
