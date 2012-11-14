<?php

namespace axelitus\Acre\Net\Http;

class MethodTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // nothing to do here...
    }

    /**
     * testIsValid
     *
     * @test
     */
    public function testIsValid()
    {
        $response = Method::isValid('OPTIONS');
        $this->assertTrue($response);

        $response = Method::isValid('GET');
        $this->assertTrue($response);

        $response = Method::isValid('HEAD');
        $this->assertTrue($response);

        $response = Method::isValid('POST');
        $this->assertTrue($response);

        $response = Method::isValid('PUT');
        $this->assertTrue($response);

        $response = Method::isValid('DELETE');
        $this->assertTrue($response);

        $response = Method::isValid('TRACE');
        $this->assertTrue($response);

        $response = Method::isValid('CONNECT');
        $this->assertTrue($response);

        $response = Method::isValid('options', false);
        $this->assertTrue($response);

        $response = Method::isValid('get', false);
        $this->assertTrue($response);

        $response = Method::isValid('head', false);
        $this->assertTrue($response);

        $response = Method::isValid('post', false);
        $this->assertTrue($response);

        $response = Method::isValid('put', false);
        $this->assertTrue($response);

        $response = Method::isValid('delete', false);
        $this->assertTrue($response);

        $response = Method::isValid('trace', false);
        $this->assertTrue($response);

        $response = Method::isValid('connect', false);
        $this->assertTrue($response);

        $response = Method::isValid('options');
        $this->assertFalse($response);

        $response = Method::isValid('get');
        $this->assertFalse($response);

        $response = Method::isValid('head');
        $this->assertFalse($response);

        $response = Method::isValid('post');
        $this->assertFalse($response);

        $response = Method::isValid('put');
        $this->assertFalse($response);

        $response = Method::isValid('delete');
        $this->assertFalse($response);

        $response = Method::isValid('trace');
        $this->assertFalse($response);

        $response = Method::isValid('connect');
        $this->assertFalse($response);
    }
}
