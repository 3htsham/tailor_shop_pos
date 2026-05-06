@extends('backend.layout.main') @section('content')
    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <div>
                                <h4 class="mb-0">Edit Measurements</h4>
                                <small class="text-muted">
                                    Customer: <strong>{{ $customer->name }}</strong> &mdash;
                                    Sale Type: <span class="badge badge-primary">{{ $sale_type->name }}</span>
                                    <span class="badge badge-secondary ml-1">{{ $sale_type->measurement_unit }}</span>
                                </small>
                            </div>
                            <a href="{{ route('customer.measurements.index', $customer->id) }}" class="btn btn-secondary ml-auto">
                                <i class="dripicons-arrow-left"></i> Back
                            </a>
                        </div>
                        <div class="card-body">
                            <p class="italic"><small>{{ trans('file.The field labels marked with * are required input fields') }}.</small></p>

                            {!! Form::open(['route' => ['customer-measurements.update', $measurement->id], 'method' => 'put']) !!}

                            <div class="row">
                                @foreach ($custom_fields as $field)
                                    @php
                                        $key = str_replace(' ', '_', strtolower($field->name));
                                        $current_value = $measurement->measurements[$key] ?? '';
                                    @endphp
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>{{ $field->name }} <small class="text-muted">({{ $sale_type->measurement_unit }})</small> *</label>
                                            <input type="number" step="any" name="{{ $key }}"
                                                class="form-control"
                                                value="{{ $current_value }}"
                                                placeholder="e.g. 36" required>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Notes</label>
                                        <textarea name="notes" rows="3" class="form-control"
                                            placeholder="Optional notes...">{{ $measurement->notes }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="dripicons-checkmark"></i> {{ trans('file.update') }}
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
