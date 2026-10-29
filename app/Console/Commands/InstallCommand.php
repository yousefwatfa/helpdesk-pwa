<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * First-time setup: migrate, seed the roles, save the organisation name and
 * create the first (root) admin. Safe to run again: it never overwrites users.
 */
class InstallCommand extends Command
{
    protected $signature = 'helpdesk:install
        {--name= : System name shown on the login page and the installed app}
        {--organization= : Organisation name}
        {--admin-username= : Username of the first admin}
        {--admin-name= : Full name of the first admin}
        {--admin-password= : Password of the first admin (prompted when omitted)}';

    protected $description = 'Set up the help desk: database, roles, branding and the first admin account';

    public function handle(): int
    {
        $this->call('migrate', ['--force' => true]);
        $this->call('db:seed', ['--force' => true]);

        $name = $this->option('name') ?: text('اسم النظام', default: config('helpdesk.brand.name'), required: true);
        $organization = $this->option('organization') ?? text('اسم المؤسسة (اختياري)', default: config('helpdesk.brand.organization'));

        Setting::putMany(array_filter([
            'brand.name' => $name,
            'brand.organization' => $organization,
        ], fn ($value) => $value !== null));

        if (User::where('is_root', true)->exists()) {
            $this->info('يوجد مدير نظام مسبقاً، لم يتم إنشاء حساب جديد.');
            return self::SUCCESS;
        }

        $username = $this->option('admin-username') ?: text('اسم المستخدم لمدير النظام', default: 'admin', required: true);
        $fullname = $this->option('admin-name') ?: text('الاسم الكامل لمدير النظام', default: 'مدير النظام', required: true);
        $secret = $this->option('admin-password') ?: password('كلمة المرور (8 أحرف على الأقل)', required: true);

        $validator = Validator::make(
            ['username' => $username, 'password' => $secret],
            ['username' => 'required|max:255|unique:users,username', 'password' => 'required|min:8'],
        );
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        User::create([
            'username' => $username,
            'fullname' => $fullname,
            'password' => Hash::make($secret),
            'role_id' => Role::where('name', 'Admin')->value('id'),
            'is_root' => true,
            'auth_type' => 'normal',
            'can_reopen_tickets' => true,
            'can_access_finished_tickets' => true,
        ]);

        $this->info("تم إنشاء حساب مدير النظام: {$username}");
        $this->line('سجّل الدخول ثم أكمل الإعداد: الإعدادات (الشعار واللون)، الأقسام، أنواع المشاكل، والمستخدمين.');

        return self::SUCCESS;
    }
}
