@extends('backend.layout.main') @section('content')
@if(session()->has('create_message'))
    <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('create_message') !!}</div>
@endif
@if(session()->has('edit_message'))
    <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('edit_message') }}</div>
@endif
@if(session()->has('import_message'))
    <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('import_message') !!}</div>
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif

<section>
    <div class="container-fluid">
        @if(in_array("customers-add", $all_permission))
            <a href="{{route('customer.create')}}" class="btn btn-info"><i class="dripicons-plus"></i> {{trans('file.Add Customer')}}</a>&nbsp;
            <a href="#" data-toggle="modal" data-target="#importCustomer" class="btn btn-primary"><i class="dripicons-copy"></i> {{trans('file.Import Customer')}}</a>
        @endif
    </div>
    <div class="table-responsive">
        <table id="customer-table" class="table" style="width: 100%">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{trans('file.Customer Group')}}</th>
                    <th>{{trans('file.Customer Details')}}</th>
                    <th>{{trans('file.Discount Plan')}}</th>
                    <th>{{trans('file.Reward Points')}}</th>
                    <th>{{trans('file.Deposited Balance')}}</th>
                    <th>{{trans('file.Total Due')}}</th>
                    @foreach($custom_fields as $fieldName)
                    <th>{{$fieldName}}</th>
                    @endforeach
                    <th class="not-exported">{{trans('file.action')}}</th>
                </tr>
            </thead>
        </table>
    </div>
</section>

<div id="importCustomer" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
      <div class="modal-content">
        {!! Form::open(['route' => 'customer.import', 'method' => 'post', 'files' => true]) !!}
        <div class="modal-header">
          <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Import Customer')}}</h5>
          <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
        </div>
        <div class="modal-body">
          <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
           <p>{{trans('file.The correct column order is')}} (customer_group*, name*, company_name, email, phone_number*, address*, city*, state, postal_code, country) {{trans('file.and you must follow this')}}.</p>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{trans('file.Upload CSV File')}} *</label>
                        {{Form::file('file', array('class' => 'form-control','required'))}}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{trans('file.Sample File')}}</label>
                        <a href="sample_file/sample_customer.csv" class="btn btn-info btn-block btn-md"><i class="dripicons-download"></i>  {{trans('file.Download')}}</a>
                    </div>
                </div>
            </div>
            <input type="submit" value="{{trans('file.submit')}}" class="btn btn-primary" id="submit-button">
        </div>
        {!! Form::close() !!}
      </div>
    </div>
</div>

<div id="clearDueModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
      <div class="modal-content">
        {!! Form::open(['route' => 'customer.clearDue', 'method' => 'post']) !!}
        <div class="modal-header">
          <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Clear Due')}}</h5>
          <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
        </div>
        <div class="modal-body">
          <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
            <div class="form-group">
                <input type="hidden" name="customer_id">
                <label>{{trans('file.Amount')}} *</label>
                <input type="number" name="amount" step="any" class="form-control" required>
            </div>
            <div class="form-group">
                <label>{{trans('file.Note')}}</label>
                <textarea name="note" rows="4" class="form-control"></textarea>
            </div>
            <input type="submit" value="{{trans('file.submit')}}" class="btn btn-primary" id="submit-button">
        </div>
        {!! Form::close() !!}
      </div>
    </div>
</div>

<div id="depositModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
      <div class="modal-content">
        {!! Form::open(['route' => 'customer.addDeposit', 'method' => 'post']) !!}
        <div class="modal-header">
          <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Add Deposit')}}</h5>
          <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
        </div>
        <div class="modal-body">
          <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
            <div class="form-group">
                <input type="hidden" name="customer_id">
                <label>{{trans('file.Amount')}} *</label>
                <input type="number" name="amount" step="any" class="form-control" required>
            </div>
            <div class="form-group">
                <label>{{trans('file.Note')}}</label>
                <textarea name="note" rows="4" class="form-control"></textarea>
            </div>
            <input type="submit" value="{{trans('file.submit')}}" class="btn btn-primary" id="submit-button">
        </div>
        {!! Form::close() !!}
      </div>
    </div>
</div>

<div id="view-deposit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">{{trans('file.All Deposit')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                <table class="table table-hover deposit-list">
                    <thead>
                        <tr>
                            <th>{{trans('file.date')}}</th>
                            <th>{{trans('file.Amount')}}</th>
                            <th>{{trans('file.Note')}}</th>
                            <th>{{trans('file.Created By')}}</th>
                            <th>{{trans('file.action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="edit-deposit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Update Deposit')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                {!! Form::open(['route' => 'customer.updateDeposit', 'method' => 'post']) !!}
                    <div class="form-group">
                        <label>{{trans('file.Amount')}} *</label>
                        <input type="number" name="amount" step="any" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>{{trans('file.Note')}}</label>
                        <textarea name="note" rows="4" class="form-control"></textarea>
                    </div>
                    <input type="hidden" name="deposit_id">
                    <button type="submit" class="btn btn-primary">{{trans('file.update')}}</button>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>


{{-- ============================
     View Measurements Modal
     ============================ --}}
<div id="viewMeasurementsModal" tabindex="-1" role="dialog" aria-labelledby="viewMeasurementsLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); color: #fff;">
                <div>
                    <h5 id="viewMeasurementsLabel" class="modal-title mb-0" style="font-weight:700; letter-spacing:.5px;">
                        <i class="dripicons-scale mr-2"></i> Customer Measurements
                    </h5>
                    <small id="vm-customer-subtitle" style="opacity:.8;"></small>
                </div>
                <div class="ml-auto d-flex align-items-center">
                    <button type="button" id="vm-print-btn" class="btn btn-sm mr-2"
                        style="background:#e94560; color:#fff; border:none; border-radius:6px; font-weight:600;">
                        <i class="fa fa-print mr-1"></i> Print
                    </button>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close" style="color:#fff; opacity:.9; margin-left:8px;">
                        <span aria-hidden="true"><i class="dripicons-cross"></i></span>
                    </button>
                </div>
            </div>
            <div class="modal-body" id="vm-modal-body" style="background:#f8f9fc; padding: 24px;">
                {{-- Filled dynamically by JS --}}
                <div class="text-center text-muted py-4" id="vm-loading">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Loading measurements...</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Printable version (hidden) --}}
<div id="vm-printable" style="display:none;"></div>

@endsection


@push('scripts')
<script type="text/javascript">
    $("ul#people").siblings('a').attr('aria-expanded','true');
    $("ul#people").addClass("show");
    $("ul#people #customer-list-menu").addClass("active");

    function confirmDelete() {
      if (confirm("Are you sure want to delete?")) {
          return true;
      }
      return false;
    }

    /* ============================================================
       VIEW MEASUREMENTS POPUP
       ============================================================ */
    $(document).on('click', '.view-measurements', function () {
        var customerId = $(this).data('id');
        var customerName = $(this).data('name');
        $('#vm-customer-subtitle').text(customerName);
        $('#vm-modal-body').html('<div class="text-center text-muted py-4"><i class="fa fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading measurements...</p></div>');

        $.get('/customer/' + customerId + '/measurements/all', function (data) {
            renderMeasurementsModal(data);
        }).fail(function () {
            $('#vm-modal-body').html('<div class="alert alert-danger">Failed to load measurements. Please try again.</div>');
        });
    });

    function renderMeasurementsModal(data) {
        var c = data.customer;
        var measurements = data.measurements;

        // -- Customer info card --
        var html = '';
        html += '<div class="card mb-4 border-0 shadow-sm" style="border-radius:12px; overflow:hidden;">';
        html += '  <div class="card-body" style="background:linear-gradient(135deg,#1a1a2e,#0f3460); color:#fff; padding:20px 24px;">';
        html += '    <div class="row align-items-center">';
        html += '      <div class="col-auto">';
        html += '        <div style="width:56px;height:56px;background:rgba(233,69,96,.9);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:700;color:#fff;">';
        html +=            c.name.charAt(0).toUpperCase();
        html += '        </div>';
        html += '      </div>';
        html += '      <div class="col">';
        html += '        <h5 class="mb-0 font-weight-bold" style="color:#fff;">' + escapeHtml(c.name) + '</h5>';
        if (c.phone_number) html += '        <small style="opacity:.85;"><i class="fa fa-phone mr-1"></i>' + escapeHtml(c.phone_number) + '</small>';
        if (c.email)        html += '&nbsp;&nbsp;<small style="opacity:.85;"><i class="fa fa-envelope mr-1"></i>' + escapeHtml(c.email) + '</small>';
        if (c.address || c.city) {
            html += '        <br><small style="opacity:.8;"><i class="fa fa-map-marker mr-1"></i>' + escapeHtml((c.address || '') + (c.city ? ', ' + c.city : '')) + '</small>';
        }
        html += '      </div>';
        html += '    </div>';
        html += '  </div>';
        html += '</div>';

        // -- Measurements accordion --
        if (measurements.length === 0) {
            html += '<div class="text-center py-4" style="color:#aaa;">';
            html += '  <i class="dripicons-scale" style="font-size:48px;"></i>';
            html += '  <p class="mt-3">No measurements recorded yet for this customer.</p>';
            html += '</div>';
        } else {
            html += '<style>';
            html += '.vm-accordion-body{overflow:hidden;transition:max-height .35s ease, opacity .35s ease; max-height:0; opacity:0;}';
            html += '.vm-accordion-body.vm-open{max-height:2000px; opacity:1;}';
            html += '.vm-accordion-header{cursor:pointer; user-select:none;}';
            html += '.vm-chevron{transition:transform .3s ease; display:inline-block;}';
            html += '.vm-open-header .vm-chevron{transform:rotate(180deg);}';
            html += '</style>';

            measurements.forEach(function (m, idx) {
                var accentColors = ['#0f3460','#1a1a2e','#16213e','#533483','#e94560'];
                var accent = accentColors[idx % accentColors.length];
                var collapseId = 'vm-collapse-' + idx;
                // First item starts open
                var isOpen = (idx === 0);
                var bodyClass  = 'vm-accordion-body' + (isOpen ? ' vm-open' : '');
                var headerClass = 'vm-accordion-header d-flex align-items-center py-2 px-3' + (isOpen ? ' vm-open-header' : '');

                html += '<div class="card mb-3 border-0 shadow-sm" style="border-radius:10px; overflow:hidden;">';

                // -- Clickable header --
                html += '  <div class="' + headerClass + '" data-target="' + collapseId + '" style="background:' + accent + '; color:#fff;">';
                html += '    <span class="font-weight-bold" style="font-size:15px;"><i class="dripicons-scale mr-2"></i>' + escapeHtml(m.sale_type_name) + '</span>';
                if (m.measurement_unit) {
                    html += '    <span class="ml-2 badge" style="background:rgba(255,255,255,.2); color:#fff; font-size:11px;">Unit: ' + escapeHtml(m.measurement_unit) + '</span>';
                }
                html += '    <small class="ml-auto mr-2" style="opacity:.75;">Updated: ' + escapeHtml(m.updated_at) + '</small>';
                html += '    <span class="vm-chevron" style="font-size:16px; line-height:1;">&#8963;</span>';
                html += '  </div>';

                // -- Collapsible body --
                html += '  <div id="' + collapseId + '" class="' + bodyClass + '">';
                html += '    <div style="padding: 16px 20px;">';

                if (m.fields.length > 0) {
                    html += '<div class="row">';
                    m.fields.forEach(function (f) {
                        html += '<div class="col-sm-6 col-md-4 mb-2">';
                        html += '  <div style="background:#f0f4ff; border-radius:8px; padding:10px 14px;">';
                        html += '    <small style="color:#888; font-size:11px; text-transform:uppercase; letter-spacing:.6px;">' + escapeHtml(f.label) + '</small>';
                        html += '    <div style="font-weight:700; font-size:16px; color:#1a1a2e;">' + (f.value !== '' ? escapeHtml(String(f.value)) : '<span style="color:#ccc;">—</span>') + '</div>';
                        html += '  </div>';
                        html += '</div>';
                    });
                    html += '</div>';
                } else {
                    html += '<p class="text-muted">No measurement fields defined for this sale type.</p>';
                }

                if (m.notes) {
                    html += '<div class="mt-2" style="background:#fff8e1; border-left:4px solid #ffc107; border-radius:4px; padding:8px 12px;">';
                    html += '  <small style="color:#888;"><i class="fa fa-sticky-note-o mr-1"></i>Notes:</small>';
                    html += '  <div style="color:#555;">' + escapeHtml(m.notes) + '</div>';
                    html += '</div>';
                }

                html += '    </div>';
                html += '  </div>';
                html += '</div>';
            });
        }

        $('#vm-modal-body').html(html);

        // -- Accordion toggle --
        $('#vm-modal-body').off('click', '.vm-accordion-header').on('click', '.vm-accordion-header', function () {
            var targetId = $(this).data('target');
            var $body = $('#' + targetId);
            var isOpen = $body.hasClass('vm-open');
            $body.toggleClass('vm-open', !isOpen);
            $(this).toggleClass('vm-open-header', !isOpen);
        });

        // build printable version
        $('#vm-printable').html(buildPrintable(data));
    }

    function buildPrintable(data) {
        var c = data.customer;
        var measurements = data.measurements;
        var html = '';
        html += '<style>';
        html += 'body{font-family:Arial,sans-serif;color:#222;padding:24px;}';
        html += 'h1{font-size:22px;border-bottom:2px solid #0f3460;padding-bottom:8px;}';
        html += '.info{margin-bottom:16px;font-size:14px;}';
        html += '.section{margin-bottom:24px;}';
        html += '.section h2{background:#0f3460;color:#fff;padding:8px 12px;border-radius:6px 6px 0 0;font-size:16px;margin:0;}';
        html += '.fields{display:flex;flex-wrap:wrap;gap:10px;padding:12px;border:1px solid #ddd;border-top:none;border-radius:0 0 6px 6px;}';
        html += '.field{background:#f0f4ff;border-radius:6px;padding:8px 12px;min-width:140px;}';
        html += '.field label{display:block;font-size:11px;color:#666;text-transform:uppercase;}';
        html += '.field span{font-size:16px;font-weight:700;}';
        html += '.notes{background:#fff8e1;border-left:4px solid #ffc107;padding:8px 12px;margin-top:8px;font-size:13px;}';
        html += '</style>';
        html += '<h1>Customer Measurements</h1>';
        html += '<div class="info">';
        html += '<strong>' + escapeHtml(c.name) + '</strong>';
        if (c.phone_number) html += ' &nbsp;|&nbsp; ' + escapeHtml(c.phone_number);
        if (c.email)        html += ' &nbsp;|&nbsp; ' + escapeHtml(c.email);
        if (c.address || c.city) html += ' &nbsp;|&nbsp; ' + escapeHtml((c.address||'') + (c.city ? ', '+c.city : ''));
        html += '</div>';

        if (measurements.length === 0) {
            html += '<p style="color:#999;">No measurements recorded.</p>';
        } else {
            measurements.forEach(function(m) {
                html += '<div class="section">';
                html += '<h2>' + escapeHtml(m.sale_type_name);
                if (m.measurement_unit) html += ' <small style="font-size:12px;font-weight:normal;">(Unit: ' + escapeHtml(m.measurement_unit) + ')</small>';
                html += ' <small style="font-size:12px;font-weight:normal;float:right;">Updated: ' + escapeHtml(m.updated_at) + '</small></h2>';
                html += '<div class="fields">';
                m.fields.forEach(function(f) {
                    html += '<div class="field"><label>' + escapeHtml(f.label) + '</label><span>' + (f.value !== '' ? escapeHtml(String(f.value)) : '—') + '</span></div>';
                });
                html += '</div>';
                if (m.notes) {
                    html += '<div class="notes"><strong>Notes:</strong> ' + escapeHtml(m.notes) + '</div>';
                }
                html += '</div>';
            });
        }
        return html;
    }

    $('#vm-print-btn').on('click', function () {
        var printContents = $('#vm-printable').html();
        var win = window.open('', '_blank', 'width=900,height=700');
        win.document.write('<html><head><title>Customer Measurements</title></head><body>');
        win.document.write(printContents);
        win.document.write('</body></html>');
        win.document.close();
        win.focus();
        win.print();
    });

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    var customer_id = [];
    var user_verified = <?php echo json_encode(env('USER_VERIFIED')) ?>;
    var all_permission = <?php echo json_encode($all_permission) ?>;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

  $(document).on("click", ".deposit", function() {
        var id = $(this).data('id').toString();
        $("#depositModal input[name='customer_id']").val(id);
  });

  $(document).on("click", ".clear-due", function() {
        var id = $(this).data('id').toString();
        console.log(id);
        $("#clearDueModal input[name='customer_id']").val(id);
  });

  $(document).on("click", ".getDeposit", function() {
        var id = $(this).data('id').toString();
        $.get('customer/getDeposit/' + id, function(data) {
            $(".deposit-list tbody").remove();
            var newBody = $("<tbody>");
            $.each(data[0], function(index){
                var newRow = $("<tr>");
                var cols = '';

                cols += '<td>' + data[1][index] + '</td>';
                cols += '<td>' + data[2][index] + '</td>';
                if(data[3][index])
                    cols += '<td>' + data[3][index] + '</td>';
                else
                    cols += '<td>N/A</td>';
                cols += '<td>' + data[4][index] + '<br>' + data[5][index] + '</td>';
                cols += '<td><div class="btn-group"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{trans("file.action")}}<span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu"><li><button type="button" class="btn btn-link edit-btn" data-id="' + data[0][index] +'" data-toggle="modal" data-target="#edit-deposit"><i class="dripicons-document-edit"></i> {{trans("file.edit")}}</button></li><li class="divider"></li>{{ Form::open(['route' => 'customer.deleteDeposit', 'method' => 'post'] ) }}<li><input type="hidden" name="id" value="' + data[0][index] + '" /> <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="dripicons-trash"></i> {{trans("file.delete")}}</button></li>{{ Form::close() }}</ul></div></td>'
                newRow.append(cols);
                newBody.append(newRow);
                $("table.deposit-list").append(newBody);
            });
            $("#view-deposit").modal('show');
        });
  });

  $(document).on("click", "table.deposit-list .edit-btn", function(event) {
        var id = $(this).data('id');
        var rowindex = $(this).closest('tr').index();
        var amount = $('table.deposit-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)').text();
        var note = $('table.deposit-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(3)').text();
        if(note == 'N/A')
            note = '';

        $('#edit-deposit input[name="deposit_id"]').val(id);
        $('#edit-deposit input[name="amount"]').val(amount);
        $('#edit-deposit textarea[name="note"]').val(note);
        $('#view-deposit').modal('hide');
    });

    var columns = [{"data": "key"}, {"data": "customer_group"}, {"data": "customer_details"}, {"data": "discount_plan"}, {"data": "reward_point"}, {"data": "deposited_balance"}, {"data": "total_due"}];
    var field_name = <?php echo json_encode($field_name) ?>;
    for(i = 0; i < field_name.length; i++) {
        columns.push({"data": field_name[i]});
    }
    columns.push({"data": "options"});

    $('#customer-table').DataTable( {
        "processing": true,
        "serverSide": true,
        "ajax":{
            url:"customers/customer-data",
            data:{
                all_permission: all_permission,
            },
            dataType: "json",
            type:"post"
        },
        // "createdRow": function( row, data, dataIndex ) {
        //     console.log(data);
        // },
        "columns": columns,
        'language': {

            'lengthMenu': '_MENU_ {{trans("file.records per page")}}',
             "info":      '<small>{{trans("file.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
            "search":  '{{trans("file.Search")}}',
            'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
            }
        },
        order:[['1', 'desc']],
        'columnDefs': [
            {
                "orderable": false,
                'targets': [0, 2, 3, 4, 5, 6, 7 ]
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
        rowId: 'ObjectID',
        buttons: [
            {
                extend: 'pdf',
                text: '<i title="export to pdf" class="fa fa-file-pdf-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                }
            },
            {
                extend: 'excel',
                text: '<i title="export to excel" class="dripicons-document-new"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                }
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                }
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                }
            },
            {
                text: '<i title="delete" class="dripicons-cross"></i>',
                className: 'buttons-delete',
                action: function ( e, dt, node, config ) {
                    if(user_verified == '1') {
                        customer_id.length = 0;
                        $(':checkbox:checked').each(function(i){
                            if(i){
                                customer_id[i-1] = $(this).closest('tr').data('id');
                            }
                        });
                        if(customer_id.length && confirm("Are you sure want to delete?")) {
                            $.ajax({
                                type:'POST',
                                url:'customer/deletebyselection',
                                data:{
                                    customerIdArray: customer_id
                                },
                                success:function(data){
                                    alert(data);
                                }
                            });
                            dt.rows({ page: 'current', selected: true }).remove().draw(false);
                        }
                        else if(!customer_id.length)
                            alert('No customer is selected!');
                    }
                    else
                        alert('This feature is disable for demo!');
                }
            },
            {
                extend: 'colvis',
                text: '<i title="column visibility" class="fa fa-eye"></i>',
                columns: ':gt(0)'
            },
        ]
    } );

  $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

  if(all_permission.indexOf("customers-delete") == -1)
        $('.buttons-delete').addClass('d-none');
</script>
@endpush
