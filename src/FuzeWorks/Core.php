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

namespace FuzeWorks;
use FuzeWorks\Exception\Exception;
use FuzeWorks\Exception\CoreException;

/**
 * FuzeWorks Core.
 *
 * Holds all the modules and starts the framework. Allows for starting and managing modules
 *
 * @author    TechFuze <contact@techfuze.net>
 * @copyright Copyright (c) 2013 - 2018, Techfuze. (http://techfuze.net)
 */
class Core
{
    /**
     * The current version of the framework.
     *
     * @var string Framework version
     */
    public static $version = '1.0.0';

    /**
     * Working directory of the Framework.
     *
     * This is required to make the shutdown function working under Apache webservers
     *
     * @var string
     */
    public static $cwd;

    public static $coreDir;

    public static $tempDir;

    public static $logDir;

    public static $appDirs = [];

    /**
     * The HTTP status code of the current request
     *
     * @var int $http_status_code Status code
     */
    public static $http_status_code = 200;

    /**
     * Initializes the core.
     *
     * @throws \Exception
     */
    public static function init(): Factory
    {
        // Set the CWD for usage in the shutdown function
        self::$cwd = getcwd();

        // Set the core dir for when the loading of classes is required
        self::$coreDir = dirname(__DIR__);
        
        // Defines the time the framework starts. Used for timing functions in the framework
        if (!defined('STARTTIME')) {
            define('STARTTIME', microtime(true));
            define('DS', DIRECTORY_SEPARATOR);
        }

        // Load basics
        ignore_user_abort(true);
        register_shutdown_function(array('\FuzeWorks\Core', 'shutdown'));

        // Return the Factory
        return new Factory();
    }

    /**
     * Stop FuzeWorks and run all shutdown functions.
     *
     * Afterwards run the Logger shutdown function in order to possibly display the log
     */
    public static function shutdown()
    {
        // Fix Apache bug where CWD is changed upon shutdown
        chdir(self::$cwd);

        // Fire the Shutdown event
        $event = Events::fireEvent('coreShutdownEvent');

        if ($event->isCancelled() === false)
        {
            Logger::shutdownError();
            Logger::shutdown();
        }
    }

    /**
     * Checks whether the current running version of PHP is equal to the input string.
     *
     * @param   string
     * @return  bool    true if running higher than input string
     */
    public static function isPHP($version): bool
    {
        static $_is_php;
        $version = (string) $version;

        if ( ! isset($_is_php[$version]))
        {
            $_is_php[$version] = version_compare(PHP_VERSION, $version, '>=');
        }

        return $_is_php[$version];
    }

    public static function isCli(): bool
    {
        return (PHP_SAPI === 'cli' OR defined('STDIN'));
    }

    /**
     * Is HTTPS?
     *
     * Determines if the application is accessed via an encrypted
     * (HTTPS) connection.
     *
     * @return  bool
     */
    public static function isHttps(): bool
    {
        if ( ! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        {
            return TRUE;
        }
        elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        {
            return TRUE;
        }
        elseif ( ! empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off')
        {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Tests for file writability
     *
     * is_writable() returns TRUE on Windows servers when you really can't write to
     * the file, based on the read-only attribute. is_writable() is also unreliable
     * on Unix servers if safe_mode is on.
     *
     * @link    https://bugs.php.net/bug.php?id=54709
     * @param   string
     * @return  bool
     */
    public static function isReallyWritable($file): bool
    {
        // If we're on a Unix server with safe_mode off we call is_writable
        if (DIRECTORY_SEPARATOR === '/' && ! ini_get('safe_mode'))
        {
            return is_writable($file);
        }

        /* For Windows servers and safe_mode "on" installations we'll actually
         * write a file then read it. Bah...
         */
        if (is_dir($file))
        {
            $file = rtrim($file, '/').'/'.md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === FALSE)
            {
                return FALSE;
            }

            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);
            return TRUE;
        }
        elseif ( ! is_file($file) OR ($fp = @fopen($file, 'ab')) === FALSE)
        {
            return FALSE;
        }

        fclose($fp);
        return TRUE;
    }

    /**
     * Set HTTP Status Header
     *
     * @param   int the status code
     * @param   string
     * @return  void
     */
    public static function setStatusHeader($code = 200, $text = '')
    {
        if (self::isCli())
        {
            return;
        }

        if (empty($code) OR ! is_numeric($code))
        {
            throw new Exception('Status codes must be numeric', 1);
        }

        if (empty($text))
        {
            is_int($code) OR $code = (int) $code;
            $stati = array(
                100 => 'Continue',
                101 => 'Switching Protocols',

                200 => 'OK',
                201 => 'Created',
                202 => 'Accepted',
                203 => 'Non-Authoritative Information',
                204 => 'No Content',
                205 => 'Reset Content',
                206 => 'Partial Content',

                300 => 'Multiple Choices',
                301 => 'Moved Permanently',
                302 => 'Found',
                303 => 'See Other',
                304 => 'Not Modified',
                305 => 'Use Proxy',
                307 => 'Temporary Redirect',

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
                422 => 'Unprocessable Entity',

                500 => 'Internal Server Error',
                501 => 'Not Implemented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable',
                504 => 'Gateway Timeout',
                505 => 'HTTP Version Not Supported'
            );

            if (isset($stati[$code]))
            {
                $text = $stati[$code];
            }
            else
            {
                throw new CoreException('No status text available. Please check your status code number or supply your own message text.', 1);
            }
        }

        if (strpos(PHP_SAPI, 'cgi') === 0)
        {
            header('Status: '.$code.' '.$text, TRUE);
        }
        else
        {
            $server_protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
            header($server_protocol.' '.$code.' '.$text, TRUE, $code);
        }
    }
}
