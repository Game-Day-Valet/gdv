@extends('layouts.vertical', ['title' => 'View Terms & Conditions'])

@section('css')
    <style>
        .terms-content { line-height: 1.6; color: #333; }
        .terms-content h1, .terms-content h2, .terms-content h3, .terms-content h4, .terms-content h5, .terms-content h6 { margin-top: 1.5rem; margin-bottom: 1rem; color: #2c3e50; }
        .terms-content h1 { font-size: 2rem; border-bottom: 2px solid #3498db; padding-bottom: 0.5rem; }
        .terms-content h2 { font-size: 1.5rem; border-bottom: 1px solid #ecf0f1; padding-bottom: 0.3rem; }
        .terms-content h3 { font-size: 1.25rem; }
        .terms-content p { margin-bottom: 1rem; }
        .terms-content ul, .terms-content ol { margin-bottom: 1rem; padding-left: 2rem; }
        .terms-content li { margin-bottom: 0.5rem; }
        .terms-content blockquote { border-left: 4px solid #3498db; padding-left: 1rem; margin: 1rem 0; font-style: italic; background-color: #f8f9fa; padding: 1rem; }
        .terms-content code { background-color: #f8f9fa; padding: 0.2rem 0.4rem; border-radius: 3px; font-family: 'Courier New', monospace; }
        .terms-content a { color: #3498db; text-decoration: none; }
        .terms-content a:hover { text-decoration: underline; }
        .status-badge { font-size: 0.875rem; padding: 0.5rem 1rem; border-radius: 0.375rem; }
        .meta-info { background-color: #f8f9fa; border-radius: 0.5rem; padding: 1rem; margin-bottom: 2rem; }
        .meta-info .row { margin-bottom: 0.5rem; }
        .meta-info .col-md-6:last-child { margin-bottom: 0; }
    </style>
@endsection

@section('content')
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Terms & Conditions Management</h4>
        </div>
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="{{ route('terms-condition-management.index') }}">Terms & Conditions Management</a></li>
                <li class="breadcrumb-item active">View</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Terms & Conditions Details</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('terms-condition-management.edit', $terms->id) }}" class="btn btn-primary">Edit</a>
                        <a href="{{ route('terms-condition-management.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="meta-info">
                        <div class="row">
                            <div class="col-md-6"><strong>Title:</strong> {{ $terms->title }}</div>
                            <div class="col-md-6"><strong>Status:</strong>
                                @if($terms->status)
                                    <span class="badge bg-success status-badge">Active</span>
                                @else
                                    <span class="badge bg-danger status-badge">Inactive</span>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6"><strong>Created:</strong> {{ $terms->created_at->format('F j, Y \a\t g:i A') }}</div>
                            <div class="col-md-6"><strong>Last Updated:</strong> {{ $terms->updated_at->format('F j, Y \a\t g:i A') }}</div>
                        </div>
                    </div>

                    <div class="terms-content">
                        {!! $terms->description !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {});
    </script>
@endsection


