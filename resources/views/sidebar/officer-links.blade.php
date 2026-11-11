<a
    href="{{ route('dashboard') }}"
    @if (request()->routeIs('dashboard')) aria-current="page" @endif
    @class([
        'flex items-center gap-3 rounded-xl px-3 py-3 text-base font-medium transition',
        'bg-clinic-600/15 text-clinic-300' => request()->routeIs('dashboard'),
        'text-slate-300 hover:bg-white/5 hover:text-white' => ! request()->routeIs('dashboard'),
    ])
>
    <svg viewBox="0 0 24 24" fill="none" class="size-5" aria-hidden="true">
        <path d="M3 10.5 12 3l9 7.5v9a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 19.5v-9Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
        <path d="M9 21v-7h6v7" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
    </svg>
    Dashboard
</a>
<a
    href="{{ route('queues.create') }}"
    @if (request()->routeIs('queues.create')) aria-current="page" @endif
    @class([
        'mt-1 flex items-center gap-3 rounded-xl px-3 py-3 text-base font-medium transition',
        'bg-clinic-600/15 text-clinic-300' => request()->routeIs('queues.create'),
        'text-slate-300 hover:bg-white/5 hover:text-white' => ! request()->routeIs('queues.create'),
    ])
>
    <svg viewBox="0 0 24 24" fill="none" class="size-5" aria-hidden="true">
        <path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        <rect x="3" y="3" width="18" height="18" rx="4" stroke="currentColor" stroke-width="1.6"/>
    </svg>
    Ambil antrean
</a>
<a
    href="{{ route('queues.display') }}"
    @if (request()->routeIs('queues.display')) aria-current="page" @endif
    @class([
        'mt-1 flex items-center gap-3 rounded-xl px-3 py-3 text-base font-medium transition',
        'bg-clinic-600/15 text-clinic-300' => request()->routeIs('queues.display'),
        'text-slate-300 hover:bg-white/5 hover:text-white' => ! request()->routeIs('queues.display'),
    ])
>
    <svg viewBox="0 0 24 24" fill="none" class="size-5" aria-hidden="true">
        <rect x="3" y="4" width="18" height="13" rx="2" stroke="currentColor" stroke-width="1.7"/>
        <path d="M8 21h8m-4-4v4m-5-9 3-3 2 2 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    Layar antrean
</a>
<a
    href="{{ route('queues.history') }}"
    @if (request()->routeIs('queues.history')) aria-current="page" @endif
    @class([
        'mt-1 flex items-center gap-3 rounded-xl px-3 py-3 text-base font-medium transition',
        'bg-clinic-600/15 text-clinic-300' => request()->routeIs('queues.history'),
        'text-slate-300 hover:bg-white/5 hover:text-white' => ! request()->routeIs('queues.history'),
    ])
>
    <svg viewBox="0 0 24 24" fill="none" class="size-5" aria-hidden="true">
        <path d="M4 5h16M4 12h16M4 19h10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
        <circle cx="18" cy="18" r="3" stroke="currentColor" stroke-width="1.7"/>
        <path d="M18 16.5V18l1 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    Riwayat antrean
</a>
<a
    href="{{ route('doctors.index') }}"
    @if (request()->routeIs('doctors.*')) aria-current="page" @endif
    @class([
        'mt-1 flex items-center gap-3 rounded-xl px-3 py-3 text-base font-medium transition',
        'bg-clinic-600/15 text-clinic-300' => request()->routeIs('doctors.*'),
        'text-slate-300 hover:bg-white/5 hover:text-white' => ! request()->routeIs('doctors.*'),
    ])
>
    <svg viewBox="0 0 24 24" fill="none" class="size-5" aria-hidden="true">
        <path d="M16 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 18.5V20m6-9a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm6-7.5a4 4 0 0 1 0 7.75M20 20v-1.5a3.5 3.5 0 0 0-2.5-3.35" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    Dokter
</a>
