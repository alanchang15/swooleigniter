<?php

namespace CI\Swoole\Server;

use CI\Swoole\Core\Application as CISwooleApplication;
use Swoole\Http\Server as SwooleHttpServer;

class Manager
{
    protected static $_server = null;

    protected $app;

    protected $config = [];

    protected $worker_log_file;

    protected $coroutine_num = 0;

    public $max_coroutine_num = 0;

    protected $events = [
        'start',
        'shutDown',
        'workerStart',
        'workerStop',
        'packet',
        'bufferFull',
        'bufferEmpty',
        'task',
        'finish',
        'pipeMessage',
        'workerError',
        'managerStart',
        'managerStop',
        'request',
    ];

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    protected static function createSwooleHttpServer($config)
    {
        if (is_null(static::$_server)) {
            static::$_server = new SwooleHttpServer($config['host'], $config['port'], SWOOLE_PROCESS, SWOOLE_SOCK_TCP);

            static::$_server->set([
                'worker_num' => $config['worker_num'],
                'max_conn'   => $config['max_conn'],
                'daemonize'  => $config['daemonize'],
                'log_file'   => $config['log_file'],
            ]);
        }

        return static::$_server;
    }

    protected function camelCase($value)
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));
        $value = str_replace(' ', '', $value);
        return lcfirst($value);
    }

    public function start()
    {
        $server = static::createSwooleHttpServer($this->config);

        foreach ($this->events as $event) {
            $listener = $this->camelCase("on_{$event}");
            $callback = (method_exists($this, $listener)) ? [$this, $listener] : function () use ($listener) {
                // todo
            };

            if ($event == 'request') {
                $server->on($event, new \CI\Swoole\Core\Session\Middleware($callback));
            } else {
                $server->on($event, $callback);
            }
        }

        $server->start();
    }

    public function onStart($server)
    {
        file_put_contents(
            $this->config['pid_file'],
            $server->master_pid
        );
    }

    public function onShutDown()
    {
        unlink($this->config['pid_file']);
    }

    public function onWorkerStart($server, $worker_id)
    {
        $this->app = CISwooleApplication::forge()->reload($server);

        if ($this->config['request_log_path']) {
            $this->worker_log_file = $this->config['request_log_path'] . date('Y-m-d') . '_' . $worker_id . '.log';
        }

        $this->coroutine_num     = 0;
        $this->max_coroutine_num = $this->config['max_coroutine'];
    }

    public function onWorkerStop($server, $worker_id)
    {
        $this->worker_log_file = null;
    }

    public function onRequest($request, $response)
    {
        ini_set('display_errors', 1);
        error_reporting(-1);

        $request->server['server_name'] = 'swoole-http-server';

        if ($this->config['stats_uri'] &&
            $request->server['request_uri'] === $this->config['stats_uri']
        ) {
            if ($this->statsJson($request, $response)) {
                return;
            }
        }

        if ($this->config['static_resources']) {
            if ($this->staticResource($request, $response)) {
                return;
            }
        }

        //go(function () use ($request, $response) {
        \Swoole\Coroutine::create(function () use ($request, $response) {
            try {
                $this->app
                    ->setSwooleRequest($request)
                    ->setSwooleResponse($response)
                    ->setGlobal()
                    ->handle();
            } catch (ErrorException $e) {
                $this->logServerError($e);
            } catch (\Swoole\ExitException $e) {
                assert($e->getStatus() === 1);
                assert($e->getFlags() === SWOOLE_EXIT_IN_COROUTINE);
                $this->app->server->reload();
                return;
            }
            // catch (\Throwable $e) {
            //     $response->end($e->getTraceAsString());
            // }
        });
    }

    protected function statsJson($request, $response)
    {
        $stats                  = static::$_server->stats();
        $stats['coroutine_num'] = $this->coroutine_num;

        $response->header('Content-Type', 'application/json');
        $response->end(json_encode($stats));

        $this->logHttpRequest($request, 200);

        return true;
    }

    protected function staticResource($request, $response)
    {
        $public_dir = $this->config['public_dir'];
        $uri        = $request->server['request_uri'];
        $file       = realpath($public_dir . $uri);
        $status     = 200;

        if (is_file($file)) {
            if (!strncasecmp($file, $uri, strlen($public_dir))) {
                $status = 403;
                $response->status($status);
                $response->end();
            } else {
                $status = 200;
                $response->status($status);
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                $response->header('Content-Type', sprintf('text/%s', $ext));
                $response->sendfile($file);
            }

            $this->logHttpRequest($request, $status);

            return true;
        }

        return false;
    }

    public function logHttpRequest($request, $status)
    {
        if ($this->worker_log_file) {
            $log = array_merge($request->header, $request->server, ['status' => $status]);
            file_put_contents($this->worker_log_file, json_encode($log));
        }
    }

    public function logServerError(ErrorException $e)
    {
        $prefix = sprintf("[%s #%d *%d]\tERROR\t", date('Y-m-d H:i:s'), static::$_server->master_pid, static::$_server->worker_id);
        fwrite(STDOUT, sprintf('%s%s(%d): %s', $prefix, $e->getFile(), $e->getLine(), $e->getMessage()) . PHP_EOL);
    }
}
