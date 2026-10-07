<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Report #{{ $service->id }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            color: #2c3e50;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #1e3a8a;
            break-after: avoid;
        }

        .header h1 {
            margin: 0 0 8px 0;
            font-size: 26px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #1e3a8a;
            font-weight: 700;
        }

        .header .subtitle {
            margin: 5px 0 0;
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }

        .header .contact {
            margin: 8px 0 0;
            font-size: 11px;
            color: #94a3b8;
        }

        .section {
            margin-bottom: 22px;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .section-title {
            font-weight: 700;
            font-size: 16px;
            color: #1e3a8a;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 8px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            break-after: avoid;
            page-break-after: avoid;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .col {
            flex: 1;
            min-width: 0;
        }

        .label {
            font-weight: 600;
            display: inline-block;
            width: 120px;
            flex-shrink: 0;
            color: #475569;
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            table-layout: fixed;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .table th,
        .table td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
            word-wrap: break-word;
        }

        .table th {
            background: linear-gradient(to bottom, #f1f5f9, #e2e8f0);
            color: #1e293b;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f8fafc;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .table tbody tr:hover {
            background-color: #f1f5f9;
        }

        .table th:nth-child(1) { width: 11%; } /* Part No */
        .table th:nth-child(2) { width: 36%; } /* Description */
        .table th:nth-child(3) { width: 8%; text-align: center; } /* Qty */
        .table th:nth-child(4) { width: 13%; text-align: center; } /* Not Working */
        .table th:nth-child(5) { width: 14%; text-align: right; } /* Price */
        .table th:nth-child(6) { width: 18%; text-align: right; } /* Subtotal */

        .table td:nth-child(3),
        .table td:nth-child(4) {
            text-align: center;
            font-weight: 500;
        }

        .table td:nth-child(5),
        .table td:nth-child(6) {
            text-align: right;
            white-space: nowrap;
            font-weight: 600;
            color: #1e3a8a;
        }

        .table tbody tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .table thead {
            display: table-header-group;
        }

        .cost-summary {
            background: linear-gradient(to right, #f8fafc, #fff);
            border: 2px solid #1e3a8a;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .cost-summary .table {
            box-shadow: none;
            margin-top: 0;
        }

        .cost-summary .table td {
            border: none;
            padding: 8px 15px;
        }

        .cost-summary .table tr:last-child td {
            border-top: 2px solid #1e3a8a;
            padding-top: 12px;
        }

        .signature-section {
            margin-top: 60px;
            break-before: avoid;
            page-break-before: avoid;
        }

        .signature-box {
            text-align: center;
            padding: 20px;
        }

        .signature-line {
            border-top: 2px solid #1e3a8a;
            width: 70%;
            margin: 0 auto 10px;
        }

        .signature-label {
            font-weight: 600;
            color: #475569;
            font-size: 13px;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 11px;
            color: #64748b;
            border-top: 2px solid #e2e8f0;
            padding-top: 15px;
            break-before: avoid;
            page-break-before: avoid;
        }

        .footer p {
            margin: 4px 0;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            margin: 3px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .badge-blue {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }

        .badge-purple {
            background-color: #f3e8ff;
            color: #7c3aed;
            border: 1px solid #c4b5fd;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-completed {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }

        .status-in-progress {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .container {
                max-width: 100%;
                padding: 0;
            }

            .section {
                margin-bottom: 18px;
            }

            .header {
                margin-bottom: 25px;
                padding-bottom: 15px;
            }

            .header h1 {
                font-size: 22px;
            }

            .section-title {
                font-size: 14px;
                border-bottom-width: 1px;
            }

            .table th,
            .table td {
                padding: 6px 8px;
                font-size: 11px;
            }

            .cost-summary {
                padding: 12px;
            }

            .cost-summary .table td {
                padding: 6px 12px;
                font-size: 11px;
            }

            .signature-section {
                margin-top: 40px;
            }

            .no-print {
                display: none !important;
            }
        }

        @media screen and (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .header h1 {
                font-size: 20px;
            }

            .section-title {
                font-size: 14px;
            }

            .table th,
            .table td {
                padding: 6px 8px;
                font-size: 11px;
            }

            .row {
                flex-direction: column;
                gap: 10px;
            }

            .col {
                padding-right: 0;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="container">
        <div class="no-print" style="margin-bottom: 20px; text-align: right;">
            <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Print Report</button>
            <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer;">Close</button>
        </div>

        <div class="header">
            <h1>101 Repair Service</h1>
            <p class="subtitle">Professional Appliance Repair Solutions</p>
            <p class="contact">123 Repair Street, Cityville, Tech State | Phone: (555) 123-4567 | Email: support@repairsystem.com</p>
        </div>

        <div class="section">
            <div class="info-box">
                <div class="row">
                    <div class="col">
                        <p><span class="label">Report ID:</span> #{{ $service->id }}</p>
                        <p><span class="label">Date In:</span> {{ $service->date_in->format('M d, Y') }}</p>
                        <p><span class="label">Status:</span>
                            @php
                                $statusClass = 'status-pending';
                                if($service->status === 'Completed') $statusClass = 'status-completed';
                                elseif(in_array($service->status, ['Under Repair', 'Waiting for Parts'])) $statusClass = 'status-in-progress';
                            @endphp
                            <span class="status-badge {{ $statusClass }}">{{ $service->status }}</span>
                        </p>
                    </div>
                    <div class="col">
                        <p><span class="label">Customer:</span> {{ $service->customer_name }}</p>
                        <p><span class="label">Technician:</span> {{ !empty($service->details->technician) ? $service->details->technician : 'No Assigned Technician' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Appliance Information</div>
            <div class="row">
                <div class="col">
                    <p><span class="label">Appliance Type:</span>
                        {{ $service->appliance ? ($service->appliance->product ?? 'N/A') : 'N/A' }}</p>
                    <p><span class="label">Brand:</span>
                        {{ $service->appliance ? ($service->appliance->brand ?? 'N/A') : 'N/A' }}</p>
                    <p><span class="label">Model No:</span>
                        {{ $service->appliance ? ($service->appliance->model_no ?? 'N/A') : 'N/A' }}</p>
                    <p><span class="label">Serial No:</span> {{ $service->appliance ? ($service->appliance->serial_no ?? 'N/A') : 'N/A' }}</p>
                </div>
                <div class="col">
                    <p><span class="label">Dealer:</span> {{ $service->appliance ? ($service->appliance->dealer ?? 'N/A') : 'N/A' }}</p>
                    <p><span class="label">Date of Purchase:</span>
                        @if($service->appliance && $service->appliance->date_in)
                            @php
                                $date = $service->appliance->date_in;
                                if (is_string($date)) {
                                    $date = \Carbon\Carbon::parse($date);
                                }
                            @endphp
                            {{ $date->format('M d, Y') }}
                        @else
                            N/A
                        @endif
                    </p>
                    <p><span class="label">Warranty End:</span>
                        @if($service->appliance && $service->appliance->warranty_end)
                            @php
                                $warranty = $service->appliance->warranty_end;
                                if (is_string($warranty)) {
                                    $warranty = \Carbon\Carbon::parse($warranty);
                                }
                            @endphp
                            @if($warranty->isPast())
                                Expired ({{ $warranty->format('M d, Y') }})
                            @else
                                Active until {{ $warranty->format('M d, Y') }}
                            @endif
                        @else
                            No Warranty
                        @endif
                    </p>
                </div>
            </div>
        </div>

        @if($service->appliance && $service->appliance->problems && $service->appliance->problems->count() > 0)
            <div class="section">
                <div class="section-title">Appliance Reported Problems</div>
                <div class="info-box" style="min-height: 50px;">
                    @foreach($service->appliance->problems as $problem)
                        @if($problem->commonProblem)
                            <span class="badge badge-blue">{{ $problem->commonProblem->problem_name }}</span>
                        @elseif($problem->other_problem)
                            <span class="badge badge-purple">Other: {{ $problem->other_problem }}</span>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        <div class="section">
            <div class="section-title">Diagnosis & Findings</div>
            <div class="info-box" style="min-height: 100px;">
                {{ $service->findings ?? 'No findings recorded.' }}
            </div>
        </div>

        @if($service->parts && $service->parts->count() > 0)
            <div class="section">
                <div class="section-title">Parts Installed / Used</div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Part No.</th>
                            <th>Description</th>
                            <th style="text-align:center;">Qty</th>
                            <th style="text-align:center;">Not Working</th>
                            <th style="text-align:right;">Price</th>
                            <th style="text-align:right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($service->parts as $part)
                            <tr>
                                <td>{{ $part->part_no }}</td>
                                <td>{{ $part->description ?: $part->name }}</td>
                                <td style="text-align:center;">{{ $part->pivot->quantity }}</td>
                                <td style="text-align:center;">{{ $part->pivot->is_not_working ? 'Yes' : '-' }}</td>
                                <td style="text-align:right;">
                                    @if($part->pivot->is_not_working)
                                        -
                                    @else
                                        {{ number_format($part->pivot->price, 2) }}
                                    @endif
                                </td>
                                <td style="text-align:right;">
                                    @if($part->pivot->is_not_working)
                                        -
                                    @else
                                        {{ number_format($part->pivot->quantity * $part->pivot->price, 2) }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="section">
            <div class="section-title">Cost Breakdown & Summary</div>
            <div class="cost-summary">
                <table class="table">
                    <tr>
                        <td style="text-align: right; white-space: nowrap;"><strong>Labor Cost:</strong></td>
                        <td style="text-align: right; white-space: nowrap;">Php
                            {{ number_format($service->details ? $service->details->labor : 0, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: right; white-space: nowrap;"><strong>Parts Total:</strong></td>
                        <td style="text-align: right; white-space: nowrap;">Php
                            {{ number_format($service->details ? $service->details->parts_total_charge : 0, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: right; white-space: nowrap;"><strong>Misc. Cost:</strong></td>
                        <td style="text-align: right; white-space: nowrap;">Php
                            {{ number_format($service->details ? $service->details->miscellaneous_cost : 0, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: right; white-space: nowrap; font-size: 15px;"><strong>TOTAL
                                AMOUNT:</strong></td>
                        <td style="text-align: right; white-space: nowrap; font-size: 15px;"><strong>Php
                                {{ number_format($service->details ? $service->details->total_amount : 0, 2) }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: right; white-space: nowrap;"><strong>Amount Paid:</strong></td>
                        <td style="text-align: right; white-space: nowrap;">Php
                            {{ number_format($service->transactions->sum(fn ($t) => $t->amountPaidThisPayment()), 2) }}
                        </td>
                    </tr>
                    @php
                        $printBill = (float) ($service->details->total_amount ?? 0);
                        $printPaid = $service->transactions->sum(fn ($t) => $t->amountPaidThisPayment());
                        $printRemain = max(0, $printBill - $printPaid);
                    @endphp
                    <tr>
                        <td style="text-align: right; white-space: nowrap; font-size: 15px; color: {{ $printRemain > 0 ? '#dc2626' : '#16a34a' }}; -webkit-print-color-adjust: exact; print-color-adjust: exact;"><strong>{{ $printRemain > 0 ? 'REMAINING BALANCE' : 'FULLY PAID' }}:</strong></td>
                        <td style="text-align: right; white-space: nowrap; font-size: 15px; color: {{ $printRemain > 0 ? '#dc2626' : '#16a34a' }}; -webkit-print-color-adjust: exact; print-color-adjust: exact;"><strong>Php
                                {{ number_format($printRemain, 2) }}</strong>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if($service->transactions->isNotEmpty())
            <div class="section">
                <div class="section-title">Payment History</div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Receipt / Ref</th>
                            <th>Amount Paid</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($service->transactions as $trans)
                            <tr>
                                <td>{{ $trans->receipt_no ?? 'N/A' }}</td>
                                <td>Php {{ number_format($trans->amountPaidThisPayment(), 2) }}</td>
                                <td>{{ $trans->payment_status }}</td>
                                <td>{{ $trans->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="section signature-section">
            <div class="row">
                <div class="col">
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <p class="signature-label">Customer Signature</p>
                    </div>
                </div>
                <div class="col">
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <p class="signature-label">Technician Signature</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <p><strong>Thank you for choosing 101 Repair Service!</strong></p>
            <p>This document serves as an official record of service performed.</p>
            <p>Generated on {{ now()->format('M d, Y H:i:s') }} | Report #{{ $service->id }}</p>
        </div>
    </div>
</body>

</html>