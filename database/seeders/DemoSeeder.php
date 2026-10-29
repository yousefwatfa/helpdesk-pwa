<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Department;
use App\Models\MainDepartment;
use App\Models\Message;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Demo data for trying the system locally. Never run in production.
 *   php artisan db:seed --class=DemoSeeder
 * Logins (local accounts): demo.member / demo.admin / demo.root (system admin), password: password
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->error('DemoSeeder is for local testing only.');
            return;
        }

        $this->call(DatabaseSeeder::class);
        $member = Role::where('name', 'Member')->first();
        $admin = Role::where('name', 'Admin')->first();

        $branch = Branch::firstOrCreate(['name' => 'الفرع الرئيسي']);

        $main = MainDepartment::firstOrCreate(['name' => 'تكنولوجيا المعلومات']);
        $dept = Department::firstOrCreate(['name' => 'الدعم الفني'], ['main_department_id' => $main->id]);
        $categories = collect(['طابعة', 'شاشة / جهاز', 'شبكة وإنترنت', 'البريد الإلكتروني'])
            ->map(fn ($name) => Category::firstOrCreate(['name' => $name], ['department_id' => $dept->id]));

        $memberUser = User::updateOrCreate(['username' => 'demo.member'], [
            'fullname' => 'أحمد الموظف', 'email' => 'demo.member@example.test', 'password' => Hash::make('password'),
            'role_id' => $member->id, 'auth_type' => 'normal', 'whatsapp_number' => '0590000001',
            'office_location' => 'الطابق الأول - المحاسبة',
        ]);
        $memberUser->branches()->syncWithoutDetaching([$branch->id]);

        $adminUser = User::updateOrCreate(['username' => 'demo.admin'], [
            'fullname' => 'سارة الدعم الفني', 'email' => 'demo.admin@example.test', 'password' => Hash::make('password'),
            'role_id' => $admin->id, 'auth_type' => 'normal', 'whatsapp_number' => '0590000002',
        ]);
        $adminUser->departments()->syncWithoutDetaching([$dept->id]);

        User::updateOrCreate(['username' => 'demo.root'], [
            'fullname' => 'مدير النظام', 'email' => 'demo.root@example.test', 'password' => Hash::make('password'),
            'role_id' => $admin->id, 'auth_type' => 'normal', 'is_root' => true,
            'can_reopen_tickets' => true, 'can_access_finished_tickets' => true,
        ]);

        if (Ticket::where('created_by', $memberUser->id)->exists()) {
            return;
        }

        $samples = [
            ['الطابعة لا تطبع', 'تظهر رسالة ورق عالق في طابعة المحاسبة', 'open'],
            ['لا يصلني البريد الإلكتروني', 'الرسائل الجديدة لا تظهر منذ الصباح', 'in_progress'],
            ['انقطاع الإنترنت في غرفة الاجتماعات', 'لا يوجد اتصال بالشبكة اللاسلكية', 'pending'],
            ['تغيير شاشة الاستقبال', 'الشاشة تومض باستمرار', 'closed'],
        ];

        foreach ($samples as $i => [$title, $description, $status]) {
            $ticket = Ticket::create([
                'title' => $title, 'description' => $description, 'office_location' => 'الطابق الأول - المحاسبة',
                'category_id' => $categories[$i]->id, 'created_by' => $memberUser->id, 'status' => $status,
            ]);

            if ($status !== 'open') {
                $ticket->assignedUsers()->attach($adminUser->id);
                Message::create(['ticket_id' => $ticket->id, 'created_by' => $adminUser->id, 'content' => 'تم استلام التذكرة وسنتابع معك قريباً.']);
            }
        }
    }
}
