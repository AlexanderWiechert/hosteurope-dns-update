<?php

require_once __DIR__ . '/../src/Lib/ArgsParser.php';

class ArgsTest extends \PHPUnit_Framework_TestCase
{

    public function testInit()
    {

        $data = [
            'test', // Originally the script itself
            '-a',
            'test'
        ];

        $args = new \Lib\ArgsParser($data, ['-a']);

        $this->assertInstanceOf(\Lib\ArgsParser::class, $args);
    }

    public function testOption()
    {
        $data = [
            'test', // Originally the script itself
            '-a',
            'test'
        ];

        $args = new \Lib\ArgsParser($data, ['-a']);

        $this->assertNotNull($args->get('a'));
        $this->assertNotEmpty($args->get('a'));
        $this->assertEmpty($args->get('b'));
        $this->assertSame('test', $args->get('a'));
        $this->assertStringStartsNotWith('-', $args->get('a'));
    }

    public function testMultipleOption()
    {
        $data = [
            'test', // Originally the script itself
            '-a',
            'test',
            '-b',
            'test2',
            '-c',
            'test3'
        ];

        $args = new \Lib\ArgsParser($data, ['-a', '-b', '-c']);

        $this->assertNotNull($args->get('a'));
        $this->assertNotEmpty($args->get('a'));
        $this->assertSame('test', $args->get('a'));

        $this->assertNotNull($args->get('b'));
        $this->assertNotEmpty($args->get('b'));
        $this->assertSame('test2', $args->get('b'));

        $this->assertNotNull($args->get('c'));
        $this->assertNotEmpty($args->get('c'));
        $this->assertSame('test3', $args->get('c'));

        $this->assertEmpty($args->get('delete'));
        $this->assertEmpty($args->get('force'));
    }

    public function testForceOption()
    {
        $data = [
            'test', // Originally the script itself
            '-a',
            'test',
            '--force'
        ];

        $args = new \Lib\ArgsParser($data, ['-a', '--force']);

        $this->assertNotNull($args->get('a'));
        $this->assertNotEmpty($args->get('a'));
        $this->assertEmpty($args->get('b'));
        $this->assertSame('test', $args->get('a'));

        $this->assertEmpty($args->get('delete'));
        $this->assertTrue($args->get('force'));
    }

    public function testDeleteOption()
    {
        $data = [
            'test', // Originally the script itself
            '-a',
            'test',
            '--delete',
            '--force'
        ];

        $args = new \Lib\ArgsParser($data, ['-a', '--delete', '--force']);

        $this->assertNotNull($args->get('a'));
        $this->assertNotEmpty($args->get('a'));
        $this->assertEmpty($args->get('b'));
        $this->assertSame('test', $args->get('a'));

        $this->assertTrue($args->get('delete'));
        $this->assertTrue($args->get('force'));
    }

}
