<?php

require_once __DIR__ . '/../src/App/Abstracts/AbstractStub.php';
require_once __DIR__ . '/../src/App/HEStub.php';

class HeStubTest extends \PHPUnit_Framework_TestCase
{
    public function testIpResolver()
    {
        $client = new \Lib\Http\Client();
        $response = $client->send('https://api.ipify.org/');

        $this->assertSame(200, $response->getHeader()['code']);
        $this->assertNotNull($response->getContent());
        $this->assertRegExp('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $response->getContent());
    }

    public function testArgs()
    {
        $this->assertNotNull(\App\HEStub::getArgsOptions());
        $this->assertArrayHasKey('-d', \App\HEStub::getArgsOptions());
        $this->assertArrayHasKey('-h', \App\HEStub::getArgsOptions());
        $this->assertArrayHasKey('-t', \App\HEStub::getArgsOptions());
        $this->assertArrayHasKey('-a', \App\HEStub::getArgsOptions());
        $this->assertArrayHasKey('--help', \App\HEStub::getArgsOptions());
        $this->assertArrayHasKey('--force', \App\HEStub::getArgsOptions());
        $this->assertArrayHasKey('--delete', \App\HEStub::getArgsOptions());
    }
}