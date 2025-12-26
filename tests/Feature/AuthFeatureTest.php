<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Authentication API', function () {
    it('can register new user', function () {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    });

    it('can login user', function () {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user',
            ]);
    });

    it('can logout user', function () {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->postJson('/api/v1/auth/logout');
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logout successful',
            ]);
    });

    it('can get current user', function () {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->getJson('/api/v1/auth/user');
        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
    });
}); 