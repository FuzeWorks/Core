<?php
/**
 * FuzeWorks.
 *
 * The FuzeWorks MVC PHP FrameWork
 *
 * Copyright (C) 2017   TechFuze
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
 * @since Version 0.0.1
 *
 * @version Version 1.0.4
 */

namespace FuzeWorks;

use FuzeWorks\TemplateEngine\{JsonEngine,PHPEngine,SmartyEngine,LatteEngine,TemplateEngine};
use FuzeWorks\Exception\LayoutException;

/**
 * Layout and Template Manager for FuzeWorks.
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2017, Techfuze. (http://techfuze.net)
 */
class Layout
{
    /**
     * The file to be loaded by the layout manager.
     *
     * @var null|string
     */
    public $file = null;

    /**
     * The directory of the file to be loaded by the layout manager.
     *
     * @var string
     */
    public $directory;

    /**
     * All assigned currently assigned to the template.
     *
     * @var array Associative Assigned Variable Array
     */
    private $assigned_variables = array();

    /**
     * All engines that can be used for templates.
     *
     * @var array of engines
     */
    private $engines = array();

    /**
     * All file extensions that can be used and are bound to a template engine.
     *
     * @var array of names of engines
     */
    private $file_extensions = array();

    /**
     * whether the template engines are already called.
     *
     * @var bool True if loaded
     */
    private $engines_loaded = false;

    /**
     * The currently selected template engine.
     *
     * @var string name of engine
     */
    private $current_engine;

    public function init()
    {
        $this->directory = Core::$appDir . DS .'Layout';
    }

    /**
     * Retrieve a template file using a string and a directory and immediatly parse it to the output class.
     *
     * What template file gets loaded depends on the template engine that is being used.
     * PHP for example uses .php files. Providing this function with 'home/dashboard' will load the home/layout.dashboard.php file.
     * You can also provide no particular engine, and the manager will decide what template to load.
     * Remember that doing so will result in a LayoutException when multiple compatible files are found.
     *
     * @param string $file         File to load
     * @param string $directory    Directory to load it from
     * @param bool   $directOutput Whether to directly output the result with an echo or send it to the output class. True if echo
     *
     * @throws LayoutException On error
     */
    public function display($file, $directory = null, $directOutput = false)
    {
        $output = Factory::getInstance()->output;
        $directory = (is_null($directory) ? $this->directory : $directory);

        if ($directOutput === true)
        {
            echo $this->get($file, $directory);
        }
        else
        {
            $output->append_output($this->get($file, $directory));  
        }
        
        return;
    }

    /**
     * Retrieve a template file using a string and a directory.
     *
     * What template file gets loaded depends on the template engine that is being used.
     * PHP for example uses .php files. Providing this function with 'home/dashboard' will load the home/layout.dashboard.php file.
     * You can also provide no particular engine, and the manager will decide what template to load.
     * Remember that doing so will result in a LayoutException when multiple compatible files are found.
     *
     * @param string $file      File to load
     * @param string $directory Directory to load it from
     *
     * @return string The output of the template
     *
     * @throws LayoutException On error
     */
    public function get($file, $directory = null): string
    {
        $directory = (is_null($directory) ? $this->directory : $directory);
        Logger::newLevel("Loading template file '".$file."' in '".$directory."'");

        // First load the template engines
        $this->loadTemplateEngines();

        // First retrieve the filepath
        if (is_null($this->current_engine)) {
            $this->setFileFromString($file, $directory, array_keys($this->file_extensions));
        } else {
            $this->setFileFromString($file, $directory, $this->current_engine->getFileExtensions());
        }

        // Then assign some basic variables for the template
        $main_config = Factory::getInstance()->config->get('main');
        $contact_config = Factory::getInstance()->config->get('contact');
        $this->assigned_variables['wwwDir'] = $main_config->base_url;
        $this->assigned_variables['siteURL'] = $main_config->base_url;
        $this->assigned_variables['serverName'] = $main_config->server_name;
        $this->assigned_variables['adminMail'] = $main_config->administrator_mail;
        $this->assigned_variables['contact'] = $contact_config->toArray();

        // Select an engine if one is not already selected
        if (is_null($this->current_engine)) {
            $this->current_engine = $this->getEngineFromExtension($this->getExtensionFromFile($this->file));
        }

        $this->current_engine->setDirectory($this->directory);

        // And run an Event to see what other parts have to say about it
        $event = Events::fireEvent('layoutLoadEvent', $this->file, $this->directory, $this->current_engine, $this->assigned_variables);

        // The event has been cancelled
        if ($event->isCancelled()) {
            return false;
        }

        // And refetch the data from the event
        $this->current_engine = $event->engine;
        $this->assigned_variables = $event->assigned_variables;

        Logger::stopLevel();

        // And finally run it
        if (file_exists($event->file)) {
            return $this->current_engine->get($event->file, $this->assigned_variables);
        }

        throw new LayoutException('The requested file was not found', 1);
    }

    /**
     * Retrieve a Template Engine from a File Extension.
     *
     * @param string $extension File extention to look for
     *
     * @return TemplateEngine
     */
    public function getEngineFromExtension($extension): TemplateEngine
    {
        if (isset($this->file_extensions[strtolower($extension)])) {
            return $this->engines[ $this->file_extensions[strtolower($extension)]];
        }

        throw new LayoutException('Could not get Template Engine. No engine has corresponding file extension', 1);
    }

    /**
     * Retrieve the extension from a file string.
     *
     * @param string $fileString The path to the file
     *
     * @return string Extension of the file
     */
    public function getExtensionFromFile($fileString): string
    {
        return substr($fileString, strrpos($fileString, '.') + 1);
    }

    /**
     * Converts a layout string to a file using the directory and the used extensions.
     *
     * It will detect whether the file exists and choose a file according to the provided extensions
     *
     * @param string $string     The string used by a controller. eg: 'dashboard/home'
     * @param string $directory  The directory to search in for the template
     * @param array  $extensions Extensions to use for this template. Eg array('php', 'tpl') etc.
     *
     * @return string Filepath of the template
     * @throws LayoutException On error
     */
    public function getFileFromString($string, $directory, $extensions = array()): string
    {
        $directory = preg_replace('#/+#', '/', (!is_null($directory) ? $directory : $this->directory).DS);

        if (strpbrk($directory, "\\/?%*:|\"<>") === TRUE || strpbrk($string, "\\/?%*:|\"<>") === TRUE)
        {
            throw new LayoutException('Could not get file. Invalid file string', 1);
        }

        if (!file_exists($directory)) {
            throw new LayoutException('Could not get file. Directory does not exist', 1);
        }

        // Set the file name and location
        $layoutSelector = explode('/', $string);
        if (count($layoutSelector) == 1) {
            $layoutSelector = 'layout.'.$layoutSelector[0];
        } else {
            // Get last file
            $file = end($layoutSelector);

            // Reset to start
            reset($layoutSelector);

            // Remove last value
            array_pop($layoutSelector);

            $layoutSelector[] = 'layout.'.$file;

            // And create the final value
            $layoutSelector = implode(DS, $layoutSelector);
        }

        // Then try and select a file
        $fileSelected = false;
        $selectedFile = null;
        foreach ($extensions as $extension) {
            $file = $directory.$layoutSelector.'.'.strtolower($extension);
            $file = preg_replace('#/+#', '/', $file);
            if (file_exists($file) && !$fileSelected) {
                $selectedFile = $file;
                $fileSelected = true;
                Logger::log("Found matching file: '".$file."'");
            } elseif (file_exists($file) && $fileSelected) {
                throw new LayoutException('Could not select template. Multiple valid extensions detected. Can not choose.', 1);
            }
        }

        // And choose what to output
        if (!$fileSelected) {
            throw new LayoutException('Could not select template. No matching file found.');
        }

        return $selectedFile;
    }

    /**
     * Converts a layout string to a file using the directory and the used extensions.
     * It also sets the file variable of this class.
     *
     * It will detect whether the file exists and choose a file according to the provided extensions
     *
     * @param string $string     The string used by a controller. eg: 'dashboard/home'
     * @param string $directory  The directory to search in for the template
     * @param array  $extensions Extensions to use for this template. Eg array('php', 'tpl') etc.
     *
     * @return string Filepath of the template
     * @throws LayoutException On error
     */
    public function setFileFromString($string, $directory, $extensions = array())
    {
        $this->file = $this->getFileFromString($string, $directory, $extensions);
        $this->directory = preg_replace('#/+#', '/', (!is_null($directory) ? $directory : $this->directory).DS);
    }

    /**
     * Get the current file to be loaded.
     *
     * @return null|string Path to the file
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set the file to be loaded.
     *
     * @param string $file Path to the file
     */
    public function setFile($file): string
    {
        $this->file = $file;
    }

    /**
     * Get the directory of the file to be loaded.
     *
     * @return null|string Path to the directory
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Set the directory of the file to be loaded.
     *
     * @param string $directory Path to the directory
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    /**
     * Assign a variable for the template.
     *
     * @param string $key   Key of the variable
     * @param mixed  $value Value of the variable
     */
    public function assign($key, $value)
    {
        $this->assigned_variables[$key] = $value;
    }

    /**
     * Set the title of the template.
     *
     * @param string $title title of the template
     */
    public function setTitle($title)
    {
        $this->assigned_variables['title'] = $title;
    }

    /**
     * Get the title of the template.
     *
     * @return string|bool title of the template
     */
    public function getTitle()
    {
        if (!isset($this->assigned_variables['title']))
        {
            return false;
        }
        return $this->assigned_variables['title'];
    }

    /**
     * Set the engine for the next layout.
     *
     * @param string $name Name of the template engine
     *
     * @return bool true on success
     * @throws LayoutException on error
     */
    public function setEngine($name): bool
    {
        $this->loadTemplateEngines();
        if (isset($this->engines[$name])) {
            $this->current_engine = $this->engines[$name];
            Logger::log('Set the Template Engine to '.$name);

            return true;
        }
        throw new LayoutException('Could not set engine. Engine does not exist', 1);
    }

    /**
     * Get a loaded template engine.
     *
     * @param string $name Name of the template engine
     *
     * @return TemplateEngine
     */
    public function getEngine($name): TemplateEngine
    {
        $this->loadTemplateEngines();
        if (isset($this->engines[$name])) {
            return $this->engines[$name];
        }
        throw new LayoutException('Could not return engine. Engine does not exist', 1);
    }

    /**
     * Register a new template engine.
     *
     * @param object $engineClass          Object that implements the \FuzeWorks\TemplateEngine
     * @param string $engineName           Name of the template engine
     * @param array  $engineFileExtensions File extensions this template engine should be used for
     *
     * @return bool true on success
     * @throws LayoutException
     */
    public function registerEngine($engineClass, $engineName, $engineFileExtensions = array()): bool
    {
        // First check if the engine already exists
        if (isset($this->engines[$engineName])) {
            throw new LayoutException("Could not register engine. Engine '".$engineName."' already registered", 1);
        }

        // Then check if the object is correct
        if ($engineClass instanceof TemplateEngine) {
            // Install it
            $this->engines[$engineName] = $engineClass;

            // Then define for what file extensions this Template Engine will work
            if (!is_array($engineFileExtensions)) {
                throw new LayoutException('Could not register engine. File extensions must be an array', 1);
            }

            // Then install them
            foreach ($engineFileExtensions as $extension) {
                if (isset($this->file_extensions[strtolower($extension)])) {
                    throw new LayoutException('Could not register engine. File extension already bound to engine', 1);
                }

                // And add it
                $this->file_extensions[strtolower($extension)] = $engineName;
            }

            // And log it
            Logger::log('Registered Template Engine: '.$engineName);

            return true;
        }

        throw new LayoutException("Could not register engine. Engine must implement \FuzeWorks\TemplateEngine", 1);
    }

    /**
     * Load the template engines by sending a layoutLoadEngineEvent.
     */
    public function loadTemplateEngines()
    {
        if (!$this->engines_loaded) {
            Events::fireEvent('layoutLoadEngineEvent');

            // Load the engines provided in this file
            $this->registerEngine(new PHPEngine(), 'PHP', array('php'));
            $this->registerEngine(new JsonEngine(), 'JSON', array('json'));
            $this->registerEngine(new SmartyEngine(), 'Smarty', array('tpl'));
            $this->registerEngine(new LatteEngine(), 'Latte', array('latte'));
            $this->engines_loaded = true;
        }
    }

    /**
     * Calls a function in the current Template engine.
     *
     * @param string     $name   Name of the function to be called
     * @param mixed      $params Parameters to be used
     *
     * @return mixed Function output
     */
    public static function __callStatic($name, $params)
    {
        // First load the template engines
        $this->loadTemplateEngines();

        if (!is_null($this->current_engine)) {
            // Call user func array here
            return call_user_func_array(array($this->current_engine, $name), $params);
        }
        throw new LayoutException('Could not access Engine. Engine not loaded', 1);
    }

    /**
     * Resets the layout manager to its default state.
     */
    public function reset()
    {
        if (!is_null($this->current_engine)) {
            $this->current_engine->reset();
        }

        // Unload the engines
        $this->engines = array();
        $this->engines_loaded = false;
        $this->file_extensions = array();

        $this->current_engine = null;
        $this->assigned_variables = array();
        $this->directory = Core::$appDir . DS . 'Layout';
        Logger::log('Reset the layout manager to its default state');
    }
}