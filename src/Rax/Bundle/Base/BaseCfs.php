<?php

namespace Rax\Bundle\Base;

use Closure;
use Exception;
use Rax\Bundle\Bundles;
use RuntimeException;
use Rax\Helper\Php;
use Symfony\Component\Finder\Finder;
use Rax\Server\ServerMode;

/**
 * The Cfs class maintains the list of loaded bundles.
 *
 * It also provides tools for searching the cascading filesystem for specific
 * set of files and directories paths.
 *
 * @author    Gregorio Ramirez <goyocode@gmail.com>
 * @copyright Copyright (c) 2012 Gregorio Ramirez <goyocode@gmail.com>
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD
 */
class BaseCfs
{
    /**
     * @var Bundles
     */
    protected $bundles;

    /**
     * @var ServerMode
     */
    protected $serverMode;

    /**
     * @var string
     */
    protected $initBasename;

    /**
     * @param Bundles    $bundles
     * @param ServerMode $serverMode
     */
    public function __construct(Bundles $bundles, ServerMode $serverMode)
    {
        $this->bundles    = $bundles;
        $this->serverMode = $serverMode;
    }

    /**
     * @param string $initBasename
     *
     * @return $this
     */
    public function setInitBasename($initBasename)
    {
        $this->initBasename = $initBasename;

        return $this;
    }

    /**
     * @return string
     */
    public function getBasename()
    {
        return $this->initBasename;
    }

    /**
     * Bootstrap each bundle.
     *
     * @return $this
     */
    public function loadBundles()
    {
        $paths = array_reverse($this->bundles->getEnabledPaths());

        foreach ($paths as $path) {
            if (is_file($file = $path.$this->initBasename.'.php')) {
                Php::load($file);
            }
        }

        return $this;
    }

    /**
     * Gets the full path to the first occurrence of the filename in the cascading
     * filesystem.
     *
     *     $path = $cfs->findFile('src', 'Vendor/Namespace/Class'); // "/path/to/Vendor/Namespace/Class.php"
     *
     * @param string $dir
     * @param string $filename
     * @param string $ext
     *
     * @return string|bool
     */
    public function findFile($dir, $filename, $ext = 'php')
    {
        if ($file = $this->findFiles($dir, $filename, $ext, true)) {
            return $file;
        }

        return false;
    }

    /**
     * Returns all the file paths found in the cascading filesystem for a given
     * file name.
     *
     *     $files = $cfs->findFiles('views', 'about/team', 'twig');
     *
     * @param string $dir
     * @param string $basename
     * @param string $ext
     * @param bool   $first
     *
     * @return array
     */
    public function findFiles($dir, $basename, $ext = 'php', $first = false)
    {
        $basename = $dir.DS.$basename.'.'.$ext;

        $files = array();

        foreach ($this->bundles->getEnabledPaths() as $path) {
            if (is_file($file = $path.$basename)) {
                if ($first) {
                    return $file;
                }
                $files[] = $file;
            }
        }

        return $files;
    }

    /**
     * Returns all the directory paths found in the cascading filesystem for a
     * given directory name.
     *
     *     $dirs = $cfs->findDirs('views');
     *
     * @param array|string $names
     *
     * @return array
     */
    public function findDirs($names)
    {
        $dirs = array();

        foreach ((array) $names as $name) {
            foreach ($this->bundles->getEnabledPaths() as $path) {
                if (is_dir($dir = $path.$name)) {
                    $dirs[] = $dir;
                }
            }
        }

        return $dirs;
    }
}
