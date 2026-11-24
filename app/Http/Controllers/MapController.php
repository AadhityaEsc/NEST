<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MapController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Map', [
            'placeSearchEnabled' => filled(config('nest.mapbox_token'))
                && config('nest.mapbox_token') !== 'your_mapbox_key',
        ]);
    }

    public function getListingsInBounds(Request $request): JsonResponse
    {
        $bounds = $request->validate([
            'sw_lat' => ['required', 'numeric', 'between:-90,90'],
            'sw_lng' => ['required', 'numeric', 'between:-180,180'],
            'ne_lat' => ['required', 'numeric', 'between:-90,90'],
            'ne_lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        abort_if(
            $bounds['sw_lat'] > $bounds['ne_lat'] ||
                $bounds['sw_lng'] > $bounds['ne_lng'],
            422,
            'Invalid map bounds.'
        );

        return response()->json(
            Listing::discoverable()
                ->whereBetween('latitude', [$bounds['sw_lat'], $bounds['ne_lat']])
                ->whereBetween('longitude', [$bounds['sw_lng'], $bounds['ne_lng']])
                ->orderByDesc('created_at')
                ->limit(200)
                ->get(['id', 'title', 'type', 'rent_amount', 'latitude', 'longitude'])
        );
    }
}
