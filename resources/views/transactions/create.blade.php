<x-app-layout>
    <div class="w-full mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Add Payment</h2>
            @if(request('report_id'))
                <a href="{{ route('services.show', request('report_id')) }}"
                    class="text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-white flex items-center transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Service Report
                </a>
            @else
                <a href="{{ route('transactions.index') }}"
                    class="text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-white flex items-center transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to List
                </a>
            @endif
        </div>

        @if(session('error'))
            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-100 dark:border-slate-700 shadow-sm overflow-hidden" x-data="{
            reports: {{ Js::from($reportsPayload) }},
            selectedReportId: '{{ old('report_id', request('report_id')) }}',
            labor: {{ old('labor', 0) }},
            materials: {{ old('materials', 0) }},
            miscellaneous: {{ old('miscellaneous', 0) }},
            delivery: {{ old('delivery', 0) }},
            payment_status: '{{ old('payment_status', 'Paid') }}',
            amount_paid: '{{ old('amount_paid', old('partial_payment_amount', '')) }}',
            total_amount: 0,
            already_paid: 0,
            remaining: 0,
            has_payments: false,
            calculateTotal() {
                this.total_amount = ((parseFloat(this.labor) || 0) + (parseFloat(this.materials) || 0) + (parseFloat(this.miscellaneous) || 0) + (parseFloat(this.delivery) || 0)).toFixed(2);
                if (!this.has_payments) {
                    this.remaining = parseFloat(this.total_amount) || 0;
                }
            },
            loadReport(value) {
                const report = this.reports.find(r => r.id == value);
                if (report) {
                    this.labor = report.labor;
                    this.materials = report.materials;
                    this.miscellaneous = report.miscellaneous || 0;
                    this.delivery = report.delivery;
                    this.has_payments = !!report.has_payments;
                    this.already_paid = report.already_paid || 0;
                    if (report.has_payments && report.bill_total > 0) {
                        this.total_amount = Number(report.bill_total).toFixed(2);
                        this.remaining = Number(report.remaining).toFixed(2);
                    } else {
                        this.calculateTotal();
                        this.remaining = this.total_amount;
                    }
                    if (this.payment_status === 'Paid') {
                        this.amount_paid = this.remaining;
                    }
                } else {
                    this.labor = 0;
                    this.materials = 0;
                    this.miscellaneous = 0;
                    this.delivery = 0;
                    this.has_payments = false;
                    this.already_paid = 0;
                    this.remaining = 0;
                    this.calculateTotal();
                }
            },
            init() {
                this.$watch('selectedReportId', (value) => this.loadReport(value));
                this.$watch('labor', () => { if (!this.has_payments) this.calculateTotal(); });
                this.$watch('materials', () => { if (!this.has_payments) this.calculateTotal(); });
                this.$watch('miscellaneous', () => { if (!this.has_payments) this.calculateTotal(); });
                this.$watch('delivery', () => { if (!this.has_payments) this.calculateTotal(); });
                this.$watch('payment_status', (status) => {
                    if (status === 'Paid') {
                        this.amount_paid = this.remaining;
                    } else if (status === 'Unpaid') {
                        this.amount_paid = 0;
                    }
                });
                if (this.selectedReportId) {
                    this.loadReport(this.selectedReportId);
                } else {
                    this.calculateTotal();
                }
            }
        }">
            <div class="p-6">
                <form action="{{ route('transactions.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="report_id" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Customer / Service Report</label>
                        <select id="report_id" name="report_id" x-model="selectedReportId" required
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-slate-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg">
                            <option value="">Select unpaid or partially paid job</option>
                            @foreach($reports as $report)
                                @php
                                    $bill = (float) ($report->details->total_amount ?? 0);
                                    $paid = $report->transactions->sum(fn ($t) => $t->amountPaidThisPayment());
                                    $remain = max(0, $bill - $paid);
                                @endphp
                                <option value="{{ $report->id }}">
                                    #{{ $report->id }} — {{ $report->customer_name }}
                                    ({{ $report->appliance ? $report->appliance->product : 'N/A' }})
                                    @if($report->transactions->isNotEmpty())
                                        — Balance ₱{{ number_format($remain, 2) }}
                                    @else
                                        — No payment yet
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-sm text-gray-500 dark:text-slate-400">Shows Completed jobs that are unpaid or still have a remaining balance.</p>
                        @error('report_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-show="has_payments" x-cloak
                        class="rounded-lg border border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-800 p-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs font-medium text-amber-800 dark:text-amber-300 uppercase">Bill Total</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">₱<span x-text="Number(total_amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-amber-800 dark:text-amber-300 uppercase">Already Paid</p>
                            <p class="text-lg font-bold text-green-600">₱<span x-text="Number(already_paid).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-amber-800 dark:text-amber-300 uppercase">Remaining</p>
                            <p class="text-lg font-bold text-red-600">₱<span x-text="Number(remaining).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6" x-show="!has_payments">
                        <div>
                            <label for="labor" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Labor Cost</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-slate-400 sm:text-sm">₱</span>
                                </div>
                                <input type="number" name="labor" id="labor" step="0.01" x-model="labor"
                                    :required="!has_payments"
                                    class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 sm:text-sm border-gray-300 dark:border-slate-500 rounded-lg"
                                    placeholder="0.00">
                            </div>
                            @error('labor')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="materials" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Materials (Parts)</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-slate-400 sm:text-sm">₱</span>
                                </div>
                                <input type="number" name="materials" id="materials" step="0.01" x-model="materials"
                                    :required="!has_payments"
                                    class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 sm:text-sm border-gray-300 dark:border-slate-500 rounded-lg"
                                    placeholder="0.00">
                            </div>
                            @error('materials')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="miscellaneous" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Miscellaneous</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-slate-400 sm:text-sm">₱</span>
                                </div>
                                <input type="number" name="miscellaneous" id="miscellaneous" step="0.01" x-model="miscellaneous"
                                    class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 sm:text-sm border-gray-300 dark:border-slate-500 rounded-lg"
                                    placeholder="0.00">
                            </div>
                            @error('miscellaneous')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="delivery" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Delivery Cost</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-slate-400 sm:text-sm">₱</span>
                                </div>
                                <input type="number" name="delivery" id="delivery" step="0.01" x-model="delivery"
                                    class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 sm:text-sm border-gray-300 dark:border-slate-500 rounded-lg"
                                    placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <div x-show="!has_payments">
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-200">Total Amount</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 dark:text-slate-400 sm:text-sm font-bold">₱</span>
                            </div>
                            <input type="number" step="0.01" x-model="total_amount" readonly
                                class="font-bold bg-gray-50 dark:bg-slate-700/50 focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 sm:text-sm border-gray-300 dark:border-slate-500 rounded-lg"
                                placeholder="0.00">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="payment_status" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Payment Status</label>
                            <select id="payment_status" name="payment_status" x-model="payment_status"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-slate-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg">
                                <option value="Paid">Paid (Full / Remaining)</option>
                                <option value="Partial">Partial</option>
                                <option value="Unpaid">Unpaid</option>
                            </select>
                        </div>

                        <div x-show="payment_status === 'Partial' || payment_status === 'Paid'" x-cloak>
                            <label for="amount_paid" class="block text-sm font-medium text-gray-700 dark:text-slate-200">
                                Amount Paid Now
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-slate-400 sm:text-sm">₱</span>
                                </div>
                                <input type="number" name="amount_paid" id="amount_paid" step="0.01" x-model="amount_paid"
                                    :readonly="payment_status === 'Paid'"
                                    :max="remaining"
                                    class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 sm:text-sm border-gray-300 dark:border-slate-500 rounded-lg"
                                    placeholder="0.00">
                            </div>
                            <p class="mt-1 text-xs text-gray-500" x-show="payment_status === 'Partial'">
                                Remaining after this payment: ₱<span x-text="Math.max(0, (parseFloat(remaining)||0) - (parseFloat(amount_paid)||0)).toFixed(2)"></span>
                            </p>
                            @error('amount_paid')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="payment_date" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Payment Date</label>
                            <input type="date" name="payment_date" id="payment_date"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 dark:border-slate-500 rounded-lg"
                                value="{{ old('payment_date', date('Y-m-d')) }}">
                        </div>

                        <div>
                            <label for="payment_due" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Payment Due</label>
                            <input type="date" name="payment_due" id="payment_due"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 dark:border-slate-500 rounded-lg"
                                value="{{ old('payment_due') }}">
                        </div>

                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Payment Method</label>
                            <select id="payment_method" name="payment_method"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-slate-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg">
                                <option value="">Select Method</option>
                                <option value="Cash" {{ old('payment_method') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                <option value="GCash" {{ old('payment_method') == 'GCash' ? 'selected' : '' }}>GCash</option>
                                <option value="PayMaya" {{ old('payment_method') == 'PayMaya' ? 'selected' : '' }}>PayMaya</option>
                                <option value="Bank Transfer" {{ old('payment_method') == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="Check" {{ old('payment_method') == 'Check' ? 'selected' : '' }}>Check</option>
                            </select>
                        </div>

                        <div>
                            <label for="reference_no" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Reference Number <span class="text-gray-400 text-xs">(If applicable)</span></label>
                            <input type="text" name="reference_no" id="reference_no"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 dark:border-slate-500 rounded-lg"
                                placeholder="e.g. 10023940192" value="{{ old('reference_no') }}">
                        </div>

                        <div>
                            <label for="received_by" class="block text-sm font-medium text-gray-700 dark:text-slate-200">Received By</label>
                            <input type="text" name="received_by" id="received_by"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 dark:border-slate-500 rounded-lg"
                                value="{{ old('received_by', auth()->user()->first_name . ' ' . auth()->user()->last_name) }}">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-100 dark:border-slate-700">
                        <a href="{{ request('report_id') ? route('services.show', request('report_id')) : route('transactions.index') }}"
                            class="px-4 py-2 border border-gray-300 dark:border-slate-500 rounded-lg text-sm font-medium text-gray-700 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                            Save Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
