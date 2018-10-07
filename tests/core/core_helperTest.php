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
		$factory = Factory::getInstance();
		$this->helpers = $factory->helpers;
	}

    public function testGetHelpersClass()
    {
        $this->assertInstanceOf('FuzeWorks\Helpers', $this->helpers);
    }

    public function testLoadHelper()
    {
    	// First test if the function/helper is not loaded yet
    	$this->assertFalse(function_exists('testHelperFunction'));

    	// Test if the helper is properly loaded
    	$this->assertTrue($this->helpers->load('test', 'tests'.DS.'helpers'.DS.'testLoadHelper'.DS));

    	// Test if the function exists now
    	$this->assertTrue(function_exists('testHelperFunction'));
    }

    /**
     * @expectedException FuzeWorks\Exception\HelperException
     */
    public function testAddHelperPathFail()
    {
    	// First test if the function is not loaded yet
    	$this->assertFalse(function_exists('testAddHelperPathFunction'));

    	// Now test if the helper can be loaded (hint: it can not)
    	$this->helpers->load('testAddHelperPath');
    }

    /**
     * @depends testAddHelperPathFail
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

    public function testSetDirectories()
    {
        // Add the directory
        $directory = 'tests' . DS . 'helpers';
        $this->helpers->setDirectories([$directory]);

        $this->assertEquals([$directory], $this->helpers->getHelperPaths());
    }
}
