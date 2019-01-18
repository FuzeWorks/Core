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

use FuzeWorks\EventPriority;
use FuzeWorks\Events;
use FuzeWorks\Helpers;

/**
 * Class HelperTest.
 *
 * Helpers testing suite, will test basic loading of Helpers
 */
class helperTest extends CoreTestAbstract
{

    /**
     * @var Helpers
     */
	protected $helpers;

	public function setUp()
	{
		// Prepare class
	    $this->helpers = new Helpers();
		$this->helpers->setDirectories(['test' . DS . 'helpers']);
	}

    public function testGetHelpersClass()
    {
        $this->assertInstanceOf('FuzeWorks\Helpers', $this->helpers);
    }

    /**
     * @covers \FuzeWorks\Helpers::load
     */
    public function testLoadHelper()
    {
    	// First test if the function/helper is not loaded yet
    	$this->assertFalse(function_exists('testHelperFunction'));

    	// Test if the helper is properly loaded
    	$this->assertTrue($this->helpers->load('TestLoadHelper'));

    	// Test if the function exists now
    	$this->assertTrue(function_exists('testHelperFunction'));
    }

    /**
     * @depends testLoadHelper
     * @covers \FuzeWorks\Helpers::load
     */
    public function testLoadHelperWithoutSubdirectory()
    {
        // First test if the function/helper is not loaded yet
        $this->assertFalse(function_exists('testLoadHelperWithoutSubdirectory'));

        // Try and load the helper
        $this->assertTrue($this->helpers->load('TestLoadHelperWithoutSubdirectory'));

        // Then test if the function/helper is loaded
        $this->assertTrue(function_exists('testLoadHelperWithoutSubdirectory'));
    }

    /**
     * @depends testLoadHelper
     * @covers \FuzeWorks\Helpers::load
     */
    public function testReloadHelper()
    {
        // First test if the function/helper is not loaded yet
        $this->assertFalse(function_exists('testReloadHelper'));

        // Try and load the helper
        $this->assertTrue($this->helpers->load('TestReloadHelper'));

        // Then test if the function/helper is loaded
        $this->assertTrue(function_exists('testReloadHelper'));

        // Try and reload the helper
        $this->assertFalse($this->helpers->load('TestReloadHelper'));

        // Test that the function still exists
        $this->assertTrue(function_exists('testReloadHelper'));
    }

    /**
     * @depends testLoadHelper
     * @covers \FuzeWorks\Helpers::load
     */
    public function testCancelLoadHelper()
    {
        // First test if the function/helper is not loaded yet
        $this->assertFalse(function_exists('testCancelLoadHelper'));

        // Prepare listener
        Events::addListener(function($event) {
            $event->setCancelled(true);

        }, 'helperLoadEvent', EventPriority::NORMAL);

        $this->assertFalse($this->helpers->load('TestCancelLoadHelper'));
    }

    /**
     * @depends testLoadHelper
     * @covers \FuzeWorks\Helpers::get
     */
    public function testGetHelper()
    {
        // First test if the function/helper is not loaded yet
        $this->assertFalse(function_exists('testGetHelper'));

        // Test if the helper is properly loaded
        $this->assertTrue($this->helpers->get('TestGetHelper'));

        // Test if the function exists now
        $this->assertTrue(function_exists('testGetHelper'));
    }

    /**
     * @expectedException FuzeWorks\Exception\HelperException
     * @covers \FuzeWorks\Helpers::load
     */
    public function testAddComponentPathFail()
    {
    	// First test if the function is not loaded yet
    	$this->assertFalse(function_exists('testAddComponentPathFunction'));

    	// Now test if the helper can be loaded (hint: it can not)
    	$this->helpers->load('TestAddComponentPathFail');
    }

    /**
     * @depends testAddComponentPathFail
     * @covers \FuzeWorks\Helpers::addComponentPath
     * @covers \FuzeWorks\Helpers::getComponentPaths
     */
    public function testAddComponentPath()
    {
    	// Add the componentPath
    	$this->helpers->addComponentPath('test'.DS.'helpers'.DS.'TestAddComponentPath');

    	// And try to load it again
    	$this->assertTrue($this->helpers->load('TestAddComponentPath'));

    	// And test if the function is loaded
    	$this->assertTrue(function_exists('testAddComponentPathFunction'));
    }

    /**
     * @covers \FuzeWorks\Helpers::removeComponentPath
     * @covers \FuzeWorks\Helpers::getComponentPaths
     */
    public function testRemoveComponentPath()
    {
    	// Test if the path does NOT exist
    	$this->assertFalse(in_array('test'.DS.'helpers'.DS.'TestRemoveComponentPath', $this->helpers->getComponentPaths()));

    	// Add it
    	$this->helpers->addComponentPath('test'.DS.'helpers'.DS.'TestRemoveComponentPath');

    	// Assert if it's there
    	$this->assertTrue(in_array('test'.DS.'helpers'.DS.'TestRemoveComponentPath', $this->helpers->getComponentPaths()));

    	// Remove it
    	$this->helpers->removeComponentPath('test'.DS.'helpers'.DS.'TestRemoveComponentPath');

    	// And test if it's gone again
    	$this->assertFalse(in_array('test'.DS.'helpers'.DS.'TestRemoveComponentPath', $this->helpers->getComponentPaths()));
    }

    /**
     * @covers \FuzeWorks\Helpers::setDirectories
     * @covers \FuzeWorks\Helpers::getComponentPaths
     */
    public function testSetDirectories()
    {
        // Add the directory
        $directory = 'test' . DS . 'helpers';
        $this->helpers->setDirectories([$directory]);

        // Assert expectations
        $expected = array_merge(\FuzeWorks\Core::$appDirs, ['test' . DS . 'helpers', $directory]);
        $this->assertEquals($expected, $this->helpers->getComponentPaths());
    }
}
