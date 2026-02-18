<x-app-layout>
<h1 class="font-bold">Test Controller</h1>

{{--{{dd($categories)}}--}}

{{-- @foreach($books as $book)--}}
{{--    <p>{{ $book }} </p>--}}

{{--    @endforeach--}}

{{--    @foreach($categories as $cat)--}}
{{--    <p>{{ $cat }} </p>--}}

{{--    @endforeach--}}



    @foreach($comments as $comment)
    <p>{{ $comment->user_id }} </p>

    @endforeach

</x-app-layout>
