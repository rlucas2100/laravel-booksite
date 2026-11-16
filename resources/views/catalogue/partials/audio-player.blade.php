{{-- The cassette player. Behaviour: audioPlayer in resources/js/app.js. --}}
<div class="card bg-tone border-night p-4 text-night sm:p-5" x-data="audioPlayer(@js($entry->slug))" :class="{ 'is-playing': playing }">
    <audio x-ref="audio" preload="metadata" src="{{ $entry->fileUrl() }}"></audio>

    <div class="flex items-center gap-4">
        {{-- The tape: its reels turn while the recording plays --}}
        <svg viewBox="0 0 160 104" class="hidden w-32 shrink-0 sm:block" aria-hidden="true">
            <rect x="2" y="2" width="156" height="100" rx="11" fill="#fff7e8" stroke="#17151c" stroke-width="4"/>
            <rect x="14" y="13" width="132" height="50" rx="6" fill="#ffcf33" stroke="#17151c" stroke-width="3"/>
            <rect x="36" y="24" width="88" height="28" rx="14" fill="#17151c"/>
            <g class="reel"><circle cx="54" cy="38" r="10" fill="#fff7e8"/><path d="M54 29v18M46.200 33.500l15.600 9M46.200 42.500l15.600-9" stroke="#17151c" stroke-width="2.500"/></g>
            <g class="reel"><circle cx="106" cy="38" r="10" fill="#fff7e8"/><path d="M106 29v18M98.200 33.500l15.600 9M98.200 42.500l15.600-9" stroke="#17151c" stroke-width="2.500"/></g>
            <path d="M42 102l7-24h62l7 24" fill="none" stroke="#17151c" stroke-width="3.500" stroke-linejoin="round"/>
            <circle cx="62" cy="91" r="3" fill="#17151c"/><circle cx="98" cy="91" r="3" fill="#17151c"/>
        </svg>

        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
                <button type="button" class="btn btn-sm h-10 border-night bg-cream px-2.5 text-night" @click="skip(-15)" aria-label="Back 15 seconds"><x-icon name="back" size="h-4.5 w-4.5"/> 15</button>

                <button type="button" class="btn h-14 w-14 rounded-full border-night bg-night p-0! text-cream" @click="toggle" :aria-label="playing ? 'Pause' : 'Play'">
                    <span x-show="!playing"><x-icon name="play" size="h-6 w-6" class="translate-x-0.5"/></span>
                    <span x-show="playing" x-cloak><x-icon name="pause" size="h-6 w-6"/></span>
                </button>

                <button type="button" class="btn btn-sm h-10 border-night bg-cream px-2.5 text-night" @click="skip(15)" aria-label="Forward 15 seconds">15 <x-icon name="forward" size="h-4.5 w-4.5"/></button>

                <button type="button" class="btn btn-sm ml-auto h-10 w-14 border-night bg-cream text-night" @click="faster" aria-label="Playback speed" x-text="rate + '×'">1×</button>
            </div>

            <label class="sr-only" for="audio-position">Position in the recording</label>
            <input id="audio-position" type="range" class="range mt-4" min="0" step="0.1" :max="duration || 0" :value="current" @input="seek" :style="'--fill: ' + percent + '%'">

            <p class="mt-1 flex justify-between text-sm font-bold tabular-nums">
                <span x-text="clock(current)">0:00</span>
                <span x-text="clock(duration)">{{ $entry->duration ? intdiv($entry->duration, 60).':'.str_pad((string) ($entry->duration % 60), 2, '0', STR_PAD_LEFT) : '0:00' }}</span>
            </p>
        </div>
    </div>

    <p class="mt-3 rounded-lg border-2 border-night bg-cream px-3 py-2 text-sm font-bold" x-show="failed" x-cloak role="alert">
        This recording wouldn’t play here. <a class="underline" href="{{ $entry->fileUrl(true) }}">Download it</a> instead.
    </p>
</div>
