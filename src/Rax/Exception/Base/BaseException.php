<?php

namespace Rax\Exception\Base;

use Exception;
use Rax\Helper\Str;

/**
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseException extends Exception
{
    /**
     * @param string    $message
     * @param mixed     $values
     * @param Exception $previous
     */
    public function __construct($message = '', $values = null, Exception $previous = null)
    {
        if (null !== $values) {
            $message = Str::embed($message, $values);
        }

        parent::__construct($message, 0, $previous);
    }
}
