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
 * @since Version 1.0.4
 *
 * @version Version 1.2.0
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
