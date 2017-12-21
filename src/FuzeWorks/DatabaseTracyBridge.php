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
 * @since Version 1.0.4
 *
 * @version Version 1.0.4
 */

namespace FuzeWorks;
use Tracy\IBarPanel;
use Tracy\Debugger;

/**
 * DatabaseTracyBridge Class.
 *
 * This class provides a bridge between FuzeWorks\Database and Tracy Debugging tool.
 * 
 * This class registers in Tracy, and creates a Bar object which contains information about database sessions. 
 * It hooks into database usage and provides the information on the Tracy Bar panel. 
 *
 * @author    Abel Hoogeveen <abel@techfuze.net>
 * @copyright Copyright (c) 2013 - 2017, Techfuze. (http://techfuze.net)
 */
class DatabaseTracyBridge implements IBarPanel
{

	public static $databases = array();
	protected $results = array();

	public static function register()
	{
		$class = new self();
		$bar = Debugger::getBar();
		$bar->addPanel($class);
	}

	public static function registerDatabase($database)
	{
		self::$databases[] = $database;
	}

	protected function getResults(): array
	{
		if (!empty($this->results))
		{
			return $this->results;
		}

		// First prepare global variables
		$results = array();
		$results['dbCount'] = 0;
		$results['queryCount'] = 0;
		$results['queryTimings'] = 0.0;
		$results['errorsFound'] = false;

		// Go through all databases
		foreach (self::$databases as $database) {
			// Increase total databases
			$results['dbCount']++;

			// First determine the ID
			if (!empty($database->dsn))
			{
				$databaseId = $database->dsn;
			}
			elseif (!empty($database->username) && !empty($database->database) && !empty($database->hostname))
			{
				$databaseId = $database->username . '@' . $database->hostname . '/' . $database->database;
			}
			else
			{
				$databaseId = spl_object_hash($database);
			}

			// Go through all queries
			foreach ($database->queries as $key => $query) {
				$results['queryCount']++;
				$results['queryTimings'] += $database->query_times[$key];
				$results['queries'][$databaseId][$key]['query'] = $query;
				$results['queries'][$databaseId][$key]['timings'] = $database->query_times[$key];
				$results['queries'][$databaseId][$key]['data'] = $database->query_data[$key];

				// If errors are found, set this at the top of the array
				if ($database->query_data[$key]['error']['code'] != 0)
				{
					$results['errorsFound'] = true;
				}
			}
		}

		// Limit the amount in order to keep things readable
		$results['queryCountProvided'] = 0;
		foreach ($results['queries'] as $id => $database) {
			$results['queries'][$id] = array_reverse(array_slice($database, -10));
			$results['queryCountProvided'] += count($results['queries'][$id]);
		}
		$results = array_slice($results, -10);

		return $this->results = $results;
	}

	public function getTab(): string
	{
		$results = $this->getResults();
		ob_start(function () {});
		require dirname(__DIR__) . DS . 'Layout' . DS . 'layout.tracydatabasetab.php';
		return ob_get_clean();
	}

	public function getPanel(): string
	{
		// Parse the panel
		$results = $this->getResults();
		ob_start(function () {});
		require dirname(__DIR__) . DS . 'Layout' . DS . 'layout.tracydatabasepanel.php';
		return ob_get_clean();
	}
}