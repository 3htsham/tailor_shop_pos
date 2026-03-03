@extends('backend.layout.main') @section('content')
    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
    @endif
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ session()->get('message') }}</div>
    @endif
    <section>
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h4>Sale Types</h4>
                    <a href="{{ route('sale-types.create') }}" class="btn btn-info ml-auto"><i class="dripicons-plus"></i>
                        Add Sale Type</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="sale-type-table" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Measurement Unit</th>
                                    <th>Custom Fields</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sale_types as $key => $type)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $type->name }}</td>
                                        <td>{{ $type->measurement_unit }}</td>
                                        <td>{{ $type->customFields->count() }}</td>
                                        <td>
                                            @if ($type->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('sale-types.edit', $type->id) }}"
                                                class="btn btn-sm btn-primary" title="Edit"><i
                                                    class="dripicons-document-edit"></i></a>
                                            @if (\App\Models\Sale::where('garment_sale_type_id', $type->id)->count() == 0)
                                                <form action="{{ route('sale-types.destroy', $type->id) }}" method="POST"
                                                    style="display:inline;"
                                                    onsubmit="return confirm('Are you sure you want to delete this sale type and all its custom fields?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i
                                                            class="dripicons-trash"></i></button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#sale-type-table').DataTable({
                "order": [],
                'language': {
                    'lengthMenu': '_MENU_ records per page',
                    "info": 'Showing _START_ - _END_ (_TOTAL_)',
                    "search": '{{ trans('file.Search') }}',
                    'paginate': {
                        'previous': '{{ trans('file.Previous') }}',
                        'next': '{{ trans('file.Next') }}'
                    }
                },
                'columnDefs': [{
                    "orderable": false,
                    "targets": [5]
                }]
            });
        });
    </script>
@endpush
