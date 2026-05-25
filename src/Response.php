<?php 

declare(strict_types=1);

namespace Yeoldebasil\Biscuit;

class Response
{
    public  string  $code    = 'HTTP/1.1 200 OK';
    public  string  $body    = '';
    private array   $headers = [];

    function header(string $name, mixed $value = null): void
    {
        if (is_null($value)) {
            $this->headers[] = $name;
        }

        $this->headers[$name] = (string) $value;
    }

    function send(): void
    {
        $this->header($this->code);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        exit($this->body);
    }

    private function minify_html($html): string
    {
        $search = [
            '/(\n|^)(\x20+|\t)/',
            '/(\n|^)\/\/(.*?)(\n|$)/',
            '/\n/',
            '/\<\!--.*?-->/',
            '/(\x20+|\t)/',   # Delete multispace (Without \n)
            '/\>\s+\</',      # strip whitespaces between tags
            '/(\"|\')\s+\>/', # strip whitespaces between quotation ("') and end tags
            '/=\s+(\"|\')/'   # strip whitespaces between = "'
        ];

        $replace = [
            "\n",
            "\n",
            " ",
            "",
            " ",
            "><",
            "$1>",
            "=$1"
        ];

        return preg_replace($search, $replace, $html);
    }
}
