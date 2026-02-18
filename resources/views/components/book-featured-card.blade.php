@props(['books'])
<div class="mt-4 my-6 p-6 bg-white border-b border-gray-200 shadow-sm sm:rounded-lg">
    <img height="50" width="50" src="{{ asset('storage/'.$book->thumbnail) }}" class="rounded-xl">
    <a href="{{ route('books.show', $book) }}">
        <h2 class="font-bold text-2xl">
            Book title:
            {{ $book->title }}
        </h2>
    </a>
    <p class="mt-2">
        Description:
        {{ Str::limit($book->description, 70) }}

    </p>
    {{-- i should use <a href="{{ route('books.show', $book) }}"> --}}{{-- categories/{{ $book->category->name }}--}}
    {{--                                  --}}
    <p class="mt-2">
        <a href="{{route('categories.show', $book->category)}}">Category
            {{ $book->category->name }} </a>
    </p>

    <p class="mt-2">
        Author:
        {{ $book->user->name }}
    </p>

    @auth
        <p class="mt-2">
            Pdf:
            <a href=" {{asset('storage/'.$book->pdf)}}" target="_blank" download="{{ $book->pdf }}">download</a>
        </p>

    @else
        <p class="mt-2">Login to download!</p>
    @endauth


</div>
