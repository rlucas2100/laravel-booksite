<x-auth-card title="New password" heading="Choose a new password" mood="4" tone="cobalt">
    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label class="label" for="email">Email</label>
            <input id="email" class="input" type="email" name="email" value="{{ old('email', $request->query('email')) }}" required autocomplete="username">
            <x-error for="email"/>
        </div>

        <div>
            <label class="label" for="password">New password</label>
            <input id="password" class="input" type="password" name="password" required minlength="8" autofocus autocomplete="new-password">
            <p class="hint">At least 8 characters.</p>
            <x-error for="password"/>
        </div>

        <div>
            <label class="label" for="password_confirmation">And again</label>
            <input id="password_confirmation" class="input" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
        </div>

        <button type="submit" class="btn btn-lg btn-primary w-full">Save the new password</button>
    </form>

    <x-slot:aside>
        <p class="pow text-4xl">Fresh start!</p>
        <p class="mt-2 font-semibold">Pick something you’ll remember and nobody else will guess.</p>
    </x-slot:aside>
</x-auth-card>
