<?php

namespace Rax\Bundle\Loader\Base;

use Rax\Bundle\Loader\AbstractBundleLoader;
use Rax\Bundle\Loader\BundleLoaderInterface;

/**
 * @author    Gregorio Ramirez <goyocode@gmail.com>
 * @copyright Copyright (c) Gregorio Ramirez <goyocode@gmail.com>
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD
 */
class BaseDirBundleLoader extends AbstractBundleLoader
{
    /**
     * {@inheritdoc}
     */
    protected function buildPath($mode)
    {
        $subdir = '';

        if ($mode) {
            $subdir = $mode.DS;
        }

        return $this->dir.DS.$subdir.$this->basename.'.php';
    }
}
