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
 * @version     Version 1.0.4
 */

use FuzeWorks\Event;
use FuzeWorks\Events;
use FuzeWorks\EventPriority;

/**
 * Class EventTest.
 *
 * This test will test Events
 */
class eventsTest extends CoreTestAbstract
{
    public function testFireEvent()
    {
        $mock = $this->getMockBuilder(Observer::class)->setMethods(['mockMethod'])->getMock();
        $mock->expects($this->once())->method('mockMethod');

        Events::addListener(array($mock, 'mockMethod'), 'mockEvent', EventPriority::NORMAL);
        Events::fireEvent('mockEvent');
    }

    /**
     * @depends testFireEvent
     */
    public function testObjectEvent()
    {
        $event = $this->getMockBuilder(MockEvent::class)->getMock();

        $listener = $this->getMockBuilder(Observer::class)->setMethods(['mockListener'])->getMock();
        $listener->expects($this->once())->method('mockListener')->with($this->equalTo($event));

        Events::addListener(array($listener, 'mockListener'), get_class($event), EventPriority::NORMAL);
        Events::fireEvent($event);
    }

    /**
     * @depends testObjectEvent
     */
    public function testVariablePassing()
    {
        $event = $this->getMockBuilder(MockEvent::class)->getMock();
        $event->key = 'value';

        $eventName = get_class($event);

        Events::addListener(function($event) {
            $this->assertEquals('value', $event->key);

        }, $eventName, EventPriority::NORMAL);

        Events::fireEvent($event);
    }

    /**
     * @depends testVariablePassing
     */
    public function testVariableChanging()
    {
        // First prepare the event
        $event = $this->getMockBuilder(MockEvent::class)->getMock();
        $event->key = 1;

        $eventName = get_class($event);

        // The first listener, should be called first due to HIGH priority
        Events::addListener(function($event) {
            $this->assertEquals(1, $event->key);
            $event->key = 2;
            return $event;

        }, $eventName, EventPriority::HIGH);

        // The second listener, should be called second due to LOW priority
        Events::addListener(function($event) {
            $this->assertEquals(2, $event->key);
            $event->key = 3;
            return $event;

        }, $eventName, EventPriority::LOW);

        // Fire the event and test if the key is the result of the last listener
        Events::fireEvent($event);
        $this->assertEquals(3, $event->key);
    }

    /**
     * @depends testFireEvent
     * @expectedException FuzeWorks\Exception\EventException
     */
    public function testInvalidTypeEvent()
    {
        Events::fireEvent(array('x', 'y', 'z'));
    }

    /**
     * @depends testFireEvent
     * @expectedException FuzeWorks\Exception\EventException
     */
    public function testInvalidClassEvent()
    {
        Events::fireEvent('nonExistingEvent', 'x', 'y', 'z');
    }

    /**
     * @depends testFireEvent
     */
    public function testAddAndRemoveListener()
    {
        // First add the listener, expect it to be never called
        $listener = $this->getMockBuilder(Observer::class)->setMethods(['mockListener'])->getMock();
        $listener->expects($this->never())->method('mockListener');
        Events::addListener(array($listener, 'mockListener'), 'mockEvent', EventPriority::NORMAL);

        // Now try and remove it
        Events::removeListener(array($listener, 'mockListener'), 'mockEvent', EventPriority::NORMAL);

        // And now fire the event
        Events::fireEvent('mockEvent');
    }

    /**
     * @depends testAddAndRemoveListener
     * @expectedException FuzeWorks\Exception\EventException
     */
    public function testAddInvalidPriorityListener()
    {
        Events::addListener('fakeCallable', 'mockEvent', 99);
    }

    /**
     * @depends testAddAndRemoveListener
     * @expectedException FuzeWorks\Exception\EventException
     */
    public function testAddInvalidCallableListener()
    {
        Events::addListener(array('nonExistingClass', 'nonExistingMethod'), 'mockEvent', EventPriority::NORMAL);
    }

    /**
     * @depends testAddAndRemoveListener
     * @expectedException FuzeWorks\Exception\EventException
     */
    public function testAddInvalidNameListener()
    {
        Events::addListener(function($e) {}, '', EventPriority::NORMAL);
    }

    /**
     * @depends testAddAndRemoveListener
     * @expectedException FuzeWorks\Exception\EventException
     */
    public function testRemoveInvalidPriorityListener()
    {
        Events::removeListener('fakeCallable', 'mockEvent', 99);
    }

    /**
     * @depends testAddAndRemoveListener
     */
    public function testRemoveUnsetEventListener()
    {
        $this->assertNull(Events::removeListener('fakeCallable', 'emptyListenerArray', EventPriority::NORMAL));
    }

    /**
     * @depends testAddAndRemoveListener
     */
    public function testRemoveUnsetListener()
    {
        Events::addListener(function($e) {}, 'mockEvent', EventPriority::NORMAL);
        $this->assertNull(Events::removeListener(function($x) {echo "Called"; }, 'mockEvent', EventPriority::NORMAL));
    }

    public function testDisable()
    {
        // First add the listener, expect it to be never called
        $listener = $this->getMockBuilder(Observer::class)->setMethods(['mockListener'])->getMock();
        $listener->expects($this->never())->method('mockListener');
        Events::addListener(array($listener, 'mockListener'), 'mockEvent', EventPriority::NORMAL);

        // Disable the event syste,
        Events::disable();

        // And now fire the event
        Events::fireEvent('mockEvent');
    }

    public function testReEnable()
    {
        // First add the listener, expect it to be never called
        $listener = $this->getMockBuilder(Observer::class)->setMethods(['mockListener'])->getMock();
        $listener->expects($this->once())->method('mockListener');
        Events::addListener(array($listener, 'mockListener'), 'mockEvent', EventPriority::NORMAL);

        // Disable the event syste,
        Events::disable();

        // And now fire the event
        Events::fireEvent('mockEvent');

        // Re-enable it
        Events::enable();

        // And fire it again, this time expecting to hit the listener
        Events::fireEvent('mockEvent');
    }
}

class Observer
{
    public function mockMethod() {}
    public function mockListener($event) {}
}

class MockEvent extends Event
{

}
