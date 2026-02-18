<x-app-layout>
    <x-slot name="header">

    </x-slot>

    {{--    @if (Route::has('login'))--}}
    {{--        <div class="hidden fixed top-0 right-0 px-6 py-4 sm:block">--}}
    {{--            @auth--}}
    {{--                --}}{{--                                <a href="{{ url('/dashboard') }}" class="text-sm underline text-white">Dashboard</a>--}}
    {{--            @else--}}
    {{--                <a href="{{ route('login') }}" class="text-sm text-white underline">Log in</a>--}}

    {{--                @if (Route::has('register'))--}}
    {{--                    <a href="{{ route('register') }}" class="ml-4 text-sm text-white underline">Register</a>--}}
    {{--                @endif--}}
    {{--            @endauth--}}
    {{--        </div>--}}
    {{--    @endif--}}

    <!-- Search Input -->

    <form class="flex items-center py-2" method="GET" action="#">
        @if (request('category'))
            <input type="hidden" name="category" value="{{ request('category') }}">
        @endif
        <label for="simple-search" class="sr-only">Search</label>
        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor"
                     viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                          d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                          clip-rule="evenodd"></path>
                </svg>
            </div>
            <input type="text"
                   id="simple-search"
                   name="search"
                   class="bg-gray-50 border border-gray-300 text-gray-900
                           text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500
                            block w-full pl-10 p-2.5  dark:bg-gray-700 dark:border-gray-600
                             dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500
                              dark:focus:border-blue-500"
                   placeholder="Search for a book by title or description"
                   value="{{ request('search') }}"
                   required>
        </div>
        <button type="submit"
                class="p-2.5 ml-2 text-sm font-medium text-white bg-blue-700 rounded-lg border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                 xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <span class="sr-only">Search</span>
        </button>
    </form>

    <h5 class="italic text-3xl text-center my-3">Browse by Category</h5>


    {{--            Browse by categories navigation --}}
    <nav class="flex bg-white py-4 rounded text-center mx-auto">
        <ul class="inline-flex flex-wrap mx-auto ">
            @foreach ($categories as $category)

                <li class="flex mx-1.5 text-xl italic">
                    <a class="text-green-900 hover:bg-purple-500"
                       href="{{ route('categories.show', $category) }}">
                        {{ $category->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">


        @foreach($books as $book)
            @include('books._cards')
        @endforeach

        {{--  main div--}}
    </div>

    {{ $books->withQueryString()->links() }}

</x-app-layout>
