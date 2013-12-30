<?php

namespace Rax\Autoload\Base;

use Composer\Autoload\ClassLoader;
use Rax\Bundle\Cfs;
use Rax\Server\ServerMode;
use RuntimeException;

/**
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseAutoload
{
    /**
     * @var Cfs
     */
    protected $cfs;

    /**
     * @var ServerMode
     */
    protected $serverMode;

    /**
     * @var ClassLoader
     */
    protected $loader;

    /**
     * @var array
     */
    protected $cache = array();

    /**
     * @param Cfs        $cfs
     * @param ServerMode $serverMode
     */
    public function __construct(Cfs $cfs, ServerMode $serverMode)
    {
        $this->cfs        = $cfs;
        $this->serverMode = $serverMode;
    }

    /**
     * @param ClassLoader $loader
     *
     * @return $this
     */
    public function consume(ClassLoader $loader)
    {
        $loader->unregister();

        $this->loader = $loader;
        $this->cache  = $loader->getClassMap();

        return $this;
    }

    /**
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
     * @return $this
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));

        return $this;
    }

    /**
     * @param string $class
     *
     * @throws RuntimeException
     */
    public function loadClass($class)
    {
        if ($file = $this->findFile($class)) {
            require $file;

            if ($this->serverMode->isDev() && !class_exists($class, false) && !interface_exists($class, false) && (!function_exists('trait_exists') || !trait_exists($class, false))) {
                if (false !== strpos($class, '/')) {
                    throw new RuntimeException(sprintf('Class "%s" cannot contain forward slashes', $class));
                }

                throw new RuntimeException(sprintf('Expected class "%s" to be defined in file "%s"', $class, $file));
            }
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

        if ($file = $this->cfs->findFile('src', str_replace('\\', '/', $class))) {
            return $file;
        } elseif ($file = $this->loader->findFile($class)) {
            return $file;
        }

        return false;
    }
}
