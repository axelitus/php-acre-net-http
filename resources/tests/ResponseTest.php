<?php

namespace axelitus\Acre\Net\Http;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // nothing to do here...
    }

    public function testForge()
    {
        $response = Response::forge();
        $response->headers->charset = 'utf-8';
        $response->headers->contentType = 'plain/text';
        $response->getBodyLength(true);

        $output = (string)$response;
        $expected = "HTTP/1.1 200 OK\r\nCharset: utf-8\r\nContent-Type: plain/text\r\nContent-Length: 0\r\n";
        $this->assertEquals($expected, $output);
    }

    public function testValidate()
    {
        $message = <<<'MSG'
HTTP/1.0 200 OK
Date: Fri, 31 Dec 1999 23:59:59 GMT
Content-Type: text/html
Content-Length: 1354

<html><body><h1>Happy New Millennium!</h1></body></html>
MSG;
        $output = Message::validate($message);
        $this->assertTrue($output);


    }
}
