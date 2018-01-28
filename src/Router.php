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

use Apatis\CallbackResolver\CallbackResolverInterface;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Router
 * @package Apatis\Route
 */
class Router implements RouterInterface
{
    /**
     * @type string for fastRoute dispatcher route parser
     */
    const ROUTE_PARSER_NAME = 'routeParser';

    /**
     * @type string key for fastRoute dispatcher cache file
     */
    const ROUTE_CACHE_FILE_NAME = 'cacheFile';

    /**
     * Route Parser
     *
     * @var RouteParser
     */
    protected $routeParser;

    /**
     * Route Cache file
     *
     * @var string $cacheFile The full path for cache file
     */
    protected $cacheFile;

    /**
     * @var CallbackResolverInterface
     */
    protected $callbackResolver;

    /**
     * Routes
     *
     * @var RouteInterface[]
     */
    protected $routes = [];

    /**
     * Route counter incrementer
     * @var int
     */
    protected $routeCounter = 0;

    /**
     * Route groups
     *
     * @var RouteGroupInterface[]
     */
    protected $routeGroups = [];

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * Create new router
     *
     * @param RouteParser $parser
     */
    public function __construct(RouteParser $parser = null)
    {
        $this->routeParser = $parser ?: new RouteParser\Std();
    }

    /**
     * {@inheritdoc}
     */
    public function map(array $methods, string $pattern, $callable) : RouteInterface
    {
        // Prepend parent group pattern(s)
        if ($this->routeGroups) {
            $pattern = $this->processGroups() . $pattern;
        }

        // According to RFC methods are defined in uppercase (See RFC 7231)
        $methods = array_map("strtoupper", $methods);
        // Add route
        $route                               = $this->createRoute($methods, $pattern, $callable);
        $this->routes[$this->routeCounter++] = $route;

        return $route;
    }

    /**
     * Process route groups
     *
     * @return string A group pattern to prefix routes with
     */
    protected function processGroups() : string
    {
        $pattern = "";
        foreach ($this->routeGroups as $group) {
            $pattern .= $group->getPattern();
        }

        return $pattern;
    }

    /**
     * Create a new Route object
     *
     * @param  string[] $methods Array of HTTP methods
     * @param  string $pattern The route pattern
     * @param  callable $callable The route callable
     *
     * @return Route
     */
    protected function createRoute($methods, $pattern, $callable)
    {
        $route = new Route($methods, $pattern, $callable, $this->routeGroups, $this->routeCounter);
        if ($this->callbackResolver) {
            $route->setCallbackResolver($this->callbackResolver);
        }

        return $route;
    }

    /**
     * Dispatch router for HTTP request
     *
     * @param  ServerRequestInterface $request The current HTTP request object
     *
     * @return array
     *
     * @link   https://github.com/nikic/FastRoute/blob/master/src/Dispatcher.php
     */
    public function dispatch(ServerRequestInterface $request) : array
    {
        $uri = $request->getUri()->getPath();
        if (! $uri || $uri[0] !== '/') {
            $uri = '/' . $uri;
        }

        return $this->createDispatcher()->dispatch(
            $request->getMethod(),
            $uri
        );
    }

    /**
     * Create Dispatcher
     *
     * @return Dispatcher
     */
    protected function createDispatcher() : Dispatcher
    {
        $dispatcher = $this->getDispatcher();
        if ($dispatcher instanceof Dispatcher) {
            return $dispatcher;
        }

        $routeDefinitionCallback = function (RouteCollector $r) {
            foreach ($this->getRoutes() as $route) {
                $r->addRoute($route->getMethods(), $route->getPattern(), $route->getIdentifier());
            }
        };

        if ($this->cacheFile) {
            $dispatcher = \FastRoute\cachedDispatcher($routeDefinitionCallback, [
                self::ROUTE_PARSER_NAME     => $this->routeParser,
                self::ROUTE_CACHE_FILE_NAME => $this->cacheFile,
            ]);
        } else {
            $dispatcher = \FastRoute\simpleDispatcher($routeDefinitionCallback, [
                self::ROUTE_PARSER_NAME => $this->routeParser,
            ]);
        }

        $this->setDispatcher($dispatcher);

        return $dispatcher;
    }

    /**
     * @return Dispatcher|null
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function setDispatcher(Dispatcher $dispatcher) : RouterInterface
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * Get route objects
     *
     * @return RouteInterface[]
     */
    public function getRoutes() : array
    {
        return $this->routes;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteParser() : RouteParser
    {
        return $this->routeParser;
    }

    /**
     * {@inheritdoc}
     */
    public function setRouteParser(RouteParser $routeParser) : RouterInterface
    {
        $this->routeParser = $routeParser;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function pushGroup(string $pattern, $callable) : RouteGroupInterface
    {
        $group               = new RouteGroup($pattern, $callable);
        $this->routeGroups[] = $group;

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function popGroup()
    {
        return array_pop($this->routeGroups);
    }

    /**
     * Remove named route
     *
     * @param string $name
     *
     * @return bool
     */
    public function removeRouteByName(string $name) : bool
    {
        if ($route = $this->getRouteByName($name)) {
            unset($this->routes[$route->getIdentifier()]);

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteByName(string $name)
    {
        foreach ($this->routes as $route) {
            if ($name === $route->getName()) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Get Route By Identifier
     *
     * @param int $identifier
     *
     * @return RouteInterface|null
     */
    public function getRouteByIdentifier(int $identifier)
    {
        return isset($this->routes[$identifier])
            ? $this->routes[$identifier]
            : null;
    }

    /**
     * @param string $cacheFile
     *
     * @return Router
     */
    public function setCacheFile(string $cacheFile) : Router
    {
        if ($cacheFile == '') {
            $this->cacheFile = false;

            return $this;
        }

        $spl    = new \SplFileInfo($cacheFile);
        $splDir = $spl->getPathInfo();
        if (! $splDir->isWritable()) {
            throw new \RuntimeException(
                'Cache file directory is not writable',
                E_WARNING
            );
        }
        if ($spl->getRealPath() && ! $spl->isFile()) {
            throw new \RuntimeException(
                sprintf(
                    'Cache file is not a file %s given',
                    $spl->getType()
                ),
                E_WARNING
            );
        }

        if (! $spl->getRealPath()) {
            $spl = new \SplFileInfo($splDir->getRealPath() . DIRECTORY_SEPARATOR . $spl->getBasename());
        }

        $this->cacheFile = $spl->__toString();
        unset($spl, $splDir);

        return $this;
    }

    /**
     * Set callback resolver
     *
     * @param CallbackResolverInterface $resolver
     *
     * @return static|RouterInterface
     */
    public function setCallbackResolver(CallbackResolverInterface $resolver) : RouterInterface
    {
        $this->callbackResolver = $resolver;

        return $this;
    }
}
