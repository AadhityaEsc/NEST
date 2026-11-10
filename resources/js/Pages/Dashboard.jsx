
import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Dashboard({
    myListingsCount = 0,
    activeListingsCount = 0,
    savedListingsCount = 0,
}) {
    const { auth } = usePage().props;
    const user = auth.user;

    const isLandlord = user.role === 'landlord';

    return (

        <AuthenticatedLayout
            user={user}
            header={
                <h2 className="text-xl font-semibold text-gray-800">
                    {isLandlord
                        ? 'Landlord Dashboard'
                        : 'Student Dashboard'}
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="min-h-screen bg-gray-50 py-10">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                    {/* Welcome section */}
                    <div className="mb-8 rounded-xl bg-white p-8 shadow-sm">
                        <h1 className="text-3xl font-bold text-gray-900">
                            Welcome, {user.name}!
                        </h1>

                        <p className="mt-2 text-gray-600">
                            {isLandlord
                                ? 'Manage your properties and accommodation listings.'
                                : 'Find your next room or roommate near your university.'}
                        </p>
                    </div>

                    {/* Landlord Dashboard */}
                    {isLandlord ? (
                        <>
                            <div className="grid gap-6 md:grid-cols-2">

                                <StatCard
                                    title="Total Listings"
                                    value={myListingsCount}
                                />

                                <StatCard
                                    title="Active Listings"
                                    value={activeListingsCount}
                                />

                            </div>

                            <div className="mt-8 grid gap-6 md:grid-cols-2">

                                <FeatureCard
                                    title="Create Listing"
                                    description="Advertise a room or accommodation."
                                    href="/listings/create"
                                    buttonText="Create Listing"
                                />

                                <FeatureCard
                                    title="Manage Listings"
                                    description="View and manage your accommodation listings."
                                    href="/my-listings"
                                    buttonText="Manage Listings"
                                />

                            </div>
                        </>
                    ) : (
                        <>
                            <div className="grid gap-6 md:grid-cols-2">

                                <StatCard
                                    title="Saved Listings"
                                    value={savedListingsCount}
                                />

                                <StatCard
                                    title="Account Type"
                                    value="Student"
                                />

                            </div>

                            <div className="mt-8 grid gap-6 md:grid-cols-2">

                                <FeatureCard
                                    title="Explore Accommodation"
                                    description="Find available rooms and potential roommates."
                                    href="/map"
                                    buttonText="Explore Map"
                                />

                                <FeatureCard
                                    title="My Wishlist"
                                    description="View accommodation listings you have saved."
                                    href="/wishlist"
                                    buttonText="View Wishlist"
                                />

                            </div>
                        </>
                    )}

                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function StatCard({ title, value }) {
    return (
        <div className="rounded-xl bg-white p-6 shadow-sm">
            <p className="text-sm font-medium text-gray-500">
                {title}
            </p>

            <h3 className="mt-3 text-3xl font-bold text-indigo-600">
                {value}
            </h3>
        </div>
    );
}

function FeatureCard({
    title,
    description,
    href,
    buttonText,
}) {
    return (
        <div className="rounded-xl bg-white p-6 shadow-sm">

            <h3 className="text-xl font-semibold text-gray-900">
                {title}
            </h3>

            <p className="mt-3 text-gray-600">
                {description}
            </p>

            <Link
                href={href}
                className="mt-6 inline-block rounded-lg bg-indigo-600 px-5 py-3 font-medium text-white transition hover:bg-indigo-700"
            >
                {buttonText}
            </Link>

        </div>
    );
}
