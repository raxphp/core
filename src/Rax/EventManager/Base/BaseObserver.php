<?php

namespace Rax\EventManager\Base;

/**
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
     * @var bool
     */
    protected $readOnly = false;

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
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        if ($this->readOnly) {
            return $this;
        }

        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param bool $enabled
     *
     * @return $this
     */
    public function setEnabled($enabled)
    {
        if ($this->readOnly) {
            return $this;
        }

        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $triggered
     *
     * @return $this
     */
    public function setTriggered($triggered)
    {
        if ($this->readOnly) {
            return $this;
        }

        $this->triggered = $triggered;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTriggered()
    {
        return $this->triggered;
    }

    /**
     * @param bool $readOnly
     *
     * @return $this
     */
    public function setReadOnly($readOnly)
    {
        if ($this->readOnly) {
            return $this;
        }

        $this->readOnly = $readOnly;

        return $this;
    }

    /**
     * @return bool
     */
    public function isReadOnly()
    {
        return $this->readOnly;
    }
}
