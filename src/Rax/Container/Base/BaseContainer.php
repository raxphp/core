<?php

namespace Rax\Container\Base;

use Closure;
use Rax\Autoload\Autoload;
use Rax\Bundle\Cfs;
use Rax\Config\Config;
use Rax\Exception\Exception;
use Rax\Helper\Arr;
use Rax\Helper\Php;
use Rax\Helper\Str;
use Rax\PhpParser\PhpParser;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Container is an automatic dependency injection container.
 *
 * @property Autoload   autoload
 * @property Cfs        cfs
 * @property Config     config
 * @property PhpParser  phpParser
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseContainer
{
    /**
     * @var Closure[]
     */
    protected $services;

    /**
     * @var array
     */
    protected $aliases;

    /**
     * @var array
     */
    protected $shareable;

    /**
     * @var array
     */
    protected $nicknames;

    /**
     * @var array
     */
    protected $shared = array();

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->services  = array_filter($config->get('container/service'));
        $this->aliases   = array_filter($config->get('container/alias'));
        $this->shareable = $config->get('container/shareable');

        $this->loadNicknames($config->get('container/nickname'));
    }

    /**
     * Sets the services configuration.
     *
     * NOTE: Use the container/service configuration if possible to define services.
     *
     *     $container->setServices(array(
     *         'Vendor\\Namespace\\ClassName' => function(Something $something) {
     *             $bar = new Bar();
     *             $bar->setSomething($something);
     *
     *             return $bar;
     *         },
     *     ));
     *
     * @param Closure[] $services
     *
     * @return $this
     */
    public function setServices(array $services)
    {
        $this->services = $services;

        return $this;
    }

    /**
     * Gets the services configuration.
     *
     *     $services = $container->getServices();
     *
     * @return Closure[]
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Gets the configuration of a service.
     *
     *     $service = $container->getService('Vendor\\Namespace\\ClassName');
     *
     * @param string $fqn
     * @param mixed  $default
     *
     * @return Closure|mixed
     */
    public function getService($fqn, $default = null)
    {
        return isset($this->services[$fqn]) ? $this->services[$fqn] : $default;
    }

    /**
     * Adds a service configuration.
     *
     * Use the container/service configuration if possible to define services.
     *
     * NOTE: This method adds a closure, use {@see Container::addShared()} to
     * add an object.
     *
     *     $container->addService('Vendor\\Namespace\\ClassName', function(Something $something) {
     *         $className = new ClassName();
     *         $className->setSomething($something);
     *
     *         return $className;
     *     });
     *
     * @param string  $fqn
     * @param Closure $closure
     *
     * @return $this
     */
    public function addService($fqn, $closure)
    {
        $this->services[$fqn] = $closure;

        return $this;
    }

    /**
     * Removes a service configuration.
     *
     * NOTE: This method removes a closure, use {@see Container::removeShared()}
     * to remove an object.
     *
     *     // By FQN
     *     $container->removeService('Vendor\\Namespace\\ClassName');
     *
     *     // By closure
     *     $container->removeService($closure);
     *
     * @param string|Closure $fqn
     *
     * @return $this
     */
    public function removeService($fqn)
    {
        Arr::removeByKeyOrValue($this->services, $fqn);

        return $this;
    }

    /**
     * Sets the service aliases.
     *
     * NOTE: Use the container/alias configuration if possible to define aliases.
     *
     *     $container->setAliases(array(
     *         'Vendor\Namespace\FooInterface' => 'Vendor\Namespace\FooEntity',
     *     ));
     *
     * @param array $aliases
     *
     * @return $this
     */
    public function setAliases(array $aliases)
    {
        $this->aliases = $aliases;

        return $this;
    }

    /**
     * Gets the service aliases.
     *
     *     $aliases = $container->getAliases();
     *
     * @return array
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Gets the alias of a service.
     *
     *     $alias = $container->getAlias('Vendor\\Namespace\\FooInterface'); // "Vendor\Namespace\FooEntity"
     *
     * @param string $fqn
     * @param string $default
     *
     * @return string
     */
    public function getAlias($fqn, $default = null)
    {
        return isset($this->aliases[$fqn]) ? $this->aliases[$fqn] : $default;
    }

    /**
     * Adds an service alias.
     *
     * NOTE: Use the container/alias configuration if possible to define aliases.
     *
     *     $container->addAlias('Vendor\\Namespace\\FooInterface', 'Vendor\\Namespace\\FooEntity');
     *
     * @param string $fqn
     * @param string $alias
     *
     * @return $this
     */
    public function addAlias($fqn, $alias)
    {
        $this->aliases[$fqn] = $alias;

        return $this;
    }

    /**
     * Removes a service alias.
     *
     *     $container->removeAlias('Vendor\\Namespace\\FooInterface');
     *
     * @param string $alias
     *
     * @return $this
     */
    public function removeAlias($alias)
    {
        unset($this->aliases[$alias]);

        return $this;
    }

    /**
     * Sets the shareable configuration.
     *
     * NOTE: Use the container/shareable configuration if possible to define
     * shareable services.
     *
     *     $container->setShareable(array(
     *         'foo'                    => false,
     *         'Vendor\\Namespace\\Foo' => true,
     *     ));
     *
     * @param array $services
     *
     * @return $this
     */
    public function setShareable(array $services)
    {
        $this->shareable = $services;

        return $this;
    }

    /**
     * Gets the shareable configuration.
     *
     *     // All
     *     $shareable = $container->getShareable();
     *
     *     // Single
     *     $shareable = $container->getShareable('Vendor\\Namespace\\Foo');
     *
     * @param string $fqn
     * @param mixed  $default
     *
     * @return array
     */
    public function getShareable($fqn = null, $default = null)
    {
        if (null === $fqn) {
            return $this->shareable;
        }

        return isset($this->shareable[$fqn]) ? $this->shareable[$fqn] : $default;
    }

    /**
     * Adds a shareable definition.
     *
     *     $container->addShareable('foo', false);
     *     $container->addShareable('Vendor\\Namespace\\Foo', false);
     *
     * @param string $service
     * @param bool   $shareable
     *
     * @return $this
     */
    public function addShareable($service, $shareable)
    {
        $this->shareable[$service] = $shareable;

        return $this;
    }

    /**
     * Removes a shareable definition.
     *
     *     $container->removeShareable('Vendor\\Namespace\\Foo');
     *
     * @param string $service
     *
     * @return $this
     */
    public function removeShareable($service)
    {
        unset($this->shareable[$service]);

        return $this;
    }

    /**
     * Checks if a service is shareable.
     *
     * Multiple FQNs may be checked. The first match will be returned.
     *
     *     // If "FooInterface" matches first, its value is returned
     *     if ($container->isShareable(array('FooInterface', 'FooEntity'))) {
     *
     * NOTE: A service is shareable by default, unless overridden in the
     * container/shareable configuration.
     *
     * @param array $services
     *
     * @return bool
     */
    public function isShareable($services)
    {
        foreach ((array) $services as $service) {
            if (isset($this->shareable[$service])) {
                return $this->shareable[$service];
            }
        }

        return true;
    }

    /**
     * Sets the service nicknames.
     *
     * NOTE: Use the container/nickname configuration if possible to define nicknames.
     *
     *     $container->setNicknames(array(
     *         'foo' => 'Vendor\Namespace\Foo',
     *     ));
     *
     * @see Container::loadNicknames()
     *
     * @param array $nicknames
     *
     * @return $this
     */
    public function setNicknames(array $nicknames)
    {
        $this->nicknames = $nicknames;

        return $this;
    }

    /**
     * Gets the service nicknames.
     *
     *     $nicknames = $container->getNicknames();
     *
     * @return array
     */
    public function getNicknames()
    {
        return $this->nicknames;
    }

    /**
     * Gets a service nickname.
     *
     *     $nickname = $container->getNickname('foo'); // "Vendor\\Namespace\\Foo"
     *
     * @param string $nickname
     * @param string $default
     *
     * @return string
     */
    public function getNickname($nickname, $default = null)
    {
        return isset($this->nicknames[$nickname]) ? $this->nicknames[$nickname] : $default;
    }

    /**
     * Adds a service nickname.
     *
     *     $container->addNickname('className', 'Vendor\Namespace\ClassName');
     *
     * @param string $nickname
     * @param string $fqn
     *
     * @return $this
     */
    public function addNickname($nickname, $fqn)
    {
        $this->nicknames[$nickname] = $fqn;

        return $this;
    }

    /**
     * Removes a service nickname.
     *
     *     // By nickname
     *     $container->removeNickname('className');
     *
     *     // By FQN
     *     $container->removeNickname('Vendor\\Namespace\\Nickname');
     *
     * @param $nickname
     *
     * @return $this
     */
    public function removeNickname($nickname)
    {
        Arr::removeByKeyOrValue($this->nicknames, $nickname);

        return $this;
    }

    /**
     * Sets the shared services.
     *
     * NOTE: Services are shared by default. This behaviour can be overridden in
     * the container/shareable configuration.
     *
     *     $container->setShared(array(
     *         'Vendor\\Namespace\\Foo' => $foo,
     *     ));
     *
     * @param array $shared
     *
     * @return $this
     */
    public function setShared(array $shared)
    {
        $this->shared = $shared;

        return $this;
    }

    /**
     * Gets the shared services.
     *
     *     // All
     *     $shared = $container->getShared();
     *
     *     // Single
     *     $shared = $container->getShared('Vendor\\Namespace\\Foo');
     *
     * @param string $fqn
     * @param mixed  $default
     *
     * @return array
     */
    public function getShared($fqn = null, $default = null)
    {
        if (null === $fqn) {
            return $this->shared;
        }

        return isset($this->shared[$fqn]) ? $this->shared[$fqn] : $default;
    }

    /**
     * Adds a shared service.
     *
     *     $container->addShared('Vendor\\Namespace\\Foo', $foo);
     *
     * @param string $fqn
     * @param mixed  $service
     *
     * @return $this
     */
    public function addShared($fqn, $service)
    {
        $this->shared[$fqn] = $service;

        return $this;
    }

    /**
     * Removes a shared service.
     *
     *     // By FQN
     *     $container->removeShareable('Vendor\\Namespace\\Foo');
     *
     *     // By object
     *     $container->removeShareable($foo);
     *
     * @param string $fqn
     *
     * @return $this
     */
    public function removeShared($fqn)
    {
        Arr::removeByKeyOrValue($this->shared, $fqn);

        return $this;
    }

    /**
     * Sets a service.
     *
     *     // Store new service that will be lazy loaded
     *     $container->set('Vendor\\Namespace\\Foo', function() {
     *         // A closure ensures the object won't be constructed until needed
     *         return new Foo();
     *     });
     *
     *     // Alternatively, you can share objects that are already built
     *     $foo = Foo();
     *
     *     // Store object that will be shared through the container
     *     $container->set($foo);
     *
     * @param string|object  $fqn
     * @param object|Closure $service
     *
     * @return $this
     */
    public function set($fqn, $service = null)
    {
        if (is_array($fqn)) {
            foreach ($fqn as $newFqn => $service) {
                $this->set($newFqn, $service);
            }

            return $this;
        }

        if (null === $service) {
            $this->shared[get_class($fqn)] = $fqn;
        } else {
            $this->services[$fqn] = $service;
        }

        return $this;
    }

    /**
     * Gets a service by nickname and/or FQN.
     *
     * @param string $nickname
     * @param string $fqn
     * @param array  $params Parameters to pass to the constructor.
     *
     * @return mixed
     */
    public function get($nickname, $fqn = null, array $params = array())
    {
        if (null === $fqn) {
            return $this->getByNickname($nickname, $params);
        } else {
            return $this->getByFqn($fqn, $params, array($nickname));
        }
    }

    /**
     * Gets a service by nickname.
     *
     * If no parameters are provided, use {@see Container::get()} instead.
     *
     *     $container->getByNickname('request');
     *     $container->get('request');
     *
     * Passing parameters should be cleaner with this method:
     *
     *     $container->getByNickname($name.'RouteFilter', array(
     *         'value' => $value,
     *         'route' => $route,
     *     ));
     *
     *     // Vs
     *     $container->get($name.'RouteFilter', null, array(
     *         'value' => $value,
     *         'route' => $route,
     *     ));
     *
     * @throws Exception
     *
     * @param string $nickname
     * @param array  $params Parameters to pass to the constructor.
     *
     * @return mixed
     */
    public function getByNickname($nickname, array $params = array())
    {
        if (!$fqn = $this->getNickname($nickname)) {
            throw new Exception('Could not locate or build the "%s" service', $nickname);
        }

        return $this->getByFqn($fqn, $params, array($nickname));
    }

    /**
     * Gets a service by FQN (Fully Qualified Name).
     *
     *     $className = $container->getByFqn('Vendor\\Namespace\\ClassName);
     *     $request   = $container->getByFqn('Rax\\Http\\Request');
     *
     * @param string $fqn
     * @param array  $params Parameters to pass to the constructor.
     * @param array  $fqns
     *
     * @return mixed
     */
    public function getByFqn($fqn, array $params = array(), array $fqns = array())
    {
        $fqns[] = $fqn;

        if (isset($this->aliases[$fqn])) {
            $fqn    = $this->aliases[$fqn];
            $fqns[] = $fqn;
        }

        if (isset($this->shared[$fqn])) {
            return $this->shared[$fqn];
        }

        if (isset($this->services[$fqn])) {
            $service = $this->call($this->services[$fqn], $params);
        } else {
            $service = $this->build($fqn, $params);
        }

        if ($this->isShareable($fqns)) {
            $this->shared[$fqn] = $service;
        }

        return $service;
    }

    /**
     * Builds an service given a FQN (Fully Qualified Name).
     *
     * @param string $fqn
     * @param array  $params Parameters to pass to the constructor.
     *
     * @return object
     */
    public function build($fqn, array $params = array())
    {
        $reflection = new ReflectionClass($fqn);

        if (!$constructor = $reflection->getConstructor()) {
            return new $fqn();
        }

        $dependencies = $this->resolveDependencies($constructor, $params);

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Calls anything that can be callable, transforming its function signature
     * into an OOD (Objects On Demand) hotspot.
     *
     * You can use this hotspot to load any object through the container using
     * automatic dependency injection.
     *
     *     // If the object doesn't exist in the container it will be automatically
     *     // created for you (including its dependencies) and shared by default
     *     public function indexAction(Foo $foo)
     *
     *     // Shared objects (AKA singletons) are simply reused when requested
     *     public function indexAction(Request $request)
     *
     * Some hotspots are predefined for your convenience:
     *
     * - Controller actions e.g. indexAction()
     * - Observers e.g. trigger()
     * - Route filters e.g. filter()
     *
     * A callable can be any of the following:
     *
     *     // Nickname (FYI the filter method is called)
     *     $container->call('ajaxRouteFilter', 'filter');
     *
     *     // FQN
     *     $container->call('Rax\\Routing\\Filter\\AjaxRouteFilter', 'filter');
     *
     *     // Both
     *     $service = array('id' => 'ajaxRouteFilter', 'fqn' => 'Rax\\Routing\\Filter\\AjaxRouteFilter');
     *     $container->call($service, 'filter');
     *
     *     // Procedural function
     *     $container->call('functionName');
     *
     *     // Closure
     *     $container->call(function() {});
     *
     *     // Object
     *     $ajaxRouteFilter = new AjaxRouteFilter();
     *     $container->call($ajaxRouteFilter, 'filter');
     *
     * You can pass values to the function signature using the $values param.
     * The array key will become the parameter name. A param will supersede a
     * service in case of a name collision.
     *
     *     $container->call($foo, 'bar', array('wut' => 123));
     *
     *     class Foo
     *     {
     *         public function bar($wut)
     *         {
     *             echo $wut; // 123
     *
     * @throws Exception
     *
     * @param string|array|Closure|object $fqn
     * @param string|array                $method
     * @param array|object                $params
     *
     * @return mixed
     */
    public function call($fqn, $method = null, $params = array())
    {
        $params = Arr::asArray($params);

        if (is_string($fqn)) {
            if (Str::contains('\\', $fqn)) {
                $service = $this->getByFqn($fqn, $params);
            } elseif (function_exists($fqn)) {
                return $this->callFunction($fqn, (array) $method);
            } else {
                $service = $this->getByNickname($fqn, $params);
            }
        } elseif (is_array($fqn)) {
            $service = $this->get($fqn['id'], $fqn['fqn'], $params);
        } elseif ($fqn instanceof Closure) {
            return $this->callFunction($fqn, (array) $method);
        } elseif (is_object($fqn)) {
            $service = $fqn;
        } else {
            throw new Exception('Invalid service, got %s', Php::getDataType($fqn));
        }

        return $this->callMethod($service, $method, $params);
    }

    /**
     * Calls a function or closure.
     *
     * The function signature will be transformed into an OOD hotspot.
     *
     *     // Procedural function
     *     $container->callFunction('foo');
     *
     *     function foo(Request $request, EventManager $eventManager)
     *     {
     *
     *     // Closure
     *     $container->call(function(Request $request) {
     *         // ...
     *     });
     *
     * Params may be passed to the OOD hotspot which would supersede any service
     * from the container with a colliding name. These params are not stored in
     * the container.
     *
     *     $container->callFunction('foo', array('request' => 123, $route));
     *
     *     // Of course you would have to remove the "Request" type hint
     *     function foo($request, Route $route)
     *     {
     *         echo $request; // 123
     *
     * @param string|Closure $function
     * @param array          $params
     *
     * @return mixed
     */
    public function callFunction($function, array $params = array())
    {
        $reflection   = new ReflectionFunction($function);
        $dependencies = $this->resolveDependencies($reflection, $params);

        return $reflection->invokeArgs($dependencies);
    }

    /**
     * Calls a method.
     *
     * @param object $obj
     * @param string $methodName
     * @param array  $params
     *
     * @return mixed
     */
    public function callMethod($obj, $methodName, array $params = array())
    {
        $reflection   = new ReflectionMethod($obj, $methodName);
        $dependencies = $this->resolveDependencies($reflection, $params);

        return $reflection->invokeArgs($obj, $dependencies);
    }

    /**
     * Resolves the function's dependencies.
     *
     *     $function = new ReflectionFunction(function($value, Route $route, Request $request) {
     *         $value;   // Passed as a parameter
     *         $route;   // Passed as a parameter
     *         $request; // Obtained from the container
     *     });
     *
     *     $dependencies = $container->resolveDependencies($function, array(
     *         'value' => 123,
     *         $route,
     *     ));
     *
     *     $function->invokeArgs($dependencies);
     *
     * @param ReflectionFunctionAbstract $function
     * @param array                      $params
     *
     * @throws Exception
     * @return array
     */
    public function resolveDependencies($function, array $params = array())
    {
        $params = $this->normalizeParams($params);

        $dependencies = array();

        foreach ($function->getParameters() as $param) {
            if (array_key_exists($param->getName(), $params)) {
                $dependencies[] = $params[$param->getName()];
            } elseif ($param->getClass()) {
                $dependencies[] = $this->get($param->getName(), $param->getClass()->getName(), $params);
            } elseif ($param->isOptional()) {
                $dependencies[] = $param->getDefaultValue();
            } else {
                throw new Exception('No value available for parameter "%s" in %s', array(
                    $param->getName(),
                    $param->getClass()->getName(),
                    $function->getName(),
                ));
            }
        }

        return $dependencies;
    }

    /**
     * Normalizes the names of all parameters.
     *
     *     $params = $container->normalizeParams(array('foo' => 123, $route));
     *
     *     // Before
     *     Array
     *     (
     *         [foo] => 123
     *         [0] => Route Object
     *             (
     *             )
     *     )
     *
     *     // After
     *     Array
     *     (
     *         [foo] => 123
     *         [route] => Route Object
     *             (
     *             )
     *     )
     *
     * @param array $params
     *
     * @return array
     */
    public function normalizeParams(array $params = array())
    {
        foreach ($params as $param => $value) {
            if (is_int($param) && is_object($value)) {
                $param = Php::getClassName($value);
            }

            $params[$param] = $value;
        }

        return $params;
    }

    /**
     * Loads the nicknames.
     *
     * NOTE: The container/nickname configuration file has all the information
     * related to nicknames.
     *
     *     $container->loadNicknames(array(
     *         'className' => 'Vendor\\Namespace\\ClassName',
     *     ));
     *
     * @param array $config
     *
     * @return $this
     */
    public function loadNicknames(array $config)
    {
        $finder = Finder::create()
            ->files()
            ->in($this->cfs->findDirs('src'))
            ->exclude('Base')
            ->name('*.php')
            ->notName('*Interface.php');

        $nicknames = array();

        /** @var $file SplFileInfo */
        foreach ($finder as $file) {
            $phpParsed = $this->phpParser->parse($file->getContents());

            if ($phpParsed->getClassName()) {
                $nicknames[lcfirst($phpParsed->getClassName())] = $phpParsed->getFqn();
            }
        }

        foreach ($config as $nickname => $fqn) {
            $nicknames[$nickname] = $fqn;
        }

        $this->nicknames = array_filter($nicknames);

        return $this;
    }

    /**
     * Proxies to {@see Container::set()}.
     *
     *     $container->foo = $foo;
     *
     * @param string         $name
     * @param object|Closure $service
     */
    public function __set($name, $service)
    {
        $this->set($name, $service);
    }

    /**
     * Proxies to {@see Container::get()}.
     *
     *     $foo = $container->foo;
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }
}
