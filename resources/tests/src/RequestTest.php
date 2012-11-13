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
     *
     * @test
     */
    public function testForgePrint()
    {
        $response = Request::forge();
        $response->headers->charset = 'utf-8';
        $response->headers->contentType = 'plain/text';
        $response->getBodyLength(false);

        $output = (string)$response;

        // Change the newlines to the testing platform specific PHP_EOL so we can accurately
        // test against the original headers string.
        // The class uses \r\n as the newline default as stated in the IETF RFC2616 standard.
        $output = str_replace("\r\n", PHP_EOL, $output);

        $expected = <<<'EXPECTED'
GET  HTTP/1.1
Charset: utf-8
Content-Type: plain/text

EXPECTED;
        $this->assertEquals($expected, $output);
    }

    /**
     * testValidateMessageGET
     *
     * @test
     */
    public function testValidateMessageGET()
    {
        $output = Message::validate($this->message_GET);
        $this->assertTrue($output);
    }

    /**
     * testValidateRequestGET
     *
     * @test
     * @depends testValidateMessageGET
     */
    public function testValidateRequestGET()
    {
        $output = Request::validate($this->message_GET);
        $this->assertTrue($output);
    }

    /**
     * testValidateMessagePOST
     *
     * @test
     */
    public function testValidateMessagePOST()
    {
        $output = Message::validate($this->message_POST);
        $this->assertTrue($output);
    }

    /**
     * testValidateRequestPOST
     *
     * @test
     * @depends testValidateMessagePOST
     */
    public function testValidateRequestPOST()
    {
        $output = Request::validate($this->message_POST);
        $this->assertTrue($output);
    }

    /**
     * testGetMessageType
     *
     * @test
     */
    public function testGetMessageType()
    {
        $message = Request::forge();
        $output = Message::type($message);
        $expected = Message::TYPE_REQUEST;
        $this->assertEquals($expected, $output);

        $message = (string)$message;
        $output = Message::type($message);
        $expected = Message::TYPE_REQUEST;
        $this->assertEquals($expected, $output);
    }
}
