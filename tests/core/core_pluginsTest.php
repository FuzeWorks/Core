<?php
/**
 * FuzeWorks.
 *
 * The FuzeWorks MVC PHP FrameWork
 *
 * Copyright (C) 2018   TechFuze
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
 * @copyright   Copyright (c) 2013 - 2018, Techfuze. (http://techfuze.net)
 * @copyright   Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license     http://opensource.org/licenses/GPL-3.0 GPLv3 License
 *
 * @link        http://techfuze.net/fuzeworks
 * @since       Version 1.1.4
 *
 * @version     Version 1.1.4
 */

use FuzeWorks\Factory;
use FuzeWorks\Plugins;

/**
 * Class PluginsTest.
 *
 * Plugins testing suite, will test basic loading of and management of Plugins
 */
class pluginTest extends CoreTestAbstract
{

    protected $plugins;

    public function setUp()
    {
        $this->plugins = new Plugins();
        $this->plugins->addPluginPath('tests'.DS.'plugins');
        $this->plugins->loadHeaders();
    }

    public function testGetPluginsClass()
    {
        $this->assertInstanceOf('FuzeWorks\Plugins', $this->plugins);
    }

    /**
     * @depends testGetPluginsClass
     */
    public function testLoadPlugin()
    {
        $this->assertInstanceOf('\Application\Plugin\TestLoadPlugin', $this->plugins->get('testLoadPlugin'));
    }

    /**
     * @depends testLoadPlugin
     */
    public function testReloadPlugin()
    {
        $this->assertSame($this->plugins->get('testReloadPlugin'), $this->plugins->get('testReloadPlugin'));
    }

    /**
     * @depends testLoadPlugin
     * @expectedException FuzeWorks\Exception\PluginException
     */
    public function testMissingHeader()
    {
        $this->plugins->get('testMissingHeader');
    }

    /**
     * @depends testLoadPlugin
     */
    public function testGetPluginMethod()
    {
        $this->assertEquals('test_string', $this->plugins->get('testGetPluginMethod'));
    }

    /**
     * @depends testLoadPlugin
     * @expectedException FuzeWorks\Exception\PluginException
     */
    public function testMissingPlugin()
    {
        $this->plugins->get('testMissingPlugin');
    }

    /**
     * @depends testLoadPlugin
     * @expectedException FuzeWorks\Exception\PluginException
     */
    public function testInvalidClass()
    {
        $this->plugins->get('testInvalidClass');
    }

    /**
     * @expectedException FuzeWorks\Exception\PluginException
     */
    public function testGetMissingName()
    {
        $this->plugins->get('');
    }

    /**
     * @depends testLoadPlugin
     * @expectedException FuzeWorks\Exception\PluginException
     */
    public function testDisabledPlugin()
    {
        Factory::getInstance()->config->plugins->disabled_plugins = array('TestDisabledPlugin');
        $this->plugins->loadHeaders();
        $this->plugins->get('testDisabledPlugin');
    }

    /**
     * @depends testLoadPlugin
     * @expectedException FuzeWorks\Exception\PluginException
     */
    public function testRunInvalidDirectory()
    {
        $this->plugins->addPluginPath('exists_not');
        $this->plugins->loadHeaders();
        $this->plugins->get('testRunInvalidDirectory');
    }

    public function testAddPluginPath()
    {
        // Add the pluginPath
        $this->plugins->addPluginPath('tests'.DS.'plugins'.DS.'testAddPluginPath');

        // And try to load it again
        $this->plugins->loadHeaders();
        $this->assertInstanceOf('Application\Plugin\ActualPlugin', $this->plugins->get('actualPlugin'));
    }

    /**
     * @depends testAddPluginPath
     */
    public function testRemovePluginPath()
    {
        // Test if the path does NOT exist
        $this->assertFalse(in_array('tests'.DS.'plugins'.DS.'testRemovePluginPath', $this->plugins->getPluginPaths()));

        // Add it
        $this->plugins->addPluginPath('tests'.DS.'plugins'.DS.'testRemovePluginPath');

        // Assert if it's there
        $this->assertTrue(in_array('tests'.DS.'plugins'.DS.'testRemovePluginPath', $this->plugins->getPluginPaths()));

        // Remove it
        $this->plugins->removePluginPath('tests'.DS.'plugins'.DS.'testRemovePluginPath');

        // And test if it's gone again
        $this->assertFalse(in_array('tests'.DS.'plugins'.DS.'testRemovePluginPath', $this->plugins->getPluginPaths()));
    }

    public function tearDown()
    {
        $factory = Factory::getInstance();
        $factory->config->plugins->revert();
    }

}
