@extends('backend.layout.main') @section('content')
@if($errors->has('name'))
<div class="alert alert-danger alert-dismissible text-center">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ $errors->first('name') }}</div>
@endif
@if($errors->has('code'))
<div class="alert alert-danger alert-dismissible text-center">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ $errors->first('code') }}</div>
@endif
@if(session()->has('message'))
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('message') }}</div>
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif

<section>
    <div class="container-fluid">
        @if($canAddTask)
        <button class="btn btn-info" data-toggle="modal" data-target="#createModal"><i class="dripicons-plus"></i> Add Task </button>&nbsp;
        @endif
    </div>
    <div class="table-responsive">
        <table id="task-table" class="table">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>Task Name</th>
                    <th>Default Price</th>
                    @if($canEditTask || $canDeleteTask)
                    <th class="not-exported">{{trans('file.action')}}</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($task_all as $key => $task_data)
                <tr data-id="{{$task_data->id}}">
                    <td></td>
                    <td>{{ $task_data->task_name }}</td>
                    <td>{{ $task_data->default_price }}</td>
                    @if($canEditTask || $canDeleteTask)
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{trans('file.action')}}
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                @if($canEditTask)
                                <li><button type="button" data-id="{{$task_data->id}}" data-name="{{$task_data->task_name}}" data-price="{{$task_data->default_price}}" class="edit-btn btn btn-link" data-toggle="modal" data-target="#editModal"><i class="dripicons-document-edit"></i> {{trans('file.edit')}}</button></li>
                                @endif
                                <li class="divider"></li>
                                @if($canDeleteTask && $task_data->task_assignments_count == 0)
                                {{ Form::open(['route' => ['tasks.destroy', $task_data->id], 'method' => 'DELETE'] ) }}
                                <li>
                                    <button type="submit" class="btn btn-link" onclick="return confirm('Are you sure want to delete?')"><i class="dripicons-trash"></i> {{trans('file.delete')}}</button>
                                </li>
                                {{ Form::close() }}
                                @endif
                            </ul>
                        </div>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<div id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
      <div class="modal-content">
        {!! Form::open(['route' => 'tasks.store', 'method' => 'post', 'files' => true]) !!}
        <div class="modal-header">
          <h5 id="exampleModalLabel" class="modal-title">Add Task</h5>
          <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
        </div>
        <div class="modal-body">
          <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
            <div class="form-group">
                <label>{{trans('file.name')}} *</label>
                {{Form::text('task_name',null,array('required' => 'required', 'class' => 'form-control', 'placeholder' => 'Type task name...'))}}
            </div>
            <div class="form-group">
                <label>Default Price *</label>
                {{Form::text('default_price',null,array('required' => 'required', 'class' => 'form-control', 'placeholder' => 'Type default price...'))}}
            </div>
            <div class="form-group">
              <input type="submit" value="{{trans('file.submit')}}" class="btn btn-primary">
            </div>
        </div>
        {{ Form::close() }}
      </div>
    </div>
</div>

<div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
  <div role="document" class="modal-dialog">
    <div class="modal-content">
        {{ Form::open(['route' => ['tasks.update', 1], 'method' => 'PUT', 'files' => true] ) }}
      <div class="modal-header">
        <h5 id="exampleModalLabel" class="modal-title">Update Task</h5>
        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
      </div>
      <div class="modal-body">
          <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
            <div class="form-group">
                <label>{{trans('file.name')}} *</label>
                {{Form::text('task_name',null,array('required' => 'required', 'class' => 'form-control', 'placeholder' => 'Type task name...'))}}
            </div>
            <div class="form-group">
                <label>Default Price *</label>
                {{Form::text('default_price',null,array('required' => 'required', 'class' => 'form-control', 'placeholder' => 'Type default price...'))}}
            </div>
            <input type="hidden" name="task_id">
            <div class="form-group">
              <input type="submit" value="{{trans('file.submit')}}" class="btn btn-primary">
            </div>
        </div>
      {{ Form::close() }}
    </div>
  </div>
</div>



@endsection

@push('scripts')
<script type="text/javascript">
    $("ul#tasks").siblings('a').attr('aria-expanded','true');
    $("ul#tasks").addClass("show");
    $("ul#tasks #task-list-menu").addClass("active");

    $(document).ready(function() {
        $(document).on('click', '.edit-btn', function() {
            $("#editModal input[name='task_id']").val($(this).data('id'));
            $("#editModal input[name='task_name']").val($(this).data('name'));
            $("#editModal input[name='default_price']").val($(this).data('price'));
        });
    });

    $('#task-table').DataTable( {
        "order": [],
        'language': {
            'lengthMenu': '_MENU_ {{trans("file.records per page")}}',
             "info":      '<small>{{trans("file.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
            "search":  '{{trans("file.Search")}}',
            'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
            }
        },
        'columnDefs': [
            {
                "orderable": false,
                'targets': [0]
            },
            {
                'render': function(data, type, row, meta){
                    if(type === 'display'){
                        data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                    }

                   return data;
                },
                'checkboxes': {
                   'selectRow': true,
                   'selectAllRender': '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>'
                },
                'targets': [0]
            }
        ],
        'select': { style: 'multi',  selector: 'td:first-child'},
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: '<"row"lfB>rtip',
        buttons: [
            {
                extend: 'pdf',
                text: '<i title="export to pdf" class="fa fa-file-pdf-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible',
                    stripHtml: false
                },
                customize: function(doc) {
                    for (var i = 1; i < doc.content[1].table.body.length; i++) {
                        if (doc.content[1].table.body[i][0].text.indexOf('<img src=') !== -1) {
                            var imagehtml = doc.content[1].table.body[i][0].text;
                            var regex = /<img.*?src=['"](.*?)['"]/;
                            var src = regex.exec(imagehtml)[1];
                            var tempImage = new Image();
                            tempImage.src = src;
                            var canvas = document.createElement("canvas");
                            canvas.width = tempImage.width;
                            canvas.height = tempImage.height;
                            var ctx = canvas.getContext("2d");
                            ctx.drawImage(tempImage, 0, 0);
                            var imagedata = canvas.toDataURL("image/png");
                            delete doc.content[1].table.body[i][0].text;
                            doc.content[1].table.body[i][0].image = imagedata;
                            doc.content[1].table.body[i][0].fit = [30, 30];
                        }
                    }
                },
            },
            {
                extend: 'excel',
                text: '<i title="export to excel" class="dripicons-document-new"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible',
                    format: {
                        body: function ( data, row, column, node ) {
                            if (column === 0 && (data.indexOf('<img src=') !== -1)) {
                                var regex = /<img.*?src=['"](.*?)['"]/;
                                data = regex.exec(data)[1];
                            }
                            return data;
                        }
                    }
                },
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible',
                    format: {
                        body: function ( data, row, column, node ) {
                            if (column === 0 && (data.indexOf('<img src=') !== -1)) {
                                var regex = /<img.*?src=['"](.*?)['"]/;
                                data = regex.exec(data)[1];
                            }
                            return data;
                        }
                    }
                },
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible',
                    stripHtml: false
                },
            },
            {
                extend: 'colvis',
                text: '<i title="column visibility" class="fa fa-eye"></i>',
                columns: ':gt(0)'
            },
        ],
    } );

</script>
@endpush
