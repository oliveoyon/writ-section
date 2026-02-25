<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
        }
        .label {
            width: 100%;
            text-align: center;
        }
        .title {
            font-weight: bold;
            font-size: 10pt;
        }
        .code {
            margin-top: 4px;
            font-size: 8pt;
            word-wrap: break-word;
        }
        .subject {
            margin-top: 2px;
            font-size: 8pt;
        }
    </style>
</head>
<body>
    <div class="label">
        <div class="title">{{ $case->final_case_number ?? '-' }}</div>
        <div style="margin-top: 4px;">
            <img src="data:image/png;base64,{{ $barcodePng }}" alt="barcode" style="max-width: 100%; height: auto;">
        </div>
        <div class="code">{{ $case->permanent_barcode }}</div>
        <div class="subject">{{ $case->subject }}</div>
    </div>
</body>
</html>
