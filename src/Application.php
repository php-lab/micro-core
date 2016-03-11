<?php
declare(strict_types=1);

/**
 * @author    Yuriy Davletshin <yuriy.davletshin@gmail.com>
 * @copyright 2016 Yuriy Davletshin
 * @license   MIT
 */
namespace PhpLab\Micro\Core;

/**
 * Dependency injection container.
 */
class Application implements \ArrayAccess
{
    /**
     * @var callable[] Contains services.
     */
    private $services = [];

    /**
     * @var mixed[] Contains parameters.
     */
    private $parameters = [];

    /**
     * @var string[] Contains subscription keys.
     */
    private $events = [];

    /**
     * @var callable[] Contains subscriptions.
     */
    private $subscriptions = [];

    /**
     * Returns a service (object).
     *
     * @param string $id The unique name of the service (object).
     *
     * @throws \LogicException If the service (object) is not defined.
     *
     * @return object The service (object) instance.
     */
    public function __get(string $id)
    {
        if (isset($this->services[$id])) {

            return $this->services[$id]($this);
        }
        throw new \LogicException(sprintf('Service (object) "%s" is not defined.', $id));
    }

    /**
     * Sets a service (object) definition.
     *
     * @param string   $id         The unique name of the service (object).
     * @param callable $definition The closure or invokable object.
     */
    public function __set(string $id, callable $definition)
    {
        if (ltrim($id, '_') !== $id) {
            $this->services[$id] = $definition;
        } else {
            $this->services[$id] = function (Application $container) use ($definition) {
                static $service;
                if (!isset($service)) {
                    $service = $definition($container);
                }

                return $service;
            };
        }
    }

    /**
     * Checks if a parameter is set.
     *
     * @param string $key The unique key of the parameter.
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return isset($this->parameters[$key]);
    }

    /**
     * Returns a parameter.
     *
     * @param string $key The unique key of the parameter.
     *
     * @throws \LogicException If the parameter is not defined.
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        if (isset($this->parameters[$key])) {

            return $this->parameters[$key];
        }
        throw new \LogicException(sprintf('Parameter "%s" is not defined.', $key));
    }

    /**
     * Sets a parameter.
     *
     * @param string $key   The unique key of the parameter.
     * @param mixed  $value The value of the parameter.
     */
    public function offsetSet($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Removes a parameter.
     *
     * @param string $key The unique key of the parameter.
     */
    public function offsetUnset($key)
    {
        unset($this->parameters[$key]);
    }

    /**
     * Adds a subscription.
     *
     * @param string   $event    The unique name of the event.
     * @param string   $listener The unique name of the listener.
     * @param callable $action   The closure or invokable object.
     */
    public function subscribe(string $event, string $listener, callable $action)
    {
        $actionKey = $event . ' ' . $listener;
        $this->events[$event][] = $actionKey;
        $this->subscriptions[$actionKey] = $action;
    }

    /**
     * Notifies listeners about an event.
     *
     * @param string $event     The unique name of the event.
     * @param array  $arguments Arguments for event actions.
     */
    public function dispatch(string $event, array $arguments = [])
    {
        if (isset($this->events[$event])) {
            foreach ($this->events[$event] as $priorityKey => $actionKey) {
                $output = $this->subscriptions[$actionKey]($this, $arguments);
                if (isset($output['stop']) && true === $output['stop']) {
                    break;
                }
            }
        }
    }
}
