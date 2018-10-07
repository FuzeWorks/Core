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
 * @since Version 1.2.0
 *
 * @version Version 1.2.0
 */

// This autoloader provide convinient way to working with mock object
// make the test looks natural. This autoloader support cascade file loading as well
// within mocks directory.
//
// Prototype :
//
// $mock_table = new Mock_Libraries_Table(); 			// Will load ./mocks/libraries/table.php
// $mock_database_driver = new Mock_Database_Driver();	// Will load ./mocks/database/driver.php
// and so on...
function autoload($class)
{
	$dir = realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR;

	$fw_core = array(
		'Benchmark',
		'Config',
		'Controller',
		'Exceptions',
		'Hooks',
		'Input',
		'Lang',
		'Loader',
		'Log',
		'Model',
		'Output',
		'Router',
		'Security',
		'URI',
		'Utf8'
	);

	$fw_libraries = array(
		'Calendar',
		'Cart',
		'Driver_Library',
		'Email',
		'Encrypt',
		'Encryption',
		'Form_validation',
		'Ftp',
		'Image_lib',
		'Javascript',
		'Migration',
		'Pagination',
		'Parser',
		'Profiler',
		'Table',
		'Trackback',
	   	'Typography',
		'Unit_test',
		'Upload',
	   	'User_agent',
		'Xmlrpc',
		'Zip'
	);

	$fw_drivers = array('Session', 'Cache');

	if (strpos($class, 'Mock_') === 0)
	{
		$class = strtolower(str_replace(array('Mock_', '_'), array('', DIRECTORY_SEPARATOR), $class));
	}
	elseif (strpos($class, 'FW_') === 0)
	{
		$subclass = substr($class, 3);

		if (in_array($subclass, $fw_core))
		{
			$dir = 'Core'.DIRECTORY_SEPARATOR.'System'.DIRECTORY_SEPARATOR;
			$class = $subclass;
		}
		elseif (in_array($subclass, $fw_libraries))
		{
			$dir = 'Core'.DIRECTORY_SEPARATOR.'Libraries'.DIRECTORY_SEPARATOR;
			$class = ($subclass === 'Driver_Library') ? 'Driver' : $subclass;
		}
		elseif (in_array($subclass, $fw_drivers))
		{
			$dir = 'Core'.DIRECTORY_SEPARATOR.'Libraries'.DIRECTORY_SEPARATOR.$subclass.DIRECTORY_SEPARATOR;
			$class = $subclass;
		}
		elseif (in_array(($parent = strtok($subclass, '_')), $fw_drivers)) {
			$dir = 'Core'.DIRECTORY_SEPARATOR.'Libraries'.DIRECTORY_SEPARATOR.$parent.DIRECTORY_SEPARATOR.'drivers'.DIRECTORY_SEPARATOR;
			$class = $subclass;
		}
		else
		{
			$class = strtolower($class);
		}
	}

	$file = isset($file) ? $file : $dir.$class.'.php';

	if ( ! file_exists($file))
	{
		return FALSE;
	}

	include_once($file);
}