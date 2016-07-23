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
 * @link        http://fuzeworks.techfuze.net
 * @since       Version 0.0.1
 *
 * @version     Version 0.0.1
 */

use FuzeWorks\Config;
use FuzeWorks\Logger;
use FuzeWorks\Exception\LoggerException;

/**
 * Class ModelTest.
 *
 * Will test the FuzeWorks Model System.
 */
class loggerTest extends CoreTestAbstract
{
    protected $logger;

    public function setUp()
    {
        Config::get('error')->error_reporting = false;
        Logger::$Logs = array();
    }

    public function testGetLogger()
    {
        $this->assertInstanceOf('FuzeWorks\Logger', new Logger);
    }

    public function testErrorHandler()
    {
        Logger::errorHandler(E_ERROR, 'Example error', __FILE__, 1, 'data');
        $this->assertCount(1, Logger::$Logs);

        $log = Logger::$Logs[0];
        $this->assertEquals('ERROR', $log['type']);
        $this->assertEquals('Example error', $log['message']);
        $this->assertEquals(__FILE__, $log['logFile']);
        $this->assertEquals(1, $log['logLine']);
        $this->assertEquals('data', $log['context']);
    }

    /**
     * @depends testErrorHandler
     */
    public function testErrorHandlerTypes()
    {
        $types = array(
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'ERROR',
            E_NOTICE => 'WARNING',
            E_CORE_ERROR => 'ERROR',
            E_CORE_WARNING => 'WARNING',
            E_COMPILE_ERROR => 'ERROR',
            E_COMPILE_WARNING => 'WARNING',
            E_USER_ERROR => 'ERROR',
            E_USER_WARNING => 'WARNING',
            E_USER_NOTICE => 'WARNING',
            E_USER_DEPRECATED => 'WARNING',
            E_STRICT => 'ERROR',
            E_RECOVERABLE_ERROR => 'ERROR',
            E_DEPRECATED => 'WARNING',
            'UNKNOWN' => 'Unknown error: UNKNOWN'
        );

        foreach ($types as $errorType => $output) {
            // Clear the log entries
            Logger::$Logs = array();

            // Log the error
            Logger::errorHandler($errorType, 'Log message');

            // Fetch the error
            $log = Logger::$Logs[0];

            // Check the type
            $this->assertEquals($output, $log['type']);
        }
    }

    public function testExceptionHandler()
    {
        // Create the exception
        $exception = new LoggerException();

        // Prepare output buffering
        ob_start(function () {});

        // Log the exception
        Logger::exceptionHandler($exception);

        // Check the output
        $this->assertEquals('<h1>500</h1><h3>Internal Server Error</h3>', ob_get_clean());

        // Check the logs
        $log = Logger::$Logs[0];
    }

    public function testLog()
    {
        // Log the message
        Logger::log('Log message', 'core_loggerTest', __FILE__, 1);

        // Fetch the message
        $log = Logger::$Logs[0];

        // Assert data
        $this->assertEquals('INFO', $log['type']);
        $this->assertEquals('Log message', $log['message']);
        $this->assertEquals(__FILE__, $log['logFile']);
        $this->assertEquals(1, $log['logLine']);
        $this->assertEquals('core_loggerTest', $log['context']);
    }

    /**
     * @depends testLog
     */
    public function testLogTypes()
    {
        $types = array(
                'newLevel' => 'LEVEL_START',
                'stopLevel' => 'LEVEL_STOP',
                'logError' => 'ERROR',
                'logWarning' => 'WARNING',
                'logDebug' => 'DEBUG',
                'mark' => 'BMARK'
            );

        foreach ($types as $method => $returnValue) {
            // Clear the log entries
            Logger::$Logs = array();

            // Log the entry
            Logger::{$method}('Log message', 'core_loggerTest', __FILE__, 1);

            // Fetch the entry
            $log = Logger::$Logs[0];

            // Assert the entry
            $this->assertEquals($returnValue, $log['type']);
        }
    }

    public function testHttpError()
    {
        $http_codes = array(
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',
            426 => 'Upgrade Required',
            428 => 'Precondition Required',
            429 => 'Too Many Requests',
            431 => 'Request Header Fields Too Large',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            509 => 'Bandwidth Limit Exceeded',
            510 => 'Not Extended',
            511 => 'Network Authentication Required',
        );

        // Test all error codes
        foreach ($http_codes as $code => $description) {
            // Prepare output buffering
            ob_start(function () {});

            // Fire the error
            Logger::http_error($code);

            // Check the output
            $this->assertEquals('<h1>'.$code.'</h1><h3>'.$description.'</h3>', ob_get_clean());            
        }

        // Test when not viewing
        Logger::http_error(404, false);
    }

    public function testEnable()
    {
        Logger::enable();
        $this->assertTrue(Logger::isEnabled());
    }

    public function testDisable()
    {
        Logger::disable();
        $this->assertFalse(Logger::isEnabled());
    }

    public function tearDown()
    {
        Logger::$Logs = array();
    }
}
