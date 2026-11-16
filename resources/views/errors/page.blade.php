{{-- Shared by the error pages. Needs $code, $heading, $text and $mood. --}}
<x-layout :title="$heading">
    <div class="shell py-14 text-center sm:py-20">
        <div class="relative mx-auto h-44 w-44">
            <x-pip :mood="$mood" class="animate-bob h-44 w-44"/>
            <span class="pow absolute -top-3 -right-10 rotate-12 rounded-xl border-2 border-night bg-sun px-3 py-1 text-4xl text-night shadow-pop">{{ $code }}</span>
        </div>

        <h1 class="mx-auto mt-8 max-w-2xl text-4xl font-black sm:text-5xl">{{ $heading }}</h1>
        <p class="mx-auto mt-4 max-w-xl text-lg text-soft">{{ $text }}</p>

        <p class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('home') }}" class="btn btn-lg btn-primary">Back to the shop</a>
            <a href="{{ route('browse') }}" class="btn btn-lg">Browse everything</a>
        </p>
    </div>
</x-layout>
