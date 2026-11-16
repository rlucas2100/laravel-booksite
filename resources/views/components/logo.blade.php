{{-- The shop's mark (a book with a bookmark hanging out of it) and its name. --}}
<a href="{{ route('home') }}" {{ $attributes->class('inline-flex shrink-0 items-center gap-2.5') }}>
    <svg viewBox="0 0 64 64" class="h-10 w-10" aria-hidden="true">
        <rect x="4" y="4" width="56" height="56" rx="14" fill="#ff5c39" stroke="#17151c" stroke-width="4"/>
        <path d="M14 22c7-3 13-3 18 1 5-4 11-4 18-1v24c-7-3-13-3-18 1-5-4-11-4-18-1Z" fill="#fff7e8" stroke="#17151c" stroke-width="3.5" stroke-linejoin="round"/>
        <path d="M32 23v24" stroke="#17151c" stroke-width="3.5"/>
        <path d="M39 20v19l4-4 4 4V21" fill="#ffcf33" stroke="#17151c" stroke-width="3" stroke-linejoin="round"/>
    </svg>
    <span class="font-display text-2xl leading-none font-black tracking-tight">{{ config('app.name') }}</span>
</a>
