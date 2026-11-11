<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Data Dokter | {{ config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
        <div class="min-h-screen lg:flex">
            @include('sidebar.officer')

            <div class="min-w-0 flex-1">
                <header class="border-b border-slate-200 bg-white">
                    <div class="flex min-h-20 items-center justify-between gap-4 px-4 sm:px-7 lg:px-10">
                        <div class="min-w-0">
                            <p class="truncate text-base font-semibold text-slate-900">{{ config('app.name') }}</p>
                            <p class="hidden text-base text-slate-500 sm:block">Data dokter</p>
                        </div>

                        <div class="flex shrink-0 items-center gap-3 sm:gap-5">
                            <div class="hidden text-right sm:block">
                                <p class="text-base font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                                <p class="text-base text-slate-500">Petugas antrean</p>
                            </div>
                            <span class="flex size-10 items-center justify-center rounded-full bg-clinic-100 text-base font-semibold text-clinic-800" aria-hidden="true">
                                {{ str(auth()->user()->name)->substr(0, 1)->upper() }}
                            </span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="rounded-xl border border-slate-200 px-3 py-2 text-base font-medium text-slate-600 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700 focus:outline-none focus:ring-2 focus:ring-clinic-500 focus:ring-offset-2"
                                >
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                <main class="mx-auto max-w-7xl px-4 py-7 sm:px-7 sm:py-9 lg:px-10">
                    <section class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-base font-medium text-clinic-700">Pengaturan layanan</p>
                            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">Data dokter</h1>
                            <p class="mt-2 text-base text-slate-500">Kelola profil, spesialisasi, dan jadwal praktik dokter.</p>
                        </div>
                        <a
                            href="{{ route('doctors.create') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-clinic-700 px-4 py-2.5 text-base font-semibold text-white transition hover:bg-clinic-800 focus:outline-none focus:ring-2 focus:ring-clinic-500 focus:ring-offset-2"
                        >
                            <svg viewBox="0 0 24 24" fill="none" class="size-5" aria-hidden="true">
                                <path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                            Tambah dokter
                        </a>
                    </section>

                    @if (session('status'))
                        <p role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-base font-medium text-emerald-800">
                            {{ session('status') }}
                        </p>
                    @endif

                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-200/50">
                        <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-5 sm:px-6">
                            <div>
                                <h2 class="font-semibold text-slate-900">Daftar dokter</h2>
                                <p class="mt-1 text-base text-slate-500">Nama dan informasi layanan yang tersedia.</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-slate-100 px-3 py-1 text-base font-medium text-slate-600">
                                {{ $doctors->total() }} dokter
                            </span>
                        </div>

                        @if ($doctors->isEmpty())
                            <div class="px-6 py-14 text-center">
                                <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                    <svg viewBox="0 0 24 24" fill="none" class="size-6" aria-hidden="true">
                                        <path d="M16 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 18.5V20m6-9a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm6-7.5a4 4 0 0 1 0 7.75M20 20v-1.5a3.5 3.5 0 0 0-2.5-3.35" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <h3 class="mt-4 text-base font-semibold text-slate-900">Belum ada data dokter</h3>
                                <p class="mt-1 text-base text-slate-500">Tambahkan dokter untuk melengkapi data layanan klinik.</p>
                                <a href="{{ route('doctors.create') }}" class="mt-5 inline-flex rounded-xl bg-clinic-700 px-4 py-2.5 text-base font-semibold text-white transition hover:bg-clinic-800">
                                    Tambah dokter pertama
                                </a>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[42rem] text-left text-base">
                                    <thead class="bg-slate-50 font-medium text-slate-500">
                                        <tr>
                                            <th scope="col" class="px-5 py-3.5 sm:px-6">Nama dokter</th>
                                            <th scope="col" class="px-4 py-3.5">Spesialisasi</th>
                                            <th scope="col" class="px-4 py-3.5">Jadwal praktik</th>
                                            <th scope="col" class="px-4 py-3.5">Total antrean</th>
                                            <th scope="col" class="px-5 py-3.5 sm:px-6">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($doctors as $doctor)
                                            <tr class="transition hover:bg-slate-50/70">
                                                <td class="px-5 py-4 sm:px-6">
                                                    <p class="font-medium text-slate-800">{{ $doctor->title ? $doctor->title.' ' : '' }}{{ $doctor->full_name }}</p>
                                                </td>
                                                <td class="px-4 py-4 text-slate-600">{{ $doctor->specialization ?: '—' }}</td>
                                                <td class="max-w-64 px-4 py-4 text-slate-600">
                                                    <p class="line-clamp-2 whitespace-pre-line">{{ $doctor->schedule ?: 'Jadwal belum diisi' }}</p>
                                                </td>
                                                <td class="whitespace-nowrap px-4 py-4 text-slate-600">{{ number_format($doctor->queues_count) }}</td>
                                                <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                                                    <a
                                                        href="{{ route('doctors.edit', $doctor) }}"
                                                        class="rounded-lg border border-slate-200 px-3 py-1.5 font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-clinic-500 focus:ring-offset-2"
                                                    >
                                                        Ubah
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if ($doctors->hasPages())
                                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">
                                    {{ $doctors->links() }}
                                </div>
                            @endif
                        @endif
                    </section>
                </main>
            </div>
        </div>
    </body>
</html>
