<?php

if (!function_exists('app')) {
    function app($component = '')
    {
        $app = CI\Swoole\Core\Application::forge();

        if (!empty($component) and property_exists($app, $component)) {
            return $app->{$component};
        }

        return $app;
    }
}

if (!function_exists('set_status_header')) {
    /**
     * Set HTTP Status Header
     *
     * @param   int the status code
     * @param   string
     * @return  void
     */
    function set_status_header($code = 200, $text = '')
    {
        if (empty($code) or !is_numeric($code)) {
            show_error('Status codes must be numeric', 500);
        }

        if (empty($text)) {
            is_int($code) or $code = (int) $code;
            $stati                 = array(
                100 => 'Continue',
                101 => 'Switching Protocols',

                200 => 'OK',
                201 => 'Created',
                202 => 'Accepted',
                203 => 'Non-Authoritative Information',
                204 => 'No Content',
                205 => 'Reset Content',
                206 => 'Partial Content',

                300 => 'Multiple Choices',
                301 => 'Moved Permanently',
                302 => 'Found',
                303 => 'See Other',
                304 => 'Not Modified',
                305 => 'Use Proxy',
                307 => 'Temporary Redirect',

                400 => 'Bad Request',
                401 => 'Unauthorized',
                402 => 'Payment Required',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                406 => 'Not Acceptable',
                407 => 'Proxy Authentication Required',
                408 => 'Request Timeout',
                409 => 'Conflict',
                410 => 'Gone',
                411 => 'Length Required',
                412 => 'Precondition Failed',
                413 => 'Request Entity Too Large',
                414 => 'Request-URI Too Long',
                415 => 'Unsupported Media Type',
                416 => 'Requested Range Not Satisfiable',
                417 => 'Expectation Failed',
                422 => 'Unprocessable Entity',
                426 => 'Upgrade Required',
                428 => 'Precondition Required',
                429 => 'Too Many Requests',
                431 => 'Request Header Fields Too Large',

                500 => 'Internal Server Error',
                501 => 'Not Implemented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable',
                504 => 'Gateway Timeout',
                505 => 'HTTP Version Not Supported',
                511 => 'Network Authentication Required',
            );

            if (isset($stati[$code])) {
                $text = $stati[$code];
            } else {
                show_error('No status text available. Please check your status code number or supply your own message text.', 500);
            }
        }

        response()->status($code);
    }
}

if (!function_exists('redirect')) {
    function redirect($uri = '', $method = 'auto', $code = null)
    {
        if (!preg_match('#^(\w+:)?//#i', $uri)) {
            $uri = site_url($uri);
        }

        $code = 302;

        response()->redirect($uri, $code);

        exit;
    }
}

if (!function_exists('is_cli')) {
    function is_cli()
    {
        if (defined('CISWOOLEPATH')) {
            return false;
        }

        return (PHP_SAPI === 'cli' or defined('STDIN'));
    }
}

if (!function_exists('request')) {
    function request()
    {
        $app = \CI\Swoole\Core\Application::forge();
        return $app->request;
    }
}

if (!function_exists('response')) {
    function response()
    {
        $app = \CI\Swoole\Core\Application::forge();
        return $app->response;
    }
}

if (!function_exists('composer')) {
    function composer()
    {
        return $GLOBALS['composer'];
    }
}
