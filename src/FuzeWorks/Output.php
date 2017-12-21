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

/**
 * Output Class
 *
 * Responsible for sending final output to the browser.
 *
 * @author		EllisLab Dev Team
 * @copyright   Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright   Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 */
class Output {

	/**
	 * Final output string
	 *
	 * @var	string
	 */
	public $final_output;

	/**
	 * Cache expiration time
	 *
	 * @var	int
	 */
	public $cache_expiration = 0;

	/**
	 * List of server headers
	 *
	 * @var	array
	 */
	public $headers = array();

	/**
	 * List of mime types
	 *
	 * @var	array
	 */
	public $mimes =	array();

	/**
	 * Mime-type for the current page
	 *
	 * @var	string
	 */
	protected $mime_type = 'text/html';

	/**
	 * Enable Profiler flag
	 *
	 * @var	bool
	 */
	public $enable_profiler = FALSE;

	/**
	 * php.ini zlib.output_compression flag
	 *
	 * @var	bool
	 */
	protected $_zlib_oc = FALSE;

	/**
	 * CI output compression flag
	 *
	 * @var	bool
	 */
	protected $_compress_output = FALSE;

	/**
	 * List of profiler sections
	 *
	 * @var	array
	 */
	protected $_profiler_sections =	array();

	/**
	 * Parse markers flag
	 *
	 * Whether or not to parse variables like {elapsed_time} and {memory_usage}.
	 *
	 * @var	bool
	 */
	public $parse_exec_vars = TRUE;
    
	/**
	 * Factory Object
	 * @var Factory
	 */
	protected $factory;
	protected $config;
	protected $uri;
	protected $router;

	/**
	 * Class constructor
	 *
	 * Determines whether zLib output compression will be used.
	 *
	 * @return	void
	 */
	public function __construct()
	{
		$this->factory = Factory::getInstance();
		$this->config = $this->factory->config;
		$this->uri = $this->factory->uri;

		$this->_zlib_oc = (bool) ini_get('zlib.output_compression');
		$this->_compress_output = (
			$this->_zlib_oc === FALSE
			&& $this->config->main->compress_output === TRUE
			&& extension_loaded('zlib')
		);

		// Get mime types for later
		$this->mimes = $this->config->mimes->toArray();
	}

	// --------------------------------------------------------------------

	/**
	 * Get Output
	 *
	 * Returns the current output string.
	 *
	 * @return	string
	 */
	public function get_output(): string
	{
		return $this->final_output;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Output
	 *
	 * Sets the output string.
	 *
	 * @param	string	$output	Output data
	 * @return	self
	 */
	public function set_output($output): self
	{
		$this->final_output = $output;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Append Output
	 *
	 * Appends data onto the output string.
	 *
	 * @param	string	$output	Data to append
	 * @return	self
	 */
	public function append_output($output): self
	{
		$this->final_output .= $output;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Header
	 *
	 * Lets you set a server header which will be sent with the final output.
	 *
	 * Note: If a file is cached, headers will not be sent.
	 * @todo	We need to figure out how to permit headers to be cached.
	 *
	 * @param	string	$header		Header
	 * @param	bool	$replace	Whether to replace the old header value, if already set
	 * @return	self
	 */
	public function set_header($header, $replace = TRUE): self
	{
		// If zlib.output_compression is enabled it will compress the output,
		// but it will not modify the content-length header to compensate for
		// the reduction, causing the browser to hang waiting for more data.
		// We'll just skip content-length in those cases.
		if ($this->_zlib_oc && strncasecmp($header, 'content-length', 14) === 0)
		{
			return $this;
		}

		$this->headers[] = array($header, $replace);
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Content-Type Header
	 *
	 * @param	string	$mime_type	Extension of the file we're outputting
	 * @param	string	$charset	Character set (default: NULL)
	 * @return	self
	 */
	public function set_content_type($mime_type, $charset = NULL): self
	{
		if (strpos($mime_type, '/') === FALSE)
		{
			$extension = ltrim($mime_type, '.');

			// Is this extension supported?
			if (isset($this->mimes[$extension]))
			{
				$mime_type =& $this->mimes[$extension];

				if (is_array($mime_type))
				{
					$mime_type = current($mime_type);
				}
			}
		}

		$this->mime_type = $mime_type;

		if (empty($charset))
		{
			$charset = Config::get('main')->charset;
		}

		$header = 'Content-Type: '.$mime_type
			.(empty($charset) ? '' : '; charset='.$charset);

		$this->headers[] = array($header, TRUE);
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Current Content-Type Header
	 *
	 * @return	string	'text/html', if not already set
	 */
	public function get_content_type(): string
	{
		for ($i = 0, $c = count($this->headers); $i < $c; $i++)
		{
			if (sscanf($this->headers[$i][0], 'Content-Type: %[^;]', $content_type) === 1)
			{
				return $content_type;
			}
		}

		return 'text/html';
	}

	// --------------------------------------------------------------------

	/**
	 * Get Header
	 *
	 * @param	string	$header_name
	 * @return	string|null
	 */
	public function get_header($header)
	{
		// Combine headers already sent with our batched headers
		$headers = array_merge(
			// We only need [x][0] from our multi-dimensional array
			array_map('array_shift', $this->headers),
			headers_list()
		);

		if (empty($headers) OR empty($header))
		{
			return NULL;
		}

		for ($i = 0, $c = count($headers); $i < $c; $i++)
		{
			if (strncasecmp($header, $headers[$i], $l = strlen($header)) === 0)
			{
				return trim(substr($headers[$i], $l+1));
			}
		}

		return NULL;
	}

	// --------------------------------------------------------------------

	/**
	 * Set HTTP Status Header
	 *
	 * As of version 1.7.2, this is an alias for common function
	 * set_status_header().
	 *
	 * @param	int	$code	Status code (default: 200)
	 * @param	string	$text	Optional message
	 * @return	self
	 */
	public function set_status_header($code = 200, $text = ''): self
	{
		Core::setStatusHeader($code, $text);
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Enable/disable Profiler
	 *
	 * @param	bool	$val	TRUE to enable or FALSE to disable
	 * @return	self
	 */
	public function enable_profiler($val = TRUE): self
	{
		$this->enable_profiler = is_bool($val) ? $val : TRUE;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Profiler Sections
	 *
	 * Allows override of default/config settings for
	 * Profiler section display.
	 *
	 * @param	array	$sections	Profiler sections
	 * @return	self
	 */
	public function set_profiler_sections($sections): self
	{
		if (isset($sections['query_toggle_count']))
		{
			$this->_profiler_sections['query_toggle_count'] = (int) $sections['query_toggle_count'];
			unset($sections['query_toggle_count']);
		}

		foreach ($sections as $section => $enable)
		{
			$this->_profiler_sections[$section] = ($enable !== FALSE);
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Cache
	 *
	 * @param	int	$time	Cache expiration time in minutes
	 * @return	self
	 */
	public function cache($time): self
	{
		$this->cache_expiration = is_numeric($time) ? $time : 0;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Display Output
	 *
	 * Processes and sends finalized output data to the browser along
	 * with any server headers and profile data. It also stops benchmark
	 * timers so the page rendering speed and memory usage can be shown.
	 *
	 * Note: All "layout" data is automatically put into $this->final_output
	 *	 by controller class.
	 *
	 * @uses	Output::$final_output
	 * @param	string	$output	Output data override
	 * @return	void
	 */
	public function _display($output = '')
	{
		$router = $this->factory->router;
		// Grab the super object if we can.
		if ($router->getCallable() === null)
		{
			$use_cache = true;
		}
		else
		{
			$use_cache = false;
		}

		// --------------------------------------------------------------------

		// Set the output data
		if ($output === '')
		{
			$output =& $this->final_output;
		}

		// --------------------------------------------------------------------

		// Do we need to write a cache file? Only if the controller does not have its
		// own _output() method and we are not dealing with a cache file, which we
		// can determine by the existence of the $CI object above
		if ($this->cache_expiration > 0 && $use_cache === false)
		{
			$this->_write_cache($output);
		}

		// --------------------------------------------------------------------

		if ($this->parse_exec_vars === TRUE)
		{
			$memory	= round(memory_get_usage() / 1024 / 1024, 2).'MB';
			$output = str_replace(array('{memory_usage}'), array($memory), $output);
		}

		// --------------------------------------------------------------------

		// Is compression requested?
		if ($use_cache === false // This means that we're not serving a cache file, if we were, it would already be compressed
			&& $this->_compress_output === TRUE
			&& isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE)
		{
			ob_start('ob_gzhandler');
		}

		// --------------------------------------------------------------------

		// Are there any server headers to send?
		if (count($this->headers) > 0)
		{
			foreach ($this->headers as $header)
			{
				@header($header[0], $header[1]);
			}
		}

		// --------------------------------------------------------------------

		if ( $use_cache === true)
		{
			if ($this->_compress_output === TRUE)
			{
				if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE)
				{
					header('Content-Encoding: gzip');
					header('Content-Length: '.strlen($output));
				}
				else
				{
					// User agent doesn't support gzip compression,
					// so we'll have to decompress our cache
					$output = gzinflate(substr($output, 10, -8));
				}
			}

			echo $output;
			Logger::log('Final output sent to browser');
			return;
		}

		// --------------------------------------------------------------------

		// Do we need to generate profile data?
		// If so, load the Profile class and run it.
		if ($this->enable_profiler === TRUE)
		{
			Logger::logWarning("Profiler not yet implemented");
			return;

			$CI->load->library('profiler');
			if ( ! empty($this->_profiler_sections))
			{
				$CI->profiler->set_sections($this->_profiler_sections);
			}

			// If the output data contains closing </body> and </html> tags
			// we will remove them and add them back after we insert the profile data
			$output = preg_replace('|</body>.*?</html>|is', '', $output, -1, $count).$CI->profiler->run();
			if ($count > 0)
			{
				$output .= '</body></html>';
			}
		}

		echo $output;

		Logger::log('Output sent to browser');
	}

	// --------------------------------------------------------------------

	/**
	 * Write Cache
	 *
	 * @param	string	$output	Output data to cache
	 * @return	void
	 */
	public function _write_cache($output)
	{
		$cache_path = Core::$tempDir . DS . 'Output' . DS;

		// First try and see if the directory exists
		if ( ! is_dir($cache_path))
		{
			// Then try and create the directory
			if (!mkdir($cache_path, 0777, false))
			{
				Logger::logError('Unable to write cache file: \''.$cache_path.'\' Cannot create directory');
				return;
			}
		}

		if (! Core::isReallyWritable($cache_path))
		{
			Logger::logError('Unable to write cache file: \''.$cache_path.'\' Directory not writeable');
			return;
		}

		$uri = $this->config->main->base_url
			.$this->config->main->index_page
			.$this->uri->uri_string();

		if (($cache_query_string = $this->config->cache->cache_query_string) && ! empty($_SERVER['QUERY_STRING']))
		{
			if (is_array($cache_query_string))
			{
				$uri .= '?'.http_build_query(array_intersect_key($_GET, array_flip($cache_query_string)));
			}
			else
			{
				$uri .= '?'.$_SERVER['QUERY_STRING'];
			}
		}

		$cache_path .= md5($uri);

		if ( ! $fp = @fopen($cache_path, 'w+b'))
		{
			Logger::logError('Unable to write cache file: \''.$cache_path.'\' Directory not writeable');
			return;
		}

		if (flock($fp, LOCK_EX))
		{
			// If output compression is enabled, compress the cache
			// itself, so that we don't have to do that each time
			// we're serving it
			if ($this->_compress_output === TRUE)
			{
				$output = gzencode($output);

				if ($this->get_header('content-type') === NULL)
				{
					$this->set_content_type($this->mime_type);
				}
			}

			$expire = time() + ($this->cache_expiration * 60);

			// Put together our serialized info.
			$cache_info = serialize(array(
				'expire'	=> $expire,
				'headers'	=> $this->headers
			));

			$output = $cache_info.'ENDCI--->'.$output;

			for ($written = 0, $length = strlen($output); $written < $length; $written += $result)
			{
				if (($result = fwrite($fp, substr($output, $written))) === FALSE)
				{
					break;
				}
			}

			flock($fp, LOCK_UN);
		}
		else
		{
			Logger::logError('Unable to secure a file lock for file at: '.$cache_path);
			return;
		}

		fclose($fp);

		if (is_int($result))
		{
			chmod($cache_path, 0640);
			Logger::logDebug('Cache file written: '.$cache_path);

			// Send HTTP cache-control headers to browser to match file cache settings.
			$this->set_cache_header($_SERVER['REQUEST_TIME'], $expire);
		}
		else
		{
			@unlink($cache_path);
			Logger::logError('Unable to write the complete cache content at: '.$cache_path);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Update/serve cached output
	 *
	 * @uses	Config
	 * @uses	URI
	 *
	 * @return	bool	TRUE on success or FALSE on failure
	 */
	public function _display_cache(): bool
	{
		$cache_path = Core::$tempDir . DS . 'Output' . DS;

		// Build the file path. The file name is an MD5 hash of the full URI
		$main = $this->config->main;
		$uri = $main->base_url.$main->index_page.$this->uri->uri_string;

		if (($cache_query_string = $this->config->cache->cache_query_string) && ! empty($_SERVER['QUERY_STRING']))
		{
			if (is_array($cache_query_string))
			{
				$uri .= '?'.http_build_query(array_intersect_key($_GET, array_flip($cache_query_string)));
			}
			else
			{
				$uri .= '?'.$_SERVER['QUERY_STRING'];
			}
		}

		$filepath = $cache_path.md5($uri);

		if ( ! file_exists($filepath) OR ! $fp = @fopen($filepath, 'rb'))
		{
			Logger::logDebug("No cache file for $uri found");
			return FALSE;
		}

		flock($fp, LOCK_SH);

		$cache = (filesize($filepath) > 0) ? fread($fp, filesize($filepath)) : '';

		flock($fp, LOCK_UN);
		fclose($fp);

		// Look for embedded serialized file info.
		if ( ! preg_match('/^(.*)ENDCI--->/', $cache, $match))
		{
			return FALSE;
		}

		$cache_info = unserialize($match[1]);
		$expire = $cache_info['expire'];

		$last_modified = filemtime($filepath);

		// Has the file expired?
		if ($_SERVER['REQUEST_TIME'] >= $expire && Core::isReallyWritable($cache_path))
		{
			// If so we'll delete it.
			@unlink($filepath);
			Logger::logDebug('Cache file has expired. File deleted.');
			return FALSE;
		}
		else
		{
			// Or else send the HTTP cache control headers.
			$this->set_cache_header($last_modified, $expire);
		}

		// Add headers from cache file.
		foreach ($cache_info['headers'] as $header)
		{
			$this->set_header($header[0], $header[1]);
		}

		// Display the cache
		$this->_display(substr($cache, strlen($match[0])));
		Logger::logDebug('Cache file is current. Sending it to browser.');
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete cache
	 *
	 * @param	string	$uri	URI string
	 * @return	bool
	 */
	public function delete_cache($uri = ''): bool
	{
		$cache_path = Core::$tempDir . DS . 'Output' . DS;

		if ( ! is_dir($cache_path))
		{
			Logger::logError('Unable to find cache path: '.$cache_path);
			return FALSE;
		}

		if (empty($uri))
		{
			$uri = $this->uri->uri_string();

			if (($cache_query_string = $this->config->cache->cache_query_string) && ! empty($_SERVER['QUERY_STRING']))
			{
				if (is_array($cache_query_string))
				{
					$uri .= '?'.http_build_query(array_intersect_key($_GET, array_flip($cache_query_string)));
				}
				else
				{
					$uri .= '?'.$_SERVER['QUERY_STRING'];
				}
			}
		}

		$cache_path .= md5($this->config->mainbase_url.$this->config->main->index_page.ltrim($uri, '/'));

		if ( ! @unlink($cache_path))
		{
			Logger::logError('Unable to delete cache file for '.$uri);
			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Cache Header
	 *
	 * Set the HTTP headers to match the server-side file cache settings
	 * in order to reduce bandwidth.
	 *
	 * @param	int	$last_modified	Timestamp of when the page was last modified
	 * @param	int	$expiration	Timestamp of when should the requested page expire from cache
	 * @return	void
	 */
	public function set_cache_header($last_modified, $expiration)
	{
		$max_age = $expiration - $_SERVER['REQUEST_TIME'];

		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $last_modified <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{
			$this->set_status_header(304);
			exit;
		}
		else
		{
			header('Pragma: public');
			header('Cache-Control: max-age='.$max_age.', public');
			header('Expires: '.gmdate('D, d M Y H:i:s', $expiration).' GMT');
			header('Last-modified: '.gmdate('D, d M Y H:i:s', $last_modified).' GMT');
		}
	}

}
