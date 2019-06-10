<?php

namespace CI\Swoole\ToolKit;

use Symfony\Component\Console\Output\OutputInterface;

class Autoreload
{
    protected $inotify;

    protected $pid;

    protected $reloadFileTypes = ['.php' => true];

    protected $watchFiles = [];

    protected $afterNSeconds = 10;

    protected $reloading = false;

    protected $events;

    protected $rootDirs = [];

    protected $output;

    public function __construct(OutputInterface $output, $pid)
    {
        if (!extension_loaded('inotify')) {
            $output->writeln([
                '<error>Inotify extension required.</error>',
                '<comment>Please install inotify with pecl via command line.</comment>',
                '# <info>sudo pecl install inotify</info>',
            ]);
            exit(1);
        }

        $this->output = $output;

        $this->pid = $pid;

        if (posix_kill($pid, 0) === false) {
            throw new NotFoundException("Process#{$pid} not found.");
        }

        $this->inotify = inotify_init();

        $this->events = IN_MODIFY | IN_DELETE | IN_CREATE | IN_MOVE;

        swoole_event_add($this->inotify, function ($ifd) {
            $events = inotify_read($this->inotify);
            if (!$events) {
                return;
            }
            // var_dump($events);
            foreach ($events as $ev) {
                if ($ev['mask'] == IN_IGNORED) {
                    continue;
                } elseif ($ev['mask'] == IN_CREATE or $ev['mask'] == IN_DELETE or $ev['mask'] == IN_MODIFY or $ev['mask'] == IN_MOVED_TO or $ev['mask'] == IN_MOVED_FROM) {
                    $fileType = strrchr($ev['name'], '.');
                    //非重启类型
                    if (!isset($this->reloadFileTypes[$fileType])) {
                        continue;
                    }
                }
                //正在reload，不再接受任何事件，冻结5秒
                if (!$this->reloading) {
                    $this->putLog("after 5 seconds reload the server");
                    //有事件发生了，进行重启
                    swoole_timer_after($this->afterNSeconds * 500, [$this, 'reload']);
                    $this->reloading = true;
                }
            }
        });
    }

    protected function putLog($log)
    {
        $this->output->writeln([
            "[" . date('Y-m-d H:i:s') . "]\t" . $log,
        ]);
    }

    public function reload()
    {
        $this->putLog("reloading");
        //向主进程发送信号
        posix_kill($this->pid, SIGUSR1);
        //清理所有监听
        $this->clearWatch();
        //重新监听
        foreach ($this->rootDirs as $root) {
            $this->watch($root);
        }
        //继续进行reload
        $this->reloading = false;
    }

    public function addFileType($type)
    {
        $type                               = trim($type, '.');
        $this->reloadFileTypes['.' . $type] = true;
    }

    public function addEvent($inotifyEvent)
    {
        $this->events |= $inotifyEvent;
    }

    public function clearWatch()
    {
        foreach ($this->watchFiles as $wd) {
            inotify_rm_watch($this->inotify, $wd);
        }

        $this->watchFiles = array();
    }

    public function watch($dir, $root = true)
    {
        //目录不存在
        if (!is_dir($dir)) {
            throw new Exception("[$dir] is not a directory.");
        }

        //避免重复监听
        if (isset($this->watchFiles[$dir])) {
            return false;
        }

        //根目录
        if ($root) {
            $this->rootDirs[] = $dir;
        }

        $wd                     = inotify_add_watch($this->inotify, $dir, $this->events);
        $this->watchFiles[$dir] = $wd;
        $files                  = scandir($dir);

        foreach ($files as $f) {
            if ($f == '.' or $f == '..') {
                continue;
            }

            $path = $dir . '/' . $f;

            //递归目录
            if (is_dir($path)) {
                @$this->watch($path, false);
            }

            //检测文件类型
            $fileType = strrchr($f, '.');

            if (isset($this->reloadFileTypes[$fileType])) {
                $wd                      = inotify_add_watch($this->inotify, $path, $this->events);
                $this->watchFiles[$path] = $wd;
            }
        }

        return true;
    }

    public function run()
    {
        swoole_event_wait();
    }
}
