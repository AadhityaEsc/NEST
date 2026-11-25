import { useEffect, useRef, useState } from 'react';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Map({ placeSearchEnabled = false }) {
    const { auth } = usePage().props;
    const container = useRef(null);
    const mapRef = useRef(null);
    const markersRef = useRef(null);
    const [rooms, setRooms] = useState([]);
    const [error, setError] = useState('');
    const [place, setPlace] = useState('');
    const [placeError, setPlaceError] = useState('');
    const [searching, setSearching] = useState(false);

    useEffect(() => {
        if (!container.current || mapRef.current) return;

        const map = L.map(container.current).setView([52.5878, -2.1270], 13);
        mapRef.current = map;
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>',
        }).addTo(map);

        const markers = L.layerGroup().addTo(map);
        markersRef.current = markers;
        let cancelled = false;
        let controller;

        async function loadRooms() {
            controller?.abort();
            controller = new AbortController();
            const b = map.getBounds();
            const query = new URLSearchParams({
                sw_lat: b.getSouthWest().lat.toFixed(7),
                sw_lng: b.getSouthWest().lng.toFixed(7),
                ne_lat: b.getNorthEast().lat.toFixed(7),
                ne_lng: b.getNorthEast().lng.toFixed(7),
            });
            try {
                const response = await fetch(`/map/listings?${query}`, {
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json' },
                    signal: controller.signal,
                });
                if (!response.ok) throw new Error(`Could not load map listings (${response.status}).`);
                const data = await response.json();
                if (cancelled) return;
                markers.clearLayers();
                for (const room of data) {
                    const marker = L.circleMarker([Number(room.latitude), Number(room.longitude)], {
                        radius: 9, color: '#0f766e', fillColor: '#14b8a6', fillOpacity: 0.85,
                    });
                    // Use DOM textContent, not HTML strings, for student-entered titles.
                    const popup = document.createElement('div');
                    const title = document.createElement('strong');
                    title.textContent = room.title;
                    const amount = document.createElement('p');
                    amount.textContent = `£${Number(room.rent_amount).toFixed(2)} / month`;
                    const link = document.createElement('a');
                    link.href = '/listings';
                    link.textContent = 'View listings';
                    popup.append(title, amount, link);
                    marker.bindPopup(popup).addTo(markers);
                }
                setRooms(data);
                setError('');
            } catch (err) {
                if (err.name !== 'AbortError' && !cancelled) setError(err.message);
            }
        }

        map.on('moveend', loadRooms);
        map.whenReady(loadRooms);
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    if (!cancelled) map.setView([position.coords.latitude, position.coords.longitude], 13);
                },
                () => { },
                { enableHighAccuracy: false, timeout: 8000 },
            );
        }

        return () => {
            cancelled = true;
            controller?.abort();
            map.remove();
            mapRef.current = null;
            markersRef.current = null;
        };
    }, []);

    async function findPlace(event) {
        event.preventDefault();
        if (!place.trim()) return;
        setSearching(true);
        setPlaceError('');
        try {
            const response = await fetch(`/map/place-search?q=${encodeURIComponent(place.trim())}`, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Place search failed.');
            mapRef.current?.setView([result.latitude, result.longitude], 14);
        } catch (err) {
            setPlaceError(err.message);
        } finally {
            setSearching(false);
        }
    }

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Nearby student rooms</h2>}>
            <Head title="Map" />
            <main className="mx-auto max-w-6xl px-4 py-8">
                <h1 className="text-2xl font-bold">Explore available rooms</h1>
                <p className="mt-2 text-gray-600">Move or zoom the map to load nearby rooms automatically. Allow location access to centre it near you.</p>
                {placeSearchEnabled && (
                    <form onSubmit={findPlace} className="mt-4 flex flex-wrap gap-2">
                        <input aria-label="Find a town or postcode" placeholder="Search a town or postcode"
                            value={place} onChange={(e) => setPlace(e.target.value)}
                            className="min-w-48 flex-1 rounded-md border-gray-300" />
                        <button disabled={searching} className="rounded-md bg-teal-700 px-4 py-2 text-white disabled:opacity-50">
                            {searching ? 'Searching...' : 'Find location'}
                        </button>
                        {placeError && <p className="w-full text-sm text-red-700">{placeError}</p>}
                        <p className="w-full text-xs text-gray-500">Place search powered by Mapbox Geocoding API.</p>
                    </form>
                )}
                <div ref={container} className="mt-5 h-[65vh] min-h-96 w-full overflow-hidden rounded-xl border" aria-label="Student rooms on interactive map" />
                {error && <p className="mt-3 text-red-700">{error}</p>}
                <div className="mt-5 flex items-center justify-between gap-3">
                    <p className="text-gray-700">{rooms.length} available room(s) in the visible map area</p>
                    <Link href="/listings" className="rounded-md bg-indigo-600 px-4 py-2 text-white">Browse all listings</Link>
                </div>
            </main>
        </AuthenticatedLayout>
    );
}
