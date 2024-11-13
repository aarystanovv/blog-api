<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Database\Seeders\RolesAndPermissionsSeeder;

class PostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function createAuthenticatedUserWithPermission($permissions = [])
    {
        $user = User::factory()->create();

        $role = Role::firstOrCreate(['name' => 'TempRoleForTesting', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);

        $user->assignRole($role);
        $this->actingAs($user);

        return $user;
    }

    /** @test */
    public function authenticated_user_can_create_post()
    {
        $this->createAuthenticatedUserWithPermission(['create_posts']);

        $response = $this->postJson('/api/posts', [
            'title' => 'Sample Post',
            'content' => 'This is a sample post content.',
            'status' => 'draft'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'title', 'content', 'status']]);
    }

    /** @test */
    public function user_without_permission_cannot_create_post()
    {
        $this->createAuthenticatedUserWithPermission();

        $response = $this->postJson('/api/posts', [
            'title' => 'Unauthorized Post',
            'content' => 'This should fail.',
            'status' => 'draft'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_view_a_post()
    {
        $this->createAuthenticatedUserWithPermission();
        $post = Post::factory()->create();

        $response = $this->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $post->id)
            ->assertJsonPath('data.title', $post->title)
            ->assertJsonPath('data.content', $post->content);
    }

    /** @test */
    public function non_existent_post_returns_404_on_view()
    {
        $this->createAuthenticatedUserWithPermission();
        $response = $this->getJson("/api/posts/99999");
        $response->assertStatus(404);
    }

    /** @test */
    public function authenticated_user_can_update_post()
    {
        $user = $this->createAuthenticatedUserWithPermission(['edit_posts']);
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->putJson("/api/posts/{$post->id}", [
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'status' => 'published'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $post->id)
            ->assertJsonPath('data.title', 'Updated Title')
            ->assertJsonPath('data.content', 'Updated content')
            ->assertJsonPath('data.status', 'published');
    }

    /** @test */
    public function user_without_permission_cannot_update_post()
    {
        $user = $this->createAuthenticatedUserWithPermission();
        $post = Post::factory()->create();

        $response = $this->putJson("/api/posts/{$post->id}", [
            'title' => 'Unauthorized Update',
            'content' => 'This should fail',
            'status' => 'draft'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function non_existent_post_returns_404_on_update()
    {
        $user = $this->createAuthenticatedUserWithPermission(['edit_posts']);
        $response = $this->putJson("/api/posts/99999", [
            'title' => 'Nonexistent Update',
            'content' => 'This should fail',
            'status' => 'draft'
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function authenticated_user_can_delete_post()
    {
        $user = $this->createAuthenticatedUserWithPermission(['delete_posts']);
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    /** @test */
    public function user_without_permission_cannot_delete_post()
    {
        $user = $this->createAuthenticatedUserWithPermission();
        $post = Post::factory()->create();

        $response = $this->deleteJson("/api/posts/{$post->id}");
        $response->assertStatus(403);
    }

    /** @test */
    public function non_existent_post_returns_404_on_delete()
    {
        $user = $this->createAuthenticatedUserWithPermission(['delete_posts']);
        $response = $this->deleteJson("/api/posts/99999");
        $response->assertStatus(404);
    }
}
