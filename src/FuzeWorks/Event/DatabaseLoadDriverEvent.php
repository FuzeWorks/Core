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
 * @copyright Copyright (c) 2013 - 2017, Techfuze. (http://techfuze.net)
 * @copyright Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license   http://opensource.org/licenses/GPL-3.0 GPLv3 License
 *
 * @link  http://techfuze.net/fuzeworks
 * @since Version 1.0.4
 *
 * @version Version 1.0.4
 */

namespace FuzeWorks\Event;

use FuzeWorks\Event;

/**
 * Event that gets loaded when a database driver is loaded
 *
 * Use this to cancel the loading of a database, or change the provided database
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2017, Techfuze. (http://techfuze.net)
 */
class DatabaseLoadDriverEvent extends Event
{
    /**
     * A possible database that can be loaded. 
     * 
     * Provide a database in this variable and it will be loaded. It shall be identified as default if 
     * the parameters variable is empty. If there is a string in parameters this database shall be identified as
     * such. 
     *
     * @var FW_DB|null
     */
    public $database = null;

    /**
     * Parameters of the database to be loaded
     *
     * @var string
     */
    public $parameters;

    /**
     * Whether a database instance shall be cloned if existing
     *
     * @var bool
     */
    public $newInstance;

    /**
     * Whether to attach the queryBuilder to the database driver
     *
     * @var null|bool
     */
    public $queryBuilder;

    public function init($parameters = '', $newInstance = false, $queryBuilder = null)
    {
        $this->parameters = $parameters;
        $this->newInstance = $newInstance;
        $this->queryBuilder = $queryBuilder;
    }
}
