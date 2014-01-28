<?php

namespace Rax\Event\Base;

use Rax\Container\Container;
use Rax\Config\Config;
use Rax\Event\CoreEvent;
use Rax\Event\Event;
use Rax\Helper\Arr;

/**
 * Eventer manages event configuration and triggers events.
 *
 * NOTE: This class maintains event configuration only, the actual objects are
 * stored in the {@see EventLog}.
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseEventer
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $events;

    /**
     * @param Container $container
     * @param Config    $config
     */
    public function __construct(Container $container, Config $config)
    {
        $this->container = $container;

        $this->loadEvents($config->get('event.events'));
    }

    /**
     * Loads the event configuration.
     *
     * Normalizes the observers so they all have an observer configuration array.
     *
     *     // Before
     *     'bundle.eventName' => array(
     *         'fooObserver'
     *         'barObserver' => array(
     *             'prepend' => true,
     *         ),
     *     ),
     *
     *     // After
     *     'bundle.eventName' => array(
     *         'fooObserver' => array(),
     *         'barObserver' => array(
     *             'prepend' => true,
     *         ),
     *     ),
     *
     * @param array $events
     *
     * @return $this
     */
    public function loadEvents(array $events = array())
    {
        foreach ($events as $event => $observers) {
            $this->events[$event] = Arr::normalize($observers, array());
        }

        return $this;
    }

    /**
     * Sets the event configuration.
     *
     * Use the event configuration if possible to define events.
     *
     * NOTE: A configuration array is expected. Follow the formatting of the
     * "event.events" config.
     *
     *     $eventManager->setEvents(array(
     *         CoreEvent::APP => array(
     *             'requestObserver'
     *         ),
     *         CoreEvent::EVENT_TRIGGERED => array(
     *             'eventLogObserver',
     *         ),
     *     ));
     *
     * @param array $events
     *
     * @return $this
     */
    public function setEvents(array $events)
    {
        $this->loadEvents($events);

        return $this;
    }

    /**
     * Gets the event configuration.
     *
     * NOTE: This method returns an event configuration array, look at the
     * {@see EventLog} for the actual objects.
     *
     *     // Get the original event configuration
     *     $events = $config->get('event.events');
     *
     *     // Get the event runtime configuration
     *     $events = $eventManager->getEvents();
     *
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Gets the configuration for a single event.
     *
     *     $event = $eventManager->getEvent('bundle.eventName');
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return array
     */
    public function getEvent($name, $default = null)
    {
        return isset($this->events[$name]) ? $this->events[$name] : $default;
    }

    /**
     * Gets the event names.
     *
     *     $eventNames = $eventManager->getEventNames();
     *
     * @return array
     */
    public function getEventNames()
    {
        return array_keys($this->events);
    }

    /**
     * Adds a new event observer.
     *
     * If the event doesn't exist it will be created automatically.
     *
     *     $eventManager->on('bundle.eventName', 'fooObserver');
     *
     *     // The observer configuration is optional and defaults to:
     *     array(
     *         'enabled' => true,
     *         'prepend' => false,
     *     )
     *
     * @param string $name
     * @param string $observer
     * @param array  $config
     *
     * @return $this
     */
    public function on($name, $observer, array $config = array())
    {
        if (!isset($this->events[$name])) {
            $this->events[$name] = array();
        }

        if (Arr::get($config, 'prepend', false)) {
            Arr::unshift($this->events, $observer, $config);
        } else {
            $this->events[$name][$observer] = $config;
        }

        return $this;
    }

    /**
     * Removes an event or an observer.
     *
     *     // Remove an entire event
     *     $eventManager->off('bundle.eventName');
     *
     *     // Remove a single observer from an event
     *     $eventManager->off('bundle.eventName', 'fooObserver');
     *
     * @param string $name
     * @param string $observer
     *
     * @return $this
     */
    public function off($name, $observer = null)
    {
        if (null === $observer) {
            unset($this->events[$name]);
        } else {
            unset($this->events[$name][$observer]);
        }

        return $this;
    }

    /**
     * Triggers an event by calling its observers.
     *
     *     $eventManager->trigger('bundle.eventName');
     *
     * Values may be passed on to the observer's trigger hotspot:
     *
     *     $eventManager->trigger('bundle.eventName', array('value' => $value, $foo));
     *
     *     class barObserver
     *     {
     *         public function trigger($value, Foo $foo, Event $event)
     *         {
     *             // Get the full list of passed params
     *             $params = $event->getParams();
     *             $value  = $params['value'];
     *             $foo    = $params['foo'];
     *
     *             // Or one at a time
     *             $value = $event->getParam('value');
     *             $foo   = $event->getParam('foo');
     *         }
     *     }
     *
     * NOTE: Events are lazy loaded, so the event and observer objects aren't
     * built until the event is triggered.
     *
     * @param string $name
     * @param array  $params
     *
     * @return $this
     */
    public function trigger($name, array $params = array())
    {
        // Check if the event exists and is enabled
        if (!isset($this->events[$name]) || !Arr::get($this->events[$name], 'enabled', true)) {
            return false;
        }

        $event = new Event($name, $this->events[$name], $params);

        foreach ($event->getObservers() as $observer) {
            if ($event->isPropagationStopped()) {
                break;
            }

            if (!$observer->isEnabled()) {
                continue;
            }

            $params[] = $event;

            $this->container->call($observer->getName(), 'trigger', $params);

            $observer->setTriggered(true);
        }

        $event->setTriggered(true);

        if (CoreEvent::EVENT_TRIGGERED !== $name) {
            $this->trigger(CoreEvent::EVENT_TRIGGERED);
        }

        return $this;
    }
}
