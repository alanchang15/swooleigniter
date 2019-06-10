<?php

namespace CI\Swoole;

use Composer\Script\Event;

class Installer
{
    private static function showMessage(Event $event = null)
    {
        $io = $event->getIO();
        $io->write('==================================================');
        $io->write(
            '<info>`/.htaccess` was installed. If you don\'t need it, please remove it.</info>'
        );
        $io->write(
            '<info>If you want to install translations for system messages or some third party libraries,</info>'
        );
        $io->write('$ cd <codeigniter_project_folder>');
        $io->write('$ php bin/install.php');
        $io->write('<info>The above command will show help message.</info>');
        $io->write('See <https://github.com/kenjis/codeigniter-composer-installer> for details');
        $io->write('==================================================');
    }
}
