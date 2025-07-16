<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SupabaseTokenValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Configure SQLite database for testing
        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set a trusted host pattern to bypass host validation
        $this->app['config']->set('app.url', 'http://localhost');
        $this->app['request']->headers->set('host', 'localhost');
    }

    public function test_auth_me_endpoint_with_jwt_creates_user_on_first_login()
    {
        // Mock JWT payload for a new user
        $jwtPayload = (object) [
            'sub' => 'test-supabase-uid-123',
            'email' => 'newuser@example.com',
            'name' => 'Test User',
            'user_metadata' => (object) [
                'name' => 'Test User'
            ]
        ];

        // Mock the SupabaseTokenValidator
        $mockValidator = Mockery::mock(SupabaseTokenValidator::class);
        $mockValidator->shouldReceive('validateToken')
            ->with('valid-jwt-token')
            ->andReturn($jwtPayload);

        $this->app->instance(SupabaseTokenValidator::class, $mockValidator);

        // Ensure no user exists initially
        $this->assertDatabaseMissing('users', [
            'supabase_uid' => 'test-supabase-uid-123'
        ]);

        // Make request with JWT token in Authorization header
        $response = $this->getJson('/api/auth/me', [
            'Authorization' => 'Bearer valid-jwt-token'
        ]);

        // Assert response is successful
        $response->assertStatus(200);

        // Assert user was created in database
        $this->assertDatabaseHas('users', [
            'supabase_uid' => 'test-supabase-uid-123',
            'email' => 'newuser@example.com',
            'name' => 'Test User'
        ]);

        // Assert response contains correct user data
        $response->assertJson([
            'email' => 'newuser@example.com',
            'supabase_uid' => 'test-supabase-uid-123'
        ]);

        // Verify the created user data
        $createdUser = User::where('supabase_uid', 'test-supabase-uid-123')->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals('newuser@example.com', $createdUser->email);
        $this->assertEquals('Test User', $createdUser->name);
    }

    public function test_auth_me_endpoint_returns_existing_user_data()
    {
        // Create an existing user
        $existingUser = User::create([
            'supabase_uid' => 'existing-supabase-uid',
            'email' => 'existing@example.com',
            'name' => 'Existing User',
            'password' => bcrypt('password')
        ]);

        // Mock JWT payload for existing user
        $jwtPayload = (object) [
            'sub' => 'existing-supabase-uid',
            'email' => 'existing@example.com',
            'name' => 'Existing User'
        ];

        // Mock the SupabaseTokenValidator
        $mockValidator = Mockery::mock(SupabaseTokenValidator::class);
        $mockValidator->shouldReceive('validateToken')
            ->with('valid-jwt-token')
            ->andReturn($jwtPayload);

        $this->app->instance(SupabaseTokenValidator::class, $mockValidator);

        // Make request with JWT token
        $response = $this->getJson('/api/auth/me', [
            'Authorization' => 'Bearer valid-jwt-token'
        ]);

        // Assert response is successful and returns existing user data
        $response->assertStatus(200)
            ->assertJson([
                'id' => $existingUser->id,
                'email' => $existingUser->email,
                'supabase_uid' => $existingUser->supabase_uid
            ]);
    }

    public function test_auth_me_endpoint_returns_unauthorized_without_token()
    {
        // Make request without Authorization header
        $response = $this->getJson('/api/auth/me');

        // Assert unauthorized response
        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }

    public function test_auth_me_endpoint_returns_unauthorized_with_invalid_token()
    {
        // Mock the SupabaseTokenValidator to throw exception for invalid token
        $mockValidator = Mockery::mock(SupabaseTokenValidator::class);
        $mockValidator->shouldReceive('validateToken')
            ->with('invalid-jwt-token')
            ->andThrow(new \Exception('Invalid token'));

        $this->app->instance(SupabaseTokenValidator::class, $mockValidator);

        // Make request with invalid JWT token
        $response = $this->getJson('/api/auth/me', [
            'Authorization' => 'Bearer invalid-jwt-token'
        ]);

        // Assert unauthorized response
        $response->assertStatus(401)
            ->assertJson(['error' => 'Invalid token']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
