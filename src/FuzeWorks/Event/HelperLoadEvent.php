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

namespace FuzeWorks\Event;

use FuzeWorks\Event;

/**
 * Event that gets loaded when a helper is loaded.
 *
 * Use this to cancel the loading of a helper, or change the helper to be loaded
 *
 * @author    TechFuze <contact@techfuze.net>
 * @copyright Copyright (c) 2013 - 2018, Techfuze. (http://techfuze.net)
 */
class HelperLoadEvent extends Event
{

    /**
     * The name of the helper that gets loaded
     *
     * @var string
     */
    public $helperName;

    /**
     * The directory of the helper that gets loaded
     *
     * @var string
     */
    public $helperFile;

    /**
     * An optional extension helper name that can get loaded.
     *
     * @var string|null
     */
    public $extendedHelperName = null;

    /**
     * The directory of an optional extension helper that can get loaded
     *
     * @var string|null
     */
    public $extendedHelperFile = null;

    public function init($helperName, $helperFile, $extendedHelperName = null, $extendedHelperFile = null)
    {
        $this->helperName = $helperName;
        $this->helperFile = $helperFile;
        $this->extendedHelperName = $extendedHelperName;
        $this->extendedHelperFile = $extendedHelperFile;
    }
}
