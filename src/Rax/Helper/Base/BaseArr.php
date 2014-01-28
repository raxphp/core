<?php

namespace Rax\Helper\Base;

use ArrayAccess;
use ArrayObject;
use Rax\Exception\Exception;
use Rax\Helper\Php;

/**
 * Arr provides array manipulation functions.
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseArr
{
    /**
     * Checks if the parameter is an array or an array-like object.
     *
     *     // true
     *     Arr::isArray(array());
     *     Arr::isArray(new ArrayObject());
     *
     *     // false
     *     Arr::isArray('foo');
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
     * There's two types of array:
     *
     * - Associative e.g. `array('key' => 'value')`.
     * - Numeric e.g. array('value')`.
     *
     *     Arr::isAssociative(array('key' => 'value')); // true
     *
     *     Arr::isAssociative(array('value')); // false
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
     * There's two types of array:
     *
     * - Numeric e.g. array('value')`.
     * - Associative e.g. `array('key' => 'value')`.
     *
     *     Arr::isNumeric(array('value')); // true
     *
     *     Arr::isNumeric(array('key' => 'value')); // false
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
     * Adds a key => value to the top of an array.
     *
     * This method is `array_unshift()` for associative arrays.
     *
     *     $arr = array('b' => 'b');
     *
     *     Arr::unshift($arr, 'a', 'a');
     *
     *     // Result
     *     Array
     *     (
     *         [a] => a
     *         [b] => b
     *     )
     *
     *     // Vs
     *     array_unshift($arr, 'a');
     *
     *     // Result
     *     Array
     *     (
     *         [0] => a
     *         [b] => b
     *     )
     *
     * @param array|ArrayObject $arr
     * @param string            $key
     * @param mixed             $value
     *
     * @return array|ArrayObject
     */
    public static function unshift($arr, $key, $value)
    {
        if ($arr instanceof ArrayObject) {
            $arr->exchangeArray(array($key => $value) + $arr->getArrayCopy());

            return $arr;
        }

        return (array($key => $value) + $arr);
    }

    /**
     * Sets a value on an array using dot notation.
     *
     * NOTE: If the path exists already it will be overridden.
     *
     *     $arr = array();
     *
     *     Arr::set($arr, 'one.two.three', 3);
     *
     *     Array
     *     (
     *         [one] => Array
     *             (
     *                 [two] => Array
     *                     (
     *                         [three] => 3
     *                     )
     *             )
     *     )
     *
     * Multiple values may be set at once:
     *
     *     $user = array(
     *         'user' => array(
     *             'info' => array(
     *                 'firstName' => 'Gregorio',
     *             ),
     *         ),
     *     );
     *
     *     Arr::set($user, array(
     *         'user.info.phoneNumber' => '6192341234',
     *         'user.address.city'     => 'San Diego',
     *     ));
     *
     *     Array
     *     (
     *         [user] => Array
     *             (
     *                 [info] => Array
     *                     (
     *                         [firstName] => Gregorio
     *                         [phoneNumber] => 6192341234
     *                     )
     *                 [address] => Array
     *                     (
     *                         [city] => San Diego
     *                     )
     *             )
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
            throw new Exception('Arr::set() expects parameter 1 to be an array or array-like object, %s given', Php::getDataType($arr));
        }

        if (is_array($key)) {
            foreach ($key as $newKey => $newValue) {
                static::set($arr, $newKey, $newValue);
            }

            return;
        }

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

    /**
     * Gets a value from an array.
     *
     * If the index doesn't exist, `null` is returned.
     *
     *     $arr = array();
     *
     *     $foo = Arr::get($arr, 'foo'); // null
     *
     *     // Vs
     *     $foo = $arr['foo']; // Notice: Undefined index: foo
     *
     * You can override the default value:
     *
     *     Arr::get($arr, 'foo', 'bar'); // "bar"
     *
     * Dot notation may be used to retrieve a value form a nested array:
     *
     *     $arr = array(
     *         'one' => array(
     *             'two' => array(
     *                 'three' => 3,
     *             ),
     *         ),
     *     );
     *
     *     Arr::get($arr, 'one.two.three'); // 3
     *
     * Multiple values may be extracted at once:
     *
     *     $arr = array(
     *         'one'   => 1,
     *         'two'   => 2,
     *         'three' => 3,
     *     );
     *
     *     Arr::get($arr, array('one', 'two'));
     *
     *     // Result
     *     Array
     *     (
     *         [one] => 1
     *         [two] => 2
     *     )
     *
     * @throws Exception
     *
     * @param array|ArrayAccess $arr
     * @param array|string      $key
     * @param mixed             $default
     * @param bool              $useDotNotation
     *
     * @return mixed
     */
    public static function get($arr, $key = null, $default = null, $useDotNotation = true)
    {
        if (!static::isArray($arr)) {
            throw new Exception('Arr::get() expects parameter 1 to be an array or array-like object, %s given', Php::getDataType($arr));
        }

        if (is_array($key)) {
            $newArr = array();

            foreach (static::normalize($key, $default) as $newKey => $newDefault) {
                $newArr[$newKey] = static::get($arr, $newKey, $newDefault);
            }

            return $newArr;
        }

        if (null === $key) {
            return $arr;
        }

        $keys = $useDotNotation ? explode('.', $key) : array($key);

        foreach ($keys as $key) {
            if ((is_array($arr) && array_key_exists($key, $arr)) || ($arr instanceof ArrayAccess && $arr->offsetExists($key))) {
                $arr = $arr[$key];
            } else {
                return Php::value($default);
            }
        }

        return $arr;
    }

    /**
     * Removes an item from an array using dot notation.
     *
     *     $arr = array(
     *         'one' => array(
     *             'two'   => 2,
     *             'three' => 3,
     *         ),
     *     );
     *
     *     Arr::remove($arr, 'one.two');
     *
     *     // Result
     *     Array
     *     (
     *         [one] => Array
     *             (
     *                 [three] => 3
     *             )
     *     )
     *
     * Multiple values may be removed at once:
     *
     *     Arr::remove($user, array(
     *         'user.info.firstName',
     *         'user.address.city',
     *     ));
     *
     * @throws Exception
     *
     * @param array|ArrayAccess $arr
     * @param array|string      $key
     *
     * @return bool
     */
    public static function remove(&$arr, $key)
    {
        if (!static::isArray($arr)) {
            throw new Exception('Arr::remove() expects parameter 1 to be an array or array-like object, %s given', Php::getDataType($arr));
        }

        if (is_array($key)) {
            $newArr = array();

            foreach ($key as $newKey) {
                $newArr[$newKey] = static::remove($arr, $newKey);
            }

            return $newArr;
        }

        $keys    = explode('.', $key);
        $currKey = array_shift($keys);

        if ((!is_array($arr) || !array_key_exists($currKey, $arr)) && (!$arr instanceof ArrayAccess || !$arr->offsetExists($currKey))) {
            return false;
        }

        if (!empty($keys)) {
            return static::remove($arr[$currKey], implode('.', $keys));
        } else {
            unset($arr[$currKey]);
        }

        return true;
    }

    /**
     * Checks if the array has an item.
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
            throw new Exception('Arr::has() expects parameter 1 to be an array or array-like object, %s given', Php::getDataType($arr));
        }

        if (is_array($key)) {
            foreach ($key as $newKey) {
                if (!static::has($arr, $newKey)) {
                    return false;
                }
            }

            return true;
        }

        $keys = explode('.', $key);

        foreach ($keys as $key) {
            if ((is_array($arr) && array_key_exists($key, $arr)) || ($arr instanceof ArrayAccess && $arr->offsetExists($key))) {
                $arr = $arr[$key];
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Removes an item from an array.
     *
     *     $arr = array('foo' => 123);
     *
     *     // By key
     *     Arr::removeByKeyOrValue($arr, 'foo');
     *
     *     // By value
     *     Arr::removeByKeyOrValue($arr, 123);
     *
     * @param array|ArrayAccess $array
     * @param string|mixed      $key
     *
     * @return bool
     */
    public static function removeByKeyOrValue($array, $key)
    {
        if (is_array($key)) {
            $newArray = array();

            foreach ($key as $newKey) {
                $newArray[$newKey] = static::removeByKeyOrValue($array, $newKey);
            }

            return $newArray;
        }

        if (array_key_exists($key, $array)) {
            unset($array[$key]);

            return true;
        }

        if (static::removeByValue($array, $key)) {
            return true;
        }

        return false;
    }

    /**
     * @param array|ArrayAccess $arr
     * @param mixed             $value
     *
     * @return bool
     */
    public static function removeByValue($arr, $value)
    {
        if (false !== ($key = array_search($value, $arr))) {
            unset($arr[$key]);

            return true;
        }

        return false;
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

    /**
     * Normalizes the keys and values of an array.
     *
     *     // Useful when you have mix of numeric and associative keys
     *     $config = array(
     *         'one' => array(),
     *         'two' => array(),
     *         'three',
     *     );
     *
     *     // Before
     *     Array
     *     (
     *         [one] => Array
     *             (
     *             )
     *         [two] => Array
     *             (
     *             )
     *         [0] => three
     *     )
     *
     *     $config = Arr::normalize($config, array());
     *
     *     // After
     *     Array
     *     (
     *         [one] => Array
     *             (
     *             )
     *         [two] => Array
     *             (
     *             )
     *         [three] => Array
     *             (
     *             )
     *     )
     *
     * @param array $arr
     * @param mixed $default
     *
     * @return array
     */
    public static function normalize($arr, $default = null)
    {
        foreach ($arr as $key => $value) {
            if (is_int($key)) {
                $key   = $value;
                $value = $default;
            }

            $arr[$key] = $value;
        }

        return $arr;
    }

    /**
     * Transforms a value into an array if not already.
     *
     *     Arr::asArray(123);
     *
     *     // Result
     *     Array
     *     (
     *         [0] => 123
     *     )
     *
     * @param mixed $value
     *
     * @return array
     */
    public static function asArray($value)
    {
        return is_array($value) ? $value : array($value);
    }
}
