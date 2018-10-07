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
use FuzeWorks\ConfigORM\ConfigORM;
use FuzeWorks\Exception\ConfigException;

/**
 * Config Class.
 *
 * This class gives access to the config files. It allows you to open configurations and edit them.
 *
 * @author    TechFuze <contact@techfuze.net>
 * @copyright Copyright (c) 2013 - 2018, Techfuze. (http://techfuze.net)
 * @todo      Implement config extensions
 */
class Config
{

    /**
     * Array where all config files are saved while FuzeWorks runs
     * 
     * @var array Array of all loaded config file ORM's
     */
    protected $cfg = array();

    /**
     * Paths where Helpers can be found. 
     * 
     * Libraries will only be loaded if either a directory is supplied or it is in one of the helperPaths
     * 
     * @var array Array of paths where helpers can be found
     */
    protected $configPaths = array();

    public function __construct()
    {
        $this->configPaths = Core::$appDirs;
    }

    /**
     * Retrieve a config file object
     * 
     * @param string $configName  Name of the config file. Eg. 'main'
     * @param array  $configPaths Optional array of where to look for the config files
     * @return  ConfigORM of the config file. Allows for easy reading and editing of the file
     * @throws  ConfigException
     */
    public function getConfig($configName, array $configPaths = array()): ConfigORM
    {
        // First determine what directories to use
        $directories = (empty($configPaths) ? $this->configPaths : $configPaths);

        // Determine the config name
        $configName = strtolower($configName);
        
        // If it's already loaded, return the existing object
        if (isset($this->cfg[$configName]))
        {
            return $this->cfg[$configName];
        }

        // Otherwise try and load a new one
        $this->cfg[$configName] = $this->loadConfigFile($configName, $directories);
        return $this->cfg[$configName];
    }
    
    public function get($configName): ConfigORM
    {
        return $this->getConfig($configName);
    }

    public function __get($configName): ConfigORM
    {
        return $this->getConfig($configName);
    }

    /**
     * Clears all the config files and discards all changes not committed
     */
    public function discardConfigFiles()
    {
        $this->cfg = array();
    }

    /**
     * Determine whether the file exists and, if so, load the ConfigORM
     * 
     * @param string $configName  Name of the config file. Eg. 'main'
     * @param array  $configPaths Required array of where to look for the config files
     * @return  ConfigORM of the config file. Allows for easy reading and editing of the file
     * @throws  ConfigException
     */
    protected function loadConfigFile($configName, array $configPaths): ConfigORM
    {
        // Cycle through all directories
        foreach ($configPaths as $directory) 
        {
            // If file exists, load it and break the loop
            $file = $directory . DS . 'config.'.$configName.'.php';
            if (file_exists($file))
            {
                // Load object
                return new ConfigORM($file);
                break;
            }
        }

        // Try fallback
        $file = Core::$coreDir . DS . 'Config' . DS . 'config.' . $configName . '.php';
        if (file_exists($file))
        {
            // Load object
            return new ConfigORM($file);
        }

        throw new ConfigException("Could not load config. File $configName not found", 1);
    }

    /**
     * Set the directories. Automatically gets invoked if configPaths are added to FuzeWorks\Configurator.
     *
     * @param array $directories
     */
    public function setDirectories(array $directories)
    {
        $this->configPaths = $directories;
    }

    /**
     * Add a path where config files can be found
     * 
     * @param string $directory The directory
     * @return void
     */
    public function addConfigPath($directory)
    {
        if (!in_array($directory, $this->configPaths))
        {
            $this->configPaths[] = $directory;
        }
    }

    /**
     * Remove a path where config files can be found
     * 
     * @param string $directory The directory
     * @return void
     */    
    public function removeConfigPath($directory)
    {
        if (($key = array_search($directory, $this->configPaths)) !== false) 
        {
            unset($this->configPaths[$key]);
        }
    }

    /**
     * Get a list of all current configPaths
     * 
     * @return array Array of paths where config files can be found
     */
    public function getConfigPaths(): array
    {
        return $this->configPaths;
    }

}