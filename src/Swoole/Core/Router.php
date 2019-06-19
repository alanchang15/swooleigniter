<?php

namespace CI\Swoole\Core;

if (file_exists(APPPATH . 'core/MY_Router.php')) {
    class Router extends \MY_Router
    {
        public function __construct($routing = null)
        {
            $this->config = &load_class('Config', 'core');
            $this->uri    = new URI;

            $this->enable_query_strings = ($this->config->item('enable_query_strings') === true);

            // If a directory override is configured, it has to be set before any dynamic routing logic
            is_array($routing) && isset($routing['directory']) && $this->set_directory($routing['directory']);
            $this->_set_routing();

            // Set any routing overrides that may exist in the main index file
            if (is_array($routing)) {
                empty($routing['controller']) or $this->set_class($routing['controller']);
                empty($routing['function']) or $this->set_method($routing['function']);
            }

            log_message('info', 'Router Class Initialized');
        }
    }
} else {
    class Router extends \CI_Router
    {
        public function __construct($routing = null)
        {
            $this->config = &load_class('Config', 'core');
            $this->uri    = new URI;

            $this->enable_query_strings = ($this->config->item('enable_query_strings') === true);

            // If a directory override is configured, it has to be set before any dynamic routing logic
            is_array($routing) && isset($routing['directory']) && $this->set_directory($routing['directory']);
            $this->_set_routing();

            // Set any routing overrides that may exist in the main index file
            if (is_array($routing)) {
                empty($routing['controller']) or $this->set_class($routing['controller']);
                empty($routing['function']) or $this->set_method($routing['function']);
            }

            log_message('info', 'Router Class Initialized');
        }
    }
}
