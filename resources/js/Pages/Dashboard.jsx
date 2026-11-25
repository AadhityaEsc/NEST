import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage } from '@inertiajs/react';

const actions = [
    ['Browse rooms', '/listings', 'Find available rooms and housemates'],
    ['Explore map', '/map', 'See listings near your university'],
    ['Publish a room', '/listings/create', 'Advertise a room you can share'],
    ['My listings', '/my-listings', 'Manage rooms you published'],
    ['My wishlist', '/wishlist', 'Revisit rooms you have saved'],
];

export default function Dashboard({ myListingsCount = 0, activeListingsCount = 0, savedListingsCount = 0 }) {
    const { auth } = usePage().props;

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800">Student Dashboard</h2>}>
            <Head title="Dashboard" />
            <main className="mx-auto max-w-7xl px-4 py-10 sm:px-6">
                <h1 className="text-3xl font-bold text-gray-900">Welcome to NEST, {auth.user.name}!</h1>
                <p className="mt-2 text-gray-600">Share a home with other university students — no landlord involved.</p>
                <div className="mt-7 grid gap-4 sm:grid-cols-3">
                    {[
                        ['My rooms', myListingsCount],
                        ['Visible rooms', activeListingsCount],
                        ['Saved rooms', savedListingsCount],
                    ].map(([label, value]) => (
                        <div key={label} className="rounded-xl bg-white p-5 shadow">
                            <p className="text-sm text-gray-500">{label}</p>
                            <p className="mt-2 text-3xl font-bold text-indigo-600">{value}</p>
                        </div>
                    ))}
                </div>
                <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {actions.map(([name, href, description]) => (
                        <Link key={href} href={href} className="rounded-xl bg-white p-6 shadow transition hover:shadow-md">
                            <h2 className="text-xl font-semibold text-gray-900">{name}</h2>
                            <p className="mt-2 text-gray-600">{description}</p>
                            <span className="mt-4 inline-block font-medium text-indigo-600">Open →</span>
                        </Link>
                    ))}
                </div>
                <p className="mt-8 text-sm text-gray-500">Listings automatically hide after 72 hours without a successful publisher login. Logging in restarts the timer.</p>
            </main>
        </AuthenticatedLayout>
    );
}
