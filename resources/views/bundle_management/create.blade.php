@extends('layouts.vertical', ['title' => 'Bundle Create'])

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@endsection

@section('content')
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Bundle Management</h4>
        </div>
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Bundle Management</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Bundle Create</h5>
                </div>
                <div class="card-body">
                    <form class="row g-3" action="{{ route('bundle-management.store') }}" method="POST" enctype="multipart/form-data">
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
                            <label for="name" class="form-label">Bundle Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" value="{{ old('name') }}" placeholder="Enter bundle name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" id="description" placeholder="Enter description">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" id="price" value="{{ old('price') }}" placeholder="Enter price" step="0.01" min="0" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" class="form-control @error('status') is-invalid @enderror" id="status">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->value }}" {{ old('status') == $status->value ? 'selected' : '' }}>{{ \Illuminate\Support\Str::title($status->value) }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Items</label>
                            <table class="table table-bordered" id="items-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Quantity</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (old('items'))
                                        @foreach (old('items', []) as $index => $item)
                                            <tr class="item-row">
                                                <td>
                                                    <select name="items[{{ $index }}][item_id]" class="form-control select2 @error('items.'.$index.'.item_id') is-invalid @enderror" required>
                                                        <option value="">Select an item</option>
                                                        @foreach ($items as $itemOption)
                                                            <option value="{{ $itemOption->id }}" {{ old('items.'.$index.'.item_id') == $itemOption->id ? 'selected' : '' }}>{{ $itemOption->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('items.'.$index.'.item_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="number" name="items[{{ $index }}][quantity]" class="form-control @error('items.'.$index.'.quantity') is-invalid @enderror" value="{{ old('items.'.$index.'.quantity', 1) }}" min="1" required>
                                                    @error('items.'.$index.'.quantity')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-danger remove-item-row">Remove</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="item-row">
                                            <td>
                                                <select name="items[0][item_id]" class="form-control select2 @error('items.0.item_id') is-invalid @enderror" required>
                                                    <option value="">Select an item</option>
                                                    @foreach ($items as $item)
                                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('items.0.item_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                            </td>
                                            <td>
                                                <input type="number" name="items[0][quantity]" class="form-control @error('items.0.quantity') is-invalid @enderror" value="1" min="1" required>
                                                @error('items.0.quantity')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger remove-item-row">Remove</button>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-sm btn-primary" id="add-item-row">Add Item</button>
                            @error('items')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        jQuery(document).ready(function($) {
            // Initialize Select2 for existing selects
            $('.select2').select2({
                placeholder: "Select an item",
                allowClear: true,
                width: '100%'
            });

            // Initialize item index
            let itemIndex = @php echo old('items', []) ? count(old('items')) : 1; @endphp

            // Add new item row
            $('#add-item-row').on('click', function() {
                const row = `
                    <tr class="item-row">
                        <td>
                            <select name="items[${itemIndex}][item_id]" class="form-control select2" required>
                                <option value="">Select an item</option>
                                @foreach ($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" name="items[${itemIndex}][quantity]" class="form-control" value="1" min="1" required>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-item-row">Remove</button>
                        </td>
                    </tr>
                `;
                $('#items-table tbody').append(row);
                // Reinitialize Select2 for the new select element
                $('#items-table tbody select.select2').last().select2({
                    placeholder: "Select an item",
                    allowClear: true,
                    width: '100%'
                });
                itemIndex++;
            });

            // Remove item row
            $(document).on('click', '.remove-item-row', function() {
                if ($('#items-table tbody tr.item-row').length > 1) {
                    $(this).closest('tr.item-row').remove();
                } else {
                    alert('At least one item is required.');
                }
            });
        });
    </script>
@endsection