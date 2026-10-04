<x-app-layout>
    <div class="w-full mx-auto space-y-6" x-data="{
        customers: {{ Js::from($customers) }},
        techniciansList: {{ Js::from($technicians) }},
        searchTech: '',
        filterTech: '',
        selectedTechs: {{ Js::from(old('technicians', [])) }},
        selectedCustomerId: '{{ old('customer_id') }}',
        selectedApplianceId: '{{ old('appliance_id') }}',
        isDirty: false,
        originalValues: {},
        get currentCustomer() {
            return this.customers.find(c => c.id == this.selectedCustomerId) || null;
        },
        get customerAppliances() {
            return this.currentCustomer ? this.currentCustomer.appliances : [];
        },
        get selectedAppliance() {
            if (!this.selectedApplianceId) return null;
            return this.customerAppliances.find(a => a.id == this.selectedApplianceId) || null;
        },
        get filteredTechnicians() {
            let filtered = this.techniciansList;
            if (this.filterTech !== '') {
                filtered = filtered.filter(t => (t.availability_status || '').toLowerCase() === this.filterTech.toLowerCase());
            }
            if (this.searchTech.trim() !== '') {
                let s = this.searchTech.toLowerCase();
                filtered = filtered.filter(t =>
                    ((t.first_name || '') + ' ' + (t.last_name || '')).toLowerCase().includes(s) ||
                    (t.role_title || '').toLowerCase().includes(s)
                );
            }
            return filtered;
        },
        toggleTech(name) {
            let idx = this.selectedTechs.indexOf(name);
            if (idx === -1) {
                if (this.selectedTechs.length < 3) {
                    this.selectedTechs.push(name);
                } else {
                    alert('You can only assign a maximum of 3 technicians.');
                }
            } else {
                this.selectedTechs.splice(idx, 1);
            }
        },
        getInitials(firstName, lastName) {
            let f = (firstName || '').charAt(0);
            let l = (lastName || '').charAt(0);
            return (f + l).toUpperCase() || '?';
        },
        init() {
            this.$nextTick(() => {
                const form = this.$el.querySelector('form');
                if (form) {
                    const inputs = form.querySelectorAll('input:not([type=hidden]), textarea, select');
                    inputs.forEach(input => {
                        if (input.name) {
                            this.originalValues[input.name] = input.value;
                        }
                    });
                }
            });
            this.$watch('selectedCustomerId', () => {
                this.selectedApplianceId = '';
                document.getElementById('dealer').value = '';
                document.getElementById('dop').value = '';
            });
            this.$watch('selectedApplianceId', () => {
                if (this.selectedAppliance) {
                    // Auto-fill dealer from appliance
                    document.getElementById('dealer').value = this.selectedAppliance.dealer || '';
                    document.getElementById('dop').value = this.selectedAppliance.date_in || '';
                    // Auto-fill problem description with customer's reported problems
                    if (this.selectedAppliance.problems && this.selectedAppliance.problems.length > 0) {
                        let problems = this.selectedAppliance.problems.map(p => {
                            if (p.common_problem) {
                                return p.common_problem.problem_name;
                            } else if (p.other_problem) {
                                return 'Other: ' + p.other_problem;
                            }
                            return '';
                        }).filter(p => p).join(', ');
                        document.getElementById('problem_desc').value = problems;
                    }
                }
            });
        },
        checkDirty() {
            const form = this.$el.querySelector('form');
            if (form) {
                const inputs = form.querySelectorAll('input:not([type=hidden]), textarea, select');
                this.isDirty = false;
                inputs.forEach(input => {
                    if (input.name && this.originalValues[input.name] !== undefined) {
                        if (input.value !== this.originalValues[input.name]) {
                            this.isDirty = true;
                        }
                    }
                });
            }
            return this.isDirty;
        }
    }">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Service Report</h2>
            <a href="{{ route('services.index') }}"
                @click="isDirty ? $dispatch('open-confirm', { title: 'Unsaved Changes', message: 'You have unsaved changes. Are you sure you want to leave?', confirmText: 'Leave', cancelText: 'Stay', variant: 'warning', action: () => window.location.href = '{{ route('services.index') }}' }) : window.location.href = '{{ route('services.index') }}'"
                class="text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-white flex items-center transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to List
            </a>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-100 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="p-6">
                <form action="{{ route('services.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Customer -->
                        <div>
                            <label for="customer_id" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Customer</label>
                            <select name="customer_id" id="customer_id" x-model="selectedCustomerId" required
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">-- Select Customer --</option>
                                <template x-for="customer in customers" :key="customer.id">
                                    <option :value="customer.id"
                                        x-text="customer.first_name + ' ' + (customer.last_name || '') + (customer.email ? ' ('+customer.email+')' : '')"
                                        :selected="customer.id == selectedCustomerId"></option>
                                </template>
                            </select>
                            @error('customer_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Date Received -> Repair Date -->
                        <div>
                            <label for="date_in" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Repair Date</label>
                            <input type="date" name="date_in" id="date_in" value="{{ old('date_in', date('Y-m-d')) }}"
                                required
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('date_in')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Appliance -->
                        <div>
                            <label for="appliance_id" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Appliance</label>
                            <select name="appliance_id" id="appliance_id" x-model="selectedApplianceId" required
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                :disabled="!customerAppliances.length">
                                <option value="">-- Select Appliance --</option>
                                <template x-for="app in customerAppliances" :key="app.id">
                                    <option :value="app.id"
                                        x-text="app.product + ' - ' + app.brand + (app.model_no ? ' ('+app.model_no+')' : '')">
                                    </option>
                                </template>
                            </select>
                            <p x-show="selectedCustomerId && !customerAppliances.length"
                                class="text-xs text-red-500 mt-1">This customer has no appliances. Please add one in
                                their profile first.</p>
                            @error('appliance_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Dealer -->
                        <div>
                            <label for="dealer" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Dealer</label>
                            <input type="text" name="dealer" id="dealer" value="{{ old('dealer') }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                placeholder="e.g. SM Appliance">
                            @error('dealer')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Date of Purchase -->
                        <div>
                            <label for="dop" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Date of Purchase</label>
                            <input type="date" name="dop" id="dop" value="{{ old('dop') }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('dop')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Assigned Technicians -->
                        <div class="md:col-span-2 mt-4">
                            <div class="flex items-center justify-between mb-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-200">
                                    Assigned Technicians (<span x-text="selectedTechs.length"></span>/3)
                                </label>

                                <!-- Search & Filter Controls -->
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                        </div>
                                        <input type="text" x-model="searchTech" placeholder="Search technician..."
                                            class="block w-full pl-8 pr-3 py-1.5 border border-gray-300 dark:border-slate-500 rounded-md text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-700 dark:text-white placeholder-gray-400 dark:placeholder-slate-400">
                                    </div>
                                    <select x-model="filterTech" class="block pl-3 pr-8 py-1.5 border border-gray-300 dark:border-slate-500 rounded-md text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-700 dark:text-white">
                                        <option value="">All Statuses</option>
                                        <option value="Available">Available</option>
                                        <option value="Busy">Busy</option>
                                        <option value="Off-Duty">Off-Duty</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Grid of Technicians -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-80 overflow-y-auto p-1">
                                <template x-for="tech in filteredTechnicians" :key="tech.id">
                                    <label class="relative flex items-start p-4 rounded-xl border cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors"
                                        :class="selectedTechs.includes((tech.first_name + ' ' + (tech.last_name || '')).trim()) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800'">

                                        <!-- Hidden Input Array for Form Submission and Interaction -->
                                        <input type="checkbox" :value="(tech.first_name + ' ' + (tech.last_name || '')).trim()"
                                            class="hidden"
                                            :checked="selectedTechs.includes((tech.first_name + ' ' + (tech.last_name || '')).trim())"
                                            @click.prevent="toggleTech((tech.first_name + ' ' + (tech.last_name || '')).trim())"
                                            :disabled="!selectedTechs.includes((tech.first_name + ' ' + (tech.last_name || '')).trim()) && selectedTechs.length >= 3" />

                                        <!-- Tech Info Profile -->
                                        <div class="ml-3 flex-1 flex flex-col justify-center">
                                            <div class="flex items-center gap-3">
                                                <div class="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center flex-shrink-0">
                                                    <span class="text-xs font-semibold text-blue-700 dark:text-blue-300" x-text="getInitials(tech.first_name, tech.last_name)"></span>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-semibold text-gray-900 dark:text-white" x-text="(tech.first_name + ' ' + (tech.last_name || '')).trim()"></div>
                                                    <div class="flex items-center gap-2 mt-0.5">
                                                        <span class="text-xs text-gray-500 dark:text-slate-400" x-text="tech.role_title || 'Technician'"></span>

                                                        <!-- Status Badges -->
                                                        <span x-show="(tech.availability_status || '').toLowerCase() === 'available'" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800/50">
                                                            Available
                                                        </span>
                                                        <span x-show="(tech.availability_status || '').toLowerCase() === 'busy'" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800/50">
                                                            Busy
                                                        </span>
                                                        <span x-show="(tech.availability_status || '').toLowerCase() === 'off-duty'" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-800 dark:bg-gray-700/50 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                                            Off-Duty
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </template>
                            </div>
                            <!-- Fallback empty state -->
                            <div x-show="filteredTechnicians.length === 0" class="py-4 text-center text-sm text-gray-500 dark:text-slate-400 italic">
                                No technicians match your search filters.
                            </div>

                            <!-- Native Form Submission explicit syncing -->
                            <template x-for="t in selectedTechs">
                                <input type="hidden" name="technicians[]" :value="t">
                            </template>

                            @error('technicians')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Problem Description (from customer's appliance problems) -->
                        <div class="md:col-span-2">
                            <label for="problem_desc" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Problem Description (from Customer)<span class="text-red-500">*</span></label>
                            <textarea id="problem_desc" name="problem_desc" rows="3" required
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                placeholder="This will be auto-filled from the customer's appliance problems">{{ old('problem_desc') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-slate-400">This field is auto-populated from the customer's reported problems. You can edit if needed.</p>
                            @error('problem_desc')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Status</label>
                            <select id="status" name="status"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-slate-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg">
                                <option value="Pending" {{ old('status') == 'Pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="Waiting for Parts" {{ old('status') == 'Waiting for Parts' ? 'selected' : '' }}>Waiting for Parts</option>
                                <option value="Under Repair" {{ old('status') == 'Under Repair' ? 'selected' : '' }}>Under
                                    Repair</option>
                                <option value="Completed" {{ old('status') == 'Completed' ? 'selected' : '' }}>Completed
                                </option>
                                <option value="Cancelled" {{ old('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled
                                </option>
                            </select>
                            @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-100 dark:border-slate-700">
                        <a href="{{ route('services.index') }}"
                            @click="isDirty ? $dispatch('open-confirm', { title: 'Unsaved Changes', message: 'You have unsaved changes. Are you sure you want to leave?', confirmText: 'Leave', cancelText: 'Stay', variant: 'warning', action: () => window.location.href = '{{ route('services.index') }}' }) : window.location.href = '{{ route('services.index') }}'"
                            class="px-4 py-2 border border-gray-300 dark:border-slate-500 rounded-lg text-sm font-medium text-gray-700 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:bg-slate-700/50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                            Create Service Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>