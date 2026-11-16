@php
    use App\Enums\EntryType;
    use App\Models\Entry;

    // This one view is the "everything" page, each shelf (/books...) and each category page.
    $heading = $type?->shelf() ?? $category?->name ?? ($search !== '' ? 'Search' : 'Everything');
    $intro = $type?->intro() ?? $category?->blurb ?? 'Every book, video, recording and post, in one place.';
    $tone = $type?->colour() ?? 'sun';
    $filtered = $search !== '' || $sort !== 'new' || (! $type && $chosenType) || (! $category && $chosenCategory);

    // Covers of one shape get a grid that suits them; a mix gets square "stages".
    $grid = match ($chosenType?->coverShape()) {
        'tall' => 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6',
        'square' => 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4',
        'wide' => 'sm:grid-cols-2 lg:grid-cols-3',
        default => 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4',
    };
@endphp
<x-layout :title="$heading" :description="$intro">
    <section class="bg-tone tone-{{ $tone }} relative overflow-hidden border-b-2 border-night">
        <div class="halftone halftone-fade pointer-events-none absolute inset-0"></div>
        <div class="shell relative py-9 sm:py-12">
            <nav class="eyebrow flex-wrap" aria-label="You are here">
                <a class="hover:underline" href="{{ route('home') }}">Home</a>
                <span aria-hidden="true">/</span>
                @if ($category)
                    <a class="hover:underline" href="{{ route('browse') }}">Categories</a>
                @else
                    <span>{{ $type ? 'Shelves' : 'Browse' }}</span>
                @endif
            </nav>

            <h1 class="mt-2 text-5xl font-black sm:text-6xl">{{ $heading }}</h1>
            <p class="mt-3 max-w-2xl text-lg font-medium">{{ $intro }}</p>
            <p class="mt-4 font-bold">
                {{ $entries->total() }} {{ Str::plural('title', $entries->total()) }}
                @if ($search !== '')
                    for “{{ $search }}”
                @endif
            </p>
        </div>
    </section>

    <div class="shell tone-{{ $tone }} mt-8">
        {{-- Search, filter and sort. Changing a drop-down sends the form straight away. --}}
        <form method="GET" action="{{ url()->current() }}" class="card grid gap-3 p-4 sm:grid-cols-2 lg:flex lg:items-end" x-data>
            <div class="sm:col-span-2 lg:flex-1">
                <label class="label" for="filter-q">Search</label>
                <div class="relative">
                    <x-icon name="search" size="h-4.5 w-4.5" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-soft"/>
                    <input id="filter-q" class="input pl-9" type="search" name="q" value="{{ $search }}" maxlength="80"
                           placeholder="{{ $type ? 'Search '.strtolower($type->shelf()).'…' : 'Title, author, category…' }}">
                </div>
            </div>

            @unless ($type)
                <div>
                    <label class="label" for="filter-type">Type</label>
                    <select id="filter-type" class="input" name="type" @change="$el.form.requestSubmit()">
                        <option value="">Everything</option>
                        @foreach (EntryType::cases() as $option)
                            <option value="{{ $option->value }}" @selected($chosenType === $option)>{{ $option->shelf() }}</option>
                        @endforeach
                    </select>
                </div>
            @endunless

            @unless ($category)
                <div>
                    <label class="label" for="filter-category">Category</label>
                    <select id="filter-category" class="input" name="category" @change="$el.form.requestSubmit()">
                        <option value="">All categories</option>
                        @foreach ($categories as $option)
                            <option value="{{ $option->slug }}" @selected($chosenCategory?->is($option))>{{ $option->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endunless

            <div>
                <label class="label" for="filter-sort">Sort by</label>
                <select id="filter-sort" class="input" name="sort" @change="$el.form.requestSubmit()">
                    @foreach (Entry::SORTS as $value => $label)
                        <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2 sm:col-span-2">
                <button type="submit" class="btn btn-tone">Search</button>
                @if ($filtered)
                    <a href="{{ url()->current() }}" class="btn btn-plain">Clear</a>
                @endif
            </div>
        </form>

        @if ($entries->isEmpty())
            <div class="mx-auto mt-14 max-w-md text-center">
                <x-pip mood="1" class="mx-auto h-32 w-32"/>
                <h2 class="mt-4 text-3xl font-black">Nothing on this shelf</h2>
                <p class="mt-2 text-soft">
                    @if ($filtered)
                        Nothing matches that. Try fewer words, or clear the filters.
                    @else
                        Nobody has added anything here yet. You could be the first.
                    @endif
                </p>
                <p class="mt-5 flex justify-center gap-3">
                    @if ($filtered)
                        <a href="{{ url()->current() }}" class="btn btn-sun">Clear the filters</a>
                    @endif
                    <a href="{{ route('entries.create', $type ? ['type' => $type->value] : []) }}" class="btn btn-primary"><x-icon name="upload" size="h-4 w-4"/> Upload</a>
                </p>
            </div>
        @else
            <div class="mt-9 grid gap-x-5 gap-y-9 {{ $grid }}">
                @foreach ($entries as $entry)
                    <x-entry-card :entry="$entry" :stage="! $chosenType" :show-type="! $chosenType"/>
                @endforeach
            </div>

            {{ $entries->links() }}
        @endif
    </div>
</x-layout>
