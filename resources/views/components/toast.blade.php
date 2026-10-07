<div x-data="{ toast: $store.toast }" class="fixed top-6 right-6 z-[9999] flex flex-col gap-3 pointer-events-none">
    <template x-for="item in toast.items" :key="item.id">
        <div 
            @mouseenter="toast.pause(item.id)"
            @mouseleave="toast.resume(item.id)"
            :class="toast.colors[item.type].bg + ' ' + toast.colors[item.type].border"
            class="pointer-events-auto w-full max-w-md rounded-2xl border shadow-2xl overflow-hidden transform transition-all duration-300"
            role="alert"
            :aria-live="item.type === 'error' ? 'assertive' : 'polite'"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-4 scale-95"
            x-transition:enter-end="opacity-100 translate-x-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0 scale-100"
            x-transition:leave-end="opacity-0 translate-x-4 scale-95">
            
            <!-- Progress Bar -->
            <div class="h-1 bg-gray-200 dark:bg-gray-700">
                <div 
                    :class="toast.colors[item.type].progress"
                    :style="`width: ${(item.remaining / item.duration) * 100}%`"
                    class="h-full transition-all duration-100 ease-linear">
                </div>
            </div>

            <div class="p-4">
                <div class="flex items-start gap-3">
                    <!-- Icon -->
                    <div 
                        :class="toast.colors[item.type].iconBg + ' ' + toast.colors[item.type].iconText"
                        class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center">
                        <div x-html="toast.icon[item.type]"></div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <h4 
                            :class="toast.colors[item.type].title"
                            class="text-sm font-semibold mb-1"
                            x-text="item.title">
                        </h4>
                        <p 
                            :class="toast.colors[item.type].message"
                            class="text-sm leading-relaxed"
                            x-text="item.message">
                        </p>
                    </div>

                    <!-- Close Button -->
                    <button 
                        @click="toast.remove(item.id)"
                        :class="toast.colors[item.type].iconText"
                        class="flex-shrink-0 inline-flex items-center justify-center w-8 h-8 rounded-lg hover:bg-black/5 dark:hover:bg-white/10 transition-colors"
                        aria-label="Close">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
