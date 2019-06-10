<?php

namespace CI\Swoole\Core;

if (file_exists(APPPATH . 'core/MY_Router.php')) {
    class Router extends \MY_Router
    {

    }
} else {
    class Router extends \CI_Router
    {

    }
}
