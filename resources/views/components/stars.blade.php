@props(['value' => null, 'size' => '1rem'])
{{-- Five stars filled up to $value (0 to 5, halves and tenths too). <x-stars :value="4.3" /> --}}
@php
    $percent = $value === null ? 0 : max(0, min(100, $value / 5 * 100));
    $star = 'M12 2.8l2.83 5.74 6.34.92-4.59 4.47 1.08 6.31L12 17.26l-5.66 2.98 1.08-6.31L2.83 9.46l6.34-.92L12 2.8z';
@endphp
<span {{ $attributes->class('stars') }} style="--star: {{ $size }}" role="img"
      aria-label="{{ $value === null ? 'No reviews yet' : rtrim(rtrim(number_format($value, 1), '0'), '.').' out of 5 stars' }}">
    <span class="stars-row">@for ($i = 0; $i < 5; $i++)<svg viewBox="0 0 24 24" class="star-empty" stroke-width="1.8" stroke-linejoin="round"><path d="{{ $star }}"/></svg>@endfor</span>
    <span class="stars-row stars-fill" style="width: {{ $percent }}%">@for ($i = 0; $i < 5; $i++)<svg viewBox="0 0 24 24" class="star-full" stroke-width="1.8" stroke-linejoin="round"><path d="{{ $star }}"/></svg>@endfor</span>
</span>
