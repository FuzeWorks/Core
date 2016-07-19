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
 * @link        http://fuzeworks.techfuze.net
 * @since       Version 0.0.1
 *
 * @version     Version 0.0.1
 */

use FuzeWorks\Models;
use FuzeWorks\Events;
use FuzeWorks\EventPriority;

/**
 * Class ModelTest.
 *
 * Will test the FuzeWorks Model System.
 */
class modelTest extends CoreTestAbstract
{
    protected $models;

    public function setUp()
    {
        $this->models = new Models();
    }

    public function testGetModel()
    {
        $model = $this->models->get('TestGetModel', 'tests/models/testGetModel');
        $this->assertInstanceOf('\Application\Model\TestGetModel', $model);
    }

    /**
     * @depends testGetModel
     */
    public function testReloadModel()
    {
        $model = $this->models->get('TestGetModel', 'tests/models/testGetModel');
        $this->assertInstanceOf('\Application\Model\TestGetModel', $model);
    }

    /**
     * @expectedException FuzeWorks\Exception\ModelException
     */
    public function testFailModelName()
    {
        $model = $this->models->get('');
    }

    public function testEventLoadModelChange()
    {
        // Register the listener
        Events::addListener(array($this, 'listener_change'), 'modelLoadEvent', EventPriority::NORMAL);

        // Load wrong model
        $model = $this->models->get('TestWrongModel', 'tests/models/testWrongDirectory');
        $this->assertInstanceOf('\Application\Model\TestRightModel', $model);
    }

    // Change the directory and model name
    public function listener_change($event)
    {
        // First test input
        $this->assertEquals('TestWrongModel', $event->modelName);
        $this->assertContains('tests/models/testWrongDirectory', $event->directories);

        // Then change variables
        $event->modelName = 'TestRightModel';
        $event->directories = array('tests/models/testRightDirectory');

        // Return the event afterwards
        return $event;
    }

    public function testEventLoadModelCancel()
    {
        // Register the listener
        Events::addListener(array($this, 'listener_cancel'), 'modelLoadEvent', EventPriority::NORMAL);

        $this->assertFalse($this->models->get('TestModelCancel'));
    }

    // Cancel the event
    public function listener_cancel($event)
    {
        $event->setCancelled(true);
        return $event;
    }

    /**
     * @expectedException FuzeWorks\Exception\ModelException
     */
    public function testNoDirectories()
    {
        // Register the listener
        Events::addListener(array($this, 'listener_nodirectories'), 'modelLoadEvent', EventPriority::NORMAL);

        $this->models->get('testNoDirectories');
    }

    // Clean the directories array
    public function listener_nodirectories($event)
    {
        $event->directories = array();
        return $event;
    }

    /**
     * @expectedException FuzeWorks\Exception\ModelException
     */
    public function testAddModelPathFail()
    {
        // First test if the model is not loaded yet
        $this->assertFalse(class_exists('TestAddModelPath', false));

        // Now test if the model can be loaded (hint: it can not)
        $this->models->get('TestAddModelPathFail');
    }

    /**
     * @depends testAddModelPathFail
     */
    public function testAddModelPath()
    {
        // Add the modelPath
        $this->models->addModelPath('tests/models/testAddModelPath');

        // And try to load it again
        $this->assertInstanceOf('Application\Model\TestAddModelPath', $this->models->get('TestAddModelPath'));
    }

    public function testRemoveModelPath()
    {
        // Test if the path does NOT exist
        $this->assertFalse(in_array('tests/models/testRemoveModelPath', $this->models->getModelPaths()));

        // Add it
        $this->models->addModelPath('tests/models/testRemoveModelPath');

        // Assert if it's there
        $this->assertTrue(in_array('tests/models/testRemoveModelPath', $this->models->getModelPaths()));

        // Remove it
        $this->models->removeModelPath('tests/models/testRemoveModelPath');

        // And test if it's gone again
        $this->assertFalse(in_array('tests/models/testRemoveModelPath', $this->models->getModelPaths()));
    }
}
