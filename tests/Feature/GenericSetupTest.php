<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Setting;
use App\Models\User;
use App\Services\IpTaggingService;
use App\Support\Branding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\BuildsTickets;
use Tests\TestCase;

/** Things that make the system usable by any organisation: local sign-in, branding, branches, install. */
class GenericSetupTest extends TestCase
{
    use RefreshDatabase, BuildsTickets;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTicketWorld();
        // keep test uploads away from a real public/branding folder
        config(['helpdesk.branding_directory' => 'branding-test']);
        app()->forgetInstance(Branding::class);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(public_path(Branding::directory()));
        parent::tearDown();
    }

    public function test_local_account_can_sign_in(): void
    {
        $user = $this->member(['username' => 'sara', 'password' => Hash::make('secret-pass'), 'auth_type' => 'normal']);

        $this->post('/login', ['username' => 'sara', 'password' => 'secret-pass'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_unknown_user_is_rejected_when_ldap_is_off(): void
    {
        config(['helpdesk.ldap.enabled' => false]);

        $this->post('/login', ['username' => 'nobody', 'password' => 'whatever'])
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_root_admin_can_change_branding(): void
    {
        $root = $this->rootAdmin();

        $this->actingAs($root)->put('/admin/settings', [
            'name' => 'نظام دعم الشركة',
            'short_name' => 'الدعم',
            'organization' => 'شركة النور',
            'primary_color' => '#7a1fa2',
        ])->assertRedirect('/admin/settings');

        app()->forgetInstance(Branding::class);
        $manifest = $this->get('/manifest.webmanifest')->json();
        $this->assertSame('نظام دعم الشركة', $manifest['name']);
        $this->assertSame('الدعم', $manifest['short_name']);
        $this->assertSame('#7A1FA2', $manifest['theme_color']);

        $this->get('/login')->assertSee('نظام دعم الشركة')->assertSee('شركة النور');
    }

    public function test_logo_upload_generates_app_icons(): void
    {
        $root = $this->rootAdmin();

        $this->actingAs($root)->put('/admin/settings', [
            'name' => 'Help Desk',
            'short_name' => 'Help',
            'primary_color' => '#0077C2',
            'logo' => UploadedFile::fake()->image('logo.png', 300, 300),
        ])->assertSessionHasNoErrors();

        foreach (['icon-192.png', 'icon-512.png', 'icon-maskable-512.png', 'apple-touch-icon.png'] as $file) {
            $this->assertFileExists(public_path(Branding::directory() . '/' . $file));
        }
        $this->assertSame('logo.png', Setting::find('brand.logo')->value);

        app()->forgetInstance(Branding::class);
        $this->assertStringContainsString('/branding-test/icon-192.png', branding()->iconUrl(192));
    }

    public function test_only_root_can_open_settings_and_branches(): void
    {
        $admin = $this->departmentAdmin();

        $this->actingAs($admin)->get('/admin/settings')->assertForbidden();
        $this->actingAs($admin)->get('/admin/branches')->assertForbidden();
    }

    public function test_branch_ip_ranges_are_validated_and_tag_tickets(): void
    {
        $root = $this->rootAdmin();

        $this->actingAs($root)->post('/admin/branches', ['name' => 'Branch A', 'ip_ranges' => '10.1.0.0/99'])
            ->assertSessionHasErrors('ip_ranges');

        $this->actingAs($root)->post('/admin/branches', ['name' => 'Branch A', 'ip_ranges' => '10.1.0.0/16 , 10.2.0.0/16'])
            ->assertSessionHasNoErrors();

        $this->assertSame('10.1.0.0/16, 10.2.0.0/16', Branch::first()->ip_ranges);
        $this->assertSame('Branch A', IpTaggingService::getTagByIp('10.2.5.9'));
        $this->assertSame('غير معروف', IpTaggingService::getTagByIp('192.168.1.5'));
    }

    public function test_branch_admin_only_sees_tickets_from_their_branch(): void
    {
        $north = Branch::create(['name' => 'North']);
        $south = Branch::create(['name' => 'South']);

        $admin = $this->departmentAdmin();
        $admin->branches()->attach($north);

        $northMember = $this->member();
        $northMember->branches()->attach($north);
        $southMember = $this->member();
        $southMember->branches()->attach($south);

        $this->assertTrue($admin->can('view', $this->ticketFor($northMember)));
        $this->assertFalse($admin->can('view', $this->ticketFor($southMember)));

        // an admin without branches sees every branch
        $this->assertTrue($this->departmentAdmin()->can('view', $this->ticketFor($southMember)));
    }

    public function test_admin_ticket_list_matches_departments_and_branches(): void
    {
        $north = Branch::create(['name' => 'North']);
        $northMember = $this->member();
        $northMember->branches()->attach($north);
        $northTicket = $this->ticketFor($northMember);
        $otherTicket = $this->ticketFor($this->member());

        $otherDepartment = \App\Models\Department::create(['name' => 'HR']);
        $hrCategory = \App\Models\Category::create(['name' => 'Payroll', 'department_id' => $otherDepartment->id]);
        $hrTicket = $this->ticketFor($northMember);
        $hrTicket->update(['category_id' => $hrCategory->id]);

        $visible = function (User $user) {
            $this->actingAs($user);
            return app(\App\DataTables\AdminTicketsDataTable::class)->visibleTo(\App\Models\Ticket::query())->pluck('id')->sort()->values()->all();
        };

        // no branches: every ticket of their department, nothing from HR
        $this->assertSame([$northTicket->id, $otherTicket->id], $visible($this->departmentAdmin()));

        $branchAdmin = $this->departmentAdmin();
        $branchAdmin->branches()->attach($north);
        $this->assertSame([$northTicket->id], $visible($branchAdmin));

        $this->assertCount(3, $visible($this->rootAdmin()));
    }

    public function test_local_user_can_update_profile_and_password(): void
    {
        $user = $this->member(['password' => Hash::make('old-password'), 'auth_type' => 'normal']);

        $this->actingAs($user)->put('/profile/update', [
            'fullname' => 'اسم جديد',
            'email' => 'new@example.test',
            'whatsapp_number' => '+970599000000',
            'office_location' => 'الطابق الثاني',
        ])->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('اسم جديد', $user->fullname);
        $this->assertSame('الطابق الثاني', $user->office_location);

        $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'wrong',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_ldap_user_cannot_change_name_or_password_here(): void
    {
        $user = $this->member(['fullname' => 'From AD', 'auth_type' => 'ldap', 'password' => null]);

        $this->actingAs($user)->put('/profile/update', ['fullname' => 'Changed', 'office_location' => 'Lab'])
            ->assertSessionHasNoErrors();
        $this->assertSame('From AD', $user->fresh()->fullname);

        $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'x', 'password' => 'new-password', 'password_confirmation' => 'new-password',
        ])->assertForbidden();
    }

    public function test_install_command_creates_the_first_root_admin_once(): void
    {
        $this->artisan('helpdesk:install', [
            '--name' => 'Acme Support',
            '--organization' => 'Acme',
            '--admin-username' => 'owner',
            '--admin-name' => 'The Owner',
            '--admin-password' => 'long-enough',
        ])->assertSuccessful();

        $owner = User::where('username', 'owner')->firstOrFail();
        $this->assertTrue($owner->is_root);
        $this->assertSame('normal', $owner->auth_type);
        $this->assertSame('Acme Support', Setting::find('brand.name')->value);

        $this->artisan('helpdesk:install', ['--name' => 'Acme Support', '--organization' => 'Acme'])->assertSuccessful();
        $this->assertSame(1, User::where('is_root', true)->count());
    }

    public function test_root_admin_creates_local_user_with_branch(): void
    {
        $root = $this->rootAdmin();
        $branch = Branch::create(['name' => 'HQ']);

        $this->actingAs($root)->post('/admin/users', [
            'username' => 'new.user',
            'fullname' => 'New User',
            'auth_type' => 'normal',
            'password' => 'password123',
            'role_id' => $this->memberRole->id,
            'branches' => [$branch->id],
        ])->assertRedirect('/admin/users');

        $user = User::where('username', 'new.user')->firstOrFail();
        $this->assertTrue($user->branches->contains($branch));

        $this->post('/logout');
        $this->post('/login', ['username' => 'new.user', 'password' => 'password123'])->assertRedirect('/dashboard');
    }
}
