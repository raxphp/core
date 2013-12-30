<?php

namespace Rax\EventManager\Base;

use Rax\EventManager\CoreEvent;
use Rax\Container\Container;
use Rax\Config\Config;
use Rax\EventManager\Event;
use Rax\Helper\Arr;

/**
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
    protected $events = array();

    /**
     * @var Event
     */
    protected $event;

    /**
     * @param Container $container
     * @param Config      $config
     */
    public function __construct(Container $container, Config $config)
    {
        $this->container = $container;
        $this->events    = $config->get('events');
    }

    /**
     * @param array $events
     *
     * @return $this
     */
    public function setEvents(array $events)
    {
        $this->events = $events;

        return $this;
    }

    /**
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param Event $event
     *
     * @return $this
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param string $name
     * @param string $observer
     *
     * @return $this
     */
    public function on($name, $observer)
    {
        $this->events[$name][] = $observer;

        return $this;
    }

    /**
     * @param string $name
     * @param string $observer
     *
     * @return $this
     */
    public function off($name, $observer = null)
    {
        if (null === $observer) {
            unset($this->events[$name]);
        } elseif (false !== ($key = array_search($observer, $this->events[$name]))) {
            unset($this->events[$name][$key]);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param array $params
     *
     * @return $this
     */
    public function trigger($name, array $params = array())
    {
        if (empty($this->events[$name])) {
            return false;
        }

        $event = new Event($name, $params);
        $event->loadObservers(Arr::normalize($this->events[$name], array()));

        $this->setEvent($event);
        $this->container->set($event);

        foreach ($event->getObservers() as $observer) {
            if (!$observer->isEnabled()) {
                continue;
            }

            list($id, $fqn) = $this->container->parseIdFqn($observer->getName());

            $service = $this->container->get($id, $fqn);
            $this->container->call($service, 'trigger');

            $observer->setTriggered(true)->setReadOnly(true);

            if ($event->isStopped()) {
                break;
            }
        }

        if (CoreEvent::EVENT_TRIGGERED !== $name) {
            $this->trigger(CoreEvent::EVENT_TRIGGERED);
        }

        return $this;
    }
}
