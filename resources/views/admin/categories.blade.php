<x-admin title="Categories">
    <div class="grid gap-8 lg:grid-cols-[1fr_20rem]">
        <ul class="space-y-3">
            @foreach ($categories as $category)
                <li class="card p-4" x-data="{ editing: {{ $errors->any() && (int) old('editing') === $category->id ? 'true' : 'false' }}, deleting: false }">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="font-bold">
                                <a href="{{ route('categories.show', $category) }}" class="hover:underline">{{ $category->name }}</a>
                                <span class="chip ml-1">{{ $category->entries_count }}</span>
                                @if ($category->isSandboxed()) <span class="chip bg-sun text-night">made by you</span> @endif
                            </p>
                            @if ($category->blurb)
                                <p class="text-sm text-soft">{{ $category->blurb }}</p>
                            @endif
                        </div>

                        @can('update', $category)
                            <button type="button" class="btn btn-sm btn-sun" @click="editing = ! editing; deleting = false">Edit</button>
                            <button type="button" class="btn btn-sm btn-danger" @click="deleting = ! deleting; editing = false">Delete</button>
                        @else
                            <span class="inline-flex items-center gap-1 text-sm text-soft"><x-icon name="lock" size="h-4 w-4"/> Locked</span>
                        @endcan
                    </div>

                    @can('update', $category)
                        <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="mt-4 grid gap-3 border-t-2 border-line/15 pt-4 sm:grid-cols-[1fr_1.5fr_auto] sm:items-end" x-show="editing" x-cloak>
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="editing" value="{{ $category->id }}">
                            <div>
                                <label class="label" for="name-{{ $category->id }}">Name</label>
                                <input id="name-{{ $category->id }}" class="input" type="text" name="name" value="{{ (int) old('editing') === $category->id ? old('name') : $category->name }}" maxlength="40" required>
                            </div>
                            <div>
                                <label class="label" for="blurb-{{ $category->id }}">One-line description</label>
                                <input id="blurb-{{ $category->id }}" class="input" type="text" name="blurb" value="{{ (int) old('editing') === $category->id ? old('blurb') : $category->blurb }}" maxlength="160">
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                            @if ((int) old('editing') === $category->id)
                                <div class="sm:col-span-3"><x-error for="name"/><x-error for="blurb"/></div>
                            @endif
                        </form>

                        {{-- A category can't vanish with entries in it: they are moved to another one first. --}}
                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="mt-4 flex flex-wrap items-end gap-3 border-t-2 border-line/15 pt-4" x-show="deleting" x-cloak>
                            @csrf
                            @method('DELETE')
                            @if ($category->entries_count > 0)
                                <div class="min-w-48 flex-1">
                                    <label class="label" for="move-{{ $category->id }}">Move its {{ $category->entries_count }} {{ Str::plural('entry', $category->entries_count) }} to</label>
                                    <select id="move-{{ $category->id }}" class="input" name="move_to" required>
                                        <option value="" disabled selected>Choose a category…</option>
                                        @foreach ($categories as $other)
                                            @continue($other->is($category))
                                            <option value="{{ $other->id }}">{{ $other->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <p class="flex-1 text-sm text-soft">This category is empty, so it can simply be deleted.</p>
                            @endif
                            <button type="submit" class="btn btn-danger"><x-icon name="trash" size="h-4.5 w-4.5"/> Delete “{{ $category->name }}”</button>
                        </form>
                    @endcan
                </li>
            @endforeach
        </ul>

        @can('create', \App\Models\Category::class)
            <form method="POST" action="{{ route('admin.categories.store') }}" class="card h-fit space-y-4 bg-sunk p-5">
                @csrf
                <h2 class="text-2xl font-black">Add a category</h2>
                <div>
                    <label class="label" for="new-name">Name</label>
                    <input id="new-name" class="input" type="text" name="name" value="{{ old('editing') ? '' : old('name') }}" maxlength="40" required>
                    @unless (old('editing')) <x-error for="name"/> @endunless
                </div>
                <div>
                    <label class="label" for="new-blurb">One-line description <span class="font-normal text-soft">(optional)</span></label>
                    <input id="new-blurb" class="input" type="text" name="blurb" value="{{ old('editing') ? '' : old('blurb') }}" maxlength="160">
                    @unless (old('editing')) <x-error for="blurb"/> @endunless
                </div>
                <button type="submit" class="btn btn-primary"><x-icon name="plus" size="h-4.5 w-4.5"/> Add category</button>
                <x-error for="move_to"/>
            </form>
        @endcan
    </div>
</x-admin>
