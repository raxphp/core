<?php

namespace Rax\Server\Base;

use Exception;
use InvalidArgumentException;
use ReflectionObject;

/**
 * ServerMode represents the application environment.
 *
 * It is a good practice to define the server mode at the server level:
 *
 * - Apache: SetEnv SERVER_MODE development
 * - Nginx:  fastcgi_param SERVER_MODE development
 * - Shell:  export SERVER_MODE=development
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseServerMode
{
    /**
     * Server modes:
     *
     * Production:  Server accessible by all users.
     * Staging:     Production clone used by team and client to test changes
     *              before they go live.
     * Testing:     Development clone used by team and developer to test changes
     *              before pushing to staging.
     * Development: Local machine.
     */
    const PRODUCTION  = 400;
    const STAGING     = 300;
    const TESTING     = 200;
    const DEVELOPMENT = 100;

    /**
     * @var int
     */
    protected $mode;

    /**
     * The server mode can be optionally set through the constructor.
     *
     *     $serverMode = new ServerMode($_SERVER['SERVER_MODE']);
     *
     *     // Or through the setter
     *     $serverMode = new ServerMode();
     *     $serverMode->set($_SERVER['SERVER_MODE']);
     *
     * @param int|string $mode
     */
    public function __construct($mode = null)
    {
        if (null !== $mode) {
            $this->set($mode);
        }
    }

    /**
     * Sets the server mode.
     *
     * Ultimately the value is stored as an integer to allow greater than logic
     * when comparing modes.
     *
     * NOTE: The server mode will be set automatically if defined at the server level.
     *
     *     // Integer
     *     $serverMode->set(ServerMode::DEVELOPMENT);
     *
     *     // String
     *     $serverMode->set('development');
     *
     * @throws InvalidArgumentException
     *
     * @param int|string $mode
     *
     * @return $this
     */
    public function set($mode)
    {
        if (is_int($mode)) {
            $this->mode = $mode;
        } elseif (is_string($mode)) {
            $this->mode = constant(get_class($this).'::'.strtoupper($mode));
        } else {
            throw new InvalidArgumentException(sprintf('Server mode must be an integer or string, %s given', gettype($mode)));
        }

        return $this;
    }

    /**
     * Gets the server mode.
     *
     * The server mode is stored as an integer to allow greater than logic when
     * comparing modes.
     *
     *     $mode = $serverMode->get(); // 100
     *
     * @return int
     */
    public function get()
    {
        return $this->mode;
    }

    /**
     * Gets the server mode as a string e.g. "development".
     *
     *     $mode = $serverMode->getName(); // "development"
     *
     * @throws Exception
     *
     * @return string
     */
    public function getName()
    {
        $reflection = new ReflectionObject($this);

        // e.g. DEVELOPMENT => 100
        foreach ($reflection->getConstants() as $name => $mode) {
            if ($this->mode === $mode) {
                return strtolower($name);
            }
        }

        throw new Exception(sprintf('The current server mode "%s" has no class constant holding its integer value', $this->mode));
    }

    /**
     * Gets the server mode's short name e.g. "dev".
     *
     * A short name represents a range of server modes that are alike in configuration:
     *
     * - prod: Any mode that falls between staging and production.
     * - dev: Any mode that falls between development and testing.
     *
     *     $mode = $serverMode->getShortName(); // "dev"
     *
     * @throws Exception
     *
     * @return string
     */
    public function getShortName()
    {
        if ($this->isProd()) {
            return 'prod';
        }

        if ($this->isDev()) {
            return 'dev';
        }

        throw new Exception(sprintf('Current server mode "%s" is not within the dev-prod range', $this->mode));
    }

    /**
     * Gets the relevant server names.
     *
     * @return array
     */
    public function all()
    {
        $modes   = array();
        $modes[] = $this->getName();
        $modes[] = $this->getShortName();

        return $modes;
    }

    /**
     * Checks if the supplied mode is the current server mode.
     *
     * @param int|string $mode
     *
     * @return bool
     */
    public function is($mode)
    {
        if ($mode === $this->mode) {
            return true;
        }

        if (is_string($mode)) {
            $mode = strtolower($mode);

            if ($mode === $this->getShortName()) {
                return true;
            }

            if ($mode === $this->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the server mode is "production".
     *
     * @return bool
     */
    public function isProduction()
    {
        return ($this->mode === static::PRODUCTION);
    }

    /**
     * Checks if the server mode is "staging".
     *
     * @return bool
     */
    public function isStaging()
    {
        return ($this->mode === static::STAGING);
    }

    /**
     * Checks if the server mode is "testing".
     *
     * @return bool
     */
    public function isTesting()
    {
        return ($this->mode === static::TESTING);
    }

    /**
     * Checks if the server mode is "development".
     *
     * @return bool
     */
    public function isDevelopment()
    {
        return ($this->mode === static::DEVELOPMENT);
    }

    /**
     * Checks if the server mode is "staging" or "production".
     *
     * @return bool
     */
    public function isProd()
    {
        return (
            ($this->mode <= static::PRODUCTION) &&
            ($this->mode > static::TESTING)
        );
    }

    /**
     * Checks if the server mode is "testing" or "development".
     *
     * @return bool
     */
    public function isDev()
    {
        return (
            ($this->mode <= static::TESTING) &&
            ($this->mode >= 0)
        );
    }
}
