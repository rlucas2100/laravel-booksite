<x-layout title="My uploads">
    <div class="shell py-10">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="eyebrow text-soft">Your shelf</p>
                <h1 class="mt-1 text-4xl font-black sm:text-5xl">My uploads</h1>
            </div>
            <a href="{{ route('entries.create') }}" class="btn btn-primary"><x-icon name="upload" size="h-4.5 w-4.5"/> Upload something</a>
        </div>

        @if ($entries->isEmpty())
            <div class="mx-auto mt-14 max-w-md text-center">
                <x-pip mood="2" class="mx-auto h-32 w-32"/>
                <h2 class="mt-4 text-3xl font-black">Your shelf is empty</h2>
                <p class="mt-2 text-soft">Add a book, a video, a recording or a blog post, and it will show up here.</p>
                <a href="{{ route('entries.create') }}" class="btn btn-lg btn-primary mt-6"><x-icon name="upload"/> Upload your first</a>
            </div>
        @else
            <div class="mt-9 grid grid-cols-2 gap-x-5 gap-y-10 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($entries as $entry)
                    <div>
                        <x-entry-card :entry="$entry" stage show-type/>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a href="{{ route('entries.edit', $entry) }}" class="btn btn-sm btn-sun"><x-icon name="pen" size="h-3.5 w-3.5"/> Edit</a>
                            <form method="POST" action="{{ route('entries.destroy', $entry) }}" data-confirm="Take this off the shelf? Its reviews and comments go with it.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><x-icon name="trash" size="h-3.5 w-3.5"/> Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            {{ $entries->links() }}
        @endif
    </div>
</x-layout>
