{{-- For errors where the database or the session may be the thing that broke: no header, no queries. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $heading }} · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <script src="{{ asset('theme.js') }}"></script>
    @vite(['resources/css/app.css'])
</head>
<body class="grid min-h-screen place-items-center p-6 text-center">
    <div>
        <div class="relative mx-auto h-40 w-40">
            <x-pip :mood="$mood" class="h-40 w-40"/>
            <span class="pow absolute -top-3 -right-10 rotate-12 rounded-xl border-2 border-night bg-sun px-3 py-1 text-4xl text-night shadow-pop">{{ $code }}</span>
        </div>
        <h1 class="mt-8 text-4xl font-black sm:text-5xl">{{ $heading }}</h1>
        <p class="mx-auto mt-4 max-w-xl text-lg text-soft">{{ $text }}</p>
        <p class="mt-8"><a href="{{ url('/') }}" class="btn btn-lg btn-primary">Try the front page</a></p>
    </div>
</body>
</html>
