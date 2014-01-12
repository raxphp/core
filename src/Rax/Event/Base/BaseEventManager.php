<?php

namespace Rax\Event\Base;

use Rax\Container\Container;
use Rax\Config\Config;
use Rax\Event\CoreEvent;
use Rax\Event\Event;
use Rax\Helper\Arr;

/**
 * EventManager manages and triggers events.
 *
 * NOTE: This class maintains the event config only, the actual objects are
 * stored in the EventLog.
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseEventManager
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
        $this->events    = $this->normalize($config->get('event.events'));
    }

    /**
     * Normalizes the observer array of all events.
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
     * @return array
     */
    public function normalize(array $events = array())
    {
        foreach ($events as $event => $observers) {
            $events[$event] = Arr::normalize($observers, array());
        }

        return $events;
    }

    /**
     * Sets the event config.
     *
     * NOTE: The array should be formatted similar to the "event.events"
     * config array.
     *
     *     $eventManager->setEvents($config);
     *
     * @param array $config
     *
     * @return $this
     */
    public function setEvents(array $config)
    {
        $this->events = $this->normalize($config);

        return $this;
    }

    /**
     * Gets the event config.
     *
     * NOTE: This method returns an event config array, look at the EventLog for
     * the actual objects.
     *
     *     // Get the original event config
     *     $events = $config->get('event.events');
     *
     *     // Get an event config that may have been modified at runtime
     *     $events = $eventManager->getEvents();
     *
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Gets an event config.
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
     * Gets all the loaded event names.
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
     *     // The observer config is optional and defaults to:
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
