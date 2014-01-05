<?php

namespace Rax\EventManager\Base;

use Rax\Config\ArrObj;
use Rax\Container\Container;
use Rax\Config\Config;
use Rax\EventManager\Event;
use Rax\Helper\Arr;

/**
 * EventManager manages and triggers events.
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
     * Gets the EventManager's maintained configuration.
     *
     *     // Get the original event configuration
     *     $eventConfig = $config->get('event');
     *
     *     // Get the config plus any modifications that been made at runtime
     *     $eventConfig = $eventManager->getConfig();
     *
     * @return ArrObj
     */
    public function getConfig()
    {
        return $this->config;
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
     * @param string $name
     * @param array  $params
     *
     * @return $this
     */
    public function trigger($name, array $params = array())
    {
        // Check if the event exists and is enabled
        if (!isset($this->config[$name]) || !Arr::get($this->config[$name], 'enabled', true)) {
            return false;
        }

        $event = new Event($name, $params);
        $event->loadObservers(Arr::normalize($this->config[$name], array()));

        // The "event" service always points to the latest triggered event
        $this->container->set($event);

        foreach ($event->getObservers() as $observer) {
            if ($event->isPropagationStopped()) {
                break;
            }

            if (!$observer->isEnabled()) {
                continue;
            }

            $this->container->call($observer->getName(), 'trigger');

            $observer->setTriggered(true);
        }

        $event->setTriggered(true);

        if ('core.eventTriggered' !== $name) {
            $this->trigger('core.eventTriggered');
        }

        return $this;
    }
}
