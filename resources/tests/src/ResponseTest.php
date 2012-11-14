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
     *
     * @test
     */
    public function testForgePrint()
    {
        $response = Response::forge();
        $this->assertInstanceOf('axelitus\Acre\Net\Http\Response', $response);
        $response->headers->charset = 'utf-8';
        $response->headers->contentType = 'plain/text';
        $response->getBodyLength(true);

        $output = (string)$response;

        // Change the newlines to the testing platform specific PHP_EOL so we can accurately
        // test against the original headers string.
        // The class uses \r\n as the newline default as stated in the IETF RFC2616 standard.
        $output = str_replace("\r\n", PHP_EOL, $output);

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
     *
     * @test
     */
    public function testValidateMessageOK()
    {
        $output = Message::validate($this->message_OK);
        $this->assertTrue($output);
    }

    /**
     * testValidateResponseOK
     *
     * @test
     * @depends testValidateMessageOK
     */
    public function testValidateResponseOK()
    {
        $output = Response::validate($this->message_OK);
        $this->assertTrue($output);
    }

    /**
     * testGetMessageType
     *
     * @test
     */
    public function testGetMessageType()
    {
        $message = Response::forge();
        $output = Message::type($message);
        $expected = Message::TYPE_RESPONSE;
        $this->assertEquals($expected, $output);

        $message = (string)$message;
        $output = Message::type($message);
        $expected = Message::TYPE_RESPONSE;
        $this->assertEquals($expected, $output);

        $output = $message;

        // Change the newlines to the testing platform specific PHP_EOL so we can accurately
        // test against the original headers string.
        // The class uses \r\n as the newline default as stated in the IETF RFC2616 standard.
        $output = str_replace("\r\n", PHP_EOL, $output);

        $expected = <<<'EXPECTED'
HTTP/1.1 200 OK

EXPECTED;
        $this->assertEquals($expected, $output);
    }
}
