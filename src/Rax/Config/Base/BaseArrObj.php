<?php

namespace Rax\Config\Base;

use ArrayAccess;
use ArrayObject;
use Rax\Helper\Arr;
use Rax\Config\ArrObj;

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
     * @param bool   $useDotNotation
     * @param bool   $throwIfNotFound
     *
     * @return ArrObj|mixed
     */
    public function get($key = null, $default = null, $useDotNotation = true, $throwIfNotFound = false)
    {
        return Arr::get($this, $key, $default, $useDotNotation, $throwIfNotFound);
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
     *
     * @return bool
     */
    public function has($key)
    {
        return Arr::has($this, $key);
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
     * @param array|ArrayAccess $arr
     *
     * @return $this
     */
    public function merge($arr)
    {
        Arr::merge($this, $arr);

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
