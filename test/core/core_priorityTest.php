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
 * @since Version 1.0.4
 *
 * @version Version 1.2.0
 */
use FuzeWorks\Priority;

/**
 * Class priorityTest.
 *
 * This test will test the Priority class
 */
class priorityTest extends CoreTestAbstract
{

    public function testPriorities()
    {
        $this->assertEquals(Priority::LOWEST, 5);
        $this->assertEquals(Priority::LOW, 4);
        $this->assertEquals(Priority::NORMAL, 3);
        $this->assertEquals(Priority::HIGH, 2);
        $this->assertEquals(Priority::HIGHEST, 1);
        $this->assertEquals(Priority::MONITOR, 0);
    }

    public function testGetPriority()
    {
        $this->assertEquals(Priority::getPriority(5), 'Priority::LOWEST');
        $this->assertEquals(Priority::getPriority(4), 'Priority::LOW');
        $this->assertEquals(Priority::getPriority(3), 'Priority::NORMAL');
        $this->assertEquals(Priority::getPriority(2), 'Priority::HIGH');
        $this->assertEquals(Priority::getPriority(1), 'Priority::HIGHEST');
        $this->assertEquals(Priority::getPriority(0), 'Priority::MONITOR');
    }

    public function testGetInvalidPriority()
    {
        $this->assertFalse(Priority::getPriority(99));
    }

    public function testHighestPriority()
    {
        $this->assertEquals(Priority::getHighestPriority(), Priority::MONITOR);
    }

    public function testLowestPriority()
    {
        $this->assertEquals(Priority::getLowestPriority(), Priority::LOWEST);
    }

}
