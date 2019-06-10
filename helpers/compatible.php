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
