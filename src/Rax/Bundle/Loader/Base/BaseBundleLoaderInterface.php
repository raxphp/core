<?php

namespace Rax\Bundle\Loader\Base;

/**
 * @author    Gregorio Ramirez <goyocode@gmail.com>
 * @copyright Copyright (c) Gregorio Ramirez <goyocode@gmail.com>
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD
 */
interface BaseBundleLoaderInterface
{
    /**
     * @return array
     */
    public function load();
}
