<?php

namespace CI\Swoole\Core;

if (file_exists(APPPATH . 'core/MY_URI.php')) {
    class URI extends \MY_URI
    {
        public function __construct()
        {
            $this->config = &load_class('Config', 'core');

            $protocol                     = $this->config->item('uri_protocol');
            empty($protocol) && $protocol = 'REQUEST_URI';

            switch ($protocol) {
                case 'AUTO': // For BC purposes only
                case 'REQUEST_URI':
                    $uri = $this->_parse_request_uri();
                    break;
                case 'QUERY_STRING':
                    $uri = $this->_parse_query_string();
                    break;
                case 'PATH_INFO':
                default:
                    $uri = isset($_SERVER[$protocol])
                    ? $_SERVER[$protocol]
                    : $this->_parse_request_uri();
                    break;
            }

            $this->_set_uri_string($uri);

            log_message('info', 'URI Class Initialized');
        }
    }
} else {
    class URI extends \CI_URI
    {
        public function __construct()
        {
            $this->config = &load_class('Config', 'core');

            $protocol                     = $this->config->item('uri_protocol');
            empty($protocol) && $protocol = 'REQUEST_URI';

            switch ($protocol) {
                case 'AUTO': // For BC purposes only
                case 'REQUEST_URI':
                    $uri = $this->_parse_request_uri();
                    break;
                case 'QUERY_STRING':
                    $uri = $this->_parse_query_string();
                    break;
                case 'PATH_INFO':
                default:
                    $uri = isset($_SERVER[$protocol])
                    ? $_SERVER[$protocol]
                    : $this->_parse_request_uri();
                    break;
            }

            $this->_set_uri_string($uri);

            log_message('info', 'URI Class Initialized');
        }
    }
}
