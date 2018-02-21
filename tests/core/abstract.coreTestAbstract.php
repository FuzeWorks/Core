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
use PHPUnit\Framework\TestCase;
use FuzeWorks\Events;
use FuzeWorks\Layout;
use FuzeWorks\Factory;
use FuzeWorks\Core;
use FuzeWorks\LoggerTracyBridge;

/**
 * Class CoreTestAbstract.
 *
 * Provides the event tests with some basic functionality
 */
abstract class CoreTestAbstract extends TestCase
{
    /**
     * Remove all listeners before the next test starts.
     *
     * Reset the layout manager
     */
    public function tearDown()
    {
        // Clear all events created by tests
        Events::$listeners = array();

        // Re-register the LoggerTracyBridge to supress errors
        LoggerTracyBridge::register();

        // Reset the layout manager
        Factory::getInstance()->layout->reset();

        // Reset all config files
        Factory::getInstance()->config->discardConfigFiles();

        // Re-enable events, in case they have been disabled
        Events::enable();

        // Clear the output
        Factory::getInstance()->output->set_output('');

        // Reset the HTTP status code
        Core::$http_status_code = 200;
    }
}
