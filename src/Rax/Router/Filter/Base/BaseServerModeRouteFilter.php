<?php

namespace Rax\Router\Filter\Base;

use Rax\Server\ServerMode;

/**
 * Server mode route filter.
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseServerModeRouteFilter
{
    /**
     * Filters a route by checking the server mode value.
     *
     * @param string     $value
     * @param ServerMode $serverMode
     *
     * @return bool
     */
    public function filter($value, ServerMode $serverMode)
    {
        foreach (explode('|', $value) as $mode) {
            if ($serverMode->is($mode)) {
                return true;
            }
        }

        return false;
    }
}
