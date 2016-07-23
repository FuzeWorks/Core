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
 * @author    TechFuze
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 * @copyright Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license   http://opensource.org/licenses/GPL-3.0 GPLv3 License
 *
 * @link  http://techfuze.net/fuzeworks
 * @since Version 0.0.1
 *
 * @version Version 1.0.0
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
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
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
	public function screenLogEventListener($event)
	{
		$event->setCancelled(true);
		return $event;
	}

	public function getTab()
	{
		ob_start(function () {});
		require dirname(__DIR__) . '/views/view.tracyloggertab.php';
		return ob_get_clean();
	}

	public function getPanel()
	{
		ob_start(function () {});
		$logs = Logger::$Logs;
		require dirname(__DIR__) . '/views/view.tracyloggerpanel.php';
		return ob_get_clean();
	}

}