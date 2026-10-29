<?php

namespace App\Http\Controllers;

use App\Services\Auth\CredentialAuthenticator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm() {
        return view('auth.login');
    }

    public function login(Request $request, CredentialAuthenticator $authenticator) {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $result = $authenticator->attempt($credentials['username'], $credentials['password']);

        if ($result) {
            Auth::login($result['user'], $request->boolean('remember'));
            $request->session()->regenerate();
            if ($result['branch_name'] !== null) {
                session(['current_branch_name' => $result['branch_name']]);
            }
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'username' => 'اسم المستخدم أو كلمة المرور غير صحيحة.',
        ]);
    }

    /*
    public function showRegisterForm() {
        return view('auth.register');
    }

    public function register(Request $request) {
        $data = $request->validate([
            'username' => 'required|unique:users',
            'fullname' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ]);

        $role = \App\Models\Role::where('name', 'Member')->first();

        $user = \App\Models\User::create([
            'username' => $data['username'],
            'fullname' => $data['fullname'],
            'email' => $data['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
            'role_id' => $role ? $role->id : 1,
        ]);

        \Illuminate\Support\Facades\Auth::login($user);

        return redirect('dashboard');
    }
    */

    public function logout(Request $request) {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
