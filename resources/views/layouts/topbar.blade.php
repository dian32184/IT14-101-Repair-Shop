<header
    class="h-16 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-b border-slate-200/80 dark:border-slate-800/80 text-slate-800 dark:text-white shadow-xs flex items-center justify-between px-4 sm:px-6 sticky top-0 z-40 transition-all">
    <!-- Left: Mobile Menu Toggle + Title -->
    <div class="flex items-center gap-3 min-w-0">
        <button type="button" @click="sidebarOpen = !sidebarOpen"
            class="lg:hidden inline-flex h-9 w-9 items-center justify-center rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500/20"
            aria-label="Toggle navigation menu"
            :aria-expanded="sidebarOpen.toString()">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        <div class="flex flex-col justify-center min-w-0">
            <h1 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white leading-tight tracking-tight truncate">
                {{ $pageTitle ?? '101 Repair Service' }}
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-tight mt-0.5 truncate flex items-center gap-1.5">
                <span>Welcome, <strong class="font-medium text-slate-700 dark:text-slate-300">{{ auth()->user()->full_name ?? auth()->user()->name ?? 'User' }}</strong></span>
                <span class="hidden sm:inline text-slate-300 dark:text-slate-600">·</span>
                <span class="hidden sm:inline text-slate-400 dark:text-slate-500">{{ now()->format('l, F j, Y') }}</span>
            </p>
        </div>
    </div>

    <!-- Right Side Actions -->
    <div class="flex items-center gap-1.5 sm:gap-2">
        <!-- Font Size Adjuster -->
        <div class="relative flex items-center justify-center" x-data="{ fontOpen: false }">
            <button @click="fontOpen = !fontOpen" @click.away="fontOpen = false"
                class="h-9 w-9 inline-flex items-center justify-center rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800/80 transition-all focus:outline-none"
                title="Adjust Font Size">
                <span class="text-base font-serif font-bold leading-none">A</span>
                <span class="text-xs font-serif leading-none -ml-0.5 mt-1">a</span>
            </button>
            <div x-show="fontOpen" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 top-11 w-40 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200/80 dark:border-slate-700/80 z-50 overflow-hidden py-1.5"
                style="display: none;">
                <div class="px-3 py-1 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Font Size</div>
                <button @click="changeFontSize('sm'); fontOpen = false" class="w-full text-left px-3 py-2 text-xs hover:bg-slate-100 dark:hover:bg-slate-700/60 text-slate-700 dark:text-slate-200 font-medium flex items-center justify-between">
                    <span>Small</span>
                    <span class="text-[10px] text-slate-400 font-mono">14px</span>
                </button>
                <button @click="changeFontSize('md'); fontOpen = false" class="w-full text-left px-3 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-700/60 text-slate-700 dark:text-slate-200 font-medium flex items-center justify-between">
                    <span>Medium</span>
                    <span class="text-[10px] text-slate-400 font-mono">16px</span>
                </button>
                <button @click="changeFontSize('lg'); fontOpen = false" class="w-full text-left px-3 py-2 text-base hover:bg-slate-100 dark:hover:bg-slate-700/60 text-slate-700 dark:text-slate-200 font-medium flex items-center justify-between">
                    <span>Large</span>
                    <span class="text-[10px] text-slate-400 font-mono">18px</span>
                </button>

            </div>
        </div>

        <!-- Dark / Light Theme Toggle -->
        <button id="theme-toggle" type="button"
            class="h-9 w-9 inline-flex items-center justify-center rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800/80 transition-all focus:outline-none"
            title="Toggle theme mode">
            <svg id="theme-toggle-dark-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
            </svg>
            <svg id="theme-toggle-light-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
            </svg>
        </button>

        <!-- Notifications Dropdown -->
        @php
            $unreadNotifications = Auth::check() ? Auth::user()->unreadNotifications : collect();
            $unreadCount = $unreadNotifications->count();
            $allNotifications = Auth::check() ? Auth::user()->notifications()->take(5)->get() : collect();
        @endphp
        <div class="relative flex items-center justify-center" x-data="{ notifyOpen: false }">
            <button @click="notifyOpen = !notifyOpen" @click.away="notifyOpen = false"
                class="relative h-9 w-9 inline-flex items-center justify-center rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800/80 transition-all focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                    </path>
                </svg>
                @if($unreadCount > 0)
                    <span class="absolute top-1.5 right-1.5 flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500 border border-white dark:border-slate-900"></span>
                    </span>
                @endif
            </button>

            <div x-show="notifyOpen" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 top-11 w-80 sm:w-96 bg-white/95 dark:bg-slate-800/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-slate-200/80 dark:border-slate-700/80 z-50 overflow-hidden flex flex-col max-h-[30rem]"
                style="display: none;">

                <div class="px-4 py-3 shrink-0 border-b border-slate-100 dark:border-slate-700/80 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Notifications</h3>
                        @if($unreadCount > 0)
                            <span class="px-2 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 rounded-full">{{ $unreadCount }} new</span>
                        @endif
                    </div>
                    @if($unreadCount > 0)
                        <form action="{{ Route::has('notifications.markRead') ? route('notifications.markRead') : '#' }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 font-semibold hover:underline">Mark all as read</button>
                        </form>
                    @endif
                </div>

                <div class="flex-1 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50 min-h-0">
                    @if($allNotifications->count() > 0)
                        @foreach($allNotifications as $notification)
                            @php
                                $isUnread = $notification->unread();
                            @endphp
                            <a href="{{ $notification->data['url'] ?? '#' }}"
                                class="flex gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors {{ $isUnread ? 'bg-blue-50/50 dark:bg-slate-800/80' : '' }}">
                                <div class="mt-1 shrink-0">
                                   @if($isUnread)
                                       <span class="h-2 w-2 rounded-full bg-blue-600 inline-block ring-4 ring-blue-100 dark:ring-blue-900/40"></span>
                                   @else
                                       <span class="h-2 w-2 rounded-full bg-slate-300 dark:bg-slate-600 inline-block"></span>
                                   @endif
                                </div>
                                <div class="flex-1 flex flex-col gap-0.5">
                                    <h4 class="text-sm font-semibold text-slate-900 dark:text-white">{{ $notification->data['title'] ?? 'Notification' }}</h4>
                                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">{{ $notification->data['message'] ?? 'New notification received.' }}</p>
                                    <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500 mt-1">{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                            </a>
                        @endforeach
                    @else
                        <div class="p-8 text-center flex flex-col items-center justify-center">
                            <div class="h-12 w-12 rounded-full bg-slate-100 dark:bg-slate-700/50 flex items-center justify-center mb-3">
                                <svg class="h-6 w-6 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">No notifications</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">You're completely caught up!</p>
                        </div>
                    @endif
                </div>

                <div class="shrink-0 p-3 bg-slate-50/80 dark:bg-slate-800/80 border-t border-slate-100 dark:border-slate-700/80 text-center">
                    <a href="{{ Route::has('notifications.index') ? route('notifications.index') : '#' }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 hover:underline">View All Notifications →</a>
                </div>
            </div>
        </div>

    </div>
</header>


