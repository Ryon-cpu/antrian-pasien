<div class="border-b border-slate-200 bg-white px-4 py-3 sm:px-7 lg:hidden">
    <div class="flex items-center justify-between gap-4">
        <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-clinic-600 text-white">
                <svg viewBox="0 0 24 24" fill="none" class="size-5" aria-hidden="true">
                    <path d="M12 3v18M3 12h18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </span>
            <span class="truncate text-base font-semibold text-slate-900">{{ config('app.name') }}</span>
        </a>

        <details class="group relative">
            <summary class="flex cursor-pointer list-none items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-base font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-clinic-500 focus:ring-offset-2 [&::-webkit-details-marker]:hidden">
                <svg viewBox="0 0 24 24" fill="none" class="size-5" aria-hidden="true">
                    <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                Menu
                <svg viewBox="0 0 24 24" fill="none" class="size-4 transition group-open:rotate-180" aria-hidden="true">
                    <path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </summary>
            <nav aria-label="Navigasi utama" class="absolute right-0 top-full z-20 mt-2 flex w-64 flex-col rounded-2xl bg-slate-950 p-3 text-white shadow-xl">
                @include('sidebar.officer-links')
            </nav>
        </details>
    </div>
</div>

<aside class="hidden w-72 shrink-0 flex-col bg-slate-950 text-white lg:flex">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-7 py-8">
        <span class="flex size-11 items-center justify-center rounded-2xl bg-clinic-600 text-white">
            <svg viewBox="0 0 24 24" fill="none" class="size-6" aria-hidden="true">
                <path d="M12 3v18M3 12h18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
            </svg>
        </span>
        <span class="min-w-0">
            <span class="block truncate text-base font-semibold">{{ config('app.name') }}</span>
            <span class="mt-0.5 block text-base text-slate-400">Panel petugas</span>
        </span>
    </a>

    <div class="px-5">
        <p class="px-3 pb-3 text-base font-semibold tracking-[0.18em] text-slate-500 uppercase">Menu utama</p>
        <nav aria-label="Navigasi utama">
            @include('sidebar.officer-links')
        </nav>
    </div>

    <div class="mt-auto p-5">
        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
            <div class="flex items-center gap-2 text-base font-medium text-white">
                <span class="size-2 rounded-full bg-status-completed"></span>
                Pantauan antrean
            </div>
            <p class="mt-2 text-base leading-6 text-slate-400">Ringkasan antrean hari ini diperbarui saat halaman dibuka.</p>
        </div>
        <p class="px-1 pt-5 text-base text-slate-500">{{ config('app.name') }}</p>
    </div>
</aside>
