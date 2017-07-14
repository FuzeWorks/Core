<?php
/**
 * FuzeWorks.
 *
 * The FuzeWorks MVC PHP FrameWork
 *
 * Copyright (C) 2015   TechFuze
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      TechFuze
 * @copyright   Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 * @copyright   Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license     http://opensource.org/licenses/GPL-3.0 GPLv3 License
 *
 * @link        http://techfuze.net/fuzeworks
 * @since       Version 0.0.1
 *
 * @version     Version 1.0.1
 */

use FuzeWorks\Exception\Exception;
use FuzeWorks\Exception\CoreException;
use FuzeWorks\Exception\ConfigException;
use FuzeWorks\Exception\DatabaseException;
use FuzeWorks\Exception\EventException;
use FuzeWorks\Exception\FactoryException;
use FuzeWorks\Exception\HelperException;
use FuzeWorks\Exception\InvalidArgumentException;
use FuzeWorks\Exception\LanguageException;
use FuzeWorks\Exception\LayoutException;
use FuzeWorks\Exception\LibraryException;
use FuzeWorks\Exception\LoggerException;
use FuzeWorks\Exception\ModelException;
use FuzeWorks\Exception\RouterException;
use FuzeWorks\Exception\SecurityException;
use FuzeWorks\Exception\UriException;

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
     * @expectedException FuzeWorks\Exception\DatabaseException
     */
    public function testDatabaseException()
    {
        throw new DatabaseException("Exception Test Run", 1);
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
     * @expectedException FuzeWorks\Exception\LayoutException
     */
    public function testLayoutException()
    {
        throw new LayoutException("Exception Test Run", 1);
    }

    /**
     * @expectedException FuzeWorks\Exception\LanguageException
     */
    public function testLanguageException()
    {
        throw new LanguageException("Exception Test Run", 1);
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
     * @expectedException FuzeWorks\Exception\ModelException
     */
    public function testModelException()
    {
        throw new ModelException("Exception Test Run", 1);
    }

    /**
     * @expectedException FuzeWorks\Exception\RouterException
     */
    public function testRouterException()
    {
        throw new RouterException("Exception Test Run", 1);
    }

    /**
     * @expectedException FuzeWorks\Exception\SecurityException
     */
    public function testSecurityException()
    {
        throw new SecurityException("Exception Test Run", 1);
    }

    /**
     * @expectedException FuzeWorks\Exception\UriException
     */
    public function testUriException()
    {
        throw new UriException("Exception Test Run", 1);
    }


}
