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
 * @copyright Copyright (c) 2013 - 2018, TechFuze. (http://techfuze.net)
 * @license   https://opensource.org/licenses/MIT MIT License
 *
 * @link  http://techfuze.net/fuzeworks
 * @since Version 0.0.1
 *
 * @version Version 1.2.0
 */

namespace FuzeWorks;
use FuzeWorks\ConfigORM\ConfigORM;
use FuzeWorks\Event\ConfigGetEvent;
use FuzeWorks\Exception\ConfigException;

/**
 * Config Class.
 *
 * This class gives access to the config files. It allows you to open configurations and edit them.
 *
 * @author    TechFuze <contact@techfuze.net>
 * @copyright Copyright (c) 2013 - 2018, TechFuze. (http://techfuze.net)
 */
class Config
{

    /**
     * Array where all config files are saved while FuzeWorks runs
     * 
     * @var array Array of all loaded config file ORM's
     */
    protected $cfg = [];

    /**
     * Array of config values that will be overridden
     *
     * @var array of config values
     */
    public static $configOverrides = [];

    /**
     * Paths where Helpers can be found. 
     * 
     * Libraries will only be loaded if either a directory is supplied or it is in one of the helperPaths
     * 
     * @var array Array of paths where helpers can be found
     */
    protected $configPaths = [];

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
    public function getConfig(string $configName, array $configPaths = []): ConfigORM
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

    /**
     * @param $configName
     * @return ConfigORM
     * @throws ConfigException
     */
    public function get($configName): ConfigORM
    {
        return $this->getConfig($configName);
    }

    /**
     * @param $configName
     * @return ConfigORM
     * @throws ConfigException
     */
    public function __get($configName): ConfigORM
    {
        return $this->getConfig($configName);
    }

    /**
     * Clears all the config files and discards all changes not committed
     */
    public function discardConfigFiles()
    {
        $this->cfg = [];
    }

    /**
     * Determine whether the file exists and, if so, load the ConfigORM
     * 
     * @param string $configName  Name of the config file. Eg. 'main'
     * @param array  $configPaths Required array of where to look for the config files
     * @return  ConfigORM of the config file. Allows for easy reading and editing of the file
     * @throws  ConfigException
     */
    protected function loadConfigFile(string $configName, array $configPaths): ConfigORM
    {
        // Fire event to intercept the loading of a config file
        /** @var ConfigGetEvent $event */
        try {
            $event = Events::fireEvent('configGetEvent', $configName, $configPaths);
            // @codeCoverageIgnoreStart
        } catch (Exception\EventException $e) {
            throw new ConfigException("Could not load config. ConfigGetEvent fired exception: '" . $e->getMessage() . "''", 1);
            // @codeCoverageIgnoreEnd
        }

        // If cancelled, load empty config
        if ($event->isCancelled())
        {
            return new ConfigORM();
        }

        // Cycle through all directories
        foreach ($event->configPaths as $configPath)
        {
            // If file exists, load it and break the loop
            $file = $configPath . DS . 'config.'.$event->configName.'.php';
            if (file_exists($file))
            {
                // Load object
                $configORM = (new ConfigORM())->load($file);

                // Override config values if they exist
                if (isset(self::$configOverrides[$event->configName]))
                {
                    foreach (self::$configOverrides[$event->configName] as $configKey => $configValue)
                        $configORM->{$configKey} = $configValue;
                }

                // Return object
                return $configORM;
                break;
            }
        }

        // Try fallback
        $file = Core::$coreDir . DS . 'Config' . DS . 'config.' . $event->configName . '.php';
        if (file_exists($file))
        {
            // Load object
            return (new ConfigORM())->load($file);
        }

        throw new ConfigException("Could not load config. File $event->configName not found", 1);
    }

    /**
     * Override a config value before FuzeWorks is loaded.
     *
     * Allows the user to change any value in config files loaded by FuzeWorks.
     *
     * @param string $configName
     * @param string $configKey
     * @param $configValue
     */
    public static function overrideConfig(string $configName, string $configKey, $configValue)
    {
        // Convert configName
        $configName = strtolower($configName);

        // If config doesn't exist yet, create it
        if (!isset(self::$configOverrides[$configName]))
            self::$configOverrides[$configName] = [];

        // And set the value
        self::$configOverrides[$configName][$configKey] = $configValue;
    }

    /**
     * Set the directories. Automatically gets invoked if configPaths are added to FuzeWorks\Configurator.
     *
     * @param array $directories
     */
    public function setDirectories(array $directories)
    {
        $this->configPaths = array_merge($this->configPaths, $directories);
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