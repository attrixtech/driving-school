<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admission Voucher - {{ $admission->admission_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #111;
            margin: 20mm;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 18pt;
            margin: 0;
            color: #1e3a8a;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0;
            font-size: 9pt;
            color: #4b5563;
        }
        .badge {
            display: inline-block;
            background: #2563eb;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9pt;
            font-weight: bold;
            margin-top: 5px;
        }
        .section-title {
            background: #f1f5f9;
            padding: 4px 8px;
            font-size: 10pt;
            font-weight: bold;
            color: #1e293b;
            border-left: 3px solid #2563eb;
            margin-top: 15px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }
        table.bordered td, table.bordered th {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
        }
        table.bordered th {
            background: #f8fafc;
            text-align: left;
        }
        .fee-table {
            margin-top: 10px;
        }
        .fee-table td {
            padding: 4px 8px;
        }
        .signatures {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .sig-box {
            width: 45%;
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

    <div class="header">
        <h1>{{ $admission->tenant?->company?->trade_name ?? 'Apex Driving Academy' }}</h1>
        <p>{{ $admission->branch?->address }}, {{ $admission->branch?->city }} - {{ $admission->branch?->postal_code }}</p>
        <p>Phone: {{ $admission->branch?->phone }} | Email: {{ $admission->branch?->email }} | GSTIN: {{ $admission->tenant?->company?->gstin ?? 'N/A' }}</p>
        <span class="badge">OFFICIAL STUDENT ADMISSION VOUCHER</span>
    </div>

    <table style="margin-bottom: 10px;">
        <tr>
            <td style="width: 50%;"><strong>Admission No:</strong> {{ $admission->admission_number }}</td>
            <td style="text-align: right;"><strong>Date:</strong> {{ $admission->admission_date->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td><strong>Student Code:</strong> {{ $admission->student?->student_code }}</td>
            <td style="text-align: right;"><strong>Branch:</strong> {{ $admission->branch?->name }}</td>
        </tr>
    </table>

    <div class="section-title">1. Student Details</div>
    <table class="bordered">
        <tr>
            <td style="width: 25%; font-weight: bold;">Student Name</td>
            <td style="width: 35%;">{{ $admission->student?->full_name }}</td>
            <td style="width: 20%; font-weight: bold;">Gender / DOB</td>
            <td style="width: 20%;">{{ ucfirst($admission->student?->gender) }} / {{ $admission->student?->date_of_birth ? $admission->student->date_of_birth->format('d/m/Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Phone</td>
            <td>{{ $admission->student?->phone }}</td>
            <td style="font-weight: bold;">Blood Group</td>
            <td>{{ $admission->student?->blood_group ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Learner License (LL)</td>
            <td colspan="3">{{ $admission->student?->learner_license_number ?? 'Not Applied / In Progress' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Address</td>
            <td colspan="3">{{ $admission->student?->address }}, {{ $admission->student?->city }}</td>
        </tr>
    </table>

    <div class="section-title">2. Course & Training Package</div>
    <table class="bordered">
        <tr>
            <td style="width: 25%; font-weight: bold;">Course Name</td>
            <td colspan="3">{{ $admission->course?->name }} ({{ $admission->course?->code }})</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Package Name</td>
            <td>{{ $admission->package?->name ?? 'Standard Package' }}</td>
            <td style="font-weight: bold;">Duration</td>
            <td>{{ $admission->package?->duration_days ?? $admission->course?->duration_days }} Days</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Practical Sessions</td>
            <td>{{ $admission->package?->practical_sessions_count ?? $admission->course?->total_practical_hours }} Hours</td>
            <td style="font-weight: bold;">Theory Classes</td>
            <td>{{ $admission->package?->theory_sessions_count ?? $admission->course?->total_theory_hours }} Hours</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Assigned Instructor</td>
            <td colspan="3">{{ $admission->instructor?->name ?? 'Assigned at commencement' }}</td>
        </tr>
    </table>

    <div class="section-title">3. Fee Structure & Payment Summary</div>
    <table class="bordered">
        <tr>
            <td style="width: 70%;">Base Course / Training Package Fee</td>
            <td style="text-align: right; width: 30%;">₹{{ number_format($admission->course_fee, 2) }}</td>
        </tr>
        <tr>
            <td>Less: Special Concession / Discount</td>
            <td style="text-align: right; color: green;">- ₹{{ number_format($admission->discount_amount, 2) }}</td>
        </tr>
        <tr>
            <td>Add: Applicable GST (18%)</td>
            <td style="text-align: right;">₹{{ number_format($admission->tax_amount, 2) }}</td>
        </tr>
        <tr style="font-weight: bold; background: #f8fafc;">
            <td>Total Invoice Amount</td>
            <td style="text-align: right;">₹{{ number_format($admission->total_amount, 2) }}</td>
        </tr>
        <tr style="font-weight: bold; color: green;">
            <td>Amount Received at Admission</td>
            <td style="text-align: right;">₹{{ number_format($admission->paid_amount, 2) }}</td>
        </tr>
        <tr style="font-weight: bold; color: {{ $admission->balance_amount > 0 ? '#b91c1c' : '#15803d' }};">
            <td>Balance Payable</td>
            <td style="text-align: right;">₹{{ number_format($admission->balance_amount, 2) }}</td>
        </tr>
    </table>

    <div style="margin-top: 25px; font-size: 8.5pt; color: #475569;">
        <strong>Terms & Declarations:</strong>
        <ol style="margin-top: 4px; padding-left: 18px;">
            <li>Student must carry their Learner License during all practical driving sessions as per Motor Vehicles Act.</li>
            <li>Session cancellations must be notified at least 12 hours prior; missed sessions without notice may be forfeited.</li>
            <li>Training fee once paid is non-refundable and valid for 6 months from date of enrollment.</li>
        </ol>
    </div>

    <div class="signatures">
        <div class="sig-box">
            Student Signature
        </div>
        <div class="sig-box">
            For {{ $admission->tenant?->company?->trade_name ?? 'Driving Academy' }}<br>
            Authorized Signatory
        </div>
    </div>

</body>
</html>
