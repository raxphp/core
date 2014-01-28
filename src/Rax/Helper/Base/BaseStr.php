<?php

namespace Rax\Helper\Base;

use Rax\Helper\Arr;

/**
 * Str provides string manipulation functions.
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseStr
{
    /**
     * Embeds values into a string using either sprintf() or strtr().
     *
     *     // "hello world"
     *     Str::embed('hello %s', 'world');
     *     Str::embed('%s %s', array('hello', 'world'));
     *     Str::embed(':greeting :planet', array(
     *         ':greeting' => 'hello',
     *         ':planet'   => 'world',
     *     ));
     *
     * @param string $str
     * @param mixed  $values
     *
     * @return string
     */
    public static function embed($str, $values)
    {
        $values = (array) $values;

        if (Arr::isAssociative($values)) {
            $str = strtr($str, $values);
        } else {
            array_unshift($values, $str);
            $str = call_user_func_array('sprintf', $values);
        }

        return $str;
    }

    /**
     * Checks if string contains substring.
     *
     *     // Case-sensitive
     *     Str::contains('sir', 'Hello sir!'); // true
     *     Str::contains('SIR', 'Hello sir!'); // false
     *
     *     // Case-insensitive
     *     Str::contains('SIR', 'Hello sir!', false); // true
     *
     * @param string $needle
     * @param string $haystack
     * @param bool   $caseSensitive
     *
     * @return bool
     */
    public static function contains($needle, $haystack, $caseSensitive = true)
    {
        if ($caseSensitive) {
            return (false !== strpos($haystack, $needle));
        } else {
            return (false !== stripos($haystack, $needle));
        }
    }
}
