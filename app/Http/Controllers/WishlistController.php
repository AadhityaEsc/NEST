<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WishlistController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Wishlist', [
            'items' => $request->user()->wishlists()
                ->with(['listing' => fn($query) => $query->discoverable()->with('user:id,name,email')])
                ->latest()
                ->get()
                ->filter(fn($wishlist) => $wishlist->listing !== null)
                ->values(),
        ]);
    }

    public function store(Request $request, Listing $listing): RedirectResponse
    {
        // Don't save an expired or manually hidden listing.
        abort_unless(Listing::discoverable()->whereKey($listing->id)->exists(), 404);
        abort_if($listing->user_id === $request->user()->id, 422, 'You cannot save your own listing.');

        $request->user()->wishlists()->firstOrCreate(['listing_id' => $listing->id]);

        return back();
    }

    public function destroy(Request $request, Listing $listing): RedirectResponse
    {
        $request->user()->wishlists()->where('listing_id', $listing->id)->delete();

        return back();
    }
}
