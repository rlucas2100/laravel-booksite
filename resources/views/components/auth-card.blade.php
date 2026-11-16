@props(['title', 'heading', 'intro' => null, 'mood' => null, 'tone' => 'sun'])
{{-- The two-panel card used by the sign-in, join and password pages. --}}
<x-layout :title="$title">
    <div class="shell py-10 sm:py-14">
        <div class="card mx-auto grid max-w-4xl overflow-hidden md:grid-cols-[1fr_.8fr]">
            <div class="p-6 sm:p-9">
                <h1 class="text-4xl font-black">{{ $heading }}</h1>
                @if ($intro)
                    <p class="mt-2 text-soft">{{ $intro }}</p>
                @endif

                <div class="mt-6">
                    {{ $slot }}
                </div>
            </div>

            <div class="bg-tone tone-{{ $tone }} relative hidden flex-col items-center justify-center gap-4 overflow-hidden border-l-2 border-night p-8 text-center md:flex">
                <div class="halftone halftone-fade pointer-events-none absolute inset-0"></div>
                <x-pip :mood="$mood" class="animate-bob relative h-40 w-40"/>
                <div class="relative">
                    {{ $aside ?? '' }}
                </div>
            </div>
        </div>
    </div>
</x-layout>
