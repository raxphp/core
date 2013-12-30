<?php

namespace Rax\Container\Base;

use Closure;
use Rax\Bundle\Cfs;
use Rax\Config\Config;
use Rax\Exception\Exception;
use Rax\Helper\Arr;
use Rax\Helper\Php;
use Rax\Helper\Text;
use Rax\PhpParser\PhpParser;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Symfony\Component\Finder\Finder;

/**
 * @property Cfs cfs
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseContainer
{
    /**
     * @var array
     */
    protected $services;

    /**
     * @var array
     */
    protected $aliases;

    /**
     * @var array
     */
    protected $freshness;

    /**
     * @var array
     */
    protected $proxies;

    /**
     * @var array
     */
    protected $lookup;

    /**
     * @var array
     */
    protected $shared = array();

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->services  = $config->get('container/services');
        $this->aliases   = $config->get('container/aliases');
        $this->freshness = $config->get('container/freshness');
        $this->proxies   = $config->get('container/proxies');
    }

    /**
     * @param array $services
     *
     * @return $this
     */
    public function setServices(array $services)
    {
        $this->services = $services;

        return $this;
    }

    /**
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
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
     * @return array
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * @param array $freshness
     *
     * @return $this
     */
    public function setFreshness(array $freshness)
    {
        $this->freshness = $freshness;

        return $this;
    }

    /**
     * @return array
     */
    public function getFreshness()
    {
        return $this->freshness;
    }

    /**
     * @param array $proxies
     *
     * @return $this
     */
    public function setProxies(array $proxies)
    {
        $this->proxies = $proxies;

        return $this;
    }

    /**
     * @return array
     */
    public function getProxies()
    {
        return $this->proxies;
    }

    /**
     * @param array $lookup
     *
     * @return $this
     */
    public function setLookup(array $lookup)
    {
        $this->lookup = $lookup;

        return $this;
    }

    /**
     * @return array
     */
    public function getLookup()
    {
        return $this->lookup;
    }

    /**
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
     * @return array
     */
    public function getShared()
    {
        return $this->shared;
    }

    /**
     * Sets a service by ID.
     *
     *     // Defines a new service with ID "foo" that will be lazy loaded
     *     $container->set('foo', function() {
     *         return new Foo();
     *     });
     *
     *     $foo = Foo();
     *
     *     // Stores a shared object with ID "foo"
     *     $container->set('foo', $foo);
     *
     *     // Same as above, but the ID is auto-detected
     *     $container->set($foo);
     *
     * @param string|object  $id
     * @param object|Closure $service
     *
     * @return $this
     */
    public function set($id, $service = null)
    {
        if ($service instanceof Closure) {
            $this->services[$id] = $service;
        } elseif (is_object($id)) {
            $this->shared[lcfirst(Php::getClassName($id))] = $id;
        } else {
            $this->shared[$id] = $service;
        }

        return $this;
    }

    /**
     * Gets a service by ID.
     *
     * This method is only useful if you have values to pass in, otherwise
     * use get().
     *
     *     $container->getById($name.'RouteFilter', array(
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
     * @param string $id
     * @param array  $values
     *
     * @return mixed
     */
    public function getById($id, array $values = array())
    {
        return $this->get($id, null, $values);
    }

    /**
     * Gets a service by FQN (Fully Qualified Name).
     *
     * The ID will be automatically determined based on the class name.
     *
     *     $request = $container->getByFqn('Rax\Http\Request');
     *
     * @param string $fqn
     * @param array  $values
     *
     * @return mixed
     */
    public function getByFqn($fqn, array $values = array())
    {
        return $this->get(null, $fqn, $values);
    }

    /**
     * Gets a service by either ID or FQN.
     *
     * @throws Exception
     *
     * @param string $id
     * @param string $fqn
     * @param array  $values
     *
     * @return mixed
     */
    public function get($id = null, $fqn = null, array $values = array())
    {
        if (null === $id) {

        }

        if (isset($this->shared[$id])) {
            return $this->shared[$id];
        }

        if (isset($this->services[$id])) {
            return ($this->shared[$id] = $this->services[$id]());
        }

        if (null === $fqn) {
            $fqn = Arr::get($this->lookup, $id);
        }

        if ($fqn && isset($this->aliases[$fqn])) {
            $fqn = $this->aliases[$fqn];
        }

        if ($fqn && ($service = $this->build($fqn, $values))) {
            return ($this->shared[$id] = $service);
        }

        throw new Exception('Could not locate or build the "%s" service', $id);
    }

    /**
     * @param string $fqn
     * @param array  $values
     *
     * @return object
     */
    public function build($fqn, array $values = array())
    {
        $refl = new ReflectionClass($fqn);

        if (!$constructor = $refl->getConstructor()) {
            return new $fqn();
        }

        $dependencies = $this->resolveDependencies($constructor, $values);

        return $refl->newInstanceArgs($dependencies);
    }

    /**
     * Calls a closure or function or a method on a service or object.
     *
     * The purpose of this method is to transform the signature of the called
     * function into an OOD (Objects On Demand) hotspot. You can use this
     * hotspot to load any object that you need on the fly without having to
     * build them yourself.
     *
     * The first param can be a service ID or FQN, the name of a function,
     * a closure or an object.
     *
     *     // Service ID or FQN
     *     $container->call('ajaxRouteFilter', 'filter');
     *     $container->call('Rax\Routing\Filter\AjaxRouteFilter', 'filter');
     *
     *     // Use an array to specify both
     *     $service = array('id' => 'ajaxRouteFilter', 'fqn' => 'Rax/Routing/Filter/AjaxRouteFilter');
     *     $container->call($service, 'filter');
     *
     *     // Procedural function
     *     $container->call('functionName');
     *
     *     // Closure
     *     $container->call(function() {
     *         // ...
     *     });
     *
     *     // Object
     *     $ajaxRouteFilter = new AjaxRouteFilter();
     *     $container->call($ajaxRouteFilter, 'filter');
     *
     * Yse can pass along values to the function signature using the $values
     * array. The array key will become the parameter name.
     *
     *     $container->call($foo, 'bar', array('wut' => 123));
     *
     *     public function bar($wut)
     *     {
     *         echo $wut; // 123
     *
     * @throws Exception
     *
     * @param string|array|Closure|object $id
     * @param string|array                $method
     * @param array                       $values
     *
     * @return mixed
     */
    public function call($id, $method = null, array $values = array())
    {
        if (is_string($id)) {
            if (Text::contains('\\', $id)) {
                $service = $this->getByFqn($id, $values);
            } elseif (function_exists($id)) {
                return $this->callFunction($id, (array) $method);
            } else {
                $service = $this->getById($id, $values);
            }
        } elseif (is_array($id)) {
            $service = $this->get($id['id'], $id['fqn'], $values);
        } elseif ($id instanceof Closure) {
            return $this->callFunction($id, (array) $method);
        } elseif (is_object($id)) {
            $service = $id;
        } else {
            throw new Exception('Invalid service ID, got %s', Php::getDataType($id));
        }

        return $this->callMethod($service, $method, $values);
    }

    /**
     * @param string|Closure $function
     * @param array          $values
     *
     * @return mixed
     */
    public function callFunction($function, array $values = array())
    {
        $function     = new ReflectionFunction($function);
        $dependencies = $this->resolveDependencies($function, $values);

        return $function->invokeArgs($dependencies);
    }

    /**
     * @param object $obj
     * @param string $methodName
     * @param array  $values
     *
     * @return mixed
     */
    public function callMethod($obj, $methodName, array $values = array())
    {
        $method       = new ReflectionMethod($obj, $methodName);
        $dependencies = $this->resolveDependencies($method, $values);

        return $method->invokeArgs($obj, $dependencies);
    }

    /**
     * @param ReflectionFunctionAbstract $function
     * @param array                      $values
     *
     * @throws Exception
     * @return array
     */
    public function resolveDependencies($function, array $values = array())
    {
        $dependencies = array();

        foreach ($function->getParameters() as $parameter) {
            if ($value = Arr::get($values, $parameter->getName())) {
                $dependencies[] = $value;
            } elseif ($parameter->getClass()) {
                $dependencies[] = $this->get($parameter->getName(), $parameter->getClass()->getName());
            } elseif ($parameter->isOptional()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new Exception('No value available for parameter "%s" in %s::%s', array(
                    $parameter->getName(),
                    $parameter->getClass()->getName(),
                    $function->getName(),
                ));
            }
        }

        return $dependencies;
    }

    /**
     * @param string $key
     *
     * @return array
     */
    public function parseIdFqn($key)
    {
        if (false !== strpos($key, '\\')) {
            $id  = lcfirst(Php::getClassName($key));
            $fqn = $key;
        } else {
            $id  = $key;
            $fqn = null;
        }

        return array($id, $fqn);
    }

    /**
     * @return $this
     */
    public function loadLookup()
    {
        $finder = Finder::create()
            ->files()
            ->in($this->cfs->findDirs('src'))
            ->name('*.php')
            ->notName('Base*')
            ->notName('*Interface.php')
        ;

        $lookup = array();

        foreach ($this->config->get('container/lookup') as $id => $fqn) {
            if (empty($this->services[$id])) {
                $lookup[$id] = Arr::get($this->aliases, $fqn, $fqn);
            }
        }

        $parser = new PhpParser();

        foreach ($finder as $file) {
            $parsed = $parser->parse($file->getContents());

            if ($parsed->getClass() && $parsed->getFqn()) {
                $id = lcfirst($parsed->getClass());

                if (empty($this->services[$id]) && empty($lookup[$id])) {
                    $lookup[$id] = $parsed->getFqn();
                }
            }
        }

        $this->lookup = $lookup;

        return $this;
    }

    /**
     * @param string         $id
     * @param object|Closure $service
     */
    public function __set($id, $service)
    {
        $this->set($id, $service);
    }

    /**
     * @param string $id
     *
     * @return mixed
     */
    public function __get($id)
    {
        return $this->get($id);
    }
}
