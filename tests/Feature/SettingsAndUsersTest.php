<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsAndUsersTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_authenticated_user_can_view_users_list(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.users.index'));
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_create_new_user(): void
    {
        $response = $this->actingAs($this->user)->post(route('admin.users.store'), [
            'name' => 'Jane Admin',
            'email' => 'jane@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'name' => 'Jane Admin',
            'email' => 'jane@example.com',
        ]);
    }

    public function test_authenticated_user_can_update_user_and_password(): void
    {
        $targetUser = User::factory()->create();

        $response = $this->actingAs($this->user)->put(route('admin.users.update', $targetUser->id), [
            'name' => 'Updated Name',
            'email' => $targetUser->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_user_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->user)->delete(route('admin.users.destroy', $this->user->id));
        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['id' => $this->user->id]);
    }

    public function test_authenticated_user_can_view_settings_card_grid(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.settings.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Admin/Settings/Index'));
    }

    public function test_authenticated_user_can_update_single_setting(): void
    {
        // Ensure settings are synced from config
        SiteSetting::syncFromConfig();
        $setting = SiteSetting::where('name', 'site_title')->firstOrFail();

        $response = $this->actingAs($this->user)->post(route('admin.settings.updateSingle', $setting->id), [
            'value' => 'Bhavesh Architecture Dev',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('site_settings', [
            'id' => $setting->id,
            'value' => 'Bhavesh Architecture Dev',
        ]);
        $this->assertEquals('Bhavesh Architecture Dev', SiteSetting::get('site_title'));
    }

    public function test_authenticated_user_can_create_new_setting(): void
    {
        $response = $this->actingAs($this->user)->post(route('admin.settings.store'), [
            'title' => 'Custom Discord Link',
            'name' => 'discord_link',
            'setting_type' => 'organization',
            'field_type' => 'text',
            'value' => 'https://discord.gg/bhavesh',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('site_settings', [
            'name' => 'discord_link',
            'title' => 'Custom Discord Link',
            'value' => 'https://discord.gg/bhavesh',
        ]);
    }

    public function test_authenticated_user_can_delete_setting(): void
    {
        $setting = SiteSetting::create([
            'title' => 'Temporary Setting',
            'name' => 'temp_setting',
            'setting_type' => 'general',
            'field_type' => 'text',
            'value' => 'xyz',
        ]);

        $response = $this->actingAs($this->user)->delete(route('admin.settings.destroy', $setting->id));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('site_settings', [
            'id' => $setting->id,
        ]);
    }
}
