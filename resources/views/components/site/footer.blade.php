@php
    use App\Enums\EntryType;
@endphp
<footer class="mt-20 border-t-2 border-night bg-night text-cream">
    <div class="shell grid gap-10 py-12 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1.2fr]">
        <div>
            <p class="font-display text-3xl font-black">{{ config('app.name') }}</p>
            <p class="mt-2 max-w-xs text-cream/75">{{ config('shop.tagline') }} Books, video, audio and a blog, all open to your verdict.</p>
            <x-pip class="mt-5 h-20 w-20"/>
        </div>

        <nav aria-label="Shelves">
            <p class="eyebrow text-sun">The shelves</p>
            <ul class="mt-3 space-y-2">
                @foreach (EntryType::cases() as $type)
                    <li><a class="hover:underline" href="{{ route('shelf', $type->segment()) }}">{{ $type->shelf() }}</a></li>
                @endforeach
                <li><a class="hover:underline" href="{{ route('browse') }}">Browse everything</a></li>
                <li><a class="hover:underline" href="{{ route('basket.show') }}">Your basket</a></li>
            </ul>
        </nav>

        <nav aria-label="Your account">
            <p class="eyebrow text-sun">You</p>
            <ul class="mt-3 space-y-2">
                @auth
                    <li><a class="hover:underline" href="{{ route('entries.create') }}">Upload something</a></li>
                    <li><a class="hover:underline" href="{{ route('library') }}">My library</a></li>
                    <li><a class="hover:underline" href="{{ route('entries.mine') }}">My uploads</a></li>
                    <li><a class="hover:underline" href="{{ route('profile.edit') }}">My account</a></li>
                @else
                    <li><a class="hover:underline" href="{{ route('register') }}">Join free</a></li>
                    <li><a class="hover:underline" href="{{ route('login') }}">Sign in</a></li>
                @endauth
            </ul>
        </nav>

        <div>
            <p class="eyebrow text-sun">About</p>
            <p class="mt-3 text-sm text-cream/75">
                Every book here is in the public domain. Full texts are kept by Project Gutenberg, and the readings come from LibriVox volunteers.
            </p>
            <p class="mt-3 text-sm text-cream/75">Built with Laravel, Blade, Tailwind CSS and Alpine.js.</p>
            <p class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-sm font-bold">
                @if (config('shop.home_url'))
                    <a class="inline-flex items-center gap-1 hover:underline" href="{{ config('shop.home_url') }}"><x-icon name="arrow-left" size="h-4 w-4"/> Back to the portfolio</a>
                @endif
                @if (config('shop.source_url'))
                    <a class="inline-flex items-center gap-1 hover:underline" href="{{ config('shop.source_url') }}" rel="noopener">Source code <x-icon name="external" size="h-4 w-4"/></a>
                @endif
            </p>
        </div>
    </div>
</footer>
