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

    <!-- Leaflet CSS (Maps) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

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
    <!-- Toast Store -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('toast', {
                items: [],
                maxVisible: 4,

                show(options) {
                    const id = Date.now() + Math.random();
                    const toast = {
                        id,
                        type: options.type || 'info',
                        title: options.title || '',
                        message: options.message || '',
                        duration: options.duration || (options.type === 'error' ? 7000 : 5000),
                        remaining: options.duration || (options.type === 'error' ? 7000 : 5000),
                        paused: false,
                        timer: null
                    };

                    this.items.push(toast);
                    this.startTimer(toast);

                    if (this.items.length > this.maxVisible) {
                        this.remove(this.items[0].id);
                    }
                },

                startTimer(toast) {
                    if (toast.timer) clearInterval(toast.timer);
                    
                    toast.timer = setInterval(() => {
                        if (!toast.paused) {
                            toast.remaining -= 100;
                            if (toast.remaining <= 0) {
                                this.remove(toast.id);
                            }
                        }
                    }, 100);
                },

                pause(id) {
                    const toast = this.items.find(t => t.id === id);
                    if (toast) {
                        toast.paused = true;
                    }
                },

                resume(id) {
                    const toast = this.items.find(t => t.id === id);
                    if (toast) {
                        toast.paused = false;
                    }
                },

                remove(id) {
                    const toast = this.items.find(t => t.id === id);
                    if (toast) {
                        if (toast.timer) clearInterval(toast.timer);
                        this.items = this.items.filter(t => t.id !== id);
                    }
                },

                get icon() {
                    return {
                        error: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
                        warning: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`,
                        success: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
                        info: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"></path></svg>`
                    };
                },

                get colors() {
                    return {
                        error: {
                            bg: 'bg-red-50 dark:bg-red-900/20',
                            border: 'border-red-200 dark:border-red-800',
                            iconBg: 'bg-red-100 dark:bg-red-900/40',
                            iconText: 'text-red-600 dark:text-red-400',
                            title: 'text-red-800 dark:text-red-200',
                            message: 'text-red-700 dark:text-red-300',
                            progress: 'bg-red-500'
                        },
                        warning: {
                            bg: 'bg-amber-50 dark:bg-amber-900/20',
                            border: 'border-amber-200 dark:border-amber-800',
                            iconBg: 'bg-amber-100 dark:bg-amber-900/40',
                            iconText: 'text-amber-600 dark:text-amber-400',
                            title: 'text-amber-800 dark:text-amber-200',
                            message: 'text-amber-700 dark:text-amber-300',
                            progress: 'bg-amber-500'
                        },
                        success: {
                            bg: 'bg-green-50 dark:bg-green-900/20',
                            border: 'border-green-200 dark:border-green-800',
                            iconBg: 'bg-green-100 dark:bg-green-900/40',
                            iconText: 'text-green-600 dark:text-green-400',
                            title: 'text-green-800 dark:text-green-200',
                            message: 'text-green-700 dark:text-green-300',
                            progress: 'bg-green-500'
                        },
                        info: {
                            bg: 'bg-blue-50 dark:bg-blue-900/20',
                            border: 'border-blue-200 dark:border-blue-800',
                            iconBg: 'bg-blue-100 dark:bg-blue-900/40',
                            iconText: 'text-blue-600 dark:text-blue-400',
                            title: 'text-blue-800 dark:text-blue-200',
                            message: 'text-blue-700 dark:text-blue-300',
                            progress: 'bg-blue-500'
                        }
                    };
                }
            });
        });
    </script>
    <!-- Leaflet JS (Maps) -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>

<body class="app-bg text-gray-900 dark:text-gray-100 antialiased font-sans"
    x-data="{
        sidebarOpen: false,
        confirmModal: false,
        confirmTitle: 'Confirm Action',
        confirmMessage: '',
        confirmAction: null,
        confirmVariant: 'danger', // danger | warning | info | success
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

    <!-- Toast Component (at top of body for proper positioning) -->
    @include('components.toast')

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

                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Global Flash Notification (Toast) -->
    @if(session()->has('success') || session()->has('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            class="fixed bottom-6 right-6 z-50 flex items-center gap-3.5 px-4 py-3 text-slate-700 bg-white/95 dark:bg-slate-800/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-slate-200/80 dark:border-slate-700/80 dark:text-slate-200 max-w-md"
            role="alert"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95">
            @if(session()->has('success'))
                <div class="inline-flex items-center justify-center shrink-0 w-9 h-9 text-emerald-600 bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-300 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ session('success') }}</div>
            @endif
            @if(session()->has('error'))
                <div class="inline-flex items-center justify-center shrink-0 w-9 h-9 text-rose-600 bg-rose-100 dark:bg-rose-900/40 dark:text-rose-300 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ session('error') }}</div>
            @endif
            <button type="button" @click="show = false" class="ml-auto inline-flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 dark:hover:text-white transition-colors" aria-label="Close alert">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    <!-- Global Confirmation Modal -->
    <div x-show="confirmModal"
        class="fixed inset-0 z-[999] overflow-y-auto" style="display:none;"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @keydown.escape.window="confirmModal = false">
        <div class="flex min-h-screen items-center justify-center px-4">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" @click="confirmModal = false"></div>
            <!-- Modal Card -->
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
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

        // Change the icons inside the button based on previous settings
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            if (themeToggleLightIcon) themeToggleLightIcon.classList.remove('hidden');
        } else {
            if (themeToggleDarkIcon) themeToggleDarkIcon.classList.remove('hidden');
        }

        var themeToggleBtn = document.getElementById('theme-toggle');
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', function() {
                // toggle icons inside button
                themeToggleDarkIcon.classList.toggle('hidden');
                themeToggleLightIcon.classList.toggle('hidden');

                // if set via local storage previously
                if (localStorage.getItem('color-theme')) {
                    if (localStorage.getItem('color-theme') === 'light') {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('color-theme', 'dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('color-theme', 'light');
                    }
                // if NOT set via local storage previously
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

        // Global Interceptor for Back, Cancel, Save, Edit, Remove, Restore actions
        document.addEventListener('DOMContentLoaded', () => {
            const dispatchConfirm = (detail) => {
                window.dispatchEvent(new CustomEvent('open-confirm', { detail }));
            };

            // Intercept form submissions for Save / Remove / Restore (skip auth, GET, and forms that already provide their own confirmation modal)
            document.querySelectorAll('form').forEach(form => {
                const isGet = form.method.toUpperCase() === 'GET';
                const isAuth = (form.action || '').includes('login') || (form.action || '').includes('logout');
                const skipByAttr = form.hasAttribute('data-confirm-skip') || form.dataset.confirmSkip === 'true';
                const insideAppModal = !!form.closest('[x-on\\:open-modal\\.window]'); // x-modal component wrapper

                if (isGet || isAuth || skipByAttr || insideAppModal) return;

                form.addEventListener('submit', function(e) {
                    if (this.dataset.confirmed === 'true') return;
                    e.preventDefault();

                    const submitter = e.submitter;
                    const submitterLabel = submitter ? (submitter.dataset.confirmConfirmText || (submitter.textContent || '').trim()) : '';

                    const methodInput = this.querySelector('input[name="_method"]');
                    const methodOverride = methodInput ? (methodInput.value || '').toUpperCase() : '';

                    const actionUrl = this.action || '';
                    const isDelete = methodOverride === 'DELETE';
                    const isRestore = /\/restore\b/i.test(actionUrl);

                    const title =
                        this.dataset.confirmTitle ||
                        (isRestore ? 'Restore Item' : isDelete ? 'Remove Item' : 'Save Changes');

                    const message =
                        this.dataset.confirmMessage ||
                        (isRestore
                            ? 'Restore this item and make it active again?'
                            : isDelete
                                ? 'Are you sure you want to remove this item?'
                                : 'Are you sure you want to save these changes?');

                    const variant =
                        this.dataset.confirmVariant ||
                        (isRestore ? 'success' : isDelete ? 'danger' : 'info');

                    const confirmText =
                        this.dataset.confirmConfirmText ||
                        (submitterLabel ? submitterLabel : (isRestore ? 'Restore' : isDelete ? 'Remove' : 'Save'));

                    const cancelText = this.dataset.confirmCancelText || 'Cancel';

                    dispatchConfirm({
                        title,
                        message,
                        variant,
                        confirmText,
                        cancelText,
                        action: () => {
                            this.dataset.confirmed = 'true';
                            // Prefer requestSubmit so validation + submitter semantics stay correct.
                            if (typeof this.requestSubmit === 'function') {
                                if (submitter) this.requestSubmit(submitter);
                                else this.requestSubmit();
                            } else {
                                this.submit();
                            }
                        }
                    });
                });
            });

            // Intercept links for Edit / Back / Cancel
            document.querySelectorAll('a').forEach(a => {
                const text = a.textContent.trim().toLowerCase();
                const isBack = text === 'back' || text.includes('back to') || text.includes('back');
                const isCancel = text === 'cancel';
                const isEdit = text === 'edit' || /\/edit\b/i.test(a.href);

                // Skip tabs, empty links, or profile pages
                if (!a.href || a.href === '#' || a.href.includes('profile')) return;

                if (isBack || isCancel || isEdit) {
                    a.addEventListener('click', function(e) {
                        if (this.dataset.confirmed === 'true') return;
                        e.preventDefault();
                        const href = this.href;

                        const title = this.dataset.confirmTitle ||
                            (isCancel ? 'Cancel Changes' : isBack ? 'Go Back' : 'Edit Item');

                        const message = this.dataset.confirmMessage ||
                            (isCancel
                                ? 'Are you sure you want to cancel? Unsaved changes will be lost.'
                                : isBack
                                    ? 'Are you sure you want to go back? Unsaved changes will be lost.'
                                    : 'Are you sure you want to edit this item?');

                        const variant = this.dataset.confirmVariant ||
                            (isCancel || isBack ? 'danger' : 'info');

                        const confirmText = this.dataset.confirmConfirmText ||
                            (isCancel ? 'Leave' : isBack ? 'Go back' : 'Edit');

                        const cancelText = this.dataset.confirmCancelText || 'Stay';

                        dispatchConfirm({
                            title,
                            message,
                            variant,
                            confirmText,
                            cancelText,
                            action: () => {
                                this.dataset.confirmed = 'true';
                                window.location.href = href;
                            }
                        });
                    });
                }
            });
        });
    </script>
</body>

</html>
