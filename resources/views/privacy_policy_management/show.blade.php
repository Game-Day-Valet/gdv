@extends('layouts.vertical', ['title' => 'View Privacy Policy'])

@section('css')
    <style>
        .privacy-policy-content {
            line-height: 1.6;
            color: #333;
        }
        .privacy-policy-content h1,
        .privacy-policy-content h2,
        .privacy-policy-content h3,
        .privacy-policy-content h4,
        .privacy-policy-content h5,
        .privacy-policy-content h6 {
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            color: #2c3e50;
        }
        .privacy-policy-content h1 {
            font-size: 2rem;
            border-bottom: 2px solid #3498db;
            padding-bottom: 0.5rem;
        }
        .privacy-policy-content h2 {
            font-size: 1.5rem;
            border-bottom: 1px solid #ecf0f1;
            padding-bottom: 0.3rem;
        }
        .privacy-policy-content h3 {
            font-size: 1.25rem;
        }
        .privacy-policy-content p {
            margin-bottom: 1rem;
        }
        .privacy-policy-content ul,
        .privacy-policy-content ol {
            margin-bottom: 1rem;
            padding-left: 2rem;
        }
        .privacy-policy-content li {
            margin-bottom: 0.5rem;
        }
        .privacy-policy-content blockquote {
            border-left: 4px solid #3498db;
            padding-left: 1rem;
            margin: 1rem 0;
            font-style: italic;
            background-color: #f8f9fa;
            padding: 1rem;
        }
        .privacy-policy-content code {
            background-color: #f8f9fa;
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .privacy-policy-content a {
            color: #3498db;
            text-decoration: none;
        }
        .privacy-policy-content a:hover {
            text-decoration: underline;
        }
        .status-badge {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
        }
        .meta-info {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        .meta-info .row {
            margin-bottom: 0.5rem;
        }
        .meta-info .col-md-6:last-child {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Privacy Policy Management</h4>
        </div>
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="{{ route('privacy-policy-management.index') }}">Privacy Policy Management</a></li>
                <li class="breadcrumb-item active">View</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Privacy Policy Details</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('privacy-policy-management.edit', $privacyPolicy->id) }}" class="btn btn-primary">Edit</a>
                        <a href="{{ route('privacy-policy-management.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Meta Information -->
                    <div class="meta-info">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Title:</strong> {{ $privacyPolicy->title }}
                            </div>
                            <div class="col-md-6">
                                <strong>Status:</strong>
                                @if($privacyPolicy->status)
                                    <span class="badge bg-success status-badge">Active</span>
                                @else
                                    <span class="badge bg-danger status-badge">Inactive</span>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Created:</strong> {{ $privacyPolicy->created_at->format('F j, Y \a\t g:i A') }}
                            </div>
                            <div class="col-md-6">
                                <strong>Last Updated:</strong> {{ $privacyPolicy->updated_at->format('F j, Y \a\t g:i A') }}
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="privacy-policy-content">
                        {!! $privacyPolicy->description !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Add any additional JavaScript if needed
        document.addEventListener('DOMContentLoaded', function() {
            // You can add any interactive features here
        });
    </script>
@endsection
