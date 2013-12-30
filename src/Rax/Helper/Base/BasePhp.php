<?php

namespace Rax\Helper\Base;

use Closure;
use Rax\Exception\Exception;

/**
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BasePhp
{
    /**
     * Loads a file within an empty scope and returns the output.
     *
     *     // e.g. file holding a configuration array
     *     return array(...);
     *
     *     $array = PhpHelper::load($file);
     *
     * @throws Exception
     *
     * @param string $file
     *
     * @return mixed
     */
    public static function load($file)
    {
        if (!is_array($array = require $file)) {
            throw new Exception('%s does not return an array', $file);
        }

        return $array;
    }

    /**
     * @param Closure|mixed $value
     *
     * @return mixed
     */
    public static function value($value)
    {
        return ($value instanceof Closure) ? $value() : $value;
    }

    /**
     * Gets the data type of the variable.
     *
     * In the case of an object, the name of the class is returned.
     *
     *     gettype(new Exception());          // "object"
     *     Php::getDataType(new Exception()); // "Exception"
     *
     * @param mixed $value
     *
     * @return string
     */
    public static function getDataType($value)
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }

    /**
     * Gets the result of var_export() with the coding style adjusted.
     *
     * @param mixed $code
     *
     * @return string
     */
    public static function varExport($code)
    {
        return strtr(var_export($code, true), array(
            '  '      => '    ',
            'array (' => 'array(',
        ));
    }

    /**
     * Gets the class name without the namespaces.
     *
     *     $bar = new Rax\Foo\Bar();
     *
     *     $className = Php::getClassName($bar); // "Bar"
     *
     * @param string|object $obj
     *
     * @return string
     */
    public static function getClassName($obj)
    {
        if (is_object($obj)) {
            $obj = get_class($obj);
        }

        $class = explode('\\', $obj);

        return end($class);
    }
}
