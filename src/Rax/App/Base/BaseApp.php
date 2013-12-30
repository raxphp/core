<?php

namespace Rax\App\Base;

use Rax\EventManager\CoreEvent;
use Rax\EventManager\EventManager;

/**
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseApp
{
    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @param EventManager $eventManager
     */
    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * @return $this
     */
    public function run()
    {
        $this->eventManager->trigger('core.app');

        return $this;
    }
}
