@extends('backend.layout.main') @section('content')
    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4>Add Sale Type</h4>
                        </div>
                        <div class="card-body">
                            <p class="italic">
                                <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                            </p>
                            {!! Form::open(['route' => 'sale-types.store', 'method' => 'post']) !!}
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Name *</label>
                                        <input type="text" name="name" class="form-control" required
                                            placeholder="e.g. Pant, Shirt" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Measurement Unit *</label>
                                        <select name="measurement_unit" class="form-control" required>
                                            <option value="cm">Centimeters (cm)</option>
                                            <option value="inches">Inches</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <div class="mt-2">
                                            <input type="checkbox" name="is_active" value="1" checked />
                                            <label>Active</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <h5>Custom Fields (Measurements)</h5>
                                    <p class="text-muted"><small>Add measurement fields for this sale type. Each field will
                                            be a required text input in the sale form.</small></p>
                                </div>
                            </div>

                            <div id="custom-fields-container">
                                {{-- Dynamic fields will be added here --}}
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-info btn-sm" id="add-field-btn">
                                        <i class="dripicons-plus"></i> Add Custom Field
                                    </button>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary">
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        var fieldIndex = 0;

        $('#add-field-btn').on('click', function() {
            fieldIndex++;
            var html = '<div class="row mt-2 field-row" id="field-row-' + fieldIndex + '">' +
                '<div class="col-md-5">' +
                '<div class="form-group">' +
                '<input type="text" name="field_names[]" class="form-control" placeholder="Field Name (e.g. Waist, Length, Chest)" required />' +
                '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<button type="button" class="btn btn-danger btn-sm remove-field-btn" data-id="field-row-' +
                fieldIndex + '">' +
                '<i class="dripicons-trash"></i> Remove' +
                '</button>' +
                '</div>' +
                '</div>';
            $('#custom-fields-container').append(html);
        });

        $(document).on('click', '.remove-field-btn', function() {
            var id = $(this).data('id');
            $('#' + id).remove();
        });
    </script>
@endpush
