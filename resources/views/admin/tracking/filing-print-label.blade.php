<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    @php
        $pageWidthMm = max(20, $widthMm - 2);
        $pageHeightMm = max(20, $heightMm - 3);
        $printPageWidthMm = $widthMm;
        $printPageHeightMm = $heightMm;
    @endphp
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('tracking.filing.print_label_title') }}</title>
    <style>
        @page {
            size: {{ $pageWidthMm }}mm {{ $pageHeightMm }}mm;
            margin: 0;
        }

        * { box-sizing: border-box; }
        html {
            width: {{ $widthMm }}mm;
            height: {{ $heightMm }}mm;
        }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            width: {{ $widthMm }}mm;
            height: {{ $heightMm }}mm;
            overflow: hidden;
        }
        .print-sheet {
            width: {{ $widthMm }}mm;
            height: {{ $heightMm }}mm;
            position: relative;
        }
        .label {
            width: {{ $widthMm }}mm;
            height: {{ $heightMm }}mm;
            border: 1px dashed #777;
            padding: 1mm 3mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            cursor: pointer;
        }
        .barcode-svg {
            width: 42mm;
            max-width: 100%;
            height: 11mm;
            display: block;
            background: #fff;
        }
        .barcode-svg svg {
            width: 100%;
            height: 100%;
            display: block;
            shape-rendering: crispEdges;
            color-rendering: optimizeSpeed;
        }
        .barcode-text {
            margin-top: 0.8mm;
            color: #000;
            font-family: "Courier New", monospace;
            font-size: 9pt;
            font-weight: 700;
            line-height: 1;
            letter-spacing: 0.15mm;
            white-space: nowrap;
        }
        .case-reference {
            margin-bottom: 0.8mm;
            color: #000;
            font-size: 10pt;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
        }
        .actions {
            position: fixed;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .actions a, .actions button {
            padding: 6px 10px;
            border: 0;
            background: #00284d;
            color: #fff;
            text-decoration: none;
            cursor: pointer;
        }
        .hint {
            position: fixed;
            top: 48px;
            right: 10px;
            background: #fff3cd;
            color: #664d03;
            border: 1px solid #ffecb5;
            padding: 6px 10px;
            max-width: 300px;
            font-size: 12px;
            display: none;
        }
        @media print {
            @page {
                size: {{ $printPageWidthMm }}mm {{ $printPageHeightMm }}mm;
                margin: 0;
            }
            .actions { display: none; }
            .hint { display: none !important; }
            body {
                width: {{ $printPageWidthMm }}mm;
                height: {{ $printPageHeightMm }}mm;
                margin: 0;
                overflow: hidden;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .print-sheet {
                width: {{ $printPageWidthMm }}mm;
                height: {{ $printPageHeightMm }}mm;
                position: relative;
                overflow: hidden;
                page-break-after: avoid;
                break-after: avoid;
            }
            .label {
                width: {{ $printPageWidthMm }}mm;
                height: {{ $printPageHeightMm }}mm;
                border: none;
                padding: 0 2.5mm;
                background: #fff;
                position: static;
                transform: none;
                page-break-after: avoid;
                break-after: avoid;
            }
            .barcode-svg {
                width: 42mm;
                height: 11mm;
            }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button type="button" id="printButton">{{ __('tracking.filing.print_now') }}</button>
    </div>
    <div class="hint" id="printHint">{{ __('tracking.filing.print_hint') }}</div>

    <div class="print-sheet">
        <div class="label" id="printableLabel" title="{{ __('tracking.filing.print_now') }}">
            <div class="case-reference">{{ $case->case_reference }}</div>
            <div class="barcode-svg" aria-label="barcode">{!! $barcodeSvg !!}</div>
            <div class="barcode-text">{{ $case->permanent_barcode }}</div>
        </div>
    </div>

    <script>
        let hasPrinted = false;
        const hintEl = document.getElementById('printHint');

        function showHint() {
            if (hintEl) {
                hintEl.style.display = 'block';
            }
        }

        function triggerPrint() {
            if (typeof window.print !== 'function') {
                showHint();
                return;
            }

            waitForBarcode().then(function () {
                try {
                    window.focus();
                    window.print();
                    hasPrinted = true;
                } catch (e) {
                    showHint();
                }
            });
        }

        function waitForBarcode() {
            return Promise.resolve();
        }

        const printButton = document.getElementById('printButton');
        const printableLabel = document.getElementById('printableLabel');
        if (printButton) {
            printButton.addEventListener('click', function (event) {
                event.preventDefault();
                triggerPrint();
                setTimeout(function () {
                    if (!hasPrinted) {
                        showHint();
                    }
                }, 800);
            });
        }
        if (printableLabel) {
            printableLabel.addEventListener('click', triggerPrint);
        }

        @if($autoPrint)
        window.addEventListener('load', function () {
            setTimeout(triggerPrint, 150);
        });
        @endif

        @if(!empty($next))
        window.onafterprint = function () {
            window.location.href = @json($next);
        };
        @endif

    </script>
</body>
</html>
