<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('admin.profile.show');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required'],
        ]);

        // Determine if input is email or phone
        $loginField = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        
        $credentials = [
            $loginField => $request->email,
            'password' => $request->password
        ];

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.profile.show'));
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // --- Forgot Password Methods (Guest) ---

    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email không tồn tại trong hệ thống.']);
        }

        // Generate OTP
        $otp = rand(100000, 999999);

        // Store in session
        session([
            'reset_email' => $user->email,
            'reset_otp' => $otp,
            'reset_otp_expires_at' => now()->addMinutes(10),
        ]);

        // Send Email
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\PasswordChangeOtp($otp));
        } catch (\Exception $e) {
            return back()->with('error', 'Không thể gửi email OTP. Vui lòng thử lại sau.');
        }

        return redirect()->route('password.verify');
    }

    public function showResetVerifyForm()
    {
        if (!session('reset_email') || !session('reset_otp')) {
            return redirect()->route('password.request');
        }
        return view('auth.passwords.verify');
    }

    public function verifyResetOtpAndChange(Request $request)
    {
        $request->validate([
            'otp' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if (!session('reset_otp') || now()->greaterThan(session('reset_otp_expires_at'))) {
            return back()->with('error', 'Mã OTP đã hết hạn hoặc không hợp lệ.');
        }

        if ($request->otp != session('reset_otp')) {
            return back()->with('error', 'Mã OTP không chính xác.');
        }

        $user = \App\Models\User::where('email', session('reset_email'))->first();

        if (!$user) {
            return redirect()->route('password.request')->with('error', 'Lỗi xác thực người dùng.');
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        session()->forget(['reset_email', 'reset_otp', 'reset_otp_expires_at']);

        return redirect()->route('login')->with('success', 'Đổi mật khẩu thành công. Vui lòng đăng nhập.');
    }
}
