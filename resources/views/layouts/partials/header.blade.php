@php
    $user = auth()->user();
@endphp
<header class="site-header">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between gap-4">

            {{-- Brand --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0" aria-label="{{ config('app.name', 'Foursouls') }} — home">
                <span class="brand-mark" aria-hidden="true">FS</span>
                <span class="text-lg font-bold tracking-tight text-gray-900">{{ config('app.name', 'Foursouls') }}</span>
            </a>

            @if ($user)
                {{-- Authenticated desktop nav (§22) --}}
                <nav class="hidden md:flex items-center gap-1" aria-label="Main navigation">
                    <a href="{{ route('dashboard') }}"
                       class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('learn.index') }}" class="nav-link {{ request()->routeIs('learn.*') ? 'nav-link-active' : '' }}">My Learning</a>
                    <a href="{{ route('certificates.index') }}" class="nav-link {{ request()->routeIs('certificates.*') ? 'nav-link-active' : '' }}">Certificates</a>
                    <a href="{{ route('learn.live-sessions.index') }}" class="nav-link {{ request()->routeIs('learn.live-sessions.*') ? 'nav-link-active' : '' }}">Live Sessions</a>
                    <a href="{{ route('courses.index') }}" class="nav-link {{ request()->routeIs('courses.*') ? 'nav-link-active' : '' }}">Courses</a>
                    <a href="{{ route('subscription.show') }}" class="nav-link {{ request()->routeIs('subscription.*') ? 'nav-link-active' : '' }}">Subscription</a>
                    <a href="{{ route('account.profile.edit') }}"
                       class="nav-link {{ request()->routeIs('account.profile.*') ? 'nav-link-active' : '' }}">
                        Profile
                    </a>
                    <a href="{{ route('account.security.edit') }}"
                       class="nav-link {{ request()->routeIs('account.security.*') ? 'nav-link-active' : '' }}">
                        Security
                    </a>
                    <a href="{{ route('account.preferences.edit') }}"
                       class="nav-link {{ request()->routeIs('account.preferences.*') ? 'nav-link-active' : '' }}">
                        Preferences
                    </a>
                </nav>

                <div class="flex items-center gap-3">
                    @include('components.currency-selector')

                    {{-- Account menu (§23) — details/summary keeps it keyboard accessible without JS --}}
                    <details class="relative group" data-account-menu>
                        <summary class="flex items-center gap-2 cursor-pointer list-none rounded-full py-1 pl-1 pr-3 hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                                 aria-haspopup="menu" aria-label="Account menu">
                            <span class="avatar avatar-sm overflow-hidden rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-semibold w-8 h-8">
                                @if ($user->avatar_path)
                                    <img src="{{ Storage::url($user->avatar_path) }}" alt="" class="w-full h-full object-cover">
                                @else
                                    {{ mb_strtoupper(mb_substr($user->full_name, 0, 1)) }}
                                @endif
                            </span>
                            <span class="hidden sm:block text-sm font-medium text-gray-700 max-w-[10rem] truncate">{{ $user->full_name }}</span>
                            <svg class="w-4 h-4 text-gray-500 transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.23 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                            </svg>
                        </summary>

                        <div class="absolute right-0 z-30 mt-2 w-52 origin-top-right rounded-xl border border-gray-200 bg-white shadow-lg py-1.5"
                             role="menu" aria-label="Account menu options">
                            <a href="{{ route('dashboard') }}" role="menuitem"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 focus-visible:bg-gray-50 focus-visible:outline-none">Dashboard</a>
                            <a href="{{ route('learn.index') }}" role="menuitem" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 focus-visible:bg-gray-50 focus-visible:outline-none">My Learning</a>
                            <a href="{{ route('certificates.index') }}" role="menuitem" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 focus-visible:bg-gray-50 focus-visible:outline-none">Certificates</a>
                            <a href="{{ route('learn.live-sessions.index') }}" role="menuitem" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 focus-visible:bg-gray-50 focus-visible:outline-none">Live Sessions</a>
                            <a href="{{ route('courses.index') }}" role="menuitem" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 focus-visible:bg-gray-50 focus-visible:outline-none">Courses</a>
                            <a href="{{ route('subscription.show') }}" role="menuitem" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 focus-visible:bg-gray-50 focus-visible:outline-none">Subscription</a>
                            <a href="{{ route('account.profile.edit') }}" role="menuitem"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 focus-visible:bg-gray-50 focus-visible:outline-none">Profile</a>
                            <a href="{{ route('account.security.edit') }}" role="menuitem"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 focus-visible:bg-gray-50 focus-visible:outline-none">Security</a>
                            <a href="{{ route('account.preferences.edit') }}" role="menuitem"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 focus-visible:bg-gray-50 focus-visible:outline-none">Preferences</a>
                            <hr class="my-1.5 border-gray-100">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" role="menuitem"
                                        class="w-full text-left px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 focus-visible:bg-red-50 focus-visible:outline-none">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </details>
                </div>
            @else
                <nav class="hidden md:flex items-center gap-1" aria-label="Public navigation">
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'nav-link-active' : '' }}">Home</a>
                    <a href="{{ route('courses.index') }}" class="nav-link {{ request()->routeIs('courses.*') ? 'nav-link-active' : '' }}">Courses</a>
                    <a href="{{ route('blog.index') }}" class="nav-link {{ request()->routeIs('blog.*') ? 'nav-link-active' : '' }}">Blog</a>
                </nav>
                {{-- Guest state (§22): Login / Register + currency selector only.
                     No Courses links — module not built yet. --}}
                <div class="flex items-center gap-3">
                    @include('components.currency-selector')
                    <a href="{{ route('login') }}" class="btn btn-secondary btn-sm">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Register</a>
                </div>
            @endif
        </div>
    </div>
</header>
