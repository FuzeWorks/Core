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

namespace FuzeWorks\Event;

use FuzeWorks\Event;

/**
 * Class routerRouteEvent.
 *
 * Fired after the router has extracted the path, and is about to find out what route matches the path.
 *
 * This Event is usefull for adding routes.
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class routerRouteEvent extends Event
{
    /**
     * @var array The routing table
     */
    public $routes;

    /**
     * @var bool Whether the callable will be loaded directly after or not
     */
    public $performLoading;

    /**
     * Whether a cached page should be ignored or not
     * 
     * @var bool true if cache should not be used
     */
    public $cacheOverride = false;

    /**
     * The current path input to FuzeWorks.
     *
     * @var null|string
     */
    public $path;

    public function init($routes, $performLoading, $path)
    {
        $this->routes = $routes;
        $this->performLoading = $performLoading;
        $this->path = $path;
    }

    /**
     * Whether a cached page should be ignored or not
     * 
     * @param bool $overrideCache true if cache should not be used
     */
    public function overrideCache($bool = true)
    {
        $this->cacheOverride = $bool;
    }
}