<x-auth-card title="Sign in" heading="Welcome back" intro="Sign in to download books, write reviews and join the conversation." mood="4">
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label class="label" for="email">Email</label>
            <input id="email" class="input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            <x-error for="email"/>
        </div>

        <div>
            <label class="label" for="password">Password</label>
            <input id="password" class="input" type="password" name="password" required autocomplete="current-password">
            <x-error for="password"/>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
            <label class="flex items-center gap-2 font-semibold">
                <input type="checkbox" name="remember" class="h-4 w-4 accent-tomato"> Keep me signed in
            </label>
            <a class="font-bold underline underline-offset-2" href="{{ route('password.request') }}">Forgot your password?</a>
        </div>

        <button type="submit" class="btn btn-lg btn-primary w-full">Sign in</button>
    </form>

    <p class="mt-5 text-sm">New here? <a class="font-bold underline underline-offset-2" href="{{ route('register') }}">Join free</a>. It takes a minute.</p>

    {{-- The public demo offers two ready-made accounts --}}
    @if ($demoAccounts)
        <div class="mt-7 rounded-xl border-2 border-dashed border-line bg-sunk p-4">
            <p class="pow text-xl">Just looking? Try a demo account</p>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                @foreach ($demoAccounts as $key => $account)
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <input type="hidden" name="email" value="{{ $account['email'] }}">
                        <input type="hidden" name="password" value="{{ $account['password'] }}">
                        <button type="submit" class="btn {{ $key === 'admin' ? 'btn-sun' : 'btn-mint' }} w-full">
                            <x-icon :name="$key === 'admin' ? 'shield' : 'book'" size="h-4.5 w-4.5"/> {{ $key === 'admin' ? 'Sign in as an admin' : 'Sign in as a reader' }}
                        </button>
                        <p class="mt-2 text-xs leading-relaxed text-soft">
                            {{ $account['email'] }}<br>password: <code class="font-bold text-ink">{{ $account['password'] }}</code>
                        </p>
                    </form>
                @endforeach
            </div>
        </div>
    @endif

    <x-slot:aside>
        <p class="pow text-4xl">Where were we?</p>
        <p class="mt-2 font-semibold">Your reviews, your uploads and your place in every recording are waiting.</p>
    </x-slot:aside>
</x-auth-card>
