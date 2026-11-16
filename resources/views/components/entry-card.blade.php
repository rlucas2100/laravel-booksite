@props(['entry', 'showType' => false, 'stage' => false])
{{--
    One entry in a grid: cover, title, who made it, stars. Works for every type.
    "stage" puts the cover in a square box, for grids that mix tall, square and wide covers.
--}}
@php
    $type = $entry->type;
@endphp
<article {{ $attributes->class(['tone-'.$type->colour()]) }}>
    <a href="{{ $entry->url() }}" class="group block">
        <div @class(['relative', 'stage' => $stage])>
            <x-cover :entry="$entry" class="lift"/>

            @if ($type->value === 'video' || $type->value === 'audio')
                <span class="bg-tone absolute right-2 bottom-2 grid h-10 w-10 place-items-center rounded-full border-2 border-night shadow-pop-sm">
                    <x-icon :name="$type->icon()" size="h-5 w-5"/>
                </span>
            @endif

            @if ($entry->isSandboxed())
                <span class="chip absolute top-2 left-2 bg-sun text-night" title="Only you can see this in the demo">Only you</span>
            @endif
        </div>

        <div class="mt-3.5">
            @if ($showType)
                <p class="eyebrow mb-1 text-soft"><span class="dot-tone"></span>{{ $type->label() }}</p>
            @endif
            <h3 class="text-lg leading-snug font-bold decoration-2 underline-offset-2 group-hover:underline">{{ $entry->title }}</h3>
            <p class="mt-0.5 truncate text-sm text-soft">{{ $entry->creator }}</p>

            <p class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-sm">
                <x-stars :value="$entry->rating()"/>
                @if ($entry->rating() !== null)
                    <span class="font-bold">{{ number_format($entry->rating(), 1) }}</span>
                    <span class="text-soft">({{ $entry->reviews_count }})</span>
                @else
                    <span class="text-soft">No reviews yet</span>
                @endif
                @if ($entry->comments_count)
                    <span class="inline-flex items-center gap-1 text-soft"><x-icon name="chat" size="h-3.5 w-3.5"/>{{ $entry->comments_count }}</span>
                @endif
            </p>
        </div>
    </a>
</article>
