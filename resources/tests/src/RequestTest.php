<?php

namespace axelitus\Acre\Net\Http;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    // HTTP Request Message Example from http://www.jmarshall.com/easy/http/
    private $message_GET = <<<'MSG'
GET /path/file.html HTTP/1.0
From: someuser@jmarshall.com
User-Agent: HTTPTool/1.0

MSG;

    // HTTP Response Message Example from http://www.jmarshall.com/easy/http/
    private $message_POST = <<<'MSG'
POST /path/script.cgi HTTP/1.0
From: frog@jmarshall.com
User-Agent: HTTPTool/1.0
Content-Type: application/x-www-form-urlencoded
Content-Length: 32

home=Cosby&favorite+flavor=flies
MSG;

    public function setUp()
    {
        // nothing to do here...
    }

    /**
     * testForgePrint
     */
    public function testForgePrint()
    {
        $response = Request::forge();
        $response->headers->charset = 'utf-8';
        $response->headers->contentType = 'plain/text';
        $response->getBodyLength(false);

        $output = (string)$response;
        $expected = <<<'EXPECTED'
GET  HTTP/1.1
Charset: utf-8
Content-Type: plain/text

EXPECTED;
        $this->assertEquals($expected, $output);
    }

    /**
     * testValidateMessageGET
     */
    public function testValidateMessageGET()
    {
        $output = Message::validate($this->message_GET);
        $this->assertTrue($output);
    }

    /**
     * testValidateRequestGET
     *
     * @depends testValidateMessageGET
     */
    public function testValidateRequestGET()
    {
        $output = Request::validate($this->message_GET);
        $this->assertTrue($output);
    }

    /**
     * testValidateMessagePOST
     */
    public function testValidateMessagePOST()
    {
        $output = Message::validate($this->message_POST);
        $this->assertTrue($output);
    }

    /**
     * testValidateRequestPOST
     *
     * @depends testValidateMessagePOST
     */
    public function testValidateRequestPOST()
    {
        $output = Request::validate($this->message_POST);
        $this->assertTrue($output);
    }
}
