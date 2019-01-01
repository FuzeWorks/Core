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
 * @copyright Copyright (c) 2013 - 2018, TechFuze. (http://techfuze.net)
 * @license   https://opensource.org/licenses/MIT MIT License
 *
 * @link  http://techfuze.net/fuzeworks
 * @since Version 1.2.0
 *
 * @version Version 1.2.0
 */

use FuzeWorks\Configurator;
use FuzeWorks\Core;
use FuzeWorks\iComponent;
use FuzeWorks\Logger;

/**
 * Class ConfiguratorTest.
 *
 * This test will test the Configurator class
 */
class configuratorTest extends CoreTestAbstract
{

    /**
     * @var Configurator
     */
    protected $configurator;

    public function setUp()
    {
        $this->configurator = new Configurator;
        $this->configurator->setTempDirectory(dirname(__DIR__) . '/temp');
        $this->configurator->setLogDirectory(dirname(__DIR__) . '/temp');
        $this->configurator->setTimeZone('Europe/Amsterdam');
    }

    public function tearDown()
    {
        Core::$appDirs = [dirname(__DIR__) . '/application'];
        Core::$tempDir = dirname(__DIR__) . '/temp';
        Core::$logDir = dirname(__DIR__) . '/temp';
    }

    public function testGetConfiguratorClass()
    {
        $this->assertInstanceOf('FuzeWorks\Configurator', $this->configurator);
    }

    public function testCreateContainer()
    {
        $this->assertInstanceOf('FuzeWorks\Factory', $this->configurator->createContainer());
    }

    /* ---------------------------------- Components ------------------------------------------------ */

    /**
     * @depends testCreateContainer
     */
    public function testAddComponent()
    {
        // Load the component
        require_once 'test'.DS.'components'.DS.'testAddComponent'.DS.'TestAddComponent.php';
        $component = new FuzeWorks\Component\TestComponent();
        $this->assertInstanceOf('FuzeWorks\Configurator', $this->configurator->addComponent($component));

        // Create container and test if component is added and has known properties
        $container = $this->configurator->createContainer()->init();
        $this->assertTrue(property_exists($container, 'test'));
        $this->assertInstanceOf('FuzeWorks\Component\Test', $container->test);
        $this->assertEquals(5, $container->test->variable);
    }

    /**
     * @depends testAddComponent
     */
    public function testAddComponentClassByObject()
    {
        // Create object
        $object = $this->getMockBuilder(MockComponentClass::class)->getMock();
        $object->variable = 'value';

        // Create and add component
        $component = $this->getMockBuilder(MockComponent::class)->setMethods(['getClasses'])->getMock();
        $component->method('getClasses')->willReturn(['componentobject' => $object]);
        $this->assertInstanceOf('FuzeWorks\Configurator', $this->configurator->addComponent($component));

        // Create container and test for variable
        $container = $this->configurator->createContainer()->init();
        $this->assertEquals('value', $container->componentobject->variable);
    }

    /**
     * @depends testAddComponent
     * @expectedException FuzeWorks\Exception\ConfiguratorException
     */
    public function testAddComponentFail()
    {
        // Load the component
        require_once 'test'.DS.'components'.DS.'testAddComponentFail'.DS.'TestAddComponentFail.php';
        $component = new FuzeWorks\Component\TestAddComponentFailComponent;
        $this->configurator->addComponent($component);

        // Create container
        $this->configurator->createContainer()->init();
    }

    /* ---------------------------------- Directories ----------------------------------------------- */

    /**
     * @depends testCreateContainer
     */
    public function testSetLogDirectory()
    {
        // Create mock filesystem
        $fs = vfsStream::setup('testSetLogDirectory');

        // Set the directory
        $this->assertInstanceOf('FuzeWorks\Configurator', $this->configurator->setLogDirectory(vfsStream::url('testSetLogDirectory')));

        // Create container and test if properly set
        $this->configurator->createContainer()->init();
        $this->assertEquals(Core::$logDir, vfsStream::url('testSetLogDirectory'));

        // Create a log and write off to file
        Logger::log('Test log for the file');
        Logger::logLastRequest();

        // Assert if exist
        $this->assertTrue($fs->hasChild('fwlog_request.log'));
    }

    /**
     * @depends testSetLogDirectory
     * @expectedException \FuzeWorks\Exception\InvalidArgumentException
     */
    public function testSetLogDirectoryNotDirectory()
    {
        // Set the directory
        $this->configurator->setLogDirectory('not_exist');
    }

    /**
     * @depends testCreateContainer
     */
    public function testSetTempDirectory()
    {
        // Create mock filesystem
        vfsStream::setup('testSetTempDirectory');

        // Set the directory
        $this->assertInstanceOf('FuzeWorks\Configurator', $this->configurator->setTempDirectory(vfsStream::url('testSetTempDirectory')));

        // Create container and test if properly set
        $this->configurator->createContainer()->init();
        $this->assertEquals(Core::$tempDir, vfsStream::url('testSetTempDirectory'));
    }

    /**
     * @depends testSetTempDirectory
     * @expectedException \FuzeWorks\Exception\InvalidArgumentException
     */
    public function testSetTempDirectoryNotDirectory()
    {
        // Set the directory
        $this->configurator->setTempDirectory('not_exist');
    }

    /**
     * @depends testCreateContainer
     */
    public function testAddAppDirectory()
    {
        // Create mock filesystem
        vfsStream::setup('testAddAppDirectory');

        // Add the directory
        $this->assertInstanceOf('FuzeWorks\Configurator', $this->configurator->addDirectory(vfsStream::url('testAddAppDirectory')));

        // Create container and test if properly set
        $this->configurator->createContainer()->init();
        $this->assertEquals(Core::$appDirs, [vfsStream::url('testAddAppDirectory')]);
    }

    /**
     * @depends testCreateContainer
     * @depends testAddComponent
     */
    public function testAddComponentDirectory()
    {
        // Create mock filesystem
        vfsStream::setup('testAddComponentDirectory');

        // Add the component
        require_once 'test'.DS.'components'.DS.'testAddComponentDirectory'.DS.'TestAddComponentDirectory.php';
        $component = new FuzeWorks\Component\TestAddComponentDirectoryComponent();
        $this->configurator->addComponent($component);

        // Add the directory
        $this->configurator->addDirectory(vfsStream::url('testAddComponentDirectory'), 'testaddcomponentdirectory');

        // Create container and test if component is added and has known properties
        $container = $this->configurator->createContainer()->init();
        $this->assertTrue(property_exists($container, 'testaddcomponentdirectory'));
        $this->assertInstanceOf('FuzeWorks\Component\TestAddComponentDirectory', $container->testaddcomponentdirectory);
        $this->assertEquals(5, $container->testaddcomponentdirectory->variable);

        // Verify directory is set
        $this->assertEquals($container->testaddcomponentdirectory->directories, [vfsStream::url('testAddComponentDirectory')]);
    }

    /* ---------------------------------- Deferred Invocation --------------------------------------- */

    /**
     * @depends testAddComponent
     */
    public function testDeferComponentClassMethod()
    {
        // Create mocks
        $componentClass = $this->getMockBuilder(MockComponentClass::class)->setMethods(['update'])->getMock();
        $componentClass->expects($this->once())->method('update')->willReturn('result');
        $component = $this->getMockBuilder(MockComponent::class)->setMethods(['getClasses'])->getMock();
        $component->method('getClasses')->willReturn(['test' => $componentClass]);

        // Add the Component
        $this->configurator->addComponent($component);

        // Defer method
        $deferred = $this->configurator->deferComponentClassMethod('test', 'update');

        // Expect false before execution
        $this->assertFalse($deferred->isInvoked());
        $this->assertFalse($deferred->getResult());

        // Create container
        $this->configurator->createContainer();

        // Make assertions
        $this->assertTrue($deferred->isInvoked());
        $this->assertEquals('result', $deferred->getResult());
    }

    /**
     * @depends testDeferComponentClassMethod
     */
    public function testDeferComponentClassMethodWithCallback()
    {
        // Create mocks
        $componentClass = $this->getMockBuilder(MockComponentClass::class)->setMethods(['update'])->getMock();
        $componentClass->expects($this->once())->method('update')->with('some_argument')->willReturn('result');
        $component = $this->getMockBuilder(MockComponent::class)->setMethods(['getClasses'])->getMock();
        $component->method('getClasses')->willReturn(['test' => $componentClass]);

        // Add the Component
        $this->configurator->addComponent($component);

        // Defer method
        $deferred = $this->configurator->deferComponentClassMethod(
            'test',
            'update',
            function($result){
                $this->assertEquals('result', $result);
            },
            'some_argument'
            );

        // Create container
        $this->configurator->createContainer();

        // Make assertions
        $this->assertTrue($deferred->isInvoked());
        $this->assertEquals('result', $deferred->getResult());
    }

    /* ---------------------------------- Parameters ------------------------------------------------ */

    /**
     * @depends testCreateContainer
     */
    public function testSetTimezone()
    {
        // Set timezone and verify returns
        $this->assertInstanceOf('FuzeWorks\Configurator', $this->configurator->setTimeZone('Europe/Amsterdam'));

        // Test if properly set
        $this->assertEquals('Europe/Amsterdam', ini_get('date.timezone'));
    }

    /**
     * @depends testSetTimezone
     * @expectedException \FuzeWorks\Exception\InvalidArgumentException
     */
    public function testSetTimezoneInvalid()
    {
        $this->configurator->setTimeZone('Europe/Amsterdamned');
    }

    /**
     * @depends testCreateContainer
     */
    public function testSetParameter()
    {
        // Set a value that can be verified and test return object
        $this->assertInstanceOf('FuzeWorks\Configurator', $this->configurator->setParameters(['tempDir' => 'fake_directory']));

        // Create container and verify
        $this->configurator->createContainer()->init();
        $this->assertEquals('fake_directory', Core::$tempDir);
    }

    public function testSetConfigOverride()
    {
        // Set an override that can be verified
        $this->configurator->setConfigOverride('test', 'somekey', 'somevalue');

        // Create container
        $this->configurator->createContainer()->init();

        // Verify that the variable is set in the Config class
        $this->assertEquals(['test' => ['somekey' => 'somevalue']], \FuzeWorks\Config::$configOverrides);
    }

    /* ---------------------------------- Debugging ------------------------------------------------- */

    /**
     * @depends testCreateContainer
     */
    public function testEnableDebugMode()
    {
        // Enable debug mode and verify return object
        $this->assertInstanceOf('FuzeWorks\Configurator', $this->configurator->enableDebugMode());

        // No match has been found yet. Verify that debug is still deactivated
        $this->assertFalse($this->configurator->isDebugMode());

        // Set a debug address, all in this case; also verify return type
        $this->assertInstanceOf('FuzeWorks\Configurator', $this->configurator->setDebugAddress('ALL'));

        // Match should be found. Verify that debug is activated
        $this->assertTrue($this->configurator->isDebugMode());

        // Load the container and verify that tracy runs in debug mode
        $this->configurator->createContainer()->init();
        $this->assertTrue(\Tracy\Debugger::isEnabled());
    }

    /**
     * @depends testEnableDebugMode
     */
    public function testDisableDebugMode()
    {
        // First enable so we can disable
        $this->assertFalse($this->configurator->enableDebugMode(false)->isDebugMode());

        // Create the container and verify that tracy debug has been disabled
        $this->configurator->createContainer()->init();

        // Tracy can't be disabled once it's been enabled. Therefor this won't be tested
    }

    /**
     * @depends testEnableDebugMode
     */
    public function testSetDebugAddress()
    {
        // First test return value
        $this->assertInstanceOf('FuzeWorks\Configurator', $this->configurator->setDebugAddress('ALL'));

        // Test address ALL and ENABLED
        $this->assertTrue($this->configurator->enableDebugMode()->setDebugAddress('ALL')->isDebugMode());

        // Test address NONE and ENABLED
        $this->assertFalse($this->configurator->enableDebugMode()->setDebugAddress('NONE')->isDebugMode());

        // Test custom addresses
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $this->assertTrue($this->configurator->enableDebugMode()->setDebugAddress([])->isDebugMode());
        $this->assertTrue($this->configurator->enableDebugMode()->setDebugAddress('192.168.1.1')->isDebugMode());

        $_SERVER['REMOTE_ADDR'] = '::1';
        $this->assertTrue($this->configurator->enableDebugMode()->setDebugAddress([])->isDebugMode());

        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';
        $this->assertFalse($this->configurator->enableDebugMode()->setDebugAddress([])->isDebugMode());
        $this->assertFalse($this->configurator->enableDebugMode()->setDebugAddress('192.168.1.1.0')->isDebugMode());
        $this->assertTrue($this->configurator->enableDebugMode()->setDebugAddress('192.168.1.1')->isDebugMode());
        $this->assertTrue($this->configurator->enableDebugMode()->setDebugAddress('a,192.168.1.1,b')->isDebugMode());
        $this->assertTrue($this->configurator->enableDebugMode()->setDebugAddress('a 192.168.1.1 b')->isDebugMode());

        // Test for HTTP_X_FORWARDED_FOR
        unset($_SERVER['HTTP_X_FORWARDED_FOR'], $_SERVER['REMOTE_ADDR']);
        $this->assertFalse($this->configurator->enableDebugMode()->setDebugAddress([])->isDebugMode());
        $this->assertFalse($this->configurator->enableDebugMode()->setDebugAddress('127.0.0.1')->isDebugMode());
        $this->assertTrue($this->configurator->enableDebugMode()->setDebugAddress(php_uname('n'))->isDebugMode());
        $this->assertTrue($this->configurator->enableDebugMode()->setDebugAddress([php_uname('n')])->isDebugMode());

        // Test for cookie based authentication
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';
        $_COOKIE[Configurator::COOKIE_SECRET] = '*secret*';
        $this->assertFalse($this->configurator->enableDebugMode()->setDebugAddress([])->isDebugMode());
        $this->assertTrue($this->configurator->enableDebugMode()->setDebugAddress('192.168.1.1')->isDebugMode());
        $this->assertFalse($this->configurator->enableDebugMode()->setDebugAddress('abc@192.168.1.1')->isDebugMode());
        $this->assertTrue($this->configurator->enableDebugMode()->setDebugAddress('*secret*@192.168.1.1')->isDebugMode());

        $_COOKIE[Configurator::COOKIE_SECRET] = ['*secret*'];
        $this->assertFalse($this->configurator->enableDebugMode()->setDebugAddress('*secret*@192.168.1.1')->isDebugMode());

        // Unset
        unset($_COOKIE[Configurator::COOKIE_SECRET], $_SERVER['REMOTE_ADDR']);
    }

    /**
     * @depends testEnableDebugMode
     * @expectedException \FuzeWorks\Exception\InvalidArgumentException
     */
    public function testSetDebugAddressInvalidArgument()
    {
        $this->configurator->setDebugAddress(null);
    }

    /**
     * @depends testEnableDebugMode
     */
    public function testSetDebugEmail()
    {
        // Set email and verify return value
        $this->assertInstanceOf('FuzeWorks\Configurator', $this->configurator->setDebugEmail('test@email.com'));

        // Create container and test Tracy for set address
        $this->configurator->createContainer()->init();
        $this->assertEquals('test@email.com', \Tracy\Debugger::$email);
    }
}

class MockComponent implements iComponent
{

    public function getClasses(): array
    {
    }

    public function onAddComponent(Configurator $configurator): Configurator
    {
        return $configurator;
    }

    public function onCreateContainer(Configurator $configurator): Configurator
    {
        return $configurator;
    }
}

class MockComponentClass
{
    //public function update(){}
}