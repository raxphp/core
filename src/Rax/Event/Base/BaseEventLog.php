<?php

namespace Rax\Event\Base;

use Rax\Event\Event;

/**
 * EventLog keeps an event log.
 *
 * Events are stored in the order they were executed and have the structure of:
 *
 *     'bundle.eventName' => $event,
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseEventLog
{
    /**
     * @var Event[]
     */
    protected $log = array();

    /**
     * Sets the event log.
     *
     *     $eventLog->set(array(
     *         $event,
     *         ...
     *     ));
     *
     * @param Event[] $log
     *
     * @return $this
     */
    public function set(array $log)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * Gets the event log.
     *
     *     $log = $eventLog->get();
     *
     * @return Event[]
     */
    public function get()
    {
        return $this->log;
    }

    /**
     * Gets the last triggered event.
     *
     *     $event = $eventLog->last();
     *
     * @return Event
     */
    public function last()
    {
        $events = $this->get();

        return end($events);
    }

    /**
     * Adds an event to the log.
     *
     *     $eventLog->add($event);
     *
     * @param Event $event
     *
     * @return $this
     */
    public function add(Event $event)
    {
        $this->log[$event->getName()] = $event;

        return $this;
    }

    /**
     * Removes an event from the log.
     *
     * You can remove an event by providing its name or object.
     *
     *     // Remove by event name
     *     $eventLog->remove('bundle.eventName');
     *
     *     // Remove by event object
     *     $eventLog->remove($event);
     *
     * @param string|Event $event
     *
     * @return $this
     */
    public function remove($event)
    {
        if (is_string($event)) {
            unset($this->log[$event]);
        } elseif (false !== ($key = array_search($event, $this->log))) {
            unset($this->log[$key]);
        }

        return $this;
    }
}
