<?php

namespace Rax\Http\Base;

use Rax\Config\Config;
use Rax\Helper\Arr;
use Rax\Http\Request;
use Rax\Config\ArrObj;
use Rax\Mvc\MatchedRoute;
use RuntimeException;
use UnexpectedValueException;

/**
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseRequest
{
    // HTTP methods
    const GET     = 'GET';
    const POST    = 'POST';
    const PUT     = 'PUT';
    const DELETE  = 'DELETE';
    const HEAD    = 'HEAD';
    const OPTIONS = 'OPTIONS';
    const TRACE   = 'TRACE';
    const CONNECT = 'CONNECT';

    /**
     * @var ArrObj
     */
    protected $config;

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
     * @param Config  $config
     * @param array $query
     * @param array $post
     * @param array $server
     * @param array $attributes
     */
    public function __construct(Config $config, array $query = null, array $post = null, array $server = null, array $attributes = array())
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

        $this->config     = $config->get('request');
        $this->query      = $query;
        $this->post       = $post;
        $this->server     = $server;
        $this->attributes = $attributes;
    }

    /**
     * @param array|string $key
     * @param mixed        $default
     * @param bool         $useDotNotation
     *
     * @return mixed
     */
    public function getQuery($key = null, $default = null, $useDotNotation = true)
    {
        return Arr::get($this->query, $key, $default, $useDotNotation);
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
    public function get($key = null, $default = null, $useDotNotation = true)
    {
        $fallback = function() use($key, $default, $useDotNotation) {
            return $this->getQuery($key, $default, $useDotNotation);
        };

        return $this->getPost($key, $fallback, $useDotNotation);
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
     * Gets a request header value.
     *
     * Use Camel-Dash for the header name, e.g. "User-Agent", not "user_agent".
     *
     *     $userAgent = $request->getHeader('User-Agent');
     *
     * @link http://en.wikipedia.org/wiki/List_of_HTTP_header_fields#Requests
     *
     * @param array|string $key
     * @param mixed        $default
     * @param bool         $useDotNotation
     *
     * @return mixed
     */
    public function getHeader($key = null, $default = null, $useDotNotation = true)
    {
        if (null === $this->headers) {
            $this->loadHeaders($this->server);
        }

        return Arr::get($this->headers, $key, $default, $useDotNotation);
    }

    /**
     * Checks if a request header exists.
     *
     *     if ($request->hasHeader('Cache-Control')) {
     *
     * @param array|string $key
     * @param bool         $useDotNotation
     *
     * @return bool
     */
    public function hasHeader($key, $useDotNotation = true)
    {
        if (null === $this->headers) {
            $this->loadHeaders($this->server);
        }

        return Arr::has($this->headers, $key, $useDotNotation);
    }

    /**
     * Loads the request headers.
     *
     * Normalizes the header names: HTTP_USER_AGENT -> User-Agent
     *
     * @link http://en.wikipedia.org/wiki/List_of_HTTP_header_fields#Requests
     *
     * @param array $server
     *
     * @return $this
     */
    protected function loadHeaders(array $server)
    {
        foreach ($server as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                // e.g. HTTP_USER_AGENT -> User-Agent
                $name = substr($key, 5);
                $name = str_replace('_', ' ', strtolower($name));
                $name = str_replace(' ', '-', ucwords($name));
            } elseif (0 === strpos($key, 'CONTENT_')) {
                // e.g. CONTENT_LENGTH -> Content-Length
                $name = substr($key, 8);
                $name = ('MD5' === $name) ? $name : ucfirst(strtolower($name));
                $name = 'Content-'.$name;
            } else {
                continue;
            }

            $this->headers[$name] = $value;
        }

        return $this;
    }

    /**
     * Gets the request method.
     *
     *     $method = $request->getMethod();
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html
     *
     * @return string E.g. "POST".
     */
    public function getMethod()
    {
        return $this->getServer('REQUEST_METHOD', static::GET);
    }

    /**
     * Checks if the supplied method is the current request method.
     *
     * Use UPPERCASE for the method name, e.g. "POST", not "post".
     *
     *     if ($request->isMethod(Request::POST)) {
     *
     * @param string $method
     *
     * @return bool
     */
    public function isMethod($method)
    {
        return ($method === $this->getMethod());
    }

    /**
     * Checks if the request method is "POST".
     *
     *     if ($request->isPost()) {
     *
     * @return bool
     */
    public function isPost()
    {
        return $this->isMethod(static::POST);
    }

    /**
     * Checks if the request was done through Ajax.
     *
     *     if ($request->isAjax()) {
     *
     * @return bool
     */
    public function isAjax()
    {
        return ('XMLHttpRequest' === $this->getHeader('X-Requested-With'));
    }

    /**
     * Gets the user agent string.
     *
     *     $userAgent = $request->getUserAgent();
     *
     * @return string E.g. "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWeb...".
     */
    public function getUserAgent()
    {
        return $this->getHeader('User-Agent');
    }

    /**
     * Gets the server's IP address.
     *
     *     $serverIp = $request->getServerIp();
     *
     * @return string E.g. "192.168.0.25".
     */
    public function getServerIp()
    {
        return $this->getServer('SERVER_ADDR');
    }

    /**
     * Gets the client IP address, or IP address chain in case the server is
     * behind a proxy.
     *
     * If the server is behind a proxy, it is your responsibility to forward
     * the real client IP from the proxy using the X-Forwarded-For header in the
     * following manner:
     *
     *     X-Forwarded-For: Client IP, Proxy IP 1, Proxy IP 2, etc
     *
     * E.g. if you're using Varnish as a reverse caching proxy and it is the
     * only proxy between the client and the web server, then you can use the
     * following config:
     *
     *     set req.http.X-Forwarded-For = client.ip;
     *
     * However if you have a proxy chain, the next proxy can have the following:
     *
     *     set req.http.X-Forwarded-For = req.http.X-Forwarded-For + ", " + client.ip;
     *
     * You must fully trust or have complete control over the proxies using
     * this header as it can be easily spoofed. To enable its usage set the
     * "request.proxyTrusted" config value to true and define the list of
     * trusted proxies in "request.trustedProxies".
     *
     *     $clientIps = $request->getClientIps();
     *
     * @link http://en.wikipedia.org/wiki/X-Forwarded-For
     * @throws RuntimeException
     *
     * @return array|string
     */
    public function getClientIps()
    {
        $clientIp = $this->getServer('REMOTE_ADDR');

        if (!$this->isProxyTrusted() || !($headerName = $this->config->get('proxy.header.for'))) {
            return array($clientIp);
        }

        if (!$clientIps = $this->getHeader($headerName)) {
            throw new RuntimeException('The client IP was not forwarded by the proxy');
        }

        $clientIps   = array_filter(array_map('trim', explode(',', $clientIps)));
        $clientIps[] = $clientIp;

        return $clientIps;
    }

    /**
     * Gets the client's IP address.
     *
     *     $clientIp = $request->getClientIp();
     *
     * @throws RuntimeException
     *
     * @return string E.g. "192.168.0.3".
     */
    public function getClientIp()
    {
        $clientIps = $this->getClientIps();

        return $clientIps[0];
    }

    /**
     * Gets the proxy IP addresses.
     *
     *     $proxyIps = $request->getProxyIps();
     *
     * @return array|bool
     */
    public function getProxyIps()
    {
        if (!$this->isProxyTrusted()) {
            return false;
        }

        $clientIps = $this->getClientIps();

        // The first IP is removed as it is the real client IP
        array_shift($clientIps);

        return $clientIps;
    }

    /**
     * Gets the proxy IP address.
     *
     *     $proxyIp = $request->getProxyIp();
     *
     * @return string|bool
     */
    public function getProxyIp()
    {
        if (!$this->isProxyTrusted()) {
            return false;
        }

        $proxyIps = $this->getProxyIps();

        return end($proxyIps);
    }

    /**
     * Checks if the proxy is trusted.
     *
     *     if ($request->isProxyTrusted()) {
     *
     * @return bool
     */
    public function isProxyTrusted()
    {
        if (!$this->config->get('proxy.isTrusted')) {
            return false;
        }

        if (!in_array($this->getServer('REMOTE_ADDR'), (array) $this->config->get('proxy.ip'))) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the request was done through HTTPS.
     *
     *     if ($request->isSecure()) {
     *
     * @return bool
     */
    public function isSecure()
    {
        if ($this->isProxyTrusted() && ($headerName = $this->config->get('proxy.header.proto'))) {
            return ('https' === $this->getHeader($headerName));
        }

        return filter_var($this->getServer('HTTPS'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Gets the URI scheme.
     *
     *     $scheme = $request->getScheme(); // "http"
     *
     * @return string Either "http" or "https".
     */
    public function getScheme()
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    /**
     * Gets the host name.
     *
     *     $host = $request->getHost();
     *
     * @throws UnexpectedValueException
     *
     * @return string
     */
    public function getHost()
    {
        if ($this->isProxyTrusted() && ($headerName = $this->config->get('proxy.header.host')) && ($host = $this->getHeader($headerName))) {
            // $host is already set above and checked for a value
        } else {
            $host = $this->getHeader('Host');
        }

        // Remove port and normalize casing
        $host = strtolower(preg_replace('/:\d+$/D', '', trim($host)));

        // Check for invalid characters
        if (!preg_match('/^[-._a-z0-9]+$/D', $host)) {
            throw new UnexpectedValueException(sprintf('Invalid Host "%s"', $host));
        }

        if (!preg_match('/'.$this->config->get('trusted.host').'/D', $host)) {
            throw new UnexpectedValueException(sprintf('Untrusted Host "%s"', $host));
        }

        return $host;
    }

    /**
     * Gets the port number.
     *
     *     $port = $request->getPort();
     *
     * @throws UnexpectedValueException
     *
     * @return string
     */
    public function getPort()
    {
        if ($this->isProxyTrusted()) {
            if (($headerName = $this->config->get('proxy.header.port')) && ($header = $this->getHeader($headerName))) {
                return (int) $header;
            }

            if (($headerName = $this->config->get('proxy.header.proto')) && ('https' === $this->getHeader($headerName))) {
                return 443;
            }
        }

        if (preg_match('/:(\d+)$/D', $this->getHeader('Host'), $matches)) {
            $port = (int) $matches[1];

            if (!in_array($port, (array) $this->config->get('trusted.port'))) {
                throw new UnexpectedValueException(sprintf('Untrusted port "%s"', $port));
            }

            return $port;
        }

        return $this->isSecure() ? 443 : 80;
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
