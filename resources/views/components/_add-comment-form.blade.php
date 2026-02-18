@auth
    {{--                        auth directive, only show post comment form if you are signed in--}}
    <x-panel>
        <form method="POST" action="#">
            @csrf

            <header class="flex items-center">
                <img src="https://i.pravatar.cc/60?u={{ auth()->id() }}" alt="" width="40" height="40"
                     class="rounded-full">
                <h2 class="ml-4">Want to participate?</h2>
            </header>

            <div>
                <textarea name="body"
                          class="w-full text-sm focus:outline-none focus:ring"
                          rows="5"
                          placeholder="Write here"
                          required></textarea>
                @error('body')
                <span class="text-xs text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex justify-end mt-6 pt-6 border-t border-gray-200">
                <x-form.button>Submit</x-form.button>
            </div>

        </form>
    </x-panel>
@else
    <p class="font-semibold">
        <a href="/register" class="hover:underline">Register</a> or
        <a href="/login" class="hover:underline">Log in </a>to leave a comment.
    </p>
@endauth
