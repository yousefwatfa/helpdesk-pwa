<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\Branding;
use App\Support\IconGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/** Branding: system name, organisation, primary colour and logo (root admins only). */
class SettingsController extends Controller
{
    public function edit()
    {
        return view('admin.settings.edit');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'short_name' => ['required', 'string', 'max:20'],
            'organization' => ['nullable', 'string', 'max:120'],
            'primary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'اسم النظام مطلوب.',
            'short_name.required' => 'الاسم المختصر مطلوب.',
            'short_name.max' => 'الاسم المختصر يجب ألا يزيد عن 20 حرفاً (يظهر تحت أيقونة التطبيق).',
            'primary_color.regex' => 'اختر لوناً صحيحاً.',
            'logo.image' => 'الشعار يجب أن يكون صورة.',
            'logo.mimes' => 'الشعار يجب أن يكون PNG أو JPG أو WEBP.',
            'logo.max' => 'حجم الشعار يجب ألا يزيد عن 2 ميغابايت.',
        ]);

        $directory = public_path(Branding::directory());
        $logo = branding()->logo;

        if ($request->boolean('remove_logo')) {
            File::cleanDirectory($directory);
            $logo = null;
        }

        if ($request->hasFile('logo')) {
            File::ensureDirectoryExists($directory);
            File::cleanDirectory($directory);

            $file = $request->file('logo');
            $logo = 'logo.' . $file->extension();
            $file->move($directory, $logo);

            try {
                IconGenerator::fromLogo($directory . '/' . $logo, $directory);
            } catch (\Throwable $e) {
                report($e);
                // the logo still shows in the pages; the app icon stays the default
            }
        }

        Setting::putMany([
            'brand.name' => $data['name'],
            'brand.short_name' => $data['short_name'],
            'brand.organization' => $data['organization'] ?? '',
            'brand.primary_color' => strtoupper($data['primary_color']),
            'brand.logo' => $logo,
            // new query string so browsers and installed apps pick up the new icons
            'brand.version' => (string) time(),
        ]);

        app()->forgetInstance(Branding::class);

        return redirect()->route('admin.settings.edit')->with('success', 'تم حفظ الإعدادات.');
    }
}
