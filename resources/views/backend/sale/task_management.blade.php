@extends('backend.layout.main')
@section('content')
<section>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h4>{{trans('file.Task Management')}} - Sale Reference: {{$sale->reference_no}}</h4>
            </div>
            <div class="card-body">
                @if(session()->has('message'))
                    <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('message') }}</div>
                @endif
                
                <div class="row">
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header">
                                <h5>Assign Task</h5>
                            </div>
                            <div class="card-body">
                                {!! Form::open(['route' => 'sales.task.store', 'method' => 'post']) !!}
                                <input type="hidden" name="sale_id" value="{{$sale->id}}">
                                
                                <div class="form-group">
                                    <label><strong>Task *</strong></label>
                                    <select name="task_id" class="form-control selectpicker" required data-live-search="true">
                                        <option value="">Select Task</option>
                                        @foreach($tasks as $task)
                                            <option value="{{$task->id}}" 
                                                @if($last_assignment && $last_assignment->task_id == $task->id) selected @endif
                                            >{{$task->task_name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label><strong>Employee *</strong></label>
                                    <select name="employee_id" class="form-control selectpicker" required data-live-search="true">
                                        <option value="">Select Employee</option>
                                        @foreach($employees as $employee)
                                            <option value="{{$employee->id}}"
                                                @if($last_assignment && $last_assignment->employee_id == $employee->id) selected @endif
                                            >{{$employee->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label><strong>Status *</strong></label>
                                    <select name="status" class="form-control" required>
                                        <option value="Pending" @if($last_assignment && $last_assignment->status == 'Pending') selected @endif>Pending</option>
                                        <option value="In-Progress" @if($last_assignment && $last_assignment->status == 'In-Progress') selected @endif>In-Progress</option>
                                        <option value="Completed" @if($last_assignment && $last_assignment->status == 'Completed') selected @endif>Completed</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label><strong>Status Date</strong></label>
                                    <input type="date" name="status_date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>

                                <div class="form-group">
                                    <input type="submit" value="{{trans('file.submit')}}" class="btn btn-primary">
                                </div>
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header">
                                <h5>Previous Tasks</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Task</th>
                                                <th>Employee</th>
                                                <th>Status</th>
                                                <th>Status Date</th>
                                                <th>Assigned At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($previous_assignments as $assignment)
                                            <tr>
                                                <td>{{$assignment->task->task_name}}</td>
                                                <td>{{$assignment->employee->name}}</td>
                                                <td>
                                                    @if($assignment->status == 'Completed')
                                                        <div class="badge badge-success">{{$assignment->status}}</div>
                                                    @elseif($assignment->status == 'In-Progress')
                                                        <div class="badge badge-primary">{{$assignment->status}}</div>
                                                    @else
                                                        <div class="badge badge-warning">{{$assignment->status}}</div>
                                                    @endif
                                                </td>
                                                <td>{{$assignment->status_date}}</td>
                                                <td>{{$assignment->created_at->format('Y-m-d H:i')}}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
