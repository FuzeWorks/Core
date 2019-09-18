<?php
/**
 * FuzeWorks Framework Core.
 *
 * The FuzeWorks PHP FrameWork
 *
 * Copyright (C) 2013-2019 TechFuze
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
 * @copyright Copyright (c) 2013 - 2019, TechFuze. (http://techfuze.net)
 * @license   https://opensource.org/licenses/MIT MIT License
 *
 * @link  http://techfuze.net/fuzeworks
 * @since Version 0.0.1
 *
 * @version Version 1.2.0
 */

use FuzeWorks\Event;
use FuzeWorks\Events;
use FuzeWorks\Priority;

/**
 * Class EventTest.
 *
 * This test will test Events
 * @coversDefaultClass \FuzeWorks\Events
 */
class eventsTest extends CoreTestAbstract
{

    /**
     * @covers ::fireEvent
     */
    public function testFireEvent()
    {
        $mock = $this->getMockBuilder(Observer::class)->setMethods(['mockListener'])->getMock();
        $mock->expects($this->once())->method('mockListener')->with($this->isInstanceOf('MockEvent'));

        Events::addListener(array($mock, 'mockListener'), 'mockEvent', Priority::NORMAL);
        Events::fireEvent('mockEvent');
    }

    /**
     * @depends testFireEvent
     * @covers ::fireEvent
     */
    public function testObjectEvent()
    {
        $event = $this->getMockBuilder(MockEvent::class)->getMock();

        $listener = $this->getMockBuilder(Observer::class)->setMethods(['mockListener'])->getMock();
        $listener->expects($this->once())->method('mockListener')->with($this->equalTo($event));

        Events::addListener(array($listener, 'mockListener'), get_class($event), Priority::NORMAL);
        Events::fireEvent($event);
    }

    /**
     * @depends testObjectEvent
     * @covers ::fireEvent
     */
    public function testVariablePassing()
    {
        $event = $this->getMockBuilder(MockEvent::class)->getMock();
        $event->key = 'value';

        $eventName = get_class($event);

        Events::addListener(function($event) {
            $this->assertEquals('value', $event->key);

        }, $eventName, Priority::NORMAL);

        Events::fireEvent($event);
    }

    /**
     * @depends testFireEvent
     * @covers ::fireEvent
     */
    public function testEventArguments()
    {
        // Prepare test argument
        $argument = 'HelloWorld';

        // Create mock event
        $event = $this->getMockBuilder(MockEvent::class)->setMethods(['init'])->getMock();
        $event->expects($this->once())->method('init')->with($this->equalTo($argument));

        // Fire it
        Events::fireEvent($event, $argument);
    }

    /**
     * @depends testVariablePassing
     * @covers ::fireEvent
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

        }, $eventName, Priority::HIGH);

        // The second listener, should be called second due to LOW priority
        Events::addListener(function($event) {
            $this->assertEquals(2, $event->key);
            $event->key = 3;
            return $event;

        }, $eventName, Priority::LOW);

        // Fire the event and test if the key is the result of the last listener
        Events::fireEvent($event);
        $this->assertEquals(3, $event->key);
    }

    /**
     * @depends testFireEvent
     * @expectedException FuzeWorks\Exception\EventException
     * @covers ::fireEvent
     */
    public function testInvalidTypeEvent()
    {
        Events::fireEvent(array('x', 'y', 'z'));
    }

    /**
     * @depends testFireEvent
     * @covers ::fireEvent
     * @expectedException FuzeWorks\Exception\EventException
     */
    public function testInvalidClassEvent()
    {
        Events::fireEvent('nonExistingEvent', 'x', 'y', 'z');
    }

    /**
     * @depends testFireEvent
     * @covers ::addListener
     * @covers ::removeListener
     */
    public function testAddAndRemoveListener()
    {
        // First add the listener, expect it to be never called
        $listener = $this->getMockBuilder(Observer::class)->setMethods(['mockListener'])->getMock();
        $listener->expects($this->never())->method('mockListener');
        Events::addListener(array($listener, 'mockListener'), 'mockEvent', Priority::NORMAL);

        // Now try and remove it
        Events::removeListener(array($listener, 'mockListener'), 'mockEvent', Priority::NORMAL);

        // And now fire the event
        Events::fireEvent('mockEvent');
    }

    /**
     * @depends testAddAndRemoveListener
     * @covers ::addListener
     * @expectedException FuzeWorks\Exception\EventException
     */
    public function testAddInvalidPriorityListener()
    {
        Events::addListener(function($event){}, 'mockEvent', 99);
    }

    /**
     * @depends testAddAndRemoveListener
     * @covers ::addListener
     * @expectedException FuzeWorks\Exception\EventException
     */
    public function testAddInvalidNameListener()
    {
        Events::addListener(function($e) {}, '', Priority::NORMAL);
    }

    /**
     * @depends testAddAndRemoveListener
     * @covers ::removeListener
     * @expectedException FuzeWorks\Exception\EventException
     */
    public function testRemoveInvalidPriorityListener()
    {
        Events::removeListener(function($event){}, 'mockEvent', 99);
    }

    /**
     * @depends testAddAndRemoveListener
     * @covers ::removeListener
     */
    public function testRemoveUnsetEventListener()
    {
        $this->assertNull(Events::removeListener(function($event){}, 'emptyListenerArray', Priority::NORMAL));
    }

    /**
     * @depends testAddAndRemoveListener
     * @covers ::removeListener
     */
    public function testRemoveUnsetListener()
    {
        Events::addListener(function($e) {}, 'mockEvent', Priority::NORMAL);
        $this->assertNull(Events::removeListener(function($x) {echo "Called"; }, 'mockEvent', Priority::NORMAL));
    }

    /**
     * @depends testAddAndRemoveListener
     * @covers ::addListener
     */
    public function testListenerVariablePass()
    {
        $event = $this->getMockBuilder(MockEvent::class)->getMock();
        $passVariable = 'value';

        $eventName = get_class($event);

        Events::addListener(function($event, $passVariable) {
            $this->assertEquals('value', $passVariable);

        }, $eventName, Priority::NORMAL, $passVariable);

        Events::fireEvent($event);
    }

    /**
     * @depends testFireEvent
     * @covers ::disable
     * @covers ::fireEvent
     */
    public function testDisable()
    {
        // First add the listener, expect it to be never called
        $listener = $this->getMockBuilder(Observer::class)->setMethods(['mockListener'])->getMock();
        $listener->expects($this->never())->method('mockListener');
        Events::addListener(array($listener, 'mockListener'), 'mockEvent', Priority::NORMAL);

        // Disable the event syste,
        Events::disable();

        // And now fire the event
        Events::fireEvent('mockEvent');
    }

    /**
     * @depends testDisable
     * @covers ::disable
     * @covers ::enable
     */
    public function testReEnable()
    {
        // First add the listener, expect it to be never called
        $listener = $this->getMockBuilder(Observer::class)->setMethods(['mockListener'])->getMock();
        $listener->expects($this->once())->method('mockListener');
        Events::addListener(array($listener, 'mockListener'), 'mockEvent', Priority::NORMAL);

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
