<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile.show', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,'.$user->id,
            'position' => 'nullable|string|max:255',
        ]);

        $user->update($request->only('name', 'email', 'position'));

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function uploadSignature(Request $request)
    {
        $request->validate([
            'signature' => 'required|file|mimes:png,jpg,jpeg|max:2048',
        ]);

        $user = Auth::user();

        // Delete old signature
        if ($user->signature_path) {
            Storage::disk('public')->delete($user->signature_path);
        }

        $path = $request->file('signature')->store('signatures/users', 'public');
        $user->update(['signature_path' => $path]);

        return back()->with('success', 'Signature uploaded successfully.');
    }

    public function removeSignature()
    {
        $user = Auth::user();
        if ($user->signature_path) {
            Storage::disk('public')->delete($user->signature_path);
            $user->update(['signature_path' => null]);
        }
        return back()->with('success', 'Signature removed.');
    }
}
