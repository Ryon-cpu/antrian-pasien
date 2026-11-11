<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        @if (in_array($queue->status, [\App\Models\Queue::STATUS_WAITING, \App\Models\Queue::STATUS_CALLED], true))
            <meta http-equiv="refresh" content="10">
        @endif
        <title>Tiket Antrean | {{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
        <main class="mx-auto max-w-xl px-4 py-10 sm:px-6 sm:py-16">
            @php
                $queueStatus = match ($queue->status) {
                    \App\Models\Queue::STATUS_WAITING => ['label' => 'Menunggu dipanggil', 'class' => 'bg-amber-50 text-amber-800'],
                    \App\Models\Queue::STATUS_CALLED => ['label' => 'Sedang dilayani', 'class' => 'bg-clinic-50 text-clinic-800'],
                    \App\Models\Queue::STATUS_COMPLETED => ['label' => 'Selesai', 'class' => 'bg-emerald-50 text-emerald-800'],
                    \App\Models\Queue::STATUS_CANCELLED => ['label' => 'Dibatalkan', 'class' => 'bg-slate-100 text-slate-700'],
                    default => ['label' => 'Tidak diketahui', 'class' => 'bg-slate-100 text-slate-700'],
                };
            @endphp

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="bg-clinic-700 px-6 py-8 text-center text-white sm:px-10">
                    <p class="text-base font-medium text-clinic-100">Tiket antrean</p>
                    <h1 class="mt-3 text-6xl font-semibold tracking-tight">
                        {{ str_pad((string) $queue->queue_number, 3, '0', STR_PAD_LEFT) }}
                    </h1>
                    <p class="mt-3 text-base text-clinic-100">{{ $queue->queue_date->locale('id')->translatedFormat('l, d F Y') }}</p>
                </div>

                <div aria-live="polite" class="flex flex-col gap-5 p-6 sm:p-9">
                    <div>
                        <p class="text-base text-slate-500">Nama pasien</p>
                        <p class="mt-1 font-semibold text-slate-900">{{ $patient->name }}</p>
                    </div>
                    @if ($queue->doctor)
                        <div>
                            <p class="text-base text-slate-500">Dokter pilihan</p>
                            <p class="mt-1 font-semibold text-slate-900">
                                {{ $queue->doctor->title ? $queue->doctor->title.' ' : '' }}{{ $queue->doctor->full_name }}
                            </p>
                            @if ($queue->doctor->specialization)
                                <p class="mt-1 text-base text-slate-600">{{ $queue->doctor->specialization }}</p>
                            @endif
                        </div>
                    @endif
                    <div>
                        <p class="text-base text-slate-500">Status</p>
                        <p @class(['mt-1 inline-flex rounded-full px-3 py-1 text-base font-medium', $queueStatus['class']])>
                            {{ $queueStatus['label'] }}
                        </p>
                    </div>
                    @if ($queue->status === \App\Models\Queue::STATUS_WAITING)
                        <div class="rounded-2xl bg-clinic-50 p-5 text-center">
                            <p class="text-base font-medium text-clinic-800">Posisi antrean Anda</p>
                            <p class="mt-1 text-4xl font-semibold text-clinic-800">{{ $waitingPosition }}</p>
                            <p class="mt-2 text-base text-clinic-800">
                                @if ($waitingPosition === 1)
                                    Anda berada di urutan berikutnya.
                                @else
                                    Ada {{ $waitingPosition - 1 }} antrean menunggu di depan Anda.
                                @endif
                            </p>
                        </div>
                        <p class="rounded-xl bg-slate-50 p-4 text-base leading-6 text-slate-600">
                            Status dan posisi diperbarui otomatis setiap 10 detik. Simpan halaman ini untuk memantau antrean Anda.
                        </p>
                    @elseif ($queue->status === \App\Models\Queue::STATUS_CALLED)
                        <p class="rounded-xl bg-clinic-50 p-4 text-base leading-6 text-clinic-800">
                            Nomor Anda sedang dipanggil. Silakan menuju petugas.
                        </p>
                    @elseif ($queue->status === \App\Models\Queue::STATUS_COMPLETED)
                        <p class="rounded-xl bg-emerald-50 p-4 text-base leading-6 text-emerald-800">
                            Pemeriksaan Anda telah selesai.
                        </p>
                    @elseif ($queue->status === \App\Models\Queue::STATUS_CANCELLED)
                        <p class="rounded-xl bg-slate-100 p-4 text-base leading-6 text-slate-700">
                            Antrean ini telah dibatalkan. Silakan hubungi petugas jika membutuhkan bantuan.
                        </p>
                    @endif
                    <a
                        href="{{ route('queues.create') }}"
                        class="text-center text-base font-semibold text-clinic-700 hover:text-clinic-800"
                    >
                        Ambil antrean lain
                    </a>
                </div>
            </section>
        </main>
    </body>
</html>
