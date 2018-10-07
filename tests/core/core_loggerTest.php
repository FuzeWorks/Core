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
 * @copyright Copyright (c) 2013 - 2018, Techfuze. (http://techfuze.net)
 * @license   https://opensource.org/licenses/MIT MIT License
 *
 * @link  http://techfuze.net/fuzeworks
 * @since Version 0.0.1
 *
 * @version Version 1.2.0
 */

use FuzeWorks\Logger;
use FuzeWorks\Factory;
use FuzeWorks\Exception\LoggerException;

/**
 * Class ModelTest.
 *
 * Will test the FuzeWorks Model System.
 */
class loggerTest extends CoreTestAbstract
{
    protected $logger;

    protected $output;

    public function setUp()
    {
        Factory::getInstance()->config->get('error')->fuzeworks_error_reporting = false;
        Logger::$Logs = array();
    }

    public function testGetLogger()
    {
        $this->assertInstanceOf('FuzeWorks\Logger', new Logger);
        Factory::getInstance()->config->get('error')->php_error_reporting = true;
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

        // Log the exception
        ob_start();
        Logger::exceptionHandler($exception);

        // Check the output
        $this->assertEquals('<h1>500</h1><h3>Internal Server Error</h3><p></p>', ob_get_clean());

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
            // Fire the error
            ob_start();
            Logger::http_error($code);

            // Check the output
            $this->assertEquals('<h1>'.$code.'</h1><h3>'.$description.'</h3><p></p>', ob_get_clean());
        }
    }

    /**
     * @depends testHttpError
     */
    public function testHttpErrorWithoutLayout()
    {
        $this->assertFalse(Logger::http_error(500, '', false));
    }

    public function testEnableDisable()
    {
        // First enable
        Logger::enable();
        $this->assertTrue(Logger::isEnabled());

        // Then disable
        Logger::disable();
        $this->assertFalse(Logger::isEnabled());
    }

    public function tearDown()
    {
        Logger::disable();
        Logger::$Logs = array();
    }
}
