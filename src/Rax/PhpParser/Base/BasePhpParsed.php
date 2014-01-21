<?php

namespace Rax\PhpParser\Base;

/**
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BasePhpParsed
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @param string $class
     *
     * @return $this
     */
    public function setClassName($class)
    {
        $this->className = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $namespace
     *
     * @return $this
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getFqn()
    {
        return $this->namespace.'\\'.$this->className;
    }
}
