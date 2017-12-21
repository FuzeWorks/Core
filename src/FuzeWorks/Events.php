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
use FuzeWorks\Exception\EventException;

/**
 * Class Events.
 *
 * FuzeWorks is built in a way that almost every core-event can be manipulated. This class provides various ways to hook into the core or other components
 * and manipulate the outcome of the functions. Components and core actions can 'fire' an event and components can 'hook' into that event. Let's take a look at the example below:
 *
 * If we want to add the current time at the end of each page title, we need to hook to the corresponding event. Those events are found in the 'events' directory in the system directory.
 * The event that will be fired when the title is changing is called layoutSetTitleEvent. So if we want our module to hook to that event, we add the following to the constructor:
 *
 * Events::addListener(array($this, "onLayoutSetTitleEvent"), "layoutSetTitleEvent", EventPriority::NORMAL);
 *
 * This will add the function "onLayoutSetTitleEvent" of our current class ($this) to the list of listeners with priority NORMAL. So we need to add
 * a method called onLayoutSetTitleEvent($event) it is very important to add the pointer-reference (&) or return the event, otherwise it doesn't change the event variables.
 *
 * If we now add the following code to our method, it will add the current time at the front of each title.
 *
 * $event->title = date('H:i:s ').$event->title;
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class Events
{
    /**
     * Array of classes that can handle events.
     *
     * @var array
     */
    public static $listeners = array();

    /**
     * Whether the event system is enabled or not.
     *
     * @var array
     */
    private static $enabled = true;

    /**
     * Adds a function as listener.
     *
     * @param mixed callback The callback when the events get fired, see {@link http://php.net/manual/en/language.types.callable.php PHP.net}
     * @param string $eventName The name of the event
     * @param int    $priority  The priority, even though integers are valid, please use EventPriority (for example EventPriority::Lowest)
     *
     * @see EventPriority
     *
     * @throws EventException
     */
    public static function addListener($callback, $eventName, $priority = EventPriority::NORMAL): void
    {
        // Perform multiple checks
        if (EventPriority::getPriority($priority) == false) {
            throw new EventException('Can not add listener: Unknown priority '.$priority, 1);
        }

        if (!is_callable($callback))
        {
            throw new EventException("Can not add listener: Callback is not callable", 1);
        }

        if (empty($eventName))
        {
            throw new EventException("Can not add listener: No eventname provided", 1);
        }

        if (!isset(self::$listeners[$eventName])) {
            self::$listeners[$eventName] = array();
        }

        if (!isset(self::$listeners[$eventName][$priority])) {
            self::$listeners[$eventName][$priority] = array();
        }

        self::$listeners[$eventName][$priority][] = $callback;
    }

    /**
     * Removes a function as listener.
     *
     * @param mixed callback The callback when the events get fired, see {@link http://php.net/manual/en/language.types.callable.php PHP.net}
     * @param string $eventName The name of the event
     * @param int    $priority  The priority, even though integers are valid, please use EventPriority (for example EventPriority::Lowest)
     *
     * @see EventPriority
     *
     * @throws EventException
     */
    public static function removeListener($callback, $eventName, $priority = EventPriority::NORMAL): void
    {
        if (EventPriority::getPriority($priority) == false) {
            throw new EventException('Unknown priority '.$priority);
        }

        if (!isset(self::$listeners[$eventName]) || !isset(self::$listeners[$eventName][$priority])) {
            return;
        }

        foreach (self::$listeners[$eventName][$priority] as $i => $_callback) {
            if ($_callback == $callback) {
                unset(self::$listeners[$eventName][$priority][$i]);

                return;
            }
        }
    }

    /**
     * Fires an Event.
     *
     * The Event gets created, passed around and then returned to the issuer.
     *
     * @param mixed $input Object for direct event, string for system event or notifierEvent
     * @todo  Implement Application Events
     * @todo  Implement Directory input for Events from other locations (like Modules)
     *
     * @return Event The Event
     */
    public static function fireEvent($input): Event
    {
        // First try and see if the object is an Event
        if (is_object($input))
        {
            $eventName = get_class($input);
            $eventName = explode('\\', $eventName);
            $eventName = end($eventName);
            $event = $input;
        }
        // Otherwise try to load an event based on the input string
        elseif (is_string($input))
        {
            $eventClass = ucfirst($input);
            $eventName = $input;

            // Try a direct class
            if (class_exists($eventClass, true))
            {
                $event = new $eventClass();
            }

            // Try a core event
            elseif (class_exists("\FuzeWorks\Event\\".$eventClass, true))
            {
                $class = "\FuzeWorks\Event\\".$eventClass;
                $event = new $class();
            }

            // Try a notifier event
            elseif (func_num_args() == 1)
            {
                $class = "\FuzeWorks\Event\NotifierEvent";
                $event = new $class();
            }

            // Or throw an exception on failure
            else
            {
                throw new EventException('Event '.$eventName.' could not be found!', 1);
            }
        }
        else
        {
            throw new EventException('Event could not be loaded. Invalid variable provided.', 1);
        }

        if (self::$enabled)
        {
            Logger::newLevel("Firing Event: '".$eventName."'");           
        }

        if (func_num_args() > 1) {
            call_user_func_array(array($event, 'init'), array_slice(func_get_args(), 1));
        }

        // Do not run if the event system is disabled
        if (!self::$enabled) {
            return $event;
        }

        Logger::log('Checking for Listeners');

        //There are listeners for this event
        if (isset(self::$listeners[$eventName])) {
            //Loop from the highest priority to the lowest
            for ($priority = EventPriority::getHighestPriority(); $priority <= EventPriority::getLowestPriority(); ++$priority) {
                //Check for listeners in this priority
                if (isset(self::$listeners[$eventName][$priority])) {
                    $listeners = self::$listeners[$eventName][$priority];
                    Logger::newLevel('Found listeners with priority '.EventPriority::getPriority($priority));
                    //Fire the event to each listener
                    foreach ($listeners as $callback) {
                        // @codeCoverageIgnoreStart
                        if (is_callable($callback)) {
                            Logger::newLevel('Firing function');
                        } elseif (!is_string($callback[0])) {
                            Logger::newLevel('Firing '.get_class($callback[0]).'->'.$callback[1]);
                        } else {
                            Logger::newLevel('Firing '.implode('->', $callback));
                        }
                        // @codeCoverageIgnoreEnd
                        
                        call_user_func($callback, $event);
                        Logger::stopLevel();
                    }

                    Logger::stopLevel();
                }
            }
        }

        Logger::stopLevel();

        return $event;
    }

    /**
     * Enables the event system.
     */
    public static function enable(): void
    {
        Logger::log('Enabled the Event system');
        self::$enabled = true;
    }

    /**
     * Disables the event system.
     */
    public static function disable(): void
    {
        Logger::log('Disabled the Event system');
        self::$enabled = false;
    }
}
