<?php

namespace Rax\Config\Observer\Base;

use Rax\Config\Config;

/**
 * Saves the queued config data to source.
 *
 * Observes the core.shutdown event.
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseConfigShutdownObserver
{
    /**
     * @param Config $config
     */
    public function trigger(Config $config)
    {
        $config->save();
    }
}
