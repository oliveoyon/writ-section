<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('tracking.register.print_title') }}</title>

    <style>
        html, body, table, tr, td, th, div, span, p, h1, h2, h3, h4, strong, b {
            font-family: solaimanlipi, sans-serif !important;
        }

        /* ✅ Official mPDF way: reserve space for header/footer */
        @page {
            header: html_CourtRegisterHeader;
            footer: html_CourtRegisterFooter;
            margin-top: 42mm;      /* tune this if header is taller */
            margin-bottom: 16mm;   /* footer space */
            margin-left: 8mm;
            margin-right: 8mm;
        }

        body {
            font-size: 10.5px;
            color: #111;
            margin: 0;
            padding: 0;
        }

        thead { display: table-header-group; }

        .mono { white-space: nowrap; }
        .wrap { word-break: break-word; overflow-wrap: anywhere; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        table { width: 100%; border-collapse: collapse; }

        table.report { table-layout: fixed; }
        table.report th, table.report td {
            border: 1px solid #333;
            padding: 4px 5px;
            vertical-align: top;
        }
        table.report th {
            background: #f1f3f5;
            text-align: left;
        }
    </style>
</head>

<body>
@php
    $scopeLabelKey = match ($movementScope) {
        'in' => 'tracking.register.scope_in',
        'out' => 'tracking.register.scope_out',
        default => 'tracking.register.scope_all_filter',
    };
    $printedAt = optional($generatedAt ?? now())->format('Y-m-d h:i A');
@endphp

<!-- Define header -->
<htmlpageheader name="CourtRegisterHeader" style="display:none;">
    <table style="width:100%; border-collapse:collapse; border-bottom:1px solid #000;">
        <tr>
            <td style="padding-bottom:6px; vertical-align:top;">
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="text-align:left; font-size:12px;">
                            <div>IN THE SUPREME COURT OF BANGLADESH</div>
                            <div>HIGH COURT DIVISION</div>
                            <div>(SPECIAL ORIGINAL JURISDICTION)</div>
                        </td>
                        <td style="text-align:right; font-size:10px;">
                            <div>{{ __('tracking.register.generated_at') }}:</div>
                            <div class="mono">{{ $printedAt }}</div>
                        </td>
                    </tr>
                </table>

                <table style="width:100%; border-collapse:collapse; margin-top:6px;">
                    <tr>
                        <td style="text-align:left; font-size:13px;">
                            <strong>{{ __('tracking.register.print_title') }}</strong>
                            <div style="font-size:11px; margin-top:2px;">{{ __('tracking.register.subtitle') }}</div>
                        </td>
                        <td style="text-align:right; font-size:11px;">
                            <strong>{{ __('tracking.register.print_range') }}</strong><br>
                            <span class="mono">{{ $dateFrom }} - {{ $dateTo }}</span>
                        </td>
                    </tr>
                </table>

                {{-- <table style="width:100%; border-collapse:collapse; font-size:9.5px; margin-top:6px;">
                    <tr>
                        <td style="border:1px solid #333; padding:4px; width:20%;">
                            <strong>{{ __('tracking.register.filter_mode') }}</strong><br>
                            {{ __('tracking.register.mode_' . $filterMode) }}
                        </td>
                        <td style="border:1px solid #333; padding:4px; width:20%;">
                            <strong>{{ __('tracking.register.movement_scope') }}</strong><br>
                            {{ __($scopeLabelKey) }}
                        </td>
                        <td style="border:1px solid #333; padding:4px; width:30%;">
                            <strong>{{ __('tracking.register.section') }}</strong><br>
                            {{ $section !== '' ? $section : __('tracking.register.all_sections') }}
                        </td>
                        <td style="border:1px solid #333; padding:4px; width:30%;">
                            <strong>{{ __('tracking.register.movement_type') }}</strong><br>
                            {{ $movementType !== '' ? $movementType : __('tracking.register.all_types') }}
                        </td>
                    </tr>
                </table> --}}

            </td>
        </tr>
    </table>
</htmlpageheader>

<!-- Define footer -->
<htmlpagefooter name="CourtRegisterFooter" style="display:none;">
    <table style="width:100%; border-collapse:collapse; border-top:1px solid #000; font-size:9.5px;">
        <tr>
            <td style="text-align:left; padding-top:6px;">
                {{ __('tracking.register.total') }}: {{ $movements->count() }}
            </td>
            <td style="text-align:right; padding-top:6px;">
                Page {PAGENO} of {nbpg}
            </td>
        </tr>
    </table>
</htmlpagefooter>

<!-- Main Report Table -->
<table class="report">
    <thead>
        <tr>
            <th style="width:4%;" class="text-center">#</th>
            <th style="width:11%;">{{ __('tracking.register.time') }}</th>
            <th style="width:12%;">{{ __('tracking.register.case_no') }}</th>
            <th style="width:14%;">{{ __('tracking.register.barcode') }}</th>
            <th style="width:12%;">{{ __('tracking.register.from') }}</th>
            <th style="width:12%;">{{ __('tracking.register.to') }}</th>
            <th style="width:9%;">{{ __('tracking.register.movement_type') }}</th>
            <th style="width:10%;">{{ __('tracking.register.by') }}</th>
            <th style="width:16%;">{{ __('tracking.register.notes') }}</th>
        </tr>
    </thead>

    <tbody>
        @forelse($movements as $i => $movement)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td class="mono">{{ optional($movement->received_at)->format('Y-m-d h:i A') }}</td>
                <td class="wrap">{{ $movement->courtCase?->case_reference ?? ('CASE-' . ($movement->case_id ?? '')) }}</td>
                <td class="mono">{{ $movement->barcode_scanned }}</td>
                <td class="wrap">{{ $movement->from_section ?? '-' }}</td>
                <td class="wrap">{{ $movement->to_section ?? '-' }}</td>
                <td class="wrap">{{ $movement->movement_type ?? '-' }}</td>
                <td class="wrap">{{ $movement->receivedBy?->name ?? '-' }}</td>
                <td class="wrap">{{ $movement->notes ?: ($movement->override_reason ?: '-') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center">{{ __('tracking.register.no_data') }}</td>
            </tr>
        @endforelse
    </tbody>
</table>

<table style="width:100%; margin-top:18px;">
    <tr>
        <td class="text-right">
            <div style="border-top:1px solid #222; width:220px; float:right; text-align:center; padding-top:4px; font-size:10px;">
                {{ __('tracking.register.authority_signature') }}
            </div>
        </td>
    </tr>
</table>

</body>
</html>
