@extends('backend.layout.main')
@section('content')
    <section>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            @if ($employee->image)
                                <img src="{{ url('images/employee', $employee->image) }}" class="rounded-circle" width="150"
                                    height="150">
                            @else
                                <img src="{{ url('images/product/zummXD2dvAtI.png') }}" class="rounded-circle" width="150"
                                    height="150">
                            @endif
                            <h3 class="mt-3">{{ $employee->name }}</h3>
                            <p>{{ $employee->email }}<br>{{ $employee->phone_number }}</p>
                            <hr>
                            <h4>Balance</h4>
                            <h2
                                class="@if ($employee->balance > 0) text-success @elseif($employee->balance < 0) text-danger @endif">
                                {{ number_format($employee->balance, 2) }}
                            </h2>
                            <p class="text-muted">
                                @if ($employee->balance > 0)
                                    (Shop owes Employee)
                                @elseif($employee->balance < 0)
                                    (Employee owes Shop)
                                @else
                                    (Settled)
                                @endif
                            </p>
                            <button class="btn btn-primary btn-block" data-toggle="modal"
                                data-target="#adjustBalanceModal">Adjust Balance</button>
                            <button class="btn btn-success btn-block mt-2" data-toggle="modal"
                                data-target="#payEmployeeModal">Pay Employee</button>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h5>Employee Details</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <tr>
                                    <td>Department</td>
                                    <td>{{ $employee->department_id }}</td> {{-- Ideally show department name --}}
                                </tr>
                                <tr>
                                    <td>Payroll Employee</td>
                                    <td>
                                        @if ($employee->is_payroll)
                                            <span class="badge badge-success">Yes</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                </tr>
                                @if ($employee->is_payroll)
                                    <tr>
                                        <td>Monthly Salary</td>
                                        <td>{{ number_format($employee->monthly_salary, 2) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td>Address</td>
                                    <td>{{ $employee->address }}</td>
                                </tr>
                                <tr>
                                    <td>City</td>
                                    <td>{{ $employee->city }}</td>
                                </tr>
                                <tr>
                                    <td>Country</td>
                                    <td>{{ $employee->country }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#tasks" role="tab" data-toggle="tab">Task
                                        History</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#payroll" role="tab" data-toggle="tab">Payroll History</a>
                                </li>
                            </ul>

                            <div class="tab-content mt-3">
                                <!-- Task History Tab -->
                                <div role="tabpanel" class="tab-pane fade show active" id="tasks">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Sale Ref</th>
                                                    <th>Task</th>
                                                    @if (!$employee->is_payroll)
                                                        <th>Price</th>
                                                    @endif
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($recent_tasks as $task)
                                                    <tr>
                                                        <td>{{ $task->created_at->format('Y-m-d') }}</td>
                                                        <td>{{ $task->sale->reference_no }}</td>
                                                        <td>{{ $task->task->task_name }}</td>
                                                        @if (!$employee->is_payroll)
                                                            <td>{{ number_format($task->price, 2) }}</td>
                                                        @endif
                                                        <td>
                                                            @if ($task->status == 'Completed')
                                                                <span
                                                                    class="badge badge-success">{{ $task->status }}</span>
                                                            @elseif($task->status == 'In-Progress')
                                                                <span
                                                                    class="badge badge-primary">{{ $task->status }}</span>
                                                            @else
                                                                <span
                                                                    class="badge badge-warning">{{ $task->status }}</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center">No tasks found</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                        {{ $recent_tasks->links() }}
                                    </div>
                                </div>

                                <!-- Payroll History Tab -->
                                <div role="tabpanel" class="tab-pane fade" id="payroll">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Reference</th>
                                                    <th>Amount</th>
                                                    <th>Method</th>
                                                    <th>Note</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($payroll_history as $payroll)
                                                    <tr>
                                                        <td>{{ $payroll->created_at->format('Y-m-d') }}</td>
                                                        <td>{{ $payroll->reference_no }}</td>
                                                        <td>{{ number_format($payroll->amount, 2) }}</td>
                                                        <td>
                                                            @if ($payroll->paying_method == 'cash')
                                                                Cash
                                                            @elseif($payroll->paying_method == 'account')
                                                                Account
                                                            @elseif($payroll->paying_method == 0)
                                                                Cash
                                                            @elseif($payroll->paying_method == 1)
                                                                Cheque
                                                            @else
                                                                Credit Card
                                                            @endif
                                                        </td>
                                                        <td>{{ $payroll->note }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center">No payroll records found</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                        {{ $payroll_history->links() }}
                                        <div class="text-right mt-3">
                                            <a href="{{ route('payroll.index') }}" class="btn btn-link">View All Payroll
                                                Records</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Adjust Balance Modal -->
    <div id="adjustBalanceModal" tabindex="-1" role="dialog" aria-labelledby="adjustBalanceModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="adjustBalanceModalLabel" class="modal-title">Adjust Employee Balance</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <p>Current Balance: <strong>{{ number_format($employee->balance, 2) }}</strong></p>
                    {!! Form::open(['route' => 'employees.balance.adjust', 'method' => 'post']) !!}
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">

                    <div class="form-group">
                        <label>Target Balance *</label>
                        <input type="number" name="target_balance" class="form-control" step="any" required
                            value="0">
                        <small>Enter the desired final balance (e.g., 0 to clear balance).</small>
                    </div>

                    <div class="form-group">
                        <label>Note</label>
                        <textarea name="note" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Adjust Balance</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pay Employee Modal -->
    <div id="payEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="payEmployeeModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="payEmployeeModalLabel" class="modal-title">Pay Employee</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    {!! Form::open(['route' => 'employees.pay', 'method' => 'post']) !!}
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
    
                    <div class="form-group">
                        <label>Amount *</label>
                        <input type="number" name="amount" class="form-control" step="any" min="0.01"
                            required>
                    </div>
    
                    <div class="form-group">
                        <label>Payment Method *</label>
                        <select name="paying_method" class="form-control" required>
                            <option value="cash">Cash</option>
                            <option value="account">Account</option>
                        </select>
                    </div>
    
                    <div class="form-group">
                        <label>Note</label>
                        <textarea name="note" class="form-control" rows="3"></textarea>
                    </div>
    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Submit Payment</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection

