<?php

namespace Rax\Autoload\Base;

use Rax\Bundle\Cfs;

/**
 * The Autoload class autoloads PHP classes.
 *
 * @author    Gregorio Ramirez <goyocode@gmail.com>
 * @copyright Copyright (c) 2012-2013 Gregorio Ramirez <goyocode@gmail.com>
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD
 */
class BaseAutoload
{
    /**
     * @var Cfs
     */
    protected $cfs;

    /**
     * @var array
     */
    protected $cache = array();

    /**
     * @var array
     */
    protected $namespaces = array();

    /**
     * @var array
     */
    public $fallbacks = array();

    /**
     * @param Cfs $cfs
     */
    public function __construct(Cfs $cfs)
    {
        $this->cfs = $cfs;
    }

    /**
     * Composer compatible cached mappings setter.
     *
     * @param array $cache
     *
     * @return $this
     */
    public function setCache(array $cache = array())
    {
        if ($cache) {
            $this->cache = $cache;
        }

        return $this;
    }

    /**
     * Gets the cached class mappings.
     *
     * @return array
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Composer compatible namespace setter.
     *
     * @param array $namespaces
     *
     * @return $this
     */
    public function setNamespaces(array $namespaces)
    {
        foreach ($namespaces as $namespace => $paths) {
            $paths = (array) $paths;

            if ($namespace) {
                $this->namespaces[$namespace] = $paths;
            } else {
                $this->fallbacks = $paths;
            }
        }

        return $this;
    }

    /**
     * Gets the namespaces.
     *
     * @return array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * Registers the class autoloader.
     *
     *     Autoload::getSingleton()->register();
     *
     * @param bool $prepend
     *
     * @return $this
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);

        return $this;
    }

    /**
     * Unregisters the class autoloader.
     *
     *     Autoload::getSingleton()->unregister();
     *
     * @return $this
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));

        return $this;
    }

    /**
     * Loads a class file from the cascading filesystem (CFS.)
     *
     * This method is PSR-0 compliant. The class is loaded on first-come
     * first-serve basis.
     *
     *     Autoload::getSingleton()->loadClass('BarClass');
     *
     * @param string $class
     */
    public function loadClass($class)
    {
        if ($file = $this->findFile($class)) {
            require $file;
        }
    }

    /**
     * @param string $class
     *
     * @return string|bool
     */
    public function findFile($class)
    {
        if (isset($this->cache[$class])) {
            return $this->cache[$class];
        }

        $classPath = str_replace(array('\\', '_'), '/', $class);

        if ($file = $this->cfs->findFile('src', $classPath)) {
            return $file;
        }

        $classPath .= '.php';

        foreach ($this->namespaces as $namespace => $dirs) {
            if (0 === strpos($class, $namespace)) {
                foreach ($dirs as $dir) {
                    if (is_file($dir.DIRECTORY_SEPARATOR.$classPath)) {
                        return $dir.DIRECTORY_SEPARATOR.$classPath;
                    }
                }
            }
        }

        foreach ($this->fallbacks as $dir) {
            if (file_exists($dir.DIRECTORY_SEPARATOR.$classPath)) {
                return $dir.DIRECTORY_SEPARATOR.$classPath;
            }
        }

        return false;
    }
}
