<?php
/**
 * FuzeWorks Framework Core.
 *
 * The FuzeWorks PHP FrameWork
 *
 * Copyright (C) 2013-2019 TechFuze
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
 * @copyright Copyright (c) 2013 - 2019, TechFuze. (http://techfuze.net)
 * @license   https://opensource.org/licenses/MIT MIT License
 *
 * @link  http://techfuze.net/fuzeworks
 * @since Version 1.1.1
 *
 * @version Version 1.2.0
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
