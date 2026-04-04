<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Report #{{ $service->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.5;
        }

        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0 0;
            font-size: 12px;
            color: #666;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-weight: bold;
            font-size: 16px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
        }

        .col {
            flex: 1;
            padding-right: 20px;
        }

        .label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none;
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
            <h1>Repair System Service Report</h1>
            <p>123 Repair Street, Cityville, Tech State</p>
            <p>Phone: (555) 123-4567 | Email: support@repairsystem.com</p>
        </div>

        <div class="section">
            <div class="row">
                <div class="col">
                    <p><span class="label">Report ID:</span> #{{ $service->id }}</p>
                    <p><span class="label">Date In:</span> {{ $service->date_in->format('M d, Y') }}</p>
                    <p><span class="label">Status:</span> {{ $service->status }}</p>
                </div>
                <div class="col">
                    <p><span class="label">Customer:</span> {{ $service->customer_name }}</p>
                    <p><span class="label">Technician:</span> {{ !empty($service->details->technician) ? $service->details->technician : 'No Assigned Technician' }}</p>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Appliance Information</div>
            <div class="row">
                <div class="col">
                    <p><span class="label">Appliance:</span>
                        {{ $service->appliance ? $service->appliance->product : 'N/A' }}</p>
                    <p><span class="label">Brand/Model:</span>
                        {{ $service->appliance ? $service->appliance->brand : 'N/A' }} /
                        {{ $service->appliance ? $service->appliance->model_no : 'N/A' }}
                    </p>
                </div>
                <div class="col">
                    <p><span class="label">Serial No:</span> {{ $service->appliance ? ($service->appliance->serial_no ?? 'N/A') : 'N/A' }}</p>
                    <p><span class="label">Warranty:</span>
                        @if($service->appliance && $service->appliance->warranty_end)
                            @if(\Carbon\Carbon::parse($service->appliance->warranty_end)->isPast())
                                Expired ({{ \Carbon\Carbon::parse($service->appliance->warranty_end)->format('M d, Y') }})
                            @else
                                Active until {{ \Carbon\Carbon::parse($service->appliance->warranty_end)->format('M d, Y') }}
                            @endif
                        @else
                            No Warranty
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Diagnosis & Findings</div>
            <div style="min-height: 100px; border: 1px solid #eee; padding: 10px;">
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
            <table class="table" style="width: 50%; float: right; border: none;">
                <tr>
                    <td style="border: none; text-align: right; padding-right: 20px;"><strong>Labor Cost:</strong></td>
                    <td style="border: none; text-align: right; width: 100px;">Php
                        {{ number_format($service->details ? $service->details->labor : 0, 2) }}
                    </td>
                </tr>
                <tr>
                    <td style="border: none; text-align: right; padding-right: 20px;"><strong>Parts Total:</strong></td>
                    <td style="border: none; text-align: right;">Php
                        {{ number_format($service->details ? $service->details->parts_total_charge : 0, 2) }}
                    </td>
                </tr>
                <tr>
                    <td style="border: none; text-align: right; padding-right: 20px;"><strong>Misc. Cost:</strong></td>
                    <td style="border: none; text-align: right;">Php
                        {{ number_format($service->details ? $service->details->miscellaneous_cost : 0, 2) }}
                    </td>
                </tr>
                <tr>
                    <td style="border: none; text-align: right; padding-right: 20px; font-size: 16px;"><strong>TOTAL
                            AMOUNT:</strong></td>
                    <td style="border: none; text-align: right; font-size: 16px;"><strong>Php
                            {{ number_format($service->details ? $service->details->total_amount : 0, 2) }}</strong>
                    </td>
                </tr>
            </table>
            <div style="clear: both;"></div>
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
                                <td>Php {{ number_format($trans->total_amount, 2) }}</td>
                                <td>{{ $trans->payment_status }}</td>
                                <td>{{ $trans->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="section" style="margin-top: 50px;">
            <div class="row">
                <div class="col" style="text-align: center;">
                    <br><br>
                    <div style="border-top: 1px solid #333; width: 80%; margin: 0 auto;"></div>
                    <p>Customer Signature</p>
                </div>
                <div class="col" style="text-align: center;">
                    <br><br>
                    <div style="border-top: 1px solid #333; width: 80%; margin: 0 auto;"></div>
                    <p>Technician Signature</p>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for choosing our service. This report serves as proof of service.</p>
            <p>Generated on {{ now()->format('M d, Y H:i:s') }}</p>
        </div>
    </div>
</body>

</html>