<?php

$config['name'] = 'Console Tool';

$config['version'] = '1.0';

$config['commands'] = [
    \CI\Swoole\Commands\SwooleHttpCommand::class,
];
