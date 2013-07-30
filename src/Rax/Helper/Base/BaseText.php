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
}
