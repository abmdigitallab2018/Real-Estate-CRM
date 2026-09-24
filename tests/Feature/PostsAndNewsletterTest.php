<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\NewsletterSubscriber;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostsAndNewsletterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $this->admin = User::factory()->create();
    }

    public function test_guests_cannot_access_admin_posts(): void
    {
        $response = $this->get('/admin/posts');
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_posts_index(): void
    {
        Post::create([
            'title' => 'Scaling Distributed Memory Caches',
            'slug' => 'scaling-distributed-memory-caches',
            'category' => 'Architecture',
            'summary' => 'Techniques for high availability.',
            'content' => 'Full article body here...',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/posts');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Posts/Index')
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Scaling Distributed Memory Caches')
        );
    }

    public function test_admin_can_create_daily_post(): void
    {
        $category = Category::create([
            'name' => 'Architecture',
            'slug' => 'architecture',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/posts', [
            'title' => 'Building Event-Driven Systems',
            'category_id' => $category->id,
            'summary' => 'Asynchronous message queues in production.',
            'content' => 'This is a comprehensive guide to building resilient distributed message queues with circuit breakers.',
            'is_published' => true,
        ]);

        $response->assertRedirect('/admin/posts');
        $this->assertDatabaseHas('posts', [
            'title' => 'Building Event-Driven Systems',
            'category_id' => $category->id,
            'category' => 'Architecture',
            'slug' => 'building-event-driven-systems',
            'is_published' => true,
        ]);
    }

    public function test_admin_can_update_post(): void
    {
        $post = Post::create([
            'title' => 'Old Post Title',
            'slug' => 'old-post-title',
            'content' => 'Content here',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/posts/{$post->id}", [
            'title' => 'Updated Post Title',
            'content' => 'Updated content text here',
            'is_published' => true,
        ]);

        $response->assertRedirect('/admin/posts');
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Post Title',
        ]);
    }

    public function test_admin_can_delete_post(): void
    {
        $post = Post::create([
            'title' => 'Post To Delete',
            'slug' => 'post-to-delete',
            'content' => 'Content',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/posts/{$post->id}");
        $response->assertRedirect('/admin/posts');
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_frontend_displays_published_posts_and_excludes_drafts(): void
    {
        Post::create([
            'title' => 'Published Article',
            'slug' => 'published-article',
            'content' => 'Published body',
            'is_published' => true,
            'published_at' => now(),
        ]);

        Post::create([
            'title' => 'Draft Article',
            'slug' => 'draft-article',
            'content' => 'Draft body',
            'is_published' => false,
        ]);

        $response = $this->get('/');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->has('posts', 1)
            ->where('posts.0.title', 'Published Article')
        );
    }

    public function test_public_user_can_subscribe_to_newsletter(): void
    {
        $response = $this->post('/newsletter/subscribe', [
            'email' => 'subscriber@domain.com',
            'name' => 'Jane Dev',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('newsletter_success');
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'subscriber@domain.com',
            'name' => 'Jane Dev',
            'is_active' => true,
        ]);
    }

    public function test_duplicate_active_subscription_notifies_user(): void
    {
        NewsletterSubscriber::create([
            'email' => 'already@domain.com',
            'is_active' => true,
        ]);

        $response = $this->post('/newsletter/subscribe', [
            'email' => 'already@domain.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('newsletter_info');
    }

    public function test_admin_can_view_newsletter_subscribers_and_stats(): void
    {
        NewsletterSubscriber::create([
            'email' => 'sub1@test.com',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/newsletter');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Newsletter/Index')
            ->has('subscribers.data', 1)
            ->has('stats.total')
            ->where('stats.total', 1)
        );
    }

    public function test_admin_can_toggle_and_delete_newsletter_subscriber(): void
    {
        $subscriber = NewsletterSubscriber::create([
            'email' => 'toggle@test.com',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)->post("/admin/newsletter/{$subscriber->id}/toggle");
        $this->assertDatabaseHas('newsletter_subscribers', [
            'id' => $subscriber->id,
            'is_active' => false,
        ]);

        $this->actingAs($this->admin)->delete("/admin/newsletter/{$subscriber->id}");
        $this->assertDatabaseMissing('newsletter_subscribers', ['id' => $subscriber->id]);
    }
}
