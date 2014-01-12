<?php

namespace Rax\Event\Observer\Base;

use Rax\Config\Config;
use Rax\Event\Event;
use Rax\Event\EventLog;

/**
 * Logs the event objects that get triggered.
 *
 * Observes the core.eventTriggered event.
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseEventLogObserver
{
    /**
     * @param EventLog $eventLog
     * @param Event    $event
     * @param Config   $config
     */
    public function trigger(EventLog $eventLog, Event $event, Config $config)
    {
        if (!$config->get('event.log')) {
            return;
        }

        $eventLog->add($event);
    }
}
