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

namespace FuzeWorks;
use FuzeWorks\Exception\HelperException;

/**
 * Helpers Class.
 *
 * Helpers, as the name suggests, help you with tasks. 
 * 
 * Each helper file is simply a collection of functions in a particular category.
 * There are URL Helpers, that assist in creating links, there are Form Helpers that help you create form elements, 
 * Text Helpers perform various text formatting routines, Cookie Helpers set and read cookies, 
 * File Helpers help you deal with files, etc.
 *
 * Unlike most other systems in FuzeWorks, Helpers are not written in an Object Oriented format. 
 * They are simple, procedural functions. Each helper function performs one specific task, with no dependence on other functions.
 *
 * FuzeWorks does not load Helper Files by default, so the first step in using a Helper is to load it. Once loaded, 
 * it becomes globally available to everything. 
 *
 * @author    TechFuze <contact@techfuze.net>
 * @copyright Copyright (c) 2013 - 2018, Techfuze. (http://techfuze.net)
 */
class Helpers
{

    /**
     * Array of loadedHelpers, so that they won't be reloaded
     * 
     * @var array Array of loaded helperNames
     */
    protected $helpers = array();

    /**
     * Paths where Helpers can be found. 
     * 
     * Libraries will only be loaded if either a directory is supplied or it is in one of the helperPaths
     * 
     * @var array Array of paths where helpers can be found
     */
    protected $helperPaths = array();

    public function __construct()
    {
        $this->helperPaths = Core::$appDirs;
        $this->helperPaths[] = Core::$coreDir . DS . 'Helpers';
    }

    /**
     * Load a helper.
     * 
     * Supply the name and the helper will be loaded from the supplied directory,
     * or from one of the helperPaths (which you can add).
     * 
     * @param string        $helperName Name of the helper
     * @param string|null   $directory  Directory to load the helper from, will ignore $helperPaths
     * @return bool                     Whether the helper was succesfully loaded (true if yes)
     */
    public function load($helperName, $directory = null): bool
    {
        // First determine the name of the helper
        $helperName = strtolower(str_replace(array('_helper', '.php'), '', $helperName).'_helper');
        
        // Determine what directories should be checked
        $directories = (is_null($directory) ? $this->helperPaths : array($directory));

        // Check it is already loaded
        if (isset($this->helpers[$helperName]))
        {
            Logger::log("Helper '".$helperName."' is already loaded. Skipping");
            return false;
        }

        // First check if there is an 'extension' class
        $extendedHelper = Factory::getInstance()->config->get('main')->application_prefix . $helperName;
        $extendedHelperLoaded = false;
        foreach ($directories as $helperPath) 
        {
            $file = $helperPath . DS . $extendedHelper . '.php';
            if (file_exists($file))
            {
                $extendedHelperLoaded = true;
                $extendedHelperFile = $file;
            }
        }

        // If an extension is loaded there needs to be a base helper
        if ($extendedHelperLoaded)
        {
            $baseHelper = Core::$coreDir . DS . 'Helpers' . DS . $helperName.'.php';
            if (!file_exists($baseHelper))
            {
                throw new HelperException("Could not load helper. Base Helper not found while Extension loaded", 1);
            }

            // Fire the associated event
            $event = Events::fireEvent('helperLoadEvent', $helperName, $baseHelper, $extendedHelper, $extendedHelperFile);
            if ($event->isCancelled()) 
            {
                Logger::log("Not loading helper. Aborted by event");
                return false;
            }

            include_once($event->extendedHelperFile);
            include_once($event->helperFile);
            $this->helpers[$event->helperName] = true;
            Logger::log("Loading base helper '".$event->helperName."' and extended helper '".$event->extendedHelperName."'");
            return true;
        }

        // If no extension exists, try loading a regular helper
        foreach ($directories as $helperPath) 
        {
            $file = $helperPath . DS . $helperName . '.php';
            if (file_exists($file))
            {

                // Fire the associated event
                $event = Events::fireEvent('helperLoadEvent', $helperName, $file);
                if ($event->isCancelled()) 
                {
                    Logger::log("Not loading helper. Aborted by event");
                    return false;
                }

                include_once($event->helperFile);
                $this->helpers[$event->helperName] = true;
                Logger::log("Loading helper '".$event->helperName."'");
                return true;
            }
        }

        throw new HelperException("Could not load helper. Helper not found.", 1);
    }

    /**
     * Alias for load
     * @see load() for more details
     *
     * @param string $helperName Name of the helper
     * @param string|null $directory Directory to load the helper from, will ignore $helperPaths
     * @return bool                     Whether the helper was succesfully loaded (true if yes)
     * @throws HelperException
     */
    public function get($helperName, $directory = null): bool
    {
        return $this->load($helperName, $directory);
    }

    /**
     * Set the directories. Automatically gets invoked if helperPaths are added to FuzeWorks\Configurator.
     *
     * @param array $directories
     */
    public function setDirectories(array $directories)
    {
        $this->helperPaths = $directories;
    }

    /**
     * Add a path where helpers can be found
     * 
     * @param string $directory The directory
     * @return void
     */
    public function addHelperPath($directory)
    {
        if (!in_array($directory, $this->helperPaths))
        {
            $this->helperPaths[] = $directory;
        }
    }

    /**
     * Remove a path where helpers can be found
     * 
     * @param string $directory The directory
     * @return void
     */    
    public function removeHelperPath($directory)
    {
        if (($key = array_search($directory, $this->helperPaths)) !== false) 
        {
            unset($this->helperPaths[$key]);
        }
    }

    /**
     * Get a list of all current helperPaths
     * 
     * @return array Array of paths where helpers can be found
     */
    public function getHelperPaths(): array
    {
        return $this->helperPaths;
    }
}