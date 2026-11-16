<x-admin title="Members">
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-end gap-3">
        <div class="min-w-48 flex-1">
            <label class="label" for="user-q">Search</label>
            <input id="user-q" class="input" type="search" name="q" value="{{ $search }}" placeholder="Name or email…">
        </div>
        <button type="submit" class="btn btn-sun">Search</button>
        @if ($search !== '')
            <a href="{{ route('admin.users.index') }}" class="btn btn-plain">Clear</a>
        @endif
    </form>

    <div class="card mt-6 overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Role</th>
                    <th>Uploads</th>
                    <th>Reviews</th>
                    <th>Comments</th>
                    <th>Joined</th>
                    <th><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <x-avatar :user="$user" small/>
                                <div class="min-w-0">
                                    <p class="font-bold">
                                        {{ $user->name }}
                                        @if ($user->is(auth()->user())) <span class="chip">you</span> @endif
                                        @if ($user->isSandboxed()) <span class="chip bg-sun text-night">made by you</span> @endif
                                    </p>
                                    <p class="text-sm text-soft">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td><span class="chip {{ $user->isAdmin() ? 'bg-sun text-night' : '' }}">{{ $user->roleLabel() }}</span></td>
                        <td class="tabular-nums">{{ $user->entries_count }}</td>
                        <td class="tabular-nums">{{ $user->reviews_count }}</td>
                        <td class="tabular-nums">{{ $user->comments_count }}</td>
                        <td class="text-sm whitespace-nowrap text-soft">{{ $user->created_at->format('j M Y') }}</td>
                        <td>
                            <div class="flex justify-end gap-2">
                                @can('update', $user)
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-sun">Edit</a>
                                    @can('delete', $user)
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm="Delete {{ $user->name }}? Their uploads, reviews and comments go too.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    @endcan
                                @else
                                    <span class="inline-flex items-center gap-1 text-sm text-soft"><x-icon name="lock" size="h-4 w-4"/> Locked</span>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-soft">Nobody matches that.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
</x-admin>
