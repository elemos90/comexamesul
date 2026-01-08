<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    public function testLoginPageLoads()
    {
        $url = 'http://localhost:8080/login';
        $headers = @get_headers($url);

        if (!$headers) {
            $this->markTestSkipped('Application not running on localhost:8080');
        }

        $this->assertStringContainsString('200 OK', $headers[0]);
    }
}
