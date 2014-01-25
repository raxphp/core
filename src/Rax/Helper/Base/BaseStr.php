<?php

namespace Rax\Helper\Base;

use Rax\Helper\Arr;

/**
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseStr
{
    /**
     * Delimiter used in a path to separate words.
     *
     * @var string
     */
    const PATH_DELIMITER = '.';

    /**
     * Embeds values into a string using either sprintf() or strtr().
     *
     *     // "hello world"
     *     $str = Str::embedValues('hello %s', 'world');
     *     $str = Str::embedValues('%s %s', array('hello', 'world'));
     *     $str = Str::embedValues('{{greeting}} {{planet}}', array(
     *         '{{greeting}}' => 'hello',
     *         '{{planet}}'   => 'world',
     *     ));
     *
     * @param string       $str
     * @param array|string $values
     *
     * @return string
     */
    public static function embedValues($str, $values = null)
    {
        if (null === $values) {
            return $str;
        }

        $values = is_array($values) ? $values : array($values);

        if (Arr::isAssociative($values)) {
            $str = strtr($str, $values);
        } else {
            array_unshift($values, $str);
            $str = call_user_func_array('sprintf', $values);
        }

        return $str;
    }

    /**
     * Checks if string contains substring at least once.
     *
     *     // Case-sensitive
     *     Str::contains('sir', 'Hello sir!'); // true
     *     Str::contains('SIR', 'Hello sir!'); // false
     *
     *     // Case-insensitive
     *     Str::contains('SIR', 'Hello sir!', true); // true
     *
     * @param string $needle
     * @param string $haystack
     * @param bool   $isCaseInsensitive
     *
     * @return bool
     */
    public static function contains($needle, $haystack, $isCaseInsensitive = false)
    {
        if ($isCaseInsensitive) {
            return (false !== stripos($haystack, $needle));
        } else {
            return (false !== strpos($haystack, $needle));
        }
    }
}
