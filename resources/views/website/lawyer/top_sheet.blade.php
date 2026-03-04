<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('lawyer.case.top_sheet') }}</title>

    <style>
        /* GLOBAL FORCE */
        html, body, * {
            font-family: solaimanlipi !important;
        }

        /* TABLE FORCE (THIS IS THE KEY FIX) */
        table, tr, td, th {
            font-family: solaimanlipi !important;
        }

        .top-meta, .top-meta td,
        .info-grid, .info-grid td,
        .tbl, .tbl td, .tbl th,
        .heading, .heading h2, .heading .sub {
            font-family: solaimanlipi !important;
        }

        body {
            font-size: 12px;
            color: #111;
            margin: 0;
            padding: 0;
        }

        table { border-collapse: collapse; }

        .top-meta { width: 100%; margin-bottom: 8px; }
        .top-meta td { vertical-align: top; }

        .heading { text-align: center; }
        .heading h2 { margin: 0; font-size: 20px; font-weight: bold; }
        .heading .sub { margin-top: 3px; font-size: 13px; }

        .barcode-wrap { text-align: right; }
        .barcode-wrap img { width: 220px; height: 55px; }
        .barcode-code { font-size: 11px; margin-top: 3px; text-align: center; }

        .rule { border-top: 1px solid #000; margin: 8px 0 10px; }

        .info-grid { width: 100%; margin-bottom: 10px; }
        .info-grid td { border: 1px solid #000; padding: 6px; vertical-align: top; }

        .label { font-weight: bold; width: 24%; background: #f2f2f2; }

        .section-title { margin: 10px 0 6px; font-weight: bold; font-size: 13px; }

        .tbl { width: 100%; margin-bottom: 10px; }
        .tbl th, .tbl td { border: 1px solid #000; padding: 6px; vertical-align: top; }
        .tbl th { background: #f2f2f2; font-weight: bold; text-align: left; }

        .sign { width: 100%; margin-top: 20px; }
        .sign td { width: 50%; height: 60px; vertical-align: bottom; }

        .line {
            border-top: 1px solid #000;
            width: 220px;
            text-align: center;
            padding-top: 4px;
            margin-left: auto;
            margin-right: auto;
            font-size: 11px;
        }

        .small { font-size: 10px; }

        .wrap { word-break: break-word; overflow-wrap: anywhere; }
    </style>
</head>

<body>

    <table class="top-meta">
        <tr>
            <td style="width:24%;" class="small">
                {{ __('lawyer.case.generated_at') }}: {{ now()->format('Y-m-d h:i A') }}
            </td>

            <td style="width:52%;" class="heading">
                <h2>বাংলা পরীক্ষা</h2>
                <div class="sub">{{ __('lawyer.case.top_sheet_subtitle') }}</div>
            </td>

            <td style="width:24%;" class="barcode-wrap">
                @if(!empty($barcode))
                    <img src="data:image/png;base64,{{ $barcode }}" alt="{{ __('lawyer.case.barcode') }}">
                @endif
                <div class="barcode-code">{{ $case->temporary_barcode ?? '' }}</div>
            </td>
        </tr>
    </table>

    <div class="rule"></div>

    <table class="info-grid">
        <tr>
            <td class="label">{{ __('lawyer.case.temp_id') }}</td>
            <td class="wrap">{{ $case->temporary_barcode ?? '-' }}</td>
            <td class="label">{{ __('lawyer.case.case_type') }}</td>
            <td class="wrap">{{ $case->case_type ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('lawyer.case.lawyer') }}</td>
            <td class="wrap">{{ $case->lawyer->full_name ?? '-' }}</td>
            <td class="label">{{ __('lawyer.case.bar_council_id') }}</td>
            <td class="wrap">{{ $case->lawyer->bar_council_id ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('lawyer.case.subject') }}</td>
            <td colspan="3" class="wrap">{{ $case->subject ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('lawyer.case.description') }}</td>
            <td colspan="3" class="wrap">{{ $case->description ?? '-' }}</td>
        </tr>
    </table>

    <div class="section-title">{{ __('lawyer.case.petitioners') }}</div>

    <table class="tbl">
        <thead>
            <tr>
                <th style="width:42%;">{{ __('lawyer.case.name_or_organization') }}</th>
                <th style="width:30%;">{{ __('lawyer.case.represented_by') }}</th>
                <th style="width:28%;">{{ __('lawyer.case.phone') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($case->petitioners as $p)
                <tr>
                    <td class="wrap">{{ $p->name_or_organization ?? '-' }}</td>
                    <td class="wrap">{{ $p->represented_by ?? '-' }}</td>
                    <td class="wrap">{{ $p->phone ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">{{ __('lawyer.case.no_data') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">{{ __('lawyer.case.respondents') }}</div>

    <table class="tbl">
        <thead>
            <tr>
                <th style="width:25%;">{{ __('lawyer.case.name') }}</th>
                <th style="width:20%;">{{ __('lawyer.case.designation') }}</th>
                <th style="width:23%;">{{ __('lawyer.case.organization') }}</th>
                <th style="width:32%;">{{ __('lawyer.case.address') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($case->respondents as $r)
                <tr>
                    <td class="wrap">{{ $r->name ?? '-' }}</td>
                    <td class="wrap">{{ $r->designation ?? '-' }}</td>
                    <td class="wrap">{{ $r->organization ?? '-' }}</td>
                    <td class="wrap">{{ $r->address ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">{{ __('lawyer.case.no_data') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="sign">
        <tr>
            <td>
                <div class="line">{{ __('lawyer.case.lawyer_signature') }}</div>
            </td>
            <td>
                <div class="line">{{ __('lawyer.case.filing_signature') }}</div>
            </td>
        </tr>
    </table>

</body>
</html>