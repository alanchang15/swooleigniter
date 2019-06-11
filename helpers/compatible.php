<?php

if (!function_exists('redirect')) {
    function redirect($uri = '', $method = 'auto', $code = null)
    {
        $app = \CI\Swoole\Core\Application::forge();

        if (!preg_match('#^(\w+:)?//#i', $uri)) {
            $uri = site_url($uri);
        }

        $code = 302;

        $app->response->redirect($uri, $code);

        exit;
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
