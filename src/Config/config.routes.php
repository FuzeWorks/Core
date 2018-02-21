<?php
/**
 * FuzeWorks Application Skeleton.
 *
 * The FuzeWorks MVC PHP FrameWork
 *
 * Copyright (C) 2016   TechFuze
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
 * @since Version 1.1.1
 *
 * @version Version 1.1.4
 */

/**
 * Possible values:
 *	Default callable: Adds a route that changes the URL structure. Sends all matches to the defaultCallable router
 * 	'routingString'
 *
 * 	Custom callable: Adds a route that sends all matches to the provided callable. Allows user to replace defaultCallable
 *	'routingString' => array('callable' => array(CALLABLE))
 *
 * 	Dynamic rewrite: Adds a route that rewrites an URL to a specific controller and method configuration, using a callable. The callable can dynamically determine which page to load. 
 * 	'routingString' => CALLABLE
 * 
 * 	Static rewrite: Adds a route that rewrites and URL to a specific controller and method using a fixed route. This allows for pre-determined rewrites of pages. 
 * 	'routingString' => 'standard/index'
 *
 * 	Example routingString: '/^(?P<controller>.*?)(|\/(?P<function>.*?)(|\/(?P<parameters>.*?)))$/'
 */
return array(
);
