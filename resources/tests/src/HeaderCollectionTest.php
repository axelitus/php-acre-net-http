<?php

namespace axelitus\Acre\Net\Http;

class HeaderCollectionTest extends \PHPUnit_Framework_TestCase
{
    private $headers = <<<'HEADERS'
Accept: */*
Accept-Language: en-gb
Accept-Encoding: gzip, deflate
User-Agent: Mozilla/4.0 (compatible; MSIE 6.0)
Host: www.httpwatch.com
Connection: Keep-Alive
HEADERS;

    public function setUp()
    {

    }

    /**
     * testHeaderCollection
     *
     * @test
     */
    public function testHeaderCollection()
    {
        $hc = HeaderCollection::forge();
        $this->assertInstanceOf('axelitus\Acre\Net\Http\HeaderCollection', $hc);

        $output = count($hc);
        $expected = 0;
        $this->assertEquals($expected, $output);

        $hc->set('Content-Type', 'text/html');
        $hc->set('Accept-Charset', 'utf-8');
        $hc->set('Accept-Encoding', 'gzip');
        $output = count($hc);
        $expected = 3;
        $this->assertEquals($expected, $output);

        $hc->set('Content-Type', 'text/xml');
        $this->assertEquals($expected, $output);

        $this->assertTrue($hc->has('Content-Type'));
        $this->assertTrue($hc->has('content-type'));
        $this->assertFalse($hc->has('ContentType'));

        $hc->set('Accept-Encoding', 'deflate');
        $output = $hc->get('Accept-Encoding');
        $expected = 'deflate';
        $this->assertEquals($expected, $output);

        $output = $hc->contentType;
        $expected = 'text/xml';
        $this->assertEquals($expected, $output);

        $hc->set('Accept-Encoding', 'gzip', true);
        $output = $hc->get('Accept-Encoding');
        $expected = array('deflate', 'gzip');
        $this->assertEquals($expected, $output);

        $output = $hc->get('Accept-Encoding', true);
        $expected = 'deflate, gzip';
        $this->assertEquals($expected, $output);

        $output = $hc->build();
        $expected = "Content-Type: text/xml\r\nAccept-Charset: utf-8\r\nAccept-Encoding: deflate, gzip";
        $this->assertEquals($expected, $output);

        $output = $hc->build(true);
        $expected = "Content-Type: text/xml\r\nAccept-Charset: utf-8\r\nAccept-Encoding: deflate\r\nAccept-Encoding: gzip";
        $this->assertEquals($expected, $output);
    }

    /**
     * testValidate
     *
     * @test
     */
    public function testValidate()
    {
        $output = HeaderCollection::validate($this->headers);
        $this->assertTrue($output);
    }

    /**
     * testParse
     *
     * @test
     * @depends testValidate
     */
    public function testParse()
    {
        $output = HeaderCollection::parseAsArray($this->headers);
        $expected = array(
            'Accept' => '*/*',
            'Accept-Language' => 'en-gb',
            'Accept-Encoding' => 'gzip, deflate',
            'User-Agent' => 'Mozilla/4.0 (compatible; MSIE 6.0)',
            'Host' => 'www.httpwatch.com',
            'Connection' => 'Keep-Alive'
        );
        $this->assertEquals($expected, $output);

        $hc = HeaderCollection::parse($this->headers, true);
        $this->assertInstanceOf('axelitus\Acre\Net\Http\HeaderCollection', $hc);
        $output = (string)$hc;

        // Change the newlines to the testing platform specific PHP_EOL so we can accurately
        // test against the original headers string.
        // The class uses \r\n as the newline default as stated in the IETF RFC2616 standard.
        $output = str_replace("\r\n", PHP_EOL, $output);

        $expected = $this->headers;
        $this->assertEquals($expected, $output);
    }
}
