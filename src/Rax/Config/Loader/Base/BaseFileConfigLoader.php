<?php

namespace Rax\Config\Loader\Base;

use Rax\Config\Loader\ConfigLoaderInterface;
use Rax\Helper\Arr;
use Rax\Helper\Php;
use Rax\Bundle\Cfs;
use Rax\Exception\Exception;

/**
 * Data file loader.
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseFileConfigLoader implements ConfigLoaderInterface
{
    /**
     * @var string
     */
    protected $dir;

    /**
     * @var Cfs
     */
    protected $cfs;

    /**
     * @param string $dir
     * @param Cfs    $cfs
     */
    public function __construct($dir = null, Cfs $cfs = null)
    {
        $this->dir = $dir;
        $this->cfs = $cfs;
    }

    /**
     * Sets the name of the directory holding the data files.
     *
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
     * Returns the directory holding the data files.
     *
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * {@inheritdoc}
     */
    public function load($name)
    {
        if (!$files = $this->cfs->findFiles($this->dir, $name)) {
            throw new Exception('Could not locate a data file for "%s"', $name);
        }

        $files = array_reverse($files);

        $data = array();

        foreach ($files as $file) {
            $data = Arr::merge($data, Php::load($file));
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function save(array $data)
    {
    }
}
