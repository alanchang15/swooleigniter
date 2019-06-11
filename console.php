<?php

define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');

switch (ENVIRONMENT) {
    case 'development':
        error_reporting(-1);
        ini_set('display_errors', 1);
        break;

    case 'testing':
    case 'production':
        ini_set('display_errors', 0);
        if (version_compare(PHP_VERSION, '5.3', '>=')) {
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        } else {
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
        }
        break;

    default:
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'The application environment is not set correctly.';
        exit(1); // EXIT_ERROR
}

$system_path = '../system';

$application_folder = '../application';

$view_folder = '';

// Set the current directory correctly for CLI requests
if (defined('STDIN')) {
    chdir(dirname(__FILE__));
}

if (($_temp = realpath($system_path)) !== false) {
    $system_path = $_temp . DIRECTORY_SEPARATOR;
} else {
    // Ensure there's a trailing slash
    $system_path = strtr(
        rtrim($system_path, '/\\'),
        '/\\',
        DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR
    ) . DIRECTORY_SEPARATOR;
}

// Is the system path correct?
if (!is_dir($system_path)) {
    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: ' . pathinfo(__FILE__, PATHINFO_BASENAME);
    exit(3); // EXIT_CONFIG
}

const CI_VERSION = '3.1.9';

define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

// Path to the system directory
define('BASEPATH', $system_path);

// Path to the front controller (this file) directory
define('FCPATH', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

// Name of the "system" directory
define('SYSDIR', basename(BASEPATH));

// The path to the "application" directory
if (is_dir($application_folder)) {
    if (($_temp = realpath($application_folder)) !== false) {
        $application_folder = $_temp;
    } else {
        $application_folder = strtr(
            rtrim($application_folder, '/\\'),
            '/\\',
            DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR
        );
    }
} elseif (is_dir(BASEPATH . $application_folder . DIRECTORY_SEPARATOR)) {
    $application_folder = BASEPATH . strtr(
        trim($application_folder, '/\\'),
        '/\\',
        DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR
    );
} else {
    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo 'Your application folder path does not appear to be set correctly. Please open the following file and correct this: ' . SELF;
    exit(3); // EXIT_CONFIG
}

define('APPPATH', $application_folder . DIRECTORY_SEPARATOR);

// The path to the "views" directory
if (!isset($view_folder[0]) && is_dir(APPPATH . 'views' . DIRECTORY_SEPARATOR)) {
    $view_folder = APPPATH . 'views';
} elseif (is_dir($view_folder)) {
    if (($_temp = realpath($view_folder)) !== false) {
        $view_folder = $_temp;
    } else {
        $view_folder = strtr(
            rtrim($view_folder, '/\\'),
            '/\\',
            DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR
        );
    }
} elseif (is_dir(APPPATH . $view_folder . DIRECTORY_SEPARATOR)) {
    $view_folder = APPPATH . strtr(
        trim($view_folder, '/\\'),
        '/\\',
        DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR
    );
} else {
    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo 'Your view folder path does not appear to be set correctly. Please open the following file and correct this: ' . SELF;
    exit(3); // EXIT_CONFIG
}

define('VIEWPATH', $view_folder . DIRECTORY_SEPARATOR);

define('STORAGEPATH', FCPATH . 'storage/');
define('CACHEPATH', FCPATH . 'storage/cache/');
define('MODPATH', FCPATH . 'modules/');
define('ROOTPATH', FCPATH);
define('CISWOOLEPATH', __DIR__ . '/');

require_once CISWOOLEPATH . 'helpers/compatible.php';
require_once BASEPATH . 'core/Common.php';

$CFG = &load_class('Config', 'core');

$CFG->_config_paths = array_merge(
    [__DIR__ . '/'],
    $CFG->_config_paths
);

$CFG->load('command', true);

if ($composer_autoload = $CFG->item('composer_autoload')) {
    if ($composer_autoload === true) {
        file_exists(APPPATH . 'vendor/autoload.php')
        ? $composer = require_once APPPATH . 'vendor/autoload.php'
        : log_message('error', '$config[\'composer_autoload\'] is set to TRUE but ' . APPPATH . 'vendor/autoload.php was not found.');
    } elseif (file_exists($composer_autoload)) {
        $composer = require_once $composer_autoload;
    } else {
        log_message('error', 'Could not find the specified $config[\'composer_autoload\'] path: ' . $composer_autoload);
    }
}

if (!function_exists('get_instance')) {
    function &get_instance()
    {
        return CI\Swoole\Core\Controller::get_instance();
    }
}

$charset = strtoupper(config_item('charset'));
ini_set('default_charset', $charset);

if (extension_loaded('mbstring')) {
    define('MB_ENABLED', true);
    @ini_set('mbstring.internal_encoding', $charset);
    mb_substitute_character('none');
} else {
    define('MB_ENABLED', false);
}

if (extension_loaded('iconv')) {
    define('ICONV_ENABLED', true);
    @ini_set('iconv.internal_encoding', $charset);
} else {
    define('ICONV_ENABLED', false);
}

if (is_php('5.6')) {
    ini_set('php.internal_encoding', $charset);
}

class_alias('CI\Swoole\Core\Controller', 'CI_Controller');

$CI                      = new CI\Swoole\Core\Console;
get_instance()->composer = &$composer;

call_user_func_array([ & $CI, 'command'], [$CFG->item('command')]);
