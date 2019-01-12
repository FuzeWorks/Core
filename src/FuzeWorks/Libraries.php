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

namespace FuzeWorks;


use FuzeWorks\Exception\ConfigException;
use FuzeWorks\Exception\LibraryException;

class Libraries
{
    /**
     * Array of all the paths where libraries can be found
     *
     * @var array Library paths
     */
    protected $libraryPaths = [];

    /**
     * Array of loaded library objects
     *
     * @var array Library objects
     */
    protected $libraryObjects = [];

    /**
     * Array of libraries with their classnames, so they can be easily loaded
     *
     * @var array Library classes
     */
    protected $libraryClasses = [];

    /**
     * FuzeWorks Factory object. For internal use.
     *
     * @var Factory
     */
    protected $factory;

    /**
     * Libraries constructor.
     *
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->factory = Factory::getInstance();
        $this->libraryPaths = Core::$appDirs;
    }

    /**
     * Add a library to FuzeWorks by adding an object.
     *
     * @param string $libraryName
     * @param object $libraryObject
     */
    public function addLibraryObject(string $libraryName, $libraryObject)
    {
        $this->libraryObjects[strtolower($libraryName)] = $libraryObject;
    }

    /**
     * Add a library to FuzeWorks by adding its class name.
     *
     * @param string $libraryName
     * @param string $libraryClass
     * @throws LibraryException
     */
    public function addLibraryClass(string $libraryName, string $libraryClass)
    {
        if (!class_exists($libraryClass, false))
            throw new LibraryException("Could not add library class. '" . $libraryClass . "' could not be loaded.", 1);

        $this->libraryClasses[strtolower($libraryName)] = $libraryClass;
    }

    /**
     * Retrieve a library.
     *
     * Loads a library from one of the following sources in the following order:
     * - From the loaded libraries
     * - From the known libraryClasses
     * - From the provided alternate library directory
     * - From the earlier provided libraryPaths
     *
     * @param string $libraryName
     * @param array $parameters
     * @param array $altDirectories
     * @return object
     * @throws LibraryException
     */
    public function get(string $libraryName, array $parameters = [], array $altDirectories = [])
    {
        // Test for empty string
        if (empty($libraryName))
        {
            throw new LibraryException("Could not load library. No name provided", 1);
        }

        // Test if the library already exists
        $libraryNameLowerCase = strtolower($libraryName);
        $libraryClassname = '\Application\Library\\' . ucfirst($libraryName);
        $libraryFilename = ucfirst($libraryName);
        if (isset($this->libraryObjects[$libraryNameLowerCase]))
            return $this->libraryObjects[$libraryNameLowerCase];

        // Library not found. First test if the libraryClass exists
        if (isset($this->libraryClasses[$libraryNameLowerCase]))
            return $this->initLibrary($libraryName, $this->libraryClasses[$libraryNameLowerCase], $parameters);

        // Try and load from the alternate directory if provided
        $paths = (empty($altDirectories) ? $this->libraryPaths : $altDirectories);

        // Try and find the library in the libraryPaths
        foreach ($paths as $path)
        {
            // First look if a .php file exists in the libraryPath
            $classFile = $path . DS . $libraryFilename . '.php';
            if (file_exists($classFile))
            {
                require_once($classFile);
                return $this->initLibrary($libraryName, $libraryClassname);
            }

            $classFile = $path . DS . $libraryFilename . DS . $libraryFilename . '.php';
            if (file_exists($classFile))
            {
                require_once($classFile);
                return $this->initLibrary($libraryName, $libraryClassname);
            }
        }

        // Throw exception if not found
        throw new LibraryException("Could not load library. Library not found.", 1);
    }

    /**
     * Library Initializer
     *
     * Instantiates and returns a library.
     * Determines whether to use the parameters array or a config file
     *
     * @param       string $libraryName
     * @param       string $libraryClass
     * @param       array $parameters
     * @throws      LibraryException
     * @return      object
     */
    protected function initLibrary(string $libraryName, string $libraryClass, array $parameters = [])
    {
        // First check to see if the library is already loaded
        if (!class_exists($libraryClass, false))
        {
            throw new LibraryException("Could not initiate library. Class not found", 1);
        }

        // Determine what parameters to use
        if (empty($parameters))
        {
            try {
                $parameters = $this->factory->config->getConfig(strtolower($libraryName))->toArray();
            } catch (ConfigException $e) {
                // No problem, just use an empty array instead
                $parameters = array();
            }
        }

        // Load the class object
        $classObject = new $libraryClass($parameters);

        // Check if the address is already reserved, if it is, we can presume that a new instance is requested.
        // Otherwise this code would not be reached

        // Now load the class
        $this->libraryObjects[strtolower($libraryName)] = $classObject;
        $this->factory->logger->log("Loaded Library: ".$libraryName);
        return $this->libraryObjects[strtolower($libraryName)];
    }

    /**
     * Set the directories. Automatically gets invoked if libraryPaths are added to FuzeWorks\Configurator.
     *
     * @param array $directories
     */
    public function setDirectories(array $directories)
    {
        $this->libraryPaths = array_merge($this->libraryPaths, $directories);
    }

    /**
     * Add a path where libraries can be found
     *
     * @param string $directory The directory
     * @return void
     */
    public function addLibraryPath($directory)
    {
        if (!in_array($directory, $this->libraryPaths))
        {
            $this->libraryPaths[] = $directory;
        }
    }

    /**
     * Remove a path where libraries can be found
     *
     * @param string $directory The directory
     * @return void
     */
    public function removeLibraryPath($directory)
    {
        if (($key = array_search($directory, $this->libraryPaths)) !== false)
        {
            unset($this->libraryPaths[$key]);
        }
    }

    /**
     * Get a list of all current libraryPaths
     *
     * @return array Array of paths where libraries can be found
     */
    public function getLibraryPaths(): array
    {
        return $this->libraryPaths;
    }
}