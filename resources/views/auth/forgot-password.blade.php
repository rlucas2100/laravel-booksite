<x-auth-card title="Forgot password" heading="Lost your password?" intro="Tell us your email address and we’ll send a link to choose a new one." mood="2" tone="cobalt">
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label class="label" for="email">Email</label>
            <input id="email" class="input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            <x-error for="email"/>
        </div>

        @if (config('mail.default') === 'log')
            <p class="hint">This site isn’t connected to an email service, so the link is written to the server’s log instead of being sent.</p>
        @endif

        <button type="submit" class="btn btn-lg btn-primary w-full">Email me a reset link</button>
    </form>

    <p class="mt-5 text-sm"><a class="font-bold underline underline-offset-2" href="{{ route('login') }}">Back to sign in</a></p>

    <x-slot:aside>
        <p class="pow text-4xl">It happens.</p>
        <p class="mt-2 font-semibold">Pip loses his bookmark at least once a week.</p>
    </x-slot:aside>
</x-auth-card>
