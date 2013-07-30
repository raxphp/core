<?php

namespace Rax\Bundle\Base;

/**
 * @author    Gregorio Ramirez <goyocode@gmail.com>
 * @copyright Copyright (c) Gregorio Ramirez <goyocode@gmail.com>
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD
 */
class BaseBundles
{
    /**
     * @var array
     */
    protected $bundles = array();

    /**
     * @var array
     */
    protected $enabledBundles = array();

    /**
     * @var array
     */
    protected $disabledBundles = array();

    /**
     * @var array
     */
    protected $paths = array();

    /**
     * @var array
     */
    protected $enabledPaths = array();

    /**
     * @var array
     */
    protected $disabledPaths = array();

    /**
     * @param array $bundles
     */
    public function __construct(array $bundles = null)
    {
        $this->bundles = $bundles;

        foreach ($bundles as $name => $params) {
            $path = $params['path'].DS;
            $this->paths[] = $path;

            if ($params['enabled']) {
                $this->enabledBundles[$name] = $params;
                $this->enabledPaths[] = $path;
            } else {
                $this->disabledBundles[$name] = $params;
                $this->disabledPaths[] = $path;
            }
        }
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this->bundles;
    }

    /**
     * @return array
     */
    public function getEnabled()
    {
        return $this->enabledBundles;
    }

    /**
     * @return array
     */
    public function getDisabled()
    {
        return $this->disabledBundles;
    }

    /**
     * @return array
     */
    public function getNames()
    {
        return array_keys($this->bundles);
    }

    /**
     * @return array
     */
    public function getEnabledNames()
    {
        return array_keys($this->enabledBundles);
    }

    /**
     * @return array
     */
    public function getDisabledNames()
    {
        return array_keys($this->disabledBundles);
    }

    /**
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * @return array
     */
    public function getEnabledPaths()
    {
        return $this->enabledPaths;
    }

    /**
     * @return array
     */
    public function getDisabledPaths()
    {
        return $this->disabledPaths;
    }
}
