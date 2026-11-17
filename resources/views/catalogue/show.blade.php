@php
    use App\Enums\EntryType;

    $type = $entry->type;
    // Free things are open to all; priced ones to whoever bought (or uploaded) them.
    $unlocked = $entry->isFree() || (bool) auth()->user()?->mayOpen($entry);
    $inBasket = \App\Support\Basket::has($entry);
    $isArticle = $type === EntryType::Article;
    $host = $entry->link ? (string) parse_url($entry->link, PHP_URL_HOST) : '';
    $linkLabel = match (true) {
        str_contains($host, 'gutenberg.org') => 'Read it all, free, at Project Gutenberg',
        str_contains($host, 'archive.org'), str_contains($host, 'librivox.org') => 'About this recording',
        default => 'Find out more',
    };
@endphp
<x-layout :title="$entry->title" :description="Str::limit($entry->description, 155)">
<div class="tone-{{ $type->colour() }}">

    <section class="border-b-2 border-line bg-sunk">
        <div class="shell py-6 sm:py-10">
            <nav class="eyebrow mb-6 flex-wrap text-soft" aria-label="You are here">
                <a class="hover:underline" href="{{ route('home') }}">Home</a>
                <span aria-hidden="true">/</span>
                <a class="hover:underline" href="{{ route('shelf', $type->segment()) }}">{{ $type->shelf() }}</a>
                <span aria-hidden="true">/</span>
                <a class="hover:underline" href="{{ route('categories.show', $entry->category) }}">{{ $entry->category->name }}</a>
            </nav>

            @if ($type === EntryType::Video && ! $unlocked)
                <div class="relative mx-auto mb-8 max-w-4xl">
                    <x-cover :entry="$entry" class="shadow-pop-lg"/>
                    <span class="pow absolute inset-x-0 bottom-5 mx-auto w-fit rounded-xl border-2 border-night bg-sun px-4 py-1.5 text-2xl text-night shadow-pop"><x-icon name="lock" class="inline"/> Buy it to watch</span>
                </div>
            @elseif ($type === EntryType::Video)
                <div class="card mx-auto mb-8 max-w-4xl overflow-hidden border-night bg-night shadow-pop-lg">
                    <video class="block aspect-video w-full bg-night" controls playsinline preload="metadata" src="{{ $entry->fileUrl() }}">
                        Your browser can’t play this video. <a href="{{ $entry->fileUrl(true) }}">Download it</a> instead.
                    </video>
                </div>
            @endif

            <div @class([
                'grid gap-8 lg:gap-12',
                'md:grid-cols-[minmax(0,16rem)_1fr]' => $type === EntryType::Book,
                'md:grid-cols-[minmax(0,18rem)_1fr]' => $type === EntryType::Audio,
                'mx-auto max-w-4xl' => $type === EntryType::Video,
                'mx-auto max-w-3xl' => $isArticle,
            ])>
                @if ($type === EntryType::Book || $type === EntryType::Audio)
                    <div class="mx-auto w-full max-w-64 md:max-w-none">
                        <x-cover :entry="$entry" class="shadow-pop-lg {{ $type === EntryType::Book ? '-rotate-2' : '' }}"/>
                    </div>
                @endif

                <div class="min-w-0">
                    <p class="flex flex-wrap items-center gap-2">
                        <span class="chip chip-tone"><x-icon :name="$type->icon()" size="h-3.5 w-3.5"/> {{ $type->label() }}</span>
                        <a class="chip hover:bg-sun hover:text-night" href="{{ route('categories.show', $entry->category) }}">{{ $entry->category->name }}</a>
                        @if ($entry->isFree())
                            <span class="chip bg-mint text-night">Free</span>
                        @elseif ($unlocked)
                            <span class="chip bg-mint text-night"><x-icon name="check" size="h-3.5 w-3.5"/> Yours</span>
                        @endif
                        @if ($entry->isSandboxed())
                            <span class="chip bg-sun text-night" title="This is the public demo: your uploads are only visible in your own browser, for {{ config('shop.sandbox_hours') }} hours.">
                                <x-icon name="lock" size="h-3.5 w-3.5"/> Only you can see this
                            </span>
                        @endif
                    </p>

                    <h1 class="mt-3 text-4xl leading-[1.05] font-black sm:text-5xl">{{ $entry->title }}</h1>

                    <p class="mt-3 text-lg">
                        <span class="text-soft">{{ $type->byline() }}</span>
                        <a class="font-bold underline decoration-2 underline-offset-3 hover:bg-sun hover:text-night" href="{{ route('browse', ['q' => Str::before($entry->creator, ',')]) }}">{{ $entry->creator }}</a>
                        @if ($isArticle)
                            <span class="text-soft">· {{ $entry->published_at->format('j F Y') }} · {{ $entry->readingMinutes() }} min read</span>
                        @elseif ($entry->year)
                            <span class="text-soft">· {{ $entry->year < 1000 ? 'around '.$entry->year.' AD' : $entry->year }}</span>
                        @endif
                    </p>

                    <a href="#reviews" class="mt-4 inline-flex flex-wrap items-center gap-2.5 rounded-lg">
                        <x-stars :value="$entry->rating()" size="1.4rem"/>
                        @if ($entry->rating() !== null)
                            <span class="font-display text-2xl font-black">{{ number_format($entry->rating(), 1) }}</span>
                            <span class="text-soft underline underline-offset-2">{{ $entry->reviews_count }} {{ Str::plural('review', $entry->reviews_count) }}</span>
                        @else
                            <span class="text-soft underline underline-offset-2">No reviews yet. Be the first.</span>
                        @endif
                        <span class="inline-flex items-center gap-1 text-soft"><x-icon name="chat" size="h-4 w-4"/> {{ $entry->comments_count }}</span>
                    </a>

                    <p class="mt-5 max-w-2xl text-lg leading-relaxed {{ $isArticle ? 'font-display text-xl' : '' }}">{{ $entry->description }}</p>

                    @if ($type === EntryType::Audio && $entry->file_path && $unlocked)
                        <div class="mt-6 max-w-xl">
                            @include('catalogue.partials.audio-player')
                        </div>
                    @endif

                    {{-- What you can do with it --}}
                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        @if (! $unlocked)
                            <span class="font-display text-4xl font-black tabular-nums">{{ $entry->priceLabel() }}</span>
                            @if ($inBasket)
                                <a class="btn btn-lg btn-tone" href="{{ route('basket.show') }}"><x-icon name="basket"/> In your basket: go and pay</a>
                            @else
                                <form method="POST" action="{{ route('basket.add', $entry) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-lg btn-tone"><x-icon name="basket"/> Add to basket</button>
                                </form>
                            @endif
                        @elseif ($type === EntryType::Book && $entry->file_path)
                            @auth
                                <a class="btn btn-lg btn-tone" href="{{ $entry->fileUrl(true) }}">
                                    <x-icon name="download"/> Download the PDF
                                    @if ($entry->fileSizeLabel()) <span class="text-sm font-medium opacity-80">{{ $entry->fileSizeLabel() }}</span> @endif
                                </a>
                            @else
                                <a class="btn btn-lg btn-tone" href="{{ $entry->fileUrl(true) }}"><x-icon name="lock"/> Sign in to download</a>
                            @endauth
                        @elseif ($type->hasFile() && $entry->file_path)
                            <a class="btn" href="{{ $entry->fileUrl(true) }}"><x-icon name="download" size="h-4.5 w-4.5"/> Download <span class="text-sm font-medium text-soft">{{ $entry->fileSizeLabel() }}</span></a>
                        @endif

                        @if ($entry->link)
                            <a class="btn" href="{{ $entry->link }}" rel="noopener nofollow">{{ $linkLabel }} <x-icon name="external" size="h-4.5 w-4.5"/></a>
                        @endif

                        @can('update', $entry)
                            <a class="btn btn-sun" href="{{ route('entries.edit', $entry) }}"><x-icon name="pen" size="h-4.5 w-4.5"/> Edit</a>
                            <form method="POST" action="{{ route('entries.destroy', $entry) }}" data-confirm="Take this off the shelf? Its reviews and comments go with it.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"><x-icon name="trash" size="h-4.5 w-4.5"/> Delete</button>
                            </form>
                        @endcan
                    </div>

                    @unless ($isArticle)
                        <dl class="mt-7 flex flex-wrap gap-x-8 gap-y-3 border-t-2 border-line/15 pt-5 text-sm">
                            @if ($entry->durationLabel())
                                <div><dt class="eyebrow text-soft">Length</dt><dd class="mt-0.5 font-bold">{{ $entry->durationLabel() }}</dd></div>
                            @endif
                            @if ($entry->file_path)
                                <div><dt class="eyebrow text-soft">File</dt><dd class="mt-0.5 font-bold">{{ strtoupper($entry->fileExtension()) }}@if ($entry->fileSizeLabel()), {{ $entry->fileSizeLabel() }}@endif</dd></div>
                            @endif
                            <div><dt class="eyebrow text-soft">Added</dt><dd class="mt-0.5 font-bold">{{ $entry->published_at->format('j M Y') }}</dd></div>
                            <div><dt class="eyebrow text-soft">Uploaded by</dt><dd class="mt-0.5 font-bold">{{ $entry->user->name }}</dd></div>
                        </dl>
                    @endunless
                </div>
            </div>
        </div>
    </section>

    {{-- A book's first lines --}}
    @if ($type === EntryType::Book && filled($entry->body))
        <section class="shell mt-12">
            <figure class="card relative mx-auto max-w-3xl p-6 pt-9 sm:p-9 sm:pt-11">
                <figcaption class="pow absolute -top-4 left-5 rounded-lg border-2 border-night bg-sun px-3 py-1 text-xl text-night shadow-pop-sm">How it begins…</figcaption>
                <blockquote class="font-display text-xl leading-relaxed sm:text-2xl">{!! nl2br(e($entry->body)) !!}</blockquote>
            </figure>
        </section>
    @endif

    {{-- An article's text --}}
    @if ($isArticle)
        <article class="shell mt-10">
            <div class="mx-auto max-w-3xl">
                <div class="prose">{!! $entry->bodyHtml() !!}</div>
                <p class="mt-10 flex flex-wrap items-center gap-3 border-t-2 border-line/15 pt-5 text-sm text-soft">
                    <x-avatar :user="$entry->user" small/>
                    Posted by <b class="text-ink">{{ $entry->user->name }}</b> on {{ $entry->published_at->format('j F Y') }}
                </p>
            </div>
        </article>
    @endif

    @include('catalogue.partials.reviews')
    @include('catalogue.partials.comments')

    @if ($related->isNotEmpty())
        <section class="shell mt-20">
            <h2 class="text-3xl font-black">More in {{ $entry->category->name }}</h2>
            <div class="mt-6 grid grid-cols-2 gap-x-5 gap-y-9 sm:grid-cols-4">
                @foreach ($related as $other)
                    <x-entry-card :entry="$other" stage show-type/>
                @endforeach
            </div>
        </section>
    @endif
</div>
</x-layout>
