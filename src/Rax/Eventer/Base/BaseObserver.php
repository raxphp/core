<?php

namespace Rax\Event\Base;

/**
 * Observer holds the name and enabled status of a single observer.
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseObserver
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @var bool
     */
    protected $triggered = false;

    /**
     * @param string $name
     * @param bool   $enabled
     */
    public function __construct($name, $enabled = true)
    {
        $this->name    = $name;
        $this->enabled = $enabled;
    }

    /**
     * Sets the observer's name.
     *
     * The name can be a service ID or FQN.
     *
     *     $observer->setName('fooObserver');
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
     * Gets the observer's name.
     *
     *     $this->container->call($observer->getName(), 'trigger');
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets whether the observer is enabled or not.
     *
     * This allows you to enable/disable an observer at runtime.
     *
     * NOTE: You can also enable/disable through the event config.
     *
     *     $observer->setEnabled(false);
     *
     * @param bool $enabled
     *
     * @return $this
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;

        return $this;
    }

    /**
     * Checks if the observer is enabled.
     *
     *     if ($observer->isEnabled()) {
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Sets whether the observer has triggered or not.
     *
     * NOTE: This will be set automatically by the Eventer.
     *
     *     $observer->setTriggered(true);
     *
     * @param bool $triggered
     *
     * @return $this
     */
    public function setTriggered($triggered)
    {
        $this->triggered = (bool) $triggered;

        return $this;
    }

    /**
     * Checks if the observer has been triggered.
     *
     *     if ($observer->isTriggered()) {
     *
     * @return bool
     */
    public function isTriggered()
    {
        return $this->triggered;
    }
}
