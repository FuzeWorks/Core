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
 * @since Version 1.1.4
 *
 * @version Version 1.2.0
 */

use FuzeWorks\Core;
use FuzeWorks\Factory;
use FuzeWorks\Plugins;

/**
 * Class PluginsTest.
 *
 * Plugins testing suite, will test basic loading of and management of Plugins
 * @coversDefaultClass \FuzeWorks\Plugins
 */
class pluginTest extends CoreTestAbstract
{

    /**
     * @var FuzeWorks\Plugins
     */
    protected $plugins;

    public function setUp()
    {
        $this->plugins = new Plugins();
        $this->plugins->addComponentPath('test'.DS.'plugins');
        $this->plugins->loadHeadersFromPluginPaths();
    }

    /**
     * @coversNothing
     */
    public function testGetPluginsClass()
    {
        $this->assertInstanceOf('FuzeWorks\Plugins', $this->plugins);
    }

    /**
     * @depends testGetPluginsClass
     * @covers ::get
     */
    public function testLoadPlugin()
    {
        $this->assertInstanceOf('\Application\Plugin\TestLoadPlugin', $this->plugins->get('testLoadPlugin'));
    }

    /**
     * @depends testLoadPlugin
     * @covers ::get
     */
    public function testReloadPlugin()
    {
        $this->assertSame($this->plugins->get('testReloadPlugin'), $this->plugins->get('testReloadPlugin'));
    }

    /**
     * @depends testLoadPlugin
     * @covers ::get
     */
    public function testLoadHeader()
    {
        // Load the header object
        require_once('test' . DS . 'plugins' . DS . 'TestLoadHeader'.DS.'loadHeader'.DS.'header.php');
        $header = new Plugins\TestLoadHeaderHeader();

        // Register the header object
        $this->plugins->addPlugin($header);

        // Assert the plugin
        $this->assertInstanceOf('Application\Plugin\Plug', $this->plugins->get('Plug'));
    }

    /**
     * @depends testLoadPlugin
     * @covers ::get
     * @expectedException FuzeWorks\Exception\PluginException
     */
    public function testMissingHeader()
    {
        $this->plugins->get('testMissingHeader');
    }

    /**
     * @depends testLoadPlugin
     * @covers ::get
     */
    public function testGetPluginMethod()
    {
        $this->assertEquals('test_string', $this->plugins->get('testGetPluginMethod'));
    }

    /**
     * @depends testLoadPlugin
     * @covers ::get
     */
    public function testGetPluginWithClassFile()
    {
        $this->assertInstanceOf('OtherPlug', $this->plugins->get('TestGetPluginWithClassFile'));
    }

    /**
     * @depends testLoadPlugin
     * @covers ::get
     * @expectedException FuzeWorks\Exception\PluginException
     */
    public function testMissingPlugin()
    {
        $this->plugins->get('testMissingPlugin');
    }

    /**
     * @depends testMissingPlugin
     * @covers ::get
     * @expectedException FuzeWorks\Exception\PluginException
     */
    public function testLoadHeaderNotIPluginHeader()
    {
        $this->plugins->get('TestLoadHeaderNotIPluginHeader');
    }

    /**
     * @depends testLoadPlugin
     * @covers ::get
     * @expectedException FuzeWorks\Exception\PluginException
     */
    public function testInvalidClass()
    {
        $this->plugins->get('testInvalidClass');
    }

    /**
     * @covers ::get
     * @expectedException FuzeWorks\Exception\PluginException
     */
    public function testGetMissingName()
    {
        $this->plugins->get('');
    }

    /**
     * @depends testLoadPlugin
     * @covers ::get
     * @covers ::loadHeadersFromPluginPaths
     * @expectedException FuzeWorks\Exception\PluginException
     */
    public function testDisabledPlugin()
    {
        Factory::getInstance()->config->plugins->disabled_plugins = array('TestDisabledPlugin');
        $this->plugins->loadHeadersFromPluginPaths();
        $this->plugins->get('testDisabledPlugin');
    }

    /**
     * @depends testLoadPlugin
     * @covers ::get
     * @covers ::loadHeadersFromPluginPaths
     * @expectedException FuzeWorks\Exception\PluginException
     */
    public function testRunInvalidDirectory()
    {
        $this->plugins->addComponentPath('exists_not');
        $this->plugins->loadHeadersFromPluginPaths();
        $this->plugins->get('testRunInvalidDirectory');
    }

    public function tearDown()
    {
        $factory = Factory::getInstance();
        $factory->config->plugins->revert();
    }

}
