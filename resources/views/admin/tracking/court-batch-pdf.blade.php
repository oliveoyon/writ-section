<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('tracking.court.batch_document') }}</title>
    <style>
        body { font-family: solaimanlipi, dejavusans, sans-serif; font-size: 12px; color: #111; }
        .heading { text-align: center; margin-bottom: 10px; }
        .heading h3 { margin: 0 0 4px; }
        .meta { margin-bottom: 10px; }
        .meta p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 6px; }
        th { background: #f2f2f2; text-align: left; }
        .sign { margin-top: 24px; width: 100%; }
        .sign td { width: 50%; text-align: center; vertical-align: bottom; height: 70px; }
        .line { border-top: 1px solid #000; width: 220px; margin: 0 auto; padding-top: 4px; }
    </style>
</head>
<body>
    <div class="heading">
        <h3>{{ __('tracking.court.batch_document') }}</h3>
        <div>{{ __('tracking.court.batch_no') }}: {{ $batch->batch_no }}</div>
    </div>

    <div class="meta">
        <p><strong>{{ __('tracking.court.type') }}:</strong> {{ strtoupper($batch->type) }}</p>
        <p><strong>{{ __('tracking.court.court') }}:</strong> {{ app()->getLocale() === 'bn' ? $batch->court?->name_bn : $batch->court?->name_en }}</p>
        <p><strong>{{ __('tracking.court.created_by') }}:</strong> {{ $batch->createdBy?->name ?? '-' }}</p>
        <p><strong>{{ __('tracking.court.processed_time') }}:</strong> {{ optional($batch->dispatched_at ?? $batch->returned_at)->format('Y-m-d h:i A') }}</p>
        @if($batch->received_by_name)
            <p><strong>{{ __('tracking.court.received_by_name') }}:</strong> {{ $batch->received_by_name }}</p>
        @endif
        @if($batch->received_by_designation)
            <p><strong>{{ __('tracking.court.received_by_designation') }}:</strong> {{ $batch->received_by_designation }}</p>
        @endif
        @if($batch->received_by_phone)
            <p><strong>{{ __('tracking.court.received_by_phone') }}:</strong> {{ $batch->received_by_phone }}</p>
        @endif
        @if($batch->handover_to_section)
            <p><strong>{{ __('tracking.court.handover_to_section') }}:</strong> {{ $batch->handover_to_section }}</p>
        @endif
        @if($batch->notes)
            <p><strong>{{ __('tracking.court.notes_label') }}:</strong> {{ $batch->notes }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:6%;">#</th>
                <th style="width:22%;">{{ __('tracking.receive.barcode') }}</th>
                <th style="width:22%;">{{ __('tracking.register.case_no') }}</th>
                <th style="width:25%;">{{ __('tracking.register.from') }}</th>
                <th style="width:25%;">{{ __('tracking.register.to') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($batch->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->barcode_scanned }}</td>
                    <td>{{ $item->courtCase?->case_reference ?? ('CASE-' . ($item->case_id ?? '')) }}</td>
                    <td>{{ $item->from_section ?? '-' }}</td>
                    <td>{{ $item->to_section ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5">{{ __('tracking.receive.none') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="sign">
        <tr>
            <td><div class="line">{{ __('tracking.court.sender_signature') }}</div></td>
            <td><div class="line">{{ __('tracking.court.receiver_signature') }}</div></td>
        </tr>
    </table>
</body>
</html>
