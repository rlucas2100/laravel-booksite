@php
    use App\Enums\EntryType;
@endphp
<x-admin title="Catalogue">
    <form method="GET" action="{{ route('admin.entries') }}" class="flex flex-wrap items-end gap-3" x-data>
        <div class="min-w-48 flex-1">
            <label class="label" for="admin-q">Search</label>
            <input id="admin-q" class="input" type="search" name="q" value="{{ $search }}" placeholder="Title, author, category…">
        </div>
        <div>
            <label class="label" for="admin-type">Type</label>
            <select id="admin-type" class="input" name="type" @change="$el.form.requestSubmit()">
                <option value="">Everything</option>
                @foreach (EntryType::cases() as $option)
                    <option value="{{ $option->value }}" @selected($type === $option)>{{ $option->shelf() }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-sun">Search</button>
        <a href="{{ route('entries.create') }}" class="btn btn-primary ml-auto"><x-icon name="plus" size="h-4.5 w-4.5"/> Add an entry</a>
    </form>

    <div class="card mt-6 overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Uploaded by</th>
                    <th>Score</th>
                    <th><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($entries as $entry)
                    <tr class="tone-{{ $entry->type->colour() }}">
                        <td>
                            <a href="{{ $entry->url() }}" class="font-bold hover:underline">{{ $entry->title }}</a>
                            <span class="block text-sm text-soft">{{ $entry->creator }}</span>
                        </td>
                        <td><span class="chip chip-tone">{{ $entry->type->label() }}</span></td>
                        <td>{{ $entry->category->name }}</td>
                        <td>
                            {{ $entry->user->name }}
                            @if ($entry->isSandboxed())
                                <span class="chip bg-sun text-night">yours</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap">
                            @if ($entry->rating() !== null)
                                <b>{{ number_format($entry->rating(), 1) }}</b> <span class="text-soft">({{ $entry->reviews_count }})</span>
                            @else
                                <span class="text-soft">–</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex justify-end gap-2">
                                @can('update', $entry)
                                    <a href="{{ route('entries.edit', $entry) }}" class="btn btn-sm btn-sun">Edit</a>
                                    <form method="POST" action="{{ route('entries.destroy', $entry) }}" data-confirm="Delete “{{ $entry->title }}”? Its reviews and comments go with it.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                @else
                                    <span class="inline-flex items-center gap-1 text-sm text-soft" title="Locked in the public demo"><x-icon name="lock" size="h-4 w-4"/> Locked</span>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-soft">Nothing matches that.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $entries->links() }}
</x-admin>
