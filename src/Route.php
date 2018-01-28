<?php
/**
 *  MIT License
 *
 * Copyright (c) 2017 Pentagonal Development
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Apatis\Route;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Route
 * @package Apatis\Route
 */
class Route extends RoutAble implements RouteInterface
{
    /**
     * @var array
     */
    protected $methods;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var RouteGroupInterface[]
     */
    protected $groups = [];

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * @var RouteHandlerInterface
     */
    protected $routeHandler;

    /**
     * @var bool
     */
    private $finalized = false;

    /**
     * @var int $identifier
     */
    protected $identifier = 0;

    /**
     * Route constructor.
     *
     * {@inheritdoc}
     */
    public function __construct(
        array $methods,
        string $pattern,
        $callable,
        array $groups = [],
        int $identifier = 0
    ) {
        $this->methods = $methods;
        $this->setPattern($pattern);
        $this->callback = $callable;
        foreach ($groups as $key => $group) {
            if (! $group instanceof RouteGroupInterface) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Route group value must be contain instance of %s only',
                        RouteGroupInterface::class
                    )
                );
            }

            $this->groups[$key] = $group;
        }

        $this->routeHandler = new RouteHandler();
        $this->identifier   = $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier() : int
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name) : RouteInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param RouteHandlerInterface $invocationRequestResponse
     */
    public function setRouteHandler(
        RouteHandlerInterface $invocationRequestResponse
    ) {
        $this->routeHandler = $invocationRequestResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setArgument($name, $value) : RouteInterface
    {
        $this->arguments[$name] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getArgument($name, $default = null)
    {
        return array_key_exists($name, $this->arguments)
            ? $this->arguments[$name]
            : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function setArguments(array $arguments) : RouteInterface
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments() : array
    {
        return $this->arguments;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(ServerRequestInterface $request, array $arguments)
    {
        foreach ($arguments as $key => $argument) {
            $this->setArgument($key, $argument);
        }
    }

    /**
     * Doing finalizing
     */
    public function finalize() : Route
    {
        if ($this->finalized) {
            return $this;
        }

        foreach ($this->getGroups() as $group) {
            foreach ($group->getMiddleware() as $middleware) {
                $this->addMiddleware($middleware);
            }
        }

        $this->finalized = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        return $this->finalize()->callMiddlewareStack($request, $response);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $callable = $this->getCallback();
        if ($this->callbackResolver) {
            $callable = $this->callbackResolver->resolve($callable);
        }

        $handler     = $this->routeHandler;
        $newResponse = $handler(
            $callable,
            $request,
            $response,
            $this->getArguments()
        );

        if ($newResponse instanceof ResponseInterface === false) {
            throw new \UnexpectedValueException(
                sprintf(
                    'Route handler must be return instance of %s, %s given',
                    ResponseInterface::class,
                    (
                    is_object($newResponse)
                        ? get_class($newResponse)
                        : gettype($newResponse)
                    )
                )
            );
        }

        return $newResponse;
    }

    /**
     * @return array
     */
    public function getMethods() : array
    {
        return $this->methods;
    }

    /**
     * Get callable callback
     *
     * @return callable
     */
    public function getCallback() : callable
    {
        return $this->callback;
    }

    /**
     * @return RouteGroupInterface[]
     */
    public function getGroups() : array
    {
        return $this->groups;
    }
}
