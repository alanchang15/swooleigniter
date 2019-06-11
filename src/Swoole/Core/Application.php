<?php

namespace CI\Swoole\Core;

use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\Http\Response as SwooleHttpResponse;
use Swoole\Http\Server as SwooleHttpServer;

class Application
{
    private $_ci;

    private static $_instance = null;

    public static function forge()
    {
        if (is_null(static::$_instance)) {
            static::$_instance = new static;
        }

        return static::$_instance;
    }

    public static function reload(SwooleHttpServer $server)
    {
        require_once CISWOOLEPATH . 'swooleigniter.php';

        load_class('Utf8', 'core');
        load_class('Router', 'core');
        load_class('Output', 'core');
        load_class('Security', 'core');
        load_class('Input', 'core');
        load_class('Lang', 'core');

        $CI = new \CI\Swoole\Core\Controller;

        $CI->server = $server;

        return $CI;
    }

    private function __construct()
    {
        $this->_ci = &get_instance();
    }

    public function __get($component)
    {
        if (property_exists($this->_ci, $component)) {
            return $this->_ci->{$component};
        }
    }

    public function __set($component, $object)
    {
        $this->_ci->{$component} = $object;
    }

    public function setSwooleRequest(SwooleHttpRequest $request)
    {
        $this->request = $request;

        return $this;
    }

    public function setSwooleResponse(SwooleHttpResponse $response)
    {
        $this->response = $response;

        return $this;
    }

    public function setGlobal()
    {
        if (isset($this->request->server)) {
            foreach ($this->request->server as $key => $value) {
                $_SERVER[strtoupper($key)] = $value;
            }
            $_SERVER['SERVER_SOFTWARE'] = 'swoole-http-server';
            $_SERVER['HTTP_HOST']       = $this->request->header['host'];
        }

        if (isset($this->request->get)) {
            foreach ($this->request->get as $key => $value) {
                $_GET[$key] = $value;
            }
        }

        if (isset($this->request->post)) {
            foreach ($this->request->post as $key => $value) {
                $_POST[$key] = $value;
            }
        }

        if (isset($this->request->files)) {
            foreach ($this->request->files as $key => $value) {
                $_FILES[$key] = $value;
            }
        }

        $this->uri    = new URI;
        $this->load   = new Loader;
        $this->router = new Router;

        return $this;
    }

    public function handle()
    {
        ob_start();

        $this->hooks->call_hook('pre_system');

        $this->config->set_item('base_url', 'http://' . $_SERVER['HTTP_HOST'] . '/');

        $RTR = $this->router;

        $URI = $this->uri;

        $e404   = false;
        $class  = ucfirst($RTR->class);
        $method = $RTR->method;

        if (empty($class) or !file_exists(APPPATH . 'controllers/' . $RTR->directory . $class . '.php')) {
            $e404 = true;
        } else {
            require_once APPPATH . 'controllers/' . $RTR->directory . $class . '.php';

            if (!class_exists($class, false) or $method[0] === '_' or method_exists('CI_Controller', $method)) {
                $e404 = true;
            } elseif (method_exists($class, '_remap')) {
                $params = array($method, array_slice($URI->rsegments, 2));
                $method = '_remap';
            } elseif (!method_exists($class, $method)) {
                $e404 = true;
            } elseif (!is_callable(array($class, $method))) {
                $reflection = new ReflectionMethod($class, $method);
                if (!$reflection->isPublic() or $reflection->isConstructor()) {
                    $e404 = true;
                }
            }
        }

        if ($e404) {
            if (!empty($RTR->routes['404_override'])) {
                if (sscanf($RTR->routes['404_override'], '%[^/]/%s', $error_class, $error_method) !== 2) {
                    $error_method = 'index';
                }

                $error_class = ucfirst($error_class);

                if (!class_exists($error_class, false)) {
                    if (file_exists(APPPATH . 'controllers/' . $RTR->directory . $error_class . '.php')) {
                        require_once APPPATH . 'controllers/' . $RTR->directory . $error_class . '.php';
                        $e404 = !class_exists($error_class, false);
                    }
                    // Were we in a directory? If so, check for a global override
                    elseif (!empty($RTR->directory) && file_exists(APPPATH . 'controllers/' . $error_class . '.php')) {
                        require_once APPPATH . 'controllers/' . $error_class . '.php';
                        if (($e404 = !class_exists($error_class, false)) === false) {
                            $RTR->directory = '';
                        }
                    }
                } else {
                    $e404 = false;
                }
            }

            // Did we reset the $e404 flag? If so, set the rsegments, starting from index 1
            if (!$e404) {
                $class  = $error_class;
                $method = $error_method;

                $URI->rsegments = array(
                    1 => $class,
                    2 => $method,
                );
            } else {
                show_404($RTR->directory . $class . '/' . $method);
            }
        }

        if ($method !== '_remap') {
            $params = array_slice($URI->rsegments, 2);
        }

        $this->hooks->call_hook('pre_controller');

        // Mark a start point so we can benchmark the controller
        $this->benchmark->mark('controller_execution_time_( ' . $class . ' / ' . $method . ' )_start');

        $request  = $this->request;
        $response = $this->response;

        $CI = new $class;

        $CI->response = $response;

        $CI->request = $request;

        $this->hooks->call_hook('post_controller_constructor');

        call_user_func_array(array(&$CI, $method), $params);

        // Mark a benchmark end point
        $this->benchmark->mark('controller_execution_time_( ' . $class . ' / ' . $method . ' )_end');

        $this->hooks->call_hook('post_controller');

        if ($this->hooks->call_hook('display_override') === false) {
            $this->output->_display();
        }

        $this->hooks->call_hook('post_system');

        $content = ob_get_clean();

        $this->response->end($content);

        $this->output->set_output('');
    }
}
