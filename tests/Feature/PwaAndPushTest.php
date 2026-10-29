<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsTickets;
use Tests\TestCase;

class PwaAndPushTest extends TestCase
{
    use RefreshDatabase, BuildsTickets;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTicketWorld();
    }

    public function test_manifest_is_valid_and_rtl(): void
    {
        $manifest = $this->get('/manifest.webmanifest')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/manifest+json')
            ->json();

        $this->assertSame('rtl', $manifest['dir']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame(config('helpdesk.brand.name'), $manifest['name']);
        foreach ($manifest['icons'] as $icon) {
            $this->assertFileExists(public_path(ltrim(parse_url($icon['src'], PHP_URL_PATH), '/')));
        }
    }

    public function test_install_page_is_public(): void
    {
        $this->get('/install')->assertOk()->assertSee('تثبيت التطبيق');
    }

    public function test_user_can_subscribe_and_unsubscribe_push(): void
    {
        $user = $this->member();
        $endpoint = 'https://fcm.googleapis.com/fcm/send/abc';

        $this->actingAs($user)->postJson('/push-subscriptions', [
            'endpoint' => $endpoint,
            'keys' => ['p256dh' => 'pub-key', 'auth' => 'auth-token'],
            'contentEncoding' => 'aes128gcm',
        ])->assertOk();

        $this->assertSame(1, $user->pushSubscriptions()->count());

        $this->actingAs($user)->deleteJson('/push-subscriptions', ['endpoint' => $endpoint])->assertOk();

        $this->assertSame(0, $user->pushSubscriptions()->count());
    }

    public function test_push_routes_require_login(): void
    {
        $this->postJson('/push-subscriptions', [])->assertUnauthorized();
    }
}
