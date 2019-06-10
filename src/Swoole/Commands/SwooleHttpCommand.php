<?php

namespace CI\Swoole\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SwooleHttpCommand extends Command
{
    protected static $defaultName = 'swoole:http';

    protected $_ci;

    protected $config;

    protected function configure()
    {
        $this->_ci = get_instance();

        $this->_ci->config->load('swoole', true);

        $this->config = $this->_ci->config->item('swoole');

        $this->setDescription('Swoole http server command.');

        $this->setHelp('php command swoole:http start|stop|restart|reload|status|auto-reload');

        $this->addArgument('action');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        switch ($input->getArgument('action')) {
            case 'start':
                $this->startService($output);
                break;

            case 'stop':
                $this->stopService($output);
                break;

            case 'restart':
                $this->restartService($output);
                break;

            case 'reload':
                $this->reloadService($output);
                break;

            case 'status':
                $this->checkService($output);
                break;

            case 'auto-reload':
                $this->autoReloadService($output);
                break;

            default:
                $output->writeln([
                    '<comment>Usage:',
                    '',
                    $this->getHelp(),
                    '</comment>',
                ]);
                exit(1);
                break;
        }
    }

    protected function getPid()
    {
        $pid_file = $this->config['pid_file'];

        if (file_exists($pid_file)) {
            $pid = (int) file_get_contents($pid_file);
            if (posix_getpgid($pid)) {
                return $pid;
            } else {
                unlink($pid_file);
            }
        }

        return false;
    }

    protected function sendSignal(OutputInterface $output, $signal)
    {
        if ($pid = $this->getPid()) {
            posix_kill($pid, $signal);
            return true;
        } else {
            $output->writeln('<comment>Swoole http server is not running</comment>');
            exit(1);
        }

        return false;
    }

    protected function startService(OutputInterface $output)
    {
        $server = new \CI\Swoole\Server\Manager($this->config);

        if ($this->getPid()) {
            $output->writeln('<comment>Swoole http server is already running</comment>');
            exit(1);
        }

        $output->writeln([
            '<comment>Starting swoole http server...</comment>',
            sprintf(
                '<comment>Swoole http server started: <http://%s:%s></comment>',
                $this->config['host'],
                $this->config['port']
            ),
        ]);

        $server->start();
    }

    protected function restartService(OutputInterface $output)
    {
        $time = 0;
        $pid  = $this->getPid();

        $this->sendSignal($output, SIGTERM);

        while (posix_getpgid($pid) && $time <= 10) {
            sleep(1);
            $time++;
        }

        if ($time > 10 && posix_getpgid($pid)) {
            $output->writeln('<comment>Swoole http server stop timeout</comment>');
            exit(1);
        }

        $this->startService($output);
    }

    protected function reloadService(OutputInterface $output)
    {
        $this->sendSignal($output, SIGUSR1);
    }

    protected function autoReloadService(OutputInterface $output)
    {
        $pid = $this->getPid();

        if ($pid) {
            $kit = new \CI\Swoole\ToolKit\AutoReload($output, $pid);
            $kit->watch($this->config['root_dir']);
            $kit->addFileType('.php');
            $kit->run();
        } else {
            $output->writeln('<comment>Swoole http server is not running</comment>');
            exit(1);
        }
    }

    protected function stopService(OutputInterface $output)
    {
        $time = 0;
        $pid  = $this->getPid();

        $this->sendSignal($output, SIGTERM);

        while (posix_getpgid($pid) && $time <= 10) {
            sleep(1);
            $time++;
        }

        if ($time > 10 && posix_getpgid($pid)) {
            $output->writeln('<comment>Swoole http server stop timeout</comment>');
            exit(1);
        }

        exit(0);
    }

    protected function checkService(OutputInterface $output)
    {
        $pid = $this->getPid();

        if ($pid) {
            $output->writeln('<comment>Swoole http server is running</comment>');
        } else {
            $output->writeln('<comment>Swoole http server is not running</comment>');
        }
    }
}
