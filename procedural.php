<?php

use Yeoldebasil\Biscuit\Http, Str, Arr;

function http(): Http 
{
    return Http::i();
}

function str($string): Str
{
    return new Str($string);
}

function arr(array $array): Arr
{
    return new Arr($array);
}