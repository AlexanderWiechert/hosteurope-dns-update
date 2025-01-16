<?php

require_once __DIR__ . '/../src/Lib/Http/Client.php';
require_once __DIR__ . '/../src/Lib/Http/Model/Response.php';

class HttpClientTest extends \PHPUnit_Framework_TestCase
{

    /* Unit Tests */
    public function testResponse()
    {
        $client = new \Lib\Http\Client(new \Lib\Http\Model\Response('test'));

        $this->assertNotNull($client->getResponse());
        $this->assertInstanceOf(\Lib\Http\Model\Response::class, $client->getResponse());
        $this->assertContains('test', $client->getResponse()->getContent());
    }

    public function testEmptyResponse()
    {
        $client = new \Lib\Http\Client();

        $this->assertNotNull($client->getResponse());
        $this->assertInstanceOf(\Lib\Http\Model\Response::class, $client->getResponse());
        $this->assertEmpty($client->getResponse()->getContent());
    }

    /* Integration Tests */
    public function testConnection()
    {
        $client = new \Lib\Http\Client();
        $response = $client->send('https://www.google.de');

        $this->assertInstanceOf(\Lib\Http\Model\Response::class, $response);
        $this->assertSame(200, $response->getHeader()['code']);
    }

}
