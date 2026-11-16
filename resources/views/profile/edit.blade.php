<x-layout title="My account">
    <div class="shell max-w-3xl py-10">
        <div class="flex items-center gap-4">
            <x-avatar :user="$user" class="h-16 w-16 text-xl"/>
            <div>
                <p class="eyebrow text-soft">{{ $user->roleLabel() }}</p>
                <h1 class="text-4xl font-black sm:text-5xl">My account</h1>
            </div>
        </div>

        @if ($user->protected)
            <p class="card mt-6 flex items-start gap-3 bg-sun p-4 text-night">
                <x-icon name="lock" class="mt-0.5"/>
                <span>This is a shared demo account, so its name, email and password are locked.
                    <a class="font-bold underline" href="{{ route('register') }}">Create your own free account</a> to try these settings (sign out first).</span>
            </p>
        @endif

        <fieldset class="mt-8 space-y-8" @disabled($user->protected)>
            <legend class="sr-only">Account settings</legend>

            <form method="POST" action="{{ route('profile.update') }}" class="card space-y-4 p-5 sm:p-7">
                @csrf
                @method('PATCH')
                <h2 class="text-2xl font-black">Your details</h2>

                <div>
                    <label class="label" for="name">Name</label>
                    <input id="name" class="input" type="text" name="name" value="{{ old('name', $user->name) }}" maxlength="60" required autocomplete="name">
                    <x-error for="name"/>
                </div>

                <div>
                    <label class="label" for="email">Email</label>
                    <input id="email" class="input" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
                    <x-error for="email"/>
                </div>

                <button type="submit" class="btn btn-primary">Save details</button>
            </form>

            <form method="POST" action="{{ route('profile.password') }}" class="card space-y-4 p-5 sm:p-7">
                @csrf
                @method('PUT')
                <h2 class="text-2xl font-black">Change your password</h2>

                <div>
                    <label class="label" for="current_password">Current password</label>
                    <input id="current_password" class="input" type="password" name="current_password" required autocomplete="current-password">
                    <x-error for="current_password" bag="password"/>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="new_password">New password</label>
                        <input id="new_password" class="input" type="password" name="password" required minlength="8" autocomplete="new-password">
                    </div>
                    <div>
                        <label class="label" for="password_confirmation">And again</label>
                        <input id="password_confirmation" class="input" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
                    </div>
                </div>
                <x-error for="password" bag="password"/>

                <button type="submit" class="btn btn-primary">Change password</button>
            </form>

            <form method="POST" action="{{ route('profile.destroy') }}" class="card space-y-4 border-tomato p-5 sm:p-7" data-confirm="Delete your account for good? Your uploads, reviews and comments go too.">
                @csrf
                @method('DELETE')
                <h2 class="text-2xl font-black">Delete your account</h2>
                <p class="text-soft">This removes your account and everything you have uploaded, reviewed and commented. It can’t be undone.</p>

                <div>
                    <label class="label" for="delete_password">Your password, to confirm</label>
                    <input id="delete_password" class="input max-w-sm" type="password" name="password" required autocomplete="current-password">
                    <x-error for="password" bag="deletion"/>
                </div>

                <button type="submit" class="btn btn-danger"><x-icon name="trash" size="h-4.5 w-4.5"/> Delete my account</button>
            </form>
        </fieldset>
    </div>
</x-layout>
