<x-auth-card title="Join" heading="Join free" intro="An account lets you download and buy books, rate and review, comment and upload." mood="5" tone="mint">
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label class="label" for="name">Name</label>
            <input id="name" class="input" type="text" name="name" value="{{ old('name') }}" maxlength="60" required autofocus autocomplete="name">
            <p class="hint">Shown beside your reviews and comments.</p>
            <x-error for="name"/>
        </div>

        <div>
            <label class="label" for="email">Email</label>
            <input id="email" class="input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
            <x-error for="email"/>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="label" for="password">Password</label>
                <input id="password" class="input" type="password" name="password" required minlength="8" autocomplete="new-password">
                <p class="hint">At least 8 characters.</p>
            </div>
            <div>
                <label class="label" for="password_confirmation">And again</label>
                <input id="password_confirmation" class="input" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
            </div>
        </div>
        <x-error for="password"/>

        @if (\App\Support\Sandbox::enabled())
            <p class="flex items-start gap-2 rounded-xl border-2 border-line bg-sun p-3 text-sm font-semibold text-night">
                <x-icon name="info" size="h-4.5 w-4.5" class="mt-0.5"/>
                This is a demo: accounts made here are deleted after {{ config('shop.sandbox_hours') }} hours. A made-up email address is fine.
            </p>
        @endif

        <button type="submit" class="btn btn-lg btn-primary w-full">Create my account</button>
    </form>

    <p class="mt-5 text-sm">Already a member? <a class="font-bold underline underline-offset-2" href="{{ route('login') }}">Sign in</a>.</p>

    <x-slot:aside>
        <p class="pow text-4xl">Pull up a chair!</p>
        <p class="mt-2 font-semibold">Most of the shelves are free. All we ask for is your honest star rating.</p>
    </x-slot:aside>
</x-auth-card>
