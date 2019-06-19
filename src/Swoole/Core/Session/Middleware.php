<?php

namespace CI\Swoole\Core\Session;

use Swoole\Http\Request;
use Swoole\Http\Response;

class Middleware
{
    protected $middleware;

    protected $idGenerator;

    protected $useCookies;

    protected $useOnlyCookies;

    public function __construct(
        callable $middleware,
        $idGenerator = 'session_create_id',
        $useCookies = null,
        $useOnlyCookies = null
    ) {
        $this->middleware     = $middleware;
        $this->idGenerator    = $idGenerator;
        $this->useCookies     = is_null($useCookies) ? (bool) ini_get('session.use_cookies') : $useCookies;
        $this->useOnlyCookies = is_null($useOnlyCookies) ? (bool) ini_get('session.use_only_cookies') : $useOnlyCookies;
    }

    public function __invoke(Request $request, Response $response)
    {
        $sessionName = config_item('sess_cookie_name');

        if ($this->useCookies && isset($request->cookie[$sessionName])) {
            $sessionId = $request->cookie[$sessionName];
        } elseif (!$this->useOnlyCookies && isset($request->get[$sessionName])) {
            $sessionId = $request->get[$sessionName];
        } else {
            $sessionId = call_user_func($this->idGenerator);
            // $sessionId = session_id();
        }

        if (!session_id()) {
            session_start();
        }

        if ($this->useCookies) {
            $response->cookie(
                $sessionName,
                $sessionId,
                (empty(config_item('cookie_lifetime')) ? 0 : time() + config_item('cookie_lifetime')),
                config_item('cookie_path'),
                config_item('cookie_domain'),
                config_item('cookie_secure'),
                true
            );
        }
        try {
            call_user_func($this->middleware, $request, $response);
        } finally {
            session_write_close();
            // unset($_SESSION);
        }
    }
}
