
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Register({ universities = [] }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        university_id: '',
        phone_number: '',
        password: '',
        password_confirmation: '',
    });

    function submit(e) {
        e.preventDefault();

        post(route('register'), {
            onFinish: () =>
                reset('password', 'password_confirmation'),
        });
    }

    return (
        <GuestLayout>
            <Head title="Student Registration" />

            <h1 className="mb-2 text-2xl font-bold text-gray-100">
                Join NEST
            </h1>

            <p className="mb-6 text-sm text-gray-400">
                Student-to-student house sharing. No landlords.
            </p>

            <form onSubmit={submit} className="space-y-4">
                <div>
                    <InputLabel
                        htmlFor="name"
                        value="Full Name"
                    />
                    <TextInput
                        id="name"
                        className="mt-1 block w-full"
                        value={data.name}
                        onChange={(e) =>
                            setData('name', e.target.value)
                        }
                        required
                        autoComplete="name"
                    />
                    <InputError
                        message={errors.name}
                        className="mt-2"
                    />
                </div>

                <div>
                    <InputLabel
                        htmlFor="university_id"
                        value="University"
                    />

                    <select
                        id="university_id"
                        value={data.university_id}
                        onChange={(e) =>
                            setData('university_id', e.target.value)
                        }
                        required
                        className="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900"
                    >
                        <option value="">
                            Choose your university
                        </option>

                        {universities.map((u) => (
                            <option key={u.id} value={u.id}>
                                {u.name}
                            </option>
                        ))}
                    </select>

                    <InputError
                        message={errors.university_id}
                        className="mt-2"
                    />
                </div>

                <div>
                    <InputLabel
                        htmlFor="email"
                        value="University Email"
                    />

                    <TextInput
                        id="email"
                        type="email"
                        className="mt-1 block w-full"
                        value={data.email}
                        onChange={(e) =>
                            setData('email', e.target.value)
                        }
                        required
                        autoComplete="email"
                    />

                    <InputError
                        message={errors.email}
                        className="mt-2"
                    />
                </div>

                <div>
                    <InputLabel
                        htmlFor="phone_number"
                        value="Phone Number (optional)"
                    />

                    <TextInput
                        id="phone_number"
                        type="tel"
                        className="mt-1 block w-full"
                        value={data.phone_number}
                        onChange={(e) =>
                            setData('phone_number', e.target.value)
                        }
                    />

                    <InputError
                        message={errors.phone_number}
                        className="mt-2"
                    />
                </div>

                <div>
                    <InputLabel
                        htmlFor="password"
                        value="Password"
                    />

                    <TextInput
                        id="password"
                        type="password"
                        className="mt-1 block w-full"
                        value={data.password}
                        onChange={(e) =>
                            setData('password', e.target.value)
                        }
                        required
                        autoComplete="new-password"
                    />

                    <InputError
                        message={errors.password}
                        className="mt-2"
                    />
                </div>

                <div>
                    <InputLabel
                        htmlFor="password_confirmation"
                        value="Confirm Password"
                    />

                    <TextInput
                        id="password_confirmation"
                        type="password"
                        className="mt-1 block w-full"
                        value={data.password_confirmation}
                        onChange={(e) =>
                            setData(
                                'password_confirmation',
                                e.target.value
                            )
                        }
                        required
                    />

                    <InputError
                        message={errors.password_confirmation}
                        className="mt-2"
                    />
                </div>

                <div className="flex items-center justify-end gap-4 pt-2">
                    <Link
                        href={route('login')}
                        className="text-sm text-indigo-400 underline"
                    >
                        Already registered?
                    </Link>

                    <PrimaryButton disabled={processing}>
                        Register
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
