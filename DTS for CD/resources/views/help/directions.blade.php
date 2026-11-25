@extends('layouts.app')
@section('title', 'Incoming and Outgoing Documents')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Help</li>
@endsection

@section('content')
<div class="page-header">
    <h4 class="mb-1 fw-700">Incoming and Outgoing Documents</h4>
    <p class="text-muted mb-0 small">User guide. Last updated 25 November 2026.</p>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h5 class="fw-600">What the two directions mean</h5>
        <p>Every document in the system is either <strong>Incoming</strong> or <strong>Outgoing</strong>. You choose the direction when you create the document, and it decides which list the document appears in.</p>
        <ul>
            <li><strong>Incoming:</strong> a document the campus receives from outside, or from another office. Examples: a request, an endorsement, or a memorandum addressed to the campus. Incoming is the default.</li>
            <li><strong>Outgoing:</strong> a document the campus sends out. Examples: a memorandum, a travel order, or a reply to an outside party.</li>
        </ul>
        <p class="small text-muted mb-0">FAM 13.1 section 3.3 says documents are "received and released" under the Records Management Officer. Incoming covers what is received, and Outgoing covers what is released.</p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h5 class="fw-600">Creating an incoming document</h5>
        <ol>
            <li>Click <strong>Submit Document</strong> in the sidebar.</li>
            <li>Under <strong>Direction</strong>, leave <strong>Incoming</strong> selected.</li>
            <li>Type the <strong>Title</strong> and choose a <strong>Category</strong>.</li>
            <li>Type the <strong>Originating office</strong> (the sender) and the <strong>Signatory</strong>, if there is one.</li>
            <li>Check <strong>Date submitted</strong>. It defaults to today. Set a <strong>Due date</strong> if the document has a deadline.</li>
            <li>Attach the file: PDF, Word, JPG, or PNG, up to 10 MB.</li>
            <li>Choose a <strong>Workflow template</strong> if the document has to be routed to offices for action.</li>
            <li>Fill in <strong>Cabinet</strong>, <strong>Drawer</strong>, and <strong>Container / Folder</strong>, and the <strong>Retention code</strong>.</li>
            <li>Click <strong>Save as draft</strong> to keep it for later, or <strong>Submit now</strong> to start routing.</li>
        </ol>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h5 class="fw-600">Creating an outgoing document</h5>
        <ol>
            <li>Click <strong>Submit Document</strong> in the sidebar.</li>
            <li>Under <strong>Direction</strong>, choose <strong>Outgoing</strong>.</li>
            <li>Type the <strong>Title</strong> and choose a <strong>Category</strong>.</li>
            <li>Type the <strong>Originating office</strong>: the campus office that prepared the document. Type the <strong>Signatory</strong>: the person who will sign it.</li>
            <li>Check <strong>Date submitted</strong>, and set a <strong>Due date</strong> if the document has a deadline.</li>
            <li>Attach the file: PDF, Word, JPG, or PNG, up to 10 MB.</li>
            <li>Choose a <strong>Workflow template</strong> so the document is routed for review and signature before it goes out.</li>
            <li>Fill in <strong>Cabinet</strong>, <strong>Drawer</strong>, and <strong>Container / Folder</strong>, and the <strong>Retention code</strong>.</li>
            <li>Click <strong>Save as draft</strong> to keep it, or <strong>Submit now</strong> to start routing.</li>
        </ol>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h5 class="fw-600">Finding documents by direction</h5>
        <ul>
            <li>Click <strong>Incoming</strong> or <strong>Outgoing</strong> in the sidebar. The document list opens on that direction.</li>
            <li>The same two tabs appear at the top of the document list. Click one to switch.</li>
            <li>The quick filters (<strong>Pending My Action</strong>, <strong>Pending</strong>, <strong>Over Due</strong>, <strong>Unacted</strong>, <strong>Deadlines</strong>) work within the direction you have open.</li>
            <li>On the <strong>Reports</strong> page, use the <strong>Direction</strong> filter to count incoming and outgoing documents for a period.</li>
        </ul>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h5 class="fw-600">Common mistakes and how to fix them</h5>
        <ul>
            <li><strong>A document is in the wrong list.</strong> The direction is set when the document is created. Check the <strong>Direction</strong> option before you save. If it was saved wrongly, ask the Document Controller to correct it.</li>
            <li><strong>You saved a draft and nothing was routed.</strong> A draft does not start routing. Open the document and click <strong>Submit</strong> when it is ready.</li>
            <li><strong>You can't see a document you created.</strong> Submitters see only the documents they created. Ask the office to check that it was saved under your account.</li>
            <li><strong>A confidential document is missing from the list.</strong> Confidential documents are shown only to managers and to the person who submitted them.</li>
        </ul>
    </div>
</div>
@endsection
