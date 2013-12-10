<?php
namespace Maxposter\Guzzle\Plugin\Referer\Test;

use Guzzle\Http\Client;
use Guzzle\Common\Event;
use Guzzle\Http\Message\Response;
use Guzzle\Http\RedirectPlugin;
use Maxposter\Guzzle\Plugin\Referer\RefererPlugin;

/**
 * Class RefererPluginTest
 *
 * @package Maxposter\Guzzle\Plugin\Referer\Test
 */
class RefererPluginTest extends \Guzzle\Tests\GuzzleTestCase
{
    public function testFirstRequestHasNoReferer()
    {
        $plugin = new RefererPlugin();
        $client = new Client();
        $client->getEventDispatcher()->addSubscriber($plugin);

        $request1 = $client->get('http://www.example.com');
        $plugin->onRequestBeforeSend(new Event(array(
            'request' => $request1,
        )));
        $request1->setResponse($response1 = new Response(200, array(), ''));
        $plugin->onRequestSuccess(new Event(array(
            'request'  => $request1,
            'response' => $response1,
        )));

        $this->assertNull($request1->getHeader('Referer'));
        $this->assertAttributeSame('http://www.example.com', 'lastAbsoluteUri', $plugin);
    }


    public function testSecondRequestHasReferer()
    {
        $plugin = new RefererPlugin();
        $client = new Client();
        $client->getEventDispatcher()->addSubscriber($plugin);

        $request1 = $client->get('http://www.example.com');
        $plugin->onRequestBeforeSend(new Event(array(
            'request' => $request1,
        )));
        $request1->setResponse($response1 = new Response(200, array(), ''));
        $plugin->onRequestSuccess(new Event(array(
            'request'  => $request1,
            'response' => $response1,
        )));

        $this->assertNull($request1->getHeader('Referer'));
        $this->assertAttributeSame('http://www.example.com', 'lastAbsoluteUri', $plugin);

        $request2 = $client->get('http://www.example.com/test');
        $plugin->onRequestBeforeSend(new Event(array(
            'request' => $request2,
        )));
        $request2->setResponse($response2 = new Response(200, array(), ''));
        $plugin->onRequestSuccess(new Event(array(
            'request'  => $request2,
            'response' => $response2,
        )));

        $this->assertNotNull($request2->getHeader('Referer'));
        $this->assertInstanceOf('\\Guzzle\\Http\\Message\\Header', $request2->getHeader('Referer'));
        $this->assertEquals('http://www.example.com', (string) $request2->getHeader('Referer'));
        $this->assertAttributeSame('http://www.example.com/test', 'lastAbsoluteUri', $plugin);
    }


    public function testRequestWithRedirectKeepReferer()
    {
        $plugin = new RefererPlugin();
        $client = new Client('', array(RedirectPlugin::DISABLE => true));
        $client->getEventDispatcher()->addSubscriber($plugin);

        $request1 = $client->get('http://www.example.com');
        $plugin->onRequestBeforeSend(new Event(array(
            'request' => $request1,
        )));
        $request1->setResponse($response1 = new Response(200, array(), ''));
        $plugin->onRequestSuccess(new Event(array(
            'request'  => $request1,
            'response' => $response1,
        )));

        $request2 = $client->get('http://www.example.com/redirect');
        $plugin->onRequestBeforeSend(new Event(array(
            'request' => $request2,
        )));
        $request2->setResponse($response2 = new Response(301, array('Location' => 'http://www.example.com/redirected'), ''));
        $plugin->onRequestSuccess(new Event(array(
            'request'  => $request2,
            'response' => $response2,
        )));

        $this->assertAttributeSame('http://www.example.com', 'lastAbsoluteUri', $plugin);
        $this->assertNotNull($request2->getHeader('Referer'));
        $this->assertInstanceOf('\\Guzzle\\Http\\Message\\Header', $request2->getHeader('Referer'));
        $this->assertEquals('http://www.example.com', (string) $request2->getHeader('Referer'));

        $redirectedRequest = $client->get('http://www.example.com/some1');
        $plugin->onRequestBeforeSend(new Event(array(
            'request' => $redirectedRequest,
        )));
        $redirectedRequest->setResponse($response3 = new Response(200, array(), ''));
        $plugin->onRequestSuccess(new Event(array(
            'request'  => $redirectedRequest,
            'response' => $response3,
        )));

        $this->assertAttributeSame('http://www.example.com/some1', 'lastAbsoluteUri', $plugin);
        $this->assertNotNull($redirectedRequest->getHeader('Referer'));
        $this->assertInstanceOf('\\Guzzle\\Http\\Message\\Header', $redirectedRequest->getHeader('Referer'));
        $this->assertEquals('http://www.example.com', (string) $redirectedRequest->getHeader('Referer'));
    }

}