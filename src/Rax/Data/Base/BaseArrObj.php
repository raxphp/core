<?php

namespace Rax\Data\Base;

use ArrayObject;
use Rax\Helper\Arr;
use Rax\Data\ArrObj;

/**
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseArrObj extends ArrayObject
{
    /**
     * @param array|object $input
     * @param int          $flags
     */
    public function __construct($input = null, $flags = ArrayObject::ARRAY_AS_PROPS)
    {
        parent::__construct($input, $flags);
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        Arr::set($this, $key, $value);

        return $this;
    }

    /**
     * Gets a value from the configuration.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return ArrObj|mixed
     */
    public function get($key = null, $default = null)
    {
        return Arr::get($this, $key, $default);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return Arr::has($this, $key);
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    public function remove($key)
    {
        Arr::remove($this, $key);

        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function unshift($key, $value)
    {
        $this->exchangeArray(array($key => $value) + $this->getArrayCopy());

        return $this;
    }

    /**
     * Casts object into an array and returns it.
     *
     * Alias for `ArrayObject::getArrayCopy()`.
     *
     * @return array
     */
    public function asArray()
    {
        return $this->getArrayCopy();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return serialize($this->getArrayCopy());
    }
}
