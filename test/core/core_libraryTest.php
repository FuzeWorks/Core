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

use FuzeWorks\Core;
use FuzeWorks\Factory;
use FuzeWorks\Libraries;

/**
 * Class LibraryTest.
 *
 * Libraries testing suite, will test basic loading of and management of Libraries
 */
class libraryTest extends CoreTestAbstract
{

    /**
     * @var Libraries
     */
    protected $libraries;

    public function setUp()
    {
        // Load new libraries class
        $this->libraries = new Libraries();

        // And then set all paths
        $this->libraries->setDirectories(['test'.DS.'libraries']);
    }

    public function testLibrariesClass()
    {
        $this->assertInstanceOf('FuzeWorks\Libraries', $this->libraries);
    }

    /* ---------------------------------- ComponentPaths ---------------------------------------------- */

    /**
     * @depends testLibrariesClass
     */
    public function testSetDirectories()
    {
        // Test initial
        $initial = array_merge(Core::$appDirs, ['test'.DS.'libraries']);
        $this->assertEquals($initial, $this->libraries->getComponentPaths());

        // Add path
        $newPath = 'addPath';
        $this->libraries->setDirectories([$newPath]);
        $initial[] = $newPath;
        $this->assertEquals($initial, $this->libraries->getComponentPaths());
    }

    /**
     * @expectedException FuzeWorks\Exception\LibraryException
     */
    public function testAddComponentPathFail()
    {
        // First test if the library is not loaded yet
        $this->assertFalse(class_exists('TestAddComponentPathFail', false));

        // Now test if the library can be loaded (hint: it can not)
        $this->libraries->get('TestAddComponentPathFail');
    }

    /**
     * @depends testAddComponentPathFail
     */
    public function testAddComponentPath()
    {
        // Add the componentPath
        $this->libraries->removeComponentPath('test'.DS.'libraries');
        $this->libraries->addComponentPath('test'.DS.'libraries'.DS.'TestAddComponentPath');

        // And try to load it again
        $this->assertInstanceOf('Application\Library\TestAddComponentPath', $this->libraries->get('TestAddComponentPath'));
    }

    public function testRemoveComponentPath()
    {
        // Test if the path does NOT exist
        $this->assertFalse(in_array('test'.DS.'libraries'.DS.'TestRemoveComponentPath', $this->libraries->getComponentPaths()));

        // Add it
        $this->libraries->addComponentPath('test'.DS.'libraries'.DS.'TestRemoveComponentPath');

        // Assert if it's there
        $this->assertTrue(in_array('test'.DS.'libraries'.DS.'TestRemoveComponentPath', $this->libraries->getComponentPaths()));

        // Remove it
        $this->libraries->removeComponentPath('test'.DS.'libraries'.DS.'TestRemoveComponentPath');

        // And test if it's gone again
        $this->assertFalse(in_array('test'.DS.'libraries'.DS.'TestRemoveComponentPath', $this->libraries->getComponentPaths()));
    }

    /* ---------------------------------- Load library from directories ------------------- */

    /**
     * @depends testLibrariesClass
     */
    public function testGetLibraryFromDirectory()
    {
        $this->assertInstanceOf('Application\Library\TestGetLibraryFromDirectory', $this->libraries->get('TestGetLibraryFromDirectory'));
    }

    /**
     * @depends testGetLibraryFromDirectory
     */
    public function testGetLibraryFromSubdirectory()
    {
        // Add test directory path
        $this->libraries->addComponentPath('test'.DS.'libraries'.DS.'TestGetLibraryFromSubdirectory');

        $this->assertInstanceOf('Application\Library\TestGetLibraryFromSubdirectory', $this->libraries->get('TestGetLibraryFromSubdirectory'));
    }

    /**
     * @depends testGetLibraryFromDirectory
     */
    public function testGetLibraryFromAltDirectory()
    {
        // Simple test of loading a library and checking if it exists
        $this->assertInstanceOf('Application\Library\TestGetLibraryFromAltDirectory',
            $this->libraries->get('TestGetLibraryFromAltDirectory', [], ['test'.DS.'libraries'.DS.'TestGetLibraryFromAltDirectory']));
    }

    /**
     * @expectedException FuzeWorks\Exception\LibraryException
     */
    public function testGetLibraryFail()
    {
        $this->libraries->get('FailLoadLibrary');
    }

    /**
     * @expectedException FuzeWorks\Exception\LibraryException
     */
    public function testGetLibraryNoName()
    {
        $this->libraries->get('');
    }

    /**
     * @expectedException FuzeWorks\Exception\LibraryException
     */
    public function testGetLibraryNoClass()
    {
        $this->libraries->get('TestGetLibraryNoClass');
    }

    public function testGetLibraryParametersFromConfig()
    {
        // Prepare the config file
        $libraryName = 'TestGetLibraryParametersFromConfig';
        $libraryDir = 'test'.DS.'libraries'.DS.'TestGetLibraryParametersFromConfig';
        $config = Factory::getInstance()->config->getConfig(strtolower($libraryName), [$libraryDir]);

        // Load the library
        $lib = $this->libraries->get('TestGetLibraryParametersFromConfig');
        $this->assertInstanceOf('Application\Library\TestGetLibraryParametersFromConfig', $lib);

        // And check the parameters
        $this->assertEquals(5, $lib->parameters['provided']);
    }

    /* ---------------------------------- Add libraries --------------------------------------------- */

    public function testAddLibraryObject()
    {
        $this->libraries->addLibraryObject('TestAddLibraryObject', 5);

        $this->assertEquals(5, $this->libraries->get('TestAddLibraryObject'));
    }

    public function testAddLibraryClass()
    {
        require_once('test'.DS.'libraries'.DS.'TestAddLibraryClass'.DS.'TestAddLibraryClass.php');

        $this->libraries->addLibraryClass('LibraryClass', '\Custom\Spaces\TestAddLibraryClass');

        $this->assertInstanceOf('\Custom\Spaces\TestAddLibraryClass', $this->libraries->get('LibraryClass'));
    }

    /**
     * @depends testAddLibraryClass
     * @expectedException \FuzeWorks\Exception\LibraryException
     */
    public function testAddLibraryClassFail()
    {
        $this->libraries->addLibraryClass('LibraryClassFail', '\Case\Not\Exist');
    }

    public function tearDown()
    {
        Factory::getInstance()->config->getConfig('error')->revert();
    }

}
