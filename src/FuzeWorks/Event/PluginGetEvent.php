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
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 * @copyright Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license   http://opensource.org/licenses/GPL-3.0 GPLv3 License
 *
 * @link  http://techfuze.net/fuzeworks
 * @since Version 1.1.4
 *
 * @version Version 1.1.4
 */

namespace FuzeWorks\Event;
use FuzeWorks\Event;

/**
 * Event that will get fired when a plugin is retrieved. 
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2018, Techfuze. (http://techfuze.net)
 */
class PluginGetEvent extends Event
{

    /**
     * The name of the plugin that should be loaded
     *
     * @var string
     */
	public $pluginName;

    /**
     * Array of directories where to look at for the plugin
     *
     * @var array
     */
	public $directories = array();

    /**
     * Potential plugin to return instead. If set, the plugins class will return this object 
     *
     * @var object
     */	
	public $plugin = null;

	public function init($pluginName, array $directories = array())
	{
		$this->pluginName = $pluginName;
		$this->directories = $directories;
	}

    /**
     * Allows listeners to set a plugin that will be returned instead.
     *
     * @param object $plugin
     */	
	public function setPlugin($plugin)
	{
		$this->plugin = $plugin;
	}

    /**
     * Plugin that will be returned if set by a listener. 
     *
     * @return object|null $plugin
     */	
	public function getPlugin()
	{
		return $this->plugin;
	}
}

?>