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
 * @since Version 0.0.1
 *
 * @version Version 1.2.0
 */

namespace FuzeWorks;

/**
 * Class EventPriority.
 *
 * The EventPriority is an "enum" which gives priorities an integer value, the higher the integer value, the lower the
 * priority. The available priorities are, from highest to lowest:
 *
 * EventPriority::MONITOR
 * EventPriority::HIGHEST
 * EventPriority::HIGH
 * EventPriority::NORMAL
 * EventPriority::LOW
 * EventPriority::LOWEST
 *
 * @author    TechFuze <contact@techfuze.net>
 * @copyright Copyright (c) 2013 - 2018, Techfuze. (http://techfuze.net)
 */
abstract class EventPriority
{
    const LOWEST = 5;
    const LOW = 4;
    const NORMAL = 3;
    const HIGH = 2;
    const HIGHEST = 1;
    const MONITOR = 0;

    /**
     * Returns the string of the priority based on the integer.
     *
     * @param $intPriorty
     *
     * @return bool|string A bool when the integer isn't a priority. If the integer is a priority, the name is returned
     */
    public static function getPriority($intPriorty)
    {
        switch ($intPriorty) {
            case 5:
                return 'EventPriority::LOWEST';
            case 4:
                return 'EventPriority::LOW';
            case 3:
                return 'EventPriority::NORMAL';
            case 2:
                return 'EventPriority::HIGH';
            case 1:
                return 'EventPriority::HIGHEST';
            case 0:
                return 'EventPriority::MONITOR';
            default:
                return false;
        }
    }

    /**
     * Returns the highest priority
     * This function is needed for the firing of events in the right order,.
     *
     * @return int
     */
    public static function getHighestPriority()
    {
        return self::MONITOR;
    }

    /**
     * Returns the lowest priority
     * This function is needed for the firing of events in the right order,.
     *
     * @return int
     */
    public static function getLowestPriority()
    {
        return self::LOWEST;
    }
}
