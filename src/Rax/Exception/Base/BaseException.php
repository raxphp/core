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
     * @param array     $values
     * @param Exception $previous
     */
    public function __construct($message = '', $values = array(), Exception $previous = null)
    {
        parent::__construct(Str::embedValues($message, $values), 0, $previous);
    }
}
