<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Barcode — {{ $document->doc_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        * { font-family: Arial, sans-serif; }
        body { background: #fff; }
        .barcode-card {
            border: 2px solid #333;
            border-radius: 8px;
            padding: 20px;
            max-width: 400px;
            margin: 40px auto;
            text-align: center;
        }
        .doc-number { font-size: 1.1rem; font-weight: 700; margin-bottom: 4px; }
        .doc-title  { font-size: 0.85rem; color: #555; margin-bottom: 12px; }
        .doc-meta   { font-size: 0.75rem; color: #777; margin-top: 8px; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="barcode-card">
        <div class="doc-number">{{ $document->doc_number }}</div>
        <div class="doc-title">{{ $document->title }}</div>
        <svg id="barcode"></svg>
        <div class="doc-meta">
            {{ $document->category }} &bull; {{ $document->direction }} &bull; {{ $document->status }}
        </div>
        <div class="doc-meta">{{ $document->campus->name ?? '' }}</div>
        @if($document->cabinet || $document->drawer || $document->container)
        <div class="doc-meta mt-1">
            📁 {{ implode(' / ', array_filter([$document->cabinet, $document->drawer, $document->container])) }}
        </div>
        @endif

        @if($document->signature_path)
        <div style="margin-top:16px;padding-top:12px;border-top:1px solid #ddd;">
            <div style="font-size:0.7rem;color:#777;margin-bottom:6px;">APPROVED BY</div>
            <img src="{{ asset('storage/'.$document->signature_path) }}"
                 alt="Signature" style="max-height:70px;max-width:200px;">
            <div style="font-size:0.72rem;color:#555;margin-top:4px;">
                {{ $document->approver->name ?? '' }}
                @if($document->approved_at)
                    — {{ $document->approved_at->format('M d, Y') }}
                @endif
            </div>
        </div>
        @elseif($document->status === 'Approved')
        <div style="margin-top:12px;padding:6px 12px;background:#d1fae5;border-radius:6px;display:inline-block;">
            <span style="color:#065f46;font-size:0.78rem;font-weight:700;">✓ APPROVED</span>
            @if($document->approved_at)
            <span style="color:#065f46;font-size:0.72rem;"> — {{ $document->approved_at->format('M d, Y') }}</span>
            @endif
        </div>
        @endif
    </div>

    <div class="text-center mt-3 no-print">
        <button onclick="window.print()" class="btn btn-primary btn-sm">
            <i class="bi bi-printer me-1"></i>Print Barcode
        </button>
        <a href="{{ route('documents.show', $document) }}" class="btn btn-light btn-sm ms-2">Back</a>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script>
        JsBarcode("#barcode", "{{ $document->barcode ?? $document->doc_number }}", {
            format: "CODE128",
            width: 2,
            height: 60,
            displayValue: true,
            fontSize: 12,
            margin: 10
        });
    </script>
</body>
</html>
