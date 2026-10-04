<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', '101 Repair Service') }} - Login</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            background:
                radial-gradient(1200px 500px at 10% -10%, rgba(59, 130, 246, 0.18), transparent 55%),
                radial-gradient(900px 400px at 100% 0%, rgba(14, 165, 233, 0.12), transparent 50%),
                linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
            background-attachment: fixed;
        }

        [x-cloak] {
            display: none !important;
        }

        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }

        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <script>
        document.documentElement.classList.remove('dark');
    </script>
</head>

<body class="antialiased text-slate-900 flex items-center justify-center min-h-screen p-4 sm:p-8">

    <div class="w-full max-w-5xl bg-white rounded-3xl shadow-[0_24px_60px_-20px_rgba(15,23,42,0.25)] overflow-hidden flex flex-col md:flex-row min-h-[560px] ring-1 ring-slate-200/80 fade-in">

        <!-- Left Pane: Login Form -->
        <div class="w-full md:w-1/2 p-8 sm:p-12 lg:p-14 flex flex-col justify-center bg-white">
            <div class="max-w-md w-full mx-auto">
                <div class="mb-2 text-center">

    <img src="{{ asset('img/repairservicelogoblue.png') }}"
        alt="101 Repair Shop Logo"
        class="h-32 md:h-38 object-contain mx-auto -mb-2">

    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">
        Sign in
    </h2>

    <p class="mt-1 text-sm text-slate-500">
        Access the 101 Repair Service workspace
    </p>

</div>


                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                </svg>
                            </div>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                class="block w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl bg-slate-50/80 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 sm:text-sm placeholder-slate-400 transition"
                                placeholder="Enter your email">
                        </div>
                        <x-input-error :messages="$errors->get('email')"
                            class="mt-2 text-red-600 text-xs font-medium" />
                    </div>

                    <div x-data="{ showPassword: false }">
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                        <div class="relative">
                            <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required
                                autocomplete="current-password"
                                class="block w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50/80 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 sm:text-sm placeholder-slate-400 transition pr-11"
                                placeholder="Enter your password">
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                <svg x-show="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg x-show="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')"
                            class="mt-2 text-red-600 text-xs font-medium" />
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center">
                            <input id="remember_me" type="checkbox" name="remember"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded cursor-pointer">
                            <label for="remember_me"
                                class="ml-2 block text-sm text-slate-600 cursor-pointer select-none">Remember me</label>
                        </div>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="text-sm font-semibold text-blue-600 hover:text-blue-500 transition-colors">
                                Forgot Password?
                            </a>
                        @endif
                    </div>

                    <div class="pt-1">
                        <button type="submit"
                            class="w-full flex justify-center py-3 px-4 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-600/25 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600 transition-colors">
                            Sign in
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Pane: Image Carousel -->
        <div class="w-full md:w-1/2 relative hidden md:block" x-data="{
                activeSlide: 1,
                slides: [
                    '{{ asset('img/slider/hero-1.jpg') }}',
                    '{{ asset('img/slider/hero-2.jpg') }}'
                ],
                init() {
                    setInterval(() => {
                        this.activeSlide = this.activeSlide === this.slides.length ? 1 : this.activeSlide + 1;
                    }, 5000);
                }
             }">

            <template x-for="(slide, index) in slides" :key="index">
                <img :src="slide" alt="Repair Shop Work Area"
                    class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 ease-in-out"
                    :class="activeSlide === index + 1 ? 'opacity-100' : 'opacity-0'" x-cloak>
            </template>

            <div class="absolute inset-0 bg-gradient-to-t from-[#0b1220] via-[#1e3a8a]/75 to-[#1e3a8a]/35 z-10"></div>

            <div class="absolute inset-0 flex flex-col justify-end p-10 text-white z-20">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-200 mb-2">101 Repair Service</p>
                <h3 class="text-3xl font-bold mb-2 leading-tight">Welcome back</h3>
                <p class="text-white/85 text-base max-w-sm">Manage customers, jobs, parts, and payments from one workspace.</p>

                <div class="mt-6 flex justify-start w-full pb-1">
                    <div class="flex space-x-2">
                        <template x-for="i in slides.length">
                            <button @click="activeSlide = i"
                                class="h-2 rounded-full transition-all duration-300 focus:outline-none"
                                :class="activeSlide === i ? 'w-8 bg-white' : 'w-2 bg-white/50 hover:bg-white/70'"></button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

    </div>

</body>

</html>
