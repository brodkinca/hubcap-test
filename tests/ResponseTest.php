<?php

/**
 * cURL Library
 *
 * PHP Version 5.3
 *
 * @category  Library
 * @package   BCA/CURL
 * @author    Brodkin CyberArts <support@brodkinca.com>
 * @copyright 2012 Brodkin CyberArts.
 * @license   All rights reserved.
 * @version   GIT: $Id$
 * @link      https://github.com/brodkinca/BCA-PHP-CURL
 */

namespace BCA\CURL;

/**
 * cURL Response Class Tests
 *
 * @category  Test
 * @package   BCA/CURL
 * @author    Brodkin CyberArts <support@brodkinca.com>
 * @copyright 2012 Brodkin CyberArts.
 * @license   All rights reserved.
 * @link      https://github.com/brodkinca/BCA-PHP-CURL
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{
    protected $object;
    protected $dataResponse = 'foobar';
    protected $dataInfo = array('foo'=>'bar', 'http_code'=>500);

    /**
     * Setup Each Test
     *
     * @return null
     */
    public function setUp()
    {
        $this->object = new Response($this->dataResponse, $this->dataInfo);
    }

    /**
     * @covers BCA\CURL\Response::__get
     */
    public function test__get()
    {
        $this->assertSame('bar', $this->object->foo);
        $this->assertSame(500, $this->object->http_code);
    }

    /**
     * @covers BCA\CURL\Response::__toString
     */
    public function test__toString()
    {
        $this->assertEquals($this->dataResponse, $this->object);
    }

    /**
     * @covers BCA\CURL\Response::debug
     */
    public function test_debug()
    {
        $this->expectOutputRegex("/debug/i");
        $this->assertNull($this->object->debug());
    }

    /**
     * @covers BCA\CURL\Response::status
     */
    public function test_status()
    {
        $this->assertSame(500, $this->object->status());
    }

    /**
     * @covers BCA\CURL\Response::success
     */
    public function test_success()
    {
        $this->assertFalse($this->object->success());

        $info = $this->dataInfo;
        $info['http_code'] = 200;
        $response = new Response($this->dataResponse, $info);
        $this->assertTrue($response->success());
    }
}
