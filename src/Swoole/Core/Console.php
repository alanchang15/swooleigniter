<?php

namespace CI\Swoole\Core;

use Symfony\Component\Console\Application as ConsoleApplication;

class Console extends Controller
{
    public function command($config = [])
    {
        try {
            $console = new ConsoleApplication(
                $config['name'],
                $config['version']
            );

            if ($app_commands = $this->config->item('commands')) {
                $commands = array_merge($config['commands'], $app_commands);
            } else {
                $commands = $config['commands'];
            }

            array_map(function ($command) use ($console) {
                $console->add(new $command);
            }, $commands);

            $console->run();
        } catch (\Throwable $e) {
            echo $e->getMessage() . PHP_EOL . $e->getTraceAsString();
        }
    }
}
