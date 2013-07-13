<?php

namespace Rax\Server\Base;

use Exception;
use ReflectionClass;
use InvalidArgumentException;

/**
 * The ServerMode class stores and manages the server mode (a.k.a application
 * environment).
 *
 * @author    Gregorio Ramirez <goyocode@gmail.com>
 * @copyright Copyright (c) Gregorio Ramirez <goyocode@gmail.com>
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD
 */
class BaseServerMode
{
    /**
     * Server modes:
     *
     * Production:  Server accessible by end users.
     * Staging:     Server with production settings used by team and client to
     *              test changes before they go live.
     * Testing:     Server with development settings used by team and developer
     *              to test changes before pushing to staging.
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
     * The accepted values can be an integer, mostly like by passing in a
     * constant e.g. ServerMode::DEVELOPMENT, or a string e.g. "development".
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
     * Gets the current server mode.
     *
     * The value is returned as an integer to allow greater-than logic in
     * conditional statements.
     *
     * @return int
     */
    public function get()
    {
        return $this->mode;
    }

    /**
     * Gets the string representation of the current server mode
     * e.g. "development".
     *
     * @throws Exception
     * @return string
     */
    public function getName()
    {
        $reflection = new ReflectionClass(get_class($this));

        foreach ($reflection->getConstants() as $name => $value) {
            if ($this->mode === $value) {
                return strtolower($name);
            }
        }

        throw new Exception(sprintf('The current server mode "%s" has no class constant holding its integer value', $this->mode));
    }

    /**
     * Gets the short name of the current server mode e.g. "dev".
     *
     * A short name usually represents a range of modes e.g. "dev" is returned
     * for any mode between "development" and "testing".
     *
     * @throws Exception
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
    public function getNames()
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
     * Checks if the current server mode is "production".
     *
     * @return bool
     */
    public function isProduction()
    {
        return ($this->mode === static::PRODUCTION);
    }

    /**
     * Checks if the current server mode is "staging".
     *
     * @return bool
     */
    public function isStaging()
    {
        return ($this->mode === static::STAGING);
    }

    /**
     * Checks if the current server mode is "testing".
     *
     * @return bool
     */
    public function isTesting()
    {
        return ($this->mode === static::TESTING);
    }

    /**
     * Checks if the current server mode is "development".
     *
     * @return bool
     */
    public function isDevelopment()
    {
        return ($this->mode === static::DEVELOPMENT);
    }

    /**
     * Checks if the current server mode is "staging" or "production".
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
     * Checks if the current server mode is "testing" or "development".
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
