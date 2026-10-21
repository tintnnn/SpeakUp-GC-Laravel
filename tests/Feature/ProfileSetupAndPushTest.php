<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProfileSetupAndPushTest extends TestCase
{
    use RefreshDatabase;

    public function test_laya_shows_after_signup_and_setup_saves_the_alias(): void
    {
        $user = User::factory()->create([
            'email' => 'student@gordoncollege.edu.ph',
            'profile_setup_complete' => false,
            'alias' => null,
            'is_admin' => false,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Profile Setup', false)
            ->assertSee('Laya', false);

        $this->actingAs($user)
            ->postJson('/account/setup', ['alias' => 'BlueStar22'])
            ->assertOk()
            ->assertJson(['alias' => 'BlueStar22']);

        $user->refresh();
        $this->assertTrue($user->profile_setup_complete);
        $this->assertSame('BlueStar22', $user->alias);
        $this->assertSame('both', $user->notification_preference);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Profile Setup', false);
    }

    public function test_duplicate_alias_gets_a_suffix(): void
    {
        User::factory()->create([
            'email' => 'taken@gordoncollege.edu.ph',
            'alias' => 'BlueStar22',
            'profile_setup_complete' => true,
        ]);
        $user = User::factory()->create([
            'email' => 'student@gordoncollege.edu.ph',
            'profile_setup_complete' => false,
            'alias' => null,
        ]);

        $this->actingAs($user)
            ->postJson('/account/setup', ['alias' => 'BlueStar22'])
            ->assertOk()
            ->assertJson(['alias' => 'BlueStar22(1)']);
    }

    public function test_push_token_is_stored_and_removed_for_this_device(): void
    {
        $user = User::factory()->create([
            'email' => 'student@gordoncollege.edu.ph',
            'profile_setup_complete' => true,
        ]);

        $this->actingAs($user)
            ->postJson('/push-token', [
                'token' => 'device-token-123',
                'userAgent' => 'Test',
                'platform' => 'Mac',
            ])
            ->assertOk();

        $this->assertDatabaseHas('fcm_tokens', [
            'user_id' => $user->id,
            'token' => 'device-token-123',
        ]);
        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'push_enabled' => true,
        ]);

        $this->actingAs($user)
            ->deleteJson('/push-token', ['token' => 'device-token-123'])
            ->assertOk();

        $this->assertDatabaseMissing('fcm_tokens', ['token' => 'device-token-123']);
    }

    public function test_rights_tabs_are_not_pulled_up_over_the_card(): void
    {
        $user = User::factory()->create([
            'email' => 'student@gordoncollege.edu.ph',
            'profile_setup_complete' => true,
            'alias' => 'BlueStar22',
        ]);

        $html = $this->actingAs($user)->get('/know-your-rights')->assertOk()->getContent();

        $this->assertStringContainsString('sticky top-0', $html);
        $this->assertStringNotContainsString('-mt-4 md:mt-0', $html);
        $this->assertStringNotContainsString('pt-4 dark:bg-background lg:px-8', $html);
    }

    public function test_account_page_does_not_fake_the_push_toggle(): void
    {
        $user = User::factory()->create([
            'email' => 'student@gordoncollege.edu.ph',
            'profile_setup_complete' => true,
            'alias' => 'BlueStar22',
        ]);

        $this->actingAs($user)
            ->get('/account')
            ->assertOk()
            ->assertSee('pushSettings()', false)
            ->assertDontSee('pushEnabled = !pushEnabled', false);

        $this->assertSame(0, DB::table('fcm_tokens')->count());
    }
}
