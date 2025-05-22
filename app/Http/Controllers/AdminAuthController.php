<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminAuthController extends Controller
{
    // Login
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'admin' => $admin
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    // Update Profile (name & image)
    public function updateProfile(Request $request)
    {
        $admin = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048'
        ]);

        $admin->name = $request->name;

        if ($request->hasFile('image')) {
            if ($admin->image) {
                Storage::disk('public')->delete($admin->image);
            }
            $path = $request->file('image')->store('images/admins', 'public');
            $admin->image = $path;
        }

        $admin->save();

        return response()->json(['admin' => $admin]);
    }

    // Ganti Password
    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6|confirmed'
        ]);

        $admin = $request->user();

        if (!Hash::check($request->old_password, $admin->password)) {
            return response()->json(['message' => 'Old password incorrect'], 400);
        }

        $admin->password = Hash::make($request->new_password);
        $admin->save();

        return response()->json(['message' => 'Password updated']);
    }

    // Forget Password (send reset link)
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $admin = Admin::where('email', $request->email)->first();
        if (!$admin) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        $token = Str::random(60);
        DB::table('password_resets')->updateOrInsert(
            ['email' => $admin->email],
            ['token' => $token, 'created_at' => now()]
        );

        // Kirim email 
        Mail::raw("Token reset password Anda: $token", function ($message) use ($admin) {
            $message->to($admin->email)
                ->subject('Reset Password Admin');
        });

        return response()->json(['message' => 'Reset link sent to email']);
    }

    // Reset Password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed'
        ]);

        $reset = DB::table('password_resets')->where([
            'email' => $request->email,
            'token' => $request->token
        ])->first();

        if (!$reset) {
            return response()->json(['message' => 'Invalid token'], 400);
        }

        $admin = Admin::where('email', $request->email)->first();
        if (!$admin) {
            return response()->json(['message' => 'Admin not found'], 404);
        }

        $admin->password = Hash::make($request->password);
        $admin->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password reset successful']);
    }
}
