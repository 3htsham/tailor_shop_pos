@extends('backend.layout.main') @section('content')
    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <div>
                                <h4 class="mb-0">Add Measurements</h4>
                                <small class="text-muted">Customer: <strong>{{ $customer->name }}</strong> &mdash; {{ $customer->phone_number }}</small>
                            </div>
                            <a href="{{ route('customer.measurements.index', $customer->id) }}" class="btn btn-secondary ml-auto">
                                <i class="dripicons-arrow-left"></i> Back
                            </a>
                        </div>
                        <div class="card-body">
                            <p class="italic"><small>{{ trans('file.The field labels marked with * are required input fields') }}.</small></p>

                            {!! Form::open(['route' => 'customer-measurements.store', 'method' => 'post', 'id' => 'measurement-form']) !!}
                            <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Sale Type *</label>
                                        <select name="sale_type_id" id="sale_type_id" class="form-control selectpicker"
                                            data-live-search="true" title="Select Sale Type..." required>
                                            @foreach ($sale_types as $type)
                                                <option value="{{ $type->id }}"
                                                    data-unit="{{ $type->measurement_unit }}">
                                                    {{ $type->name }} ({{ $type->measurement_unit }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 d-flex align-items-center" id="unit-display" style="display:none!important;">
                                    <span class="badge badge-info p-2" style="font-size:14px;">
                                        Unit: <strong id="unit-label"></strong>
                                    </span>
                                </div>
                            </div>

                            {{-- Dynamic measurement fields loaded via AJAX --}}
                            <div id="measurement-fields-container" class="row mt-3"></div>

                            <div class="row mt-3" id="notes-section" style="display:none;">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Notes</label>
                                        <textarea name="notes" rows="3" class="form-control"
                                            placeholder="Optional notes about this customer's measurements..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-3" id="submit-section" style="display:none;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="dripicons-checkmark"></i> {{ trans('file.submit') }}
                                </button>
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
    $(document).ready(function() {
        // Init selectpicker
        $('#sale_type_id').selectpicker('refresh');

        $('#sale_type_id').on('change', function() {
            var saleTypeId = $(this).val();
            var unit = $(this).find('option:selected').data('unit');

            if (!saleTypeId) {
                $('#measurement-fields-container').empty();
                $('#notes-section, #submit-section, #unit-display').hide();
                return;
            }

            // Show unit badge
            $('#unit-label').text(unit);
            $('#unit-display').show();

            // Fetch custom fields for the selected sale type
            $.get('{{ url('sale-types') }}/' + saleTypeId + '/custom-fields', function(data) {
                var fields = data.custom_fields;
                var container = $('#measurement-fields-container');
                container.empty();

                if (fields.length === 0) {
                    container.html('<div class="col-md-12"><div class="alert alert-warning">This Sale Type has no measurement fields defined.</div></div>');
                    $('#submit-section').hide();
                    return;
                }

                $.each(fields, function(i, field) {
                    var fieldKey = field.name.toLowerCase().replace(/ /g, '_');
                    var html = '<div class="col-md-3">' +
                        '<div class="form-group">' +
                        '<label>' + field.name + ' <small class="text-muted">(' + data.measurement_unit + ')</small> *</label>' +
                        '<input type="number" step="any" name="' + fieldKey + '" ' +
                        'class="form-control" placeholder="e.g. 36" required>' +
                        '</div></div>';
                    container.append(html);
                });

                $('#notes-section, #submit-section').show();
            });
        });
    });
</script>
@endpush
