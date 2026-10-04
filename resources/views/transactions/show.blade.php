<x-app-layout>
    <div class="w-full mx-auto space-y-6">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Transaction #{{ $transaction->id }}</h2>
                @if($transaction->receipt_no)
                    <p class="text-sm text-gray-500 dark:text-slate-400">Receipt {{ $transaction->receipt_no }}</p>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('transactions.index') }}"
                    class="px-4 py-2 border border-gray-300 dark:border-slate-500 rounded-lg text-sm font-medium text-gray-700 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                    Back to List
                </a>
                <a href="{{ route('transactions.receipt', $transaction) }}" target="_blank"
                    class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                    Print Receipt
                </a>
                @if(!$transaction->isLocked())
                    <a href="{{ route('transactions.edit', $transaction) }}"
                        class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Edit Transaction
                    </a>
                @elseif($remaining > 0 && $transaction->report_id)
                    <a href="{{ route('transactions.create', ['report_id' => $transaction->report_id]) }}"
                        class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Add Payment
                    </a>
                @else
                    <span class="px-4 py-2 rounded-lg text-sm font-medium text-gray-500 bg-gray-100 dark:bg-slate-700 dark:text-slate-300 border border-gray-200 dark:border-slate-600"
                        title="Payments cannot be edited — add a new payment for remaining balance">
                        Locked
                    </span>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-100 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400">Date Recorded</h3>
                        <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $transaction->created_at->format('M d, Y g:i A') }}
                        </p>
                    </div>
                    <div class="md:text-right">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400">Payment Status</h3>
                        @php
                            $statusClass = match ($transaction->payment_status) {
                                'Paid' => 'bg-green-100 text-green-800 ring-green-600/20',
                                'Unpaid' => 'bg-red-100 text-red-800 ring-red-600/10',
                                'Partial' => 'bg-yellow-100 text-yellow-800 ring-yellow-600/20',
                                default => 'bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-slate-100 ring-gray-500/10',
                            };
                        @endphp
                        <span class="mt-1 inline-flex items-center rounded-md px-3 py-1 text-sm font-medium ring-1 ring-inset {{ $statusClass }}">
                            {{ $transaction->payment_status }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400">Received By</h3>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $transaction->received_by ?? 'System' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400">Payment Method</h3>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $transaction->payment_method ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400">Reference Number</h3>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $transaction->reference_no ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400">Amount Paid (This Payment)</h3>
                        <p class="mt-1 text-sm font-semibold text-emerald-600">₱{{ number_format($transaction->amountPaidThisPayment(), 2) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                    <div class="rounded-lg border border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-slate-700/40 p-4 text-center">
                        <p class="text-xs uppercase text-gray-500 dark:text-slate-400">Bill Total</p>
                        <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">₱{{ number_format($billTotal, 2) }}</p>
                    </div>
                    <div class="rounded-lg border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-4 text-center">
                        <p class="text-xs uppercase text-green-700 dark:text-green-300">Total Paid</p>
                        <p class="mt-1 text-xl font-bold text-green-700 dark:text-green-300">₱{{ number_format($alreadyPaid, 2) }}</p>
                    </div>
                    <div class="rounded-lg border {{ $remaining > 0 ? 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20' : 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20' }} p-4 text-center">
                        <p class="text-xs uppercase {{ $remaining > 0 ? 'text-red-700 dark:text-red-300' : 'text-green-700 dark:text-green-300' }}">
                            {{ $remaining > 0 ? 'Remaining Balance' : 'Fully Paid' }}
                        </p>
                        <p class="mt-1 text-xl font-bold {{ $remaining > 0 ? 'text-red-700 dark:text-red-300' : 'text-green-700 dark:text-green-300' }}">
                            ₱{{ number_format($remaining, 2) }}
                        </p>
                    </div>
                </div>

                @if($remaining > 0 && $transaction->report_id)
                    <div class="mb-8">
                        <a href="{{ route('transactions.create', ['report_id' => $transaction->report_id]) }}"
                            class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                            + Add Another Payment
                        </a>
                    </div>
                @endif

                @if($transaction->report)
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Linked Service Report</h3>
                        <div class="bg-gray-50 dark:bg-slate-700/50 rounded-lg p-4 border border-gray-200 dark:border-slate-600">
                            <div class="flex items-center justify-between flex-wrap gap-3">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ $transaction->report->customer_name }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                                        Report #{{ $transaction->report->id }}
                                        @if($transaction->report->appliance)
                                            — {{ $transaction->report->appliance->product }}
                                        @endif
                                    </p>
                                </div>
                                <a href="{{ route('services.show', $transaction->report) }}"
                                    class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                    View Service Report
                                </a>
                            </div>
                        </div>
                    </div>

                    @if($transaction->report->transactions->count() > 1)
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Payment History</h3>
                            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-slate-600">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-600">
                                    <thead class="bg-gray-50 dark:bg-slate-700/50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-slate-400">Receipt</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-slate-400">Amount</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-slate-400">Status</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-slate-400">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                                        @foreach($transaction->report->transactions->sortBy('created_at') as $pay)
                                            <tr class="{{ $pay->id === $transaction->id ? 'bg-blue-50/60 dark:bg-blue-900/20' : '' }}">
                                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">
                                                    <a href="{{ route('transactions.show', $pay) }}" class="hover:underline">
                                                        {{ $pay->receipt_no ?? '#'.$pay->id }}
                                                    </a>
                                                </td>
                                                <td class="px-4 py-2 text-sm text-right font-medium text-gray-900 dark:text-white">
                                                    ₱{{ number_format($pay->amountPaidThisPayment(), 2) }}
                                                </td>
                                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-slate-300">{{ $pay->payment_status }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-500 dark:text-slate-400">
                                                    {{ $pay->payment_date ? $pay->payment_date->format('M d, Y') : $pay->created_at->format('M d, Y') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                @endif

                @if($transaction->payment_url && $transaction->payment_status !== 'Paid' && $remaining > 0)
                    <hr class="border-gray-100 dark:border-slate-700 mb-8">
                    <div class="max-w-xl mx-auto text-center" x-data="{ copied: false }">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">PayMongo Payment Link</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-slate-400 mb-4">
                            Send this link for the remaining balance.
                        </p>
                        <div class="flex shadow-sm rounded-md">
                            <input type="text" id="payment_url" readonly value="{{ $transaction->payment_url }}"
                                class="block w-full rounded-l-md border-gray-300 bg-gray-50 dark:bg-slate-700/50 text-gray-500 sm:text-sm">
                            <button type="button" @click="navigator.clipboard.writeText(document.getElementById('payment_url').value); copied = true; setTimeout(() => copied = false, 2000);"
                                class="relative -ml-px inline-flex items-center border border-gray-300 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 w-32 justify-center">
                                <span x-text="copied ? 'Copied!' : 'Copy Link'"></span>
                            </button>
                            <a href="{{ $transaction->payment_url }}" target="_blank"
                                class="relative -ml-px inline-flex items-center rounded-r-md border border-gray-300 bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                Open
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
