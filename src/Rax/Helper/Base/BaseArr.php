<?php

namespace Rax\Helper\Base;

use ArrayAccess;
use ArrayObject;
use Rax\Exception\Exception;
use Rax\Helper\Php;

/**
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseArr
{
    /**
     * Checks if the parameter is an array or array like object.
     *
     *     // true
     *     Arr::isArray(array());
     *     Arr::isArray(new ArrayObject());
     *
     *     // false
     *     Arr::isArray('a');
     *     Arr::isArray(123);
     *
     * @param array|ArrayAccess $arr
     *
     * @return bool
     */
    public static function isArray($arr)
    {
        return (is_array($arr) || $arr instanceof ArrayAccess);
    }

    /**
     * Checks if the parameter is an associative array.
     *
     *     Arr::isAssociative(array('a' => 'b')); // true
     *     Arr::isAssociative(array('a'));        // false
     *
     * @param array|ArrayObject $arr
     *
     * @return bool
     */
    public static function isAssociative($arr)
    {
        if ($arr instanceof ArrayObject) {
            $arr = $arr->getArrayCopy();
        }

        if (!is_array($arr) || empty($arr)) {
            return false;
        }

        return ($arr !== array_values($arr));
    }

    /**
     * Checks if the parameter is a numeric array.
     *
     *     Arr::isNumeric(array('a'));        // true
     *     Arr::isNumeric(array('a' => 'b')); // false
     *
     * @param array|ArrayObject $arr
     *
     * @return bool
     */
    public static function isNumeric($arr)
    {
        if ($arr instanceof ArrayObject) {
            $arr = $arr->getArrayCopy();
        }

        if (!is_array($arr) || empty($arr)) {
            return false;
        }

        return ($arr === array_values($arr));
    }

    /**
     * array_unshift() for associative arrays. Prepends a key=>value item to the
     * beginning of an array.
     *
     *     $arr = array('b' => 'b');
     *
     *     Arr::unshift($arr, 'a', 'a'); // array("a" => "a", "b" => "b")
     *     array_unshift($arr, 'a');     // array(0   => "a", "b" => "b")
     *
     * @param array|ArrayAccess  $arr
     * @param string             $key
     * @param mixed              $value
     *
     * @return array
     */
    public static function unshift(array &$arr, $key, $value)
    {
        return ($arr = array($key => $value) + $arr);
    }

    /**
     * Sets a value on an array using dot notation.
     *
     *     $array = array();
     *     Arr::set($array, 'one.two.three', 'wut');
     *
     *     array(
     *         "one" => array(
     *             "two" => array(
     *                 "three" => "wut",
     *             )
     *         )
     *     )
     *
     * @throws Exception
     *
     * @param array|ArrayAccess $arr
     * @param array|string      $key
     * @param mixed             $value
     */
    public static function set(&$arr, $key, $value = null)
    {
        if (!static::isArray($arr)) {
            throw new Exception('Arr::set() expects parameter 1 to be an array or ArrayAccess object, %s given', Php::getDataType($arr));
        }

        if (is_array($key)) {
            foreach ($key as $tmpKey => $tmpValue) {
                static::set($arr, $tmpKey, $tmpValue);
            }
        } else {
            $keys = explode('.', $key);

            while (count($keys) > 1) {
                $key = array_shift($keys);

                if (!isset($arr[$key]) || !static::isArray($arr[$key])) {
                    $arr[$key] = array();
                }

                $arr =& $arr[$key];
            }

            $arr[array_shift($keys)] = $value;
        }
    }

    /**
     * Gets the value found in the array or array like object at the specified
     * index or dot notation path.
     *
     * This function also helps avoid the dreaded notice that's thrown when you
     * try to access an undefined index.
     *
     *     $arr = array(
     *         'one' => array(
     *             'two' => 2,
     *         ),
     *         'three' => 3,
     *         'four'  => 4,
     *     );
     *
     *     Arr::get($arr, 'one');                  // array("two" => 2)
     *     Arr::get($arr, 'one.two');              // 2
     *     Arr::get($arr, array('three', 'four')); // array("three" => 3, "four" => 4)
     *
     * @throws Exception
     *
     * @param array|ArrayAccess $arr
     * @param array|string      $key
     * @param mixed             $default
     *
     * @return mixed
     */
    public static function get($arr, $key = null, $default = null)
    {
        if (!static::isArray($arr)) {
            throw new Exception('Arr::get() expects parameter 1 to be an array or ArrayAccess object, %s given', Php::getDataType($arr));
        }

        if (is_array($key)) {
            $tmp = array();
            foreach ($key as $tmpKey) {
                $tmp[$tmpKey] = static::get($arr, $tmpKey, $default);
            }

            return $tmp;
        }

        if (null === $key) {
            return $arr;
        }

        $keys = explode('.', $key);

        foreach ($keys as $key) {
            if ((is_array($arr) && array_key_exists($key, $arr)) ||
                ($arr instanceof ArrayAccess && $arr->offsetExists($key))
            ) {
                $arr = $arr[$key];
            } else {
                return $default;
            }
        }

        return $arr;
    }

    /**
     * Unsets an array item using dot notation.
     *
     *     $arr = array(
     *         'one' => array(
     *             'two'   => 2,
     *             'three' => 3,
     *         ),
     *     );
     *
     *     Arr::delete($arr, 'one.two');
     *
     *     array(
     *         "one" => array(
     *             "three" => 3,
     *         )
     *     )
     *
     * @param array|ArrayAccess $arr
     * @param array|string      $key
     *
     * @return array|bool
     */
    public static function remove(&$arr, $key)
    {
        if (is_array($key)) {
            $tmp = array();
            foreach ($key as $tmpKey) {
                $tmp[$tmpKey] = static::remove($arr, $tmpKey);
            }

            return $tmp;
        }

        $keys    = explode('.', $key);
        $currKey = array_shift($keys);

        if ((!is_array($arr) || !array_key_exists($currKey, $arr)) &&
            (!$arr instanceof ArrayAccess || !$arr->offsetExists($currKey))
        ) {
            return false;
        }

        if (!empty($keys)) {
            $key = implode('.', $keys);

            return static::remove($arr[$currKey], $key);
        } else {
            unset($arr[$currKey]);
        }

        return true;
    }

    /**
     * Checks if the key exists in the array; accepts dot notation.
     *
     *     $arr = array(
     *         'one' => array(
     *             'two'   => 2,
     *         ),
     *     );
     *
     *     Arr::has($arr, 'one.two');   // true
     *     Arr::has($arr, 'one.three'); // false
     *
     * @throws Exception
     *
     * @param array|ArrayAccess $arr
     * @param array|string      $key
     *
     * @return bool
     */
    public static function has($arr, $key)
    {
        if (!static::isArray($arr)) {
            throw new Exception('Arr::has() expects parameter 1 to be an array or ArrayAccess object, %s given', Php::getDataType($arr));
        }

        if (is_array($key)) {
            foreach ($key as $tmpKey) {
                if (!static::has($arr, $tmpKey)) {
                    return false;
                }
            }
        } else {
            $keys = explode('.', $key);

            foreach ($keys as $key) {
                if ((is_array($arr) && array_key_exists($key, $arr)) ||
                    ($arr instanceof ArrayAccess && $arr->offsetExists($key))
                ) {
                    $arr = $arr[$key];
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param array|ArrayAccess $a
     * @param array|ArrayAccess $b
     *
     * @return array|ArrayAccess
     */
    public static function merge(array $a, array $b)
    {
        foreach ($b as $key => $value) {
            if (array_key_exists($key, $a)) {
                if (is_int($key)) {
                    $a[] = $value;
                } elseif (is_array($value) && is_array($a[$key])) {
                    $a[$key] = static::merge($a[$key], $value);
                } else {
                    $a[$key] = $value;
                }
            } else {
                $a[$key] = $value;
            }
        }

        return $a;
    }
}
