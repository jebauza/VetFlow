<?php

namespace Tests\Feature\Api\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterApiTest extends TestCase
{
    use RefreshDatabase;

    private $api = 'api/auth/register';

    public function test_register_201(): void
    {
        $this->postJson($this->api, [
            'name' => 'Test',
            'surname' => 'Test',
            'email' => 'test@gmail.com',
            'password' => 'test123456789',
        ])
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'surname',
                'email',
            ]);
    }

    public function test_register_validation_with_invalid_data_422(): void
    {
        $this->postJson($this->api, [
            'email' => 'test',
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'name',
                'surname',
                'email',
                'password'
            ]);
    }
}
