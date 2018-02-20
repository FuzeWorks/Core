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
use FuzeWorks\Exception\FactoryException;

/**
 * Factory Class.
 * 
 * The Factory class is the central point for class communication in FuzeWorks. 
 * When someone needs to load, for instance, the layout class, one has to do the following:
 * $factory = Factory::getInstance();
 * $layout = $factory->layout;
 * 
 * The Factory class allows the user to replace dependencies on the fly. It is possible for a class
 * to replace a dependency, like Logger, on the fly by calling the $factory->newInstance('Logger'); or the
 * $factory->setInstance('Logger', $object); This allows for creative ways to do dependency injection, or keep classes
 * separated. 
 * 
 * It is also possible to load a cloned instance of the Factory class, so that all properties are independant as well,
 * all to suit your very needs.
 * 
 * The Factory class is also extendible. This allows classes that extend Factory to access all it's properties. 
 * 
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class Factory
{

	/**
	 * The Factory instance that is shared by default when calling Factory::getInstance();
	 * 
	 * @var Factory Default shared instance
	 */
	private static $sharedFactoryInstance;

	/**
	 * Whether to clone all Factory instances upon calling Factory::getInstance()
	 * 
	 * @var bool Clone all Factory instances.
	 */
	protected static $cloneInstances = false;

	/**
	 * Config Object
	 * @var Config
	 */
	public $config;
	
	/**
	 * Logger Object
	 * @var Logger
	 */
	public $logger;
	
	/**
	 * Events Object
	 * @var Events
	 */
	public $events;
	
	/**
	 * Models Object
	 * @var Models
	 */
	public $models;
	   
	/**
	 * Layout Object
	 * @var Layout
	 */
	public $layout;
	
	/**
	 * Libraries Object
	 * @var Libraries
	 */
	public $libraries;
	
	/**
	 * Helpers Object
	 * @var Helpers
	 */
	public $helpers;
	
	/**
	 * Database Object
	 * @var Database
	 */
	public $database;
	
	/**
	 * Language Object
	 * @var Language
	 */
	public $language;
	
	/**
	 * Utf8 Object
	 * @var Utf8
	 */
	public $utf8;
	
	/**
	 * URI Object
	 * @var URI
	 */
	public $uri;
	
	/**
	 * Security Object
	 * @var Security
	 */
	public $security;
	
	/**
	 * Input Object
	 * @var Input
	 */
	public $input;
	
	/**
	 * Output Object
	 * @var Output
	 */
	public $output;
	
	/**
	 * Router Object
	 * @var Router
	 */
	public $router;
	
	/**
	 * Plugins Object
	 * @var Plugins
	 */
	public $plugins;

	/**
	 * Factory instance constructor. Should only really be called once
	 * @return void
	 */
	public function __construct()
	{
		// If there is no sharedFactoryInstance, prepare it
		if (is_null(self::$sharedFactoryInstance))
		{
			// @codeCoverageIgnoreStart
			self::$sharedFactoryInstance = $this;
	        $this->config = new Config();
	        $this->logger = new Logger();
	        $this->events = new Events();
	        $this->models = new Models();
	        $this->layout = new Layout();
	        $this->libraries = new Libraries();
	        $this->helpers = new Helpers();
	        $this->database = new Database();
	        $this->language = new Language();
	        $this->utf8 = new Utf8();
	        $this->uri = new URI();
	        $this->output = new Output();
	        $this->security = new Security();
	        $this->input = new Input();
	        $this->router = new Router();
	        $this->plugins = new Plugins();

	        return true;
		}
		// @codeCoverageIgnoreEnd

		// Otherwise, copy the existing instances
		$x = self::getInstance();
		foreach ($x as $key => $value)
		{
		    $this->{$key} = $value;
		}

		return true;
	}

	/**
	 * Get a new instance of the Factory class. 
	 * 
	 * @param bool $cloneInstance Whether to get a cloned instance (true) or exactly the same instance (false)
	 * @return Factory Instance
	 */
	public static function getInstance($cloneInstance = false): Factory
	{	
		if ($cloneInstance === true || self::$cloneInstances === true)
		{
			return clone self::$sharedFactoryInstance;
		}

		return self::$sharedFactoryInstance;
	}

	/**
	 * Enable cloning all Factory instances upon calling Factory::getInstance()
	 * 
	 * @return void
	 */
	public static function enableCloneInstances()
	{
		self::$cloneInstances = true;
	}

	/**
	 * Disable cloning all Factory instances upon calling Factory::getInstance()
	 * 
	 * @return void
	 */
	public static function disableCloneInstances()
	{
		self::$cloneInstances = false;
	}

	/**
	 * Create a new instance of one of the loaded classes.
	 * It reloads the class. It does NOT clone it. 
	 * 
	 * @param string $className The name of the loaded class, WITHOUT the namespace
	 * @param string $namespace Optional namespace. Defaults to 'FuzeWorks\'
	 * @return Factory Instance
	 */
	public function newInstance($className, $namespace = 'FuzeWorks\\'): self
	{
		// Determine the class to load
		$instanceName = strtolower($className);
		$className = $namespace.ucfirst($className);

		if (!isset($this->{$instanceName}))
		{
			throw new FactoryException("Could not load new instance of '".$instanceName."'. Instance was not found.", 1);
		}
		elseif (!class_exists($className, false))
		{
			throw new FactoryException("Could not load new instance of '".$instanceName."'. Class not found.", 1);
		}

		// Remove the current instance
		unset($this->{$instanceName});

		// And set the new one
		$this->{$instanceName} = new $className();

		// Return itself
		return $this;
	}

	/**
	 * Clone an instance of one of the loaded classes.
	 * It clones the class. It does NOT re-create it. 
	 * 
	 * @param string $className The name of the loaded class, WITHOUT the namespace
	 * @return Factory Instance
	 */
	public function cloneInstance($className): self
	{
		// Determine the class to load
		$instanceName = strtolower($className);

		if (!isset($this->{$instanceName}))
		{
			throw new FactoryException("Could not clone instance of '".$instanceName."'. Instance was not found.", 1);
		}

		// Clone the instance
		$this->{$instanceName} = clone $this->{$instanceName};

		// Return itself
		return $this;
	}

	/**
	 * Set an instance of one of the loaded classes with your own $object.
	 * Replace the existing class with one of your own.
	 * 
	 * @param string $className The name of the loaded class, WITHOUT the namespace
	 * @param mixed  $object    Object to replace the class with
	 * @return Factory Instance
	 */
	public function setInstance($className, $object): self
	{
		// Determine the instance name
		$instanceName = strtolower($className);

		// Unset and set
		unset($this->{$instanceName});
		$this->{$instanceName} = $object;

		// Return itself
		return $this;
	}

	/**
	 * Remove an instance of one of the loaded classes. 
	 * 
	 * @param string $className The name of the loaded class, WITHOUT the namespace
	 * @return Factory Factory Instance
	 */
	public function removeInstance($className): self
	{
		// Determine the instance name
		$instanceName = strtolower($className);

		if (!isset($this->{$instanceName}))
		{
			throw new FactoryException("Could not remove instance of '".$instanceName."'. Instance was not found.", 1);
		}

		// Unset
		unset($this->{$instanceName});

		// Return itself
		return $this;
	}
}
