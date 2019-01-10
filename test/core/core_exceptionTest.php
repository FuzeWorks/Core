<?php
/**
 * FuzeWorks Framework Core.
 *
 * The FuzeWorks PHP FrameWork
 *
 * Copyright (C) 2013-2018 TechFuze
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
 * @copyright Copyright (c) 2013 - 2018, TechFuze. (http://techfuze.net)
 * @license   https://opensource.org/licenses/MIT MIT License
 *
 * @link  http://techfuze.net/fuzeworks
 * @since Version 0.0.1
 *
 * @version Version 1.2.0
 */

use FuzeWorks\Exception\Exception;
use FuzeWorks\Exception\CoreException;
use FuzeWorks\Exception\ConfigException;
use FuzeWorks\Exception\EventException;
use FuzeWorks\Exception\FactoryException;
use FuzeWorks\Exception\HelperException;
use FuzeWorks\Exception\InvalidArgumentException;
use FuzeWorks\Exception\LibraryException;
use FuzeWorks\Exception\LoggerException;
use FuzeWorks\Exception\ConfiguratorException;

/**
 * Class ExceptionTest.
 *
 * Exception testing suite, tests if all exceptions can be fired
 */
class exceptionTestTest extends CoreTestAbstract
{

    /**
     * @expectedException FuzeWorks\Exception\Exception
     */
    public function testException()
    {
        throw new Exception("Exception Test Run", 1);
    }

    /**
     * @expectedException FuzeWorks\Exception\CoreException
     */
    public function testCoreException()
    {
        throw new CoreException("Exception Test Run", 1);
    }

    /**
     * @expectedException FuzeWorks\Exception\ConfigException
     */
    public function testConfigException()
    {
        throw new ConfigException("Exception Test Run", 1);
    }

    /**
     * @expectedException FuzeWorks\Exception\EventException
     */
    public function testEventException()
    {
        throw new EventException("Exception Test Run", 1);
    }

    /**
     * @expectedException FuzeWorks\Exception\FactoryException
     */
    public function testFactoryException()
    {
        throw new FactoryException("Exception Test Run", 1);
    }

    /**
     * @expectedException FuzeWorks\Exception\HelperException
     */
    public function testHelperException()
    {
        throw new HelperException("Exception Test Run", 1);
    }

    /**
     * @expectedException FuzeWorks\Exception\InvalidArgumentException
     */
    public function testInvalidArgumentException()
    {
        throw new InvalidArgumentException("Exception Test Run", 1);
    }

    /**
     * @expectedException FuzeWorks\Exception\LibraryException
     */
    public function testLibraryException()
    {
        throw new LibraryException("Exception Test Run", 1);
    }

    /**
     * @expectedException FuzeWorks\Exception\LoggerException
     */
    public function testLoggerException()
    {
        throw new LoggerException("Exception Test Run", 1);
    }

    /**
     * @expectedException FuzeWorks\Exception\ConfiguratorException
     */
    public function testConfiguratorException()
    {
        throw new ConfiguratorException("Exception Test Run", 1);
    }

}