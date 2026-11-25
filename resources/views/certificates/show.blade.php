<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Driver Training Certificate - {{ $certificate->certificate_number }}</title>
    <style>
        body {
            font-family: 'Georgia', serif;
            color: #1e293b;
            margin: 0;
            padding: 20px;
            background: #f8fafc;
        }
        .cert-container {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            border: 12px double #1e3a8a;
            padding: 40px;
            position: relative;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .header-title {
            font-family: 'Arial', sans-serif;
            font-size: 24pt;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }
        .sub-header {
            font-size: 11pt;
            color: #64748b;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .cert-presented {
            font-size: 13pt;
            font-style: italic;
            color: #475569;
            margin-bottom: 10px;
        }
        .student-name {
            font-size: 26pt;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 2px solid #cbd5e1;
            display: inline-block;
            padding: 0 30px 5px 30px;
            margin-bottom: 20px;
        }
        .cert-body {
            font-size: 12pt;
            line-height: 1.6;
            color: #334155;
            max-width: 650px;
            margin: 0 auto 30px auto;
        }
        .meta-box {
            display: flex;
            justify-content: space-around;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 40px;
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
        }
        .meta-item strong {
            display: block;
            font-size: 13pt;
            color: #1e3a8a;
        }
        .footer-signatures {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 40px;
            padding: 0 20px;
        }
        .sig-block {
            text-align: center;
            border-top: 1px solid #94a3b8;
            width: 200px;
            padding-top: 6px;
            font-size: 9pt;
            font-family: 'Arial', sans-serif;
            color: #475569;
        }
        .qr-placeholder {
            border: 2px dashed #94a3b8;
            padding: 8px 12px;
            font-size: 8pt;
            font-family: 'Courier New', monospace;
            color: #475569;
            background: #f1f5f9;
            border-radius: 4px;
        }
        @media print {
            body { padding: 0; background: #fff; }
            .cert-container { box-shadow: none; border: 10px double #1e3a8a; margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="max-width: 850px; margin: 0 auto 15px auto; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 18px; background: #2563eb; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 10pt;">
            Print Certificate
        </button>
    </div>

    <div class="cert-container">
        
        <div class="header-title">{{ $certificate->tenant?->company?->trade_name ?? 'thrumble.driving-school' }}</div>
        <div class="sub-header">{{ $certificate->branch?->name }} &bull; Government Approved Motor Training School</div>

        <div class="cert-presented">This is to certify that</div>

        <div class="student-name">{{ $certificate->student?->full_name }}</div>

        <div class="cert-body">
            has successfully completed the comprehensive motor vehicle driver training program for <strong>{{ $certificate->course_name }} ({{ $certificate->vehicle_category_code }})</strong> covering road safety regulations, defensive driving, parking maneuvers, and dual-control vehicle operation in full accordance with the Motor Vehicles Act.
        </div>

        <div class="meta-box">
            <div class="meta-item">
                <span>Certificate No.</span>
                <strong style="font-family: monospace;">{{ $certificate->certificate_number }}</strong>
            </div>
            <div class="meta-item">
                <span>Practical Hours</span>
                <strong>{{ $certificate->practical_hours_completed }} Hrs</strong>
            </div>
            <div class="meta-item">
                <span>Theory Classes</span>
                <strong>{{ $certificate->theory_hours_completed }} Hrs</strong>
            </div>
            <div class="meta-item">
                <span>Assessment Grade</span>
                <strong>{{ $certificate->final_grade }}%</strong>
            </div>
        </div>

        <div class="footer-signatures">
            <div class="sig-block">
                Chief Driving Instructor<br>
                {{ $certificate->issuedBy?->name ?? 'Head of Training' }}
            </div>

            <div class="qr-placeholder">
                <div>VERIFICATION CODE</div>
                <div style="font-weight: bold; margin: 2px 0;">{{ substr($certificate->verification_code, 0, 16) }}</div>
                <div style="font-size: 7.5pt;">Verify online at thrumble.driving-school</div>
            </div>

            <div class="sig-block">
                Academy Principal / Director<br>
                For {{ $certificate->tenant?->company?->trade_name ?? 'thrumble.driving-school' }}
            </div>
        </div>

        <div style="margin-top: 30px; font-size: 8pt; color: #94a3b8; font-family: 'Arial', sans-serif;">
            Issued on {{ $certificate->issue_date->format('d F Y') }} &bull; Valid across India for RTO Driving License Test Eligibility
        </div>

    </div>

</body>
</html>
