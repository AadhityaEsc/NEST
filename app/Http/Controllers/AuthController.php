<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\University;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Handle the registration of a new Student or Landlord.
     */
    public function register(Request $request)
    {
        // Validate incoming request based on role
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:student,landlord',
            'email' => 'required_if:role,student|email|unique:users',
            'phone_number' => 'required_if:role,landlord|unique:users',
            'university_id' => 'required_if:role,student|exists:universities,id',
            'password' => 'required|min:8',
        ]);

        // Strict University Domain Validation for Students
        if ($request->role === 'student') {
            $university = University::findOrFail($request->university_id);
            $emailDomain = substr(strrchr($request->email, "@"), 1);

            // e.g., verifying @wlv.ac.uk matches the university record
            if ($emailDomain !== $university->email_domain && !str_ends_with($emailDomain, '.edu')) {
                return response()->json(['error' => 'You must use a valid university email address.'], 403);
            }
        }

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email, // Nullable for landlords depending on DB setup
            'phone_number' => $request->phone_number,
            'role' => $request->role,
            'university_id' => $request->university_id,
            'password' => Hash::make($request->password),
            'last_app_opened_at' => now(), // Initialize activity timer
        ]);

        Auth::login($user);

        return response()->json(['message' => 'Registration successful', 'user' => $user]);
    }
}
