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
 * @author    TechFuze
 * @copyright Copyright (c) 2013 - 2018, Techfuze. (http://techfuze.net)
 * @copyright Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license   http://opensource.org/licenses/GPL-3.0 GPLv3 License
 *
 * @link  http://techfuze.net/fuzeworks
 * @since Version 1.1.4
 *
 * @version Version 1.1.4
 */

namespace FuzeWorks;
use FuzeWorks\Exception\PluginException;

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
 * It is recommended that this header file implements the FuzeWorks\PluginInterface. All headers must have the init() method. This method will run upon starting FuzeWorks. 
 * 
 * Next a plugin class should be created. This file should be named the same as the folder, and be in the Application\Plugin namespace. An alternative classname can be set in the header, by creating a public $className variable. This plugin can be called using the $plugins->get() method.
 *
 * @todo 	  Implement events
 * @author    Abel Hoogeveen <abel@techfuze.net>
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
		$this->pluginPaths[] = Core::$appDir . DS . 'Plugins';
		$this->cfg = Factory::getInstance()->config->plugins;
	}

    /**
     * Load the header files of all plugins. 
     */
	public function loadHeaders(): void
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
				$pluginName = ucfirst($pluginFolder);
				$className = '\FuzeWorks\Plugins\\'.$pluginName.'Header'; 
				if (file_exists($file))
				{
					// And load it
					if (in_array($pluginName, $this->cfg->disabled_plugins))
					{
						$this->headers[$pluginName] = 'disabled';
					}
					else
					{
						require_once($file);
						$header = new $className();
						$this->headers[$pluginName] = $header;
						$this->headers[$pluginName]->init();
						Factory::getInstance()->logger->log('Loaded Plugin Header: \'' . $pluginName . '\'');
					}

				}

				// If it doesn't exist, skip it
				continue;
			}

		}
	}

    /**
     * Get a plugin. 
     * 
     * @param string 	$pluginName 	Name of the plugin
     * @param array 	$parameters 	Parameters to send to the __construct() method
     * @param array 	$directory 		Directory to search for plugins in
     * @return object 					Plugin
     */
	public function get($pluginName, array $parameters = null, array $directory = null)
	{
		if (empty($pluginName)) 
		{
			throw new PluginException("Could not load plugin. No name provided", 1);
		}

		// First get the directories where the plugin can be located
		$directories = (is_null($directory) ? $this->pluginPaths : $directory);

		// Determine the name of the plugin
		$pluginFolder = $pluginName;
		$pluginName = ucfirst($pluginName);

		// Fire pluginGetEvent, and cancel or return custom plugin if required
		$event = Events::fireEvent('pluginGetEvent', $pluginName, $directories);
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
		$directories = $event->directories;

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
		if (method_exists($header, 'getPlugin'))
		{
			$this->plugins[$pluginName] = $header->getPlugin();
			Factory::getInstance()->logger->log('Loaded Plugin: \'' . $pluginName . '\'');
			return $this->plugins[$pluginName];
		}

		$classFile = (isset($header->classFile) ? $header->classFile : $pluginName.".php");
		$className = (isset($header->className) ? $header->className : '\Application\Plugin\\'.$pluginName);

		// Find the correct file
		$pluginFile = '';
		foreach ($directories as $pluginPath) {
			$file = $pluginPath . DS . $pluginFolder . DS . $classFile;
			if (file_exists($file))
			{
				$pluginFile = $file;
				break;
			}
		}

		// If not found, throw exception
		if (empty($pluginFile))
		{
			throw new PluginException("Could not load plugin. Class file does not exist", 1);
		}

		// Attempt to load the plugin
		require_once($pluginFile);
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