<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MapboxSearchController extends Controller
{
    /**
     * On-demand REST call to the Mapbox Geocoding v6 API.
     * No autocomplete and no caching of temporary geocoding results.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:3', 'max:100', 'not_regex:/;/'],
        ]);

        $token = config('nest.mapbox_token');
        if (!$token || $token === 'your_mapbox_key') {
            return response()->json(['message' => 'Mapbox place search is not configured.'], 503);
        }

        try {
            $response = Http::acceptJson()->timeout(8)->get(
                'https://api.mapbox.com/search/geocode/v6/forward',
                [
                    'q' => $validated['q'],
                    'access_token' => $token,
                    'country' => 'GB',
                    'autocomplete' => 'false',
                    'limit' => 1,
                    'permanent' => 'false',
                ]
            );

            if (!$response->successful()) {
                return response()->json(['message' => 'Place search is temporarily unavailable.'], 502);
            }

            $coordinates = data_get($response->json(), 'features.0.geometry.coordinates');
            if (!is_array($coordinates) || count($coordinates) < 2) {
                return response()->json(['message' => 'No location found.'], 404);
            }

            return response()->json([
                'latitude' => (float) $coordinates[1],
                'longitude' => (float) $coordinates[0],
                'name' => data_get($response->json(), 'features.0.properties.full_address')
                    ?: data_get($response->json(), 'features.0.properties.name')
                    ?: $validated['q'],
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'Place search could not be completed.'], 502);
        }
    }
}
