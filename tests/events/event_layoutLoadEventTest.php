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
 * @version     Version 1.0.0
 */
use FuzeWorks\Events;
use FuzeWorks\Factory;
use FuzeWorks\EventPriority;

/**
 * Class LayoutLoadEventTest.
 */
class layoutLoadEventTest extends CoreTestAbstract
{

    protected $factory;

    public function setUp()
    {
        // Load the factory first
        $this->factory = Factory::getInstance();
    }

    /**
     * Check if the event is fired when it should be.
     */
    public function test_basic()
    {
        $mock = $this->getMockBuilder(MockLayoutEventTest::class)->setMethods(['mockMethod'])->getMock();
        $mock->expects($this->once())->method('mockMethod');

        Events::addListener(array($mock, 'mockMethod'), 'layoutLoadEvent', EventPriority::NORMAL);

        // And run the test
        $this->factory->layout->get('home');
    }

    /**
     * Intercept and change the event.
     *
     * @expectedException FuzeWorks\Exception\LayoutException
     */
    public function test_change()
    {
        Events::addListener(array($this, 'listener_change'), 'layoutLoadEvent', EventPriority::NORMAL);
        $this->factory->layout->get('home');
    }

    // Change title from new to other
    public function listener_change($event)
    {

        // This controller should not exist
        $this->assertTrue(strpos($event->file, 'application/Layout/layout.home.php') !== false);
        $this->assertTrue(strpos($event->directory, 'application/Layout/') !== false);

        // It should exist now
        $event->file = $event->directory . 'layout.test.not_found';

        return $event;
    }

    /**
     * Cancel events.
     */
    public function test_cancel()
    {

        // Listen for the event and cancel it
        Events::addListener(array($this, 'listener_cancel'), 'layoutLoadEvent', EventPriority::NORMAL);
        $this->assertFalse($this->factory->layout->get('home'));
    }

    // Cancel all calls
    public function listener_cancel($event)
    {
        $event->setCancelled(true);
    }
}

class MockLayoutEventTest {
    public function MockMethod() {}
}
