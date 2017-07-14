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
use FuzeWorks\Exception\DatabaseException;
use FW_DB;

/**
 * Database loading class
 * 
 * Loads databases, forges and utilities in a standardized manner. 
 * 
 * @author  TechFuze <contact@techfuze.net>
 * @copyright (c) 2013 - 2014, TechFuze. (https://techfuze.net)
 * 
 */
class Database
{
    
    /**
     * The default database forge.
     * @var type FW_DB|null
     */
    protected static $defaultDB = null;

    /**
     * Array of all the non-default databases
     * @var array FW_DB|null
     */    
    protected static $databases = array();
    
    /**
     * The default database forge.
     * @var type FW_DB_forge|null
     */
    protected static $defaultForge = null;
    
    /**
     * Array of all the non-default databases forges.
     * @var array FW_DB_forge|null
     */    
    protected static $forges = array();

    /**
     * The default database utility.
     * @var type FW_DB_utility|null
     */
    protected static $defaultUtil = null;
    
    /**
     * Register with the TracyBridge upon startup
     */
    public function __construct()
    {
        if (class_exists('Tracy\Debugger', true))
        {
            DatabaseTracyBridge::register();
        }
    }

    /**
     * Retrieve a database using a DSN or the default configuration.
     * 
     * If a string is provided like this: 'dbdriver://username:password@hostname/database',
     * the string will be interpreted and converted into a database connection parameter array.
     * 
     * If a string is provided with a name, like this: 'default' the 'default' connection from the
     * configuration file will be loaded. If no string is provided the default database will be loaded.
     * 
     * If the $newInstance is a true boolean, a new instance will be loaded instead of loading the 
     * default one. $newInstance will also make sure that the loaded database is not default one. 
     * This behaviour will be changed in the future. 
     * 
     * 
     * If $queryBuilder = false is provided, the database will load without a queryBuilder. 
     * By default the queryBuilder will load.
     * 
     * @param string $parameters      
     * @param bool $newInstance
     * @param bool $queryBuilder
     * @return FW_DB|bool
     */
    public static function get($parameters = '', $newInstance = false, $queryBuilder = null) 
    {
        // Fire the event to allow settings to be changed
        $event = Events::fireEvent('databaseLoadDriverEvent', $parameters, $newInstance, $queryBuilder);
        if ($event->isCancelled())
        {
            return false;
        }

        // If an instance already exists and is requested, return it
        if (isset($event->database) && empty($event->parameters))
        {
            return self::$defaultDB = $event->database;
        }
        elseif (isset($event->database) && !empty($event->parameters))
        {
            return self::$databases[$event->parameters] = $event->database;
        }
        elseif (empty($event->parameters) && !$event->newInstance && is_object(self::$defaultDB) && ! empty(self::$defaultDB->conn_id))
        {
            return $reference = self::$defaultDB;
        }
        elseif (!empty($event->parameters) && !$event->newInstance && isset(self::$databases[$event->parameters])) 
        {
            return $reference = self::$databases[$event->parameters];
        }

        // If a new instance is required, load it
        require_once (Core::$coreDir . DS . 'Database'.DS.'DB.php');

        if ($event->newInstance === TRUE)
        {
            $database = DB($event->parameters, $event->queryBuilder);
        }
        elseif (empty($event->parameters) && $event->newInstance === FALSE)
        {
            $database = self::$defaultDB = DB($event->parameters, $event->queryBuilder);
        }
        else
        {
            $database = self::$databases[$event->parameters] = DB($event->parameters, $event->queryBuilder);
        }

        // Tie it into the Tracy Bar if available
        if (class_exists('\Tracy\Debugger', true))
        {
            DatabaseTracyBridge::registerDatabase($database);
        }

        return $database;
    }
    
    /**
     * Retrieves a database forge from the provided or default database.
     * 
     * If no database is provided, the default database will be used.
     * 
     * @param FW_DB|null    $database
     * @param bool          $newInstance
     * @return FW_DB_forge
     */
    public static function getForge($database = null, $newInstance = false)
    {
        // Fire the event to allow settings to be changed
        $event = Events::fireEvent('databaseLoadForgeEvent', $database, $newInstance);
        if ($event->isCancelled())
        {
            return false;
        }

        // First check if we're talking about the default forge and that one is already set
        if (is_object($event->forge) && ($event->forge instanceof FW_DB_forge) )
        {
            return $event->forge;
        }
        elseif (is_object($event->database) && $event->database === self::$defaultDB && is_object(self::$defaultForge))
        {
            return $reference = self::$defaultForge;
        }
        elseif ( ! is_object($event->database) OR ! ($event->database instanceof FW_DB))
        {
            isset(self::$defaultDB) OR self::get('', false);
            $database =& self::$defaultDB;
        }

        require_once (Core::$coreDir . DS . 'Database'.DS.'DB_forge.php');
        require_once(Core::$coreDir . DS . 'Database'.DS.'drivers'.DS.$database->dbdriver.DS.$database->dbdriver.'_forge.php');

        if ( ! empty($database->subdriver))
        {
            $driver_path = Core::$coreDir . DS . 'Database'.DS.'drivers'.DS.$database->dbdriver.DS.'subdrivers'.DS.$database->dbdriver.'_'.$database->subdriver.'_forge.php';
            if (file_exists($driver_path))
            {
                require_once($driver_path);
                $class = 'FW_DB_'.$database->dbdriver.'_'.$database->subdriver.'_forge';
            }
            else
            {
                throw new DatabaseException("Could not load forge. Driver file does not exist.", 1);
            }
        }
        else
        {
            $class = 'FW_DB_'.$database->dbdriver.'_forge';
        }

        // Create a new instance of set the default database
        if ($event->newInstance)
        {
            return new $class($database);
        }
        else 
        {
            return self::$defaultForge = new $class($database);
        }
    }
    
    /**
     * Retrieves a database utility from the provided or default database.
     * 
     * If no database is provided, the default database will be used.
     * 
     * @param FW_DB|null $database
     * @param bool $newInstance
     * @return FW_DB_utility
     */
    public static function getUtil($database = null, $newInstance = false)
    {
        // Fire the event to allow settings to be changed
        $event = Events::fireEvent('databaseLoadUtilEvent', $database, $newInstance);
        if ($event->isCancelled())
        {
            return false;
        }

        // First check if we're talking about the default util and that one is already set
        if (is_object($event->util) && ($event->util instanceof FW_DB_utility))
        {
            return $event->util;
        }
        elseif (is_object($event->database) && $event->database === self::$defaultDB && is_object(self::$defaultUtil))
        {
            return $reference = self::$defaultUtil;
        }

        if ( ! is_object($event->database) OR ! ($event->database instanceof FW_DB))
        {
            isset(self::$defaultDB) OR self::get('', false);
            $database = & self::$defaultDB;
        }

        require_once (Core::$coreDir . DS . 'Database'.DS.'DB_utility.php');
        require_once(Core::$coreDir . DS . 'Database'.DS.'drivers'.DS.$database->dbdriver.DS.$database->dbdriver.'_utility.php');
        $class = 'FW_DB_'.$database->dbdriver.'_utility';

        if ($event->newInstance)
        {
            return new $class($database);
        }      
        else
        {
            return self::$defaultUtil = new $class($database);
        }
    }
}