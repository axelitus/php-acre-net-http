<?php

namespace axelitus\Acre\Net\Http;

class HeaderCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // nothing to do here...
    }

    public function testHeaderCollection()
    {
        $hc = HeaderCollection::forge();
        $this->assertTrue($hc instanceof HeaderCollection);

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

        $output = $hc->build(false);
        $expected = "Content-Type: text/xml\r\nAccept-Charset: utf-8\r\nAccept-Encoding: deflate\r\nAccept-Encoding: gzip";
        $this->assertEquals($expected, $output);
    }
}
