<?php

namespace Rax\Bundle\Loader\Base;

use Rax\Bundle\Loader\BundleLoaderInterface;
use Rax\Helper\Arr;
use Rax\Helper\Php;
use Rax\Server\ServerMode;
use RuntimeException;

/**
 * @author    Gregorio Ramirez <goyocode@gmail.com>
 * @copyright Copyright (c) Gregorio Ramirez <goyocode@gmail.com>
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD
 */
abstract class BaseAbstractBundleLoader implements BundleLoaderInterface
{
    /**
     * @var string
     */
    public $dir;

    /**
     * @var string
     */
    public $basename;

    /**
     * @var ServerMode
     */
    protected $serverMode;

    /**
     * @param ServerMode $serverMode
     */
    public function __construct(ServerMode $serverMode)
    {
        $this->serverMode = $serverMode;
    }

    /**
     * @param string $dir
     *
     * @return $this
     */
    public function setDir($dir)
    {
        $this->dir = $dir;

        return $this;
    }

    /**
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * @param string $basename
     *
     * @return $this
     */
    public function setBasename($basename)
    {
        $this->basename = $basename;

        return $this;
    }

    /**
     * @return string
     */
    public function getBasename()
    {
        return $this->basename;
    }

    /**
     * @return array
     */
    public function load()
    {
        $files = array_reverse($this->getFiles());

        $bundles = array();

        foreach ($files as $file) {
            $bundles = Arr::merge($bundles, Php::load($file));
        }

        return $bundles;
    }

    /**
     * @return array
     */
    protected function getFiles()
    {
        // config/dev/bundles.php
        $modes = $this->serverMode->all();

        // config/bundles.php
        $modes[] = '';

        $files = array();

        foreach ($modes as $mode) {
            if (is_file($file = $this->buildPath($mode))) {
                $files[] = $file;
            }
        }

        return $files;
    }

    /**
     * Builds the file path.
     *
     * @param string $mode
     *
     * @throws RuntimeException
     * @return string
     */
    protected function buildPath($mode)
    {
        throw new RuntimeException('Child loader has not defined a buildPath() method');
    }
}
