<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile.index', [
            'user' => Auth::user()->load('branches'),
        ]);
    }

    /**
     * Contact details and office for everyone; name and email only for local
     * accounts (LDAP users get them from Active Directory).
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $isLocal = $user->auth_type === 'normal';

        $rules = [
            'whatsapp_number' => ['nullable', 'regex:/^\+?[0-9]{6,20}$/', Rule::unique('users', 'whatsapp_number')->ignore($user->id)],
            'office_location' => ['nullable', 'string', 'max:255'],
        ];
        if ($isLocal) {
            $rules['fullname'] = ['required', 'string', 'max:255'];
            $rules['email'] = ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)];
        }

        $validated = $request->validate($rules, [
            'whatsapp_number.regex' => 'رقم الجوال يجب أن يحتوي على أرقام فقط.',
            'whatsapp_number.unique' => 'رقم الجوال هذا مسجل لمستخدم آخر.',
            'email.unique' => 'البريد الإلكتروني مسجل لمستخدم آخر.',
        ]);

        $user->fill($validated)->save();

        return back()->with('success', 'تم تحديث بياناتك بنجاح.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->auth_type === 'normal', 403);

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'كلمة المرور الحالية غير صحيحة.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',
        ]);

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح.');
    }

    public function getUserDetails(User $user)
    {
        return response()->json([
            'full_name' => $user->fullname,
            'email' => $user->email,
            'whatsapp_number' => $user->whatsapp_number,
            'office_location' => $user->office_location ?: 'غير محدد',
            'branches' => $user->branches()->pluck('name')->join('، '),
        ]);
    }
}
