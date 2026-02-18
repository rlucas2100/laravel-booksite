<x-app-layout>
    <x-slot name="header">

    </x-slot>

    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Category: Show page. Cat:  ' . $category->name) }}
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        {{--        returns all--}}
        {{--        @foreach($category->books as $book)     --}}

        @foreach($books as $book)
            @include('books._cards')
        @endforeach
        {{--        {{ $category->links() }}--}}
    </div>
    <a class="underline" href="{{ route('cat-books', $category) }}">Show more books in cat {{ $category->name }}</a>
    {{--    @foreach($podcasts as $podcast)--}}
    @include('podcasts.index')
    {{--    @endforeach--}}

    <a class="underline" href="{{ route('all-podcasts') }}">Show more podcasts </a>


    {{ $books->links() }}

</x-app-layout>
