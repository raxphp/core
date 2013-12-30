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
    protected $class;

    /**
     * @var string
     */
    protected $fqn;

    /**
     * @param string $class
     *
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $fqn
     *
     * @return $this
     */
    public function setFqn($fqn)
    {
        $this->fqn = $fqn;

        return $this;
    }

    /**
     * @return string
     */
    public function getFqn()
    {
        return $this->fqn;
    }
}
