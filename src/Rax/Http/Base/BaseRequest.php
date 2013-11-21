<?php

namespace Rax\Http\Base;

use Rax\Data\Data;
use Rax\Helper\Arr;
use Rax\Http\Request;
use Rax\Data\ArrObj;
use Rax\Mvc\MatchedRoute;
use RuntimeException;

/**
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseRequest
{
    // HTTP Methods
    const GET     = 'GET';
    const POST    = 'POST';
    const PUT     = 'PUT';
    const DELETE  = 'DELETE';
    const HEAD    = 'HEAD';
    const OPTIONS = 'OPTIONS';
    const TRACE   = 'TRACE';
    const CONNECT = 'CONNECT';

    /**
     * @var array
     */
    protected $query;

    /**
     * @var array
     */
    protected $post;

    /**
     * @var array
     */
    protected $server;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var string
     */
    protected $method;

    /**
     * Is the server behind a trusted proxy?
     *
     * @var bool
     */
    protected $proxyTrusted;

    /**
     * Whitelist of trusted proxy server IPs.
     *
     * @var array
     */
    protected $trustedProxies;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var MatchedRoute
     */
    protected $routeMatch;

    /**
     * @var string
     */
    protected $controller;

    /**
     * @var string
     */
    protected $action;

    /**
     * @param array $query
     * @param array $post
     * @param array $server
     * @param array $attributes
     * @param Data  $config
     */
    public function __construct(array $query = null, array $post = null, array $server = null, array $attributes = array(), Data $config)
    {
        if (null === $query) {
            $query = $_GET;
        }

        if (null === $post) {
            $post = $_POST;
        }

        if (null === $server) {
            $server = $_SERVER;
        }

        $this->query      = $query;
        $this->post       = $post;
        $this->server     = $server;
        $this->attributes = $attributes;
        $this->config     = $config;
    }

    /**
     * @param array|string $name
     * @param mixed        $default
     * @param bool         $useDotNotation
     *
     * @return mixed
     */
    public function getQuery($name = null, $default = null, $useDotNotation = true)
    {
        return Arr::get($this->query, $name, $default, $useDotNotation);
    }

    /**
     * @param array|string $key
     * @param bool         $useDotNotation
     *
     * @return bool
     */
    public function hasQuery($key, $useDotNotation = true)
    {
        return Arr::has($this->query, $key, $useDotNotation);
    }

    /**
     * @param array|string $key
     * @param mixed        $default
     * @param bool         $useDotNotation
     *
     * @return mixed
     */
    public function getPost($key = null, $default = null, $useDotNotation = true)
    {
        return Arr::get($this->post, $key, $default, $useDotNotation);
    }

    /**
     * @param array|string $key
     * @param bool         $useDotNotation
     *
     * @return bool
     */
    public function hasPost($key, $useDotNotation = true)
    {
        return Arr::has($this->post, $key, $useDotNotation);
    }

    /**
     * Gets values from both query and post.
     *
     * @param array|string $key
     * @param mixed        $default
     * @param bool         $useDotNotation
     *
     * @return mixed
     */
    public function getParam($key = null, $default = null, $useDotNotation = true)
    {
        return $this->getPost($key, function() use($key, $default, $useDotNotation) {
            return $this->getQuery($key, $default, $useDotNotation);
        }, $useDotNotation);
    }

    /**
     * @param array|string $key
     * @param mixed        $default
     * @param bool         $useDotNotation
     *
     * @return mixed
     */
    public function getServer($key = null, $default = null, $useDotNotation = true)
    {
        return Arr::get($this->server, $key, $default, $useDotNotation);
    }

    /**
     * @param array|string $key
     * @param bool         $useDotNotation
     *
     * @return bool
     */
    public function hasServer($key, $useDotNotation = true)
    {
        return Arr::has($this->server, $key, $useDotNotation);
    }

    /**
     * @param array|string $key
     * @param mixed        $value
     * @param bool         $useDotNotation
     *
     * @return $this
     */
    public function setAttribute($key, $value = null, $useDotNotation = true)
    {
        Arr::set($this->attributes, $key, $value, $useDotNotation);

        return $this;
    }

    /**
     * @param array|string $key
     * @param mixed        $default
     * @param bool         $useDotNotation
     *
     * @return mixed
     */
    public function getAttribute($key = null, $default = null, $useDotNotation = true)
    {
        return Arr::get($this->attributes, $key, $default, $useDotNotation);
    }

    /**
     * @param array|string $key
     * @param bool         $useDotNotation
     *
     * @return bool
     */
    public function hasAttribute($key, $useDotNotation = true)
    {
        return Arr::has($this->attributes, $key, $useDotNotation);
    }

    /**
     * @param array|string $key
     * @param mixed        $default
     * @param bool         $useDotNotation
     *
     * @return mixed
     */
    public function getHeader($key = null, $default = null, $useDotNotation = true)
    {
        if (null === $this->headers) {
            $this->headers = $this->parseHeaders($this->server);
        }

        return Arr::get($this->headers, $key, $default, $useDotNotation);
    }

    /**
     * @param array|string $key
     * @param string       $delimiter
     *
     * @return bool
     */
    public function hasHeader($key, $delimiter = null)
    {
        if (null === $this->headers) {
            $this->headers = $this->parseHeaders($this->server);
        }

        return Arr::has($this->headers, $key, $delimiter);
    }

    /**
     * @param array $server
     *
     * @return array
     */
    public function parseHeaders(array $server)
    {
        $headers = array();

        foreach ($server as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $name = str_replace('_', ' ', strtolower(substr($key, 5)));
                $name = str_replace(' ', '-', ucwords($name));
            } elseif (0 === strpos($key, 'CONTENT_')) {
                $name = substr($key, 8);
                $name = 'Content-' . (($name == 'MD5') ? $name : ucfirst(strtolower($name)));
            } else {
                continue;
            }

            $headers[$name] = $value;
        }

        return $headers;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        if (null === $this->method) {
            $this->method = $this->getServer('REQUEST_METHOD', static::GET);
        }

        return $this->method;
    }

    /**
     * @param string $method
     *
     * @return bool
     */
    public function isMethod($method)
    {
        return ($method === $this->getMethod());
    }

    /**
     * @return bool
     */
    public function isPost()
    {
        return $this->isMethod(static::POST);
    }

    /**
     * Checks if the request is ajax.
     *
     *     if ($request->isAjax())
     *
     *     {{ request.isAjax() }}
     *
     * @return bool
     */
    public function isAjax()
    {
        return ('XMLHttpRequest' === $this->getServer('HTTP_X_REQUESTED_WITH'));
    }

    /**
     * Gets the user agent string.
     *
     * e.g. Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.101 Safari/537.36
     *
     *     $userAgent = $request->getUserAgent();
     *
     *     {{ request.getUserAgent() }}
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->getHeader('User-Agent');
    }

    /**
     * Gets the server ip.
     *
     * e.g. 192.168.0.25
     *
     *     $request->getServerIp();
     *
     *     {{ request.getServerIp() }}
     *
     * @return string
     */
    public function getServerIp()
    {
        return $this->getServer('SERVER_ADDR');
    }

    public function getClientIps()
    {
        $clientIp = $this->getServer('REMOTE_ADDR');

        if ($this->isProxyTrusted() && in_array($clientIp, $this->getTrustedProxies())) {
            if (!$clientIp = $this->getHeader('X-Forwarded-For')) {
                throw new RuntimeException('The client IP was not forwarded by the reverse proxy');
            }

            return trim(current(explode(',', $clientIp)));
        }

        return $clientIp;
    }

    /**
     * Gets the client's IP address.
     *
     * If the server is behind a reverse proxy, you will need to set the
     * "request.proxyTrusted" config value to "true" and add the reverse proxies
     * to the list of "request.trustedProxies". Lastly, make sure you forward
     * the client's IP through the "X-Forwarded-For" request header.
     *
     * e.g. 192.168.0.3
     *
     *     $clientIp = $request->getClientIp();
     *
     *     {{ request.getClientIp() }}
     *
     * @throws RuntimeException
     *
     * @return string
     */
    public function getClientIp()
    {
        $clientIp = $this->getServer('REMOTE_ADDR');

        if ($this->isProxyTrusted() && in_array($clientIp, $this->getTrustedProxies())) {
            if (!$clientIp = $this->getHeader('X-Forwarded-For')) {
                throw new RuntimeException('The client IP was not forwarded by the reverse proxy');
            }

            return trim(current(explode(',', $clientIp)));
        }

        return $clientIp;
    }

    /**
     * @return bool
     */
    public function isProxyTrusted()
    {
        if (null === $this->proxyTrusted) {
            $this->proxyTrusted = $this->config->get('proxyTrusted');
        }

        return $this->proxyTrusted;
    }

    /**
     * @return array
     */
    public function getTrustedProxies()
    {
        if (null === $this->trustedProxies) {
            $this->trustedProxies = $this->config->get('trustedProxies');
        }

        return $this->trustedProxies;
    }

    /**
     * @return bool
     */
    public function isSecure()
    {
        return (
            filter_var($this->getServer('HTTPS'), FILTER_VALIDATE_BOOLEAN) ||
            (
                $this->setTrustProxy() && (
                    filter_var($this->getServer('HTTP_SSL_HTTPS'), FILTER_VALIDATE_BOOLEAN) ||
                    filter_var($this->getServer('HTTP_X_FORWARDED_PROTO'), FILTER_VALIDATE_BOOLEAN)
                ))
        );
    }

    /**
     * @return string
     */
    public function getUri()
    {
        if (null === $this->uri) {
            $this->uri = $this->detectUri();
        }

        return $this->uri;
    }

    /**
     * @return string
     */
    protected function detectUri()
    {
        return rawurldecode(parse_url($this->getServer('REQUEST_URI'), PHP_URL_PATH));
    }

    /**
     * @param MatchedRoute $routeMatch
     */
    public function setRouteMatch(MatchedRoute $routeMatch)
    {
        $this->routeMatch = $routeMatch;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->routeMatch->getControllerId();
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->routeMatch->getActionId();
    }

    /**
     * @return \Rax\Mvc\MatchedRoute
     */
    public function getRouteMatch()
    {
        return $this->routeMatch;
    }
}
