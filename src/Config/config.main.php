<?php
/**
 * FuzeWorks Application Skeleton.
 *
 * The FuzeWorks MVC PHP FrameWork
 *
 * Copyright (C) 2016   TechFuze
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
 * @since Version 1.1.1
 *
 * @version Version 1.1.1
 */

return array(
  'base_url' => '',
  'index_page' => 'index.php',
  'server_name' => '',

  'administrator_mail' => '',

  'default_controller' => 'standard',
  'default_function' => 'index',
  'application_prefix' => 'MY_',

  'charset' => 'UTF-8',
  'language' => 'english',

  /*
  |--------------------------------------------------------------------------
  | Cookie Related Variables
  |--------------------------------------------------------------------------
  |
  | 'cookie_prefix'   = Set a cookie name prefix if you need to avoid collisions
  | 'cookie_domain'   = Set to .your-domain.com for site-wide cookies
  | 'cookie_path'     = Typically will be a forward slash
  | 'cookie_secure'   = Cookie will only be set if a secure HTTPS connection exists.
  | 'cookie_httponly' = Cookie will only be accessible via HTTP(S) (no javascript)
  |
  | Note: These settings (with the exception of 'cookie_prefix' and
  |       'cookie_httponly') will also affect sessions.
  |
  */
  'cookie_prefix' => '',
  'cookie_domain' => '',
  'cookie_path' => '/',
  'cookie_secure' => FALSE,
  'cookie_httponly' => FALSE,

  /*
  |--------------------------------------------------------------------------
  | Output Compression
  |--------------------------------------------------------------------------
  |
  | Enables Gzip output compression for faster page loads.  When enabled,
  | the output class will test whether your server supports Gzip.
  | Even if it does, however, not all browsers support compression
  | so enable only if you are reasonably sure your visitors can handle it.
  |
  | Only used if zlib.output_compression is turned off in your php.ini.
  | Please do not use it together with httpd-level output compression.
  |
  | VERY IMPORTANT:  If you are getting a blank page when compression is enabled it
  | means you are prematurely outputting something to your browser. It could
  | even be a line of whitespace at the end of one of your scripts.  For
  | compression to work, nothing can be sent before the output buffer is called
  | by the output class.  Do not 'echo' any values with compression enabled.
  |
  */
  'compress_output' => FALSE,
);
