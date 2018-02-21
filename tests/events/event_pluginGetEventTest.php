<?php
/**
 * FuzeWorks.
 *
 * The FuzeWorks MVC PHP FrameWork
 *
 * Copyright (C) 2018   TechFuze
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
 * @copyright   Copyright (c) 2013 - 2018, Techfuze. (http://techfuze.net)
 * @copyright   Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license     http://opensource.org/licenses/GPL-3.0 GPLv3 License
 *
 * @link        http://techfuze.net/fuzeworks
 * @since       Version 1.1.4
 *
 * @version     Version 1.1.4
 */
use FuzeWorks\Factory;
use FuzeWorks\Events;
use FuzeWorks\EventPriority;

/**
 * Class pluginGetEventTest.
 */
class pluginGetEventTest extends CoreTestAbstract
{
    /**
     * Check if the event is fired when it should be.
     */
    public function testPluginGetEvent()
    {
        // Create mock listener
        Events::addListener(
            function($event){$event->setCancelled(true);return $event;}, 
            'pluginGetEvent', 
            EventPriority::NORMAL);

        // And fire the event
        $this->assertFalse(Factory::getInstance()->plugins->get('test'));
    }

    /**
     * @depends testPluginGetEvent
     */
    public function testReplacePlugin()
    {
        // Create mock listener
        Events::addListener(
            function($event){$event->setPlugin('test_string');return $event;}, 
            'pluginGetEvent', 
            EventPriority::NORMAL);

        // And fire the event
        $this->assertEquals('test_string', Factory::getInstance()->plugins->get('test'));
    }
}