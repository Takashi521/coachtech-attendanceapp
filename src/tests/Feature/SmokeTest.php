<?php

namespace Tests\Feature;

use Tests\TestCase;

class SmokeTest extends TestCase
{
    public function test_root_redirects_to_login_for_guest(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
