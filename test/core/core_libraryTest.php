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

    /* ---------------------------------- LibraryPaths ---------------------------------------------- */

    /**
     * @depends testLibrariesClass
     */
    public function testSetDirectories()
    {
        // Test initial
        $initial = array_merge(Core::$appDirs, ['test'.DS.'libraries']);
        $this->assertEquals($initial, $this->libraries->getLibraryPaths());

        // Add path
        $newPath = 'addPath';
        $this->libraries->setDirectories([$newPath]);
        $initial[] = $newPath;
        $this->assertEquals($initial, $this->libraries->getLibraryPaths());
    }

    /**
     * @expectedException FuzeWorks\Exception\LibraryException
     */
    public function testAddLibraryPathFail()
    {
        // First test if the library is not loaded yet
        $this->assertFalse(class_exists('TestAddLibraryPathFail', false));

        // Now test if the library can be loaded (hint: it can not)
        $this->libraries->get('TestAddLibraryPathFail');
    }

    /**
     * @depends testAddLibraryPathFail
     */
    public function testAddLibraryPath()
    {
        // Add the libraryPath
        $this->libraries->removeLibraryPath('test'.DS.'libraries');
        $this->libraries->addLibraryPath('test'.DS.'libraries'.DS.'TestAddLibraryPath');

        // And try to load it again
        $this->assertInstanceOf('Application\Library\TestAddLibraryPath', $this->libraries->get('TestAddLibraryPath'));
    }

    public function testRemoveLibraryPath()
    {
        // Test if the path does NOT exist
        $this->assertFalse(in_array('test'.DS.'libraries'.DS.'TestRemoveLibraryPath', $this->libraries->getLibraryPaths()));

        // Add it
        $this->libraries->addLibraryPath('test'.DS.'libraries'.DS.'TestRemoveLibraryPath');

        // Assert if it's there
        $this->assertTrue(in_array('test'.DS.'libraries'.DS.'TestRemoveLibraryPath', $this->libraries->getLibraryPaths()));

        // Remove it
        $this->libraries->removeLibraryPath('test'.DS.'libraries'.DS.'TestRemoveLibraryPath');

        // And test if it's gone again
        $this->assertFalse(in_array('test'.DS.'libraries'.DS.'TestRemoveLibraryPath', $this->libraries->getLibraryPaths()));
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
        $this->libraries->addLibraryPath('test'.DS.'libraries'.DS.'TestGetLibraryFromSubdirectory');

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
        Factory::getInstance()->config->getConfig('main')->revert();
    }

}
