<nav x-data="{ open: false }" class="print-hidden bg-white dark:bg-ink-900 border-b border-gray-100 dark:border-ink-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-14">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center gap-2">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        @if (Auth::user()->organization->icon_image_path)
                            <img src="{{ asset('storage/'.Auth::user()->organization->icon_image_path) }}" alt=""
                                class="h-9 w-9 rounded-md object-cover">
                        @else
                            <x-brand-mark class="h-7 w-7 text-leather-400 shrink-0" />
                        @endif
                        <span class="font-serif font-semibold text-ink-800 dark:text-paper-100">{{ Auth::user()->organization->name }}</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @can('manage')
                        <x-nav-link :href="route('meetings.index')" :active="request()->routeIs('meetings.*')">
                            {{ __('会議管理') }}
                        </x-nav-link>
                        <x-nav-link :href="route('positions.index')" :active="request()->routeIs('positions.*')">
                            {{ __('役職管理') }}
                        </x-nav-link>
                        <x-nav-link :href="route('departments.index')" :active="request()->routeIs('departments.*')">
                            {{ __('部署管理') }}
                        </x-nav-link>
                        <x-nav-link :href="route('members.index')" :active="request()->routeIs('members.*')">
                            {{ __('メンバー管理') }}
                        </x-nav-link>
                        <x-nav-link :href="route('materials.index')" :active="request()->routeIs('materials.*')">
                            {{ __('資料管理') }}
                        </x-nav-link>
                        <x-nav-link :href="route('settings.edit')" :active="request()->routeIs('settings.*')">
                            {{ __('基本設定') }}
                        </x-nav-link>
                    @else
                        <x-nav-link :href="route('meetings.index')" :active="request()->routeIs('meetings.*')">
                            {{ __('会議一覧') }}
                        </x-nav-link>
                        <x-nav-link :href="route('members.index')" :active="request()->routeIs('members.*')">
                            {{ __('メンバー一覧') }}
                        </x-nav-link>
                    @endcan
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-2">
                @include('layouts._trial-status-badge')
                @include('layouts._storage-usage-badge')
                <button type="button" x-data x-on:click="$dispatch('open-modal', 'inquiry-form')"
                    class="inline-flex items-center justify-center h-9 w-9 rounded-full text-gray-500 dark:text-paper-100/70 hover:text-gray-700 dark:hover:text-paper-100 hover:bg-gray-100 dark:hover:bg-ink-800 focus:outline-none transition"
                    title="{{ __('お問い合わせ') }}">
                    <span class="sr-only">{{ __('お問い合わせ') }}</span>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z" />
                    </svg>
                </button>
                <x-theme-toggle />
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-paper-100/70 bg-white dark:bg-ink-900 hover:text-gray-700 dark:hover:text-paper-100 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @can('manage')
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('プロフィール') }}
                            </x-dropdown-link>
                        @endcan

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button type="submit" class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 dark:text-paper-100/80 hover:bg-gray-100 dark:hover:bg-ink-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-ink-700 transition duration-150 ease-in-out">
                                {{ __('ログアウト') }}
                            </button>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-paper-100/50 hover:text-gray-500 dark:hover:text-paper-100/80 hover:bg-gray-100 dark:hover:bg-ink-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-ink-800 focus:text-gray-500 dark:focus:text-paper-100/80 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white dark:bg-ink-900">
        <div class="pt-2 pb-3 space-y-1">
            @can('manage')
                <x-responsive-nav-link :href="route('meetings.index')" :active="request()->routeIs('meetings.*')">
                    {{ __('会議管理') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('positions.index')" :active="request()->routeIs('positions.*')">
                    {{ __('役職管理') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('departments.index')" :active="request()->routeIs('departments.*')">
                    {{ __('部署管理') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('members.index')" :active="request()->routeIs('members.*')">
                    {{ __('メンバー管理') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('materials.index')" :active="request()->routeIs('materials.*')">
                    {{ __('資料管理') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('settings.edit')" :active="request()->routeIs('settings.*')">
                    {{ __('基本設定') }}
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('meetings.index')" :active="request()->routeIs('meetings.*')">
                    {{ __('会議一覧') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('members.index')" :active="request()->routeIs('members.*')">
                    {{ __('メンバー一覧') }}
                </x-responsive-nav-link>
            @endcan
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-ink-700">
            <div class="px-4 flex items-center justify-between">
                <div>
                    <div class="font-medium text-base text-gray-800 dark:text-paper-100">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500 dark:text-paper-100/50">{{ Auth::user()->email }}</div>
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" x-data x-on:click="$dispatch('open-modal', 'inquiry-form')"
                        class="inline-flex items-center justify-center h-9 w-9 rounded-full text-gray-500 dark:text-paper-100/70 hover:text-gray-700 dark:hover:text-paper-100 hover:bg-gray-100 dark:hover:bg-ink-800 focus:outline-none transition"
                        title="{{ __('お問い合わせ') }}">
                        <span class="sr-only">{{ __('お問い合わせ') }}</span>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z" />
                        </svg>
                    </button>
                    <x-theme-toggle />
                </div>
            </div>

            @if (($storageUsagePercent ?? 0) >= 80 || (($trialDaysRemaining ?? null) !== null && $trialDaysRemaining <= 3))
                <div class="px-4 mt-3 flex flex-wrap gap-2">
                    @include('layouts._trial-status-badge')
                    @include('layouts._storage-usage-badge')
                </div>
            @endif

            <div class="mt-3 space-y-1">
                @can('manage')
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('プロフィール') }}
                    </x-responsive-nav-link>
                @endcan

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit" class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-paper-200 hover:bg-gray-50 dark:hover:bg-ink-700 hover:border-gray-300 dark:hover:border-ink-600 focus:outline-none focus:text-gray-800 dark:focus:text-paper-200 focus:bg-gray-50 dark:focus:bg-ink-700 focus:border-gray-300 dark:focus:border-ink-600 transition duration-150 ease-in-out">
                        {{ __('ログアウト') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
