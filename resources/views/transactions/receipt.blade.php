<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $transaction->receipt_no ?? '#'.$transaction->id }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Libre+Barcode+39&family=Source+Sans+3:wght@400;600;700&display=swap');

        :root {
            --ink: #1a1a1a;
            --muted: #5c5c5c;
            --line: #d4d4d4;
            --accent: #0b3d91;
            --paper: #fffef8;
            --receipt-width: 80mm;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
        }

        body {
            background: #e8e6e1;
            color: var(--ink);
            font-family: 'Source Sans 3', 'Segoe UI', sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .toolbar {
            width: var(--receipt-width);
            margin: 16px auto;
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .toolbar button,
        .toolbar a {
            border: 0;
            border-radius: 6px;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            color: #fff;
            background: var(--accent);
        }

        .toolbar .secondary {
            background: #4b5563;
        }

        /* Same width on screen and in print so the preview matches the paper */
        .receipt {
            width: var(--receipt-width);
            margin: 0 auto 40px;
            background: var(--paper);
            box-shadow: 0 12px 40px rgba(0,0,0,.12);
            padding: 8mm 6mm 8mm;
            position: relative;
            overflow: hidden;
        }

        .receipt::before {
            content: '';
            position: absolute;
            inset: 4px;
            border: 1px solid var(--line);
            pointer-events: none;
        }

        .brand {
            text-align: center;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--ink);
            margin-bottom: 12px;
        }

        .brand .logo-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 0 8px;
        }

        .brand .logo {
            display: block;
            width: 120px;
            max-width: 70%;
            height: auto;
            margin: 0 auto;
            object-fit: contain;
        }

        .brand .shop-name {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--accent);
            margin: 0;
            line-height: 1.2;
        }

        .brand .tagline {
            margin: 6px 0 0;
            font-size: 10px;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .brand .address {
            margin: 8px 0 0;
            font-size: 11px;
            color: var(--muted);
            line-height: 1.45;
        }

        .doc-title {
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .2em;
            text-transform: uppercase;
            margin: 10px 0 14px;
        }

        .meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 10px;
            font-size: 12px;
            margin-bottom: 12px;
        }

        .meta > div { min-width: 0; }

        .meta .label {
            display: block;
            color: var(--muted);
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .meta .value {
            font-family: 'IBM Plex Mono', monospace;
            font-weight: 500;
            font-size: 11px;
            word-break: break-all;
        }

        .section-head {
            margin: 14px 0 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            border-bottom: 1px dashed var(--line);
            padding-bottom: 4px;
        }

        .info-line {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: 12px;
            padding: 3px 0;
        }

        .info-line span:first-child { color: var(--muted); flex-shrink: 0; }
        .info-line span:last-child { font-weight: 600; text-align: right; word-break: break-word; }

        table.lines {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 4px;
        }

        table.lines th {
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--muted);
            border-bottom: 1px solid var(--ink);
            padding: 4px 0;
        }

        table.lines th.num,
        table.lines td.num {
            text-align: right;
        }

        table.lines td {
            padding: 6px 0;
            border-bottom: 1px dotted var(--line);
            vertical-align: top;
        }

        .totals {
            margin-top: 12px;
            border-top: 2px solid var(--ink);
            padding-top: 8px;
        }

        .totals .row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            padding: 3px 0;
        }

        .totals .row.grand {
            font-size: 15px;
            font-weight: 700;
            margin-top: 6px;
            padding-top: 8px;
            border-top: 1px dashed var(--line);
        }

        .totals .row.paid { color: #166534; }
        .totals .row.balance { color: #b91c1c; font-weight: 700; }
        .totals .row.clear { color: #166534; font-weight: 700; }

        .status-pill {
            display: inline-block;
            margin-top: 10px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            border: 1px solid currentColor;
        }

        .status-paid { color: #166534; background: #dcfce7; }
        .status-partial { color: #a16207; background: #fef9c3; }
        .status-unpaid { color: #b91c1c; background: #fee2e2; }

        .footer {
            margin-top: 18px;
            text-align: center;
            border-top: 1px dashed var(--line);
            padding-top: 12px;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        /* Barcode is auto-shrunk by script so it never overflows the receipt */
        .barcode-wrap {
            width: 100%;
            overflow: hidden;
        }

        .barcode {
            display: block;
            white-space: nowrap;
            font-family: 'Libre Barcode 39', cursive;
            font-size: 36px;
            line-height: 1.1;
            margin: 4px 0 0;
            text-align: center;
        }

        .barcode-text {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 10px;
            letter-spacing: .08em;
            color: var(--muted);
            margin: 2px 0 6px;
        }

        .footer p {
            margin: 4px 0;
            font-size: 10.5px;
            color: var(--muted);
        }

        .thanks {
            font-size: 12px;
            font-weight: 700;
            color: var(--ink) !important;
            margin-top: 8px !important;
        }

        .cut {
            text-align: center;
            color: var(--muted);
            font-size: 10px;
            letter-spacing: .3em;
            margin: 10px 0 0 !important;
            overflow: hidden;
            white-space: nowrap;
        }

        /* Thermal-style roll: paper is exactly the receipt width, height fits the content */
        @page {
            size: 80mm auto;
            margin: 0;
        }

        @media print {
            html, body {
                width: 80mm;
                background: #fff;
            }
            .toolbar { display: none !important; }
            .receipt {
                width: 80mm;
                margin: 0;
                box-shadow: none;
                padding: 6mm 5mm 10mm;
            }
            .receipt::before { display: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button type="button" onclick="window.print()">Print Receipt</button>
        <a href="{{ route('transactions.show', $transaction) }}" class="secondary">Back</a>
    </div>

    <article class="receipt">
        <header class="brand">
            <div class="logo-wrap">
                <img src="{{ asset('img/repairservicelogoblue.png') }}"
                    alt="101 Repair Service"
                    class="logo">
            </div>
            <p class="tagline">Official Payment Receipt</p>
            <p class="address">
                Appliance Repair &amp; Service Center<br>
                Tel: (083) 000-0000 · Cashier Copy
            </p>
        </header>

        <div class="doc-title">Sales Receipt</div>

        <div class="meta">
            <div>
                <span class="label">Receipt No.</span>
                <span class="value">{{ $transaction->receipt_no ?? 'TXN-'.$transaction->id }}</span>
            </div>
            <div>
                <span class="label">Date</span>
                <span class="value">
                    {{ ($transaction->payment_date ?? $transaction->created_at)->format('M d, Y') }}
                </span>
            </div>
            <div>
                <span class="label">Service Report</span>
                <span class="value">#{{ $transaction->report_id ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="label">Txn ID</span>
                <span class="value">#{{ $transaction->id }}</span>
            </div>
        </div>

        <div class="section-head">Customer</div>
        <div class="info-line">
            <span>Name</span>
            <span>{{ $transaction->report->customer_name ?? 'Walk-in' }}</span>
        </div>
        @if($transaction->report && $transaction->report->customer && $transaction->report->customer->phone_no)
            <div class="info-line">
                <span>Phone</span>
                <span>{{ $transaction->report->customer->phone_no }}</span>
            </div>
        @endif
        @if($transaction->report && $transaction->report->appliance)
            <div class="info-line">
                <span>Appliance</span>
                <span>{{ $transaction->report->appliance->product }}</span>
            </div>
            <div class="info-line">
                <span>Brand / Model</span>
                <span>{{ $transaction->report->appliance->brand }} / {{ $transaction->report->appliance->model_no }}</span>
            </div>
        @endif

        <div class="section-head">Charges</div>
        <table class="lines">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="num">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Labor</td>
                    <td class="num">₱{{ number_format((float) $transaction->labor_total, 2) }}</td>
                </tr>
                <tr>
                    <td>Parts / Materials</td>
                    <td class="num">₱{{ number_format((float) $transaction->parts_total, 2) }}</td>
                </tr>
                @if($transaction->report && $transaction->report->details && (float) $transaction->report->details->miscellaneous_cost > 0)
                    <tr>
                        <td>Miscellaneous</td>
                        <td class="num">₱{{ number_format((float) $transaction->report->details->miscellaneous_cost, 2) }}</td>
                    </tr>
                @endif
                @php
                    $delivery = max(0, (float) $billTotal - (float) $transaction->labor_total - (float) $transaction->parts_total - (float) ($transaction->report->details->miscellaneous_cost ?? 0));
                @endphp
                @if($delivery > 0)
                    <tr>
                        <td>Delivery / Pull-out</td>
                        <td class="num">₱{{ number_format($delivery, 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="totals">
            <div class="row">
                <span>Bill Total</span>
                <span>₱{{ number_format($billTotal, 2) }}</span>
            </div>
            <div class="row paid">
                <span>Paid This Receipt</span>
                <span>₱{{ number_format($transaction->amountPaidThisPayment(), 2) }}</span>
            </div>
            <div class="row">
                <span>Total Paid to Date</span>
                <span>₱{{ number_format($totalPaid, 2) }}</span>
            </div>
            @if($remaining > 0)
                <div class="row balance grand">
                    <span>Balance Due</span>
                    <span>₱{{ number_format($remaining, 2) }}</span>
                </div>
            @else
                <div class="row clear grand">
                    <span>Balance Due</span>
                    <span>₱0.00</span>
                </div>
            @endif
        </div>

        <div style="margin-top: 10px; font-size: 12px;">
            <div class="info-line">
                <span>Method</span>
                <span>{{ $transaction->payment_method ?? 'N/A' }}</span>
            </div>
            <div class="info-line">
                <span>Received By</span>
                <span>{{ $transaction->received_by ?? 'System' }}</span>
            </div>
            @if($transaction->reference_no)
                <div class="info-line">
                    <span>Reference</span>
                    <span>{{ $transaction->reference_no }}</span>
                </div>
            @endif
            <div style="text-align:center; margin-top:8px;">
                @php
                    $pill = match($transaction->payment_status) {
                        'Paid' => 'status-paid',
                        'Partial' => 'status-partial',
                        default => 'status-unpaid',
                    };
                @endphp
                <span class="status-pill {{ $pill }}">{{ $transaction->payment_status }}</span>
            </div>
        </div>

        @if($paymentHistory->count() > 1)
            <div class="section-head">Payment History</div>
            <table class="lines">
                <thead>
                    <tr>
                        <th>Receipt</th>
                        <th class="num">Paid</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paymentHistory as $pay)
                        <tr>
                            <td>
                                {{ $pay->receipt_no ?? '#'.$pay->id }}
                                <div style="font-size:10px;color:var(--muted);">
                                    {{ ($pay->payment_date ?? $pay->created_at)->format('M d, Y') }} · {{ $pay->payment_status }}
                                </div>
                            </td>
                            <td class="num">₱{{ number_format($pay->amountPaidThisPayment(), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @php
            $barcodeValue = preg_replace('/[^A-Za-z0-9]/', '', $transaction->receipt_no ?? ('TXN'.$transaction->id));
        @endphp
        <footer class="footer">
            <div class="barcode-wrap">
                <div class="barcode" id="barcode">*{{ $barcodeValue }}*</div>
            </div>
            <div class="barcode-text">{{ $barcodeValue }}</div>
            <p class="thanks">Thank you for trusting 101 Repair Service</p>
            <p>This receipt is your official proof of payment.</p>
            <p>Please keep for warranty validation.</p>
            <p class="cut">✂ - - - - - - - - - - - - - -</p>
        </footer>
    </article>

    <script>
        // Shrink the barcode font until it fits inside the receipt width
        function fitBarcode() {
            var el = document.getElementById('barcode');
            if (!el) return;
            var max = el.parentElement.clientWidth;
            var size = 36;
            el.style.fontSize = size + 'px';
            while (el.scrollWidth > max && size > 10) {
                size -= 1;
                el.style.fontSize = size + 'px';
            }
        }

        window.addEventListener('beforeprint', fitBarcode);

        window.addEventListener('load', function () {
            // Wait for web fonts (incl. the barcode font) before measuring and printing
            var ready = (document.fonts && document.fonts.ready) ? document.fonts.ready : Promise.resolve();
            ready.then(function () {
                fitBarcode();
                setTimeout(function () { window.print(); }, 150);
            });
        });
    </script>
</body>
</html>