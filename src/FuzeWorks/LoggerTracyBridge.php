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
use Tracy\IBarPanel;
use Tracy\Debugger;

/**
 * LoggerTracyBridge Class.
 *
 * This class provides a bridge between FuzeWorks\Logger and Tracy Debugging tool.
 * 
 * This class registers in Tracy, and creates a Bar object which contains the log. 
 * Afterwards it blocks a screen log so that the content is not shown on the screen as well.
 *
 * @author    TechFuze <contact@techfuze.net>
 * @copyright Copyright (c) 2013 - 2018, Techfuze. (http://techfuze.net)
 */
class LoggerTracyBridge implements IBarPanel {

    /**
     * Register the bar and register the event which will block the screen log
     */
	public static function register()
	{
		$class = new self();
		Events::addListener(array($class, 'screenLogEventListener'), 'screenLogEvent', EventPriority::NORMAL);
		$bar = Debugger::getBar();
		$bar->addPanel($class);
	}

    /**
     * Listener that blocks the screen log
     *
     * @param Event
     * @return Event
     */
	public function screenLogEventListener($event): Event
	{
		$event->setCancelled(true);
		return $event;
	}

	public function getTab(): string
	{
		ob_start(function () {});
		require dirname(__DIR__) . DS . 'Layout' . DS . 'layout.tracyloggertab.php';
		return ob_get_clean();
	}

	public function getPanel(): string
	{
        // If an error is thrown, log it
        $errfile = 'Unknown file';
        $errstr = 'shutdown';
        $errno = E_CORE_ERROR;
        $errline = 0;

        $error = error_get_last();
        if ($error !== null) {
            $errno = $error['type'];
            $errfile = $error['file'];
            $errline = $error['line'];
            $errstr = $error['message'];

            // Log it!
            Logger::errorHandler($errno, $errstr, $errfile, $errline);
        }

        // Reverse the logs 
		$logs = array_reverse(Logger::$Logs, true);

		// Parse the panel
		ob_start(function () {});
		require dirname(__DIR__) . DS . 'Layout' . DS . 'layout.tracyloggerpanel.php';
		return ob_get_clean();
	}

}