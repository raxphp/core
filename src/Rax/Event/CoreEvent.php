<?php

namespace Rax\Event;

/**
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
final class CoreEvent
{
    /**
     * @var string
     */
    const APP = 'core.app';

    /**
     * @var string
     */
    const STARTUP = 'core.startup';

    /**
     * @var string
     */
    const REQUEST = 'core.request';

    /**
     * @var string
     */
    const CONTROLLER = 'core.controller';

    /**
     * @var string
     */
    const VIEW = 'core.view';

    /**
     * @var string
     */
    const RESPONSE = 'core.response';

    /**
     * @var string
     */
    const SHUTDOWN = 'core.shutdown';

    /**
     * @var string
     */
    const EVENT_TRIGGERED = 'core.event_triggered';
}
