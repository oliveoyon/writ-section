<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <title>{{ __('lawyer.case.top_sheet') }}</title>

    <style>
        html,
        body,
        table,
        tr,
        td,
        th,
        div,
        span,
        p,
        h1,
        h2,
        h3,
        h4 {
            font-family: solaimanlipi, sans-serif !important;
        }

        body {
            font-size: 12px;
            color: #111;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .small {
            font-size: 10px;
        }

        .wrap {
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        .header-wrap {
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .header-table td {
            vertical-align: top;
        }

        .header-center {
            width: 75%;
            text-align: left;
        }

        .header-right {
            width: 25%;
            text-align: right;
        }

        .court-title {
            font-size: 18px;
        }

        .court-sub {
            font-size: 16px;
        }

        .court-juris {
            font-size: 13px;
        }

        .doc-title {
            font-size: 15px;
            margin-top: 6px;
        }

        .system-name {
            font-size: 10px;
            font-weight: bold;
            margin-top: 5px;
        }

        .doc-sub {
            font-size: 12px;
            margin-top: 2px;
        }

        .barcode-wrap img {
            width: 220px;
            height: 55px;
        }

        .barcode-code {
            font-size: 11px;
            margin-top: 3px;
            text-align: center;
        }

        .rule {
            border-top: 1px solid #000;
            margin: 10px 0;
        }

        /* case info table */

        .info-grid td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        .label {
            width: 24%;
            background: #f2f2f2;
        }

        /* section title */

        .section-title {
            margin: 12px 0 6px;
            font-size: 13px;
        }

        /* data tables */

        .tbl th,
        .tbl td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        .tbl th {
            background: #f2f2f2;
            text-align: left;
        }

        /* petition block */

        .case-block {
            margin-top: 8px;
            border: 1px solid #000;
        }

        .case-block td {
            padding: 8px;
            vertical-align: top;
        }

        .case-left {
            width: 50%;
        }

        .case-right {
            width: 50%;
        }

        /* signature */

        .sign {
            margin-top: 28px;
        }

        .sign td {
            width: 50%;
            height: 70px;
            vertical-align: bottom;
        }

        .line {
            border-top: 1px solid #000;
            width: 220px;
            text-align: center;
            padding-top: 4px;
            margin-left: auto;
            margin-right: auto;
            font-size: 11px;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}

    <div class="header-wrap">

        <table class="header-table">

            <tr>

                <td class="header-center">

                    <div class="court-title">
                        SUPREME COURT OF BANGLADESH
                    </div>

                    <div class="court-sub">
                        HIGH COURT DIVISION
                    </div>

                    <div class="court-juris">
                        (SPECIAL ORIGINAL JURISDICTION)
                    </div>
                    <div class="system-name">
                        Real Time File Tracking System - RTFTS
                    </div>
                    <br>
                    <div class="doc-title">
                        {{ __('lawyer.case.top_sheet_official') }}
                    </div>

                    <div class="doc-sub">
                        {{ __('lawyer.case.top_sheet_subtitle') }}
                    </div>

                </td>


                <td class="header-right">

                    @if (!empty($barcode))
                        <div class="barcode-wrap">
                            <img src="data:image/png;base64,{{ $barcode }}">
                            <div class="barcode-code">
                                {{ __('lawyer.case.temp_id') }}: {{ $case->temporary_barcode ?? '' }}
                            </div>
                        </div>
                    @endif

                    <div class="small" style="margin-top:8px;">
                        {{ __('lawyer.case.generated_at') }}:<br>
                        <span>{{ now()->format('d-m-Y h:i A') }}</span>
                    </div>

                </td>

            </tr>

        </table>

    </div>


    {{-- COURT STYLE PETITION BLOCK --}}

    <table class="case-block">

        <tr>

            <td colspan="2">

                <div>IN THE SUPREME COURT OF BANGLADESH</div>
                <div>HIGH COURT DIVISION</div>
                <div>(SPECIAL ORIGINAL JURISDICTION)</div>

                <div style="margin-top:6px;">
                    IN THE MATTER OF: {{ $case->case_type ?? '-' }}
                </div>

            </td>

        </tr>


        <tr>

            <td class="case-left wrap">

                <div><strong>Petitioner(s):</strong></div>

                @if (($case->petitioners ?? collect())->count())

                    <ol style="margin:6px 0 0 18px; padding:0;">

                        @foreach ($case->petitioners as $p)
                            <li>{{ $p->name_or_organization ?? '-' }}</li>
                        @endforeach

                    </ol>
                @else
                    <div style="margin-top:6px;">
                        {{ __('lawyer.case.no_data') }}
                    </div>

                @endif

            </td>


            <td class="case-right wrap">

                <div><strong>Respondent(s):</strong></div>

                @if (($case->respondents ?? collect())->count())

                    <ol style="margin:6px 0 0 18px; padding:0;">

                        @foreach ($case->respondents as $r)
                            <li>{{ $r->name ?? '-' }}</li>
                        @endforeach

                    </ol>
                @else
                    <div style="margin-top:6px;">
                        {{ __('lawyer.case.no_data') }}
                    </div>

                @endif

            </td>

        </tr>

    </table>


    <div class="rule"></div>


    {{-- CASE INFORMATION --}}

    <table class="info-grid">

        <tr>

            <td class="label">
                {{ __('lawyer.case.temp_id') }}
            </td>

            <td class="wrap">
                {{ $case->temporary_barcode ?? '-' }}
            </td>

            <td class="label">
                {{ __('lawyer.case.case_type') }}
            </td>

            <td class="wrap">
                {{ $case->case_type ?? '-' }}
            </td>

        </tr>


        <tr>

            <td class="label">
                {{ __('lawyer.case.lawyer') }}
            </td>

            <td class="wrap">
                {{ $case->lawyer->full_name ?? '-' }}
            </td>

            <td class="label">
                {{ __('lawyer.case.bar_council_id') }}
            </td>

            <td class="wrap">
                {{ $case->lawyer->bar_council_id ?? '-' }}
            </td>

        </tr>


        <tr>

            <td class="label">
                {{ __('lawyer.case.subject') }}
            </td>

            <td colspan="3" class="wrap">
                {{ $case->subject ?? '-' }}
            </td>

        </tr>


        <tr>

            <td class="label">
                {{ __('lawyer.case.description') }}
            </td>

            <td colspan="3" class="wrap">
                {{ $case->description ?? '-' }}
            </td>

        </tr>

    </table>


    {{-- PETITIONERS TABLE --}}

    <div class="section-title">
        {{ __('lawyer.case.petitioners') }}
    </div>

    <table class="tbl">

        <thead>

            <tr>

                <th style="width:42%;">
                    {{ __('lawyer.case.name_or_organization') }}
                </th>

                <th style="width:30%;">
                    {{ __('lawyer.case.represented_by') }}
                </th>

                <th style="width:28%;">
                    {{ __('lawyer.case.phone') }}
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse($case->petitioners as $p)
                <tr>

                    <td class="wrap">
                        {{ $p->name_or_organization ?? '-' }}
                    </td>

                    <td class="wrap">
                        {{ $p->represented_by ?? '-' }}
                    </td>

                    <td class="wrap">
                        {{ $p->phone ?? '-' }}
                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="3">
                        {{ __('lawyer.case.no_data') }}
                    </td>

                </tr>
            @endforelse

        </tbody>

    </table>


    {{-- RESPONDENTS TABLE --}}

    <div class="section-title">
        {{ __('lawyer.case.respondents') }}
    </div>

    <table class="tbl">

        <thead>

            <tr>

                <th style="width:25%;">
                    {{ __('lawyer.case.name') }}
                </th>

                <th style="width:20%;">
                    {{ __('lawyer.case.designation') }}
                </th>

                <th style="width:23%;">
                    {{ __('lawyer.case.organization') }}
                </th>

                <th style="width:32%;">
                    {{ __('lawyer.case.address') }}
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse($case->respondents as $r)
                <tr>

                    <td class="wrap">
                        {{ $r->name ?? '-' }}
                    </td>

                    <td class="wrap">
                        {{ $r->designation ?? '-' }}
                    </td>

                    <td class="wrap">
                        {{ $r->organization ?? '-' }}
                    </td>

                    <td class="wrap">
                        {{ $r->address ?? '-' }}
                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="4">
                        {{ __('lawyer.case.no_data') }}
                    </td>

                </tr>
            @endforelse

        </tbody>

    </table>


    {{-- SIGNATURE --}}

    <table class="sign">

        <tr>

            <td>
                <div class="line">
                    {{ __('lawyer.case.lawyer_signature') }}
                </div>
            </td>

            <td>
                <div class="line">
                    {{ __('lawyer.case.filing_signature') }}
                </div>
            </td>

        </tr>

    </table>

</body>

</html>
