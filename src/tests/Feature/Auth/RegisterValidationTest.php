<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function name_is_required()
    {
        $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function email_is_required()
    {
        $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function password_must_be_at_least_8_characters()
    {
        $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'pass123', // 7文字
            'password_confirmation' => 'pass123',
        ])->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function password_confirmation_must_match()
    {
        $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password999',
        ])->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function password_is_required()
    {
        $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ])->assertSessionHasErrors(['password']);
    }
}
