<?php
/**
 * FuzeWorks.
 *
 * The FuzeWorks MVC PHP FrameWork
 *
 * Copyright (C) 2017   TechFuze
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
 * @since       Version 1.0.4
 *
 * @version     Version 1.0.4
 */
use FuzeWorks\EventPriority;

/**
 * Class EventPriorityTest.
 *
 * This test will test the EventPriority class
 */
class eventPriorityTest extends CoreTestAbstract
{

    public function testPriorities()
    {
        $this->assertEquals(EventPriority::LOWEST, 5);
        $this->assertEquals(EventPriority::LOW, 4);
        $this->assertEquals(EventPriority::NORMAL, 3);
        $this->assertEquals(EventPriority::HIGH, 2);
        $this->assertEquals(EventPriority::HIGHEST, 1);
        $this->assertEquals(EventPriority::MONITOR, 0);
    }

    public function testGetPriority()
    {
        $this->assertEquals(EventPriority::getPriority(5), 'EventPriority::LOWEST');
        $this->assertEquals(EventPriority::getPriority(4), 'EventPriority::LOW');
        $this->assertEquals(EventPriority::getPriority(3), 'EventPriority::NORMAL');
        $this->assertEquals(EventPriority::getPriority(2), 'EventPriority::HIGH');
        $this->assertEquals(EventPriority::getPriority(1), 'EventPriority::HIGHEST');
        $this->assertEquals(EventPriority::getPriority(0), 'EventPriority::MONITOR');
    }

    public function testGetInvalidPriority()
    {
        $this->assertFalse(EventPriority::getPriority(99));
    }

    public function testHighestPriority()
    {
        $this->assertEquals(EventPriority::getHighestPriority(), EventPriority::MONITOR);
    }

    public function testLowestPriority()
    {
        $this->assertEquals(EventPriority::getLowestPriority(), EventPriority::LOWEST);
    }

}
