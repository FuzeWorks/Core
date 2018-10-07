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

use FuzeWorks\Factory;
use FuzeWorks\Exception\FactoryException;

/**
 * Class FactoryTest.
 *
 * Will test the FuzeWorks Factory.
 */
class factoryTest extends CoreTestAbstract
{
    public function testCanLoadFactory()
    {
        $this->assertInstanceOf('FuzeWorks\Factory', Factory::getInstance());
    }

    /**
     * @depends testCanLoadFactory
     */
    public function testLoadSameInstance()
    {
        $this->assertSame(Factory::getInstance(), Factory::getInstance());
    }

    /**
     * @depends testCanLoadFactory
     */
    public function testLoadDifferentInstance()
    {
        // First a situation where one is the shared instance and one is a cloned instance
        $this->assertNotSame(Factory::getInstance(), Factory::getInstance(true));

        // And a situation where both are cloned instances
        $this->assertNotSame(Factory::getInstance(true), Factory::getInstance(true));
    }

    /**
     * @depends testCanLoadFactory
     */
    public function testObjectsSameInstance()
    {
        // Create mock
        $mock = $this->getMockBuilder(MockFactory::class)->setMethods(['mockListener'])->getMock();

        // Test not set
        $this->assertFalse(isset(Factory::getInstance()->mock));

        // Same instance factories
        $factory1 = Factory::getInstance()->setInstance('Mock', $mock);
        $factory2 = Factory::getInstance()->setInstance('Mock', $mock);

        // Return the mocks
        $this->assertSame($factory1->mock, $factory2->mock);

        // Different instance factories
        $factory3 = Factory::getInstance(true)->setInstance('Mock', $mock);
        $factory4 = Factory::getInstance(true)->setInstance('Mock', $mock);

        // Return the mocks
        $this->assertSame($factory3->mock, $factory4->mock);
    }

    /**
     * @depends testObjectsSameInstance
     */
    public function testObjectsDifferentInstance()
    {
        // Create mock
        $mock = $this->getMockBuilder(MockFactory::class)->getMock();

        // Same instance factories
        $factory1 = Factory::getInstance()->setInstance('Mock', $mock);
        $factory2 = Factory::getInstance()->setInstance('Mock', $mock);

        // Clone the instance in factory2
        $factory2->cloneInstance('Mock');

        // Should be true, since both Factories use the same Mock instance
        $this->assertSame($factory1->mock, $factory2->mock);

        // Different instance factories
        $factory3 = Factory::getInstance(true)->setInstance('Mock', $mock);
        $factory4 = Factory::getInstance(true)->setInstance('Mock', $mock);

        // Clone the instance in factory4
        $factory4->cloneInstance('Mock');

        // Should be false, since both Factories use a different Mock instance
        $this->assertNotSame($factory3->mock, $factory4->mock);
    }

    /**
     * @expectedException FuzeWorks\Exception\FactoryException
     */
    public function testCloneInstanceWrongClassname()
    {
        // Get factory
        $factory = new Factory;

        // Attempt
        $factory->cloneInstance('fake');
    }

    public function testGlobalCloneInstance()
    {
        // First test without global cloning
        $this->assertSame(Factory::getInstance(), Factory::getInstance());

        // Now enable global cloning
        Factory::enableCloneInstances();

        // Now test without global cloning
        $this->assertNotSame(Factory::getInstance(), Factory::getInstance());

        // Disable global cloning
        Factory::disableCloneInstances();

        // And test again without global cloning
        $this->assertSame(Factory::getInstance(), Factory::getInstance());
    }

    public function testNewFactoryInstance()
    {
        // Load the different factories
        $factory = new Factory();
        $factory2 = Factory::getInstance();

        // Test if the objects are different factory instances
        $this->assertNotSame($factory, $factory2);

        // And test if all ClassInstances are the same
        $this->assertSame($factory->config, $factory2->config);
        $this->assertSame($factory->logger, $factory2->logger);
        $this->assertSame($factory->events, $factory2->events);
        $this->assertSame($factory->libraries, $factory2->libraries);
        $this->assertSame($factory->helpers, $factory2->helpers);

        // And test when changing one classInstance
        $factory->newInstance('Helpers');
        $this->assertNotSame($factory->helpers, $factory2->helpers);
    }

    /**
     * @expectedException FuzeWorks\Exception\FactoryException
     */
    public function testFactoryNewInstanceNotExist()
    {
        // Load the factory
        $factory = new Factory;

        // First, it does not exist
        $factory->newInstance('fake');
    }

    /**
     * @expectedException FuzeWorks\Exception\FactoryException
     */
    public function testFactoryNewInstanceWrongNamespace()
    {
        // Load the factory
        $factory = new Factory;

        // Second, it just fails
        $factory->newInstance('helpers', 'Test\\');
    }

    public function testRemoveInstance()
    {
        // Load the factory
        $factory = new Factory;

        // Create the object
        $object = new MockObject;

        // Add it to the factory
        $factory->setInstance('test', $object);

        // Test if it is there
        $this->assertObjectHasAttribute('test', $factory);
        $this->assertSame($object, $factory->test);

        // Now remove it
        $factory->removeInstance('test');

        // Assert that it's gone
        $this->assertObjectNotHasAttribute('test', $factory);
    }

    /**
     * @expectedException FuzeWorks\Exception\FactoryException
     */
    public function testRemoveInstanceNotExist()
    {
        // Load the factory
        $factory = new Factory;

        // Test
        $factory->removeInstance('fake');
    }

    public function testInstanceIsset()
    {
        // Load the factory
        $factory = new Factory;

        // Test if not set and add instance
        $this->assertFalse($factory->instanceIsset('test'));
        $factory->setInstance('test', 5);

        // Test if isset and value
        $this->assertTrue($factory->instanceIsset('test'));
        $this->assertEquals(5, $factory->test);
    }

    public function tearDown()
    {
        Factory::disableCloneInstances();
        
        $factory = Factory::getInstance();
        if (isset($factory->Mock))
        {
           $factory->removeInstance('Mock'); 
        }
    }

}

class MockFactory {

}

class MockObject {

}