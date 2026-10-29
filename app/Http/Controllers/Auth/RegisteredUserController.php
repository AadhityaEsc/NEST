<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\University;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        // Fetch all universities to populate the dropdown on the React frontend
        $universities = University::orderBy('name')->get();

        return Inertia::render('Auth/Register', [
            'universities' => $universities
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:student,landlord',
            'email' => 'required_if:role,student|nullable|string|lowercase|email|max:255|unique:' . User::class,
            'phone_number' => 'required_if:role,landlord|nullable|string|max:20|unique:' . User::class,
            'university_id' => 'required_if:role,student|nullable|exists:universities,id',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Strict Domain Validation for Students
        if ($request->role === 'student') {
            $university = University::find($request->university_id);
            $emailDomain = substr(strrchr($request->email, "@"), 1);

            if ($emailDomain !== $university->email_domain && !str_ends_with($emailDomain, '.edu')) {
                throw ValidationException::withMessages([
                    'email' => "You must use a valid university email address ending in @{$university->email_domain}.",
                ]);
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'role' => $request->role,
            'university_id' => $request->university_id,
            'password' => Hash::make($request->password),
            'last_app_opened_at' => now(),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
