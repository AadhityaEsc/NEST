import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';

export default function Index({ listings, savedIds = [], filters = {} }) {
    const { auth } = usePage().props;
    const [search, setSearch] = useState(filters.search || '');
    const [maxRent, setMaxRent] = useState(filters.max_rent || '');
    const [type, setType] = useState(filters.type || '');
    const saved = new Set(savedIds.map(Number));

    function applyFilters(e) {
        e.preventDefault();
        router.get('/listings', { search, max_rent: maxRent, type }, {
            preserveState: true, replace: true, preserveScroll: true,
        });
    }

    function toggleSaved(id) {
        const url = `/wishlist/${id}`;
        if (saved.has(id)) router.delete(url, { preserveScroll: true });
        else router.post(url, {}, { preserveScroll: true });
    }

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Browse student rooms</h2>}>
            <Head title="Find student housing" />
            <main className="mx-auto max-w-6xl px-4 py-8">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <h1 className="text-2xl font-bold">Available rooms and housemates</h1>
                    <div className="flex gap-2">
                        <Link href="/map" className="rounded-md bg-teal-700 px-4 py-2 text-white">Map view</Link>
                        <Link href="/listings/create" className="rounded-md bg-indigo-600 px-4 py-2 text-white">Publish a room</Link>
                    </div>
                </div>
                <form onSubmit={applyFilters} className="mt-6 grid gap-3 rounded-xl bg-white p-4 shadow sm:grid-cols-4">
                    <input aria-label="Search listings" className="rounded-md border-gray-300" value={search}
                        onChange={(e) => setSearch(e.target.value)} placeholder="Search description or title" />
                    <input aria-label="Maximum monthly rent" type="number" min="0" className="rounded-md border-gray-300"
                        value={maxRent} onChange={(e) => setMaxRent(e.target.value)} placeholder="Max rent (£/month)" />
                    <select aria-label="Listing type" className="rounded-md border-gray-300" value={type} onChange={(e) => setType(e.target.value)}>
                        <option value="">All types</option>
                        <option value="room_available">Room available</option>
                        <option value="roommate_wanted">Roommate wanted</option>
                    </select>
                    <button className="rounded-md bg-indigo-600 px-4 py-2 text-white">Search</button>
                </form>
                <p className="mt-4 text-sm text-gray-600">{listings.total} visible student listing(s)</p>
                <section className="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {listings.data.map((listing) => (
                        <article key={listing.id} className="rounded-xl bg-white p-5 shadow">
                            <h2 className="text-lg font-semibold">{listing.title}</h2>
                            <p className="mt-1 text-sm text-gray-500">{listing.type === 'room_available' ? 'Room available' : 'Roommate wanted'}</p>
                            <p className="mt-3 text-xl font-bold text-indigo-700">£{Number(listing.rent_amount).toFixed(2)} / month</p>
                            <p className="mt-3 line-clamp-4 whitespace-pre-line text-gray-700">{listing.description}</p>
                            <p className="mt-3 text-sm text-gray-500">Posted by {listing.user?.name || 'Student'}</p>
                            <div className="mt-4 flex flex-wrap gap-2">
                                {listing.user_id !== auth.user.id && (
                                    <button type="button" onClick={() => toggleSaved(listing.id)}
                                        className="rounded-md border border-indigo-600 px-3 py-2 text-indigo-700">
                                        {saved.has(listing.id) ? '♥ Saved — remove' : '♡ Save room'}
                                    </button>
                                )}
                                {listing.user?.email && listing.user_id !== auth.user.id && (
                                    <a href={`mailto:${listing.user.email}?subject=${encodeURIComponent('NEST room enquiry')}`}
                                        className="rounded-md bg-slate-800 px-3 py-2 text-white">Contact student</a>
                                )}
                            </div>
                        </article>
                    ))}
                </section>
                {listings.data.length === 0 && <p className="mt-8 rounded-xl bg-white p-6 text-gray-600">No available rooms match your search. Try another filter.</p>}
                <nav aria-label="Listing pages" className="mt-8 flex flex-wrap gap-2">
                    {listings.links.map((page, i) => page.url ? (
                        <Link key={i} href={page.url} preserveScroll
                            className={`rounded-md px-3 py-2 ${page.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-800'}`}>
                            <span dangerouslySetInnerHTML={{ __html: page.label }} />
                        </Link>
                    ) : (
                        <span key={i} className="px-3 py-2 text-gray-400"><span dangerouslySetInnerHTML={{ __html: page.label }} /></span>
                    ))}
                </nav>
            </main>
        </AuthenticatedLayout>
    );
}