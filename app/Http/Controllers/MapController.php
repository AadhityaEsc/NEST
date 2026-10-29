<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Listing;
use Illuminate\Support\Facades\Auth;

class MapController extends Controller
{
    /**
     * Fetch active listings within the map's visible bounding box.
     * Triggered via AJAX every time the user pans or zooms the map.
     */
    public function getListingsInBounds(Request $request)
    {
        // Block landlords from viewing the map
        if (Auth::user()->role === 'landlord') {
            return response()->json(['error' => 'Landlords are restricted to managing listings only.'], 403);
        }

        // Validate the GPS coordinates sent from the frontend map (SouthWest and NorthEast corners)
        $request->validate([
            'sw_lat' => 'required|numeric',
            'sw_lng' => 'required|numeric',
            'ne_lat' => 'required|numeric',
            'ne_lng' => 'required|numeric',
        ]);

        // Query database for active listings inside the coordinate box
        $listings = Listing::where('is_active', true)
            ->whereBetween('latitude', [$request->sw_lat, $request->ne_lat])
            ->whereBetween('longitude', [$request->sw_lng, $request->ne_lng])
            ->get();

        return response()->json($listings);
    }
}
