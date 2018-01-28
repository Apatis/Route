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
 * Interface RouteInterface
 * @package Apatis\Route
 */
interface RouteInterface extends RoutAbleInterface
{
    /**
     * RouteInterface constructor.
     *
     * @param array $methods
     * @param string $pattern
     * @param callable $callable
     * @param array $groups
     * @param int $identifier
     */
    public function __construct(
        array $methods,
        string $pattern,
        $callable,
        array $groups = [],
        int $identifier = 0
    );

    /**
     * Set route name
     *
     * @param string $name
     *
     * @return static
     */
    public function setName(string $name) : RouteInterface;

    /**
     * Get route defined name
     *
     * @return string|null return string if route name has been set
     */
    public function getName();

    /**
     * Set argument
     *
     * @param string|float|int $name
     * @param mixed $value
     *
     * @return RouteInterface
     */
    public function setArgument($name, $value) : RouteInterface;

    /**
     * Get argument by name
     *
     * @param string|float|int $name
     * @param null $default
     *
     * @return mixed|null
     */
    public function getArgument($name, $default = null);

    /**
     * Get all arguments
     *
     * @param array $arguments
     *
     * @return static
     */
    public function setArguments(array $arguments) : RouteInterface;

    /**
     * @return array
     */
    public function getArguments() : array;

    /**
     * Get defined pattern
     *
     * @return string
     */
    public function getPattern() : string;

    /**
     * Get route Identifier
     *
     * @return int
     */
    public function getIdentifier() : int;

    /**
     * Prepare Route process
     *
     * @param ServerRequestInterface $request
     * @param array $arguments
     *
     * @return mixed
     */
    public function prepare(ServerRequestInterface $request, array $arguments);

    /**
     * Run the route
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface;

    /**
     * Invoke callable
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface;
}
