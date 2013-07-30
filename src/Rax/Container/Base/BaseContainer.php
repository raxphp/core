<?php

namespace Rax\Container\Base;

use Closure;
use Rax\Data\Data;
use Rax\Exception\Exception;
use Rax\Helper\Arr;
use ReflectionClass;
use ReflectionFunctionAbstract;

/**
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BaseContainer
{
    /**
     * @var array
     */
    protected $shared = array();

    /**
     * @var array
     */
    protected $lookup = array();

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
     * @param Data $config
     */
    public function __construct(Data $config)
    {
        $this->services  = $config->get('container/services');
        $this->aliases   = $config->get('container/aliases');
        $this->freshness = $config->get('container/freshness');
        $this->proxies   = $config->get('container/proxies');

        $this->shared['container'] = $this;
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

    /**
     * @param string         $id
     * @param object|Closure $service
     */
    public function __set($id, $service)
    {
        $this->set($id, $service);
    }

    /**
     * @param string|array   $id
     * @param object|Closure $service
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
        } else {
            $this->shared[$id] = $service;
        }

        return $this;
    }

    /**
     * @param string $id
     * @param string $fqn
     *
     * @throws Exception
     * @return mixed
     */
    public function get($id, $fqn = null)
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

        if ($fqn && ($service = $this->build($fqn))) {
            return ($this->shared[$id] = $service);
        }

        throw new Exception('Cannot locate or build the "%s" service.', $id);
    }

    /**
     * @param string $fqn
     *
     * @return object
     */
    public function build($fqn)
    {
        $refl = new ReflectionClass($fqn);

        if (!$constructor = $refl->getConstructor()) {
            return new $fqn();
        }

        $dependencies = $this->resolveDependencies($constructor);

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
}
