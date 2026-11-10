<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\University;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'universities' => University::orderBy('name')->get(['id', 'name', 'email_domain']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'university_id' => ['required', 'integer', 'exists:universities,id'],
            'phone_number' => ['nullable', 'string', 'max:20', 'unique:users,phone_number'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $university = University::findOrFail($validated['university_id']);
        $emailDomain = Str::lower(Str::afterLast($validated['email'], '@'));

        if ($emailDomain !== Str::lower(ltrim($university->email_domain, '@'))) {
            throw ValidationException::withMessages([
                'email' => 'Use the email address issued by your selected university.',
            ]);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => Str::lower($validated['email']),
            'phone_number' => $validated['phone_number'] ?? null,
            'university_id' => $university->id,
            // Legacy column remains until existing landlord records are reviewed.
            'role' => 'student',
            'password' => Hash::make($validated['password']),
            'last_app_opened_at' => now(),
        ]);

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
