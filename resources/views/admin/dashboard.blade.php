@php
    use App\Enums\EntryType;
    use App\Models\Review;

    $most = max(1, (int) $spread->max());
@endphp
<x-admin title="Overview">
    {{-- The numbers --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach (EntryType::cases() as $type)
            <a href="{{ route('admin.entries', ['type' => $type->value]) }}" class="card bg-tone tone-{{ $type->colour() }} border-night p-4 transition-transform hover:-translate-y-0.5">
                <span class="flex items-center justify-between"><x-icon :name="$type->icon()" size="h-6 w-6"/><span class="pow text-4xl">{{ $counts[$type->value] ?? 0 }}</span></span>
                <span class="mt-1 block font-bold">{{ $type->shelf() }}</span>
            </a>
        @endforeach

        <div class="card p-4">
            <span class="flex items-center justify-between"><x-icon name="users" size="h-6 w-6"/><span class="pow text-4xl">{{ $members }}</span></span>
            <span class="mt-1 block font-bold">Members</span>
        </div>
        <div class="card p-4">
            <span class="flex items-center justify-between"><x-icon name="star" size="h-6 w-6"/><span class="pow text-4xl">{{ $reviewCount }}</span></span>
            <span class="mt-1 block font-bold">Reviews</span>
        </div>
        <div class="card p-4">
            <span class="flex items-center justify-between"><x-icon name="chat" size="h-6 w-6"/><span class="pow text-4xl">{{ $commentCount }}</span></span>
            <span class="mt-1 block font-bold">Comments</span>
        </div>
        <div class="card bg-sun p-4 text-night">
            <span class="flex items-center justify-between"><x-stars :value="$averageRating" size="1.1rem"/><span class="pow text-4xl">{{ $averageRating !== null ? number_format($averageRating, 1) : '–' }}</span></span>
            <span class="mt-1 block font-bold">Average score</span>
        </div>
    </div>

    <div class="mt-8 grid gap-8 lg:grid-cols-2">
        {{-- How the stars fall, drawn as a bar chart --}}
        <section class="card p-5">
            <h2 class="text-2xl font-black">How readers score</h2>
            <p class="text-sm text-soft">Every review on the site, by number of stars.</p>

            <div class="mt-5 flex h-44 items-end gap-3" role="img" aria-label="Bar chart of reviews by score">
                @foreach (range(Review::MIN, Review::MAX) as $stars)
                    @php
                        $count = (int) ($spread[$stars] ?? 0);
                    @endphp
                    <div class="flex h-full flex-1 flex-col items-center justify-end gap-1">
                        <span class="text-sm font-bold tabular-nums">{{ $count }}</span>
                        <span class="w-full rounded-t-lg border-2 border-line {{ $stars === 0 ? 'bg-tomato' : 'bg-sun' }}" style="height: {{ max(3, $count / $most * 100) }}%"></span>
                    </div>
                @endforeach
            </div>
            <div class="mt-2 flex gap-3 border-t-2 border-line pt-2">
                @foreach (range(Review::MIN, Review::MAX) as $stars)
                    <span class="flex-1 text-center text-sm font-bold">{{ $stars }}★</span>
                @endforeach
            </div>
        </section>

        <section class="card p-5">
            <h2 class="text-2xl font-black">Top rated</h2>
            <p class="text-sm text-soft">With at least two reviews.</p>
            <ol class="mt-4 space-y-3">
                @forelse ($topRated as $i => $entry)
                    <li class="flex items-center gap-3">
                        <span class="pow w-6 text-center text-2xl">{{ $i + 1 }}</span>
                        <a href="{{ $entry->url() }}" class="min-w-0 flex-1 truncate font-bold hover:underline">{{ $entry->title }}</a>
                        <x-stars :value="$entry->rating()"/>
                        <span class="w-8 text-right font-bold tabular-nums">{{ number_format($entry->rating(), 1) }}</span>
                    </li>
                @empty
                    <li class="text-soft">Nothing has two reviews yet.</li>
                @endforelse
            </ol>
        </section>

        <section class="card p-5">
            <h2 class="text-2xl font-black">Latest reviews</h2>
            <ul class="mt-4 space-y-4">
                @forelse ($latestReviews as $review)
                    <li class="flex gap-3">
                        <x-avatar :user="$review->user" small/>
                        <div class="min-w-0">
                            <p class="text-sm"><b>{{ $review->user->name }}</b> on <a class="font-bold hover:underline" href="{{ $review->entry->url() }}#review-{{ $review->id }}">{{ $review->entry->title }}</a></p>
                            <p class="flex items-center gap-2 text-sm text-soft"><x-stars :value="$review->rating" size=".85rem"/> {{ $review->created_at->diffForHumans() }}</p>
                        </div>
                    </li>
                @empty
                    <li class="text-soft">No reviews yet.</li>
                @endforelse
            </ul>
            @can('moderate-community')
                <a href="{{ route('admin.community') }}" class="btn btn-sm mt-5">All reviews and comments <x-icon name="arrow-right" size="h-4 w-4"/></a>
            @endcan
        </section>

        <section class="card p-5">
            <h2 class="text-2xl font-black">Newest on the shelves</h2>
            <ul class="mt-4 space-y-3">
                @foreach ($latestEntries as $entry)
                    <li class="tone-{{ $entry->type->colour() }} flex items-center gap-3">
                        <span class="chip chip-tone shrink-0">{{ $entry->type->label() }}</span>
                        <a href="{{ $entry->url() }}" class="min-w-0 flex-1 truncate font-bold hover:underline">{{ $entry->title }}</a>
                        <span class="shrink-0 text-sm text-soft">{{ $entry->created_at->diffForHumans(short: true) }}</span>
                    </li>
                @endforeach
            </ul>
            @can('manage-entries')
                <a href="{{ route('admin.entries') }}" class="btn btn-sm mt-5">The whole catalogue <x-icon name="arrow-right" size="h-4 w-4"/></a>
            @endcan
        </section>
    </div>
</x-admin>
