<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (file_exists(APPPATH . 'config/' . ENVIRONMENT . '/constants.php')) {
    require_once APPPATH . 'config/' . ENVIRONMENT . '/constants.php';
}

if (file_exists(APPPATH . 'config/constants.php')) {
    require_once APPPATH . 'config/constants.php';
}

require_once BASEPATH . 'core/Common.php';

set_error_handler('_error_handler');
set_exception_handler('_exception_handler');
register_shutdown_function('_shutdown_handler');

$BM = &load_class('Benchmark', 'core');
$BM->mark('total_execution_time_start');
$BM->mark('loading_time:_base_classes_start');

$EXT = &load_class('Hooks', 'core');
$CFG = &load_class('Config', 'core');

require_once BASEPATH . 'core/compat/mbstring.php';
require_once BASEPATH . 'core/compat/hash.php';
require_once BASEPATH . 'core/compat/password.php';
require_once BASEPATH . 'core/compat/standard.php';

if (file_exists(APPPATH . 'core/' . $CFG->config['subclass_prefix'] . 'Controller.php')) {
    require_once APPPATH . 'core/' . $CFG->config['subclass_prefix'] . 'Controller.php';
}

if (!file_exists(FCPATH . 'storage')) {
    mkdir(FCPATH . 'storage', 0777);
}

if (!file_exists(FCPATH . 'storage/app')) {
    mkdir(FCPATH . 'storage/app', 0777);
}

if (!file_exists(FCPATH . 'storage/logs')) {
    mkdir(FCPATH . 'storage/logs', 0777);
}
