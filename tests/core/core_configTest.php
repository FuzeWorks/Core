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

use FuzeWorks\Config;

/**
 * Class ConfigTest.
 *
 * Config testing suite, will test basic config functionality while also testing default ORM's
 */
class configTest extends CoreTestAbstract
{

    /**
     * @var Config
     */
	protected $config;

	public function setUp()
	{
		$this->config = new Config();
	}

	public function testGetConfigClass()
	{
		$this->assertInstanceOf('FuzeWorks\Config', $this->config);
	}

	/**
	 * @depends testGetConfigClass
	 */
	public function testLoadConfig()
	{
		$this->assertInstanceOf('FuzeWorks\ConfigORM\ConfigORM', $this->config->getConfig('main'));
	}

	/**
	 * @depends testLoadConfig
	 * @expectedException FuzeWorks\Exception\ConfigException
	 */
	public function testFileNotFound()
	{
		$this->config->getConfig('notFound');
	}

    /**
     * @expectedException FuzeWorks\Exception\ConfigException
     */
    public function testAddConfigPathFail()
    {
    	// Now test if the config can be loaded (hint: it can not)
    	$this->config->getConfig('testAddConfigPath');
    }

    /**
     * @depends testAddConfigPathFail
     */
    public function testAddConfigPath()
    {
    	// Add the configPath
    	$this->config->addConfigPath('tests'.DS.'config'.DS.'testAddConfigPath');

    	// And try to load it again
    	$this->assertInstanceOf('FuzeWorks\ConfigORM\ConfigORM', $this->config->getConfig('testAddConfigPath'));
    }

    public function testRemoveConfigPath()
    {
    	// Test if the path does NOT exist
    	$this->assertFalse(in_array('tests'.DS.'config'.DS.'testRemoveConfigPath', $this->config->getConfigPaths()));

    	// Add it
    	$this->config->addConfigPath('tests'.DS.'config'.DS.'testRemoveConfigPath');

    	// Assert if it's there
    	$this->assertTrue(in_array('tests'.DS.'config'.DS.'testRemoveConfigPath', $this->config->getConfigPaths()));

    	// Remove it
    	$this->config->removeConfigPath('tests'.DS.'config'.DS.'testRemoveConfigPath');

    	// And test if it's gone again
    	$this->assertFalse(in_array('tests'.DS.'config'.DS.'testRemoveConfigPath', $this->config->getConfigPaths()));
    }

    public function testSameConfigObject()
    {
        $config = $this->config->getConfig('testsameconfigobject', array('tests'.DS.'config'.DS.'testSameConfigObject'));
        $config2 = $this->config->getConfig('testsameconfigobject', array('tests'.DS.'config'.DS.'testSameConfigObject'));

        // First test if the objects are the same instance
        $this->assertSame($config, $config2);

        // First test the existing key
        $this->assertEquals($config->key, 'value');

        // Change it and test if it's different now
        $config->key = 'other_value';
        $this->assertEquals($config2->key, 'other_value');
    }

    public function testSetDirectories()
    {
        // Add the directory
        $directory = 'tests' . DS . 'config';
        $this->config->setDirectories([$directory]);

        $this->assertEquals([$directory], $this->config->getConfigPaths());
    }

}
