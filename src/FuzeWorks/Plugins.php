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
 * @since Version 1.1.4
 *
 * @version Version 1.2.0
 */

namespace FuzeWorks;
use FuzeWorks\Exception\PluginException;
use ReflectionClass;
use ReflectionException;

/**
 * Plugins Class.
 *
 * Plugins are small component that can be implemented into FuzeWorks and will run upon its start. When FuzeWorks loads, a header file of every plugin will be loaded. This allows plugins to hook into routes, events, factory components and other parts of the framework. This for instance allows the creation of an administration panel, or a contnet management system. 
 * 
 * To make a plugin there are 2 requirements:
 * 1. A plugin class file
 * 2. A plugin header file
 * 
 * To create the plugin, create a directory (with the name of the plugin) in the Plugin folder, inside the Application environment. Next you should add a header.php file in this directory. 
 * This file needs to be in the FuzeWorks\Plugins namespace, and be named *PluginName*Header. For example: TestHeader. 
 * It is required that this header file implements the FuzeWorks\iPluginHeader. All headers must have the init() method. This method will run upon starting FuzeWorks.
 * 
 * Next a plugin class should be created. This file should be named the same as the folder, and be in the Application\Plugin namespace. An alternative classname can be set in the header, by creating a public $className variable. This plugin can be called using the $plugins->get() method.
 *
 * @todo 	  Implement events
 * @todo      Add methods to enable and disable plugins
 * @author    TechFuze <contact@techfuze.net>
 * @copyright Copyright (c) 2013 - 2018, Techfuze. (http://techfuze.net)
 */
class Plugins
{

	/**
	 * Array of all the paths where plugins can be found
	 * 
	 * @var array Plugin paths
	 */
	protected $pluginPaths = array();

	/**
	 * Array of loaded Plugins, so that they won't be reloaded
	 * Plugins only end up here after being explicitly loaded. Header files do NOT count.
	 * 
	 * @var array Array of loaded plugins
	 */
	protected $plugins = array();

	/**
	 * Array of plugin header classes. 
	 * Loaded upon startup. Provide details on what class should load for the plugin. 
	 * 
	 * @var array Array of loaded plugin header classes
	 */
	protected $headers = array();

	/**
	 * Config file for the plugin system 
	 * 
	 * @var ConfigORM
	 */	
	protected $cfg;

    /**
     * Called upon creation of the plugins class.
     * 
     * @param string $directory The directory
     * @return void
     */
	public function __construct()
	{
		$this->cfg = Factory::getInstance()->config->plugins;
		$this->pluginPaths = Core::$appDirs;
	}

    /**
     * Load the header files of all plugins. 
     */
	public function loadHeadersFromPluginPaths()
	{
		// Cycle through all pluginPaths
		$this->headers = array();
		foreach ($this->pluginPaths as $pluginPath) {
			
			// If directory does not exist, skip it
			if (!file_exists($pluginPath) || !is_dir($pluginPath))
			{
				continue;
			}

			// Fetch the contents of the path
			$pluginPathContents = array_diff(scandir($pluginPath), array('..', '.'));
			
			// Now go through each entry in the plugin folder
			foreach ($pluginPathContents as $pluginFolder) {
				if (!is_dir($pluginPath . DS . $pluginFolder))
				{
					continue;
				}

				// If a header file exists, use it
				$file = $pluginPath . DS . $pluginFolder . DS . 'header.php';
				$pluginFolder = ucfirst($pluginFolder);
				$className = '\FuzeWorks\Plugins\\'.$pluginFolder.'Header';
				if (file_exists($file))
				{
				    // Load the header file
                    require_once($file);
                    $header = new $className();
                    if (!$header instanceof iPluginHeader)
                    {
                        continue;
                    }

					// Load the header
                    $this->loadHeader($header);
				}

				// If it doesn't exist, skip it
				continue;
			}

		}
	}

    /**
     * Load a header object.
     *
     * The provided header will be loaded into the header registry and initialized.
     *
     * @param iPluginHeader $header
     * @return bool
     */
	protected function loadHeader(iPluginHeader $header): bool
    {
        // Fetch the name
        $pluginName = $header->getName();

        // Check if the plugin is disabled
        if (in_array($pluginName, $this->cfg->disabled_plugins))
        {
            $this->headers[$pluginName] = 'disabled';
            return false;
        }

        // Initialize it
        $h = $this->headers[$pluginName] = $header;
        $h->init();

        // And log it
        Factory::getInstance()->logger->log('Loaded Plugin Header: \'' . $pluginName . '\'');
        return true;
    }

    /**
     * Add a Plugin to FuzeWorks
     *
     * The provided plugin header will be loaded into the registry and initialized
     *
     * @param iPluginHeader $header
     * @return bool
     */
    public function addPlugin(iPluginHeader $header): bool
    {
        return $this->loadHeader($header);
    }

    /**
     * Get a plugin.
     *
     * @param string $pluginName Name of the plugin
     * @param array $parameters Parameters to send to the __construct() method
     * @return object                    Plugin
     * @throws Exception\EventException
     * @throws PluginException
     * @throws ReflectionException
     */
	public function get($pluginName, array $parameters = null)
	{
		if (empty($pluginName)) 
		{
			throw new PluginException("Could not load plugin. No name provided", 1);
		}

		// Determine the name of the plugin
		$pluginName = ucfirst($pluginName);

		// Fire pluginGetEvent, and cancel or return custom plugin if required
		$event = Events::fireEvent('pluginGetEvent', $pluginName);
		if ($event->isCancelled())
		{
			return false;
		}
		elseif ($event->getPlugin() != null)
		{
			return $event->getPlugin();
		}

		// Otherwise just set the variables
		$pluginName = $event->pluginName;

		// Check if the plugin is already loaded and return directly
		if (isset($this->plugins[$pluginName]))
		{
			return $this->plugins[$pluginName];
		}

		// Check if the plugin header exists
		if (!isset($this->headers[$pluginName]))
		{
			throw new PluginException("Could not load plugin. Plugin header does not exist", 1);
		}

		// If disabled, don't bother
		if (in_array($pluginName, $this->cfg->disabled_plugins))
		{
			throw new PluginException("Could not load plugin. Plugin is disabled", 1);
		}

		// Determine what file to load
		$header = $this->headers[$pluginName];

		// If a 'getPlugin' method is found in the header, call that instead
		if (method_exists($header, 'getPlugin'))
		{
			$this->plugins[$pluginName] = $header->getPlugin();
			Factory::getInstance()->logger->log('Loaded Plugin: \'' . $pluginName . '\'');
			return $this->plugins[$pluginName];
		}

		// Determine class name and file
        // @todo Find a more reliable method for determining header directory
        $headerReflection = new ReflectionClass( get_class($header) );
        $directory = dirname($headerReflection->getFileName());
		$classFile = (isset($header->classFile) ? $directory.DS.$header->classFile : $directory.DS.$pluginName.".php");
		$className = (isset($header->className) ? $header->className : '\Application\Plugin\\'.$pluginName);

		// Try to access the file
        if (!file_exists($classFile))
        {
            throw new PluginException("Could not load plugin. Class file does not exist", 1);
        }

		// Attempt to load the plugin
		require_once($classFile);
		if (!class_exists($className, false))
		{
			throw new PluginException("Could not load plugin. Class does not exist", 1);
		}
		$this->plugins[$pluginName] = new $className($parameters);
		Factory::getInstance()->logger->log('Loaded Plugin: \'' . $pluginName . '\'');

		// And return it
		return $this->plugins[$pluginName];
	}

    /**
     * Set the directories. Automatically gets invoked if pluginPaths are added to FuzeWorks\Configurator.
     *
     * @param array $directories
     */
    public function setDirectories(array $directories)
    {
        $this->pluginPaths = $directories;
    }

    /**
     * Add a path where plugins can be found
     * 
     * @param string $directory The directory
     * @return void
     */
	public function addPluginPath($directory)
	{
		if (!in_array($directory, $this->pluginPaths))
		{
			$this->pluginPaths[] = $directory;
		}
	}

    /**
     * Remove a path where plugins can be found
     * 
     * @param string $directory The directory
     * @return void
     */  
	public function removePluginPath($directory)
	{
		if (($key = array_search($directory, $this->pluginPaths)) !== false) 
		{
		    unset($this->pluginPaths[$key]);
		}
	}

    /**
     * Get a list of all current pluginPaths
     * 
     * @return array Array of paths where plugins can be found
     */
	public function getPluginPaths(): array
	{
		return $this->pluginPaths;
	}
}