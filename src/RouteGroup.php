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

/**
 * Class RouteGroup
 * @package Apatis\Route
 */
class RouteGroup extends RoutAble implements RouteGroupInterface
{
    /**
     * @var callable
     */
    protected $callable;

    /**
     * Create a new RouteGroup
     *
     * @param string $pattern The pattern prefix for the group
     * @param callable $callable The group callable
     */
    public function __construct(string $pattern, $callable)
    {
        $this->setPattern($pattern);
        $this->callable = $callable;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($bindTo = false) : RouteGroupInterface
    {
        // Resolve route callable
        $callable = $this->callable;
        $binding  = null;
        if ($this->callbackResolver) {
            $resolver = clone $this->callbackResolver;
            $callable = $resolver->resolve($callable);
            $binding  = $resolver->getBinding();
            if ($bindTo === false) {
                $bindTo = $binding;
            }
        }

        if (! is_callable($callable)) {
            throw new \RuntimeException(
                'Route group callback is not callable'
            );
        }

        if ($callable instanceof \Closure
            && (is_object($bindTo) || $bindTo === null)
        ) {
            $callable = $callable->bindTo($bindTo);
        }

        call_user_func($callable, $bindTo, $this, $binding);

        return $this;
    }
}
