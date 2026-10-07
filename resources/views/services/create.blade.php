<x-app-layout>
    <script>
        window.serviceFormData = {
            customers: @json($customers),
            techniciansList: @json($technicians),
            applianceTypes: @json($applianceTypes ?? []),
            selectedTechs: @json(old('technicians', [])),
            selectedCustomerId: '{{ old('customer_id') }}',
            selectedApplianceTypeId: '{{ old('appliance_type_id') }}',
        };
        window.saveNewProblem = async function(alpineComponent) {
            if (!(alpineComponent.newProblemName || '').trim()) {
                alert('Please enter a problem name');
                return;
            }
            try {
                const response = await fetch('/common-problems', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        problem_name: alpineComponent.newProblemName.trim(),
                        appliance_type_id: alpineComponent.selectedApplianceTypeId
                    })
                });
                const data = await response.json();
                if (data.success) {
                    const type = alpineComponent.applianceTypes.find(t => t.id == alpineComponent.selectedApplianceTypeId);
                    if (type) {
                        type.common_problems.push(data.problem);
                    }

                    await new Promise(resolve => Alpine.nextTick(resolve));

                    document.querySelectorAll('input[name="common_problems[]"]').forEach(cb => {
                        if (cb.value == data.problem.id) cb.checked = true;
                    });

                    alpineComponent.newProblemName = '';
                    alpineComponent.showAddProblemModal = false;
                } else {
                    alert('Failed to save problem: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                alert('Error saving problem: ' + error.message);
            }
        };
    </script>
    <div class="w-full mx-auto space-y-6" x-data="{
        customers: window.serviceFormData.customers,
        techniciansList: window.serviceFormData.techniciansList,
        applianceTypes: window.serviceFormData.applianceTypes,
        searchTech: '',
        filterTech: '',
        selectedTechs: window.serviceFormData.selectedTechs,
        selectedCustomerId: window.serviceFormData.selectedCustomerId,
        selectedApplianceTypeId: window.serviceFormData.selectedApplianceTypeId,
        otherApplianceTypeName: '',
        applianceTypeWarning: '',
        showAddProblemModal: false,
        newProblemName: '',
        isDirty: false,
        originalValues: {},
        get currentCustomer() {
            return this.customers.find(c => c.id == this.selectedCustomerId) || null;
        },
        get commonProblems() {
            if (!this.selectedApplianceTypeId || this.selectedApplianceTypeId === 'other') return [];
            const type = this.applianceTypes.find(t => t.id == this.selectedApplianceTypeId);
            return type ? type.common_problems : [];
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

                        <!-- Appliance Type -->
                        <div>
                            <label for="appliance_type_id" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Appliance Type</label>
                            <select name="appliance_type_id" id="appliance_type_id" x-model="selectedApplianceTypeId"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">-- Select Appliance Type --</option>
                                @foreach($applianceTypes ?? [] as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                                <option value="other">Other (specify below)</option>
                            </select>
                            @error('appliance_type_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Other Appliance Type (conditional) -->
                        <div x-show="selectedApplianceTypeId === 'other'">
                            <label for="other_appliance_type" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Specify Appliance Type</label>
                            <input type="text" name="other_appliance_type" id="other_appliance_type" value="{{ old('other_appliance_type') }}"
                                x-model="otherApplianceTypeName"
                                @input="applianceTypeWarning = applianceTypes.find(t => t.name.toLowerCase() === otherApplianceTypeName.toLowerCase()) ? 'This appliance type already exists. Please select it from the dropdown.' : ''"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                placeholder="e.g. Microwave Oven">
                            <p x-show="applianceTypeWarning" x-text="applianceTypeWarning" class="mt-1 text-sm text-amber-600"></p>
                            @error('other_appliance_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Brand -->
                        <div>
                            <label for="appliance_brand" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Brand</label>
                            <input type="text" name="appliance_brand" id="appliance_brand" value="{{ old('appliance_brand') }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                placeholder="e.g. Samsung">
                            @error('appliance_brand')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Model Number -->
                        <div>
                            <label for="appliance_model" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Model Number</label>
                            <input type="text" name="appliance_model" id="appliance_model" value="{{ old('appliance_model') }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                placeholder="e.g. AR12TXFYAWK">
                            @error('appliance_model')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Serial Number -->
                        <div>
                            <label for="appliance_serial" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Serial Number</label>
                            <input type="text" name="appliance_serial" id="appliance_serial" value="{{ old('appliance_serial') }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                placeholder="e.g. 1234567890">
                            @error('appliance_serial')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Dealer -->
                        <div>
                            <label for="dealer" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Dealer (Optional)</label>
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

                        <!-- Warranty End Date -->
                        <div>
                            <label for="warranty_end" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Warranty End Date</label>
                            <input type="date" name="warranty_end" id="warranty_end" value="{{ old('warranty_end') }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('warranty_end')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Common Problems -->
                        <div class="md:col-span-2" x-show="selectedApplianceTypeId && selectedApplianceTypeId !== 'other'">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-200">Common Problems</label>
                                <button type="button" @click="showAddProblemModal = true"
                                    class="text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                    + Add New Problem
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-slate-400 mb-3">Select all problems that apply to this appliance</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <template x-for="problem in commonProblems" :key="problem.id">
                                    <label class="flex items-start space-x-3 p-3 border border-gray-200 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700/50 cursor-pointer transition-colors">
                                        <input type="checkbox" name="common_problems[]" :value="problem.id"
                                            class="mt-0.5 rounded border-gray-300 dark:border-slate-500 text-blue-600 dark:text-blue-400 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        <span class="text-sm text-gray-700 dark:text-slate-200" x-text="problem.problem_name"></span>
                                    </label>
                                </template>
                            </div>
                            @error('common_problems')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Notes</label>
                            <textarea id="notes" name="notes" rows="2"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                placeholder="Add any additional notes or customer observations (e.g., customer accidentally wet it)">{{ old('notes') }}</textarea>
                            @error('notes')
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

        <!-- Add New Problem Modal (outside form) -->
        <div x-show="showAddProblemModal" class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">
            <div class="absolute inset-0 bg-black bg-opacity-50" @click="showAddProblemModal = false"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Add New Problem</h3>
                <div class="space-y-4">
                    <div>
                        <label for="new_problem_name" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Problem Name</label>
                        <input type="text" id="new_problem_name" x-model="newProblemName"
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            placeholder="e.g., Compressor clicking">
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" @click="showAddProblemModal = false"
                        class="px-4 py-2 border border-gray-300 dark:border-slate-500 rounded-lg text-sm font-medium text-gray-700 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700">
                        Cancel
                    </button>
                    <button type="button" @click="window.saveNewProblem($data)"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                        Save Problem
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>