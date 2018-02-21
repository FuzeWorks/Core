<?php
/**
 * FuzeWorks.
 *
 * The FuzeWorks MVC PHP FrameWork
 *
 * Copyright (C) 2018   TechFuze
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
 * @since       Version 1.1.2
 *
 * @version     Version 1.1.2
 */

use FuzeWorks\Router;
use FuzeWorks\Factory;
use FuzeWorks\Core;
use FuzeWorks\Logger;

/**
 * Class RouterTest.
 *
 * Core testing suite, will test basic core functionality of the FuzeWorks Router
 */
class routerTest extends CoreTestAbstract
{

    protected $router;

    public function setUp()
    {
        $this->router = new Router();
        $this->router->setControllerDirectory('tests'.DS.'controller');
    }

    public function testGetRouterClass()
    {
        $this->assertInstanceOf('FuzeWorks\Router', $this->router);
    }

    public function testGetSetRouterDirectory()
    {
        $directory = 'tests' . DS . 'controller';
        $this->router->setControllerDirectory($directory);
        $this->assertEquals($directory, $this->router->getControllerDirectory($directory));
    }

    /* Route Parsing ------------------------------------------------------ */

    /**
     * @depends testGetRouterClass
     */
    public function testAddRoutes()
    {
        $this->router->addRoute('testRoute', function(){});
        $this->assertArrayHasKey('testRoute', $this->router->getRoutes());
    }

    /**
     * @depends testAddRoutes
     */
    public function testAppendRoutes()
    {
        $testRouteFunction = array(function(){});
        $testAppendRouteFunction = array(function(){});
        $this->router->addRoute('testRoute', $testRouteFunction);
        $this->router->addRoute('testAppendRoute', $testAppendRouteFunction, false);

        // Test if the order is correct
        $this->assertSame(array('testRoute' => $testRouteFunction, 'testAppendRoute' => $testAppendRouteFunction), $this->router->getRoutes());

        // Test if the order is not incorrect
        $this->assertNotSame(array('testAppendRoute' => $testAppendRouteFunction,'testRoute' => $testRouteFunction), $this->router->getRoutes());
    }

    /**
     * @depends testAddRoutes
     */
    public function testRemoveRoutes()
    {
        // First add
        $this->router->addRoute('testRemoveRoute', function(){});
        $this->assertArrayHasKey('testRemoveRoute', $this->router->getRoutes());

        // Then remove
        $this->router->removeRoute('testRemoveRoute');
        $this->assertArrayNotHasKey('testRemoveRoute', $this->router->getRoutes());
    }

    /**
     * @depends testAddRoutes
     */
    public function testParseRouting()
    {
        // Prepare the routes so they can be parsed
        $config = Factory::getInstance()->config;
        $config->routes->{'testParseRouting'} = function(){};
        $this->router = new Router();

        // Now verify whether the passing has gone correctly
        $this->assertArrayHasKey('testParseRouting', $this->router->getRoutes());
    }

    /**
     * @depends testParseRouting
     */
    public function testVerbParsing()
    {
        // Prepare the routes so they can be parsed
        $config = Factory::getInstance()->config;
        $getFunction = function($get){};
        $postFunction = function($post){};
        $config->routes->{'testVerbPassing'} = array('GET' => $getFunction, 'POST' => $postFunction);
        $this->router = new Router();

        // Now verify whether the passing has gone correctly
        $routes = $this->router->getRoutes();
        $this->assertArrayHasKey('testVerbPassing', $routes);
        $this->assertSame($getFunction, $routes['testVerbPassing']);
        $this->assertNotSame($postFunction, $routes['testVerbPassing']);
    }

    /**
     * @depends testVerbParsing
     */
    public function testInvalidParsing()
    {
        // Prepare the routes so they can be parsed
        $config = Factory::getInstance()->config;
        $config->routes->{'testInvalidParsing'} = array('NOTGET' => function(){});
        $this->router = new Router();

        // Now verify whether the route has been skipped
        $this->assertArrayNotHasKey('testInvalidParsing', $this->router->getRoutes());
    }

    /**
     * @depends testVerbParsing
     */
    public function testWildcardsParsing()
    {
        // Prepare the routes so they can be parsed
        $config = Factory::getInstance()->config;
        $config->routes->{'testWildcardsParsing/:any/:num'} = function(){};
        $this->router = new Router();

        // Now verify whether the route has been skipped
        $this->assertArrayHasKey('testWildcardsParsing/[^/]+/[0-9]+', $this->router->getRoutes());
    }

    /* defaultCallable() -------------------------------------------------- */

    /**
     * @depends testGetRouterClass
     */ 
    public function testDefaultCallable()
    {
        // First set the segments, callable and the route
        $arguments = array('controller' => 'TestDefaultCallable', 'function' => 'index');

        // Verify that the controller is found and loaded
        $this->assertTrue($this->router->defaultCallable($arguments));
        $this->assertInstanceOf('\Application\Controller\TestDefaultCallable', $this->router->getCallable());
    }

    /**
     * @depends testDefaultCallable
     */ 
    public function testDefaultCallableMissingMethod()
    {
        // First set the arguments
        $this->assertEquals(200, Core::$http_status_code);
        $arguments = array('controller' => 'TestDefaultCallableMissingMethod', 'function' => 'missing');

        // Verify that the method is not found
        $this->assertFalse($this->router->defaultCallable($arguments));
        $this->assertEquals(404, Core::$http_status_code);   
    }

    /**
     * @depends testDefaultCallable
     */ 
    public function testDefaultCallableMissingController()
    {
        // First set the arguments
        $this->assertEquals(200, Core::$http_status_code);
        $arguments = array('controller' => 'TestDefaultCallableMissingController', 'function' => 'index');

        // Verify that the controller is not found
        $this->assertFalse($this->router->defaultCallable($arguments));
        $this->assertEquals(404, Core::$http_status_code); 
    }

    /**
     * @depends testDefaultCallable
     */ 
    public function testDefaultCallableHalt()
    {
        // First set the arguments
        $this->assertEquals(200, Core::$http_status_code);
        $arguments = array('controller' => 'TestDefaultCallableHalt', 'function' => 'index');

        // Verify that the controller is halted
        $this->assertFalse($this->router->defaultCallable($arguments));
        $this->assertEquals(200, Core::$http_status_code);
    }

    /* -------------------------------------------------------------------- */

    /* route() ------------------------------------------------------------ */

    /**
     * @depends testGetRouterClass
     */    
    public function testRoute()
    {
        // First set the segments and the route
        $uri = Factory::getInstance()->uri;
        $uri->segments = array('testRoute');
        $this->router->addRoute('testRoute', function(){});

        // Perform verification
        $this->assertFalse($this->router->route(false));
        $this->assertContains('testRoute', $this->router->getMatches());
    }

    /**
     * @depends testRoute
     */
    public function testRouteDefaultRoute()
    {
        // First set the segments and the route
        $uri = Factory::getInstance()->uri;
        $uri->segments = array();

        // Perform verification
        $this->assertFalse($this->router->route(false));
        $this->assertEmpty($this->router->getMatches());
    }

    /**
     * @depends testRoute
     */
    public function testArrayRoute()
    {
        // First set the segments and the route
        $uri = Factory::getInstance()->uri;
        $uri->segments = array('testArrayRoute');
        $this->router->addRoute('testArrayRoute', array('callable' => function(){}));

        // Perform verification
        $this->assertTrue($this->router->route(false));
        $this->assertContains('testArrayRoute', $this->router->getMatches());
    }

    /* -------------------------------------------------------------------- */

    /* loadCallable() ----------------------------------------------------- */

    /**
     * @depends testArrayRoute
     */
    public function testLoadCallable()
    {
        // First set the segments, callable and the route
        $uri = Factory::getInstance()->uri;
        $uri->segments = array('testLoadCallable');
        $callable = function($arg){$this->assertEquals('testLoadCallable', $arg[0]);return true;};
        $this->router->addRoute('testLoadCallable', array('callable' => $callable));

        // Perform verification
        $this->assertTrue($this->router->route(true));
        $this->assertSame($callable, $this->router->getCallable());
    }

    /**
     * @depends testLoadCallable
     */ 
    public function testLoadCallableNotCallable()
    {
        // First set the segments, callable and the route
        $uri = Factory::getInstance()->uri;
        $uri->segments = array('testLoadCallableNotCallable');
        $this->router->addRoute('testLoadCallableNotCallable', array('callable' => 'notCallable'));

        // Perform verification
        $this->assertTrue($this->router->route(true));
        $this->assertSame('notCallable', $this->router->getCallable());
        $this->assertEquals(500, Core::$http_status_code);
    }

    /**
     * @depends testLoadCallable
     */ 
    public function testLoadCallableToDefaultCallabe()
    {
        // First set the segments, callable and the route
        $uri = Factory::getInstance()->uri;
        $uri->segments = array('testLoadCallableToDefaultCallable');
        $this->router->addRoute('testLoadCallableToDefaultCallable', array('callable' => array('controller' => 'TestLoadCallableToDefaultCallable', 'function' => 'index', 'parameters' => null)));

        // Perform verification
        $this->assertTrue($this->router->route(true));
    }

    public function testLoadCallableUnsatisfiedCallable()
    {
        // First set the segments, callable and the route
        $uri = Factory::getInstance()->uri;
        $uri->segments = array('testLoadCallableUnsatisfiedCallable');
        $this->router->addRoute('testLoadCallableUnsatisfiedCallable', array('callable' => function() {return false;} ));

        // Perform verification
        $this->assertTrue($this->router->route(true));
    }

    /* -------------------------------------------------------------------- */

    /* routeDefault() ----------------------------------------------------- */

    /**
     * @depends testLoadCallable
     */ 
    public function testRouteDefault()
    {
        // First testing the default route without any segments
        $uri = Factory::getInstance()->uri;
        $uri->segments = array();

        // Change the default controller so it is easier to test
        $config = Factory::getInstance()->config;
        $config->routing->default_controller = 'testRouteDefault';
        
        // Perform verification
        $this->assertTrue($this->router->route(true));
        $this->assertEquals(200, Core::$http_status_code);
    }

    /**
     * @depends testRouteDefault
     */ 
    public function testRouteDefaultUriDashes()
    {
        // First testing the default route without any segments
        $uri = Factory::getInstance()->uri;

        // Change the default controller so URI Dashes get translated
        $config = Factory::getInstance()->config;
        $config->routing->translate_uri_dashes = TRUE;

        // Perform verification, first test should fail
        $uri->segments = array('test-Route-Default-Uri-Dashes-Fail');
        $this->assertTrue($this->router->route(true));
        $this->assertEquals(404, Core::$http_status_code);

        // Reset the HTTP status code
        Core::$http_status_code = 200;

        // Next test should succeed
        $uri->segments = array('test-Route-Default-Uri-Dashes-Succeed', 'dashed-method');
        $this->assertTrue($this->router->route(true));
        $this->assertEquals(200, Core::$http_status_code);
    }

    /* -------------------------------------------------------------------- */

    /* Simulation tests --------------------------------------------------- */

    /**
     * @depends testLoadCallable
     */ 
    public function testLoadController()
    {
        // Adjust the URL
        $uri = Factory::getInstance()->uri;
        $uri->segments = array('testLoadController');

        // Perform verification
        $this->assertTrue($this->router->route(true));
        $this->assertEquals(200, Core::$http_status_code);
    }

    /**
     * @depends testLoadController
     */ 
    public function testLoadStandardController()
    {
        // Adjust the URL
        $uri = Factory::getInstance()->uri;
        $uri->segments = array();

        // Perform verification
        $this->assertTrue($this->router->route(true));
        $this->assertEquals(200, Core::$http_status_code);
    }

    /**
     * @depends testLoadController
     */ 
    public function testControllerNotFound()
    {
        // Adjust the URL
        $uri = Factory::getInstance()->uri;
        $uri->segments = array('controllerNotFound');

        // Perform verification
        $this->assertTrue($this->router->route(true));
        $this->assertEquals(404, Core::$http_status_code);
    }

    /**
     * @depends testLoadController
     */ 
    public function testMatchingRoute()
    {
        // Adjust the URL
        $uri = Factory::getInstance()->uri;
        $uri->segments = array('methodInFirstArgument', 'testMatchingRoute');

        // Create a custom route
        $this->router->addRoute('^(?P<function>.*?)(|\/(?P<controller>.*?)(|\/(?P<parameters>.*?)))$', array('callable' => array($this->router, 'defaultCallable')));

        // Perform verification
        $this->assertTrue($this->router->route(true));
        $this->assertEquals(200, Core::$http_status_code);
    }

    /**
     * @depends testMatchingRoute
     */
    public function testStaticRoute()
    {
        // Adjust the URL
        $uri = Factory::getInstance()->uri;
        $uri->segments = array('staticKey'); 

        // Create a static route
        $this->router->addRoute('staticKey', 'standard/index');

        // Perform verification
        $this->assertTrue($this->router->route(true));
        $this->assertEquals(200, Core::$http_status_code);
    }

    /**
     * @depends testMatchingRoute
     */
    public function testCustomCallable()
    {
        // Adjust the URL
        $uri = Factory::getInstance()->uri;
        $uri->segments = array('customCallable');

        // Create a custom callable
        $this->router->addRoute('customCallable', array('callable' => function($arguments){
            return true;
        } ));

        $this->assertTrue($this->router->route(true));
        $this->assertEquals(200, Core::$http_status_code);
    }

    /**
     * @depends testMatchingRoute
     */
    public function testCustomRewriteCallable()
    {
        // Adjust the URL
        $uri = Factory::getInstance()->uri;
        $uri->segments = array('customRewriteCallable');

        // Create a custom callable
        $this->router->addRoute('customRewriteCallable', function($arguments = array()){
            return 'standard/index';
        });

        $this->assertTrue($this->router->route(true));
        $this->assertEquals(200, Core::$http_status_code);
    }

    /* -------------------------------------------------------------------- */
}
