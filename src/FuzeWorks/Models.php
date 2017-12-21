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
use FuzeWorks\Exception\ModelException;

/**
 * Models Class.
 *
 * Simple loader class for MVC Models. 
 * Typically loads models from Application/Models unless otherwise specified.
 * 
 * If a model is not found, it will load a DatabaseModel type which will 
 * analyze the database and can directly be used.
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 */
class Models
{

    /**
     * Paths where Models can be found. 
     * 
     * Models will only be loaded if either a directory is supplied or it is in one of the modelPaths
     * 
     * @var array Array of paths where models can be found
     */
    protected $modelPaths = array();

    public function __construct()
    {
        $this->modelPaths[] = Core::$appDir . DS . 'Models';
    }

    /**
     * Get a model.
     * 
     * Supply the name and the model will be loaded from the supplied directory,
     * or from one of the modelPaths (which you can add).
     * 
     * @param string        $modelName  Name of the model
     * @param string|null   $directory  Directory to load the model from, will ignore $modelPaths
     * @return ModelAbstract|bool       The Model object
     */
    public function get($modelName, $directory = null)
    {
        if (empty($modelName))
        {
            throw new ModelException("Could not load model. No name provided", 1);
        }

        // First get the directories where the model can be located
        $directories = (is_null($directory) ? $this->modelPaths : array($directory));

        // Fire a model load event
        $event = Events::fireEvent('modelLoadEvent', $modelName, $directories);
        $directories = $event->directories;
        $modelName = $event->modelName;

        // If the event is cancelled, stop loading
        if ($event->isCancelled())
        {
            return false;
        }

        // And attempt to load the model
        return $this->loadModel($modelName, $directories);
    }

    /**
     * Load and return a model.
     * 
     * Supply the name and the model will be loaded from one of the supplied directories
     * 
     * @param string        $modelName   Name of the model
     * @param array         $directories Directories to try and load the model from
     * @return ModelAbstract             The Model object
     */
    protected function loadModel($modelName, $directories): ModelAbstract
    {
        if (empty($directories))
        {
            throw new ModelException("Could not load model. No directories provided", 1);
        }

        // Now figure out the className and subdir
        $class = trim($modelName, '/');
        if (($last_slash = strrpos($class, '/')) !== FALSE)
        {
            // Extract the path
            $subdir = substr($class, 0, ++$last_slash);

            // Get the filename from the path
            $class = substr($class, $last_slash);
        }
        else
        {
            $subdir = '';
        }

        $class = ucfirst($class);

        // Search for the model file
        foreach ($directories as $directory) {

            // Determine the file
            $file = $directory . DS . $subdir . "model." . strtolower($class) . '.php';
            $className = '\Application\Model\\'.$class;

            // If the class already exists, return a new instance directly
            if (class_exists($className, false))
            {
                return new $className();
            }

            // If it doesn't, try and load the file
            if (file_exists($file))
            {
                include_once($file);
                return new $className();
            }
        }

        // Maybe it's in a subdirectory with the same name as the class
        if ($subdir === '')
        {
            return $this->loadModel($class."/".$class, $directories);
        }

        throw new ModelException("Could not load model. Model was not found", 1);
    }

    /**
     * Add a path where models can be found
     * 
     * @param string $directory The directory
     * @return void
     */
    public function addModelPath($directory): void
    {
        if (!in_array($directory, $this->ModelPaths))
        {
            $this->modelPaths[] = $directory;
        }
    }

    /**
     * Remove a path where models can be found
     * 
     * @param string $directory The directory
     * @return void
     */    
    public function removeModelPath($directory): void
    {
        if (($key = array_search($directory, $this->modelPaths)) !== false) 
        {
            unset($this->modelPaths[$key]);
        }
    }

    /**
     * Get a list of all current ModelPaths
     * 
     * @return array Array of paths where models can be found
     */
    public function getModelPaths(): array
    {
        return $this->modelPaths;
    }
}