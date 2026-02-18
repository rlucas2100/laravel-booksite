<x-app-layout>
    <x-slot name="header">
    </x-slot>

    <h2 class="font-semibold text-xl leading-tight inline-flex mx-2 text-black">
        {{ __('Dashboard') }}
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
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <p>Welcome {{ auth()->user()->name }}, you are logged in.</p>
        </div>
        <div class="p-6 bg-white border-b border-gray-200">
            <a href="{{ route('my-books') }}" class="hover:text-blue-400 underline">View My Books</a>
        </div>
    </div>

</x-app-layout>
