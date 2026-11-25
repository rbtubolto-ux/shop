<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Routing Slip - {{ $document->doc_number }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000; margin: 0; padding: 16px; background: #fff; }
        .slip { width: 720px; margin: 0 auto; border: 1px solid #000; }
        .head { text-align: center; padding: 10px 6px 6px; border-bottom: 1px solid #000; }
        .head .school { font-weight: bold; font-size: 13px; }
        .head .title { font-weight: bold; font-size: 13px; margin-top: 8px; }
        .row { border-bottom: 1px solid #000; padding: 6px 8px; min-height: 16px; }
        .grid { display: flex; }
        .grid > .left { width: 50%; border-right: 1px solid #000; }
        .grid > .right { width: 50%; padding: 6px 8px; }
        .box { display: inline-block; width: 12px; height: 12px; border: 1px solid #000; vertical-align: middle; margin-right: 8px; text-align: center; line-height: 12px; font-size: 10px; }
        .subject { padding: 6px 8px; }
        .subject div { margin: 2px 0; }
        .footer { font-weight: bold; font-size: 10px; padding: 6px 8px; }
        .label { font-weight: bold; }
        .print-btn { margin: 0 auto 12px; display: block; padding: 6px 14px; }
        @media print { .print-btn { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Print routing slip</button>

    <div class="slip">
        <div class="head">
            <div class="school">PHILIPPINE SCIENCE HIGH SCHOOL</div>
            <div>CAMPUS: {{ $document->campus->name ?? '________________' }}</div>
            <div class="title">ROUTING SLIP</div>
        </div>

        <div class="row"><span class="label">Date:</span> {{ ($document->date_submitted ?? $document->created_at)?->format('F d, Y') }}</div>

        <div class="row">
            <span class="label">Document:</span> {{ $document->doc_number }} &mdash; {{ $document->title }}
        </div>

        <div class="row">
            <span class="label">For/ To:</span>
            @if($current && $current->assignedUser)
                {{ $current->assignedUser->name }} ({{ $current->assignedUser->getRoleLabel() }}) &mdash; {{ $current->step_name }}
            @elseif($current)
                {{ $current->step_name }}
            @else
                &mdash;
            @endif
        </div>

        <div class="grid">
            <div class="left">
                <div class="label row">Subject:</div>
                <div class="subject">
                    @foreach($subjects as $subject)
                        <div>
                            <span class="box">{{ ($current && $current->subject === $subject) ? '✓' : '' }}</span>{{ $subject }}
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="right">
                <div class="label">Other instructions (please specify):</div>
                <div style="white-space: pre-wrap; margin-top: 6px;">{{ $document->routing_instructions }}</div>
            </div>
        </div>

        <div class="row"><span class="label">Due Date:</span> {{ $document->due_date?->format('F d, Y') }}</div>

        <div class="row"><span class="label">From:</span> {{ $document->submitter->name ?? '' }}{{ $document->originating_office ? ' ('.$document->originating_office.')' : '' }}</div>

        <div class="footer">PSHS-00-F-RMU-01-Ver02-Rev1-10/18/20</div>
    </div>
</body>
</html>
