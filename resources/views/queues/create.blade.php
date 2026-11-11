<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ambil Antrean | {{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
        <main class="mx-auto max-w-2xl px-4 py-10 sm:px-6 sm:py-16">
            <a href="{{ route('login') }}" class="text-base font-medium text-clinic-700 hover:text-clinic-800">
                &larr; Kembali ke login petugas
            </a>

            <section class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-9">
                <p class="text-base font-medium text-clinic-700">{{ config('app.name') }}</p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">Ambil nomor antrean</h1>
                <p class="mt-2 text-base leading-6 text-slate-500">
                    Isi data pasien dengan benar. Nomor antrean berlaku untuk hari ini. Memilih dokter bersifat opsional.
                </p>

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

                <form method="POST" action="{{ route('queues.store') }}" class="mt-7 flex flex-col gap-5">
                    @csrf

                    <div>
                        <label for="name" class="mb-2 block text-base font-medium text-slate-700">Nama pasien</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            maxlength="100"
                            autocomplete="name"
                            required
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-base outline-none transition focus:border-clinic-500 focus:ring-2 focus:ring-clinic-500/20"
                        >
                        @error('name')
                            <p class="mt-1 text-base text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="age" class="mb-2 block text-base font-medium text-slate-700">Usia pasien</label>
                        <input
                            id="age"
                            name="age"
                            type="number"
                            value="{{ old('age') }}"
                            min="0"
                            max="255"
                            required
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-base outline-none transition focus:border-clinic-500 focus:ring-2 focus:ring-clinic-500/20"
                        >
                        @error('age')
                            <p class="mt-1 text-base text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="address" class="mb-2 block text-base font-medium text-slate-700">Alamat</label>
                        <input
                            id="address"
                            name="address"
                            type="text"
                            value="{{ old('address') }}"
                            maxlength="255"
                            autocomplete="street-address"
                            required
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-base outline-none transition focus:border-clinic-500 focus:ring-2 focus:ring-clinic-500/20"
                        >
                        @error('address')
                            <p class="mt-1 text-base text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="doctor_id" class="mb-2 block text-base font-medium text-slate-700">Dokter pilihan <span class="font-normal text-slate-500">(opsional)</span></label>
                        <select
                            id="doctor_id"
                            name="doctor_id"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-base outline-none transition focus:border-clinic-500 focus:ring-2 focus:ring-clinic-500/20"
                        >
                            <option value="">Tidak memilih dokter</option>
                            @foreach ($doctors as $doctor)
                                <option value="{{ $doctor->id }}" @selected(old('doctor_id') == $doctor->id)>
                                    {{ $doctor->title ? $doctor->title.' ' : '' }}{{ $doctor->full_name }}{{ $doctor->specialization ? ' — '.$doctor->specialization : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('doctor_id')
                            <p class="mt-1 text-base text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="symptom" class="mb-2 block text-base font-medium text-slate-700">Keluhan</label>
                        <textarea
                            id="symptom"
                            name="symptom"
                            rows="4"
                            maxlength="5000"
                            required
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 font-serif text-base outline-none transition focus:border-clinic-500 focus:ring-2 focus:ring-clinic-500/20"
                        >{{ old('symptom') }}</textarea>
                        @error('symptom')
                            <p class="mt-1 text-base text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-clinic-600 px-4 py-3.5 text-base font-semibold text-white transition hover:bg-clinic-700 focus:outline-none focus:ring-2 focus:ring-clinic-500 focus:ring-offset-2"
                    >
                        Ambil nomor antrean
                    </button>
                </form>
            </section>
        </main>
    </body>
</html>
