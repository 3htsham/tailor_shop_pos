@extends('backend.layout.main')
@section('content')
<section>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4>{{trans('file.Task Management')}} — Sale: <strong>{{$sale->reference_no}}</strong></h4>
                <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
                    <i class="dripicons-arrow-left"></i> Back
                </a>
            </div>
            <div class="card-body">

                @if(session()->has('message'))
                    <div class="alert alert-success alert-dismissible text-center">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        {{ session()->get('message') }}
                    </div>
                @endif
                @if(session()->has('not_permitted'))
                    <div class="alert alert-danger alert-dismissible text-center">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        {{ session()->get('not_permitted') }}
                    </div>
                @endif

                {{-- ─── Step 1 & 2: Product + Unit Selection ────────────────── --}}
                <div class="card mb-3" style="border: 1px solid #dee2e6; border-radius: 6px;">
                    <div class="card-header bg-light">
                        <strong><i class="dripicons-store"></i> Step 1 — Select Product &amp; Unit Item</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            {{-- Product Dropdown --}}
                            <div class="col-md-5">
                                <div class="form-group mb-0">
                                    <label><strong>Product</strong></label>
                                    <select id="product_sale_select" class="form-control selectpicker" data-live-search="true" title="Select a product...">
                                        @foreach($productSales as $ps)
                                            <option
                                                value="{{ $ps->id }}"
                                                data-qty="{{ (int) $ps->qty }}"
                                                data-name="{{ $ps->product ? $ps->product->name : 'Unknown' }}"
                                            >
                                                {{ $ps->product ? $ps->product->name : 'Unknown' }}
                                                &nbsp;(Qty: {{ (int) $ps->qty }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Unit Item Dropdown --}}
                            <div class="col-md-4" id="unit_item_wrapper" style="display:none;">
                                <div class="form-group mb-0">
                                    <label><strong>Unit Item</strong></label>
                                    <select id="unit_item_select" class="form-control" title="Select unit...">
                                    </select>
                                </div>
                            </div>

                            {{-- Loading indicator --}}
                            <div class="col-md-3 d-flex align-items-end" id="unit_loading" style="display:none!important;">
                                <span class="text-muted"><i class="fa fa-spinner fa-spin"></i> Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ─── Main Content (hidden until unit is selected) ────────── --}}
                <div id="task-panel" style="display:none;">
                    <div class="row">

                        {{-- ─── Left: Assignment Form ────────────────────────── --}}
                        <div class="col-md-5">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="dripicons-plus"></i> Assign Task</h5>
                                </div>
                                <div class="card-body">
                                    {!! Form::open(['route' => 'sales.task.store', 'method' => 'post', 'id' => 'task-assignment-form']) !!}
                                    <input type="hidden" name="sale_id"         value="{{ $sale->id }}">
                                    <input type="hidden" name="product_sale_id" id="form_product_sale_id" value="">
                                    <input type="hidden" name="unit_item_no"    id="form_unit_item_no"    value="">
                                    {{-- Hidden fallback task_id when locked --}}
                                    <input type="hidden" name="task_id_locked"  id="task_id_locked"       value="">

                                    <div class="form-group">
                                        <label><strong>Task *</strong></label>
                                        <div id="task_select_wrapper">
                                            <select name="task_id" id="task_id_select" class="form-control selectpicker" required data-live-search="true">
                                                <option value="">Select Task</option>
                                                @foreach($tasks as $task)
                                                    <option value="{{ $task->id }}" data-price="{{ $task->default_price }}">
                                                        {{ $task->task_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        {{-- Locked state: shows task name as text, hidden input carries value --}}
                                        <div id="task_locked_display" style="display:none;">
                                            {{-- disabled by default so it only submits when locked --}}
                                            <input type="hidden" name="task_id" id="task_id_hidden" value="" disabled>
                                            <p class="form-control-plaintext font-weight-bold" id="task_locked_name"></p>
                                            <small class="text-muted">Task locked — complete current task before switching.</small>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label><strong>Price *</strong></label>
                                        <input type="number" name="price" id="price_input" class="form-control" step="any" min="0" required placeholder="0.00">
                                    </div>

                                    <div class="form-group">
                                        <label><strong>Employee *</strong></label>
                                        <select name="employee_id" id="employee_select" class="form-control selectpicker" required data-live-search="true">
                                            <option value="">Select Employee</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label><strong>Status *</strong></label>
                                        <select name="status" id="status_select" class="form-control" required>
                                            <option value="Pending">Pending</option>
                                            <option value="In-Progress">In-Progress</option>
                                            <option value="Completed">Completed</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="dripicons-checkmark"></i> Submit
                                        </button>
                                    </div>
                                    {!! Form::close() !!}
                                </div>
                            </div>
                        </div>

                        {{-- ─── Right: Task History ──────────────────────────── --}}
                        <div class="col-md-7">
                            <div class="card">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <h5 class="mb-0"><i class="dripicons-clock"></i> Task History</h5>
                                    <small class="text-muted" id="history_label"></small>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped mb-0" id="history-table">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Task</th>
                                                    <th>Price</th>
                                                    <th>Employee</th>
                                                    <th>Status</th>
                                                    <th>Assigned At</th>
                                                </tr>
                                            </thead>
                                            <tbody id="history-tbody">
                                                <tr id="history-empty-row">
                                                    <td colspan="5" class="text-center text-muted py-3">
                                                        No tasks assigned yet for this unit item.
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /row --}}
                </div>{{-- /#task-panel --}}

            </div>{{-- /card-body --}}
        </div>{{-- /card --}}
    </div>{{-- /container-fluid --}}
</section>
@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function () {

    /* ── Data ──────────────────────────────────────────────────────── */
    var tasks = @json($tasks);           // array of { id, task_name, default_price }
    var saleId = {{ $sale->id }};
    var currentLastAssignment = null;    // tracks last assignment for selected unit

    /* ── Helpers ───────────────────────────────────────────────────── */

    /** Return badge HTML for a status string */
    function statusBadge(status) {
        var cls = 'warning';
        if (status === 'Completed')  cls = 'success';
        if (status === 'In-Progress') cls = 'primary';
        return '<span class="badge badge-' + cls + '">' + status + '</span>';
    }

    /** Populate the task history tbody from an array of assignment objects */
    function renderHistory(assignments) {
        var $tbody = $('#history-tbody');
        $tbody.empty();
        if (!assignments || assignments.length === 0) {
            $tbody.append(
                '<tr id="history-empty-row"><td colspan="5" class="text-center text-muted py-3">No tasks assigned yet for this unit item.</td></tr>'
            );
            return;
        }
        $.each(assignments, function (i, a) {
            $tbody.append(
                '<tr>' +
                '<td>' + a.task_name + '</td>' +
                '<td>' + a.price + '</td>' +
                '<td>' + a.employee + '</td>' +
                '<td>' + statusBadge(a.status) + '</td>' +
                '<td>' + a.created_at + '</td>' +
                '</tr>'
            );
        });
    }

    /** Update the assignment form based on last_assignment data */
    function applyLastAssignment(last) {
        currentLastAssignment = last;

        var $taskSelect   = $('#task_id_select');
        var $taskWrapper  = $('#task_select_wrapper');
        var $taskLocked   = $('#task_locked_display');
        var $priceInput   = $('#price_input');
        var $statusSelect = $('#status_select');
        var $empSelect    = $('#employee_select');

        // Reset all options
        $('#status_select option').prop('disabled', false);

        if (!last) {
            // Fresh unit — full form unlocked
            $taskWrapper.show();
            $taskLocked.hide();
            $('#task_id_hidden').prop('disabled', true).val('');   // hidden input OFF
            $taskSelect.prop('disabled', false).val('');           // select ON
            $priceInput.val('').prop('readonly', false);
            $statusSelect.val('Pending');
            $empSelect.val('');
            $('.selectpicker').selectpicker('refresh');
            return;
        }

        // Pre-fill employee
        $empSelect.val(last.employee_id);

        if (last.status === 'Completed') {
            // Completed — allow selecting a new task; reset form
            $taskWrapper.show();
            $taskLocked.hide();
            $('#task_id_hidden').prop('disabled', true).val('');   // hidden input OFF
            $taskSelect.prop('disabled', false).val('');           // select ON
            $priceInput.val('').prop('readonly', false);
            $statusSelect.val('Pending');
            $('#status_select option[value="Pending"]').prop('disabled', false);
            $('.selectpicker').selectpicker('refresh');
            return;
        }

        // Task is In-Progress or Pending — lock the task dropdown
        $taskWrapper.hide();
        $taskLocked.show();
        $taskSelect.prop('disabled', true);                        // select OFF
        $('#task_id_hidden').prop('disabled', false);              // hidden input ON

        // Find task name
        var taskName = '';
        $.each(tasks, function (i, t) {
            if (t.id == last.task_id) { taskName = t.task_name; return false; }
        });
        $('#task_locked_name').text(taskName);
        $('#task_id_hidden').val(last.task_id);

        // Price: lock if In-Progress
        $priceInput.val(last.price);
        $priceInput.prop('readonly', last.status === 'In-Progress');

        // Status: disable reverting to Pending if In-Progress
        $statusSelect.val(last.status);
        if (last.status === 'In-Progress') {
            $('#status_select option[value="Pending"]').prop('disabled', true);
        }

        $('.selectpicker').selectpicker('refresh');
    }

    /** Auto-fill price when task dropdown changes (new task selection) */
    $('#task_id_select').on('change', function () {
        var selected = $(this).find('option:selected');
        var defaultPrice = selected.data('price');
        if (defaultPrice !== undefined && defaultPrice !== '') {
            $('#price_input').val(defaultPrice);
        } else {
            $('#price_input').val('');
        }
    });

    /* ── Product Dropdown ──────────────────────────────────────────── */
    $('#product_sale_select').on('change', function () {
        var selectedVal = $(this).val();
        var $selected   = $(this).find('option[value="' + selectedVal + '"]');
        var qty         = parseInt($selected.attr('data-qty')) || 0;

        // Hide the task panel until a unit is picked
        $('#task-panel').hide();

        if (!selectedVal || qty < 1) {
            $('#unit_item_wrapper').hide();
            return;
        }

        // Build unit items dropdown
        var $unitSelect = $('#unit_item_select');

        // Destroy selectpicker first so we can cleanly repopulate
        try { $unitSelect.selectpicker('destroy'); } catch(e) {}

        $unitSelect.empty().append('<option value="">-- Select unit item --</option>');
        for (var i = 1; i <= qty; i++) {
            $unitSelect.append('<option value="' + i + '">Unit Item ' + i + '</option>');
        }
        $unitSelect.val('');

        // Re-init selectpicker so UI reflects the new options
        try { $unitSelect.selectpicker(); } catch(e) {}

        $('#unit_item_wrapper').show();
    });

    /* ── Unit Item Dropdown ────────────────────────────────────────── */
    /* Use changed.bs.select (fires after selectpicker updates) + fallback to change */
    var unitSelectChanging = false;
    $(document).on('changed.bs.select change', '#unit_item_select', function (e) {
        // Prevent double-fire when both events fire
        if (unitSelectChanging) return;
        unitSelectChanging = true;
        setTimeout(function () { unitSelectChanging = false; }, 50);

        var unitNo        = $('#unit_item_select').val();
        var productSaleId = $('#product_sale_select').val();
        var productName   = $('#product_sale_select').find('option[value="' + productSaleId + '"]').attr('data-name') || '';

        if (!unitNo || !productSaleId) {
            $('#task-panel').hide();
            return;
        }

        // Update hidden inputs in the form
        $('#form_product_sale_id').val(productSaleId);
        $('#form_unit_item_no').val(unitNo);

        // Update history label
        $('#history_label').text(productName + ' — Unit ' + unitNo);

        // Fetch assignments for this unit via AJAX
        $('#unit_loading').show();
        $.ajax({
            url: '{{ route("sales.task.unit-assignments", $sale->id) }}',
            type: 'GET',
            data: {
                product_sale_id: productSaleId,
                unit_item_no:    unitNo,
            },
            success: function (response) {
                renderHistory(response.assignments);
                applyLastAssignment(response.last_assignment);
                $('#task-panel').show();
            },
            error: function () {
                alert('Error loading task data. Please try again.');
            },
            complete: function () {
                $('#unit_loading').hide();
            }
        });
    });

    /* ── Form Submit Validation ────────────────────────────────────── */
    $('#task-assignment-form').on('submit', function (e) {
        var productSaleId = $('#form_product_sale_id').val();
        var unitItemNo    = $('#form_unit_item_no').val();

        if (!productSaleId || !unitItemNo) {
            e.preventDefault();
            alert('Please select a product and unit item first.');
        }
    });

    /* ── Init selectpicker ─────────────────────────────────────────── */
    $('.selectpicker').selectpicker('refresh');
});
</script>
@endpush
