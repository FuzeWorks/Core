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
use FuzeWorks\Layout;
use FuzeWorks\Factory;

/**
 * Class LayoutTest.
 *
 * This test will test the layout manager and the default TemplateEngines
 */
class layoutTest extends CoreTestAbstract
{

    protected $factory;

    public function setUp()
    {
        // Load the factory first
        $this->factory = Factory::getInstance();
    }

    public function testGetFileExtensions()
    {
        // Test getting php files
        $this->assertEquals('php', $this->factory->layout->getExtensionFromFile('class.test.php'));
        $this->assertEquals('php', $this->factory->layout->getExtensionFromFile('class.test.org.php'));
        $this->assertEquals('random', $this->factory->layout->getExtensionFromFile('class.test.something.random'));
    }

    /**
     * @depends testGetFileExtensions
     */
    public function testGetFilePath()
    {
        // Extensions to be used in this test
        $extensions = array('php', 'json');

        // Basic path
        $this->factory->layout->setFileFromString('test', 'tests/layout/testGetFilePath/', $extensions);
        $this->assertEquals('tests/layout/testGetFilePath/view.test.php', $this->factory->layout->getFile());
        $this->assertEquals('tests/layout/testGetFilePath/', $this->factory->layout->getDirectory());

        // Alternate file extension
        $this->factory->layout->setFileFromString('JSON', 'tests/layout/testGetFilePath/', $extensions);
        $this->assertEquals('tests/layout/testGetFilePath/view.JSON.json', $this->factory->layout->getFile());
        $this->assertEquals('tests/layout/testGetFilePath/', $this->factory->layout->getDirectory());

        // Complex deeper path
        $this->factory->layout->setFileFromString('Deeper/test', 'tests/layout/testGetFilePath/', $extensions);
        $this->assertEquals('tests/layout/testGetFilePath/Deeper/view.test.php', $this->factory->layout->getFile());
        $this->assertEquals('tests/layout/testGetFilePath/', $this->factory->layout->getDirectory());
    }

    /**
     * @depends testGetFilePath
     * @expectedException FuzeWorks\Exception\LayoutException
     */
    public function testMalformedPaths()
    {
        // Extensions to be used in this test
        $extensions = array('php', 'json');

        $this->factory->layout->setFileFromString('test?\/<>', 'test|?/*<>', $extensions);
    }

    /**
     * @expectedException FuzeWorks\Exception\LayoutException
     */
    public function testMissingDirectory()
    {
        // Directory that does not exist
        $this->factory->layout->setFileFromString('test', 'tests/layout/doesNotExist/', array('php'));
    }

    /**
     * @expectedException FuzeWorks\Exception\LayoutException
     */
    public function testMissingFile()
    {
        $this->factory->layout->setFileFromString('test', 'tests/layout/testMissingFile/', array('php'));
    }

    /**
     * @expectedException FuzeWorks\Exception\LayoutException
     */
    public function testUnknownFileExtension()
    {
        $this->factory->layout->setFileFromString('test', 'tests/layout/testUnknownFileExtension/', array('php'));
    }

    public function testLayoutGet()
    {
        // Directory of these tests
        $directory = 'tests/layout/testLayoutGet/';

        $this->assertEquals('Retrieved Data', $this->factory->layout->get('test', $directory));
    }

    public function testLayoutView()
    {
        // Directory of these tests
        $directory = 'tests/layout/testLayoutGet/';

        ob_start();
        $this->factory->layout->view('test', $directory);
        Factory::getInstance()->output->_display();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('Retrieved Data', $output);
    }

    public function testReset()
    {
        // First the the variables
        $this->factory->layout->setTitle('Test Title');
        $this->factory->layout->setDirectory('tests/layout/testLayoutGet');

        // Test if they are actually set
        $this->assertEquals('Test Title', $this->factory->layout->getTitle());
        $this->assertEquals('tests/layout/testLayoutGet', $this->factory->layout->getDirectory());

        // Reset the layout system
        $this->factory->layout->reset();

        // Test for default values
        $this->assertFalse($this->factory->layout->getTitle());
        $this->assertTrue(strpos($this->factory->layout->getDirectory(), 'application/Views') !== false);
    }

    public function testGetEngineFromExtension()
    {
        $this->factory->layout->loadTemplateEngines();

        // Test all the default engines
        $this->assertInstanceOf('FuzeWorks\TemplateEngine\PHPEngine', $this->factory->layout->getEngineFromExtension('php'));
        $this->assertInstanceOf('FuzeWorks\TemplateEngine\JsonEngine', $this->factory->layout->getEngineFromExtension('json'));
        $this->assertInstanceOf('FuzeWorks\TemplateEngine\SmartyEngine', $this->factory->layout->getEngineFromExtension('tpl'));
    }

    /**
     * @depends testGetEngineFromExtension
     * @expectedException FuzeWorks\Exception\LayoutException
     */
    public function testGetEngineFromExtensionFail()
    {
        $this->factory->layout->getEngineFromExtension('faulty');
    }

    /**
     * @depends testGetEngineFromExtension
     */
    public function testCustomEngine()
    {
        // Create the engine
        $mock = $this->getMockBuilder('FuzeWorks\TemplateEngine\TemplateEngine')->getMock();

        // Add the methods
        $mock->method('get')->willReturn('output');

        // And listen for usage
        $mock->expects($this->once())->method('get')->with('tests/layout/testCustomEngine/view.test.test');

        // Register the engine
        $this->factory->layout->registerEngine($mock, 'Custom', array('test'));

        // And run the engine
        $this->assertEquals('output', $this->factory->layout->get('test', 'tests/layout/testCustomEngine/'));
    }

    /**
     * @depends testCustomEngine
     * @expectedException FuzeWorks\Exception\LayoutException
     */
    public function testInvalidCustomEngine()
    {
        $mock = $this->getMockBuilder(MockEngine::class)->getMock();

        // Does not implement FuzeWorks\TemplateEngine\TemplateEngine, this should fail
        $this->factory->layout->registerEngine($mock, 'Custom', array('test'));
    }

    public function testEnginesLoadView()
    {
        // Directory of these tests
        $directory = 'tests/layout/testEngines/'; 
        
        // First the PHP Engine
        $this->assertEquals('PHP Template Check', $this->factory->layout->get('php', $directory));
        $this->factory->layout->reset();

        // Then the JSON Engine
        $this->assertEquals('JSON Template Check', json_decode($this->factory->layout->get('json', $directory), true)[0]);
        $this->factory->layout->reset();

        // And the Smarty Engine
        $this->assertEquals('Smarty Template Check', $this->factory->layout->get('smarty', $directory));
    }

    public function testEngineVariables()
    {
        // Directory of these tests
        $directory = 'tests/layout/testEngineVariables/'; 
        
        // First the PHP Engine
        $this->factory->layout->assign('key', 'value');
        $this->assertEquals('value', $this->factory->layout->get('php', $directory));
        $this->factory->layout->reset();

        // Then the JSON Engine
        $this->factory->layout->assign('key', 'value');
        $this->assertEquals('value', json_decode($this->factory->layout->get('json', $directory), true)['data']['key']);
        $this->factory->layout->reset();

        // And the Smarty Engine
        $this->factory->layout->assign('key', 'value');
        $this->assertEquals('value', $this->factory->layout->get('smarty', $directory));
    }
}

class MockEngine {

}
