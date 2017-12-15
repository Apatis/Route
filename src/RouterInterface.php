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

use FastRoute\Dispatcher;
use FastRoute\RouteParser;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface RouterInterface
 * @package Apatis\Route
 */
interface RouterInterface
{
    /**
     * Add route
     *
     * @param array $methods
     * @param string $pattern
     * @param callable $callable
     *
     * @return RouteInterface
     */
    public function map(array $methods, string $pattern, $callable) : RouteInterface;

    /**
     * Dispatch router for HTTP request
     *
     * @param  ServerRequestInterface $request The current HTTP request object
     *
     * @return array
     *
     * @link   https://github.com/nikic/FastRoute/blob/master/src/Dispatcher.php
     */
    public function dispatch(ServerRequestInterface $request) : array;

    /**
     * Add a route group to the array
     *
     * @param string $pattern The group pattern
     * @param callable $callable A group callable
     *
     * @return RouteGroupInterface
     */
    public function pushGroup(string $pattern, callable $callable) : RouteGroupInterface;

    /**
     * Removes the last route group from the array
     *
     * @return RouteGroupInterface|null
     */
    public function popGroup();

    /**
     * Get named route object
     *
     * @param string $name Route name
     *
     * @return RouteInterface|null
     */
    public function getRouteByName(string $name);

    /**
     * @param string $name
     *
     * @return bool
     */
    public function removeRouteByName(string $name) : bool;

    /**
     * @param int $identifier
     *
     * @return RouteInterface|null
     */
    public function getRouteByIdentifier(int $identifier);

    /**
     * Get route collection
     *
     * @return RouteInterface[]
     */
    public function getRoutes() : array;

    /**
     * Set Route Dispatcher
     *
     * @param Dispatcher $dispatcher
     *
     * @return RouterInterface
     */
    public function setDispatcher(Dispatcher $dispatcher) : RouterInterface;

    /**
     * Get route Dispatcher
     *
     * @return Dispatcher|null
     */
    public function getDispatcher();

    /**
     * Set RouteParser
     *
     * @param RouteParser $routeParser
     *
     * @return RouterInterface
     */
    public function setRouteParser(RouteParser $routeParser) : RouterInterface;

    /**
     * Get Route Parser
     *
     * @return RouteParser
     */
    public function getRouteParser() : RouteParser;
}
