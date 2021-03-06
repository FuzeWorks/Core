<?php
/**
 * FuzeWorks Framework Core.
 *
 * The FuzeWorks PHP FrameWork
 *
 * Copyright (C) 2013-2019 TechFuze
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
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @author    TechFuze
 * @copyright Copyright (c) 2013 - 2019, TechFuze. (http://techfuze.net)
 * @license   https://opensource.org/licenses/MIT MIT License
 *
 * @link  http://techfuze.net/fuzeworks
 * @since Version 0.0.1
 *
 * @version Version 1.2.0
 */
use PHPUnit\Framework\TestCase;
use FuzeWorks\Events;
use FuzeWorks\Factory;
use FuzeWorks\Core;
use FuzeWorks\Config;
use FuzeWorks\LoggerTracyBridge;

/**
 * Class CoreTestAbstract.
 *
 * Provides the event tests with some basic functionality
 */
abstract class CoreTestAbstract extends TestCase
{
    /**
     * Reset multiple components to their original state before running the next test
     */
    public function tearDown()
    {
        // Clear all events created by tests
        Events::$listeners = [];

        // Reset all config files
        Factory::getInstance('config')->discardConfigFiles();

        // Re-enable events, in case they have been disabled
        Events::enable();

        // Remove Config overrides
        Config::$configOverrides = [];

        // Remove autoloader
        Core::clearAutoloader();
    }
}
