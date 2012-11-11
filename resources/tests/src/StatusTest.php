<?php

namespace axelitus\Acre\Net\Http;

class StatusTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // nothing to do here...
    }

    /**
     * testIsValid
     */
    public function testIsValid()
    {
        $valid_codes = Status::codes();
        for ($i = 100; $i < 520; $i++) {
            $output = Status::isValid($i);
            if (in_array($i, array_keys($valid_codes))) {
                $this->assertTrue($output, "Should be valid, code {$i}.");
            } else {
                $this->assertFalse($output, "Should NOT be valid, code {$i}.");
            }
        }
    }
}
