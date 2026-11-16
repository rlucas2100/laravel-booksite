@props(['title'])
{{-- The frame for every admin page: a heading and the admin tabs. <x-admin title="Members"> --}}
@php
    $user = auth()->user();
    $tabs = array_filter([
        ['admin.dashboard', 'Overview', 'grid', true],
        ['admin.entries', 'Catalogue', 'book', $user->can('manage-entries')],
        ['admin.community', 'Reviews & comments', 'chat', $user->can('moderate-community')],
        ['admin.users.index', 'Members', 'users', $user->can('manage-users')],
        ['admin.categories.index', 'Categories', 'tag', $user->can('manage-categories')],
    ], fn (array $tab) => $tab[3]);
@endphp
<x-layout :title="$title.' · Admin'">
    <section class="border-b-2 border-night bg-night text-cream">
        <div class="shell pt-8">
            <p class="eyebrow text-sun"><x-icon name="shield" size="h-4 w-4"/> Admin · signed in as {{ $user->name }} ({{ $user->roleLabel() }})</p>
            <h1 class="mt-1 text-4xl font-black sm:text-5xl">{{ $title }}</h1>

            <nav class="mt-6 flex gap-1 overflow-x-auto" aria-label="Admin pages">
                @foreach ($tabs as [$route, $label, $icon])
                    @php
                        $current = request()->routeIs($route) || ($route === 'admin.users.index' && request()->routeIs('admin.users.*'));
                    @endphp
                    <a href="{{ route($route) }}" @if ($current) aria-current="page" @endif
                       class="inline-flex shrink-0 items-center gap-2 rounded-t-xl border-2 border-b-0 px-4 py-2.5 text-sm font-bold {{ $current ? 'border-cream bg-paper text-ink' : 'border-transparent text-cream/80 hover:bg-cream/10' }}">
                        <x-icon :name="$icon" size="h-4 w-4"/> {{ $label }}
                    </a>
                @endforeach
            </nav>
        </div>
    </section>

    @if (\App\Support\Sandbox::enabled() && ! \App\Support\Sandbox::unrestricted())
        <div class="shell mt-6">
            <p class="card flex items-start gap-3 bg-sun p-4 text-sm font-semibold text-night">
                <x-icon name="lock" class="mt-0.5"/>
                <span>You’re looking at the admin area of the public demo. The shop’s own books, members and reviews are locked (<x-icon name="lock" size="h-3.5 w-3.5" class="inline"/>), so the next visitor finds them intact.
                    Anything <b>you</b> add, as a member or as an admin, you can edit and delete here.</span>
            </p>
        </div>
    @endif

    <div class="shell py-8">
        {{ $slot }}
    </div>
</x-layout>
