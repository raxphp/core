<?php

namespace Rax\Config\Base;

use Rax\Config\ArrObj;
use Rax\Config\Loader\ConfigLoaderInterface;
use Rax\Helper\Arr;

/**
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseConfig
{
    /**
     * @var ConfigLoaderInterface[]
     */
    protected $loaders = array();

    /**
     * @var array
     */
    protected $config = array();

    /**
     * @var array
     */
    protected $queue = array();

    /**
     * Sets the config loaders.
     *
     *     $config->setLoaders(array(
     *         new FileConfigLoader('config', $cfs),
     *     ));
     *
     * @param ConfigLoaderInterface[] $loaders
     *
     * @return $this
     */
    public function setLoaders($loaders)
    {
        $this->loaders = $loaders;

        return $this;
    }

    /**
     * Gets the config loaders.
     *
     *     $configLoaders = $config->getLoaders();
     *
     * @return ConfigLoaderInterface[]
     */
    public function getLoaders()
    {
        return $this->loaders;
    }

    /**
     * Adds a config loader.
     *
     *     $config->addLoader(new FileConfigLoader('config', $cfs));
     *
     * @param ConfigLoaderInterface $loader
     *
     * @return $this
     */
    public function addLoader(ConfigLoaderInterface $loader)
    {
        $this->loaders[] = $loader;

        return $this;
    }

    /**
     * Removes a config loader.
     *
     *     $config->removeLoader($fileConfigLoader);
     *
     * @param ConfigLoaderInterface $loader
     *
     * @return $this
     */
    public function removeLoader(ConfigLoaderInterface $loader)
    {
        if (false !== ($key = array_search($loader, $this->loaders))) {
            unset($this->loaders[$key]);
        }

        return $this;
    }

    /**
     * Sets a config value.
     *
     * The config data is not saved immediately to source. Instead a queue is
     * maintained, and flushed when the core.shutdown event gets triggered.
     *
     *     $config->set('request.proxy.trusted', false);
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function set($key, $value = null)
    {
        Arr::set($this->config, $key, $value);
        Arr::set($this->queue, $key, $value);

        return $this;
    }

    /**
     * Gets a config value.
     *
     *     // One-off
     *     $isTrusted = $config->get('request.proxy.isTrusted');
     *
     *     // Multi-access
     *     $config = $config->get('request');
     *
     *     $isTrusted = $config->get('proxy.isTrusted');
     *
     * @param string $key
     * @param mixed  $default
     * @param bool   $reload
     *
     * @return mixed
     */
    public function get($key, $default = null, $reload = false)
    {
        $configName = current(explode('.', $key, 2));

        if ($reload || !isset($this->config[$configName])) {
            $this->load($configName);
        }

        return Arr::get($this->config, $key, $default);
    }

    /**
     * Loads the data from source.
     *
     * NOTE: This method does not cache anything. If in doubt, use get() instead.
     *
     *     $request = $config->load('request');
     *
     * @param string $key
     *
     * @return ArrObj
     */
    public function load($key)
    {
        $data = array();

        foreach ($this->loaders as $loader) {
            $data = Arr::merge($data, $loader->load($key));
        }

        return ($this->config[$key] = new ArrObj($data));
    }

    /**
     * Saves the queued data to source.
     *
     * NOTE: The save will happen automatically by ConfigShutdownObserver.
     *
     *     $config->save();
     *
     * @see ConfigShutdownObserver::trigger
     *
     * @return $this
     */
    public function save()
    {
        foreach ($this->loaders as $loader) {
            $loader->save($this->queue);
        }

        return $this;
    }
}
