import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';

export default function Mine({ listings = [] }) {
    const { auth } = usePage().props;
    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">My room listings</h2>}>
            <Head title="My listings" />
            <main className="mx-auto max-w-5xl px-4 py-8">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <h1 className="text-2xl font-bold">Rooms you published</h1>
                    <Link href="/listings/create" className="rounded-md bg-indigo-600 px-4 py-2 text-white">Publish another</Link>
                </div>
                <p className="mt-2 text-sm text-gray-600">Listings automatically expire after 72 hours without logging in. Automatic expiry is reversed when you log in again; manual withdrawals stay hidden.</p>
                <div className="mt-6 space-y-4">
                    {listings.map((listing) => (
                        <article key={listing.id} className="rounded-xl bg-white p-6 shadow">
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h2 className="text-xl font-semibold">{listing.title}</h2>
                                    <p className="text-sm text-gray-600">£{Number(listing.rent_amount).toFixed(2)} per month</p>
                                    <p className="mt-2 text-gray-700">{listing.description}</p>
                                </div>
                                <span className={`rounded-full px-3 py-1 text-sm ${listing.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-800'}`}>
                                    {listing.is_active ? 'Published' : listing.withdrawn_reason === 'inactivity' ? 'Expired' : 'Withdrawn'}
                                </span>
                            </div>
                            <div className="mt-4">
                                {listing.is_active ? (
                                    <button className="rounded-md bg-rose-700 px-4 py-2 text-white"
                                        onClick={() => { if (window.confirm('Withdraw this listing?')) router.patch(`/my-listings/${listing.id}/withdraw`); }}>
                                        Withdraw
                                    </button>
                                ) : (
                                    <button className="rounded-md bg-indigo-600 px-4 py-2 text-white"
                                        onClick={() => router.patch(`/my-listings/${listing.id}/republish`)}>Republish</button>
                                )}
                            </div>
                        </article>
                    ))}
                    {listings.length === 0 && <p className="rounded-xl bg-white p-6">No rooms published yet.</p>}
                </div>
            </main>
        </AuthenticatedLayout>
    );
}
