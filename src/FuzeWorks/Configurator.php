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
use FuzeWorks\Exception\ConfiguratorException;
use FuzeWorks\Exception\InvalidArgumentException;
use Tracy\Debugger;

/**
 * Class Configurator.
 *
 * The configurator prepares FuzeWorks and loads it when requested. 
 * 
 * The user passes variables into the Configurator and the Configurator makes sure
 * that FuzeWorks is loaded accordingly. 
 * 
 * This allows for more flexible startups.
 * @author    TechFuze <contact@techfuze.net>
 * @copyright Copyright (c) 2013 - 2018, Techfuze. (http://techfuze.net)
 */
class Configurator
{

    /**
     * The parameters that will be passed to FuzeWorks.
     *
     * @var array
     */ 
    protected $parameters = ['debugEnabled' => false];

    /**
     * Components that will be attached to the Factory.
     *
     * @var array Array of classnames
     */ 
    protected $components = [];

    /**
     * Directories that will be passed to FuzeWorks components. 
     * 
     * These are NOT the temp and log directory.
     *
     * @var array of directories
     */     
    protected $directories = ['app' => []];

    const COOKIE_SECRET = 'fuzeworks-debug';

    /* ---------------- Core Directories--------------------- */

    /**
     * Sets path to temporary directory.
     *
     * @param string $path
     * @return Configurator
     * @throws InvalidArgumentException
     */
    public function setLogDirectory(string $path)
    {
        if (!is_dir($path))
            throw new InvalidArgumentException("Could not set log directory. Directory does not exist", 1);
        $this->parameters['logDir'] = $path;

        return $this;
    }

    /**
     * Sets path to temporary directory.
     *
     * @param string $path
     * @return Configurator
     * @throws InvalidArgumentException
     */
    public function setTempDirectory(string $path)
    {
        if (!is_dir($path))
            throw new InvalidArgumentException("Could not set temp directory. Directory does not exist", 1);
        $this->parameters['tempDir'] = $path;

        return $this;
    }

    /**
     * Add a directory to FuzeWorks
     *
     * @param string $directory
     * @param string $category Optional. Defaults to 'app
     * @return $this
     */
    public function addDirectory(string $directory, string $category = 'app')
    {
        $this->directories[$category][] = $directory;

        return $this;
    }

    /* ---------------- Components -------------------------- */

    /**
     * Registers a component that will be added to the Factory when the container is built
     *
     * @param iComponent $component
     * @return Configurator
     */
    public function addComponent(iComponent $component)
    {
        foreach ($component->getClasses() as $objectName => $className) {
            $this->components[$objectName] = $className;
        }

        return $this;
    }

    /* ---------------- Other Features ---------------------- */

    /**
     * Sets the default timezone.
     * @param string $timezone
     * @return Configurator
     * @throws InvalidArgumentException
     */
    public function setTimeZone(string $timezone)
    {
        if (!date_default_timezone_set($timezone))
            throw new InvalidArgumentException("Could not set timezone. Invalid timezone provided.", 1);
        @ini_set('date.timezone', $timezone); // @ - function may be disabled

        return $this;
    }

    /**
     * Adds new parameters. Use to quickly set multiple parameters at once
     * @param array $params
     * @return Configurator
     */
    public function setParameters(array $params)
    {
        foreach ($params as $key => $value) {
            $this->parameters[$key] = $value;
        }

        return $this;
    }

    /* ---------------- Debug Mode -------------------------- */

    /**
     * Fully enable or disable debug mode using one variable
     * @param bool $bool
     * @return Configurator
     */
    public function enableDebugMode(bool $bool = true)
    {
        $this->parameters['debugEnabled'] = $bool;

        return $this;
    }

    /**
     * Provide a string from where debug mode can be accessed.
     * Can be the following type of addresses:
     * @todo
     * @param string|array $address
     * @return Configurator
     * @throws InvalidArgumentException
     */
    public function setDebugAddress($address = 'NONE')
    {
        // First we fetch the list
        if (!is_string($address) && !is_array($address))
            throw new InvalidArgumentException("Can not set debug address. Address must be a string or array",1);

        // Then we test some common cases
        if (is_string($address) && $address == 'NONE')
        {
            $this->parameters['debugMatch'] = false;
            return $this;
        }
        elseif (is_string($address) && $address == 'ALL')
        {
            $this->parameters['debugMatch'] = true;
            return $this;
        }

        // Otherwise we run the regular tracy detectDebugMode
        $list = is_string($address)
            ? preg_split('#[,\s]+#', $address)
            : (array) $address;
        $addr = isset($_SERVER['REMOTE_ADDR'])
            ? $_SERVER['REMOTE_ADDR']
            : php_uname('n');
        $secret = isset($_COOKIE[self::COOKIE_SECRET]) && is_string($_COOKIE[self::COOKIE_SECRET])
            ? $_COOKIE[self::COOKIE_SECRET]
            : NULL;
        if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $list[] = '127.0.0.1';
            $list[] = '::1';
        }
       
        $this->parameters['debugMatch'] = in_array($addr, $list, TRUE) || in_array("$secret@$addr", $list, TRUE);

        return $this;
    }

    /**
     * Set the email to send logs to from Tracy
     * @param string
     * @return Configurator
     */
    public function setDebugEmail($email): self
    {
        $this->parameters['debugEmail'] = $email;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return $this->parameters['debugEnabled'] && $this->parameters['debugMatch'];
    }

    /**
     * Create the container which holds FuzeWorks.
     *
     * Due to the static nature of FuzeWorks, this is not yet possible.
     * When issue #101 is completed, this should be resolved.
     *
     * @return Factory
     * @throws \Exception
     */
    public function createContainer(): Factory
    {
        // First set all the fixed directories
        Core::$tempDir = $this->parameters['tempDir'];
        Core::$logDir = $this->parameters['logDir'];
        Core::$appDirs = $this->directories['app'];

        // Then prepare the debugger
        $debug = ($this->parameters['debugEnabled'] && $this->parameters['debugMatch'] ? true : false);
        if (!defined('ENVIRONMENT'))
            define('ENVIRONMENT', ($debug ? 'DEVELOPMENT' : 'PRODUCTION')); // @codeCoverageIgnore

        // And enable Tracy Debugger 
        if (class_exists('Tracy\Debugger', true))
        {
            Debugger::enable(!$debug, realpath($this->parameters['logDir']));
            if (isset($this->parameters['debugEmail']))
            {
                Debugger::$email = $this->parameters['debugEmail'];
            }
            Logger::$useTracy = true;
        }

        // Then load the framework
        $container = Core::init();

        // Add all components
        foreach ($this->components as $componentName => $componentClass) {
            if (!class_exists($componentClass))
                throw new ConfiguratorException("Could not load component '".$componentName."'. Class '".$componentClass."' does not exist.", 1);

            $container->setInstance($componentName, new $componentClass());
        }

        // And add all directories to the components
        foreach ($this->directories as $component => $directories) {
            if ($component == 'app')
                continue;

            $container->{$component}->setDirectories($directories);
        }

        return $container;
    }
}