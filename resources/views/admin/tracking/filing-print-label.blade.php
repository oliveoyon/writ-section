<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    @php
        $pageWidthMm = max(20, $widthMm - 2);
        $pageHeightMm = max(20, $heightMm - 3);
        $printPageWidthMm = min($widthMm, $heightMm);
        $printPageHeightMm = max($widthMm, $heightMm);
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
            padding: 6mm 8mm;
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            text-align: center;
            cursor: pointer;
        }
        .barcode-svg {
            width: 34mm;
            max-width: 100%;
            height: 10mm;
            display: block;
        }
        .barcode-svg svg {
            width: 100%;
            height: 100%;
            display: block;
            shape-rendering: crispEdges;
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
                width: 34mm;
                height: 10mm;
                border: none;
                padding: 0;
                position: absolute;
                top: 8mm;
                left: 18mm;
                transform: rotate(90deg);
                transform-origin: top left;
                page-break-after: avoid;
                break-after: avoid;
            }
            .barcode-svg {
                width: 34mm;
                height: 10mm;
            }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button type="button" id="printButton">{{ __('tracking.filing.print_now') }}</button>
        <a href="{{ route('admin.tracking.filing.print-label-pdf', ['case' => $case->id, 'width_mm' => $widthMm, 'height_mm' => $heightMm]) }}" target="_blank">
            {{ __('tracking.filing.print_pdf') }}
        </a>
        <a href="{{ route('admin.tracking.filing.print-label-tspl', ['case' => $case->id, 'width_mm' => $widthMm, 'height_mm' => $heightMm]) }}">
            GS 2406T File
        </a>
        <form method="POST"
              action="{{ route('admin.tracking.filing.print-label-direct', ['case' => $case->id]) }}"
              style="display:inline;">
            @csrf
            <input type="hidden" name="width_mm" value="{{ $widthMm }}">
            <input type="hidden" name="height_mm" value="{{ $heightMm }}">
            <button type="submit">Direct Print GS2406T</button>
        </form>
        @if(!empty($next))
            <a href="{{ $next }}">{{ __('tracking.filing.back_after_print') }}</a>
        @endif
    </div>
    <div class="hint" id="printHint">{{ __('tracking.filing.print_hint') }}</div>

    <div class="print-sheet">
        <div class="label" id="printableLabel" title="{{ __('tracking.filing.print_now') }}">
            <div class="barcode-svg" aria-label="barcode">{!! $barcodeSvg !!}</div>
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

        @if(!empty($next))
        window.onafterprint = function () {
            window.location.href = @json($next);
        };
        @endif

    </script>
</body>
</html>
