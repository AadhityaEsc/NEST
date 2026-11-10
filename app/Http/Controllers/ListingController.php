<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListingController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'max_rent' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'type' => ['nullable', 'in:room_available,roommate_wanted'],
        ]);

        $query = Listing::discoverable()
            ->with('user:id,name,email')
            ->latest();

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if (isset($filters['max_rent']) && $filters['max_rent'] !== '') {
            $query->where('rent_amount', '<=', $filters['max_rent']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return Inertia::render('Listings/Index', [
            'listings' => $query->paginate(12)->withQueryString(),
            'filters' => $filters,
            'savedIds' => $request->user()->wishlists()->pluck('listing_id'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Listings/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:room_available,roommate_wanted'],
            'title' => ['required', 'string', 'min:5', 'max:150'],
            'description' => ['required', 'string', 'min:15', 'max:3000'],
            'rent_amount' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $request->user()->listings()->create([
            ...$validated,
            'is_active' => true,
            'withdrawn_reason' => null,
        ]);

        return redirect()->route('listings.mine')->with('success', 'Your room listing is published.');
    }

    public function mine(Request $request): Response
    {
        return Inertia::render('Listings/Mine', [
            'listings' => $request->user()->listings()->latest()->get(),
        ]);
    }

    public function withdraw(Request $request, Listing $listing): RedirectResponse
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        $listing->update([
            'is_active' => false,
            'withdrawn_reason' => 'manual',
        ]);

        return back()->with('success', 'Listing withdrawn.');
    }

    public function republish(Request $request, Listing $listing): RedirectResponse
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        // Publishing again is a new interaction: refresh the login activity clock.
        // The three-day rule is specifically about successful login, however, so
        // a publisher must have signed in within the last 72 hours.
        abort_unless(
            $request->user()->last_app_opened_at &&
                $request->user()->last_app_opened_at->gte(now()->subHours(72)),
            403,
            'Please log out and sign in again before republishing.'
        );

        $listing->update([
            'is_active' => true,
            'withdrawn_reason' => null,
        ]);

        return back()->with('success', 'Listing republished.');
    }
}
