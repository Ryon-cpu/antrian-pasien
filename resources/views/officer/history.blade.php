<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Riwayat Antrean | {{ config('app.name') }}</title>

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
                            <p class="hidden text-base text-slate-500 sm:block">Riwayat antrean pasien</p>
                        </div>

                        <div class="flex shrink-0 items-center gap-3 sm:gap-5">
                            <p class="hidden text-base text-slate-500 md:block">
                                {{ $today->locale('id')->translatedFormat('l, d F Y') }}
                            </p>
                            <div class="hidden h-9 w-px bg-slate-200 sm:block"></div>
                            <div class="hidden text-right sm:block">
                                <p class="text-base font-semibold text-slate-800">{{ $user->name }}</p>
                                <p class="text-base text-slate-500">Petugas antrean</p>
                            </div>
                            <span class="flex size-10 items-center justify-center rounded-full bg-clinic-100 text-base font-semibold text-clinic-800" aria-hidden="true">
                                {{ str($user->name)->substr(0, 1)->upper() }}
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
                            <p class="text-base font-medium text-clinic-700">Data layanan</p>
                            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">Riwayat antrean</h1>
                            <p class="mt-2 text-base text-slate-500">Lihat antrean pasien berdasarkan tanggal pendaftaran.</p>
                        </div>

                        <form method="GET" action="{{ route('queues.history') }}" class="flex flex-col gap-2 sm:flex-row sm:items-end">
                            <div>
                                <label for="date" class="mb-1 block text-base font-medium text-slate-700">Tanggal antrean</label>
                                <input
                                    id="date"
                                    name="date"
                                    type="date"
                                    value="{{ $selectedDate->toDateString() }}"
                                    max="{{ $today->toDateString() }}"
                                    required
                                    class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-base outline-none transition focus:border-clinic-500 focus:ring-2 focus:ring-clinic-500/20"
                                >
                            </div>
                            <button
                                type="submit"
                                class="rounded-xl bg-clinic-700 px-4 py-2.5 text-base font-semibold text-white transition hover:bg-clinic-800 focus:outline-none focus:ring-2 focus:ring-clinic-500 focus:ring-offset-2"
                            >
                                Tampilkan
                            </button>
                        </form>
                    </section>

                    @if ($errors->has('date'))
                        <p role="alert" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-base font-medium text-rose-800">
                            {{ $errors->first('date') }}
                        </p>
                    @endif

                    <section aria-label="Ringkasan status antrean" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                        @foreach ([
                            ['label' => 'Total antrean', 'value' => $totalQueueCount, 'class' => 'bg-clinic-50 text-clinic-700'],
                            ['label' => 'Menunggu', 'value' => $waitingQueueCount, 'class' => 'bg-status-waiting/10 text-status-waiting'],
                            ['label' => 'Dipanggil', 'value' => $calledQueueCount, 'class' => 'bg-status-called/10 text-status-called'],
                            ['label' => 'Selesai', 'value' => $completedQueueCount, 'class' => 'bg-status-completed/10 text-status-completed'],
                            ['label' => 'Dibatalkan', 'value' => $cancelledQueueCount, 'class' => 'bg-status-cancelled/10 text-status-cancelled'],
                        ] as $stat)
                            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/50">
                                <p class="text-base font-medium text-slate-500">{{ $stat['label'] }}</p>
                                <p class="mt-3 inline-flex rounded-xl px-3 py-1 text-2xl font-semibold {{ $stat['class'] }}">
                                    {{ number_format($stat['value']) }}
                                </p>
                            </article>
                        @endforeach
                    </section>

                    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-200/50">
                        <div class="flex flex-col gap-1 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div>
                                <h2 class="font-semibold text-slate-900">Daftar pasien</h2>
                                <p class="mt-1 text-base text-slate-500">
                                    {{ $selectedDate->locale('id')->translatedFormat('l, d F Y') }}
                                </p>
                            </div>
                            <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-base font-medium text-slate-600">
                                {{ $queues->total() }} antrean
                            </span>
                        </div>

                        @if ($queues->isEmpty())
                            <div class="px-6 py-14 text-center">
                                <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                    <svg viewBox="0 0 24 24" fill="none" class="size-6" aria-hidden="true">
                                        <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <h3 class="mt-4 text-base font-semibold text-slate-900">Belum ada riwayat antrean</h3>
                                <p class="mt-1 text-base text-slate-500">Tidak ada antrean yang terdaftar pada tanggal ini.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[48rem] text-left text-base">
                                    <thead class="bg-slate-50 text-base font-medium text-slate-500">
                                        <tr>
                                            <th scope="col" class="px-5 py-3.5 sm:px-6">Nomor</th>
                                            <th scope="col" class="px-4 py-3.5">Pasien</th>
                                            <th scope="col" class="px-4 py-3.5">Dokter</th>
                                            <th scope="col" class="px-4 py-3.5">Waktu daftar</th>
                                            <th scope="col" class="px-5 py-3.5 sm:px-6">Status</th>
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
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if ($queues->hasPages())
                                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">
                                    {{ $queues->links() }}
                                </div>
                            @endif
                        @endif
                    </section>
                </main>
            </div>
        </div>
    </body>
</html>
