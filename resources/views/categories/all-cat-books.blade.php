<x-app-layout>
    <x-slot name="header">

    </x-slot>

    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Category:  ' . $category->name) }}
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach($category->books as $book)

            @include('books._cards')
        @endforeach
{{--                        {{ $category->links() }}--}}
    </div>

    {{--    @foreach($podcasts as $podcast)--}}

{{--    @include('podcasts.index')--}}

    {{--    @endforeach--}}

</x-app-layout>
