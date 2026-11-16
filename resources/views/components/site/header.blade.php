@php
    use App\Enums\EntryType;

    $onShelf = request()->route()?->parameter('shelf');   // the EntryType of the shelf being viewed, if any
    $user = auth()->user();
@endphp
<header class="sticky top-0 z-40 border-b-2 border-line bg-paper" x-data="{ open: false }" @keydown.escape.window="open = false">
    <div class="shell flex h-16 items-center gap-2 sm:gap-3">
        <x-logo/>

        {{-- The four shelves --}}
        <nav class="ml-3 hidden items-center gap-1 lg:flex" aria-label="Shelves">
            @foreach (EntryType::cases() as $type)
                <a href="{{ route('shelf', $type->segment()) }}" class="nav-link tone-{{ $type->colour() }}" @if ($onShelf === $type) aria-current="page" @endif>
                    {{ $type->shelf() }}
                </a>
            @endforeach
        </nav>

        <form action="{{ route('browse') }}" method="GET" role="search" class="relative ml-auto hidden md:block">
            <label for="site-search" class="sr-only">Search the shop</label>
            <x-icon name="search" size="h-4.5 w-4.5" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-soft"/>
            <input id="site-search" type="search" name="q" value="{{ request()->routeIs('browse') ? request('q') : '' }}" placeholder="Search titles, authors…" class="input h-10 w-52 pl-9 text-sm xl:w-64">
        </form>

        <button type="button" class="btn btn-sm btn-plain ml-auto h-10 w-10 p-0! md:ml-0" x-data="themeToggle" @click="toggle" :aria-pressed="dark" aria-label="Switch between light and dark">
            <span x-show="!dark"><x-icon name="moon"/></span>
            <span x-show="dark" x-cloak><x-icon name="sun"/></span>
        </button>

        @auth
            <a href="{{ route('entries.create') }}" class="btn btn-sm btn-primary hidden h-10 sm:inline-flex"><x-icon name="upload" size="h-4 w-4"/> Upload</a>

            <div class="relative hidden lg:block" x-data="{ menu: false }" @click.outside="menu = false" @keydown.escape="menu = false">
                <button type="button" class="avatar tone-{{ $user->colour() }} cursor-pointer shadow-pop-sm" @click="menu = !menu" :aria-expanded="menu" aria-label="Your account menu">{{ $user->initials() }}</button>

                <div class="card menu" x-show="menu" x-cloak x-transition.origin.top.right>
                    <p class="px-3 pt-2 pb-2.5 text-sm leading-tight">
                        <span class="block font-bold">{{ $user->name }}</span>
                        <span class="text-soft">{{ $user->roleLabel() }}</span>
                    </p>
                    <hr class="mb-1 border-line/20">
                    <a class="menu-item" href="{{ route('entries.mine') }}"><x-icon name="grid" size="h-4.5 w-4.5"/> My uploads</a>
                    <a class="menu-item" href="{{ route('profile.edit') }}"><x-icon name="user" size="h-4.5 w-4.5"/> My account</a>
                    @if ($user->isAdmin())
                        <a class="menu-item" href="{{ route('admin.dashboard') }}"><x-icon name="shield" size="h-4.5 w-4.5"/> Admin</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="menu-item cursor-pointer"><x-icon name="logout" size="h-4.5 w-4.5"/> Sign out</button>
                    </form>
                </div>
            </div>
        @else
            <a href="{{ route('login') }}" class="nav-link hidden text-sm sm:inline-flex">Sign in</a>
            <a href="{{ route('register') }}" class="btn btn-sm btn-primary hidden h-10 sm:inline-flex">Join free</a>
        @endauth

        <button type="button" class="btn btn-sm h-10 w-10 p-0! lg:hidden" @click="open = !open" :aria-expanded="open" aria-controls="mobile-menu" aria-label="Menu">
            <span x-show="!open"><x-icon name="menu"/></span>
            <span x-show="open" x-cloak><x-icon name="close"/></span>
        </button>
    </div>

    {{-- Phone and tablet menu --}}
    <div id="mobile-menu" class="border-t-2 border-line bg-paper lg:hidden" x-show="open" x-cloak x-transition.opacity>
        <div class="shell space-y-4 py-4">
            <form action="{{ route('browse') }}" method="GET" role="search" class="relative md:hidden">
                <label for="mobile-search" class="sr-only">Search the shop</label>
                <x-icon name="search" size="h-4.5 w-4.5" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-soft"/>
                <input id="mobile-search" type="search" name="q" placeholder="Search titles, authors…" class="input pl-9">
            </form>

            <nav class="grid grid-cols-2 gap-2" aria-label="Shelves">
                @foreach (EntryType::cases() as $type)
                    <a href="{{ route('shelf', $type->segment()) }}" class="btn bg-tone tone-{{ $type->colour() }} justify-start">
                        <x-icon :name="$type->icon()" size="h-4.5 w-4.5"/> {{ $type->shelf() }}
                    </a>
                @endforeach
            </nav>

            <div class="flex flex-wrap gap-2 border-t-2 border-line/15 pt-4">
                @auth
                    <a class="btn btn-sm btn-primary" href="{{ route('entries.create') }}"><x-icon name="upload" size="h-4 w-4"/> Upload</a>
                    <a class="btn btn-sm" href="{{ route('entries.mine') }}">My uploads</a>
                    <a class="btn btn-sm" href="{{ route('profile.edit') }}">My account</a>
                    @if ($user->isAdmin())
                        <a class="btn btn-sm" href="{{ route('admin.dashboard') }}">Admin</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-sm">Sign out</button>
                    </form>
                @else
                    <a class="btn btn-sm btn-primary" href="{{ route('register') }}">Join free</a>
                    <a class="btn btn-sm" href="{{ route('login') }}">Sign in</a>
                @endauth
            </div>
        </div>
    </div>
</header>
