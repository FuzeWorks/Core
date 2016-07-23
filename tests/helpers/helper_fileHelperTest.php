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
 * @author      TechFuze
 * @copyright   Copyright (c) 2013 - 2016, Techfuze. (http://techfuze.net)
 * @copyright   Copyright (c) 1996 - 2015, Free Software Foundation, Inc. (http://www.fsf.org/)
 * @license     http://opensource.org/licenses/GPL-3.0 GPLv3 License
 *
 * @link        http://techfuze.net/fuzeworks
 * @since       Version 0.0.1
 *
 * @version     Version 1.0.0
 */

use FuzeWorks\Factory;

/**
 * Class FileHelperTest.
 *
 * Helpers testing suite, will test specific helper
 */
class fileHelperTest extends CoreTestAbstract
{
    public function setUp()
    {
        // Load Helper
        Factory::getInstance()->helpers->load('file');

		vfsStreamWrapper::register();
		vfsStreamWrapper::setRoot(new vfsStreamDirectory('testDir'));

		$this->_test_dir = vfsStreamWrapper::getRoot();
    }

	// --------------------------------------------------------------------

	public function test_read_file()
	{
		$this->assertFalse(read_file('does_not_exist'));

		$content = 'Jack and Jill went up the mountain to fight a billy goat.';

		$file = vfsStream::newFile('my_file.txt')->withContent($content)->at($this->_test_dir);

		$this->assertEquals($content, read_file(vfsStream::url('my_file.txt')));
	}

	// --------------------------------------------------------------------

	public function test_octal_permissions()
	{
		$content = 'Jack and Jill went up the mountain to fight a billy goat.';

		$file = vfsStream::newFile('my_file.txt', 0777)
			->withContent($content)
			->lastModified(time() - 86400)
			->at($this->_test_dir);

		$this->assertEquals('777', octal_permissions($file->getPermissions()));
	}

	// --------------------------------------------------------------------

	/**
	 * More tests should happen here, since I'm not hitting the whole function.
	 */
	public function test_symbolic_permissions()
	{
		$content = 'Jack and Jill went up the mountain to fight a billy goat.';

		$file = vfsStream::newFile('my_file.txt', 0777)
			->withContent($content)
			->lastModified(time() - 86400)
			->at($this->_test_dir);

		$this->assertEquals('urwxrwxrwx', symbolic_permissions($file->getPermissions()));
	}

	// --------------------------------------------------------------------

	public function test_get_mime_by_extension()
	{
		$content = 'Jack and Jill went up the mountain to fight a billy goat.';

		$file = vfsStream::newFile('my_file.txt', 0777)
			->withContent($content)
			->lastModified(time() - 86400)
			->at($this->_test_dir);

		$this->assertEquals('text/plain', get_mime_by_extension(vfsStream::url('my_file.txt')));

		// Test a mime with an array, such as png
		$file = vfsStream::newFile('foo.png')->at($this->_test_dir);

		$this->assertEquals('image/png', get_mime_by_extension(vfsStream::url('foo.png')));

		// Test a file not in the mimes array
		$file = vfsStream::newFile('foo.blarfengar')->at($this->_test_dir);

		$this->assertFalse(get_mime_by_extension(vfsStream::url('foo.blarfengar')));
	}

	// --------------------------------------------------------------------

	public function test_get_file_info()
	{
		// Test Bad File
		$this->assertFalse(get_file_info('i_am_bad_boo'));

		// Test the rest

		// First pass in an array
		$vals = array(
			'name', 'server_path', 'size', 'date',
			'readable', 'writable', 'executable', 'fileperms'
		);

		$this->_test_get_file_info($vals);

		// Test passing in vals as a string.
		$this->_test_get_file_info(implode(', ', $vals));
	}

	private function _test_get_file_info($vals)
	{
		$content = 'Jack and Jill went up the mountain to fight a billy goat.';
		$last_modified = time() - 86400;

		$file = vfsStream::newFile('my_file.txt', 0777)
			->withContent($content)
			->lastModified($last_modified)
			->at($this->_test_dir);

		$ret_values = array(
			'name'        => 'my_file.txt',
			'server_path' => 'vfs://my_file.txt',
			'size'        => 57,
			'date'        => $last_modified,
			'readable'    => TRUE,
			'writable'    => TRUE,
			'executable'  => TRUE,
			'fileperms'   => 33279
		);

		$info = get_file_info(vfsStream::url('my_file.txt'), $vals);

		foreach ($info as $k => $v)
		{
			$this->assertEquals($ret_values[$k], $v);
		}
	}

	// --------------------------------------------------------------------

	 public function test_write_file()
	 {
		$content = 'Jack and Jill went up the mountain to fight a billy goat.';

		$file = vfsStream::newFile('write.txt', 0777)
			->withContent('')
			->lastModified(time() - 86400)
			->at($this->_test_dir);

		$this->assertTrue(write_file(vfsStream::url('write.txt'), $content));
	 }

}
