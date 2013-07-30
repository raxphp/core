<?php

namespace Rax\App\Base;

use Rax\Container\Container;
use Rax\Http\Request;

/**
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseApp
{
    public function run()
    {
        echo 1;
//        $response = $this->handle($request);
//        $response->send();
    }

    public function handle(Request $request)
    {

    }
}
