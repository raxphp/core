<?php

namespace Rax\Server\Base;

use Exception;
use InvalidArgumentException;
use ReflectionObject;

/**
 * ServerMode holds and manages the server mode (aka application environment).
 *
 * Define the server mode at the server level:
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
     * - Production  (400-499): Production server (caching on, errors off, etc)
     *                          accessible by end users.
     * - Staging     (300-399): Same as production but runs on a server used for
     *                          testing purposes by the team and client.
     * - Testing     (200-299): Same as development but runs on a server used
     *                          for testing purposes by the team.
     * - Development (100-199): Local machine with dev settings (caching off,
     *                          errors on, etc) used by the developer.
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
     * NOTE: The server mode will be set automatically by the application at
     * runtime if defined at the server level.
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
     * NOTE: The server mode will be set automatically by the application at
     * runtime if defined at the server level.
     *
     *     // By integer
     *     $serverMode->set(ServerMode::DEVELOPMENT);
     *
     *     // By string
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
     * comparing modes. To retrieve the server mode name or short name use
     * {@see ServerMode::getName()} or {@see ServerMode::getShortName()}
     *
     *     $mode = $serverMode->get();          // 100
     *     $mode = $serverMode->getName();      // "development"
     *     $mode = $serverMode->getShortName(); // "dev"
     *
     * @return int
     */
    public function get()
    {
        return $this->mode;
    }

    /**
     * Gets the server mode' name e.g. "staging".
     *
     * NOTE: A constant holding the server mode must be defined.
     *
     *     // The 100 mode is represented by the "development" name
     *     const DEVELOPMENT = 100;
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

        // e.g. PRODUCTION => 400
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
     * - dev:  Any mode that falls between development and testing.
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
     * Gets the server mode's name and short name.
     *
     *     $modeNames = $serverMode->all();
     *
     *     // Result
     *     Array
     *     (
     *         [0] => development
     *         [1] => dev
     *     )
     *
     * @return array
     */
    public function all()
    {
        return array($this->getName(), $this->getShortName());
    }

    /**
     * Checks if the parameter is the current server mode.
     *
     *     if ($serverMode->is(ServerMode::DEVELOPMENT)) {
     *     if ($serverMode->is('development')) {
     *     if ($serverMode->is('dev')) {
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

        if (!is_string($mode)) {
            return false;
        }

        if ($mode === $this->getShortName()) {
            return true;
        }

        if ($mode === $this->getName()) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the server mode is production.
     *
     *     if ($serverMode->isProduction()) {
     *
     * @return bool
     */
    public function isProduction()
    {
        return ($this->mode === static::PRODUCTION);
    }

    /**
     * Checks if the server mode is staging.
     *
     *     if ($serverMode->isStaging()) {
     *
     * @return bool
     */
    public function isStaging()
    {
        return ($this->mode === static::STAGING);
    }

    /**
     * Checks if the server mode is testing.
     *
     *     if ($serverMode->isTesting()) {
     *
     * @return bool
     */
    public function isTesting()
    {
        return ($this->mode === static::TESTING);
    }

    /**
     * Checks if the server mode is development.
     *
     *     if ($serverMode->isDevelopment()) {
     *
     * @return bool
     */
    public function isDevelopment()
    {
        return ($this->mode === static::DEVELOPMENT);
    }

    /**
     * Checks if the server mode falls between the production range (400-499).
     *
     *     if ($serverMode->isProductionIsh()) {
     *
     * @return bool
     */
    public function isProductionIsh()
    {
        return ($this->mode >= static::PRODUCTION) && ($this->mode <= 499);
    }

    /**
     * Checks if the server mode falls between the staging range (300-399).
     *
     *     if ($serverMode->isStagingIsh()) {
     *
     * @return bool
     */
    public function isStagingIsh()
    {
        return ($this->mode >= static::STAGING) && ($this->mode <= 399);
    }

    /**
     * Checks if the server mode falls between the testing range (200-299).
     *
     *     if ($serverMode->isTestingIsh()) {
     *
     * @return bool
     */
    public function isTestingIsh()
    {
        return ($this->mode >= static::TESTING) && ($this->mode <= 299);
    }

    /**
     * Checks if the server mode falls between the development range (100-199).
     *
     *     if ($serverMode->isDevelopmentIsh()) {
     *
     * @return bool
     */
    public function isDevelopmentIsh()
    {
        return ($this->mode >= static::DEVELOPMENT) && ($this->mode <= 199);
    }

    /**
     * Checks if the server mode is prod.
     *
     * Prod represents all the server modes that fall between the range of
     * staging and production. This allows you to run the same logic on both
     * environments since they should behave exactly the same.
     *
     * - Production (400-499): Production server (caching on, errors off, etc)
     *                         accessible by end users.
     * - Staging    (300-399): Same as production but runs on a server used for
     *                         testing purposes by the team and client.
     *
     *     if ($serverMode->isProd()) {
     *
     * @return bool
     */
    public function isProd()
    {
        return (($this->mode >= static::STAGING) && ($this->mode <= 499));
    }

    /**
     * Checks if the server mode is dev.
     *
     * Dev represents all the server modes that fall between the range of
     * development and testing. This allows you to run the same logic on both
     * environments since they should behave exactly the same.
     *
     * - Development (100-199): Local machine with dev settings (caching off,
     *                          errors on, etc).
     * - Testing     (200-299): Same as development but runs on a server used
     *                          for testing purposes by the team.
     *
     *     if ($serverMode->isDev()) {
     *
     * @return bool
     */
    public function isDev()
    {
        return (($this->mode >= static::DEVELOPMENT) && ($this->mode <= 299));
    }
}
