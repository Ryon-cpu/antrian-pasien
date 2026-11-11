<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="refresh" content="10">
        <title>Layar Antrean | {{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 font-sans text-white antialiased">
        <main class="mx-auto flex min-h-screen w-full max-w-7xl flex-col px-5 py-7 sm:px-8 sm:py-10">
            <header class="flex flex-col gap-2 border-b border-white/10 pb-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-base font-medium tracking-wide text-clinic-300">{{ config('app.name') }}</p>
                    <h1 class="mt-1 text-2xl font-semibold sm:text-3xl">Informasi antrean</h1>
                </div>
                <div class="text-base text-slate-400 sm:text-right">
                    <p>{{ $today->locale('id')->translatedFormat('l, d F Y') }}</p>
                    <p class="mt-1">Diperbarui otomatis setiap 10 detik</p>
                </div>
            </header>

            <section aria-live="polite" aria-label="Antrean yang sedang dipanggil" class="grid flex-1 content-center gap-8 py-8 lg:grid-cols-[1.2fr_0.8fr]">
                <article class="flex min-h-72 flex-col items-center justify-center rounded-3xl border border-white/10 bg-white/5 px-6 py-10 text-center sm:px-10">
                    <p class="text-lg font-medium text-slate-300 sm:text-xl">NOMOR SEDANG DIPANGGIL</p>
                    @if ($currentQueue)
                        <p class="mt-5 text-8xl leading-none font-semibold tracking-tight text-white sm:text-9xl">
                            {{ str_pad((string) $currentQueue->queue_number, 3, '0', STR_PAD_LEFT) }}
                        </p>
                        <p class="mt-6 text-base text-slate-400">
                            Dipanggil pukul {{ $currentQueue->called_at?->format('H:i') ?? '—' }}
                        </p>
                    @else
                        <p class="mt-8 text-2xl font-semibold text-slate-300 sm:text-3xl">Belum ada antrean dipanggil</p>
                        <p class="mt-3 text-base text-slate-400">Silakan menunggu, nomor Anda akan tampil di sini.</p>
                    @endif
                </article>

                <article class="rounded-3xl border border-white/10 bg-white/5 p-6 sm:p-8">
                    <div class="flex items-end justify-between gap-4 border-b border-white/10 pb-5">
                        <div>
                            <p class="text-base font-medium text-clinic-300">SELANJUTNYA</p>
                            <h2 class="mt-1 text-2xl font-semibold">Antrean menunggu</h2>
                        </div>
                        <p class="text-base text-slate-400">{{ $waitingQueueCount }} menunggu</p>
                    </div>

                    @if ($nextQueues->isEmpty())
                        <p class="py-10 text-center text-base text-slate-400">Belum ada antrean menunggu.</p>
                    @else
                        <ol class="flex flex-col divide-y divide-white/10">
                            @foreach ($nextQueues as $index => $queue)
                                <li class="flex items-center justify-between gap-4 py-4">
                                    <span class="text-base text-slate-400">{{ $index + 1 }}</span>
                                    <span class="text-3xl font-semibold tracking-wide text-white">
                                        {{ str_pad((string) $queue->queue_number, 3, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <span class="rounded-full bg-amber-400/10 px-3 py-1 text-base font-medium text-amber-300">Menunggu</span>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </article>
            </section>

            <footer class="border-t border-white/10 pt-5 text-center text-base text-slate-500">
                Mohon perhatikan layar saat nomor antrean Anda dipanggil.
            </footer>
        </main>
    </body>
</html>
