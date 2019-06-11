<?php

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
        return false;
    }
}

if (!function_exists('show_404')) {
    /**
     * 404 Page Handler
     *
     * This function is similar to the show_error() function above
     * However, instead of the standard error template it displays
     * 404 errors.
     *
     * @param   string
     * @param   bool
     * @return  void
     */
    function show_404($page = '', $log_error = true)
    {
        $_error = &load_class('Exceptions', 'core');

        ob_start();

        $heading = '404 Page Not Found';
        $message = 'The page you requested was not found.';

        // By default we log this, but allow a dev to skip it
        if ($log_error) {
            log_message('error', $heading . ': ' . $page);
        }

        echo $_error->show_error($heading, $message, 'error_404', 404);

        $content = ob_get_clean();

        response()->status(404);

        response()->write($content);

        response()->end();

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
