<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Card — {{ $student->full_name }}</title>
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
            margin-bottom: 16px;
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
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .grades-table th {
            background-color: #2d3748;
            color: #fff;
            padding: 6px 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        .grades-table th.numeric {
            text-align: right;
        }
        .grades-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .grades-table td.numeric {
            text-align: right;
        }
        .grades-table tr:nth-child(even) {
            background-color: #f7fafc;
        }
        .summary {
            margin-top: 20px;
            border-top: 2px solid #1a1a1a;
            padding-top: 12px;
        }
        .summary table {
            width: 50%;
        }
        .summary td {
            padding: 3px 0;
        }
        .summary .label {
            font-weight: bold;
            width: 160px;
        }
        .summary .value {
            font-size: 13px;
            font-weight: bold;
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
    <div class="header">
        <h1>Georgia Job Challenge Academy</h1>
        <h2>Report Card</h2>
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
                <td class="label">Class:</td>
                <td>{{ $class->class_number ?? '—' }}</td>
                <td class="label">Name:</td>
                <td>{{ $class->name ?? '' }}</td>
            </tr>
            @if($student->date_of_birth)
            <tr>
                <td class="label">Date of Birth:</td>
                <td>{{ $student->date_of_birth->format('M d, Y') }}</td>
                <td></td>
                <td></td>
            </tr>
            @endif
        </table>
    </div>

    <table class="grades-table">
        <thead>
            <tr>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Instructor</th>
                <th class="numeric">Avg %</th>
                <th>Grade</th>
                <th class="numeric">GPA Pts</th>
                <th class="numeric">Credits</th>
            </tr>
        </thead>
        <tbody>
            @forelse($enrollments as $enrollment)
                <tr>
                    <td>{{ $enrollment->classCourse->course->course_code ?? '—' }}</td>
                    <td>{{ $enrollment->classCourse->course->name ?? '—' }}</td>
                    <td>{{ $enrollment->classCourse->employee?->full_name ?? '—' }}</td>
                    <td class="numeric">{{ $enrollment->weighted_average !== null ? number_format($enrollment->weighted_average, 1) : '—' }}</td>
                    <td>{{ $enrollment->final_letter_grade ?? '—' }}</td>
                    <td class="numeric">{{ $enrollment->grade_points !== null ? number_format($enrollment->grade_points, 2) : '—' }}</td>
                    <td class="numeric">{{ number_format($enrollment->classCourse->course->credits ?? 1, 1) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 12px;">No enrollments for this class.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td class="label">Class GPA:</td>
                <td class="value">{{ number_format($classGpa, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Cumulative GPA:</td>
                <td class="value">{{ number_format($cumulativeGpa, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Generated on {{ $generatedAt->format('F d, Y \a\t g:i A') }}
    </div>
</body>
</html>
