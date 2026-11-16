<x-admin :title="'Edit '.$user->name">
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="card max-w-2xl space-y-5 p-5 sm:p-7">
        @csrf
        @method('PUT')

        <div>
            <label class="label" for="name">Name</label>
            <input id="name" class="input" type="text" name="name" value="{{ old('name', $user->name) }}" maxlength="60" required>
            <x-error for="name"/>
        </div>

        <div>
            <label class="label" for="email">Email</label>
            <input id="email" class="input" type="email" name="email" value="{{ old('email', $user->email) }}" required>
            <x-error for="email"/>
        </div>

        {{-- Only the owner sees this: the Admin role is theirs to give. --}}
        @can('changeRole', $user)
            @unless ($user->isOwner())
                <div>
                    <label class="label" for="role">Role</label>
                    <select id="role" class="input max-w-xs" name="role">
                        <option value="member" @selected(! $user->isAdmin())>Member</option>
                        <option value="admin" @selected($user->isAdmin())>Admin</option>
                    </select>
                    <p class="hint">Admins can manage the catalogue, categories, members, reviews and comments.</p>
                    <x-error for="role"/>
                </div>
            @endunless
        @endcan

        <fieldset class="rounded-xl border-2 border-dashed border-line p-4">
            <legend class="px-2 text-sm font-bold">Set a new password (optional)</legend>
            <p class="hint mt-0 mb-3">Leave both boxes empty to keep the password they have now.</p>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="password">New password</label>
                    <input id="password" class="input" type="password" name="password" minlength="8" autocomplete="new-password">
                </div>
                <div>
                    <label class="label" for="password_confirmation">And again</label>
                    <input id="password_confirmation" class="input" type="password" name="password_confirmation" minlength="8" autocomplete="new-password">
                </div>
            </div>
            <x-error for="password"/>
        </fieldset>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="btn btn-primary"><x-icon name="check" size="h-4.5 w-4.5"/> Save</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-plain">Cancel</a>
        </div>
    </form>
</x-admin>
