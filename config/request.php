<?php

/**
 * Request configuration.
 */
return array(
    /**
     * Trusted domain names and port numbers.
     *
     * Rax uses a whitelist approach to prevent Host header spoofing attacks.
     */
    'trusted' => array(
        /**
         * Host regex pattern.
         *
         * Specify the domains that will be pointed to this installation in the
         * form of a regular expression. E.g. the value "example.com$" will
         * whitelist the root domain "example.com" as well as all of its subdomains:
         *
         * You can also define multiple root domains by using the pipe character,
         * e.g. "example.com|one.com|two.com$".
         *
         * IMPORTANT: Make sure to keep the "$" at the end of the string.
         */
        'host' => 'example.com$',

        /**
         * Trusted port numbers.
         */
        'port' => array(80, 443),
    ),

    /**
     * Proxy server settings.
     */
    'proxy' => array(

        /**
         * Enable if your site is behind a proxy server.
         */
        'trusted' => false,

        /**
         * IP of the proxy server.
         *
         * You can define multiple IPs:
         *
         *     'ip' => array('127.0.0.1', '198.168.0.3'),
         */
        'ip'     => '127.0.0.1',

        /**
         * Proxy header names.
         *
         * These are disabled by default for security purposes. Only enable the
         * headers defined by your proxy server.
         */
        'header'  => array(
            // 'for'   => 'X-Forwarded-For',
            // 'host'  => 'X-Forwarded-Host',
            // 'port'  => 'X-Forwarded-Port',
            // 'proto' => 'X-Forwarded-Proto',
        ),
    ),
);
