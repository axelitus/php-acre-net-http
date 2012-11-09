<?php

namespace axelitus\Acre\Net\Http;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // nothing to do here...
    }

    public function testForge()
    {
        $response = Request::forge();
        $response->headers->charset = 'utf-8';
        $response->headers->contentType = 'plain/text';
        $response->getBodyLength(false);

        $output = (string)$response;
        $expected = "GET  HTTP/1.1\r\nCharset: utf-8\r\nContent-Type: plain/text\r\n";
        $this->assertEquals($expected, $output);
    }

    public function testValidateMessageGET()
    {
        // HTTP Request Message Example from http://www.jmarshall.com/easy/http/
        $message = <<<'MSG'
GET /path/file.html HTTP/1.0
From: someuser@jmarshall.com
User-Agent: HTTPTool/1.0

MSG;
        $output = Message::validate($message);
        $this->assertTrue($output);
    }

    public function testValidateRequestGET()
    {
        // HTTP Request Message Example from http://www.jmarshall.com/easy/http/
        $message = <<<'MSG'
GET /path/file.html HTTP/1.0
From: someuser@jmarshall.com
User-Agent: HTTPTool/1.0

MSG;
        $output = Request::validate($message);
        $this->assertTrue($output);
    }

    public function testValidateMessagePOST()
    {
        // HTTP Response Message Example from http://www.jmarshall.com/easy/http/
        $message = <<<'MSG'
POST /path/script.cgi HTTP/1.0
From: frog@jmarshall.com
User-Agent: HTTPTool/1.0
Content-Type: application/x-www-form-urlencoded
Content-Length: 32

home=Cosby&favorite+flavor=flies
MSG;
        $output = Message::validate($message);
        $this->assertTrue($output);
    }

    public function testValidateRequestPOST()
    {
        // HTTP Response Message Example from http://www.jmarshall.com/easy/http/
        $message = <<<'MSG'
POST /path/script.cgi HTTP/1.0
From: frog@jmarshall.com
User-Agent: HTTPTool/1.0
Content-Type: application/x-www-form-urlencoded
Content-Length: 32

home=Cosby&favorite+flavor=flies
MSG;
        $output = Request::validate($message);
        $this->assertTrue($output);
    }
}
