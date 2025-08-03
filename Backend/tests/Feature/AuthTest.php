<?php

declare(strict_types = 1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user registration
     */
    public function test_user_can_register_successfully(): void
    {
        $body = [
            'name'                  => fake()->name(),
            'email'                 => fake()->unique()->safeEmail(),
            'password'              => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->postJson('/api/auth/register', $body);
        $response->assertCreated();

        $json = $response->json();

        $this->assertTrue($json['data']['success']);
        $this->assertNotEmpty($json['data']['access_token']);

        $this->assertDatabaseHas('users', [
            'email' => $body['email'],
        ]);
    }

    /**
     * Test user login
     */
    public function test_user_can_login_successfully(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $body = [
            'email'    => $user->email,
            'password' => 'password',
        ];

        $response = $this->postJson('/api/auth/login', $body);
        $response->assertOk();

        $json = $response->json();
        $this->assertTrue($json['data']['success']);
        $this->assertNotEmpty($json['data']['access_token']);
        $this->assertEquals($body['email'], $json['data']['user']['email']);

        $this->assertDatabaseHas('users', [
            'email' => $body['email'],
        ]);
    }

    /**
     * Test get authenticated user
     */
    public function test_user_can_get_authenticated_user(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/auth/user');
        $response->assertOk();

        $json = $response->json();
        $this->assertEquals($user->email, $json['data']['email']);
    }

    /**
     * Test user logout
     */
    public function test_user_can_logout(): void
    {
        $user  = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/auth/logout');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Successfully logged out',
            ]);

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/auth/user')
            ->assertUnauthorized();
    }
}
