<?php

namespace Rax\EventManager\Base;

use Rax\EventManager\Observer;
use Rax\Helper\Arr;

/**
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
     * @var bool
     */
    protected $stopped = false;

    /**
     * @var Observer[]
     */
    protected $observers;

    /**
     * @param string $name
     * @param array  $params
     */
    public function __construct($name = null, array $params = array())
    {
        $this->name   = $name;
        $this->params = $params;
    }

    /**
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
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
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        return Arr::get($this->params, $name, $default);
    }

    /**
     * @return $this
     */
    public function stop()
    {
        $this->stopped = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStopped()
    {
        return $this->stopped;
    }

    /**
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
     * @return Observer[]
     */
    public function getObservers()
    {
        return $this->observers;
    }

    /**
     * array(
     *     // Default values are as follow:
     *     'fooObserver' => array('enabled' => true, 'prepend' => false),
     *
     *     // Omitting a config array will fallback to the default values
     *     'barObserver',
     * ),
     *
     * @param array $observers
     *
     * @return $this
     */
    public function loadObservers(array $observers)
    {
        foreach ($observers as $name => $params) {
            $config = Arr::get($params, array(
                'name'    => $name,
                'enabled' => true,
                'prepend' => false,
            ));

            $observer = new Observer($config['name'], $config['enabled']);

            if ($config['prepend']) {
                array_unshift($this->observers, $observer);
            } else {
                $this->observers[] = $observer;
            }
        }

        return $this;
    }
}
