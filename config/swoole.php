<?php

$config = [
    'host'             => '127.0.0.1',
    'port'             => 1215,
    'gzip'             => 1,
    'gzip_min_length'  => 1024,
    'static_resources' => true,
    'pid_file'         => FCPATH . 'storage/app/swoole-http.pid',
    'stats_uri'        => '/swoole-http-status',
    'request_log_path' => FCPATH . 'storage/logs/',
    'root_dir'         => FCPATH,
    'public_dir'       => FCPATH,
    'max_coroutine'    => 10,

    'worker_num'       => 1,
    'max_conn'         => 255,
    'daemonize'        => false,
    'log_file'         => FCPATH . 'storage/logs/swoole-http.log',
];
