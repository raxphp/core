<?php

namespace Rax\Helper\Base;

use Rax\Helper\Arr;

/**
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseText
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
     *     $str = Text::embedValues('hello %s', 'world');
     *     $str = Text::embedValues('%s %s', array('hello', 'world'));
     *     $str = Text::embedValues('{{greeting}} {{planet}}', array(
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
     *     Text::contains('sir', 'Hello sir!'); // true
     *     Text::contains('SIR', 'Hello sir!'); // false
     *
     *     // Case-insensitive
     *     Text::contains('SIR', 'Hello sir!', true); // true
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
