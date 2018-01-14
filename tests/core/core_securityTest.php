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
 * Class SecurityTest.
 *
 * Core testing suite, will test security class functionality
 */
class securityTest extends CoreTestAbstract
{

    public function setUp()
    {
        // Set cookie for security test
        $_COOKIE['fw_csrf_cookie'] = md5(uniqid(mt_rand(), TRUE));

        // Set config for Security class
        $config = Factory::getInstance()->config->getConfig('security');

        $config->csrf_protection = true;
        $config->csrf_token_name = 'fw_csrf_token';
        $config->csrf_cookie_name = 'fw_csrf_cookie';

        $this->security = new Mock_Core_Security();
    }

    // --------------------------------------------------------------------

    public function test_csrf_verify()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->assertInstanceOf('FuzeWorks\Security', $this->security->csrf_verify());
    }

    // --------------------------------------------------------------------

    /**
     * @expectedException FuzeWorks\Exception\SecurityException
     */
    public function test_csrf_verify_invalid()
    {
        // Without issuing $_POST[csrf_token_name], this request will triggering CSRF error
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->security->csrf_verify();
    }

    // --------------------------------------------------------------------

    public function test_csrf_verify_valid()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST[$this->security->csrf_token_name] = $this->security->csrf_hash;

        $this->assertInstanceOf('FuzeWorks\Security', $this->security->csrf_verify());
    }

    // --------------------------------------------------------------------

    public function test_get_csrf_hash()
    {
        $this->assertEquals($this->security->csrf_hash, $this->security->get_csrf_hash());
    }

    // --------------------------------------------------------------------

    public function test_get_csrf_token_name()
    {
        $this->assertEquals('fw_csrf_token', $this->security->get_csrf_token_name());
    }

    // --------------------------------------------------------------------

    public function test_xss_clean()
    {
        $harm_string = "Hello, i try to <script>alert('Hack');</script> your site";

        $harmless_string = $this->security->xss_clean($harm_string);

        $this->assertEquals("Hello, i try to [removed]alert&#40;'Hack'&#41;;[removed] your site", $harmless_string);
    }

    // --------------------------------------------------------------------

    public function test_xss_clean_string_array()
    {
        $harm_strings = array(
            "Hello, i try to <script>alert('Hack');</script> your site",
            "Simple clean string",
            "Hello, i try to <script>alert('Hack');</script> your site"
        );

        $harmless_strings = $this->security->xss_clean($harm_strings);

        $this->assertEquals("Hello, i try to [removed]alert&#40;'Hack'&#41;;[removed] your site", $harmless_strings[0]);
        $this->assertEquals("Simple clean string", $harmless_strings[1]);
        $this->assertEquals("Hello, i try to [removed]alert&#40;'Hack'&#41;;[removed] your site", $harmless_strings[2]);
    }

    // --------------------------------------------------------------------

    public function test_xss_clean_image_valid()
    {
        $harm_string = '<img src="test.png">';

        $xss_clean_return = $this->security->xss_clean($harm_string, TRUE);

        $this->assertTrue($xss_clean_return);
    }

    // --------------------------------------------------------------------

    public function test_xss_clean_image_invalid()
    {
        $harm_string = '<img src=javascript:alert(String.fromCharCode(88,83,83))>';

        $xss_clean_return = $this->security->xss_clean($harm_string, TRUE);

        $this->assertFalse($xss_clean_return);
    }

    // --------------------------------------------------------------------

    public function test_xss_clean_entity_double_encoded()
    {
        $input = '<a href="&#38&#35&#49&#48&#54&#38&#35&#57&#55&#38&#35&#49&#49&#56&#38&#35&#57&#55&#38&#35&#49&#49&#53&#38&#35&#57&#57&#38&#35&#49&#49&#52&#38&#35&#49&#48&#53&#38&#35&#49&#49&#50&#38&#35&#49&#49&#54&#38&#35&#53&#56&#38&#35&#57&#57&#38&#35&#49&#49&#49&#38&#35&#49&#49&#48&#38&#35&#49&#48&#50&#38&#35&#49&#48&#53&#38&#35&#49&#49&#52&#38&#35&#49&#48&#57&#38&#35&#52&#48&#38&#35&#52&#57&#38&#35&#52&#49">Clickhere</a>';
        $this->assertEquals('<a>Clickhere</a>', $this->security->xss_clean($input));
    }

    // --------------------------------------------------------------------

    public function text_xss_clean_js_link_removal()
    {
        // This one is to prevent a false positive
        $this->assertEquals(
            "<a href=\"javascrip\n<t\n:alert\n&#40;1&#41;\"\n>",
            $this->security->xss_clean("<a href=\"javascrip\n<t\n:alert\n(1)\"\n>")
        );
    }

    // --------------------------------------------------------------------

    public function test_xss_clean_js_img_removal()
    {
        $input = '<img src="&#38&#35&#49&#48&#54&#38&#35&#57&#55&#38&#35&#49&#49&#56&#38&#35&#57&#55&#38&#35&#49&#49&#53&#38&#35&#57&#57&#38&#35&#49&#49&#52&#38&#35&#49&#48&#53&#38&#35&#49&#49&#50&#38&#35&#49&#49&#54&#38&#35&#53&#56&#38&#35&#57&#57&#38&#35&#49&#49&#49&#38&#35&#49&#49&#48&#38&#35&#49&#48&#50&#38&#35&#49&#48&#53&#38&#35&#49&#49&#52&#38&#35&#49&#48&#57&#38&#35&#52&#48&#38&#35&#52&#57&#38&#35&#52&#49">Clickhere';
        $this->assertEquals('<img>', $this->security->xss_clean($input));
    }

    // --------------------------------------------------------------------

    public function test_xss_clean_sanitize_naughty_html_tags()
    {
        $this->assertEquals('&lt;unclosedTag', $this->security->xss_clean('<unclosedTag'));
        $this->assertEquals('&lt;blink&gt;', $this->security->xss_clean('<blink>'));
        $this->assertEquals('<fubar>', $this->security->xss_clean('<fubar>'));

        $this->assertEquals(
            '<img svg=""> src="x">',
            $this->security->xss_clean('<img <svg=""> src="x">')
        );

        $this->assertEquals(
            '<img src="b on=">on=">"x onerror="alert&#40;1&#41;">',
            $this->security->xss_clean('<img src="b on="<x">on=">"x onerror="alert(1)">')
        );
    }

    // --------------------------------------------------------------------

    public function test_xss_clean_sanitize_naughty_html_attributes()
    {
        $this->assertEquals('<foo xss=removed>', $this->security->xss_clean('<foo onAttribute="bar">'));
        $this->assertEquals('<foo xss=removed>', $this->security->xss_clean('<foo onAttributeNoQuotes=bar>'));
        $this->assertEquals('<foo xss=removed>', $this->security->xss_clean('<foo onAttributeWithSpaces = bar>'));
        $this->assertEquals('<foo prefixOnAttribute="bar">', $this->security->xss_clean('<foo prefixOnAttribute="bar">'));
        $this->assertEquals('<foo>onOutsideOfTag=test</foo>', $this->security->xss_clean('<foo>onOutsideOfTag=test</foo>'));
        $this->assertEquals('onNoTagAtAll = true', $this->security->xss_clean('onNoTagAtAll = true'));
        $this->assertEquals('<foo xss=removed>', $this->security->xss_clean('<foo fscommand=case-insensitive>'));
        $this->assertEquals('<foo xss=removed>', $this->security->xss_clean('<foo seekSegmentTime=whatever>'));

        $this->assertEquals(
            '<foo bar=">" baz=\'>\' xss=removed>',
            $this->security->xss_clean('<foo bar=">" baz=\'>\' onAfterGreaterThan="quotes">')
        );
        $this->assertEquals(
            '<foo bar=">" baz=\'>\' xss=removed>',
            $this->security->xss_clean('<foo bar=">" baz=\'>\' onAfterGreaterThan=noQuotes>')
        );

        $this->assertEquals(
            '<img src="x" on=""> on=&lt;svg&gt; onerror=alert&#40;1&#41;>',
            $this->security->xss_clean('<img src="x" on=""> on=<svg> onerror=alert(1)>')
        );

        $this->assertEquals(
            '<img src="on=\'">"&lt;svg&gt; onerror=alert&#40;1&#41; onmouseover=alert&#40;1&#41;>',
            $this->security->xss_clean('<img src="on=\'">"<svg> onerror=alert(1) onmouseover=alert(1)>')
        );

        $this->assertEquals(
            '<img src="x"> on=\'x\' onerror=``,alert&#40;1&#41;>',
            $this->security->xss_clean('<img src="x"> on=\'x\' onerror=``,alert(1)>')
        );

        $this->assertEquals(
            '<a xss=removed>',
            $this->security->xss_clean('<a< onmouseover="alert(1)">')
        );

        $this->assertEquals(
            '<img src="x"> on=\'x\' onerror=,xssm()>',
            $this->security->xss_clean('<img src="x"> on=\'x\' onerror=,xssm()>')
        );

        $this->assertEquals(
            '<image src="<>" xss=removed>',
            $this->security->xss_clean('<image src="<>" onerror=\'alert(1)\'>')
        );

        $this->assertEquals(
            '<b xss=removed>',
            $this->security->xss_clean('<b "=<= onmouseover=alert(1)>')
        );

        $this->assertEquals(
            '<b xss=removed xss=removed>1">',
            $this->security->xss_clean('<b a=<=" onmouseover="alert(1),1>1">')
        );

        $this->assertEquals(
            '<b x=" onmouseover=alert&#40;1&#41;//">',
            $this->security->xss_clean('<b "="< x=" onmouseover=alert(1)//">')
        );
    }

    // --------------------------------------------------------------------

    /**
     * @depends test_xss_clean_sanitize_naughty_html_tags
     * @depends test_xss_clean_sanitize_naughty_html_attributes
     */
    public function test_naughty_html_plus_evil_attributes()
    {
        $this->assertEquals(
            '&lt;svg<img src="x" xss=removed>',
            $this->security->xss_clean('<svg<img > src="x" onerror="location=/javascript/.source+/:alert/.source+/(1)/.source">')
        );
    }

    // --------------------------------------------------------------------

    public function test_xss_hash()
    {
        $this->assertEmpty($this->security->xss_hash);

        // Perform hash
        $this->security->xss_hash();

        $this->assertTrue(preg_match('#^[0-9a-f]{32}$#iS', $this->security->xss_hash) === 1);
    }

    // --------------------------------------------------------------------

    public function test_get_random_bytes()
    {
        $this->markTestSkipped("Fails to work in the current condition");
        $length = "invalid";
        $this->assertFalse($this->security->get_random_bytes($length));

        $length = 10;
        $this->assertNotEmpty($this->security->get_random_bytes($length));
    }

    // --------------------------------------------------------------------

    public function test_entity_decode()
    {
        $encoded = '&lt;div&gt;Hello &lt;b&gt;Booya&lt;/b&gt;&lt;/div&gt;';
        $decoded = $this->security->entity_decode($encoded);

        $this->assertEquals('<div>Hello <b>Booya</b></div>', $decoded);

        // Issue #3057 (https://github.com/bcit-ci/CodeIgniter/issues/3057)
        $this->assertEquals(
            '&foo should not include a semicolon',
            $this->security->entity_decode('&foo should not include a semicolon')
        );
    }

    // --------------------------------------------------------------------

    public function test_sanitize_filename()
    {
        $filename = './<!--foo-->';
        $safe_filename = $this->security->sanitize_filename($filename);

        $this->assertEquals('foo', $safe_filename);
    }

    // --------------------------------------------------------------------

    public function test_strip_image_tags()
    {
        $imgtags = array(
            '<img src="smiley.gif" alt="Smiley face" height="42" width="42">',
            '<img alt="Smiley face" height="42" width="42" src="smiley.gif">',
            '<img src="http://www.w3schools.com/images/w3schools_green.jpg">',
            '<img src="/img/sunset.gif" height="100%" width="100%">',
            '<img src="mdn-logo-sm.png" alt="MD Logo" srcset="mdn-logo-HD.png 2x, mdn-logo-small.png 15w, mdn-banner-HD.png 100w 2x" />',
            '<img sqrc="/img/sunset.gif" height="100%" width="100%">',
            '<img srqc="/img/sunset.gif" height="100%" width="100%">',
            '<img srcq="/img/sunset.gif" height="100%" width="100%">',
            '<img src=non-quoted.attribute foo="bar">'
        );

        $urls = array(
            'smiley.gif',
            'smiley.gif',
            'http://www.w3schools.com/images/w3schools_green.jpg',
            '/img/sunset.gif',
            'mdn-logo-sm.png',
            '<img sqrc="/img/sunset.gif" height="100%" width="100%">',
            '<img srqc="/img/sunset.gif" height="100%" width="100%">',
            '<img srcq="/img/sunset.gif" height="100%" width="100%">',
            'non-quoted.attribute'
        );

        for ($i = 0; $i < count($imgtags); $i++)
        {
            $this->assertEquals($urls[$i], $this->security->strip_image_tags($imgtags[$i]));
        }
    }

    // --------------------------------------------------------------------

    public function test_csrf_set_hash()
    {
        // Set cookie for security test
        $_COOKIE['fw_csrf_cookie'] = md5(uniqid(mt_rand(), TRUE));

        // Set config for Security class
        $config = Factory::getInstance()->config->getConfig('security');

        $config->csrf_protection = true;
        $config->csrf_token_name = 'fw_csrf_token';

        // leave csrf_cookie_name as blank to test _csrf_set_hash function
        $config->csrf_cookie_name = '';

        $this->security = new Mock_Core_Security();

        $this->assertNotEmpty($this->security->get_csrf_hash());
    }
}
