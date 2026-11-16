@php
    use App\Models\Review;

    $star = 'M12 2.8l2.83 5.74 6.34.92-4.59 4.47 1.08 6.31L12 17.26l-5.66 2.98 1.08-6.31L2.83 9.46l6.34-.92L12 2.8z';
    $most = max(1, max($spread));

    // What the star picker starts on: the last thing typed, or the review already written, or nothing.
    $chosen = old('rating', $myReview?->rating);
    $chosen = is_numeric($chosen) ? max(Review::MIN, min(Review::MAX, (int) $chosen)) : null;
@endphp
<section id="reviews" class="shell mt-16">
    <h2 class="flex items-center gap-3 text-3xl font-black sm:text-4xl">
        Reviews <span class="chip chip-tone text-sm">{{ $reviews->count() }}</span>
    </h2>

    <div class="mt-6 grid gap-8 lg:grid-cols-[22rem_1fr]">
        <div class="space-y-6">
            {{-- The verdict so far --}}
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <p class="font-display text-6xl leading-none font-black">{{ $entry->rating() !== null ? number_format($entry->rating(), 1) : '–' }}</p>
                    <div>
                        <x-stars :value="$entry->rating()" size="1.5rem"/>
                        <p class="mt-1 text-sm text-soft">
                            @if ($reviews->isEmpty())
                                Nobody has rated this yet
                            @else
                                out of 5, from {{ $reviews->count() }} {{ Str::plural('review', $reviews->count()) }}
                            @endif
                        </p>
                    </div>
                </div>

                <ul class="mt-5 space-y-1.5" aria-label="How many reviews gave each score">
                    @foreach ($spread as $stars => $count)
                        <li class="flex items-center gap-2 text-sm font-bold">
                            <span class="flex w-9 shrink-0 items-center justify-end gap-0.5 tabular-nums">{{ $stars }} <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 fill-sun stroke-night" stroke-width="2" stroke-linejoin="round"><path d="{{ $star }}"/></svg></span>
                            <span class="h-3.5 flex-1 overflow-hidden rounded-full border-2 border-line bg-sunk">
                                <span class="block h-full {{ $stars === 0 ? 'bg-tomato' : 'bg-sun' }}" style="width: {{ $count / $most * 100 }}%"></span>
                            </span>
                            <span class="w-5 shrink-0 text-right text-soft tabular-nums">{{ $count }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Write (or change) your review --}}
            <div class="card p-5" id="review-form">
                @guest
                    <div class="flex items-center gap-4">
                        <x-pip class="h-20 w-20 shrink-0"/>
                        <div>
                            <h3 class="text-xl font-bold">What did you think?</h3>
                            <p class="mt-1 text-sm text-soft">Sign in to give this 0 to 5 stars.</p>
                            <p class="mt-3 flex flex-wrap gap-2">
                                <a class="btn btn-sm btn-primary" href="{{ route('login') }}">Sign in</a>
                                <a class="btn btn-sm" href="{{ route('register') }}">Join free</a>
                            </p>
                        </div>
                    </div>
                @else
                    @can('create', [Review::class, $entry])
                        <form method="POST" action="{{ route('reviews.store', $entry) }}" x-data="starPicker(@js($chosen), @js(Review::WORDS))">
                            @csrf
                            <h3 class="text-xl font-bold">{{ $myReview ? 'Change your review' : 'What did you think?' }}</h3>

                            <fieldset class="mt-3">
                                <legend class="sr-only">Your score, from 0 to 5 stars</legend>

                                <div class="flex items-center gap-2.5">
                                    {{-- Pip's face follows the score --}}
                                    <x-pip class="h-[4.5rem] w-[4.5rem] shrink-0" x-bind:data-mood="shown === null ? 'none' : String(shown)"/>

                                    <div>
                                        <div class="flex items-center gap-0.5" @mouseleave="hover = null">
                                            <label class="zero-btn mr-1" :class="{ 'is-lit': shown === 0 }" @mouseenter="hover = 0" title="0 stars: {{ Review::WORDS[0] }}">
                                                <input type="radio" name="rating" value="0" class="sr-only" x-model.number="value" @change="pick(0)">
                                                <span aria-hidden="true">0</span><span class="sr-only">0 stars</span>
                                            </label>
                                            @for ($stars = 1; $stars <= Review::MAX; $stars++)
                                                <label class="star-btn" :class="{ 'is-lit': lit({{ $stars }}) }" @mouseenter="hover = {{ $stars }}" title="{{ $stars }} {{ Str::plural('star', $stars) }}: {{ Review::WORDS[$stars] }}">
                                                    <input type="radio" name="rating" value="{{ $stars }}" class="sr-only" x-model.number="value" @change="pick({{ $stars }})">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $star }}"/></svg>
                                                    <span class="sr-only">{{ $stars }} {{ Str::plural('star', $stars) }}</span>
                                                </label>
                                            @endfor
                                        </div>
                                        <p class="pow mt-1.5 min-h-6 origin-left text-xl" :class="{ 'animate-pop': popping, 'text-tomato': shown === 0 }" x-text="word" aria-live="polite">Tap a star (or the zero)</p>
                                    </div>
                                </div>
                                <x-error for="rating"/>
                            </fieldset>

                            <div class="mt-4">
                                <label class="label" for="review-title">Headline <span class="font-normal text-soft">(optional)</span></label>
                                <input id="review-title" class="input" type="text" name="title" maxlength="100" value="{{ old('title', $myReview?->title) }}" placeholder="Sum it up in a line">
                                <x-error for="title"/>
                            </div>

                            <div class="mt-4" x-data="counted(3000)">
                                <label class="label" for="review-body">Your review <span class="font-normal text-soft">(optional)</span></label>
                                <textarea id="review-body" class="input" name="body" rows="4" maxlength="3000" x-ref="text" @input="count" placeholder="What worked? What didn’t? No spoilers without a warning.">{{ old('body', $myReview?->body) }}</textarea>
                                <p class="hint text-right tabular-nums"><span x-text="used">0</span> / 3000</p>
                                <x-error for="body"/>
                            </div>

                            <button type="submit" class="btn btn-tone mt-2 w-full">{{ $myReview ? 'Update my review' : 'Post my review' }}</button>
                        </form>

                        @if ($myReview)
                            <form method="POST" action="{{ route('reviews.destroy', $myReview) }}" class="mt-3 text-center" data-confirm="Delete your review?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="cursor-pointer text-sm font-bold text-soft underline underline-offset-2 hover:text-ink">Delete my review</button>
                            </form>
                        @endif
                    @else
                        <div class="flex items-center gap-4">
                            <x-pip class="h-16 w-16 shrink-0"/>
                            <p class="text-sm text-soft">This one is your own upload, so the reviewing is up to everyone else.</p>
                        </div>
                    @endcan
                @endguest
            </div>
        </div>

        {{-- What readers said --}}
        <div>
            @forelse ($reviews as $review)
                <article id="review-{{ $review->id }}" class="card mb-5 p-5">
                    <header class="flex flex-wrap items-start gap-3">
                        <x-avatar :user="$review->user"/>
                        <div class="min-w-0 flex-1">
                            <p class="flex flex-wrap items-center gap-2 font-bold">
                                {{ $review->user->name }}
                                @if ($review->isSandboxed())
                                    <span class="chip bg-sun text-night" title="This is the public demo: only your browser shows this review.">Only you can see this</span>
                                @endif
                            </p>
                            <p class="text-sm text-soft">
                                <time datetime="{{ $review->created_at->toIso8601String() }}">{{ $review->created_at->diffForHumans() }}</time>
                                @if ($review->updated_at->gt($review->created_at->copy()->addMinute())) · edited @endif
                            </p>
                        </div>
                        <div class="text-right">
                            <x-stars :value="$review->rating" size="1.2rem"/>
                            <p class="pow mt-1 text-base {{ $review->rating === 0 ? 'text-tomato' : '' }}">{{ $review->words() }}</p>
                        </div>
                    </header>

                    @if ($review->title)
                        <h3 class="mt-3 text-xl leading-snug font-bold">{{ $review->title }}</h3>
                    @endif
                    @if ($review->body)
                        <p class="mt-2 leading-relaxed whitespace-pre-line">{{ $review->body }}</p>
                    @endif

                    @can('delete', $review)
                        @unless ($myReview?->is($review))
                            <form method="POST" action="{{ route('reviews.destroy', $review) }}" class="mt-3" data-confirm="Remove this review?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="h-3.5 w-3.5"/> Remove</button>
                            </form>
                        @endunless
                    @endcan
                </article>
            @empty
                <div class="card flex flex-col items-center gap-3 border-dashed p-8 text-center shadow-none">
                    <x-pip mood="2" class="h-24 w-24"/>
                    <h3 class="text-2xl font-bold">No reviews yet</h3>
                    <p class="max-w-sm text-soft">Be the first to say what you thought. Even a zero is useful to the next reader.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
