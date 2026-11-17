@props(['title' => null, 'description' => null])
{{-- The page frame: <x-layout title="Books"> ... </x-layout> --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}{{ $title ? '' : ': '.config('shop.tagline') }}</title>
    <meta name="description" content="{{ $description ?? 'A bookshop for books, video and audio, with star reviews, comments and a basket. '.config('shop.tagline') }}">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    {{-- Sets dark mode before anything is drawn. A file, not an inline script, so the Content-Security-Policy can stay strict. --}}
    <script src="{{ asset('theme.js') }}"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col">
    <a href="#main" class="skip-link">Skip to the content</a>

    @if (\App\Support\Sandbox::enabled())
        <p class="border-b-2 border-night bg-night px-4 py-1.5 text-center text-xs font-semibold text-cream sm:text-sm">
            <span class="pow mr-1 text-base text-sun">Demo!</span>
            Anything you post or upload here is only visible in your own browser, and is cleared after {{ config('shop.sandbox_hours') }} hours.
        </p>
    @endif

    <x-site.header/>

    <main id="main" class="flex-1">
        {{ $slot }}
    </main>

    <x-site.footer/>

    {{-- A message from the last thing you did ("Your review is up"). --}}
    @if (session('status'))
        <div class="pointer-events-none fixed inset-x-0 bottom-5 z-50 flex justify-center px-4"
             x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition.opacity.duration.300ms>
            <p class="card pointer-events-auto flex max-w-xl items-center gap-3 bg-mint px-4 py-3 font-bold text-night" role="status">
                <x-icon name="check"/>
                <span>{{ session('status') }}</span>
                <button type="button" class="ml-1 rounded-md p-0.5 hover:bg-night/10" @click="show = false" aria-label="Close this message"><x-icon name="close" size="h-4 w-4"/></button>
            </p>
        </div>
    @endif
</body>
</html>
