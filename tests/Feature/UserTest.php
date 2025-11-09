<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_users_as_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;

        User::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data'  => [
                    '*' => ['id', 'name', 'email', 'role'],
                ],
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links' => [
                    '*' => ['url', 'label', 'active'],
                ],
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total',
            ]);
    }

    /** @test */
    public function it_cannot_list_users_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token    = $customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users');

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_show_user_as_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;

        $user = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users/' . $user->id);

        $response->assertStatus(200)
            ->assertJson([
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ]);
    }

    /** @test */
    public function it_can_show_own_profile_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token    = $customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users/' . $customer->id);

        $response->assertStatus(200)
            ->assertJson([
                'id'    => $customer->id,
                'name'  => $customer->name,
                'email' => $customer->email,
                'role'  => $customer->role,
            ]);
    }

    /** @test */
    public function it_cannot_show_other_user_profile_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token    = $customer->createToken('test-token')->plainTextToken;

        $otherUser = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users/' . $otherUser->id);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_update_own_profile_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token    = $customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/users/' . $customer->id, [
                'name'  => 'Updated Name',
                'email' => 'updated@example.com',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'id'    => $customer->id,
                'name'  => 'Updated Name',
                'email' => 'updated@example.com',
                'role'  => $customer->role,
            ]);
    }

    /** @test */
    public function it_cannot_update_other_user_profile_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token    = $customer->createToken('test-token')->plainTextToken;

        $otherUser = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/users/' . $otherUser->id, [
                'name'  => 'Updated Name',
                'email' => 'updated@example.com',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_update_any_user_as_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;

        $user = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/users/' . $user->id, [
                'name'  => 'Updated Name',
                'email' => 'updated@example.com',
                'role'  => 'admin',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'id'    => $user->id,
                'name'  => 'Updated Name',
                'email' => 'updated@example.com',
                'role'  => 'admin',
            ]);
    }
}
