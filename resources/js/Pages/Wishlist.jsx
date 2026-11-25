import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';

export default function Wishlist({ items = [] }) {
    const { auth } = usePage().props;
    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Saved rooms</h2>}>
            <Head title="Wishlist" />
            <main className="mx-auto max-w-5xl px-4 py-8">
                <h1 className="text-2xl font-bold">My wishlist</h1>
                <p className="mt-2 text-gray-600">Saved rooms can expire or be withdrawn by their publishers.</p>
                <div className="mt-6 grid gap-4 md:grid-cols-2">
                    {items.map(({ id, listing }) => (
                        <article key={id} className="rounded-xl bg-white p-5 shadow">
                            <h2 className="text-xl font-semibold">{listing.title}</h2>
                            <p className="mt-1 text-indigo-700">£{Number(listing.rent_amount).toFixed(2)} / month</p>
                            <p className="mt-2 text-gray-600">{listing.description}</p>
                            <p className="mt-2 text-sm text-gray-500">Posted by {listing.user?.name || 'Student'}</p>
                            <div className="mt-4 flex gap-3">
                                {listing.user?.email && <a className="rounded-md bg-indigo-600 px-3 py-2 text-white" href={`mailto:${listing.user.email}`}>Contact</a>}
                                <button className="rounded-md border px-3 py-2" onClick={() => router.delete(`/wishlist/${listing.id}`, { preserveScroll: true })}>Remove saved room</button>
                            </div>
                            {(!listing.is_active || listing.withdrawn_reason) && <p className="mt-2 text-sm text-rose-600">This room is no longer publicly available.</p>}
                        </article>
                    ))}
                    {items.length === 0 && <p className="rounded-xl bg-white p-6">You have no saved rooms. <Link href="/listings" className="text-indigo-700 underline">Browse rooms</Link>.</p>}
                </div>
            </main>
        </AuthenticatedLayout>
    );
}
