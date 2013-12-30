<?php

use Rax\EventManager\CoreEvent;

/**
 * 'bundle.event_name' => array('enabled' => true, 'prepend' => false),
 */
return array(
    'core.startup' => array(
        'requestObserver'
    ),
);
