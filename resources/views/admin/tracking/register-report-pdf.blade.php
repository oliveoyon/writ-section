<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('tracking.register.print_title') }}</title>
    <style>
        body {
            font-family: dejavusans, sans-serif;
            font-size: 11px;
            color: #222;
        }
        .header {
            text-align: center;
            margin-bottom: 8px;
        }
        .header h2 {
            margin: 0 0 3px;
            font-size: 16px;
        }
        .meta {
            margin-bottom: 10px;
            font-size: 10px;
        }
        .meta-row {
            margin-bottom: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #333;
            padding: 4px;
            vertical-align: top;
            word-wrap: break-word;
        }
        th {
            background: #f1f3f5;
            text-align: left;
        }
        .footer {
            margin-top: 14px;
            width: 100%;
        }
        .sign-wrap {
            margin-top: 28px;
        }
        .sign-line {
            display: inline-block;
            width: 240px;
            border-top: 1px solid #222;
            text-align: center;
            padding-top: 4px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
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
    @endphp
    <div class="header">
        <h2>{{ __('tracking.register.print_title') }}</h2>
        <div>{{ __('tracking.register.subtitle') }}</div>
    </div>

    <div class="meta">
        <div class="meta-row">{{ __('tracking.register.print_range') }}: {{ $dateFrom }} - {{ $dateTo }}</div>
        <div class="meta-row">{{ __('tracking.register.filter_mode') }}: {{ __('tracking.register.mode_' . $filterMode) }}</div>
        <div class="meta-row">{{ __('tracking.register.movement_scope') }}: {{ __($scopeLabelKey) }}</div>
        <div class="meta-row">{{ __('tracking.register.section') }}: {{ $section !== '' ? $section : __('tracking.register.all_sections') }}</div>
        <div class="meta-row">{{ __('tracking.register.movement_type') }}: {{ $movementType !== '' ? $movementType : __('tracking.register.all_types') }}</div>
        <div class="meta-row">{{ __('tracking.register.generated_at') }}: {{ optional($generatedAt ?? now())->format('Y-m-d h:i A') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:4%;">#</th>
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
                    <td>{{ optional($movement->received_at)->format('Y-m-d h:i A') }}</td>
                    <td>{{ $movement->courtCase?->final_case_number ?? ('CASE-' . ($movement->case_id ?? '')) }}</td>
                    <td>{{ $movement->barcode_scanned }}</td>
                    <td>{{ $movement->from_section ?? '-' }}</td>
                    <td>{{ $movement->to_section ?? '-' }}</td>
                    <td>{{ $movement->movement_type }}</td>
                    <td>{{ $movement->receivedBy?->name ?? '-' }}</td>
                    <td>{{ $movement->notes ?: ($movement->override_reason ?: '-') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">{{ __('tracking.register.no_data') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div><strong>{{ __('tracking.register.total') }}:</strong> {{ $movements->count() }}</div>

        <div class="sign-wrap text-right">
            <span class="sign-line">{{ __('tracking.register.authority_signature') }}</span>
        </div>
    </div>
</body>
</html>
