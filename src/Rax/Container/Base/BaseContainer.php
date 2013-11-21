<?php

namespace Rax\Container\Base;

use Closure;
use Rax\Data\Data;
use Rax\Exception\Exception;
use Rax\Helper\Arr;
use Rax\Helper\Php;
use Rax\PhpParser\PhpParser;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Symfony\Component\Finder\Finder;

/**
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
     * @param Data $config
     */
    public function __construct(Data $config)
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
     * @param string|object|array $id
     * @param object|Closure      $service
     *
     * @return $this
     */
    public function set($id, $service = null)
    {
        if (is_array($id)) {
            array_map(array($this, __FUNCTION__), array_keys($id), array_values($id));

            return $this;
        }

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
     * @throws Exception
     *
     * @param string $id
     * @param string $fqn
     * @param array  $values
     *
     * @return mixed
     */
    public function get($id, $fqn = null, array $values = array())
    {
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

        throw new Exception('Cannot locate or build the "%s" service.', $id);
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function resolveIdFqn($name)
    {
        if (strpos($name, '\\')) {
            $id  = lcfirst(Php::getClassName($name));
            $fqn = $name;
        } else {
            $id  = $name;
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
            ->notName('*Interface.php');

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
     * @param string|Closure|object $obj
     * @param string                $methodName
     * @param array                 $values
     *
     * @return mixed
     */
    public function call($obj, $methodName = null, array $values = array())
    {
        if (is_string($obj) || $obj instanceof Closure) {
            return $this->callFunction($obj, $values);
        } else {
            return $this->callMethod($obj, $methodName, $values);
        }
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
