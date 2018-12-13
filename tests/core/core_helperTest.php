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
		$this->helpers->setDirectories(['tests' . DS . 'helpers']);
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
    	$this->assertTrue($this->helpers->load('testLoadHelper'));

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
        $this->assertTrue($this->helpers->load('testLoadHelperWithoutSubdirectory'));

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
        $this->assertTrue($this->helpers->load('testReloadHelper'));

        // Then test if the function/helper is loaded
        $this->assertTrue(function_exists('testReloadHelper'));

        // Try and reload the helper
        $this->assertFalse($this->helpers->load('testReloadHelper'));

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

        $this->assertFalse($this->helpers->load('testCancelLoadHelper'));
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
        $this->assertTrue($this->helpers->get('testGetHelper'));

        // Test if the function exists now
        $this->assertTrue(function_exists('testGetHelper'));
    }

    /**
     * @expectedException FuzeWorks\Exception\HelperException
     * @covers \FuzeWorks\Helpers::load
     */
    public function testAddHelperPathFail()
    {
    	// First test if the function is not loaded yet
    	$this->assertFalse(function_exists('testAddHelperPathFunction'));

    	// Now test if the helper can be loaded (hint: it can not)
    	$this->helpers->load('testAddHelperPathFail');
    }

    /**
     * @depends testAddHelperPathFail
     * @covers \FuzeWorks\Helpers::addHelperPath
     * @covers \FuzeWorks\Helpers::getHelperPaths
     */
    public function testAddHelperPath()
    {
    	// Add the helperPath
    	$this->helpers->addHelperPath('tests'.DS.'helpers'.DS.'testAddHelperPath');

    	// And try to load it again
    	$this->assertTrue($this->helpers->load('testAddHelperPath'));

    	// And test if the function is loaded
    	$this->assertTrue(function_exists('testAddHelperPathFunction'));
    }

    /**
     * @covers \FuzeWorks\Helpers::removeHelperPath
     * @covers \FuzeWorks\Helpers::getHelperPaths
     */
    public function testRemoveHelperPath()
    {
    	// Test if the path does NOT exist
    	$this->assertFalse(in_array('tests'.DS.'helpers'.DS.'testRemoveHelperPath', $this->helpers->getHelperPaths()));

    	// Add it
    	$this->helpers->addHelperPath('tests'.DS.'helpers'.DS.'testRemoveHelperPath');

    	// Assert if it's there
    	$this->assertTrue(in_array('tests'.DS.'helpers'.DS.'testRemoveHelperPath', $this->helpers->getHelperPaths()));

    	// Remove it
    	$this->helpers->removeHelperPath('tests'.DS.'helpers'.DS.'testRemoveHelperPath');

    	// And test if it's gone again
    	$this->assertFalse(in_array('tests'.DS.'helpers'.DS.'testRemoveHelperPath', $this->helpers->getHelperPaths()));
    }

    /**
     * @covers \FuzeWorks\Helpers::setDirectories
     * @covers \FuzeWorks\Helpers::getHelperPaths
     */
    public function testSetDirectories()
    {
        // Add the directory
        $directory = 'tests' . DS . 'helpers';
        $this->helpers->setDirectories([$directory]);

        // Assert expectations
        $expected = array_merge(\FuzeWorks\Core::$appDirs, ['tests' . DS . 'helpers', $directory]);
        $this->assertEquals($expected, $this->helpers->getHelperPaths());
    }
}
