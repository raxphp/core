<?php

namespace Rax\Helper\Base;

use Closure;

/**
 * Php provides custom PHP functions.
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BasePhp
{
    /**
     * Loads a file within an empty scope and returns the output.
     *
     *     // e.g. file holding a configuration array
     *     <?php
     *
     *     return array();
     *
     *     // $file holds the full path to file above
     *     $config = Php::load($file);
     *
     * @param string $file
     *
     * @return mixed
     */
    public static function load($file)
    {
        /** @noinspection PhpIncludeInspection */
        return (require $file);
    }

    /**
     * Returns the passed in value.
     *
     * If the value is a closure its result is returned instead.
     *
     * NOTE: This is a low level function that allows callbacks to be passed in as the
     * default value in methods that support defaults e.g. {@see Arr::get()}.
     *
     *     // "foo"
     *     $foo = Php::value('foo');
     *     $foo = Php::value(function() {
     *         return 'foo';
     *     });
     *
     * @param Closure|mixed $value
     *
     * @return mixed
     */
    public static function value($value)
    {
        return ($value instanceof Closure) ? $value() : $value;
    }

    /**
     * Gets the parameter's data type.
     *
     * If the parameter is an object, its class name is returned.
     *
     *     $dataType = Php::dataType(new Exception()); // "Exception"
     *
     *     // Vs
     *     $dataType = gettype(new Exception()); // "object"
     *
     * @param mixed $value
     *
     * @return string
     */
    public static function dataType($value)
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }

    /**
     * Gets the source code of a variable.
     *
     * Basically `var_export()` with the coding style adjusted.
     *
     *     $code = array('key' => 'value');
     *
     *     $sourceCode = Php::sourceCode($code);
     *
     *     // Result
     *     array(
     *         'key' => 'value',
     *     )
     *
     *     // Vs
     *     $sourceCode = var_export($code);
     *
     *     // Result
     *     array (
     *       'key' => 'value',
     *     )
     *
     * @param mixed $code
     *
     * @return string
     */
    public static function sourceCode($code)
    {
        return strtr(var_export($code, true), array(
            '  '      => '    ',
            'array (' => 'array(',
        ));
    }

    /**
     * Gets the class name of an object without the namespace.
     *
     *     $bar = new Rax\Foo\Bar();
     *
     *     $className = Php::className($bar); // "Bar"
     *
     * @param string|object $obj
     *
     * @return string
     */
    public static function className($obj)
    {
        if (is_object($obj)) {
            $obj = get_class($obj);
        }

        $class = explode('\\', $obj);

        return end($class);
    }
}
