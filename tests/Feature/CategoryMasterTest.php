<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryMasterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $this->admin = User::factory()->create();
    }

    public function test_guests_cannot_access_category_master(): void
    {
        $response = $this->get('/admin/categories');
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_admin_can_view_category_master(): void
    {
        Category::create([
            'name' => 'Enterprise SaaS',
            'slug' => 'enterprise-saas',
            'description' => 'Multi-tenant cloud platforms',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/categories');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Categories/Index')
            ->has('categories.data', 1)
            ->where('categories.data.0.name', 'Enterprise SaaS')
        );
    }

    public function test_admin_can_create_category(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => 'Mobile Apps',
            'slug' => 'mobile-apps',
            'description' => 'Cross-platform mobile applications',
            'sort_order' => 5,
            'is_active' => true,
        ]);

        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories', [
            'name' => 'Mobile Apps',
            'slug' => 'mobile-apps',
            'sort_order' => 5,
        ]);
    }

    public function test_admin_can_quick_add_category_via_json(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/admin/categories', [
            'name' => 'AI & Machine Learning',
            'description' => 'Predictive modeling and LLM systems',
            'is_active' => true,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'category' => [
                'name' => 'AI & Machine Learning',
                'slug' => 'ai-machine-learning',
            ],
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'AI & Machine Learning',
        ]);
    }

    public function test_admin_can_update_category_and_syncs_portfolio(): void
    {
        $category = Category::create([
            'name' => 'Old Category',
            'slug' => 'old-category',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $portfolio = Portfolio::create([
            'title' => 'Test Project',
            'category_id' => $category->id,
            'category' => 'Old Category',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/categories/{$category->id}", [
            'name' => 'New Category Name',
            'slug' => 'new-category-name',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'New Category Name',
        ]);

        // Verify linked portfolio's category string was also updated
        $this->assertDatabaseHas('portfolios', [
            'id' => $portfolio->id,
            'category' => 'New Category Name',
        ]);
    }

    public function test_admin_can_delete_category_and_unlinks_portfolios(): void
    {
        $category = Category::create([
            'name' => 'Category To Delete',
            'slug' => 'category-to-delete',
            'is_active' => true,
        ]);

        $portfolio = Portfolio::create([
            'title' => 'Portfolio Project',
            'category_id' => $category->id,
            'category' => 'Category To Delete',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/categories/{$category->id}");

        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);

        // Portfolio remains, category_id is unlinked
        $this->assertDatabaseHas('portfolios', [
            'id' => $portfolio->id,
            'category_id' => null,
        ]);
    }

    public function test_portfolio_create_and_edit_receive_categories(): void
    {
        Category::create([
            'name' => 'Cloud Solutions',
            'slug' => 'cloud-solutions',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $createResponse = $this->actingAs($this->admin)->get('/admin/portfolios/create');
        $createResponse->assertOk();
        $createResponse->assertInertia(fn ($page) => $page
            ->component('Admin/Portfolios/Create')
            ->has('categories', 1)
            ->where('categories.0.name', 'Cloud Solutions')
        );

        $portfolio = Portfolio::create([
            'title' => 'Existing Project',
            'category' => 'Cloud Solutions',
            'is_active' => true,
        ]);

        $editResponse = $this->actingAs($this->admin)->get("/admin/portfolios/{$portfolio->id}/edit");
        $editResponse->assertOk();
        $editResponse->assertInertia(fn ($page) => $page
            ->component('Admin/Portfolios/Edit')
            ->has('categories', 1)
        );
    }

    public function test_admin_can_create_portfolio_with_category_master_id(): void
    {
        $category = Category::create([
            'name' => 'Web Architecture',
            'slug' => 'web-architecture',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/portfolios', [
            'title' => 'Scalable Enterprise App',
            'category_id' => $category->id,
            'description' => 'A robust cloud platform',
            'is_active' => true,
        ]);

        $response->assertRedirect('/admin/portfolios');
        $this->assertDatabaseHas('portfolios', [
            'title' => 'Scalable Enterprise App',
            'category_id' => $category->id,
            'category' => 'Web Architecture',
        ]);
    }

    public function test_frontend_receives_active_categories_and_excludes_inactive(): void
    {
        Category::create([
            'name' => 'Active Category',
            'slug' => 'active-cat',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Inactive Category',
            'slug' => 'inactive-cat',
            'sort_order' => 2,
            'is_active' => false,
        ]);

        $response = $this->get('/');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->has('categories', 1)
            ->where('categories.0.name', 'Active Category')
        );
    }
}
