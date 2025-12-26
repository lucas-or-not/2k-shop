<?php

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Product API', function () {
    it('can get all products', function () {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);
        // Login the user using session
        $this->post('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);
        Product::factory()->count(5)->create(['stock_quantity' => 10]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);
    });

    it('includes stock_quantity in product response', function () {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);
        $this->post('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);
        $product = Product::factory()->create(['stock_quantity' => 15]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.stock_quantity', 15);
    });

    it('can get single product with stock_quantity', function () {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);
        $this->post('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);
        $product = Product::factory()->create(['stock_quantity' => 20]);

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.stock_quantity', 20);
    });
});
