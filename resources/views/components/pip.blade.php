@props(['mood' => null])
{{--
    Pip the bookworm, the shop's mascot. His face follows data-mood (0 to 5, the
    same as the stars): <x-pip mood="5" />, or live with Alpine:
    <x-pip x-bind:data-mood="shown" />. Without a mood he just smiles.
--}}
<svg {{ $attributes->class('pip') }} @if ($mood !== null) data-mood="{{ $mood }}" @endif viewBox="0 0 120 132" role="img" aria-label="Pip the bookworm">
    {{-- body and head --}}
    <path d="M62 118c0-26-10-30-8-52" fill="none" stroke="#17151c" stroke-width="31" stroke-linecap="round"/>
    <path d="M62 118c0-26-10-30-8-52" fill="none" stroke="#2fd1a5" stroke-width="24.5" stroke-linecap="round"/>
    <path d="M47 88q9 4 18 0M46 76q8 3 16-1" fill="none" stroke="#17151c" stroke-width="2.5" stroke-linecap="round" opacity=".45"/>
    <path d="M46 21l-5-11M65 20l4-11" stroke="#17151c" stroke-width="3" stroke-linecap="round"/>
    <circle cx="40" cy="8" r="3.4" fill="#ffcf33" stroke="#17151c" stroke-width="2.5"/>
    <circle cx="70" cy="7" r="3.4" fill="#ffcf33" stroke="#17151c" stroke-width="2.5"/>
    <circle cx="56" cy="41" r="23" fill="#2fd1a5" stroke="#17151c" stroke-width="3.5"/>

    {{-- glasses --}}
    <path d="M37 37l-5-2M76 37l5-2M55 38h3" stroke="#17151c" stroke-width="3" stroke-linecap="round"/>
    <circle cx="46" cy="39" r="9.5" fill="#fff" stroke="#17151c" stroke-width="3"/>
    <circle cx="67" cy="39" r="9.5" fill="#fff" stroke="#17151c" stroke-width="3"/>

    {{-- 0 stars: knocked out --}}
    <g data-face="0" fill="none" stroke="#17151c" stroke-width="3" stroke-linecap="round">
        <path d="M42.500 35.500l7 7m0-7-7 7M63.500 35.500l7 7m0-7-7 7"/>
        <path d="M47.500 56q2.250-3.500 4.500 0t4.500 0 4.500 0 4.500 0"/>
    </g>
    {{-- 1 star: sad --}}
    <g data-face="1">
        <circle cx="46" cy="42" r="2.700" fill="#17151c"/><circle cx="67" cy="42" r="2.700" fill="#17151c"/>
        <path d="M37 25l11 3.500M76 25l-11 3.500M50 57q6.500-5.500 13 0" fill="none" stroke="#17151c" stroke-width="3" stroke-linecap="round"/>
    </g>
    {{-- 2 stars: unmoved --}}
    <g data-face="2">
        <circle cx="46" cy="40" r="2.700" fill="#17151c"/><circle cx="67" cy="40" r="2.700" fill="#17151c"/>
        <path d="M51 55h11" stroke="#17151c" stroke-width="3" stroke-linecap="round"/>
    </g>
    {{-- 3 stars (and his everyday face): a smile --}}
    <g data-face="3">
        <circle cx="47" cy="40" r="2.900" fill="#17151c"/><circle cx="66" cy="40" r="2.900" fill="#17151c"/>
        <path d="M50 53q6.500 5.500 13 0" fill="none" stroke="#17151c" stroke-width="3" stroke-linecap="round"/>
    </g>
    {{-- 4 stars: delighted --}}
    <g data-face="4" stroke="#17151c" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
        <path d="M41.500 41.500q4.500-6 9 0M62.500 41.500q4.500-6 9 0" fill="none"/>
        <path d="M48 52q8.500 11 17 0Z" fill="#17151c"/>
    </g>
    {{-- 5 stars: star-struck --}}
    <g data-face="5">
        <g fill="#ffcf33" stroke="#17151c" stroke-width="1.700" stroke-linejoin="round">
            <polygon transform="translate(46 39.500)" points="0,-6.500 1.700,-2.300 6.200,-2 2.700,.900 3.800,5.300 0,2.900 -3.800,5.300 -2.700,.900 -6.200,-2 -1.700,-2.300"/>
            <polygon transform="translate(67 39.500)" points="0,-6.500 1.700,-2.300 6.200,-2 2.700,.900 3.800,5.300 0,2.900 -3.800,5.300 -2.700,.900 -6.200,-2 -1.700,-2.300"/>
            <path d="M93 16l2.200 5.300 5.300 2.200-5.300 2.200-2.200 5.300-2.200-5.300-5.300-2.200 5.300-2.200ZM17 30l1.600 3.900 3.900 1.600-3.900 1.600-1.600 3.900-1.600-3.900-3.900-1.600 3.900-1.600Z"/>
        </g>
        <path d="M47 51q9.500 13.500 19 0Z" fill="#17151c" stroke="#17151c" stroke-width="3" stroke-linejoin="round"/>
        <path d="M52.500 57.500q4 2.500 8 0" fill="none" stroke="#ff5c39" stroke-width="3" stroke-linecap="round"/>
    </g>

    {{-- the book he lives in --}}
    <path d="M8 102c18-8 34-8 52 2 18-10 34-10 52-2v26c-18-8-34-8-52 2-18-10-34-10-52-2Z" fill="#ff5c39" stroke="#17151c" stroke-width="3.500" stroke-linejoin="round"/>
    <path d="M14 98c16-7 30-6 46 3 16-9 30-10 46-3v24c-16-7-30-6-46 3-16-9-30-10-46-3Z" fill="#fff7e8" stroke="#17151c" stroke-width="3" stroke-linejoin="round"/>
    <path d="M60 101v24" stroke="#17151c" stroke-width="3"/>
    <path d="M22 107c9-3 18-3 28 1M22 114c9-3 18-3 28 1M70 108c10-4 19-4 28-1M70 115c10-4 19-4 28-1" fill="none" stroke="#17151c" stroke-width="2" stroke-linecap="round" opacity=".3"/>
</svg>
