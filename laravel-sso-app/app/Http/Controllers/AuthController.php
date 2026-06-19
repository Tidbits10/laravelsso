<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\MockSis;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $field = filter_var($credentials['login_id'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$field => $credentials['login_id'], 'password' => $credentials['password']], true)) {
            $request->session()->regenerate();

            if (Auth::user()->role === 'admin') {
                AuditLog::create([
                    'admin_username' => Auth::user()->username ?? Auth::user()->email,
                    'action' => 'Admin logged in',
                ]);

                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('student.dashboard');
        }

        return back()->withErrors(['login_id' => 'Invalid email/username or password.'])->onlyInput('login_id');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->merge([
            'student_number' => trim((string) $request->input('student_number')),
            'email' => strtolower(trim((string) $request->input('email'))),
            'username' => trim((string) $request->input('username')),
        ]);

        $data = $request->validate([
            'student_number' => ['required', 'regex:/^\d{4}-\d{5}-SP-0$/', 'unique:users,student_number'],
            'email' => ['required', 'email', 'unique:users,email'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $sis = MockSis::where('student_number', $data['student_number'])
            ->where('email', $data['email'])
            ->first();

        if (! $sis) {
            return back()->withErrors(['student_number' => 'Student number and email do not match the SIS master list.'])->withInput();
        }

        User::create([
            'student_number' => $sis->student_number,
            'name' => $sis->name,
            'email' => $sis->email,
            'username' => $data['username'],
            'password' => $data['password'],
            'role' => 'student',
        ]);

        return redirect()->route('login')->with('success', 'Account created. You can now log in.');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $data = $request->validate([
            'student_number' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $user = User::where('student_number', $data['student_number'])
            ->where('email', $data['email'])
            ->first();

        if (! $user) {
            return back()->withErrors(['email' => 'Invalid student number or email.']);
        }

        $temporaryPassword = substr(str_shuffle('abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 10);
        $user->update(['password' => $temporaryPassword]);

        Mail::raw("Hello {$user->name}, your temporary SSO password is: {$temporaryPassword}", function ($message) use ($user) {
            $message->to($user->email)->subject('SSO Password Reset');
        });

        return back()->with('success', 'Temporary password generated. Check the mail log or configured email inbox.');
    }

    public function logout(Request $request)
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            AuditLog::create([
                'admin_username' => Auth::user()->username ?? Auth::user()->email,
                'action' => 'Admin logged out',
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
