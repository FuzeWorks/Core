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
 * @copyright Copyright (c) 2013 - 2018, TechFuze. (http://techfuze.net)
 * @license   https://opensource.org/licenses/MIT MIT License
 *
 * @link  http://techfuze.net/fuzeworks
 * @since Version 0.0.1
 *
 * @version Version 1.2.0
 */

namespace FuzeWorks\ConfigORM;
use Iterator;

/**
 * Abstract ConfigORM class.
 *
 * This class implements the iterator, so a config file can be accessed using foreach.
 * A file can also be returned using toArray(), so it will be converted to an array
 *
 * @author    TechFuze <contact@techfuze.net>
 * @copyright Copyright (c) 2013 - 2018, TechFuze. (http://techfuze.net)
 */
abstract class ConfigORMAbstract implements Iterator
{
    /**
     * The original state of a config file. Can be reverted to using revert().
     *
     * @var array Config file
     */
    protected $originalCfg = [];

    /**
     * The current state of a config file.
     *
     * @var array Config file
     */
    protected $cfg = [];

    /**
     * Revert to the original conditions of the config file.
     */
    public function revert()
    {
        $this->cfg = $this->originalCfg;
    }

    /**
     * Checks if a requested key is set in the config file.
     *
     * @param string $name Parameter name
     *
     * @return bool true on isset, false on not
     */
    public function __isset($name)
    {
        return isset($this->cfg[$name]);
    }

    /**
     * Return a value from a config file.
     *
     * @param string $name Key of the requested entry
     *
     * @return mixed Value of the requested entry
     */
    public function __get($name)
    {
        return $this->cfg[$name];
    }

    /**
     * Sets an entry in the config file.
     *
     * @param string $name  Key of the entry
     * @param mixed  $value Value of the entry
     */
    public function __set($name, $value)
    {
        $this->cfg[$name] = $value;
    }

    /**
     * Unset a value in a config file.
     *
     * @param string Key of the entry
     */
    public function __unset($name)
    {
        unset($this->cfg[$name]);
    }

    /**
     * Iterator method.
     */
    public function rewind()
    {
        return reset($this->cfg);
    }

    /**
     * Iterator method.
     */
    public function current()
    {
        return current($this->cfg);
    }

    /**
     * Iterator method.
     */
    public function key()
    {
        return key($this->cfg);
    }

    /**
     * Iterator method.
     */
    public function next()
    {
        return next($this->cfg);
    }

    /**
     * Iterator method.
     */
    public function valid()
    {
        return key($this->cfg) !== null;
    }

    /**
     * Returns the config file as an array.
     *
     * @return array Config file
     */
    public function toArray()
    {
        return $this->cfg;
    }
}