<?php

namespace CI\Swoole\Core;

if (file_exists(APPPATH . 'core/MY_Loader.php')) {
    class Loader extends \MY_Loader
    {

    }
} else {
    class Loader extends \CI_Loader
    {

    }
}
