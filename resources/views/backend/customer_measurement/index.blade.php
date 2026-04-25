@extends('backend.layout.main') @section('content')
    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
    @endif
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('message') }}</div>
    @endif

    <section>
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <div>
                        <h4 class="mb-0">Measurements</h4>
                        <small class="text-muted">Customer: <strong>{{ $customer->name }}</strong> &mdash; {{ $customer->phone_number }}</small>
                    </div>
                    <div class="ml-auto">
                        <a href="{{ route('customer.measurements.create', $customer->id) }}" class="btn btn-info">
                            <i class="dripicons-plus"></i> Add Measurements
                        </a>
                        <a href="{{ route('customer.index') }}" class="btn btn-secondary ml-1">
                            <i class="dripicons-arrow-left"></i> Back to Customers
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if ($measurements->isEmpty())
                        <div class="text-center py-5">
                            <i class="dripicons-ruler" style="font-size:48px; color:#ccc;"></i>
                            <p class="mt-3 text-muted">No measurements recorded yet for this customer.</p>
                            <a href="{{ route('customer.measurements.create', $customer->id) }}" class="btn btn-info">
                                <i class="dripicons-plus"></i> Add First Measurement
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table id="measurements-table" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Sale Type</th>
                                        <th>Unit</th>
                                        <th>Fields</th>
                                        <th>Notes</th>
                                        <th>Last Updated</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($measurements as $key => $measurement)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td><span class="badge badge-primary">{{ $measurement->saleType->name ?? 'N/A' }}</span></td>
                                            <td>{{ $measurement->saleType->measurement_unit ?? '' }}</td>
                                            <td>
                                                @foreach ($measurement->measurements as $field => $value)
                                                    <span class="badge badge-light border mr-1 mb-1">
                                                        {{ ucwords(str_replace('_', ' ', $field)) }}: <strong>{{ $value }}</strong>
                                                    </span>
                                                @endforeach
                                            </td>
                                            <td>{{ $measurement->notes ?? '—' }}</td>
                                            <td>{{ $measurement->updated_at->format('d M Y') }}</td>
                                            <td>
                                                <a href="{{ route('customer-measurements.edit', $measurement->id) }}"
                                                    class="btn btn-sm btn-primary" title="Edit">
                                                    <i class="dripicons-document-edit"></i>
                                                </a>
                                                <form action="{{ route('customer-measurements.destroy', $measurement->id) }}"
                                                    method="POST" style="display:inline;"
                                                    onsubmit="return confirm('Delete this measurement record?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="dripicons-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#measurements-table').DataTable({
            "order": [],
            'language': {
                'lengthMenu': '_MENU_ records per page',
                "info": 'Showing _START_ - _END_ (_TOTAL_)',
                "search": '{{ trans('file.Search') }}',
                'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
                }
            },
            'columnDefs': [{ "orderable": false, "targets": [3, 6] }]
        });
    });
</script>
@endpush
