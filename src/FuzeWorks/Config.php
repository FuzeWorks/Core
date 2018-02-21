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
 * @author    TechFuze
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 * @copyright Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license   http://opensource.org/licenses/GPL-3.0 GPLv3 License
 *
 * @link  http://techfuze.net/fuzeworks
 * @since Version 0.0.1
 *
 * @version Version 1.0.0
 */

namespace FuzeWorks;
use FuzeWorks\ConfigORM\ConfigORM;
use FuzeWorks\Exception\ConfigException;

/**
 * Config Class.
 *
 * This class gives access to the config files. It allows you to open configurations and edit them.
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
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
        $this->configPaths[] = Core::$appDir . DS. 'Config';
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