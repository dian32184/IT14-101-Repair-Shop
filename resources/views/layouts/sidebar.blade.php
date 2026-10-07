<aside
    class="w-64 bg-[#0b1220] dark:bg-slate-900 border-r border-white/5 dark:border-slate-800/80 flex flex-col h-screen fixed left-0 top-0 z-50 transform transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0 shadow-2xl ring-1 ring-black/10' : '-translate-x-full lg:translate-x-0'"
    @keydown.escape.window="sidebarOpen = false">
    <!-- Brand Logo Header -->
    <div class="flex items-center justify-between px-5 py-4 border-b border-white/5 dark:border-slate-800/80 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group" @click="sidebarOpen = false">
            <div class="flex items-center justify-center">
                <img src="{{ asset('img/repairservicelogoblue.png') }}" alt="101 Repair Shop Logo"
                    class="w-36 h-auto max-h-12 object-contain block dark:hidden group-hover:scale-105 transition-transform duration-200">
                <img src="{{ asset('img/repairservicelogogray.png') }}" alt="101 Repair Shop Logo"
                    class="w-36 h-auto max-h-12 object-contain hidden dark:block group-hover:scale-105 transition-transform duration-200">
            </div>
        </a>
        <button type="button" @click="sidebarOpen = false"
            class="lg:hidden inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors"
            aria-label="Close menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Scrollable Navigation -->
    <nav class="flex-1 overflow-y-auto py-5 px-3 sidebar-scroll space-y-6">
        <!-- Main Menu / Operations -->
        <div>
            <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400/80 dark:text-slate-400">Main Menu</p>
            <div class="space-y-1">
                <a href="{{ route('dashboard') }}" @click="sidebarOpen = false"
                    class="sidebar-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                        </path>
                    </svg>
                    <span>Dashboard</span>
                </a>

                @if(in_array(auth()->user()->role, ['Administrator', 'Secretary', 'Cashier']))
                    <a href="{{ route('customers.index') }}" @click="sidebarOpen = false"
                        class="sidebar-link {{ request()->routeIs('customers.*') ? 'is-active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                        <span>Customer Info</span>
                    </a>
                @endif

                <a href="{{ route('services.index') }}" @click="sidebarOpen = false"
                    class="sidebar-link {{ request()->routeIs('services.*') ? 'is-active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <span>Service Reports</span>
                </a>

                @if(in_array(auth()->user()->role, ['Administrator', 'Cashier']))
                    <a href="{{ route('transactions.index') }}" @click="sidebarOpen = false"
                        class="sidebar-link {{ request()->routeIs('transactions.*') ? 'is-active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        <span>Transactions</span>
                    </a>
                @endif

                @if(in_array(auth()->user()->role, ['Administrator', 'Secretary']))
                    <a href="{{ route('inventory.index') }}" @click="sidebarOpen = false"
                        class="sidebar-link {{ request()->routeIs('inventory.*') ? 'is-active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span>Inventory / Parts</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Administration Menu -->
        @if(auth()->user()->role === 'Administrator')
            <div>
                <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400/80 dark:text-slate-400">Administration</p>
                <div class="space-y-1">
                    <a href="{{ route('staff.index') }}" @click="sidebarOpen = false"
                        class="sidebar-link {{ request()->routeIs('staff.*') ? 'is-active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span>Staff Management</span>
                    </a>

                    <a href="{{ route('prices.index') }}" @click="sidebarOpen = false"
                        class="sidebar-link {{ request()->routeIs('prices.*') ? 'is-active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                        <span>Service Prices</span>
                    </a>

                    <a href="{{ route('archive.index') }}" @click="sidebarOpen = false"
                        class="sidebar-link {{ request()->routeIs('archive.*') ? 'is-active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                        </svg>
                        <span>System Archive</span>
                    </a>
                </div>
            </div>
        @endif
    </nav>

   <!-- Signed-in User Card Footer & Popover Dropdown -->
<div
    class="p-3 border-t border-white/5 dark:border-slate-800/80 shrink-0 bg-[#0b1220] dark:bg-slate-900 relative"
    x-data="{ userMenuOpen: false }">

    <!-- Popover Menu -->
    <div
        x-show="userMenuOpen"
        x-cloak
        @click.away="userMenuOpen = false"

        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"

        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"

        class="absolute left-3 right-3 bg-slate-900 dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-700/80 overflow-hidden divide-y divide-slate-800 dark:divide-slate-700/60"

        style="display: none; bottom: calc(100% + 12px); z-index: 9999;">

        <!-- User Header Info -->
        <div class="px-4 py-3 bg-slate-900/90 dark:bg-slate-800/90">

            <p class="text-sm font-semibold text-white truncate">
                {{ Auth::user()->name }}
            </p>

            <p class="text-xs text-slate-400 truncate mt-0.5">
                {{ Auth::user()->email }}
            </p>

            <span class="inline-block mt-2 px-2 py-0.5 text-[10px] font-semibold bg-blue-500/20 text-blue-300 rounded-full border border-blue-500/30">
                {{ Auth::user()->role }}
            </span>

        </div>


        <!-- Profile Links -->
        <div class="py-1">

            <!-- View Profile -->
            <a
                href="{{ route('profile.show') }}"
                @click="userMenuOpen = false; sidebarOpen = false;"
                class="flex items-center px-4 py-2.5 text-sm font-medium text-slate-200 hover:bg-blue-600 hover:text-white transition-colors group">

                <svg
                    class="w-4 h-4 mr-3 text-slate-400 group-hover:text-white transition-colors"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7-7z">
                    </path>

                </svg>

                View Profile
            </a>


            <!-- Settings -->
            <a
                href="{{ route('profile.edit') }}"
                @click="userMenuOpen = false; sidebarOpen = false;"
                class="flex items-center px-4 py-2.5 text-sm font-medium text-slate-200 hover:bg-blue-600 hover:text-white transition-colors group">

                <svg
                    class="w-4 h-4 mr-3 text-slate-400 group-hover:text-white transition-colors"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94-1.543-.826-3.31-2.37-2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 00-1.066-2.573c-.94-1.543.826-2.37-2.37-2.37-.996.608-2.296.07-2.572-1.065z">
                    </path>

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                    </path>

                </svg>

                Settings
            </a>

        </div>


        <!-- Logout -->
        <div class="py-1">

            <form
                method="POST"
                action="{{ route('logout') }}">

                @csrf

                <button
                    type="submit"
                    class="w-full flex items-center px-4 py-2.5 text-sm font-medium text-red-400 hover:bg-red-600 hover:text-white transition-colors text-left group">

                    <svg
                        class="w-4 h-4 mr-3 text-red-400 group-hover:text-white transition-colors"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                        </path>

                    </svg>

                    Logout

                </button>

            </form>

        </div>

    </div>


    <!-- Trigger Button -->
    <button
        type="button"
        @click="userMenuOpen = !userMenuOpen"
        class="w-full flex items-center gap-3 rounded-xl p-2 hover:bg-white/10 dark:hover:bg-slate-800 transition-all duration-200 group text-left focus:outline-none">

        <!-- Profile Picture -->
        <div
            class="h-10 w-10 rounded-xl bg-blue-600 flex items-center justify-center text-white text-sm font-bold overflow-hidden shrink-0 ring-2 ring-blue-400/30 group-hover:scale-105 transition-transform duration-200">

            @if(Auth::user()->profile_picture)

                <img
                    src="{{ Auth::user()->profile_picture }}"
                    alt="{{ Auth::user()->name }}"
                    class="h-full w-full object-cover">

            @else

                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}

            @endif

        </div>


        <!-- User Information -->
        <div class="min-w-0 flex-1">

            <p class="text-sm font-semibold text-white truncate group-hover:text-blue-300 transition-colors">
                {{ Auth::user()->name }}
            </p>

            <div class="flex items-center gap-1.5 mt-0.5">

                <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>

                <p class="text-[11px] font-medium text-slate-400 truncate tracking-wide">
                    {{ Auth::user()->role }}
                </p>

            </div>

        </div>


        <!-- Arrow -->
        <svg
            class="w-4 h-4 text-slate-400 group-hover:text-white transition-all shrink-0"
            :class="{ 'rotate-180': userMenuOpen, 'rotate-0': !userMenuOpen }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24">

            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M5 15l7-7 7 7">
            </path>

        </svg>

    </button>

</div>
</aside>



