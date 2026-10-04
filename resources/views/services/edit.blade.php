<x-app-layout>
    @php
        $catalogPrices = ($servicePrices ?? collect())->values();
        $catalogNames = $catalogPrices->pluck('service_name')->all();
        $oldTypes = old('service_types', $service->details?->service_types ?? []) ?: [];
        if (!is_array($oldTypes)) {
            $oldTypes = [];
        }
        $oldCustom = old('custom_services', $service->details?->custom_services ?? []) ?: [];
        if (!is_array($oldCustom)) {
            $oldCustom = [];
        }
        // Normalize custom rows
        $oldCustom = collect($oldCustom)
            ->filter(fn ($row) => filled(is_array($row) ? ($row['name'] ?? null) : $row))
            ->map(function ($row) {
                if (!is_array($row)) {
                    return ['name' => (string) $row, 'price' => 0];
                }
                return [
                    'name' => trim((string) ($row['name'] ?? '')),
                    'price' => (float) ($row['price'] ?? 0),
                ];
            })
            ->values()
            ->all();

        $customNames = collect($oldCustom)->pluck('name')->all();
        // Recover legacy custom names saved only in service_types
        foreach ($oldTypes as $typeName) {
            $typeName = trim((string) $typeName);
            if ($typeName === '') {
                continue;
            }
            if (!in_array($typeName, $catalogNames, true) && !in_array($typeName, $customNames, true)) {
                $oldCustom[] = ['name' => $typeName, 'price' => 0];
                $customNames[] = $typeName;
            }
        }

        $checkedCatalog = array_values(array_filter($oldTypes, fn ($t) => in_array($t, $catalogNames, true)));
    @endphp
    <div class="w-full mx-auto space-y-6" x-data="{
        customers: {{ Js::from($customers) }},
        parts: {{ Js::from($parts) }},
        servicePrices: {{ Js::from($catalogPrices) }},
        selectedCustomerId: '{{ old('customer_id', $service->customer_id) }}',
        selectedApplianceId: '{{ old('appliance_id', $service->appliance_id) }}',
        selectedParts: [],
        selectedPartId: '',
        partQuantity: 1,
        miscCost: {{ old('miscellaneous_cost', $service->details?->miscellaneous_cost ?? 0) }},
        checkedTypes: {{ Js::from($checkedCatalog) }},
        customServices: {{ Js::from(array_values($oldCustom)) }},
        serviceSearch: '',
        showServiceList: false,
        showCustomForm: false,
        customName: '',
        customPrice: '',
        techniciansList: {{ Js::from($technicians) }},
        searchTech: '',
        filterTech: '',
        selectedTechs: {{ Js::from(old('technicians', $service->details && $service->details->technician ? array_map('trim', explode(',', $service->details->technician)) : [])) }},
        isDirty: false,
        originalValues: {},
        get currentCustomer() {
            return this.customers.find(c => c.id == this.selectedCustomerId) || null;
        },
        get customerAppliances() {
            return this.currentCustomer ? this.currentCustomer.appliances : [];
        },
        get totalPartsCost() {
            return this.selectedParts.reduce((total, part) => {
                if (part.is_not_working) return total;
                return total + (part.price * part.quantity);
            }, 0);
        },
        get uniqueServicePrices() {
            const seen = new Set();
            return this.servicePrices.filter(sp => {
                if (seen.has(sp.service_name)) return false;
                seen.add(sp.service_name);
                return true;
            });
        },
        get filteredServicePrices() {
            const q = (this.serviceSearch || '').toLowerCase().trim();
            return this.uniqueServicePrices.filter(sp => {
                if (this.checkedTypes.includes(sp.service_name)) return false;
                if (!q) return true;
                return sp.service_name.toLowerCase().includes(q);
            });
        },
        get selectedServiceChips() {
            const chips = [];
            this.uniqueServicePrices.forEach(sp => {
                if (this.checkedTypes.includes(sp.service_name)) {
                    chips.push({ key: 'c:' + sp.service_name, kind: 'catalog', name: sp.service_name, price: parseFloat(sp.service_price) });
                }
            });
            this.customServices.forEach((cs, i) => {
                chips.push({ key: 'x:' + i, kind: 'custom', index: i, name: cs.name, price: parseFloat(cs.price) || 0 });
            });
            return chips;
        },
        get computedLabor() {
            let base = 0;
            this.uniqueServicePrices.forEach(sp => {
                if (this.checkedTypes.includes(sp.service_name)) {
                    base += parseFloat(sp.service_price) || 0;
                }
            });
            this.customServices.forEach(cs => {
                base += parseFloat(cs.price) || 0;
            });
            return base;
        },
        addServiceType(name) {
            if (!name || this.checkedTypes.includes(name)) return;
            this.checkedTypes.push(name);
            this.serviceSearch = '';
            this.showServiceList = false;
        },
        removeServiceType(name) {
            const idx = this.checkedTypes.indexOf(name);
            if (idx !== -1) this.checkedTypes.splice(idx, 1);
        },
        toggleServiceType(name) {
            if (this.checkedTypes.includes(name)) this.removeServiceType(name);
            else this.addServiceType(name);
        },
        addCustomService() {
            const name = (this.customName || '').trim();
            const price = parseFloat(this.customPrice);
            if (!name) {
                alert('Enter a custom service name.');
                return;
            }
            if (isNaN(price) || price < 0) {
                alert('Enter a valid price.');
                return;
            }
            const existsCatalog = this.uniqueServicePrices.some(sp => sp.service_name.toLowerCase() === name.toLowerCase());
            if (existsCatalog) {
                alert('That service already exists in the list. Select it from search instead.');
                return;
            }
            const existsCustom = this.customServices.some(cs => (cs.name || '').toLowerCase() === name.toLowerCase());
            if (existsCustom) {
                alert('That custom service is already added.');
                return;
            }
            this.customServices.push({ name, price });
            this.customName = '';
            this.customPrice = '';
            this.showCustomForm = false;
            this.serviceSearch = '';
        },
        removeCustomService(index) {
            this.customServices.splice(index, 1);
        },
        onServiceSearchEnter() {
            if (this.filteredServicePrices.length === 1) {
                this.addServiceType(this.filteredServicePrices[0].service_name);
            } else if (this.serviceSearch.trim() && this.filteredServicePrices.length === 0) {
                this.customName = this.serviceSearch.trim();
                this.showCustomForm = true;
                this.showServiceList = false;
            }
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
            // Edit blade allows changes unless disabled by role, which we handle in the HTML toggle disabling
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
        addPart() {
            if (!this.selectedPartId || this.partQuantity < 1) return;
            const partIndex = this.parts.findIndex(p => p.id == this.selectedPartId);
            if (partIndex === -1) return;
            const part = this.parts[partIndex];
            
            // Check if already in list
            const existingIndex = this.selectedParts.findIndex(p => p.id === part.id);
            if (existingIndex !== -1) {
                this.selectedParts[existingIndex].quantity += parseInt(this.partQuantity);
            } else {
                this.selectedParts.push({
                    id: part.id,
                    name: part.name,
                    part_no: part.part_no,
                    price: parseFloat(part.price),
                    quantity: parseInt(this.partQuantity),
                    is_not_working: false
                });
            }
            this.selectedPartId = '';
            this.partQuantity = 1;
        },
        removePart(id) {
            this.selectedParts = this.selectedParts.filter(p => p.id !== id);
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
            this.$watch('selectedCustomerId', (newVal, oldVal) => {
                if(oldVal && oldVal !== newVal) {
                    this.selectedApplianceId = ''; // Only reset if specifically changed by user, not initial load
                }
            });

            // Re-populate selectedParts from old input or existing service parts
            let oldParts = {{ Js::from(old('parts', $service->parts ?: [])) }};
            if (oldParts.length > 0) {
                oldParts.forEach(oldPart => {
                    const p = this.parts.find(px => px.id == oldPart.id);
                    if (p) {
                         this.selectedParts.push({
                            id: p.id,
                            name: p.name,
                            part_no: p.part_no,
                            price: parseFloat(oldPart.pivot ? oldPart.pivot.price : (oldPart.price || p.price)),
                            quantity: parseInt(oldPart.pivot ? oldPart.pivot.quantity : oldPart.quantity),
                            is_not_working: oldPart.pivot ? (oldPart.pivot.is_not_working === 1 || oldPart.pivot.is_not_working === true) : (oldPart.is_not_working === '1' || oldPart.is_not_working === true)
                        });
                    }
                });
            }
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
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Service Report #{{ $service->id }}</h2>
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
                <form action="{{ route('services.update', $service) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    @php
                    $userRole = auth()->user()->role;
                    $isTech = $userRole === 'Technician';
                    $isSec = $userRole === 'Secretary';

                    $techDisabled = $isTech ? 'disabled' : '';
                    $secDisabled = $isSec ? 'disabled' : '';
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Customer -->
                        <div>
                            <label for="customer_id" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Customer</label>
                            <select name="customer_id" id="customer_id" x-model="selectedCustomerId" required {{ $techDisabled }}
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 dark:bg-slate-700 disabled:cursor-not-allowed">
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
                            <input type="date" name="date_in" id="date_in"
                                value="{{ old('date_in', $service->date_in ? $service->date_in->format('Y-m-d') : '') }}"
                                required {{ $techDisabled }}
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 dark:bg-slate-700 disabled:cursor-not-allowed">
                            @error('date_in')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Appliance -->
                        <div>
                            <label for="appliance_id" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Appliance</label>
                            <select name="appliance_id" id="appliance_id" x-model="selectedApplianceId" required {{ $techDisabled }}
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 dark:bg-slate-700 disabled:cursor-not-allowed"
                                :disabled="!customerAppliances.length || '{{ $techDisabled }}' === 'disabled'">
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
                            <input type="text" name="dealer" id="dealer" value="{{ old('dealer', $service->dealer) }}" {{ $techDisabled }}
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 dark:bg-slate-700 disabled:cursor-not-allowed"
                                placeholder="e.g. SM Appliance">
                            @error('dealer')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Date of Purchase -->
                        <div>
                            <label for="dop" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Date of Purchase</label>
                            <input type="date" name="dop" id="dop"
                                value="{{ old('dop', $service->dop ? $service->dop->format('Y-m-d') : '') }}" {{ $techDisabled }}
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 dark:bg-slate-700 disabled:cursor-not-allowed">
                            @error('dop')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @php
                        $savedTechs = $service->details ? explode(', ', $service->details->technician) : [];
                        $savedServiceTypes = $service->details && $service->details->service_types ? $service->details->service_types : [];
                        @endphp

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
                                            class="block w-full pl-8 pr-3 py-1.5 border border-gray-300 dark:border-slate-500 rounded-md text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-700 dark:text-white placeholder-gray-400 dark:placeholder-slate-400"
                                            {{ $techDisabled }}>
                                    </div>
                                    <select x-model="filterTech" class="block pl-3 pr-8 py-1.5 border border-gray-300 dark:border-slate-500 rounded-md text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-700 dark:text-white" {{ $techDisabled }}>
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
                                    <label class="relative flex items-start p-4 rounded-xl border transition-colors"
                                        :class="[
                                            selectedTechs.includes((tech.first_name + ' ' + (tech.last_name || '')).trim()) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800',
                                            '{{ $techDisabled }}' === 'disabled' ? 'cursor-not-allowed opacity-70' : 'cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700/50'
                                        ]">

                                        <!-- Hidden Input Array for Form Submission and Interaction -->
                                        <input type="checkbox" :value="(tech.first_name + ' ' + (tech.last_name || '')).trim()"
                                            class="hidden"
                                            :checked="selectedTechs.includes((tech.first_name + ' ' + (tech.last_name || '')).trim())"
                                            @click.prevent="if('{{ $techDisabled }}' !== 'disabled') toggleTech((tech.first_name + ' ' + (tech.last_name || '')).trim())"
                                            :disabled="'{{ $techDisabled }}' === 'disabled' || (!selectedTechs.includes((tech.first_name + ' ' + (tech.last_name || '')).trim()) && selectedTechs.length >= 3)" />

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

                        <!-- Service Types (search + chips + custom) -->
                        <div class="md:col-span-2 mt-2">
                            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-1 mb-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-200">Service Types <span class="text-red-500">*</span></label>
                                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Search to add services, or create a custom one if it’s not listed. Technicians only.</p>
                                </div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white tabular-nums"
                                    x-show="selectedServiceChips.length > 0"
                                    x-cloak>
                                    Labor
                                    <span class="text-blue-700 dark:text-blue-400" x-text="'₱' + Number(computedLabor).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                </p>
                            </div>

                            {{-- Hidden fields for submit --}}
                            <template x-for="name in checkedTypes" :key="'st-' + name">
                                <input type="hidden" name="service_types[]" :value="name">
                            </template>
                            <template x-for="(cs, i) in customServices" :key="'cs-' + i">
                                <div>
                                    <input type="hidden" :name="'custom_services[' + i + '][name]'" :value="cs.name">
                                    <input type="hidden" :name="'custom_services[' + i + '][price]'" :value="cs.price">
                                </div>
                            </template>
                            <input type="hidden" name="labor_cost" :value="computedLabor">

                            <div class="rounded-xl border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800/60 shadow-sm overflow-visible {{ $secDisabled ? 'opacity-70 pointer-events-none' : '' }}">
                                {{-- Selected chips --}}
                                <div class="px-4 pt-4 pb-3 border-b border-gray-100 dark:border-slate-700/80">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-slate-500">Selected</span>
                                        <span class="text-[11px] text-gray-400 dark:text-slate-500" x-text="selectedServiceChips.length + (selectedServiceChips.length === 1 ? ' service' : ' services')"></span>
                                    </div>
                                    <template x-if="selectedServiceChips.length === 0">
                                        <p class="text-sm text-gray-400 dark:text-slate-500 py-1">Nothing selected — search below to add a service.</p>
                                    </template>
                                    <div class="flex flex-wrap gap-2" x-show="selectedServiceChips.length > 0">
                                        <template x-for="chip in selectedServiceChips" :key="chip.key">
                                            <span class="inline-flex items-center gap-1.5 max-w-full rounded-lg pl-2.5 pr-1 py-1 text-xs font-medium border"
                                                :class="chip.kind === 'custom'
                                                    ? 'bg-violet-50 text-violet-900 border-violet-200 dark:bg-violet-900/30 dark:text-violet-100 dark:border-violet-700/60'
                                                    : 'bg-sky-50 text-sky-900 border-sky-200 dark:bg-sky-900/30 dark:text-sky-100 dark:border-sky-700/60'">
                                                <span class="truncate" x-text="chip.name"></span>
                                                <span class="opacity-70 whitespace-nowrap tabular-nums" x-text="'₱' + Number(chip.price).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                                @if(!$secDisabled)
                                                <button type="button"
                                                    @click="chip.kind === 'custom' ? removeCustomService(chip.index) : removeServiceType(chip.name)"
                                                    class="ml-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md hover:bg-black/10 dark:hover:bg-white/10 focus:outline-none"
                                                    title="Remove">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                                @endif
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                {{-- Search --}}
                                <div class="px-4 py-3 relative" @click.outside="showServiceList = false">
                                    <label class="block text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-slate-500 mb-1.5">Find a service</label>
                                    <div class="relative">
                                        <div class="pointer inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </div>
                                        <input type="text" x-model="serviceSearch" {{ $secDisabled }}
                                            @focus="showServiceList = true"
                                            @input="showServiceList = true"
                                            @keydown.escape="showServiceList = false"
                                            @keydown.enter.prevent="onServiceSearchEnter()"
                                            placeholder="Type to search (e.g. refrigerator)…"
                                            autocomplete="off"
                                            class="block w-full pl-9 pr-3 py-2.5 text-sm rounded-lg border-gray-300 dark:border-slate-500 bg-gray-50 dark:bg-slate-700/50 focus:ring-blue-500 focus:border-blue-500 disabled:cursor-not-allowed">
                                    </div>

                                    <div x-show="showServiceList" x-cloak
                                        class="absolute z-30 left-4 right-4 mt-1.5 max-h-56 overflow-y-auto rounded-xl border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 shadow-xl">
                                        <template x-if="filteredServicePrices.length === 0">
                                            <div class="px-3 py-3 text-sm text-gray-500 dark:text-slate-400 space-y-2">
                                                <p x-show="serviceSearch.trim()">No match for “<span class="font-medium text-gray-700 dark:text-slate-200" x-text="serviceSearch.trim()"></span>”.</p>
                                                <p x-show="!serviceSearch.trim()">All catalog services are already selected.</p>
                                                <button type="button" x-show="serviceSearch.trim()"
                                                    @click="customName = serviceSearch.trim(); showCustomForm = true; showServiceList = false"
                                                    class="text-sm font-medium text-violet-700 dark:text-violet-300 hover:underline">
                                                    Add as custom service →
                                                </button>
                                            </div>
                                        </template>
                                        <template x-for="sp in filteredServicePrices" :key="sp.id || sp.service_name">
                                            <button type="button"
                                                @click="addServiceType(sp.service_name)"
                                                class="w-full flex items-center justify-between gap-3 px-3 py-2.5 text-left text-sm hover:bg-sky-50 dark:hover:bg-slate-700/80 border-b border-gray-100 dark:border-slate-700 last:border-0 transition-colors">
                                                <span class="font-medium text-gray-800 dark:text-slate-100" x-text="sp.service_name"></span>
                                                <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 whitespace-nowrap tabular-nums"
                                                    x-text="'+₱' + Number(sp.service_price).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                {{-- Custom service --}}
                                <div class="border-t border-gray-100 dark:border-slate-700 bg-slate-50/90 dark:bg-slate-900/40 px-4 py-3">
                                    <button type="button" {{ $secDisabled }}
                                        @click="showCustomForm = !showCustomForm"
                                        class="inline-flex items-center text-sm font-medium text-violet-700 dark:text-violet-300 hover:text-violet-900 dark:hover:text-violet-200">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span x-text="showCustomForm ? 'Hide custom service' : 'Not in the list? Add a custom service'"></span>
                                    </button>

                                    <div x-show="showCustomForm" x-cloak x-transition class="mt-3 rounded-lg border border-violet-200 dark:border-violet-800/50 bg-white dark:bg-slate-800 p-3">
                                        <p class="text-xs text-gray-500 dark:text-slate-400 mb-3">For one-off jobs that aren’t in Service Prices. Name + price are added to labor for this report only.</p>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                            <div class="sm:col-span-2">
                                                <label class="block text-xs font-medium text-gray-600 dark:text-slate-300 mb-1">Service name</label>
                                                <input type="text" x-model="customName" {{ $secDisabled }}
                                                    @keydown.enter.prevent="addCustomService()"
                                                    placeholder="e.g. Ice maker repair"
                                                    class="block w-full text-sm rounded-lg border-gray-300 dark:border-slate-500 bg-white dark:bg-slate-800 focus:ring-violet-500 focus:border-violet-500">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 dark:text-slate-300 mb-1">Price (₱)</label>
                                                <input type="number" step="0.01" min="0" x-model="customPrice" {{ $secDisabled }}
                                                    @keydown.enter.prevent="addCustomService()"
                                                    placeholder="0.00"
                                                    class="block w-full text-sm rounded-lg border-gray-300 dark:border-slate-500 bg-white dark:bg-slate-800 focus:ring-violet-500 focus:border-violet-500">
                                            </div>
                                            <div class="sm:col-span-3 flex justify-end gap-2">
                                                <button type="button" {{ $secDisabled }} @click="showCustomForm = false; customName = ''; customPrice = ''"
                                                    class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700">
                                                    Cancel
                                                </button>
                                                <button type="button" {{ $secDisabled }} @click="addCustomService()"
                                                    class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium text-white bg-violet-600 hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500">
                                                    Add to labor
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($catalogPrices->isEmpty())
                                <p class="mt-2 text-sm text-gray-400 italic">No service prices configured. <a href="{{ route('prices.create') }}" class="text-blue-600 dark:text-blue-400 underline">Add service prices</a> or use a custom service above.</p>
                            @endif
                            @error('service_types')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('custom_services')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('custom_services.*.name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('custom_services.*.price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Problem Description -->
                        <div class="md:col-span-2">
                            <label for="problem_desc" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Problem Description<span class="text-red-500">*</span></label>
                            <textarea id="problem_desc" name="problem_desc" rows="3" required {{ $techDisabled }}
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 dark:bg-slate-700 dark:text-white disabled:cursor-not-allowed">{{ old('problem_desc', $service->details ? $service->details->complaint : '') }}</textarea>
                            @error('problem_desc')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if($service->appliance && $service->appliance->problems && $service->appliance->problems->count() > 0)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-200">Appliance Reported Problems</label>
                                <div class="mt-1 p-3 bg-gray-50 dark:bg-slate-700/50 rounded-lg border border-gray-100 dark:border-slate-700">
                                    @foreach($service->appliance->problems as $problem)
                                        @if($problem->common_problem)
                                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded mr-1 mb-1">{{ $problem->common_problem->problem_name }}</span>
                                        @elseif($problem->other_problem)
                                            <span class="inline-block bg-purple-100 text-purple-800 text-xs px-2 py-0.5 rounded mr-1 mb-1">Other: {{ $problem->other_problem }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Findings -->
                        <div class="md:col-span-2">
                            <label for="findings" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Findings <span class="text-red-500">*</span></label>
                            <textarea id="findings" name="findings" rows="3" required {{ $secDisabled }}
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 dark:bg-slate-700 dark:text-white disabled:cursor-not-allowed">{{ old('findings', $service->findings) }}</textarea>
                            @error('findings')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Remarks -->
                        <div class="md:col-span-2">
                            <label for="remarks" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Remarks <span class="text-red-500">*</span></label>
                            <textarea id="remarks" name="remarks" rows="2" required {{ $secDisabled }}
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 dark:bg-slate-700 dark:text-white disabled:cursor-not-allowed">{{ old('remarks', $service->remarks) }}</textarea>
                            @error('remarks')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Attachments Upload -->
                        <div class="md:col-span-2" x-data="{ 
                            files: [],
                            init() {
                                this.$watch('files', () => {
                                    if(this.files.length === 0) {
                                        document.getElementById('attachments').value = '';
                                    }
                                });
                            },
                            removeFile(index) {
                                this.files.splice(index, 1);
                                
                                // Reconstruct the FileList using DataTransfer
                                const dt = new DataTransfer();
                                const input = document.getElementById('attachments');
                                const { files } = input;
                                
                                for (let i = 0; i < files.length; i++) {
                                    if (i !== index) {
                                        dt.items.add(files[i]);
                                    }
                                }
                                
                                input.files = dt.files;
                            }
                        }">
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Attachments & Files <span class="text-gray-400 text-xs font-normal">Optional (Max 5 files)</span></label>

                            @if (!empty($service->attachments))
                            <div x-data="{ removedExisting: [] }" class="mb-3 p-3 bg-gray-50 dark:bg-slate-700/50 border border-gray-200 dark:border-slate-600 rounded-lg flex flex-col gap-3">
                                <p class="w-full text-xs font-semibold text-gray-600 dark:text-slate-300 mb-1 uppercase tracking-wider">Existing Attachments</p>
                                <div class="flex flex-wrap gap-3">
                                    @foreach($service->attachments as $attachment)
                                    <div class="flex items-center space-x-2 text-sm bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-600 px-3 py-1.5 rounded-md hover:shadow-sm"
                                        x-show="!removedExisting.includes('{{ isset($attachment['path']) ? $attachment['path'] : (isset($attachment['url']) ? $attachment['url'] : '') }}')">
                                        <a href="{{ $attachment['url'] ?? '#' }}" target="_blank" class="flex items-center space-x-2 text-blue-600 hover:text-blue-800">
                                            @if(isset($attachment['resource_type']) && $attachment['resource_type'] === 'image')
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L28 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            @else
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                            </svg>
                                            @endif
                                            <span class="truncate max-w-[150px]">{{ $attachment['original_name'] ?? 'View File' }}</span>
                                        </a>
                                        <button type="button" @click="removedExisting.push('{{ isset($attachment['path']) ? $attachment['path'] : (isset($attachment['url']) ? $attachment['url'] : '') }}')" class="ml-2 text-gray-400 hover:text-red-500 focus:outline-none transition-colors border-l pl-2 border-gray-200 dark:border-slate-600" {{ $secDisabled }}>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    @endforeach
                                </div>
                                <template x-for="path in removedExisting" :key="path">
                                    <input type="hidden" name="remove_attachments[]" :value="path">
                                </template>
                            </div>
                            @endif

                            <div>
                                <label for="attachments" class="inline-flex items-center px-4 py-2 bg-blue-50 border border-blue-200 rounded-lg text-sm font-medium text-blue-700 hover:bg-blue-100 cursor-pointer transition-colors cursor-pointer {{ $secDisabled === 'disabled' ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Add More Files
                                </label>
                                <input type="file" name="attachments[]" id="attachments" multiple accept="image/*,.pdf,.doc,.docx" class="hidden" {{ $secDisabled }}
                                    @change="
                                        let selected = Array.from($event.target.files);
                                        if (selected.length > 5) {
                                            alert('Maximum of 5 files allowed.');
                                            $event.target.value = '';
                                            files = [];
                                        } else {
                                            files = selected.map(f => f.name);
                                        }
                                    ">
                            </div>

                            <div x-show="files.length > 0" class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                <template x-for="(file, index) in files" :key="index">
                                    <div class="flex items-center justify-between text-sm text-gray-600 bg-gray-50 p-2 rounded border border-gray-100 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-300 shadow-sm">
                                        <div class="flex items-center truncate">
                                            <svg class="flex-shrink-0 w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                            </svg>
                                            <span x-text="file" class="truncate"></span>
                                        </div>
                                        <button type="button" @click="removeFile(index)" class="ml-2 text-gray-400 hover:text-red-500 focus:outline-none transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            @error('attachments.*')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Dynamic Used Parts -->
                        <div class="md:col-span-2 bg-gray-50 dark:bg-slate-700/50 p-4 rounded-lg border border-gray-200 dark:border-slate-600">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Parts Used (Inventory)</h3>

                            <div class="flex items-end gap-3 mb-4">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-gray-700 dark:text-slate-200">Select Part</label>
                                    <select x-model="selectedPartId" {{ $secDisabled }} class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 dark:bg-slate-700 disabled:cursor-not-allowed">
                                        <option value="">-- Choose Part --</option>
                                        <template x-for="part in parts" :key="part.id">
                                            <option :value="part.id" x-text="part.part_no + ' - ' + part.name + ' (₱' + part.price + ') - Stock: ' + part.quantity_stock"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="w-24">
                                    <label class="block text-xs font-medium text-gray-700 dark:text-slate-200">Qty</label>
                                    <input type="number" x-model="partQuantity" min="1" {{ $secDisabled }} class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 dark:bg-slate-700 disabled:cursor-not-allowed">
                                </div>
                                <button type="button" @click="addPart" {{ $secDisabled }} class="mb-px px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Add Part
                                </button>
                            </div>

                            <!-- Parts Table -->
                            <div x-show="selectedParts.length > 0" class="mt-4 border border-gray-200 dark:border-slate-600 rounded-md overflow-hidden bg-white dark:bg-slate-800">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50 dark:bg-slate-700/50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-slate-400">Part No.</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-slate-400">Name/Desc</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-slate-400">Price</th>
                                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-slate-400">Qty</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-slate-400">Subtotal</th>
                                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-slate-400">Not Working</th>
                                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-slate-400">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <template x-for="(part, index) in selectedParts" :key="part.id">
                                            <tr>
                                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-white" x-text="part.part_no"></td>
                                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-white" x-text="part.name"></td>
                                                <td class="px-4 py-2 text-sm text-right text-gray-900 dark:text-white" x-text="'₱' + part.price.toFixed(2)"></td>
                                                <td class="px-4 py-2 text-sm text-center text-gray-900 dark:text-white">
                                                    <input type="number" x-model.number="part.quantity" min="1" {{ $secDisabled }} class="w-16 p-1 text-center text-sm border-gray-300 dark:border-slate-500 rounded disabled:bg-gray-100 dark:bg-slate-700 disabled:cursor-not-allowed" @change="$dispatch('input')">
                                                </td>
                                                <td class="px-4 py-2 text-sm text-right text-gray-900 dark:text-white" x-text="'₱' + (part.is_not_working ? '0.00' : (part.price * part.quantity).toFixed(2))"></td>
                                                <td class="px-4 py-2 text-sm text-center text-gray-900 dark:text-white">
                                                    <input type="checkbox" x-model="part.is_not_working" {{ $secDisabled }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed">
                                                </td>
                                                <td class="px-4 py-2 text-sm text-center">
                                                    <button type="button" @click="removePart(part.id)" {{ $secDisabled }} class="text-red-500 hover:text-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                                        <svg class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </td>

                                                <!-- Hidden inputs to submit array -->
                                                <td class="hidden">
                                                    <input type="hidden" :name="'parts['+index+'][id]'" :value="part.id">
                                                    <input type="hidden" :name="'parts['+index+'][quantity]'" :value="part.quantity">
                                                    <input type="hidden" :name="'parts['+index+'][price]'" :value="part.price">
                                                    <input type="hidden" :name="'parts['+index+'][is_not_working]'" :value="part.is_not_working ? '1' : '0'">
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot class="bg-gray-50 dark:bg-slate-700/50 font-semibold">
                                        <tr>
                                            <td colspan="4" class="px-4 py-3 text-right text-sm text-gray-900 dark:text-white">Parts Total:</td>
                                            <td class="px-4 py-3 text-right text-sm text-blue-700 dark:blue-600" x-text="'₱' + totalPartsCost.toFixed(2)"></td>
                                            <td colspan="2" class="px-4 py-3"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Optional manual text just in case they need to write something else that is not in inventory -->
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="used_parts" class="block text-xs font-medium text-gray-700 dark:text-slate-200">Additional Notes / Miscellaneous Not In Inventory</label>
                                    <textarea id="used_parts" name="used_parts" rows="2" {{ $secDisabled }} class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 dark:bg-slate-700 dark:text-white disabled:cursor-not-allowed" placeholder="Any screws, tapes, manual items used...">{{ old('used_parts', $service->used_parts) }}</textarea>
                                </div>
                                <div>
                                    <label for="miscellaneous_cost" class="block text-xs font-medium text-gray-700 dark:text-slate-200">Miscellaneous Cost</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">₱</span>
                                        </div>
                                        <input type="number" name="miscellaneous_cost" id="miscellaneous_cost" step="0.01" min="0" x-model.number="miscCost" {{ $secDisabled }}
                                            class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 sm:text-sm border-gray-300 dark:border-slate-500 rounded-lg disabled:bg-gray-100 dark:bg-slate-700 disabled:cursor-not-allowed" placeholder="0.00">
                                    </div>
                                    @error('miscellaneous_cost')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-4 p-3 bg-blue-50 dark:bg-slate-700/50 rounded-lg border border-blue-100 dark:border-slate-600 flex justify-between items-center text-sm font-medium text-gray-900 dark:text-white mb-1">
                                <span>Total Labor Material Cost (Parts + Misc):</span>
                                <span class="text-lg text-blue-700 dark:text-blue-400 font-bold" x-text="'₱' + (totalPartsCost + (Number(miscCost) || 0)).toFixed(2)"></span>
                            </div>
                        </div>

                        <!-- Labor Cost (auto-computed from service types) -->
                        <div>
                            <label for="labor_cost_display" class="block text-sm font-medium text-gray-700 dark:text-slate-200">
                                Labor Cost <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-slate-400 sm:text-sm">₱</span>
                                </div>
                                <input type="text" id="labor_cost_display" readonly
                                    :value="Number(computedLabor).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"
                                    class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 sm:text-sm border-gray-300 dark:border-slate-500 rounded-lg cursor-default bg-gray-50 dark:bg-slate-700/50">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Auto-calculated from selected + custom services.</p>
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Status</label>
                        <select id="status" name="status" {{ $secDisabled }}
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-slate-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg disabled:bg-gray-100 dark:bg-slate-700 disabled:cursor-not-allowed">
                            <option value="Pending" {{ old('status', $service->status) == 'Pending' ? 'selected' : '' }}>
                                Pending</option>
                            <option value="Waiting for Parts" {{ old('status', $service->status) == 'Waiting for Parts' ? 'selected' : '' }}>Waiting for Parts</option>
                            <option value="Under Repair" {{ old('status', $service->status) == 'Under Repair' ? 'selected' : '' }}>Under Repair</option>
                            <option value="Completed" {{ old('status', $service->status) == 'Completed' ? 'selected' : '' }}>Completed</option>
                            <option value="Cancelled" {{ old('status', $service->status) == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-100 dark:border-slate-700">
                        <a href="{{ route('services.index') }}"
                            @click="isDirty ? $dispatch('open-confirm', { title: 'Unsaved Changes', message: 'You have unsaved changes. Are you sure you want to leave?', confirmText: 'Leave', cancelText: 'Stay', variant: 'warning', action: () => window.location.href = '{{ route('services.index') }}' }) : window.location.href = '{{ route('services.index') }}'"
                            class="px-4 py-2 border border-gray-300 dark:border-slate-500 rounded-lg text-sm font-medium text-gray-700 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:bg-slate-700/50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Update Service Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
</x-app-layout>