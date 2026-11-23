<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Tax Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #1e293b;
            margin: 20mm;
            line-height: 1.4;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: #1e3a8a;
        }
        .inv-title {
            text-align: right;
        }
        .inv-title h2 {
            margin: 0;
            font-size: 16pt;
            color: #2563eb;
            text-transform: uppercase;
        }
        .grid-2 {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .grid-col {
            width: 48%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px;
        }
        .grid-col h3 {
            margin: 0 0 6px 0;
            font-size: 10pt;
            text-transform: uppercase;
            color: #475569;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th, table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 9.5pt;
        }
        table th {
            background: #f1f5f9;
            text-align: left;
        }
        .totals-table {
            width: 50%;
            margin-left: auto;
            margin-top: 15px;
        }
        .totals-table td {
            padding: 5px 8px;
        }
        .signatures {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .sig-box {
            width: 40%;
            text-align: center;
            border-top: 1px solid #64748b;
            padding-top: 5px;
            font-size: 9pt;
        }
        @media print {
            .no-print { display: none; }
            body { margin: 10mm; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 6px 14px; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 10pt; font-weight: bold;">
            Print / Save as PDF
        </button>
    </div>

    <div class="invoice-header">
        <div>
            <div class="company-name">{{ $invoice->tenant?->company?->legal_name ?? 'Apex Motor Driving Academy Pvt Ltd' }}</div>
            <div style="font-size: 9pt; color: #475569; margin-top: 2px;">
                Trade Name: <strong>{{ $invoice->tenant?->company?->trade_name }}</strong><br>
                {{ $invoice->branch?->address }}, {{ $invoice->branch?->city }} - {{ $invoice->branch?->postal_code }}<br>
                Phone: {{ $invoice->branch?->phone }} | Email: {{ $invoice->branch?->email }}<br>
                <strong>GSTIN:</strong> {{ $invoice->tenant?->company?->gstin ?? '29AABCA1234F1Z8' }} | State Code: 29 (Karnataka)
            </div>
        </div>
        <div class="inv-title">
            <h2>TAX INVOICE</h2>
            <div style="font-family: monospace; font-weight: bold; margin-top: 3px;">{{ $invoice->invoice_number }}</div>
            <div style="font-size: 9pt; color: #64748b; margin-top: 2px;">Date: {{ $invoice->invoice_date->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="grid-2">
        <div class="grid-col">
            <h3>Billed To (Student)</h3>
            <strong>{{ $invoice->student?->full_name }}</strong><br>
            Student Code: {{ $invoice->student?->student_code }}<br>
            Phone: {{ $invoice->student?->phone }}<br>
            Address: {{ $invoice->student?->address ?? $invoice->student?->city }}<br>
            Learner License (LL): {{ $invoice->student?->learner_license_number ?? 'In Process' }}
        </div>
        <div class="grid-col">
            <h3>Admission & Course Details</h3>
            Admission No: <strong>{{ $invoice->admission?->admission_number }}</strong><br>
            Course: {{ $invoice->admission?->course?->name }}<br>
            Package: {{ $invoice->admission?->package?->name ?? 'Standard Package' }}<br>
            Branch: {{ $invoice->branch?->name }}<br>
            Status: <strong style="text-transform: uppercase;">{{ str_replace('_', ' ', $invoice->status) }}</strong>
        </div>
    </div>

    <!-- Items Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 50%;">Description of Service</th>
                <th style="width: 15%;">SAC Code</th>
                <th style="width: 10%; text-align: center;">Qty</th>
                <th style="width: 20%; text-align: right;">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>
                    <strong>{{ $invoice->admission?->course?->name }}</strong><br>
                    <span style="font-size: 8.5pt; color: #64748b;">
                        {{ $invoice->admission?->package?->name ?? 'Standard Training Course' }} &bull; 
                        {{ $invoice->admission?->package?->practical_sessions_count ?? $invoice->admission?->course?->total_practical_hours }} Practical Hours &bull; 
                        {{ $invoice->admission?->package?->theory_sessions_count ?? $invoice->admission?->course?->total_theory_hours }} Theory Hours
                    </span>
                </td>
                <td style="font-family: monospace;">999293</td>
                <td style="text-align: center;">1</td>
                <td style="text-align: right;">₹{{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Totals Table -->
    <table class="totals-table">
        <tr>
            <td>Taxable Subtotal</td>
            <td style="text-align: right; font-weight: bold;">₹{{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        @if($invoice->discount_amount > 0)
        <tr>
            <td>Special Concession / Discount</td>
            <td style="text-align: right; color: green;">- ₹{{ number_format($invoice->discount_amount, 2) }}</td>
        </tr>
        @endif
        <tr>
            <td>CGST @ 9.0%</td>
            <td style="text-align: right;">₹{{ number_format($invoice->tax_amount / 2, 2) }}</td>
        </tr>
        <tr>
            <td>SGST @ 9.0%</td>
            <td style="text-align: right;">₹{{ number_format($invoice->tax_amount / 2, 2) }}</td>
        </tr>
        <tr style="background: #f1f5f9; font-weight: bold; font-size: 11pt;">
            <td>Total Invoice Amount</td>
            <td style="text-align: right; color: #1e3a8a;">₹{{ number_format($invoice->total_amount, 2) }}</td>
        </tr>
        <tr style="color: green; font-weight: bold;">
            <td>Total Amount Paid</td>
            <td style="text-align: right;">₹{{ number_format($invoice->paid_amount, 2) }}</td>
        </tr>
        <tr style="font-weight: bold; color: {{ $invoice->balance_amount > 0 ? '#b91c1c' : '#15803d' }};">
            <td>Balance Due</td>
            <td style="text-align: right;">₹{{ number_format($invoice->balance_amount, 2) }}</td>
        </tr>
    </table>

    <div style="margin-top: 40px; font-size: 8.5pt; color: #64748b;">
        <strong>Declaration:</strong><br>
        We declare that this invoice shows the actual price of the services described and that all particulars are true and correct. Training fee covers instructor tuition, dual-control fuel, and classroom study materials.
    </div>

    <div class="signatures">
        <div class="sig-box">
            Customer / Student Signature
        </div>
        <div class="sig-box">
            For {{ $invoice->tenant?->company?->trade_name ?? 'Driving Academy' }}<br>
            <strong>Authorized Signatory</strong>
        </div>
    </div>

</body>
</html>
