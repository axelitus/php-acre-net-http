<?php

namespace axelitus\Acre\Net\Http;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    // HTTP Response Message Example from http://www.jmarshall.com/easy/http/
    private $message_OK = <<<'MSG'
HTTP/1.0 200 OK
Date: Fri, 31 Dec 1999 23:59:59 GMT
Content-Type: text/html
Content-Length: 1354

<html><body><h1>Happy New Millennium!</h1></body></html>
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
        $response = Response::forge();
        $response->headers->charset = 'utf-8';
        $response->headers->contentType = 'plain/text';
        $response->getBodyLength(true);

        $output = (string)$response;
        $expected = <<<'EXPECTED'
HTTP/1.1 200 OK
Charset: utf-8
Content-Type: plain/text
Content-Length: 0

EXPECTED;
        $this->assertEquals($expected, $output);
    }

    /**
     * testValidateMessageOK
     */
    public function testValidateMessageOK()
    {
        $output = Message::validate($this->message_OK);
        $this->assertTrue($output);
    }

    /**
     * testValidateResponseOK
     *
     * @depends testValidateMessageOK
     */
    public function testValidateResponseOK()
    {
        $output = Response::validate($this->message_OK);
        $this->assertTrue($output);
    }
}
