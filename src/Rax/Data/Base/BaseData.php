<?php

namespace Rax\Data\Base;

use Rax\Data\ArrObj;
use Rax\Data\Loader\LoaderInterface;
use Rax\Helper\Arr;

/**
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseData
{
    /**
     * @var LoaderInterface[]
     */
    protected $loaders = array();

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var array
     */
    protected $queue = array();

    /**
     * Sets the data loaders.
     *
     * @param LoaderInterface[] $loaders
     *
     * @return $this
     */
    public function setLoaders($loaders)
    {
        $this->loaders = $loaders;

        return $this;
    }

    /**
     * Gets the data loaders.
     *
     * @return LoaderInterface[]
     */
    public function getLoaders()
    {
        return $this->loaders;
    }

    /**
     * Adds a data loader.
     *
     * @param LoaderInterface $loader
     *
     * @return $this
     */
    public function addLoader(LoaderInterface $loader)
    {
        $this->loaders[] = $loader;

        return $this;
    }

    /**
     * Removes a data loader.
     *
     * @param LoaderInterface $loader
     *
     * @return $this
     */
    public function removeLoader(LoaderInterface $loader)
    {
        if (false !== ($key = array_search($loader, $this->loaders))) {
            unset($this->loaders[$key]);
        }

        return $this;
    }

    /**
     * Sets a data value.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function set($key, $value = null)
    {
        Arr::set($this->data, $key, $value);
        Arr::set($this->queue, $key, $value);

        return $this;
    }

    /**
     * Gets a data value.
     *
     * @param string $key
     * @param mixed  $default
     * @param bool   $reload
     *
     * @return mixed
     */
    public function get($key, $default = null, $reload = false)
    {
        if (false === strpos($key, '.')) {
            $name = $key;
        } else {
            $arr = explode('.', $key);
            $name = array_shift($arr);
        }

        if ($reload || !isset($this->data[$name])) {
            $this->load($name);
        }

        return Arr::get($this->data, $key, $default);
    }

    /**
     * Loads the data from source.
     *
     * @param string $key
     *
     * @return ArrObj
     */
    public function load($key)
    {
        $data = array();

        foreach ($this->loaders as $driver) {
            $data = Arr::merge($data, $driver->load($key));
        }

        return ($this->data[$key] = new ArrObj($data));
    }

    /**
     * Save the queued data.
     *
     * @return $this
     */
    public function save()
    {
        foreach ($this->loaders as $driver) {
            $driver->save($this->queue);
        }

        return $this;
    }
}
