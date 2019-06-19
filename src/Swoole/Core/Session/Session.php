<?php

namespace CI\Swoole\Core\Session;

use CI_Session as BaseSession;

class Session extends BaseSession
{
    public function __construct(array $params = [])
    {
        $this->_ci_init_vars();

        log_message('info', "Session: Class initialized using '" . $this->_driver . "' driver.");
    }
}
