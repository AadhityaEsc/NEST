import { useEffect, useRef } from 'react';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

function LocationPicker({ onPick }) {
    const elementRef = useRef(null);
    const mapRef = useRef(null);
    const onPickRef = useRef(onPick);
    onPickRef.current = onPick;

    useEffect(() => {
        if (!elementRef.current || mapRef.current) return;
        const map = L.map(elementRef.current).setView([52.5878, -2.1270], 12);
        mapRef.current = map;
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>',
        }).addTo(map);
        let marker;
        map.on('click', (e) => {
            const { lat, lng } = e.latlng;
            if (!marker) marker = L.circleMarker([lat, lng], { radius: 9, color: '#4f46e5' }).addTo(map);
            else marker.setLatLng([lat, lng]);
            onPickRef.current(lat.toFixed(7), lng.toFixed(7));
        });
        return () => { map.remove(); mapRef.current = null; };
    }, []);
    return <div ref={elementRef} className="h-64 w-full rounded-lg" aria-label="Select room location on the map" />;
}

export default function Create() {
    const { auth } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({
        type: 'room_available', title: '', description: '', rent_amount: '', latitude: '', longitude: '',
    });

    function submit(e) {
        e.preventDefault();
        post('/listings');
    }

    const input = 'mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Publish a room</h2>}>
            <Head title="Publish room" />
            <main className="mx-auto max-w-3xl px-4 py-8">
                <div className="rounded-xl bg-white p-6 shadow">
                    <h1 className="text-2xl font-bold">Add a student house-sharing listing</h1>
                    <p className="mt-1 text-sm text-gray-600">Your room is visible for 72 hours after posting or your latest login.</p>
                    <form onSubmit={submit} className="mt-6 space-y-5">
                        <div>
                            <label htmlFor="type" className="font-medium">Listing type</label>
                            <select id="type" className={input} value={data.type} onChange={(e) => setData('type', e.target.value)}>
                                <option value="room_available">Room available</option>
                                <option value="roommate_wanted">Roommate wanted</option>
                            </select>
                            {errors.type && <p className="text-red-600">{errors.type}</p>}
                        </div>
                        <div>
                            <label htmlFor="title" className="font-medium">Title</label>
                            <input id="title" required minLength={5} maxLength={150} className={input} value={data.title} onChange={(e) => setData('title', e.target.value)} placeholder="Spare room near campus" />
                            {errors.title && <p className="text-red-600">{errors.title}</p>}
                        </div>
                        <div>
                            <label htmlFor="description" className="font-medium">Description</label>
                            <textarea id="description" required minLength={15} maxLength={3000} rows={5} className={input} value={data.description} onChange={(e) => setData('description', e.target.value)} placeholder="Tell students about the room, house and living arrangements." />
                            {errors.description && <p className="text-red-600">{errors.description}</p>}
                        </div>
                        <div>
                            <label htmlFor="rent_amount" className="font-medium">Monthly rent (£)</label>
                            <input id="rent_amount" type="number" min="0" max="999999.99" step="0.01" required className={input} value={data.rent_amount} onChange={(e) => setData('rent_amount', e.target.value)} />
                            {errors.rent_amount && <p className="text-red-600">{errors.rent_amount}</p>}
                        </div>
                        <div>
                            <p className="font-medium">Location — click the map to select your room</p>
                            <div className="mt-2"><LocationPicker onPick={(lat, lng) => {
                                setData((old) => ({ ...old, latitude: lat, longitude: lng }));
                            }} /></div>
                            <p className="mt-2 text-sm text-gray-600">Coordinates: {data.latitude || 'not selected'}, {data.longitude || 'not selected'}</p>
                            {(errors.latitude || errors.longitude) && <p className="text-red-600">{errors.latitude || errors.longitude}</p>}
                            <p className="mt-1 text-xs text-gray-500">For privacy, choose an approximate location rather than an exact front door.</p>
                        </div>
                        <div className="flex gap-3">
                            <button type="submit" disabled={processing || !data.latitude} className="rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white disabled:opacity-50">Publish room</button>
                            <Link href="/dashboard" className="rounded-lg border px-5 py-3">Cancel</Link>
                        </div>
                    </form>
                </div>
            </main>
        </AuthenticatedLayout>
    );
}
