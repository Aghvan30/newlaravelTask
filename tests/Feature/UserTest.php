<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
//    use RefreshDatabase;

    /** @test */
    public function it_denies_non_admin_users_from_listing_users()
    {
        // Create a regular user (non-admin)
        $user = User::factory()->create([
            'role' => 'user', // Ensure your database has a 'role' column
        ]);

        // Authenticate as the non-admin user
        $this->actingAs($user);

        // Attempt to fetch users list
        $response = $this->getJson('/api/users');

        // Assert that the request is forbidden
        $response->assertStatus(403);
    }

    /** @test */
    public function it_allows_admin_users_to_list_users()
    {
        // Create an admin user
        $admin = User::factory()->create([
            'role' => 'admin', // Ensure the admin user has the correct role
        ]);

        // Authenticate as the admin user
        $this->actingAs($admin);

        // Attempt to fetch users list
        $response = $this->getJson('/api/users');

        // Assert that the request is successful
        $response->assertStatus(200);
    }
}
