<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $official ? 'Official' : 'Unofficial' }} Transcript — {{ $student->full_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            line-height: 1.4;
        }
        @if(!$official)
        .watermark {
            position: fixed;
            top: 40%;
            left: 15%;
            font-size: 100px;
            font-weight: bold;
            color: #000;
            opacity: 0.08;
            transform: rotate(-45deg);
            z-index: -1;
        }
        @endif
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1a1a1a;
            padding-bottom: 12px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .header h2 {
            font-size: 14px;
            font-weight: normal;
            color: #444;
        }
        .student-info {
            margin-bottom: 20px;
            border: 1px solid #cbd5e0;
            padding: 10px;
        }
        .student-info table {
            width: 100%;
        }
        .student-info td {
            padding: 2px 0;
        }
        .student-info .label {
            font-weight: bold;
            width: 140px;
        }
        .term-block {
            page-break-inside: avoid;
            margin-bottom: 16px;
        }
        .term-header {
            background-color: #edf2f7;
            padding: 6px 8px;
            font-weight: bold;
            font-size: 12px;
            border: 1px solid #cbd5e0;
            border-bottom: none;
        }
        .term-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .term-table th {
            background-color: #2d3748;
            color: #fff;
            padding: 5px 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        .term-table th.numeric {
            text-align: right;
        }
        .term-table td {
            padding: 4px 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .term-table td.numeric {
            text-align: right;
        }
        .term-table tr:nth-child(even) {
            background-color: #f7fafc;
        }
        .term-footer {
            background-color: #edf2f7;
            padding: 6px 8px;
            border: 1px solid #cbd5e0;
            border-top: none;
            font-size: 10px;
        }
        .term-footer span {
            margin-right: 24px;
            font-weight: bold;
        }
        .cumulative {
            margin-top: 20px;
            border-top: 2px solid #1a1a1a;
            padding-top: 12px;
        }
        .cumulative table {
            width: 50%;
        }
        .cumulative td {
            padding: 3px 0;
        }
        .cumulative .label {
            font-weight: bold;
            width: 160px;
        }
        .cumulative .value {
            font-size: 13px;
            font-weight: bold;
        }
        .signature {
            margin-top: 50px;
        }
        .signature-line {
            margin-bottom: 8px;
            font-size: 12px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #888;
        }
    </style>
</head>
<body>
    @if(!$official)
        <div class="watermark">UNOFFICIAL</div>
    @endif

    <div class="header">
        <h1>Georgia Job Challenge Academy</h1>
        <h2>{{ $official ? 'OFFICIAL TRANSCRIPT' : 'UNOFFICIAL TRANSCRIPT' }}</h2>
    </div>

    <div class="student-info">
        <table>
            <tr>
                <td class="label">Student Name:</td>
                <td>{{ $student->full_name }}</td>
                <td class="label">Student ID:</td>
                <td>{{ $student->student_id }}</td>
            </tr>
            <tr>
                <td class="label">Date of Birth:</td>
                <td>{{ $student->date_of_birth->format('M d, Y') }}</td>
                <td class="label">Enrollment Date:</td>
                <td>{{ $student->enrollment_date->format('M d, Y') }}</td>
            </tr>
        </table>
    </div>

    @foreach($termGroups as $group)
        <div class="term-block">
            <div class="term-header">
                {{ $group['term']->name }} — {{ $group['term']->academicYear->name }}
            </div>
            <table class="term-table">
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th>Grade</th>
                        <th class="numeric">GPA Pts</th>
                        <th class="numeric">Credits</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group['enrollments'] as $enrollment)
                        <tr>
                            <td>{{ $enrollment->class->course->course_code }}</td>
                            <td>{{ $enrollment->class->course->name }}</td>
                            <td>{{ $enrollment->final_letter_grade ?? '—' }}</td>
                            <td class="numeric">{{ $enrollment->grade_points !== null ? number_format($enrollment->grade_points, 2) : '—' }}</td>
                            <td class="numeric">{{ number_format($enrollment->class->course->credits, 1) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="term-footer">
                <span>Term GPA: {{ number_format($group['termGpa'], 2) }}</span>
                <span>Term Credits: {{ number_format($group['termCredits'], 1) }}</span>
            </div>
        </div>
    @endforeach

    <div class="cumulative">
        <table>
            <tr>
                <td class="label">Total Credits:</td>
                <td class="value">{{ number_format($totalCredits, 1) }}</td>
            </tr>
            <tr>
                <td class="label">Cumulative GPA:</td>
                <td class="value">{{ number_format($cumulativeGpa, 2) }}</td>
            </tr>
        </table>
    </div>

    @if($official)
        <div class="signature">
            <div class="signature-line">Registrar: _______________________________________________</div>
            <div class="signature-line">Date: _______________________________________________</div>
        </div>
    @endif

    <div class="footer">
        Generated on {{ $generatedAt->format('F d, Y \a\t g:i A') }}
    </div>
</body>
</html>
