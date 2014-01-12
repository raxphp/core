<?php

namespace Rax\Event\Base;

use Rax\Event\Observer;
use Rax\Helper\Arr;

/**
 * Event represents an event, and manages its state and observer chain.
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseEvent
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $params = array();

    /**
     * @var Observer[]
     */
    protected $observers = array();

    /**
     * @var bool
     */
    protected $stopPropagation = false;

    /**
     * @var bool
     */
    protected $triggered = false;

    /**
     * @param string $name
     * @param array  $observers
     * @param array  $params
     */
    public function __construct($name = null, array $observers = array(), array $params = array())
    {
        $this->name   = $name;
        $this->params = $params;

        $this->loadObservers($observers);
    }

    /**
     * Sets the event's name.
     *
     *     $event->setName('bundle.eventName');
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the event's name.
     *
     *     $eventName = $event->getName(); // "bundle.eventName"
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the event parameters.
     *
     * The parameters are usually set when the event gets triggered:
     *
     *     $eventManager->trigger('bundle.eventName', array('foo' => $foo));
     *
     * NOTE: This method will override those original parameters.
     *
     *     $event->setParams(array('foo' => $bar));
     *
     * @param array $params
     *
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Sets a single event parameter.
     *
     *     $event->setParam('foo', $foo);
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * Gets the event parameters.
     *
     *     $params = $event->getParams();
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Gets a single event parameter.
     *
     *     $foo = $event->getParam('foo');
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getParam($key, $default = null)
    {
        return isset($this->params[$key]) ? $this->params[$key] : $default;
    }

    /**
     * Sets whether the event propagation has stopped or not.
     *
     *     // Stop event propagation
     *     $event->stopPropagation();
     *
     *     // Re-enable event propagation
     *     $event->setStopPropagation(true);
     *
     * @param bool $stopPropagation
     *
     * @return $this
     */
    public function setStopPropagation($stopPropagation)
    {
        $this->stopPropagation = (bool) $stopPropagation;

        return $this;
    }

    /**
     * Stops the event propagation.
     *
     *     // Stop subsequent observers down the chain from triggering
     *     $event->stopPropagation();
     *
     *     // Alternative, in case the value is dynamic
     *     $event->setStopPropagation($stopPropagation);
     *
     * @return $this
     */
    public function stopPropagation()
    {
        $this->stopPropagation = true;

        return $this;
    }

    /**
     * Checks if the event propagation has stopped.
     *
     *     if ($event->isPropagationStopped()) {
     *
     * @return bool
     */
    public function isPropagationStopped()
    {
        return $this->stopPropagation;
    }

    /**
     * Sets the event observer chain.
     *
     * NOTE: Observers are defined in the event configuration, and single
     * observers can be added with {@see EventManager::on}. Use this method
     * to override all the registered observers.
     *
     *     $event->setObservers(array(
     *         new Observer(fooObserver),
     *     ));
     *
     * @param Observer[] $observers
     *
     * @return $this
     */
    public function setObservers(array $observers)
    {
        $this->observers = $observers;

        return $this;
    }

    /**
     * Gets the event observer chain.
     *
     *     $observers = $event->getObservers();
     *
     * @return Observer[]
     */
    public function getObservers()
    {
        return $this->observers;
    }

    /**
     * Gets the observer names.
     *
     *     $observerNames = $event->getObserverNames();
     *
     * @return array
     */
    public function getObserverNames()
    {
        return array_keys($this->observers);
    }

    /**
     * Sets the whether the event has triggered or not.
     *
     * NOTE: This will be set automatically by the EventManager.
     *
     *     $event->setTriggered(true);
     *
     * @param $triggered
     *
     * @return $this
     */
    public function setTriggered($triggered)
    {
        $this->triggered = $triggered;

        return $this;
    }

    /**
     * Checks if the event has triggered.
     *
     *     if ($event->isTriggered()) {
     *
     * @return bool
     */
    public function isTriggered()
    {
        return $this->triggered;
    }

    /**
     * Loads the observers defined in the configuration.
     *
     * NOTE: The observers will be loaded automatically by the EventManager.
     *
     *     $event->loadObservers(array(
     *         'fooObserver' => array(),
     *     ));
     *
     * Observers are stored as:
     *
     *     'fooObserver' => $observer,
     *
     * @param array $observers
     *
     * @return $this
     */
    public function loadObservers(array $observers)
    {
        foreach ($observers as $name => $config) {
            // Default observer configuration
            $defaults = array(
                'name'    => $name,
                'enabled' => true,
                'prepend' => false,
            );

            $config = Arr::merge($defaults, $config);

            $observer = new Observer($config['name'], $config['enabled']);

            if ($config['prepend']) {
                Arr::unshift($this->observers, $name, $observer);
            } else {
                $this->observers[$name] = $observer;
            }
        }

        return $this;
    }
}
