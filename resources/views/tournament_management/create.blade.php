@extends('layouts.vertical', ['title' => 'Tournament Create'])

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css">
    <style>
        #description-editor .ql-container {
            min-height: 200px;
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            padding: 10px;
        }

        #description-editor .ql-toolbar {
            border: 1px solid #ced4da;
            border-bottom: none;
            border-radius: 0.25rem 0.25rem 0 0;
            background-color: #f8f9fa;
        }

        #description-editor.is-invalid .ql-container {
            border-color: #dc3545;
        }

        #description-editor {
            margin-bottom: 1.5rem;
        }
    </style>
@endsection

@section('content')
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Tournament Management</h4>
        </div>
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Tournament Management</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tournament Create</h5>
                </div>
                <div class="card-body">
                    <form class="row g-3" action="{{ route('tournament-management.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <label for="sport_id" class="form-label">Sport</label>
                            <select name="sport_id" class="form-control @error('sport_id') is-invalid @enderror"
                                id="sport_id" required>
                                <option value="">Select a sport</option>
                                @foreach ($sports as $sport)
                                    <option value="{{ $sport->id }}" {{ old('sport_id') == $sport->id ? 'selected' : '' }}>
                                        {{ $sport->name }}</option>
                                @endforeach
                            </select>
                            @error('sport_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Tournament Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                id="name" value="{{ old('name') }}" placeholder="Enter tournament name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="image" class="form-label">Tournament Image <span
                                    class="text-danger">*</span></label>
                            <input type="file" name="image" class="form-control @error('image') is-invalid @enderror"
                                id="image" accept="image/*" required>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Upload an image (JPEG, PNG, JPG, GIF) up to 2MB</small>
                        </div>
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date"
                                class="form-control @error('start_date') is-invalid @enderror" id="start_date"
                                value="{{ old('start_date') }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                                id="end_date" value="{{ old('end_date') }}">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="game_date" class="form-label">Game Date</label>
                            <input type="date" name="game_date"
                                class="form-control @error('game_date') is-invalid @enderror" id="game_date"
                                value="{{ old('game_date') }}">
                            @error('game_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="game_time" class="form-label">Game Time</label>
                            <input type="time" name="game_time"
                                class="form-control @error('game_time') is-invalid @enderror" id="game_time"
                                value="{{ old('game_time') }}">
                            @error('game_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
                                id="location" value="{{ old('location') }}" placeholder="Enter location" required>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" class="form-control @error('status') is-invalid @enderror" id="status">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->value }}" {{ old('status') == $status->value ? 'selected' : '' }}>
                                        {{ \Illuminate\Support\Str::title($status->value) }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="tax_rate" class="form-label">Tax Rate (%) Between</label>
                            <input type="number" step="0.01" min="0" max="100" name="tax_rate"
                                class="form-control @error('tax_rate') is-invalid @enderror" id="tax_rate"
                                value="{{ old('tax_rate') }}" placeholder="e.g. 7.5" required>
                            @error('tax_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small for="tax_rate" class="form-label" style="color: red;">
                                Tax Rate (%) Between 0-100
                            </small>
                        </div>
                        <div class="col-12 mb-4">
                            <label for="description" class="form-label">Description</label>
                            <div id="description-editor" class="form-control @error('description') is-invalid @enderror">
                            </div>
                            <textarea name="description" id="description"
                                style="display: none;">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 mt-5">
                            <hr>
                            <h5>Items for this Tournament</h5>
                            <div style="margin:6px 0 10px;">
                                <label style="display:flex;align-items:center;gap:8px;">
                                    <input type="checkbox" id="select_all_items" checked>
                                    <span>Select All</span>
                                </label>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th style="width:60px;">Enable</th>
                                            <th>Name</th>
                                            <th style="width:160px;">Price (optional)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(($items ?? []) as $it)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="items[{{ $it->id }}][enabled]" value="1"
                                                        checked>
                                                </td>
                                                <td>{{ $it->name }} <small class="text-muted">(default
                                                        {{ number_format($it->price, 2) }})</small></td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" name="items[{{ $it->id }}][price]"
                                                        class="form-control form-control-sm"
                                                        placeholder="Leave blank for default">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <h5 class="mt-4">Bundles for this Tournament</h5>
                            <div style="margin:6px 0 10px;">
                                <label style="display:flex;align-items:center;gap:8px;">
                                    <input type="checkbox" id="select_all_bundles" checked>
                                    <span>Select All</span>
                                </label>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th style="width:60px;">Enable</th>
                                            <th>Name</th>
                                            <th style="width:160px;">Price (optional)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(($bundles ?? []) as $b)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="bundles[{{ $b->id }}][enabled]" value="1"
                                                        checked>
                                                </td>
                                                <td>{{ $b->name }} <small class="text-muted">(default
                                                        {{ number_format($b->price, 2) }})</small></td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" name="bundles[{{ $b->id }}][price]"
                                                        class="form-control form-control-sm"
                                                        placeholder="Leave blank for default">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-12 mt-4">
                            <button class="btn btn-primary" type="submit">Submit form</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const quill = new Quill('#description-editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline'],
                        ['link', 'blockquote'],
                        [{ 'list': 'ordered' }, { 'list': 'bullet' }]
                    ]
                }
            });

            const descriptionInput = document.querySelector('#description');
            const oldDescription = @json(old('description'));
            if (oldDescription) {
                quill.clipboard.dangerouslyPasteHTML(oldDescription);
            }
            quill.on('text-change', function () {
                descriptionInput.value = quill.root.innerHTML;
            });

            function wireSelectAll(masterId, selector) {
                const master = document.getElementById(masterId);
                if (!master) return;
                const syncFromMaster = () => document.querySelectorAll(selector).forEach(c => c.checked = master.checked);
                const syncMaster = () => {
                    const list = Array.from(document.querySelectorAll(selector));
                    master.checked = list.length > 0 && list.every(c => c.checked);
                };
                master.addEventListener('change', syncFromMaster);
                document.querySelectorAll(selector).forEach(c => c.addEventListener('change', syncMaster));
                syncMaster();
            }
            wireSelectAll('select_all_items', 'input[type="checkbox"][name^="items"][name$="[enabled]"]');
            wireSelectAll('select_all_bundles', 'input[type="checkbox"][name^="bundles"][name$="[enabled]"]');
        });
    </script>
@endsection