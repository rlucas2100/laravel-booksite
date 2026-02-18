<div class="mx-4 my-6 p-10 bg-emerald-500 border-b border-gray-200 shadow-sm sm:rounded-lg hover:bg-purple-500">

    <a class="item-center" href="{{ route('books.show', $book) }}">
        <img height="50" width="50" src="{{ asset('storage/'.$book->thumbnail) }}" class="rounded-xl item-center ">
    </a>

    <div class="p-5">
        <a href="{{ route('books.show', $book) }}">
            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white break-words">
                Book Title:
                {{ Str::limit($book->title, 11) }}
            </h5>

        </a>
        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400 break-words">
            Description:
            {{ Str::limit($book->description, 15) }}.</p>

        <p class="mt-2">

            Category:
            {{ $book->category->name }}

            {{--                    {{ $book->category->name }}--}}
        </p>

        <p class="mt-2">
            Author:
            {{ $book->user->name }}
        </p>

        <p class="mt-2">
            @auth
                Pdf:
                <a class="hover:bg-blue-500" href=" {{asset('storage/'.$book->pdf)}}" target="_blank"
                   download="{{ $book->pdf }}">download</a>
            @else
                <a href="{{ route('login') }}" class="underline">Log in to download</a>
            @endauth
        </p>
        <a href="{{ route('books.show', $book) }}"
           class="mt-2 inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
            Read more
            <svg aria-hidden="true" class="w-4 h-4 ml-2 -mr-1" fill="currentColor" viewBox="0 0 20 20"
                 xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                      d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                      clip-rule="evenodd"></path>
            </svg>
        </a>
    </div>

</div>
