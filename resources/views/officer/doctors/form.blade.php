<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $doctor->exists ? 'Ubah Dokter' : 'Tambah Dokter' }} | {{ config('app.name') }}</title>

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
                                <button type="submit" class="rounded-xl border border-slate-200 px-3 py-2 text-base font-medium text-slate-600 transition hover:bg-rose-50 hover:text-rose-700">
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                <main class="mx-auto max-w-4xl px-4 py-7 sm:px-7 sm:py-9 lg:px-10">
                    <a href="{{ route('doctors.index') }}" class="text-base font-medium text-clinic-700 transition hover:text-clinic-800">
                        &larr; Kembali ke data dokter
                    </a>

                    <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/50 sm:p-8">
                        <div>
                            <p class="text-base font-medium text-clinic-700">Profil layanan</p>
                            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-950">
                                {{ $doctor->exists ? 'Ubah data dokter' : 'Tambah dokter' }}
                            </h1>
                            <p class="mt-2 text-base text-slate-500">Isi informasi dokter yang akan ditampilkan pada data klinik.</p>
                        </div>

                        @if ($errors->any())
                            <div role="alert" class="mt-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800">
                                <p class="font-semibold">Periksa kembali data yang diisi.</p>
                                <ul class="mt-2 list-inside list-disc text-base">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form
                            method="POST"
                            action="{{ $doctor->exists ? route('doctors.update', $doctor) : route('doctors.store') }}"
                            class="mt-7 flex flex-col gap-5"
                        >
                            @csrf
                            @if ($doctor->exists)
                                @method('PUT')
                            @endif

                            <div>
                                <label for="full_name" class="mb-2 block text-base font-medium text-slate-700">Nama lengkap <span class="text-rose-600">*</span></label>
                                <input
                                    id="full_name"
                                    name="full_name"
                                    type="text"
                                    value="{{ old('full_name', $doctor->full_name) }}"
                                    maxlength="255"
                                    autocomplete="name"
                                    required
                                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-base outline-none transition focus:border-clinic-500 focus:ring-2 focus:ring-clinic-500/20"
                                >
                                @error('full_name')
                                    <p class="mt-1 text-base text-rose-700">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="title" class="mb-2 block text-base font-medium text-slate-700">Gelar</label>
                                    <input
                                        id="title"
                                        name="title"
                                        type="text"
                                        value="{{ old('title', $doctor->title) }}"
                                        maxlength="50"
                                        placeholder="Contoh: dr."
                                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-base outline-none transition focus:border-clinic-500 focus:ring-2 focus:ring-clinic-500/20"
                                    >
                                    @error('title')
                                        <p class="mt-1 text-base text-rose-700">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="specialization" class="mb-2 block text-base font-medium text-slate-700">Spesialisasi</label>
                                    <input
                                        id="specialization"
                                        name="specialization"
                                        type="text"
                                        value="{{ old('specialization', $doctor->specialization) }}"
                                        maxlength="100"
                                        placeholder="Contoh: Dokter Umum"
                                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-base outline-none transition focus:border-clinic-500 focus:ring-2 focus:ring-clinic-500/20"
                                    >
                                    @error('specialization')
                                        <p class="mt-1 text-base text-rose-700">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="education" class="mb-2 block text-base font-medium text-slate-700">Pendidikan</label>
                                <textarea
                                    id="education"
                                    name="education"
                                    rows="3"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-3 font-serif text-base outline-none transition focus:border-clinic-500 focus:ring-2 focus:ring-clinic-500/20"
                                >{{ old('education', $doctor->education) }}</textarea>
                                @error('education')
                                    <p class="mt-1 text-base text-rose-700">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="experience" class="mb-2 block text-base font-medium text-slate-700">Pengalaman</label>
                                <textarea
                                    id="experience"
                                    name="experience"
                                    rows="3"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-3 font-serif text-base outline-none transition focus:border-clinic-500 focus:ring-2 focus:ring-clinic-500/20"
                                >{{ old('experience', $doctor->experience) }}</textarea>
                                @error('experience')
                                    <p class="mt-1 text-base text-rose-700">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="schedule" class="mb-2 block text-base font-medium text-slate-700">Jadwal praktik</label>
                                <textarea
                                    id="schedule"
                                    name="schedule"
                                    rows="3"
                                    placeholder="Contoh: Senin–Jumat, 08.00–14.00"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-3 font-serif text-base outline-none transition focus:border-clinic-500 focus:ring-2 focus:ring-clinic-500/20"
                                >{{ old('schedule', $doctor->schedule) }}</textarea>
                                @error('schedule')
                                    <p class="mt-1 text-base text-rose-700">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                                <a
                                    href="{{ route('doctors.index') }}"
                                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-center text-base font-medium text-slate-700 transition hover:bg-slate-50"
                                >
                                    Batal
                                </a>
                                <button
                                    type="submit"
                                    class="rounded-xl bg-clinic-700 px-4 py-2.5 text-base font-semibold text-white transition hover:bg-clinic-800 focus:outline-none focus:ring-2 focus:ring-clinic-500 focus:ring-offset-2"
                                >
                                    {{ $doctor->exists ? 'Simpan perubahan' : 'Simpan dokter' }}
                                </button>
                            </div>
                        </form>
                    </section>
                </main>
            </div>
        </div>
    </body>
</html>
