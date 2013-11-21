<?php

namespace Rax\Routing\Filter\Base;

use Rax\Exception\Exception;
use Rax\Helper\Php;
use Rax\Routing\Route;
use Rax\Server\ServerMode;

/**
 * @author    Gregorio Ramirez <goyocode@gmail.com>
 * @copyright Copyright (c) Gregorio Ramirez <goyocode@gmail.com>
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD
 */
class BaseServerModeRouteFilter
{
    /**
     * @throws Exception
     *
     * @param string             $value
     * @param ServerMode         $serverMode
     * @param Route $route
     *
     * @return bool
     */
    public function filter($value, ServerMode $serverMode, Route $route)
    {
        if (!is_string($value)) {
            throw new Exception('Route "%s", serverMode filter expects a string, got %s', array(
                $route->getName(),
                Php::getDataType($value),
            ));
        }

        if (!strlen($value)) {
            throw new Exception('Route "%s", serverMode filter is missing a value', array(
                $route->getName(),
            ));
        }

        foreach (explode('|', $value) as $mode) {
            if ($serverMode->is($mode)) {
                return true;
            }
        }

        return false;
    }
}
