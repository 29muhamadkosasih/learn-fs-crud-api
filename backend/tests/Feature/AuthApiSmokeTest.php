<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthApiSmokeTest extends TestCase
{
    public function test_register_and_login_endpoint_work(): void
    {
        $email = 'smoke_' . now()->format('YmdHis') . '_' . random_int(100, 999) . '@example.com';

        $registerResponse = $this->postJson('/api/register', [
            'name' => 'Smoke User',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $registerResponse
            ->assertCreated()
            ->assertJsonStructure([
                'token_type',
                'access_token',
                'user' => ['id', 'name', 'email'],
                'message',
            ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'password123',
        ]);

        $loginResponse
            ->assertOk()
            ->assertJsonStructure([
                'token_type',
                'access_token',
                'user' => ['id', 'name', 'email'],
                'message',
            ]);
    }
}
