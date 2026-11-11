<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Dashboard | {{ config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
        <div class="min-h-screen lg:flex">
            @include('sidebar.officer')

            <div class="min-w-0 flex-1">
                <header class="border-b border-slate-200 bg-white">
                    <div class="flex min-h-20 items-center justify-between gap-4 px-4 sm:px-7 lg:px-10">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-base font-semibold text-slate-900">{{ config('app.name') }}</p>
                                <p class="hidden text-base text-slate-500 sm:block">Dashboard petugas</p>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-3 sm:gap-5">
                            <p class="hidden text-base text-slate-500 md:block">{{ $today->locale('id')->translatedFormat('l, d F Y') }}</p>
                            <div class="hidden h-9 w-px bg-slate-200 sm:block"></div>
                            <div class="hidden text-right sm:block">
                                <p class="text-base font-semibold text-slate-800">{{ $user?->name ?? 'Petugas' }}</p>
                                <p class="text-base text-slate-500">Petugas antrean</p>
                            </div>
                            <span class="flex size-10 items-center justify-center rounded-full bg-clinic-100 text-base font-semibold text-clinic-800" aria-hidden="true">
                                {{ str($user?->name ?? 'P')->substr(0, 1)->upper() }}
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
                    <section class="mb-7 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-base font-medium text-clinic-700">Ringkasan layanan</p>
                            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">Dashboard antrean</h1>
                            <p class="mt-2 text-base text-slate-500">Pantau antrean pasien dan status pemeriksaan hari ini.</p>
                        </div>
                        <p class="text-base font-medium text-slate-500 md:hidden">{{ $today->locale('id')->translatedFormat('l, d F Y') }}</p>
                    </section>

                    @if (session('status'))
                        <p role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-base font-medium text-emerald-800">
                            {{ session('status') }}
                        </p>
                    @endif
                    @if (session('error'))
                        <p role="alert" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-base font-medium text-rose-800">
                            {{ session('error') }}
                        </p>
                    @endif

                    <section aria-label="Ringkasan antrean" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ([
                            ['label' => 'Total antrean', 'value' => $totalQueueCount, 'description' => 'terdaftar hari ini', 'tone' => 'clinic'],
                            ['label' => 'Menunggu', 'value' => $waitingQueueCount, 'description' => 'belum dipanggil', 'tone' => 'waiting'],
                            ['label' => 'Sedang dilayani', 'value' => $calledQueueCount, 'description' => 'pasien dipanggil', 'tone' => 'called'],
                            ['label' => 'Selesai', 'value' => $completedQueueCount, 'description' => 'pemeriksaan selesai', 'tone' => 'completed'],
                        ] as $stat)
                            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/50">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-base font-medium text-slate-500">{{ $stat['label'] }}</p>
                                        <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ number_format($stat['value']) }}</p>
                                    </div>
                                    <span @class([
                                        'flex size-11 items-center justify-center rounded-xl text-base font-semibold',
                                        'bg-clinic-50 text-clinic-700' => $stat['tone'] === 'clinic',
                                        'bg-status-waiting/10 text-status-waiting' => $stat['tone'] === 'waiting',
                                        'bg-status-called/10 text-status-called' => $stat['tone'] === 'called',
                                        'bg-status-completed/10 text-status-completed' => $stat['tone'] === 'completed',
                                    ]) aria-hidden="true">
                                        @if ($stat['tone'] === 'clinic')
                                            <svg viewBox="0 0 24 24" fill="none" class="size-5" aria-hidden="true">
                                                <path d="M16 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 18.5V20m6-9a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm6-7.5a4 4 0 0 1 0 7.75M20 20v-1.5a3.5 3.5 0 0 0-2.5-3.35" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        @elseif ($stat['tone'] === 'waiting')
                                            <svg viewBox="0 0 24 24" fill="none" class="size-5" aria-hidden="true">
                                                <circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.7"/>
                                                <path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                            </svg>
                                        @elseif ($stat['tone'] === 'called')
                                            <svg viewBox="0 0 24 24" fill="none" class="size-5" aria-hidden="true">
                                                <path d="M5 12h14m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        @else
                                            <svg viewBox="0 0 24 24" fill="none" class="size-5" aria-hidden="true">
                                                <path d="m5 12 4.5 4.5L19 7" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        @endif
                                    </span>
                                </div>
                                <p class="mt-4 border-t border-slate-100 pt-3 text-base text-slate-500">{{ $stat['description'] }}</p>
                            </article>
                        @endforeach
                    </section>

                    <section class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(18rem,0.65fr)]">
                        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-200/50">
                            <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-5 sm:px-6">
                                <div>
                                    <h2 class="font-semibold text-slate-900">Antrean hari ini</h2>
                                    <p class="mt-1 text-base text-slate-500">Urutan pasien berdasarkan posisi antrean.</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-slate-100 px-3 py-1 text-base font-medium text-slate-600">
                                    {{ $queues->count() }} antrean teratas
                                </span>
                            </div>

                            @if ($queues->isEmpty())
                                <div class="px-6 py-14 text-center">
                                    <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                        <svg viewBox="0 0 24 24" fill="none" class="size-6" aria-hidden="true">
                                            <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <h3 class="mt-4 text-base font-semibold text-slate-900">Belum ada antrean</h3>
                                    <p class="mt-1 text-base text-slate-500">Antrean pasien yang terdaftar hari ini akan muncul di sini.</p>
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="w-full min-w-[52rem] text-left text-base">
                                        <thead class="bg-slate-50 text-base font-medium text-slate-500">
                                            <tr>
                                                <th scope="col" class="px-5 py-3.5 sm:px-6">Nomor</th>
                                                <th scope="col" class="px-4 py-3.5">Pasien</th>
                                                <th scope="col" class="px-4 py-3.5">Dokter</th>
                                                <th scope="col" class="px-4 py-3.5">Waktu daftar</th>
                                                <th scope="col" class="px-5 py-3.5 sm:px-6">Status</th>
                                                <th scope="col" class="px-5 py-3.5 sm:px-6">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @foreach ($queues as $queue)
                                                @php
                                                    $queueStatus = match ($queue->status) {
                                                        \App\Models\Queue::STATUS_WAITING => ['label' => 'Menunggu', 'class' => 'bg-status-waiting/10 text-status-waiting ring-status-waiting/20'],
                                                        \App\Models\Queue::STATUS_CALLED => ['label' => 'Dipanggil', 'class' => 'bg-status-called/10 text-status-called ring-status-called/20'],
                                                        \App\Models\Queue::STATUS_COMPLETED => ['label' => 'Selesai', 'class' => 'bg-status-completed/10 text-status-completed ring-status-completed/20'],
                                                        \App\Models\Queue::STATUS_CANCELLED => ['label' => 'Dibatalkan', 'class' => 'bg-status-cancelled/10 text-status-cancelled ring-status-cancelled/20'],
                                                        default => ['label' => 'Tidak diketahui', 'class' => 'bg-status-cancelled/10 text-status-cancelled ring-status-cancelled/20'],
                                                    };
                                                @endphp
                                                <tr class="transition hover:bg-slate-50/70">
                                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-900 sm:px-6">
                                                        {{ str_pad((string) $queue->queue_number, 3, '0', STR_PAD_LEFT) }}
                                                    </td>
                                                    <td class="px-4 py-4">
                                                        <p class="font-medium text-slate-800">{{ $queue->patient->name }}</p>
                                                        <p class="mt-0.5 text-base text-slate-500">Usia {{ $queue->patient->age }} tahun</p>
                                                    </td>
                                                    <td class="px-4 py-4 text-slate-600">
                                                        @if ($queue->doctor)
                                                            {{ $queue->doctor->title ? $queue->doctor->title.' ' : '' }}{{ $queue->doctor->full_name }}
                                                        @else
                                                            <span class="text-slate-400">Belum dipilih</span>
                                                        @endif
                                                    </td>
                                                    <td class="whitespace-nowrap px-4 py-4 text-slate-500">{{ $queue->created_at->format('H:i') }}</td>
                                                    <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                                                        <span @class(['inline-flex rounded-full px-2.5 py-1 text-base font-medium ring-1 ring-inset', $queueStatus['class']])>
                                                            {{ $queueStatus['label'] }}
                                                        </span>
                                                    </td>
                                                    <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                                                        @if ($queue->status === \App\Models\Queue::STATUS_WAITING)
                                                            <form
                                                                method="POST"
                                                                action="{{ route('queues.cancel', $queue) }}"
                                                                onsubmit="return confirm('Yakin ingin membatalkan antrean ini?')"
                                                            >
                                                                @csrf
                                                                <button
                                                                    type="submit"
                                                                    class="rounded-lg border border-rose-200 px-3 py-1.5 text-base font-medium text-rose-700 transition hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2"
                                                                >
                                                                    Batalkan
                                                                </button>
                                                            </form>
                                                        @else
                                                            <span class="text-slate-400">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </article>

                        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/50 sm:p-6">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <h2 class="font-semibold text-slate-900">Sedang dilayani</h2>
                                    <p class="mt-1 text-base text-slate-500">Pasien dengan giliran aktif.</p>
                                </div>
                                <span class="flex size-10 items-center justify-center rounded-xl bg-clinic-50 text-clinic-700">
                                    <svg viewBox="0 0 24 24" fill="none" class="size-5" aria-hidden="true">
                                        <path d="M12 3v18M3 12h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </span>
                            </div>

                            @if ($currentQueue)
                                <div class="mt-7 rounded-2xl bg-gradient-to-br from-clinic-600 to-clinic-800 p-5 text-white">
                                    <p class="text-base font-medium text-clinic-50">Nomor antrean</p>
                                    <p class="mt-2 text-5xl font-semibold tracking-tight">
                                        {{ str_pad((string) $currentQueue->queue_number, 3, '0', STR_PAD_LEFT) }}
                                    </p>
                                    <div class="mt-5 border-t border-white/20 pt-4">
                                        <p class="font-medium">{{ $currentQueue->patient->name }}</p>
                                        <p class="mt-1 text-base text-clinic-50">Usia {{ $currentQueue->patient->age }} tahun</p>
                                        @if ($currentQueue->doctor)
                                            <p class="mt-1 text-base text-clinic-50">
                                                Dokter: {{ $currentQueue->doctor->title ? $currentQueue->doctor->title.' ' : '' }}{{ $currentQueue->doctor->full_name }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <p class="mt-4 text-base text-slate-500">
                                    Dipanggil pukul {{ $currentQueue->called_at?->format('H:i') ?? '—' }}
                                </p>
                                <form method="POST" action="{{ route('queues.complete', $currentQueue) }}" class="mt-5">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-base font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                                    >
                                        Selesaikan pemeriksaan
                                    </button>
                                </form>
                            @else
                                <div class="mt-7 rounded-2xl border border-dashed border-slate-200 px-5 py-8 text-center">
                                    <p class="text-base font-medium text-slate-700">Belum ada pasien dipanggil</p>
                                    <p class="mt-1 text-base text-slate-500">Nomor yang sedang dilayani akan tampil di sini.</p>
                                </div>
                            @endif

                            <div class="mt-6 border-t border-slate-100 pt-5">
                                <div class="flex items-center justify-between text-base">
                                    <span class="text-slate-500">Antrean menunggu</span>
                                    <span class="font-semibold text-slate-800">{{ number_format($waitingQueueCount) }} pasien</span>
                                </div>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div
                                        class="h-full rounded-full bg-clinic-600"
                                        style="width: {{ $activeQueueCount > 0 ? min(100, (int) round(($completedQueueCount / $activeQueueCount) * 100)) : 0 }}%"
                                    ></div>
                                </div>
                                <p class="mt-2 text-base text-slate-500">Ringkasan antrean terdaftar hari ini</p>
                            </div>

                            @if ($waitingQueueCount > 0)
                                <form method="POST" action="{{ route('queues.call-next') }}" class="mt-5">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="w-full rounded-xl bg-clinic-600 px-4 py-3 text-base font-semibold text-white transition hover:bg-clinic-700 focus:outline-none focus:ring-2 focus:ring-clinic-500 focus:ring-offset-2"
                                    >
                                        Panggil antrean berikutnya
                                    </button>
                                </form>
                            @endif
                        </article>
                    </section>

                    <footer class="py-7 text-center text-base text-slate-400">
                        &copy; {{ now()->year }} {{ config('app.name') }}. Panel petugas antrean.
                    </footer>
                </main>
            </div>
        </div>
    </body>
</html>
