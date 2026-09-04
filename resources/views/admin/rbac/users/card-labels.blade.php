<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RTFTS Staff Card Barcodes</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #111827;
            background: #f3f4f6;
            font-family: Arial, Helvetica, sans-serif;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            max-width: 194mm;
            margin: 16px auto;
            padding: 12px 14px;
            background: #fff;
            border: 1px solid #d8dee8;
            border-radius: 6px;
        }

        .toolbar h1 {
            margin: 0;
            color: #00284d;
            font-size: 18px;
        }

        .toolbar p {
            margin: 2px 0 0;
            color: #6b7280;
            font-size: 12px;
            font-weight: 700;
        }

        .print-button {
            border: 0;
            border-radius: 4px;
            padding: 9px 14px;
            background: #d4a017;
            color: #fff;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
        }

        .sheet {
            width: 194mm;
            min-height: 281mm;
            margin: 0 auto 16px;
            padding: 0;
            background: #fff;
            display: grid;
            grid-template-columns: repeat(2, 85.6mm);
            grid-auto-rows: 54mm;
            column-gap: 12mm;
            row-gap: 2mm;
            align-content: start;
            justify-content: center;
        }

        .card-label {
            position: relative;
            width: 85.6mm;
            height: 54mm;
            overflow: hidden;
            break-inside: avoid;
            page-break-inside: avoid;
            border: 1px dashed #9ca3af;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 6mm 7mm 5mm;
        }

        .employee-id {
            color: #00284d;
            font-size: 18px;
            line-height: 1;
            font-weight: 900;
            letter-spacing: 0;
            margin-bottom: 4mm;
        }

        .barcode {
            width: 60mm;
            height: 15mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .barcode svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .system-name {
            margin-top: 3mm;
            color: #00284d;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 0;
        }

        .system-subtitle {
            margin-top: 1mm;
            color: #6b7280;
            font-size: 8px;
            font-weight: 700;
        }

        .staff-name {
            position: absolute;
            left: 6mm;
            right: 6mm;
            bottom: 4mm;
            overflow: hidden;
            color: #374151;
            font-size: 10px;
            font-weight: 700;
            text-align: center;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .empty-message {
            max-width: 194mm;
            margin: 24px auto;
            padding: 16px;
            border: 1px solid #f3d58e;
            border-radius: 6px;
            background: #fff7e6;
            color: #805500;
            font-weight: 800;
        }

        @media print {
            body {
                background: #fff;
            }

            .toolbar {
                display: none;
            }

            .sheet {
                width: auto;
                min-height: auto;
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div>
            <h1>RTFTS Staff Card Barcodes</h1>
            <p>{{ $users->count() }} card{{ $users->count() === 1 ? '' : 's' }} ready for A4 print</p>
        </div>
        <button class="print-button" type="button" onclick="window.print()">Print</button>
    </div>

    @if($users->isEmpty())
        <div class="empty-message">No active staff/admin users with Card ID found.</div>
    @else
        <main class="sheet">
            @foreach($users as $user)
                <section class="card-label">
                    <div class="employee-id">{{ $user->employee_id }}</div>
                    <div class="barcode">{!! $user->barcode_svg !!}</div>
                    <div class="system-name">RTFTS</div>
                    <div class="system-subtitle">Real Time File Tracking System</div>
                    <div class="staff-name">{{ $user->name }}</div>
                </section>
            @endforeach
        </main>
    @endif
</body>
</html>
