<?php

namespace CI\Swoole\Core;

class Controller
{
    private static $instance;

    public function __construct()
    {
        self::$instance = &$this;

        // Assign all the class objects that were instantiated by the
        // bootstrap file (CodeIgniter.php) to local class variables
        // so that CI can run as one big super object.

        $core = [
            'benchmark' => 'Benchmark',
            'hooks'     => 'Hooks',
            'config'    => 'Config',
            'log'       => 'Log',
            'utf8'      => 'Utf8',
            'uri'       => 'URI',
            'router'    => 'Router',
            'output'    => 'Output',
            'security'  => 'Security',
            'input'     => 'Input',
            'lang'      => 'Lang',
        ];

        foreach (is_loaded() as $var => $class) {
            if (array_key_exists($var, $core)) {
                $this->$var = &load_class($class);
            }
        }

        $this->load = &load_class('Loader', 'core');
        $this->load->initialize();
        log_message('info', 'Controller Class Initialized');
    }

    // --------------------------------------------------------------------

    public static function &get_instance()
    {
        return self::$instance;
    }
}
