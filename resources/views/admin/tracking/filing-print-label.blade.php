<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('tracking.filing.print_label_title') }}</title>
    <style>
        @page {
            size: {{ $widthMm }}mm {{ $heightMm }}mm;
            margin: 2mm;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .label {
            width: {{ $widthMm - 4 }}mm;
            height: {{ $heightMm - 4 }}mm;
            border: 1px dashed #777;
            padding: 2mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        .code {
            font-size: 10px;
            margin-top: 2mm;
            word-break: break-all;
        }
        .meta {
            font-size: 9px;
            margin-top: 1.5mm;
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
            .actions { display: none; }
            .hint { display: none !important; }
            .label { border: none; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button type="button" id="printButton">{{ __('tracking.filing.print_now') }}</button>
        <a href="{{ route('admin.tracking.filing.print-label-pdf', ['case' => $case->id, 'width_mm' => $widthMm, 'height_mm' => $heightMm]) }}" target="_blank">
            {{ __('tracking.filing.print_pdf') }}
        </a>
        @if(!empty($next))
            <a href="{{ $next }}">{{ __('tracking.filing.back_after_print') }}</a>
        @endif
    </div>
    <div class="hint" id="printHint">{{ __('tracking.filing.print_hint') }}</div>

    <div class="label">
        <div><strong>{{ $case->final_case_number ?? '-' }}</strong></div>
        <img src="data:image/png;base64,{{ $barcodePng }}" alt="barcode" style="max-width: 100%; height: auto;">
        <div class="code">{{ $case->permanent_barcode }}</div>
        <div class="meta">{{ $case->subject }}</div>
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

            try {
                window.focus();
                window.print();
                hasPrinted = true;
            } catch (e) {
                showHint();
            }
        }

        const printButton = document.getElementById('printButton');
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

        @if(!empty($next))
        window.onafterprint = function () {
            window.location.href = @json($next);
        };
        @endif

        @if($autoPrint)
        window.addEventListener('load', function () {
            setTimeout(triggerPrint, 250);
            setTimeout(function () {
                if (!hasPrinted) {
                    triggerPrint();
                    showHint();
                }
            }, 1200);
        });
        @endif
    </script>
</body>
</html>
