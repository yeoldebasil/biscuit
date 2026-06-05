<?php

declare (strict_types = 1);

use Biscuit\Http;

/**
 * URL Routing functions
 *
 * Partially based on Bramus\Router code
 * Copyright (c) 2013 Bram(us) Van Damme - http://www.bram.us/
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @url https://github.com/bramus/router
 */
function url(string $url, string $pattern, &$params): bool
{
    $regex   = '';
    $lastPos = 0;

    // Replace all curly braces matches {} into word patterns
    while (preg_match('/\{([^}]+)\}/', $pattern, $match, PREG_OFFSET_CAPTURE, $lastPos)) {
        // Escape anything that is not part of the placeholder
        $staticPart  = $pattern->cut($lastPos, $match[0][1] - $lastPos);
        $regex      .= preg_quote($staticPart, '#');

        // Add a capturing group for the placeholder,
        // [^/]+ matches any character except a slash.
        $regex .= '([^/]+)';

        // Move next
        $lastPos = $match[0][1] + $match[0][0]->len();
    }

    // Append any remaining static part
    $staticPart = $pattern->cut($lastPos, null);
    $regex      .= preg_quote($staticPart, '#');

    if (preg_match_all('#^' . $regex . '$#', $url, $matches, PREG_OFFSET_CAPTURE)) {
        $matches = array_slice($matches, 1);

        // Extract the matched URL parameters (and only the parameters)
        $params = array_map(function ($match, $index) use ($matches) {

            // We have a following parameter: take the substring from the current subpattern position until the next one's position (thank you PREG_OFFSET_CAPTURE)
            if (isset($matches[$index + 1]) && isset($matches[$index + 1][0]) && is_array($matches[$index + 1][0])) {
                if ($matches[$index + 1][0][1] > -1) {
                    return trim(substr($match[0][0], 0, $matches[$index + 1][0][1] - $match[0][1]), '/');
                }
            } // We have no following parameters: return the whole lot

            return isset($match[0][0]) && $match[0][1] != -1 ? trim($match[0][0], '/') : null;
        }, $matches, array_keys($matches));

        return true;
    }

    return false;
}

function get($pattern, $closure)
{
    if (http::$method != 'GET') {
        return;
    }

    if (url(http::$url, $pattern, $params)) {
        call_user_func_array($closure, $params);
    }
}

function post($pattern, $closure)
{
    if (http::$method != 'POST') {
        return;
    }

    $matches = [];

    if (url(http::$url, $pattern, $params)) {
        call_user_func_array($closure, $params);
    }
}

function dispatch($closure = null, $params = [])
{
    if (defined('BISCUIT_ENDS')) {
        return;
    }

    if (! $closure) {
        $closure = fn() => http::respond(404, [], 'Not Found');
    }

    call_user_func_array($closure, $params);
}
