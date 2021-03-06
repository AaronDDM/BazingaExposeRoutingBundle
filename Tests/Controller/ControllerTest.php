<?php

namespace Bazinga\ExposeRoutingBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Bazinga\ExposeRoutingBundle\Controller\Controller;
use Bazinga\ExposeRoutingBundle\Service\ExposedRoutesExtractor;

/**
 * ControllerTest class.
 *
 * @package     ExposeRoutingBundle
 * @subpackage  Controller
 * @author William DURAND <william.durand1@gmail.com>
 */
class ControllerTest extends WebTestCase
{
    /**
     * @var \Bazinga\ExposeRoutingBundle\Controller\Controller
     */
    private $controller;

    /**
     * Unit tests.
     */
    public function testIndexAction()
    {
        $router = $this->getMockRouter($this->getMockRouteCollection());
        $engine = $this->getMockEngine();

        $extractor = new ExposedRoutesExtractor($router, array());
        $this->controller = new Controller($engine, $extractor);

        $_format  = 'js';
        $response = $this->controller->indexAction($_format);
        $content  = $response->getContent();

        $this->assertNotEmpty($content);
        $this->assertArrayHasKey('var_prefix', $content, 'A variable prefix is required');
        $this->assertArrayHasKey('var_suffix', $content, 'A variable suffix is required');
        $this->assertArrayHasKey('prefix', $content, 'An url prefix is required');
        $this->assertArrayHasKey('exposed_routes', $content, 'Exposed routes are required');

        $this->assertNotNull($content['var_prefix'], 'A variable prefix is set');
        $this->assertEquals('{', $content['var_prefix'], 'The Symfony2 prefix is }');
        $this->assertNotNull($content['var_suffix'], 'A variable suffix is set');
        $this->assertEquals('}', $content['var_suffix'], 'The Symfony2 suffix is {');
        $this->assertNotNull($content['prefix'], 'The prefix cannot be null');

        $this->assertEquals(4, count($content['exposed_routes']), '4 exposed routes are registered');
        $this->assertArrayHasKey('bar', $content['exposed_routes'], 'bar is an exposed route');
        $this->assertArrayHasKey('baz', $content['exposed_routes'], 'baz is an exposed route');
        $this->assertArrayHasKey('foobar', $content['exposed_routes'], 'foobar is an exposed route');
        $this->assertArrayHasKey('foobaz', $content['exposed_routes'], 'foobaz is an exposed route');

        $this->assertArrayNotHasKey('_controller', $content['exposed_routes']['foobar']->getDefaults(), '_* defaults are removed');
        $this->assertArrayNotHasKey('_controller', $content['exposed_routes']['foobaz']->getDefaults(), '_* defaults are removed');
        $this->assertArrayHasKey('id', $content['exposed_routes']['foobaz']->getDefaults(), 'Valid defaults are kept');
    }

    /**
     * Function tests.
     */
    public function testIndexActionResponse()
    {
        $client  = $this->createClient();
        $crawler = $client->request('GET', '/js/routing.js');

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->headers->contains('content-type', 'application/javascript'));

        $expected = <<<EOT
Routing.prefix = '';
Routing.variablePrefix = '{';
Routing.variableSuffix = '}';

EOT;

        $this->assertEquals($expected, $client->getResponse()->getContent());
    }

    /**
     * Get a mock object which represents a RouteCollection.
     * @return \Symfony\Symfony\Component\Routing\RouteCollection
     */
    private function getMockRouteCollection()
    {
        $array = array(
            // Not exposed
            'foo' => new \Symfony\Component\Routing\Route('/foo'),
            'foz' => new \Symfony\Component\Routing\Route('/foo', array('_controller' => 'foz')),
            // Exposed
            'bar' => new \Symfony\Component\Routing\Route('/bar', array(), array(), array('expose' => true)),
            'baz' => new \Symfony\Component\Routing\Route('/baz/{id}', array(), array(), array('expose' => true)),
            // Exposed with defaults
            'foobar' => new \Symfony\Component\Routing\Route(
                '/foobar', array('_controller' => 'foobar'),
                array(), array('expose' => true)
            ),
            'foobaz' => new \Symfony\Component\Routing\Route(
                '/foobaz/{id}', array('_controller' => 'foobar', 'id' => 1),
                array(), array('expose' => true)
            ),
        );

        $routeCollection = $this->getMock('\Symfony\Component\Routing\RouteCollection', array(), array(), '', false);
        $routeCollection
            ->expects($this->atLeastOnce())
            ->method('all')
            ->will($this->returnValue($array));

        return $routeCollection;
    }

    /**
     * Get a mock object which represents a Router.
     * @return \Symfony\Component\Routing\Router
     */
    private function getMockRouter($routeCollection)
    {
        $router = $this->getMock('\Symfony\Component\Routing\Router', array(), array(), '', false);
        $router
            ->expects($this->atLeastOnce())
            ->method('getRouteCollection')
            ->will($this->returnValue($routeCollection));
        $router
            ->expects($this->atLeastOnce())
            ->method('getContext')
            ->will($this->returnValue($this->getMock('\Symfony\Component\Routing\RequestContext')));

        return $router;
    }

    /**
     * Get a mock object which represents an EngineInterface.
     * @return \Symfony\Component\Templating\EngineInterface
     */
    private function getMockEngine()
    {
        $engine = $this->getMock('\Symfony\Component\Templating\EngineInterface');
        $engine->expects($this->atLeastOnce())
            ->method('render')
            ->will($this->returnArgument(1));

        return $engine;
    }
}
