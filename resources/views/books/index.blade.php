<x-app-layout>
    {{--  MY BOOKS PAGE --}}
    <x-slot name="header">

    </x-slot>
    <h2 class="font-semibold text-xl leading-tight inline-flex mx-2 text-black">
        {{ __('My Books') }}
    </h2>
    <br/>
    @if(Auth::check())
        <ul class="sm:block md:inline-flex lg:inline-flex flex-wrap align-content-center">


            <li class="underline py-2 px-1 text-2xl sm:text-2xl italic lg:text-3xl xl:text-3xl">
                <a href="{{ route('books.create') }}" class="text-green-900 text-center hover:bg-purple-500">Upload
                    Book</a>
            </li>
        </ul>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        @foreach($books as $book)
            @include('books._cards')
        @endforeach
    </div>

</x-app-layout>
