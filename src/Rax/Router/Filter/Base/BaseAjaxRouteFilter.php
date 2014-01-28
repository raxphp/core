<?php

namespace Rax\Router\Filter\Base;

use Rax\Http\Request;

/**
 * Ajax route filter.
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseAjaxRouteFilter
{
    /**
     * Filters a route by checking whether the request was made through Ajax.
     *
     * @param bool    $value
     * @param Request $request
     *
     * @return bool
     */
    public function filter($value, Request $request)
    {
        return ($value === $request->isAjax());
    }
}
