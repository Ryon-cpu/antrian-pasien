<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Petugas Antrian</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
        <main class="mx-auto flex min-h-screen w-full max-w-md flex-col justify-center px-4 py-10 sm:px-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-9">
                <p class="text-base font-medium text-clinic-700">{{ config('app.name') }}</p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">Login Petugas</h1>
                <p class="mt-2 text-base leading-6 text-slate-500">Masuk untuk mengelola antrean pasien.</p>

                @if ($errors->any())
                    <div role="alert" class="mt-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800">
                        <ul class="list-inside list-disc text-base">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}" class="mt-7 flex flex-col gap-5">
                    @csrf

                    <div>
                        <label for="username" class="mb-2 block text-base font-medium text-slate-700">Username</label>
                        <input
                            id="username"
                            name="username"
                            type="text"
                            value="{{ old('username') }}"
                            autocomplete="username"
                            required
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-base outline-none transition focus:border-clinic-500 focus:ring-2 focus:ring-clinic-500/20"
                        >
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-base font-medium text-slate-700">Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-base outline-none transition focus:border-clinic-500 focus:ring-2 focus:ring-clinic-500/20"
                        >
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-clinic-600 px-4 py-3 text-base font-semibold text-white transition hover:bg-clinic-700 focus:outline-none focus:ring-2 focus:ring-clinic-500 focus:ring-offset-2"
                    >
                        Masuk
                    </button>
                </form>

                <p class="mt-6 text-center text-base leading-6 text-slate-500">
                    Pasien belum memiliki nomor antrean?
                    <a href="{{ route('queues.create') }}" class="font-semibold text-clinic-700 transition hover:text-clinic-800">Ambil antrean di sini</a>
                </p>
            </div>
        </main>
    </body>
</html>
