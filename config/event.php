<?php

use Rax\Event\CoreEvent;

/**
 * Event configuration.
 */
return array(
    /**
     * Enables the logging of event objects that might be useful in development
     * mode when debugging. If false, only the event config will be available.
     */
    'log' => true,

    /**
     * Events are defined as follows:
     *
     *     // Event name is namespaced with the bundle name to avoid collisions
     *     'bundle.eventName' => array(
     *
     *         // The observer name can be a service ID or FQN
     *         'fooObserver' => array(
     *
     *             // The observer configuration:
     *
     *             // Allows you to disable core or third party module observers
     *             // from your app bundle, it defaults to true
     *             'enabled' => true,
     *
     *             // Allows you to prepend an observer to the start of the observer
     *             // chain, defaults to false
     *             'prepend' => false,
     *         ),
     *
     *         // Omitting a config array will fallback to the default values
     *         'fooObserver', // Defaults to array('enabled' => true, 'prepend' => false)
     *     ),
     *
     * Events can be disabled through any of the following ways:
     *
     *     // Through the config
     *     'bundle.eventName' => array(
     *         'enabled' => false,
     *     ),
     *
     *     // At runtime
     *     $eventManager->off('bundle.eventName');
     *
     *     // By disabling all the event observers
     *     'bundle.eventName' => array(
     *         'fooObserver' => array(
     *             'enabled' => false,
     *         ),
     *     ),
     *
     *     // Event processing can be stopped at any point inside an observer
     *     $event->stopPropagation();
     *
     * By convention Eventer will call the trigger() method of the
     * observer class, which becomes an OOD hotspot:
     *
     *     class FooObserver
     *     {
     *         public function trigger(Event $event, ...)
     *         {
     *             // NOTE: $event will always point to the current triggered event
     *             $event->stopPropagation();
     *         }
     *     }
     */
    'events' => array(
        CoreEvent::APP => array(
            'requestObserver'
        ),
        CoreEvent::EVENT_TRIGGERED => array(
            'eventLogObserver',
        ),
    ),
);
