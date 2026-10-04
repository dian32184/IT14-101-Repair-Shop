<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

    <style>
        html.text-sm-size { font-size: 14px; }
        html.text-md-size { font-size: 16px; }
        html.text-lg-size { font-size: 18px; }
    </style>

    <!-- Scripts & Styles (Offline TailWind via Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Turbo.js for SPA-like navigation -->
    <script src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@7.3.0/dist/turbo.min.js"></script>

    <script>
        // Check for dark mode preference to prevent FOUC
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        // Apply font size preference
        const savedSize = localStorage.getItem('font-size') || 'md';
        document.documentElement.classList.add('text-' + savedSize + '-size');

        function changeFontSize(size) {
            document.documentElement.classList.remove('text-sm-size', 'text-md-size', 'text-lg-size');
            document.documentElement.classList.add('text-' + size + '-size');
            localStorage.setItem('font-size', size);
        }
        window.changeFontSize = changeFontSize;
    </script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="app-bg text-gray-900 dark:text-gray-100 antialiased font-sans"
    x-data="{
        sidebarOpen: false,
        confirmModal: false,
        confirmTitle: 'Confirm Action',
        confirmMessage: '',
        confirmAction: null,
        confirmVariant: 'danger',
        confirmConfirmText: 'Confirm',
        confirmCancelText: 'Cancel',
        askConfirm(message, action, options = {}) {
            this.confirmMessage = message;
            this.confirmAction = action;
            this.confirmTitle = options.title || 'Confirm Action';
            this.confirmVariant = options.variant || 'danger';
            this.confirmConfirmText = options.confirmText || 'Confirm';
            this.confirmCancelText = options.cancelText || 'Cancel';
            this.confirmModal = true;
        },
        doConfirm() {
            if(this.confirmAction) this.confirmAction();
            this.confirmModal = false;
            this.confirmAction = null;
        },
        variantTheme() {
            const theme = {
                danger: {
                    iconBg: 'bg-red-100 dark:bg-red-900/30',
                    iconText: 'text-red-600 dark:text-red-400',
                    confirmBtn: 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
                },
                warning: {
                    iconBg: 'bg-amber-100 dark:bg-amber-900/30',
                    iconText: 'text-amber-700 dark:text-amber-400',
                    confirmBtn: 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-500',
                },
                info: {
                    iconBg: 'bg-blue-100 dark:bg-blue-900/30',
                    iconText: 'text-blue-600 dark:text-blue-400',
                    confirmBtn: 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
                },
                success: {
                    iconBg: 'bg-green-100 dark:bg-green-900/30',
                    iconText: 'text-green-600 dark:text-green-400',
                    confirmBtn: 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
                },
            };
            return theme[this.confirmVariant] || theme.danger;
        }
    }"
    @open-confirm.window="askConfirm($event.detail.message, $event.detail.action, $event.detail)">

    <div class="flex h-screen overflow-hidden">
        <!-- Mobile sidebar overlay -->
        <div x-show="sidebarOpen" x-transition.opacity
            class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-[2px] lg:hidden"
            style="display: none;"
            @click="sidebarOpen = false"></div>

        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col ml-0 lg:ml-64 min-w-0 transition-all duration-300">
            <!-- Topbar -->
            @include('layouts.topbar')

            <!-- Main Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto app-bg p-4 sm:p-6">
                @if(isset($header))
                    <div class="mb-6">
                        {{ $header }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-4 p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-800 dark:text-emerald-300">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl text-red-800 dark:text-red-300">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Global Confirmation Modal -->
    <div x-show="confirmModal"
        class="fixed inset-0 z-[999] overflow-y-auto" style="display:none;"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @keydown.escape.window="confirmModal = false">
        <div class="flex min-h-screen items-center justify-center px-4">
            <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" @click="confirmModal = false"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-3xl shadow-2xl border border-slate-200/80 dark:border-slate-700/80 p-6 max-w-md w-full z-10 transform transition-all"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2">
                <div class="flex items-start gap-4 mb-5">
                    <div class="h-12 w-12 rounded-2xl flex items-center justify-center shrink-0 ring-4 ring-slate-100 dark:ring-slate-700/40"
                        :class="variantTheme().iconBg">
                        <template x-if="confirmVariant === 'success'">
                            <svg class="h-6 w-6" :class="variantTheme().iconText" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                        <template x-if="confirmVariant === 'info'">
                            <svg class="h-6 w-6" :class="variantTheme().iconText" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                            </svg>
                        </template>
                        <template x-if="confirmVariant !== 'success' && confirmVariant !== 'info'">
                            <svg class="h-6 w-6" :class="variantTheme().iconText" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77-1.333.192 3 1.732 3z" />
                            </svg>
                        </template>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white" x-text="confirmTitle"></h3>
                        <p class="text-sm text-slate-500 dark:text-slate-300 mt-1 leading-relaxed" x-text="confirmMessage"></p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-700/60">
                    <button @click="confirmModal = false"
                        class="px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/60 transition-colors"
                        x-text="confirmCancelText">
                    </button>
                    <button @click="doConfirm()"
                        class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-slate-800"
                        :class="variantTheme().confirmBtn"
                        x-text="confirmConfirmText">
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            if (themeToggleLightIcon) themeToggleLightIcon.classList.remove('hidden');
        } else {
            if (themeToggleDarkIcon) themeToggleDarkIcon.classList.remove('hidden');
        }

        var themeToggleBtn = document.getElementById('theme-toggle');
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', function() {
                themeToggleDarkIcon.classList.toggle('hidden');
                themeToggleLightIcon.classList.toggle('hidden');

                if (localStorage.getItem('color-theme')) {
                    if (localStorage.getItem('color-theme') === 'light') {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('color-theme', 'dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('color-theme', 'light');
                    }
                } else {
                    if (document.documentElement.classList.contains('dark')) {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('color-theme', 'light');
                    } else {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('color-theme', 'dark');
                    }
                }
            });
        }
    </script>
</body>

</html>