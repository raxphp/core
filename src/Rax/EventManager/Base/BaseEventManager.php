<?php

namespace Rax\EventManager\Base;

use Rax\Config\ArrObj;
use Rax\Container\Container;
use Rax\Config\Config;
use Rax\EventManager\CoreEvent;
use Rax\EventManager\Event;
use Rax\Helper\Arr;

/**
 * EventManager manages and triggers events.
 *
 * NOTE: This class maintains array configuration, look at the EventLog for the
 * actual objects.
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
     * @var ArrObj
     */
    protected $config;

    /**
     * @param Container $container
     * @param Config    $config
     */
    public function __construct(Container $container, Config $config)
    {
        $this->container = $container;
        $this->config    = $config->get('event');
    }

    /**
     * Sets the event runtime config.
     *
     *     $eventManager->setConfig($config);
     *
     * @param ArrObj $config
     *
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Gets the maintained event config.
     *
     *     // Get the original event configuration
     *     $eventConfig = $config->get('event');
     *
     *     // Get the above config plus any modifications that've been made at runtime
     *     $eventConfig = $eventManager->getConfig();
     *
     * @return ArrObj
     */
    public function getConfig()
    {
        return $this->config;
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
        return array_keys($this->getConfig()->asArray());
    }

    /**
     * Adds a new event observer at runtime.
     *
     *     // If the event doesn't exist it will be created automatically
     *     $eventManager->on('bundle.eventName', 'fooObserver');
     *
     * @param string $name
     * @param string $observer
     * @param bool   $prepend
     *
     * @return $this
     */
    public function on($name, $observer, $prepend = false)
    {
        if (!isset($this->config[$name])) {
            $this->config[$name] = array();
        }

        if ($prepend) {
            array_unshift($this->config[$name], $observer);
        } else {
            $this->config[$name][] = $observer;
        }

        return $this;
    }

    /**
     * Removes an event, or an observer from the event's observer chain.
     *
     *     // Remove the event so it can't be triggered anymore
     *     $eventManager->off('bundle.eventName');
     *
     *     // Remove a single observer from the observer chain
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
            unset($this->config[$name]);
        } elseif (false !== ($key = array_search($observer, $this->config[$name]))) {
            unset($this->config[$name][$key]);
        }

        return $this;
    }

    /**
     * Triggers an event by executing its observer chain.
     *
     * Events are lazyloaded, i.e. the event and observer objects are only built
     * once the event is triggered, otherwise they remain as array configuration.
     *
     *     $eventManager->trigger('bundle.eventName', array('value', $value));
     *
     *     // Params are available through the event object
     *     class FooObserver
     *     {
     *         public function trigger(Event $event)
     *         {
     *             $value = $event->getParam('value');
     *         }
     *     }
     *
     * @param string $eventName
     * @param array  $params
     *
     * @return $this
     */
    public function trigger($eventName, array $params = array())
    {
        // Check if the event exists and is enabled
        if (!isset($this->config[$eventName]) || !Arr::get($this->config[$eventName], 'enabled', true)) {
            return false;
        }

        $event = new Event($eventName, $params);
        $event->loadObservers(Arr::normalize($this->config[$eventName], array()));

        // The "event" service always points to the latest triggered event
        $this->container->set($event);

        foreach ($event->getObservers() as $observer) {
            // The event can be stopped by any observer
            if ($event->isPropagationStopped()) {
                break;
            }

            // Observers can be disabled in the config or at runtime
            if (!$observer->isEnabled()) {
                continue;
            }

            $this->container->call($observer->getName(), 'trigger');

            $observer->setTriggered(true);
        }

        $event->setTriggered(true);

        if (CoreEvent::EVENT_TRIGGERED !== $eventName) {
            $this->trigger(CoreEvent::EVENT_TRIGGERED);
        }

        return $this;
    }
}
