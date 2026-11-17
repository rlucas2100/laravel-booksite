@php
    use App\Enums\EntryType;

    $total = $counts->sum();
    // Where each cover of the hero's fan sits: left edge, top, tilt.
    $fan = [['2%', '14%', -10], ['25%', '4%', -3], ['48%', '9%', 5], ['69%', '20%', 12]];
@endphp
<x-layout>
    {{-- ---------------------------------------------------------------- hero --}}
    <section class="relative overflow-hidden border-b-2 border-night bg-sun text-night">
        <div class="halftone halftone-fade pointer-events-none absolute inset-0"></div>

        <div class="shell relative grid items-center gap-8 py-12 lg:grid-cols-[1.05fr_.95fr] lg:py-16">
            <div>
                <p class="chip border-night bg-cream text-night">{{ $total }} titles · free and paid · all yours to review</p>

                <h1 class="mt-5 text-5xl leading-[.95] font-black sm:text-6xl xl:text-7xl">
                    Stories you can’t
                    <span class="relative inline-block whitespace-nowrap">
                        put down.
                        <svg class="absolute -bottom-3 left-0 w-full" viewBox="0 0 300 16" preserveAspectRatio="none" aria-hidden="true">
                            <path d="M3 10c40-9 60 7 98-1s58-8 98 0 60 3 98-3" fill="none" stroke="#ff5c39" stroke-width="6" stroke-linecap="round"/>
                        </svg>
                    </span>
                </h1>

                <p class="mt-7 max-w-xl text-lg font-medium sm:text-xl">
                    Books to buy or download free, video to watch, audio to play and a blog to argue with.
                    Rate anything from <strong>0 to 5 stars</strong>, write a review and join the conversation.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('shelf', 'books') }}" class="btn btn-lg btn-primary border-night">Browse the books <x-icon name="arrow-right"/></a>
                    @auth
                        <a href="{{ route('entries.create') }}" class="btn btn-lg border-night bg-cream text-night"><x-icon name="upload"/> Upload yours</a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-lg border-night bg-cream text-night">Join free</a>
                    @endauth
                </div>
            </div>

            {{-- A fan of featured covers, with Pip keeping an eye on them --}}
            <div class="relative mx-auto h-[300px] w-full max-w-[520px] sm:h-[380px]">
                @foreach ($featured as $i => $book)
                    <a href="{{ $book->url() }}" class="absolute block w-[30%] transition-transform duration-150 hover:z-20 hover:-translate-y-3"
                       style="left: {{ $fan[$i][0] }}; top: {{ $fan[$i][1] }}; rotate: {{ $fan[$i][2] }}deg; z-index: {{ $i + 1 }}">
                        <x-cover :entry="$book" class="border-night shadow-[6px_6px_0_#17151c]"/>
                    </a>
                @endforeach

                <div class="absolute -top-2 right-0 z-30 grid h-24 w-24 rotate-12 place-items-center sm:h-28 sm:w-28">
                    <svg viewBox="0 0 100 100" class="absolute inset-0 h-full w-full" aria-hidden="true">
                        <polygon points="50,2 60,14 74,7 77,22 92,22 88,37 100,46 89,56 96,70 81,74 80,89 65,86 55,98 45,87 31,94 26,80 11,81 14,66 1,57 12,46 4,32 19,27 20,12 35,14" fill="#ff5c39" stroke="#17151c" stroke-width="3" stroke-linejoin="round"/>
                    </svg>
                    <span class="pow relative text-2xl sm:text-3xl">New!</span>
                </div>

                <x-pip class="animate-bob absolute -bottom-6 -left-3 z-30 h-28 w-28 sm:-left-8 sm:h-36 sm:w-36"/>
            </div>
        </div>
    </section>

    {{-- ---------------------------------------------------------------- the four shelves --}}
    <section class="shell -mt-6 relative z-10" aria-label="The shelves">
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            @foreach (EntryType::cases() as $type)
                <a href="{{ route('shelf', $type->segment()) }}" class="card bg-tone tone-{{ $type->colour() }} group flex flex-col gap-1 border-night p-4 transition-transform hover:-translate-y-1 sm:p-5">
                    <span class="flex items-center justify-between">
                        <span class="grid h-11 w-11 place-items-center rounded-xl border-2 border-night bg-cream"><x-icon :name="$type->icon()" size="h-6 w-6"/></span>
                        <span class="pow text-3xl">{{ $counts[$type->value] ?? 0 }}</span>
                    </span>
                    <span class="font-display mt-2 text-2xl font-black">{{ $type->shelf() }}</span>
                    <span class="hidden text-sm font-medium sm:block">{{ $type->intro() }}</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ---------------------------------------------------------------- new books --}}
    <section class="shell mt-16">
        <div class="mb-6 flex items-end justify-between gap-4">
            <div>
                <p class="eyebrow text-soft"><span class="dot-tone tone-tomato"></span> Books</p>
                <h2 class="text-3xl font-black sm:text-4xl">Fresh on the shelves</h2>
            </div>
            <a href="{{ route('shelf', 'books') }}" class="btn btn-sm shrink-0">All books <x-icon name="arrow-right" size="h-4 w-4"/></a>
        </div>

        <div class="grid grid-cols-2 gap-x-5 gap-y-8 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ($newBooks as $entry)
                <x-entry-card :entry="$entry"/>
            @endforeach
        </div>
    </section>

    {{-- ---------------------------------------------------------------- top rated, as a chart --}}
    @if ($favourites->isNotEmpty())
        <section class="mt-20 border-y-2 border-line bg-sunk py-14">
            <div class="shell">
                <div class="mb-7 flex items-end justify-between gap-4">
                    <div>
                        <p class="eyebrow text-soft"><x-icon name="star" size="h-4 w-4"/> Rated by readers</p>
                        <h2 class="text-3xl font-black sm:text-4xl">The readers’ top six</h2>
                    </div>
                    <a href="{{ route('browse', ['sort' => 'top']) }}" class="btn btn-sm shrink-0">The full chart <x-icon name="arrow-right" size="h-4 w-4"/></a>
                </div>

                <ol class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($favourites as $i => $entry)
                        <li>
                            <a href="{{ $entry->url() }}" class="card group flex h-full items-center gap-4 p-3 pr-4 transition-transform hover:-translate-y-1">
                                <span class="pow w-9 shrink-0 text-center text-5xl {{ $i === 0 ? 'text-tomato' : '' }}">{{ $i + 1 }}</span>
                                <x-cover :entry="$entry" class="w-16 shrink-0 shadow-pop-sm {{ $entry->type->coverShape() === 'wide' ? 'w-24' : '' }}"/>
                                <span class="min-w-0">
                                    <span class="eyebrow tone-{{ $entry->type->colour() }} text-soft"><span class="dot-tone"></span>{{ $entry->type->label() }}</span>
                                    <span class="font-display block truncate text-lg leading-snug font-bold group-hover:underline">{{ $entry->title }}</span>
                                    <span class="mt-0.5 flex items-center gap-2 text-sm">
                                        <x-stars :value="$entry->rating()"/>
                                        <b>{{ number_format($entry->rating(), 1) }}</b>
                                        <span class="text-soft">({{ $entry->reviews_count }})</span>
                                    </span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    {{-- ---------------------------------------------------------------- audio and video --}}
    @php
        $both = $audio->isNotEmpty() && $videos->isNotEmpty();
    @endphp
    <div @class(['shell mt-16 grid gap-14', 'lg:grid-cols-2' => $both])>
        @if ($audio->isNotEmpty())
            <section>
                <div class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <p class="eyebrow text-soft"><span class="dot-tone tone-mint"></span> Audio</p>
                        <h2 class="text-3xl font-black sm:text-4xl">Press play</h2>
                    </div>
                    <a href="{{ route('shelf', 'audio') }}" class="btn btn-sm shrink-0">All audio <x-icon name="arrow-right" size="h-4 w-4"/></a>
                </div>
                <div @class(['grid grid-cols-2 gap-x-5 gap-y-8', 'sm:grid-cols-4' => ! $both])>
                    @foreach ($audio as $entry)
                        <x-entry-card :entry="$entry"/>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($videos->isNotEmpty())
            <section>
                <div class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <p class="eyebrow text-soft"><span class="dot-tone tone-cobalt"></span> Video</p>
                        <h2 class="text-3xl font-black sm:text-4xl">Now showing</h2>
                    </div>
                    <a href="{{ route('shelf', 'videos') }}" class="btn btn-sm shrink-0">All video <x-icon name="arrow-right" size="h-4 w-4"/></a>
                </div>
                <div @class(['grid gap-x-5 gap-y-8 sm:grid-cols-2', 'lg:grid-cols-3' => ! $both])>
                    @foreach ($videos as $entry)
                        <x-entry-card :entry="$entry" class="{{ $both && $loop->first ? 'sm:col-span-2' : '' }}"/>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    {{-- ---------------------------------------------------------------- the blog --}}
    @if ($articles->isNotEmpty())
        <section class="shell mt-20">
            <div class="mb-6 flex items-end justify-between gap-4">
                <div>
                    <p class="eyebrow text-soft"><span class="dot-tone tone-grape"></span> Blog</p>
                    <h2 class="text-3xl font-black sm:text-4xl">From the desk</h2>
                </div>
                <a href="{{ route('shelf', 'blog') }}" class="btn btn-sm shrink-0">All posts <x-icon name="arrow-right" size="h-4 w-4"/></a>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($articles as $entry)
                    <a href="{{ $entry->url() }}" class="card group flex flex-col overflow-hidden transition-transform hover:-translate-y-1">
                        <x-cover :entry="$entry" class="rounded-none border-0 border-b-2 shadow-none"/>
                        <span class="flex flex-1 flex-col p-5">
                            <span class="font-display text-xl leading-snug font-bold group-hover:underline">{{ $entry->title }}</span>
                            <span class="mt-2 text-soft">{{ $entry->description }}</span>
                            <span class="mt-auto flex items-center gap-3 pt-4 text-sm font-bold">
                                <span class="inline-flex items-center gap-1"><x-icon name="clock" size="h-4 w-4"/> {{ $entry->readingMinutes() }} min read</span>
                                @if ($entry->comments_count)
                                    <span class="inline-flex items-center gap-1"><x-icon name="chat" size="h-4 w-4"/> {{ $entry->comments_count }}</span>
                                @endif
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ---------------------------------------------------------------- categories --}}
    <section class="shell mt-20">
        <h2 class="text-3xl font-black sm:text-4xl">Browse by category</h2>
        <ul class="mt-6 flex flex-wrap gap-3">
            @foreach ($categories as $i => $category)
                @continue($category->entries_count === 0)
                <li>
                    <a href="{{ route('categories.show', $category) }}" class="btn tone-{{ ['sun', 'mint', 'tomato', 'cobalt', 'grape'][$i % 5] }} hover:bg-(--tone) hover:text-night" title="{{ $category->blurb }}">
                        {{ $category->name }} <span class="chip border-night bg-night text-cream">{{ $category->entries_count }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- ---------------------------------------------------------------- join in --}}
    <section class="shell mt-20">
        <div class="card relative overflow-hidden border-night bg-mint p-7 text-night sm:p-10">
            <div class="halftone halftone-fade pointer-events-none absolute inset-0"></div>
            <div class="relative flex flex-col items-start gap-6 sm:flex-row sm:items-center">
                <x-pip mood="5" class="h-28 w-28 shrink-0"/>
                <div class="flex-1">
                    <h2 class="text-3xl font-black sm:text-4xl">Got something worth sharing?</h2>
                    <p class="mt-2 max-w-2xl text-lg font-medium">Put a book, a video, a recording or a blog post on the shelves. It takes a minute, and if you have no cover we’ll draw one for you.</p>
                </div>
                <a href="{{ auth()->check() ? route('entries.create') : route('register') }}" class="btn btn-lg border-night bg-night text-cream">
                    {{ auth()->check() ? 'Upload now' : 'Join and upload' }} <x-icon name="arrow-right"/>
                </a>
            </div>
        </div>
    </section>
</x-layout>
