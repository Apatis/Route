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
use Apatis\Middleware\MiddlewareInterface;

/**
 * Interface RoutAbleInterface
 * @package Apatis\Route
 */
interface RoutAbleInterface extends MiddlewareInterface
{
    /**
     * Set Route Pattern
     *
     * @param string $pattern
     *
     * @return static|RoutAbleInterface
     */
    public function setPattern(string $pattern) : RoutAbleInterface;

    /**
     * Get Pattern
     *
     * @return string
     */
    public function getPattern() : string;

    /**
     * @param CallbackResolverInterface $resolver
     *
     * @return static|RoutAbleInterface
     */
    public function setCallbackResolver(CallbackResolverInterface $resolver) : RoutAbleInterface;

    /**
     * Get resolver for callback
     *
     * @return CallbackResolverInterface|null
     */
    public function getCallbackResolver();
}
