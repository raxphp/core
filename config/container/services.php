<?php

use Rax\Http\Request;

/**
 * Core services.
 *
 * You can override any service in your bundle's configuration.
 */
return array(
    'request' => function() {
        return new Request();
    },
);
