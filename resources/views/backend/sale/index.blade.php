@extends('backend.layout.main')
@section('content')
@if(session()->has('message'))
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}</div>
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif


<section>
    <div class="container-fluid">
        @if(in_array("sales-add", $all_permission))
            <a href="{{route('sales.create')}}" class="btn btn-info add-sale-btn"><i class="dripicons-plus"></i> {{trans('file.Add Sale')}}</a>&nbsp;
            <a href="{{url('sales/sale_by_csv')}}" class="btn btn-primary add-sale-btn"><i class="dripicons-copy"></i> {{trans('file.Import Sale')}}</a>
        @endif
        <div class="card mt-3">
            <h3 class="text-center mt-3">{{trans('file.Filter Sales')}}</h3>
            <div class="card-body">
                {!! Form::open(['route' => 'sales.index', 'method' => 'get']) !!}
                <div class="row mt-2">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><strong>{{trans('file.Date')}}</strong></label>
                            <input type="text" class="daterangepicker-field form-control" value="{{$starting_date}} To {{$ending_date}}" required />
                            <input type="hidden" name="starting_date" value="{{$starting_date}}" />
                            <input type="hidden" name="ending_date" value="{{$ending_date}}" />
                        </div>
                    </div>
                    <div class="col-md-3 @if(\Auth::user()->role_id > 2){{'d-none'}}@endif">
                        <div class="form-group">
                            <label><strong>{{trans('file.Warehouse')}}</strong></label>
                            <select id="warehouse_id" name="warehouse_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" >
                                <option value="0">{{trans('file.All Warehouse')}}</option>
                                @foreach($lims_warehouse_list as $warehouse)
                                    <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label><strong>{{trans('file.Sale Status')}}</strong></label>
                            <select id="sale-status" class="form-control" name="sale_status">
                                <option value="0">{{trans('file.All')}}</option>
                                <option value="1">{{trans('file.Completed')}}</option>
                                <option value="2">{{trans('file.Pending')}}</option>
                                <option value="4">{{trans('file.Returned')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label><strong>{{trans('file.Payment Status')}}</strong></label>
                            <select id="payment-status" class="form-control" name="payment_status">
                                <option value="0">{{trans('file.All')}}</option>
                                <option value="1">{{trans('file.Pending')}}</option>
                                <option value="2">{{trans('file.Due')}}</option>
                                <option value="3">{{trans('file.Partial')}}</option>
                                <option value="4">{{trans('file.Paid')}}</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label><strong>{{trans('file.Payment Method')}}</strong></label>
                            <select id="payment-method" class="form-control" name="payment_method">
                                <option value="0">All</option>
                                <option value="Cash">Cash</option>
                                <option value="Gift Card">Gift Card</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Deposit">Deposit</option>
                                <option value="Points">Points</option>
                                <option value="Pesapal">Pesapal</option>
                                @foreach($options as $option)
                                    @if($option !== 'cash' && $option !== 'card' && $option !== 'card' && $option !== 'cheque' && $option !== 'gift_card' && $option !== 'deposit' && $option !== 'paypal' && $option !== 'pesapal')
                                        <option value="{{$option}}">{{$option}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2 @if(!in_array('ecommerce',explode(',',$general_setting->modules))) d-none @endif">
                        <div class="form-group">
                            <label><strong>{{trans('file.Sale Type')}}</strong></label>
                            <select id="sale-type" class="form-control" name="sale_type">
                                <option value="0">{{trans('file.All')}}</option>
                                <option value="pos">{{trans('file.POS')}}</option>
                                <option value="online">{{trans('file.eCommerce')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 mt-3">
                        <div class="form-group">
                            <button class="btn btn-primary" id="filter-btn" type="submit">{{trans('file.submit')}}</button>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table id="sale-table" class="table sale-list" style="width: 100%">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{trans('file.date')}}</th>
                    <th>{{trans('file.reference')}}</th>
                    <th>{{trans('file.Biller')}}</th>
                    <th>{{trans('file.customer')}}</th>
                    <th>{{trans('file.Sale Status')}}</th>
                    <th>{{trans('file.Payment Status')}}</th>
                    <th>{{trans('file.Payment Method')}}</th>
                    <th>{{trans('file.Delivery Status')}}</th>
                    <th>{{trans('file.grand total')}}</th>
                    <th>{{trans('file.Returned Amount')}}</th>
                    <th>{{trans('file.Paid')}}</th>
                    <th>{{trans('file.Due')}}</th>
                    @foreach($custom_fields as $fieldName)
                    <th>{{$fieldName}}</th>
                    @endforeach
                    <th class="not-exported">{{trans('file.action')}}</th>
                </tr>
            </thead>

            <tfoot class="tfoot active">
                <th></th>
                <th>{{trans('file.Total')}}</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                @foreach($custom_fields as $fieldName)
                <th></th>
                @endforeach
                <th></th>
            </tfoot>
        </table>
    </div>
</section>

<div id="sale-details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="container mt-3 pb-2 border-bottom">
                <div class="row">
                    <div class="col-md-6 d-print-none">
                        <button id="print-btn" type="button" class="btn btn-default btn-sm"><i class="dripicons-print"></i> {{trans('file.Print')}}</button>

                        {{ Form::open(['route' => 'sale.sendmail', 'method' => 'post', 'class' => 'sendmail-form'] ) }}
                            <input type="hidden" name="sale_id">
                            <button class="btn btn-default btn-sm d-print-none"><i class="dripicons-mail"></i> {{trans('file.Email')}}</button>
                        {{ Form::close() }}
                    </div>
                    <div class="col-md-6 d-print-none">
                        <button type="button" id="close-btn" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="col-md-4 text-left">
                        <img src="{{url('logo', $general_setting->site_logo)}}" width="90px;">
                    </div>
                    <div class="col-md-4 text-center">
                        <h3 id="exampleModalLabel" class="modal-title container-fluid">{{$general_setting->site_title}}</h3>
                    </div>
                    <div class="col-md-4 text-right">
                        <i style="font-size: 15px;">{{trans('file.Sale Details')}}</i>
                    </div>
                </div>
            </div>
            <div id="sale-content" class="modal-body">
            </div>
            <br>
            <table class="table table-bordered product-sale-list">
                <thead>
                    <th>#</th>
                    <th>{{trans('file.product')}}</th>
                    <th>{{trans('file.Batch No')}}</th>
                    <th>{{trans('file.Qty')}}</th>
                    <th>{{trans('file.Returned')}}</th>
                    <th>{{trans('file.Unit Price')}}</th>
                    <th>{{trans('file.Tax')}}</th>
                    <th>{{trans('file.Discount')}}</th>
                    <th>{{trans('file.Subtotal')}}</th>
                    <th>{{trans('file.Delivered')}}</th>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div id="sale-footer" class="modal-body"></div>
        </div>
    </div>
</div>

<!-- Packing Slip modal -->
<div id="packing-slip-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">Create Packing Slip</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <form action="{{route('packingSlip.store')}}" method="POST" class="packing-slip-form">
                @csrf
                  <div class="row">
                        <input type="hidden" name="sale_id">
                        <input type="hidden" name="amount">
                        <div class="col-md-12 form-group">
                            <h5>Product List</h5>
                            <table class="table table-bordered table-hover product-list mt-3">
                                <thead>
                                    <tr>
                                        <th>{{ trans('file.name') }}</th>
                                        <th>{{ trans('file.Code') }}</th>
                                        <th>Qty</th>
                                        <th>{{ trans('file.Unit Price') }}</th>
                                        <th>{{ trans('file.Total Price') }}</th>
                                        <th>{{ trans('file.Packed') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                  </div>
                  <div class="form-group">
                      <button type="submit" class="btn btn-primary packing-slip-submit-btn">Submit</button>
                  </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="view-payment" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">{{trans('file.All')}} {{trans('file.Payment')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                <table class="table table-hover payment-list">
                    <thead>
                        <tr>
                            <th>{{trans('file.date')}}</th>
                            <th>{{trans('file.reference')}}</th>
                            <th>{{trans('file.Account')}}</th>
                            <th>{{trans('file.Amount')}}</th>
                            <th>{{trans('file.Paid By')}}</th>
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

<div id="add-payment" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Add Payment')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                {!! Form::open(['route' => 'sale.add-payment', 'method' => 'post', 'files' => true, 'class' => 'payment-form' ]) !!}
                    <div class="row">
                        <input type="hidden" name="balance">
                        <div class="col-md-6">
                            <label>{{trans('file.Recieved Amount')}} *</label>
                            <input type="text" name="paying_amount" class="form-control numkey" step="any" required>
                        </div>
                        <div class="col-md-6">
                            <label>{{trans('file.Paying Amount')}} *</label>
                            <input type="text" id="amount" name="amount" class="form-control"  step="any" required>
                        </div>
                        <div class="col-md-6 mt-1">
                            <label>{{trans('file.Change')}} : </label>
                            <p class="change ml-2">{{number_format(0, $general_setting->decimal, '.', '')}}</p>
                        </div>
                        <div class="col-md-6 mt-1">
                            <label>{{trans('file.Paid By')}}</label>
                            <select name="paid_by_id" class="form-control">
                                @if(in_array("cash",$options))
                                <option value="1">Cash</option>
                                @endif
                                @if(in_array("gift_card",$options))
                                <option value="2">Gift Card</option>
                                @endif
                                @if(in_array("card",$options))
                                <option value="3">Credit Card</option>
                                @endif
                                @if(in_array("cheque",$options))
                                <option value="4">Cheque</option>
                                @endif
                                @if(in_array("paypal",$options) && (strlen($lims_pos_setting_data->paypal_live_api_username)>0) && (strlen($lims_pos_setting_data->paypal_live_api_password)>0) && (strlen($lims_pos_setting_data->paypal_live_api_secret)>0))
                                <option value="5">Paypal</option>
                                @endif
                                @if(in_array("deposit",$options))
                                <option value="6">Deposit</option>
                                @endif
                                @if($lims_reward_point_setting_data && $lims_reward_point_setting_data->is_active)
                                <option value="7">Points</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>{{trans('file.Payment Receiver')}}</label>
                            <input type="text" name="payment_receiver" class="form-control">
                        </div>
                    </div>
                    <div class="gift-card form-group">
                        <label> {{trans('file.Gift Card')}} *</label>
                        <select id="gift_card_id" name="gift_card_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Select Gift Card...">
                            @php
                                $balance = [];
                                $expired_date = [];
                            @endphp
                            @foreach($lims_gift_card_list as $gift_card)
                            <?php
                                $balance[$gift_card->id] = $gift_card->amount - $gift_card->expense;
                                $expired_date[$gift_card->id] = $gift_card->expired_date;
                            ?>
                                <option value="{{$gift_card->id}}">{{$gift_card->card_no}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mt-2">
                        <div class="card-element" class="form-control">
                        </div>
                        <div class="card-errors" role="alert"></div>
                    </div>
                    <div id="cheque">
                        <div class="form-group">
                            <label>{{trans('file.Cheque Number')}} *</label>
                            <input type="text" name="cheque_no" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label> {{trans('file.Account')}}</label>
                        <select class="form-control selectpicker" name="account_id">
                        @foreach($lims_account_list as $account)
                            @if($account->is_default)
                            <option selected value="{{$account->id}}">{{$account->name}} [{{$account->account_no}}]</option>
                            @else
                            <option value="{{$account->id}}">{{$account->name}} [{{$account->account_no}}]</option>
                            @endif
                        @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{trans('file.Payment Note')}}</label>
                        <textarea rows="3" class="form-control" name="payment_note"></textarea>
                    </div>

                    <input type="hidden" name="sale_id">

                    <button type="submit" class="btn btn-primary">{{trans('file.submit')}}</button>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="edit-payment" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Update Payment')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                {!! Form::open(['route' => 'sale.update-payment', 'method' => 'post', 'class' => 'payment-form' ]) !!}
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{trans('file.Recieved Amount')}} *</label>
                            <input type="text" name="edit_paying_amount" class="form-control numkey"  step="any" required>
                        </div>
                        <div class="col-md-6">
                            <label>{{trans('file.Paying Amount')}} *</label>
                            <input type="text" name="edit_amount" class="form-control"  step="any" required>
                        </div>
                        <div class="col-md-6 mt-1">
                            <label>{{trans('file.Change')}} : </label>
                            <p class="change ml-2">{{number_format(0, $general_setting->decimal, '.', '')}}</p>
                        </div>
                        <div class="col-md-6 mt-1">
                            <label>{{trans('file.Paid By')}}</label>
                            <select name="edit_paid_by_id" class="form-control selectpicker">
                                @if(in_array("cash",$options))
                                <option value="1">Cash</option>
                                @endif
                                @if(in_array("gift_card",$options))
                                <option value="2">Gift Card</option>
                                @endif
                                @if(in_array("card",$options))
                                <option value="3">Credit Card</option>
                                @endif
                                @if(in_array("cheque",$options))
                                <option value="4">Cheque</option>
                                @endif
                                @if(in_array("paypal",$options) && (strlen($lims_pos_setting_data->paypal_live_api_username)>0) && (strlen($lims_pos_setting_data->paypal_live_api_password)>0) && (strlen($lims_pos_setting_data->paypal_live_api_secret)>0))
                                <option value="5">Paypal</option>
                                @endif
                                @if(in_array("deposit",$options))
                                <option value="6">Deposit</option>
                                @endif
                                @if($lims_reward_point_setting_data && $lims_reward_point_setting_data->is_active)
                                <option value="7">Points</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>{{trans('file.Payment Receiver')}}</label>
                            <input type="text" name="payment_receiver" class="form-control">
                        </div>
                    </div>
                    <div class="gift-card form-group">
                        <label> {{trans('file.Gift Card')}} *</label>
                        <select id="gift_card_id" name="gift_card_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Select Gift Card...">
                            @foreach($lims_gift_card_list as $gift_card)
                                <option value="{{$gift_card->id}}">{{$gift_card->card_no}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mt-2">
                        <div class="card-element" class="form-control">
                        </div>
                        <div class="card-errors" role="alert"></div>
                    </div>
                    <div id="edit-cheque">
                        <div class="form-group">
                            <label>{{trans('file.Cheque Number')}} *</label>
                            <input type="text" name="edit_cheque_no" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label> {{trans('file.Account')}}</label>
                        <select class="form-control selectpicker" name="account_id">
                        @foreach($lims_account_list as $account)
                            <option value="{{$account->id}}">{{$account->name}} [{{$account->account_no}}]</option>
                        @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{trans('file.Payment Note')}}</label>
                        <textarea rows="3" class="form-control" name="edit_payment_note"></textarea>
                    </div>

                    <input type="hidden" name="payment_id">

                    <button type="submit" class="btn btn-primary">{{trans('file.update')}}</button>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="add-delivery" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Add Delivery')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                {!! Form::open(['route' => 'delivery.store', 'method' => 'post', 'files' => true]) !!}
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.Delivery Reference')}}</label>
                        <p id="dr"></p>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.Sale Reference')}}</label>
                        <p id="sr"></p>
                    </div>
                    <div class="col-md-12 form-group">
                        <label>{{trans('file.Status')}} *</label>
                        <select name="status" required class="form-control selectpicker">
                            <option value="1">{{trans('file.Packing')}}</option>
                            <option value="2">{{trans('file.Delivering')}}</option>
                            <option value="3">{{trans('file.Delivered')}}</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.Courier')}}</label>
                        <select name="courier_id" id="courier_id" class="selectpicker form-control" data-live-search="true" title="Select courier...">
                            @foreach($lims_courier_list as $courier)
                            <option value="{{$courier->id}}">{{$courier->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mt-2 form-group">
                        <label>{{trans('file.Delivered By')}}</label>
                        <input type="text" name="delivered_by" class="form-control">
                    </div>
                    <div class="col-md-6 mt-2 form-group">
                        <label>{{trans('file.Recieved By')}} </label>
                        <input type="text" name="recieved_by" class="form-control">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.customer')}} *</label>
                        <p id="customer"></p>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.Attach File')}}</label>
                        <input type="file" name="file" class="form-control">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.Address')}} *</label>
                        <textarea rows="3" name="address" class="form-control" required></textarea>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.Note')}}</label>
                        <textarea rows="3" name="note" class="form-control"></textarea>
                    </div>
                </div>
                <input type="hidden" name="reference_no">
                <input type="hidden" name="sale_id">
                <button type="submit" class="btn btn-primary">{{trans('file.submit')}}</button>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="send-sms" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Send SMS')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('sale.sendsms') }}" method="post">
                    @csrf
                    <div class="row">
                        <input type="hidden" name="customer_id">
                        <input type="hidden" name="reference_no">
                        <input type="hidden" name="sale_status">
                        <input type="hidden" name="payment_status">
                        <div class="col-md-6 mt-1">
                            <label>{{trans('file.SMS Template')}}</label>
                            <select name="template_id" class="form-control">
                                <option value="">Select Template</option>
                                @foreach($smsTemplates as $template)
                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-2">{{trans('file.submit')}}</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ============================
     Sale View Measurements Modal
     ============================ --}}
<div id="saleMeasurementsModal" tabindex="-1" role="dialog" aria-labelledby="saleMeasurementsLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); color: #fff;">
                <div>
                    <h5 id="saleMeasurementsLabel" class="modal-title mb-0" style="font-weight:700; letter-spacing:.5px;">
                        <i class="dripicons-scale mr-2"></i> Sale Measurements
                    </h5>
                    <small id="sm-sale-subtitle" style="opacity:.8;"></small>
                </div>
                <div class="ml-auto d-flex align-items-center">
                    <button type="button" id="sm-print-btn" class="btn btn-sm mr-2"
                        style="background:#e94560; color:#fff; border:none; border-radius:6px; font-weight:600;">
                        <i class="fa fa-print mr-1"></i> Print
                    </button>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close" style="color:#fff; opacity:.9; margin-left:8px;">
                        <span aria-hidden="true"><i class="dripicons-cross"></i></span>
                    </button>
                </div>
            </div>
            <div class="modal-body" id="sm-modal-body" style="background:#f8f9fc; padding:24px;">
                <div class="text-center text-muted py-4">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Loading...</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Printable version (hidden) --}}
<div id="sm-printable" style="display:none;"></div>

@endsection

@push('scripts')
<script type="text/javascript">

    $("ul#sale").siblings('a').attr('aria-expanded','true');
    $("ul#sale").addClass("show");
    $("ul#sale #sale-list-menu").addClass("active");

    @if(config('database.connections.saleprosaas_landlord'))
        if(localStorage.getItem("message")) {
            alert(localStorage.getItem("message"));
            localStorage.removeItem("message");
        }

        numberOfInvoice = <?php echo json_encode($numberOfInvoice)?>;
        $.ajax({
            type: 'GET',
            async: false,
            url: '{{route("package.fetchData", $general_setting->package_id)}}',
            success: function(data) {
                if(data['number_of_invoice'] > 0 && data['number_of_product'] <= numberOfInvoice) {
                    $("a.add-sale-btn").addClass('d-none');
                }
            }
        });
    @endif


    var columns = [{"data": "key"}, {"data": "date"}, {"data": "reference_no"}, {"data": "biller"}, {"data": "customer"}, {"data": "sale_status"}, {"data": "payment_status"},{"data": "payment_method"},{"data": "delivery_status"}, {"data": "grand_total"}, {"data": "returned_amount"}, {"data": "paid_amount"}, {"data": "due"}];
    var field_name = <?php echo json_encode($field_name) ?>;
    for(i = 0; i < field_name.length; i++) {
        columns.push({"data": field_name[i]});
    }
    columns.push({"data": "options"});

    @if($lims_pos_setting_data)
        var public_key = <?php echo json_encode($lims_pos_setting_data->stripe_public_key) ?>;
    @endif
    var all_permission = <?php echo json_encode($all_permission) ?>;
    @if($lims_reward_point_setting_data)
        var reward_point_setting = <?php echo json_encode($lims_reward_point_setting_data) ?>;
    @endif
    var sale_id = [];
    var user_verified = <?php echo json_encode(env('USER_VERIFIED')) ?>;
    var starting_date = <?php echo json_encode($starting_date); ?>;
    var ending_date = <?php echo json_encode($ending_date); ?>;
    var warehouse_id = <?php echo json_encode($warehouse_id); ?>;
    var sale_status = <?php echo json_encode($sale_status); ?>;
    var payment_status = <?php echo json_encode($payment_status); ?>;
    var sale_type = <?php echo json_encode($sale_type); ?>;
    var payment_method = <?php echo json_encode($payment_method); ?>;
    var balance = <?php echo json_encode($balance) ?>;
    var expired_date = <?php echo json_encode($expired_date) ?>;
    var current_date = <?php echo json_encode(date("Y-m-d")) ?>;
    var payment_date = [];
    var payment_reference = [];
    var paid_amount = [];
    var paying_method = [];
    var payment_id = [];
    var payment_note = [];
    var account = [];
    var deposit;
    var without_stock = <?php echo json_encode($general_setting->without_stock) ?>;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#warehouse_id").val(warehouse_id);
    $("#sale-status").val(sale_status);
    $("#payment-status").val(payment_status);
    $("#sale-type").val(sale_type);
    $("#payment-method").val(payment_method);

    // console.log(payment_method);

    $(".daterangepicker-field").daterangepicker({
      callback: function(startDate, endDate, period){
        var starting_date = startDate.format('YYYY-MM-DD');
        var ending_date = endDate.format('YYYY-MM-DD');
        var title = starting_date + ' To ' + ending_date;
        $(this).val(title);
        $('input[name="starting_date"]').val(starting_date);
        $('input[name="ending_date"]').val(ending_date);
      }
    });

    $(".gift-card").hide();
    $(".card-element").hide();
    $("#cheque").hide();
    $('#view-payment').modal('hide');

    $('.selectpicker').selectpicker('refresh');

    $(document).on("click", "tr.sale-link td:not(:first-child, :last-child)", function() {
        var sale = $(this).parent().data('sale');
        saleDetails(sale);
    });

    $(document).on("click", ".view", function(){
        var sale = $(this).parent().parent().parent().parent().parent().data('sale');
        saleDetails(sale);
    });

    $(document).on("click", ".create-packing-slip-btn", function (e) {
        e.preventDefault();
        id = $(this).data('id');
        $("#packing-slip-modal input[name=sale_id]").val(id);
        $.get('sales/get-sold-items/'+id, function (data) {
            if(data == 'All the items of this sale has already been packed') {
                alert(data);
                $("#packing-slip-modal").modal('hide');
            }
            else {
                $("table.product-list tbody").remove();
                var newBody = $("<tbody>");
                $.each(data, function(index){
                    if(index != 'amount') {
                        var newRow = $("<tr>");
                        var cols = '';
                        cols += '<td>' + data[index]['name'] + '</td>';
                        cols += '<td>' + data[index]['code'] + '</td>';
                        cols += '<td>' + data[index]['sold_qty'] + '</td>';
                        cols += '<td>' + data[index]['unit_price'] + '</td>';
                        cols += '<td class="total-price">' + data[index]['total_price'] + '</td>';
                        if( data[index]['type'] == 'standard' && without_stock == 'no' && (data[index]['qty'] > data[index]['stock']) ) {
                            cols += '<td>In stock: '+data[index]['stock']+'</td>';
                        }
                        else if( data[index]['type'] == 'combo' && without_stock == 'no' && !data[index]['combo_in_stock'] ) {
                            cols += '<td>'+data[index]['child_info']+'</td>';
                        }
                        else if(data[index]['is_packing']) {
                            cols += '<td><input type="checkbox" class="is-packing" name="is_packing[]" value="'+data[index]['product_id']+'" checked disabled /></td>';
                        }
                        else {
                            cols += '<td><input type="checkbox" class="is-packing" name="is_packing[]" value="'+data[index]['product_id']+'"/></td>';
                        }

                        newRow.append(cols);
                        newBody.append(newRow);
                        $("table.product-list").append(newBody);
                    }
                });
                $("#packing-slip-modal input[name=amount]").val(data['amount']);
                $("#packing-slip-modal").modal();
            }
        });
    });

    $(document).on("change", ".is-packing", function (e) {
        rowindex = $(this).closest('tr').index();
        var total_price = $('table.product-list tbody tr:nth-child(' + (rowindex + 1) + ') .total-price').text();
        var amount = $("#packing-slip-modal input[name=amount]").val();
        if($(this).is(":checked")) {
            $("#packing-slip-modal input[name=amount]").val(parseFloat(amount) + parseFloat(total_price));
        }
        else {
            $("#packing-slip-modal input[name=amount]").val(parseFloat(amount) - parseFloat(total_price));
        }
    });

    $(document).on('submit', '.packing-slip-form', function(e) {
        $(".packing-slip-submit-btn").prop("disabled", true);
    });

    $(document).on("click", "#print-btn", function() {
        var divContents = document.getElementById("sale-details").innerHTML;
        //console.log(divContents);
        var a = window.open('');
        a.document.write('<html>');
        a.document.write('<body>');
        a.document.write('<style>body{line-height: 1.15;-webkit-text-size-adjust: 100%;}.d-print-none{display:none}.text-left{text-align:left}.text-center{text-align:center}.text-right{text-align:right}.row{width:100%;margin-right: -15px;margin-left: -15px;}.col-md-12{width:100%;display:block;padding: 5px 15px;}.col-md-6{width: 50%;float:left;padding: 5px 15px;}table{width:100%;margin-top:30px;}th{text-aligh:left}td{padding:10px}table,th,td{border: 1px solid black; border-collapse: collapse;}</style><style>@media print {.modal-dialog { max-width: 1000px;} }</style>');
        a.document.write(divContents);
        a.document.write('</body></html>');
        a.document.close();
        a.print();
        setTimeout(function(){a.close();},10);
        //setTimeout(function(){a.print();},20);
        //a.print();
    });

    $(document).on("click", "table.sale-list tbody .add-payment", function() {
        $("#cheque").hide();
        $(".gift-card").hide();
        $(".card-element").hide();
        $('select[name="paid_by_id"]').val(1);
        $('.selectpicker').selectpicker('refresh');
        rowindex = $(this).closest('tr').index();
        deposit = $('table.sale-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.deposit').val();
        var sale_id = $(this).data('id').toString();
        var balance = $('table.sale-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(12)').text();
        balance = parseFloat(balance.replace(/,/g, ''));
        $('input[name="paying_amount"]').val(balance);
        $('#add-payment input[name="balance"]').val(balance);
        $('input[name="amount"]').val(balance);
        $('input[name="sale_id"]').val(sale_id);
    });

    $(document).on("click", "table.sale-list tbody .get-payment", function(event) {
        rowindex = $(this).closest('tr').index();
        deposit = $('table.sale-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.deposit').val();
        var id = $(this).data('id').toString();
        $.get('sales/getpayment/' + id, function(data) {
            $(".payment-list tbody").remove();
            var newBody = $("<tbody>");
            payment_date  = data[0];
            payment_reference = data[1];
            paid_amount = data[2];
            paying_method = data[3];
            payment_id = data[4];
            payment_note = data[5];
            cheque_no = data[6];
            gift_card_id = data[7];
            change = data[8];
            paying_amount = data[9];
            account_name = data[10];
            account_id = data[11];
            payment_receiver = data[12];

            $.each(payment_date, function(index) {
                var newRow = $("<tr>");
                var cols = '';

                cols += '<td>' + payment_date[index] + '</td>';
                cols += '<td>' + payment_reference[index] + '</td>';
                cols += '<td>' + account_name[index] + '</td>';
                cols += '<td>' + paid_amount[index] + '</td>';
                cols += '<td>' + paying_method[index] + '</td>';
                cols += '<td><div class="btn-group"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{trans("file.action")}}<span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">';
                if(paying_method[index] != 'Paypal' && all_permission.indexOf("sale-payment-edit") != -1)
                    cols += '<li><button type="button" class="btn btn-link edit-btn" data-id="' + payment_id[index] +'" data-clicked=false data-toggle="modal" data-target="#edit-payment"><i class="dripicons-document-edit"></i> {{trans("file.edit")}}</button></li> ';
                if(all_permission.indexOf("sale-payment-delete") != -1)
                    cols += '{{ Form::open(['route' => 'sale.delete-payment', 'method' => 'post'] ) }}<li><input type="hidden" name="id" value="' + payment_id[index] + '" /> <button type="submit" class="btn btn-link" onclick="return confirmPaymentDelete()"><i class="dripicons-trash"></i> {{trans("file.delete")}}</button></li>{{ Form::close() }}';
                cols += '</ul></div></td>';
                newRow.append(cols);
                newBody.append(newRow);
                $("table.payment-list").append(newBody);
            });
            $('#view-payment').modal('show');
        });
    });

    $("table.payment-list").on("click", ".edit-btn", function(event) {
        $(".edit-btn").attr('data-clicked', true);
        $(".card-element").hide();
        $("#edit-cheque").hide();
        $('.gift-card').hide();
        $('#edit-payment select[name="edit_paid_by_id"]').prop('disabled', false);
        var id = $(this).data('id').toString();
        $.each(payment_id, function(index){
            if(payment_id[index] == parseFloat(id)){
                $('input[name="payment_id"]').val(payment_id[index]);
                $('#edit-payment select[name="account_id"]').val(account_id[index]);
                if(paying_method[index] == 'Cash')
                    $('select[name="edit_paid_by_id"]').val(1);
                else if(paying_method[index] == 'Gift Card'){
                    $('select[name="edit_paid_by_id"]').val(2);
                    $('#edit-payment select[name="gift_card_id"]').val(gift_card_id[index]);
                    $('.gift-card').show();
                    $('#edit-payment select[name="edit_paid_by_id"]').prop('disabled', true);
                }
                else if(paying_method[index] == 'Credit Card'){
                    $('select[name="edit_paid_by_id"]').val(3);
                    @if($lims_pos_setting_data && (strlen($lims_pos_setting_data->stripe_public_key)>0) && (strlen($lims_pos_setting_data->stripe_secret_key )>0))
                        $.getScript( "vendor/stripe/checkout.js" );
                        $(".card-element").show();
                    @endif
                    $('#edit-payment select[name="edit_paid_by_id"]').prop('disabled', true);
                }
                else if(paying_method[index] == 'Cheque'){
                    $('select[name="edit_paid_by_id"]').val(4);
                    $("#edit-cheque").show();
                    $('input[name="edit_cheque_no"]').val(cheque_no[index]);
                    $('input[name="edit_cheque_no"]').attr('required', true);
                }
                else if(paying_method[index] == 'Deposit')
                    $('select[name="edit_paid_by_id"]').val(6);
                else if(paying_method[index] == 'Points'){
                    $('select[name="edit_paid_by_id"]').val(7);
                }

                $('.selectpicker').selectpicker('refresh');
                $("#payment_reference").html(payment_reference[index]);
                $('input[name="edit_paying_amount"]').val(paying_amount[index]);
                $('#edit-payment .change').text(change[index]);
                $('input[name="edit_amount"]').val(paid_amount[index]);
                $('textarea[name="edit_payment_note"]').val(payment_note[index]);
                $('input[name="payment_receiver"]').val(payment_receiver[index]);
                return false;
            }
        });
        $('#view-payment').modal('hide');
    });

    $('select[name="paid_by_id"]').on("change", function() {
        var id = $(this).val();
        $('input[name="cheque_no"]').attr('required', false);
        $('#add-payment select[name="gift_card_id"]').attr('required', false);
        $(".payment-form").off("submit");
        if(id == 2){
            $(".gift-card").show();
            $(".card-element").hide();
            $("#cheque").hide();
            $('#add-payment select[name="gift_card_id"]').attr('required', true);
        }
        else if (id == 3) {
            @if($lims_pos_setting_data && (strlen($lims_pos_setting_data->stripe_public_key)>0) && (strlen($lims_pos_setting_data->stripe_secret_key )>0))
                $.getScript( "vendor/stripe/checkout.js" );
                $(".card-element").show();
            @endif
            $(".gift-card").hide();
            $("#cheque").hide();
        } else if (id == 4) {
            $("#cheque").show();
            $(".gift-card").hide();
            $(".card-element").hide();
            $('input[name="cheque_no"]').attr('required', true);
        } else if (id == 5) {
            $(".card-element").hide();
            $(".gift-card").hide();
            $("#cheque").hide();
        } else {
            $(".card-element").hide();
            $(".gift-card").hide();
            $("#cheque").hide();
            if(id == 6){
                if($('#add-payment input[name="amount"]').val() > parseFloat(deposit))
                    alert('Amount exceeds customer deposit! Customer deposit : ' + deposit);
            }
            else if(id==7) {
                pointCalculation($('#add-payment input[name="amount"]').val());
            }
        }
    });

    $('#add-payment select[name="gift_card_id"]').on("change", function() {
        var id = $(this).val();
        if(expired_date[id] < current_date)
            alert('This card is expired!');
        else if($('#add-payment input[name="amount"]').val() > balance[id]){
            alert('Amount exceeds card balance! Gift Card balance: '+ balance[id]);
        }
    });

    $('input[name="paying_amount"]').on("input", function() {
        $(".change").text(parseFloat( $(this).val() - $('input[name="amount"]').val() ).toFixed({{$general_setting->decimal}}));
    });

    $('input[name="amount"]').on("input", function() {
        if( $(this).val() > parseFloat($('input[name="paying_amount"]').val()) ) {
            alert('Paying amount cannot be bigger than recieved amount');
            $(this).val('');
        }
        else if( $(this).val() > parseFloat($('input[name="balance"]').val()) ) {
            alert('Paying amount cannot be bigger than due amount');
            $(this).val('');
        }
        $(".change").text(parseFloat($('input[name="paying_amount"]').val() - $(this).val()).toFixed({{$general_setting->decimal}}));
        var id = $('#add-payment select[name="paid_by_id"]').val();
        var amount = $(this).val();
        if(id == 2){
            id = $('#add-payment select[name="gift_card_id"]').val();
            if(amount > balance[id])
                alert('Amount exceeds card balance! Gift Card balance: '+ balance[id]);
        }
        else if(id == 6){
            if(amount > parseFloat(deposit))
                alert('Amount exceeds customer deposit! Customer deposit : ' + deposit);
        }
        else if(id==7) {
            pointCalculation(amount);
        }
    });

    $('select[name="edit_paid_by_id"]').on("change", function() {
        var id = $(this).val();
        $('input[name="edit_cheque_no"]').attr('required', false);
        $('#edit-payment select[name="gift_card_id"]').attr('required', false);
        $(".payment-form").off("submit");
        if(id == 2){
            $(".card-element").hide();
            $("#edit-cheque").hide();
            $('.gift-card').show();
            $('#edit-payment select[name="gift_card_id"]').attr('required', true);
        }
        else if (id == 3) {
            $(".edit-btn").attr('data-clicked', true);
            @if($lims_pos_setting_data && (strlen($lims_pos_setting_data->stripe_public_key)>0) && (strlen($lims_pos_setting_data->stripe_secret_key )>0))
                $.getScript( "vendor/stripe/checkout.js" );
                $(".card-element").show();
            @endif
            $("#edit-cheque").hide();
            $('.gift-card').hide();
        } else if (id == 4) {
            $("#edit-cheque").show();
            $(".card-element").hide();
            $('.gift-card').hide();
            $('input[name="edit_cheque_no"]').attr('required', true);
        } else {
            $(".card-element").hide();
            $("#edit-cheque").hide();
            $('.gift-card').hide();
            if(id == 6) {
                if($('input[name="edit_amount"]').val() > parseFloat(deposit))
                    alert('Amount exceeds customer deposit! Customer deposit : ' + deposit);
            }
            else if(id==7) {
                pointCalculation($('input[name="edit_amount"]').val());
            }
        }
    });

    $('#edit-payment select[name="gift_card_id"]').on("change", function() {
        var id = $(this).val();
        if(expired_date[id] < current_date)
            alert('This card is expired!');
        else if($('#edit-payment input[name="edit_amount"]').val() > balance[id])
            alert('Amount exceeds card balance! Gift Card balance: '+ balance[id]);
    });

    $('input[name="edit_paying_amount"]').on("input", function() {
        $(".change").text(parseFloat( $(this).val() - $('input[name="edit_amount"]').val() ).toFixed({{$general_setting->decimal}}));
    });

    $('input[name="edit_amount"]').on("input", function() {
        if( $(this).val() > parseFloat($('input[name="edit_paying_amount"]').val()) ) {
            alert('Paying amount cannot be bigger than recieved amount');
            $(this).val('');
        }
        $(".change").text(parseFloat($('input[name="edit_paying_amount"]').val() - $(this).val()).toFixed({{$general_setting->decimal}}));
        var amount = $(this).val();
        var id = $('#edit-payment select[name="gift_card_id"]').val();
        if(amount > balance[id]){
            alert('Amount exceeds card balance! Gift Card balance: '+ balance[id]);
        }
        var id = $('#edit-payment select[name="edit_paid_by_id"]').val();
        if(id == 6){
            if(amount > parseFloat(deposit))
                alert('Amount exceeds customer deposit! Customer deposit : ' + deposit);
        }
        else if(id==7) {
            pointCalculation(amount);
        }
    });

    $(document).on("click", "table.sale-list tbody .add-delivery", function(event) {
        var id = $(this).data('id').toString();
        $.get('delivery/create/'+id, function(data) {
            $('#dr').text(data[0]);
            $('#sr').text(data[1]);

            $('select[name="status"]').val(data[2]);
            $('.selectpicker').selectpicker('refresh');
            $('input[name="delivered_by"]').val(data[3]);
            $('input[name="recieved_by"]').val(data[4]);
            $('#customer').text(data[5]);
            $('textarea[name="address"]').val(data[6]);
            $('textarea[name="note"]').val(data[7]);
            $('select[name="courier_id"]').val(data[8]);
            $('.selectpicker').selectpicker('refresh');
            $('input[name="reference_no"]').val(data[0]);
            $('input[name="sale_id"]').val(id);
            $('#add-delivery').modal('show');
        });
    });

    function pointCalculation(amount) {
        availablePoints = $('table.sale-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.points').val();
        required_point = Math.ceil(amount / reward_point_setting['per_point_amount']);
        if(required_point > availablePoints) {
          alert('Customer does not have sufficient points. Available points: '+availablePoints+'. Required points: '+required_point);
        }
    }

    $('#sale-table').DataTable( {
        "processing": true,
        "serverSide": true,
        "ajax":{
            url:"sales/sale-data",
            data:{
                all_permission: all_permission,
                starting_date: starting_date,
                ending_date: ending_date,
                warehouse_id: warehouse_id,
                sale_status: sale_status,
                sale_type: sale_type,
                payment_status: payment_status,
                payment_method: payment_method,
            },
            dataType: "json",
            type:"post"
        },
        /*rowId: function(data) {
              return 'row_'+data['id'];
        },*/
        "createdRow": function( row, data, dataIndex ) {
            $(row).addClass('sale-link');
            $(row).attr('data-sale', data['sale']);
        },
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
                'targets': [0, 3, 4, 5, 6, 7, 10, 11, 12]
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
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                extend: 'excel',
                text: '<i title="export to excel" class="dripicons-document-new"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                text: '<i title="delete" class="dripicons-cross"></i>',
                className: 'buttons-delete',
                action: function ( e, dt, node, config ) {
                    if(user_verified == '1') {
                        sale_id.length = 0;
                        $(':checkbox:checked').each(function(i){
                            if(i){
                                var sale = $(this).closest('tr').data('sale');
                                if(sale)
                                    sale_id[i-1] = sale[13];
                            }
                        });
                        if(sale_id.length && confirm("Are you sure want to delete?")) {
                            $.ajax({
                                type:'POST',
                                url:'sales/deletebyselection',
                                data:{
                                    saleIdArray: sale_id
                                },
                                success:function(data){
                                    alert(data);
                                    //dt.rows({ page: 'current', selected: true }).deselect();
                                    dt.rows({ page: 'current', selected: true }).remove().draw(false);
                                }
                            });
                        }
                        else if(!sale_id.length)
                            alert('Nothing is selected!');
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
        ],
        drawCallback: function () {
            var api = this.api();
            datatable_sum(api, false);
        }
    } );

    function datatable_sum(dt_selector, is_calling_first) {
        if (dt_selector.rows( '.selected' ).any() && is_calling_first) {
            var rows = dt_selector.rows( '.selected' ).indexes();

            $( dt_selector.column( 8 ).footer() ).html(dt_selector.cells( rows, 8, { page: 'current' } ).data().sum().toFixed({{$general_setting->decimal}}));
            $( dt_selector.column( 9 ).footer() ).html(dt_selector.cells( rows, 9, { page: 'current' } ).data().sum().toFixed({{$general_setting->decimal}}));
            $( dt_selector.column( 10 ).footer() ).html(dt_selector.cells( rows, 10, { page: 'current' } ).data().sum().toFixed({{$general_setting->decimal}}));
            $( dt_selector.column( 11 ).footer() ).html(dt_selector.cells( rows, 11, { page: 'current' } ).data().sum().toFixed({{$general_setting->decimal}}));
        }
        else {
            $( dt_selector.column( 8 ).footer() ).html(dt_selector.cells( rows, 8, { page: 'current' } ).data().sum().toFixed({{$general_setting->decimal}}));
            $( dt_selector.column( 9 ).footer() ).html(dt_selector.cells( rows, 9, { page: 'current' } ).data().sum().toFixed({{$general_setting->decimal}}));
            $( dt_selector.column( 10 ).footer() ).html(dt_selector.cells( rows, 10, { page: 'current' } ).data().sum().toFixed({{$general_setting->decimal}}));
            $( dt_selector.column( 11 ).footer() ).html(dt_selector.cells( rows, 11, { page: 'current' } ).data().sum().toFixed({{$general_setting->decimal}}));
        }
    }

    function saleDetails(sale){
        $("#sale-details input[name='sale_id']").val(sale[13]);

        var htmltext = '<strong>{{trans("file.date")}}: </strong>'+sale[0]+'<br><strong>{{trans("file.reference")}}: </strong>'+sale[1]+'<br><strong>{{trans("file.Warehouse")}}: </strong>'+sale[27]+'<br><strong>{{trans("file.Sale Status")}}: </strong>'+sale[2]+'<br><strong>{{trans("file.Currency")}}: </strong>'+sale[31];
        if(sale[32])
            htmltext += '<br><strong>{{trans("file.Exchange Rate")}}: </strong>'+sale[32]+'<br>';
        else
            htmltext += '<br><strong>{{trans("file.Exchange Rate")}}: </strong>N/A<br>';
        if(sale[30])
            htmltext += '<strong>{{trans("file.Attach Document")}}: </strong><a href="documents/sale/'+sale[30]+'">Download</a><br>';
        htmltext += '<br><div class="row"><div class="col-md-6"><strong>{{trans("file.From")}}:</strong><br>'+sale[3]+'<br>'+sale[4]+'<br>'+sale[5]+'<br>'+sale[6]+'<br>'+sale[7]+'<br>'+sale[8]+'</div><div class="col-md-6"><div class="float-right"><strong>{{trans("file.To")}}:</strong><br>'+sale[9]+'<br>'+sale[10]+'<br>'+sale[11]+'<br>'+sale[12]+'</div></div></div>';
        $.get('sales/product_sale/' + sale[13], function(data){
            $(".product-sale-list tbody").remove();
            var name_code = data[0];
            var qty = data[1];
            var unit_code = data[2];
            var tax = data[3];
            var tax_rate = data[4];
            var discount = data[5];
            var subtotal = data[6];
            var batch_no = data[7];
            var return_qty = data[8];
            var is_delivered = data[9];
            var total_qty = 0;
            var newBody = $("<tbody>");
            $.each(name_code, function(index){
                var newRow = $("<tr>");
                var cols = '';
                cols += '<td><strong>' + (index+1) + '</strong></td>';
                cols += '<td>' + name_code[index] + '</td>';
                cols += '<td>' + batch_no[index] + '</td>';
                cols += '<td>' + qty[index] + ' ' + unit_code[index] + '</td>';
                cols += '<td>' + return_qty[index] + '</td>';
                cols += '<td>' + parseFloat(subtotal[index] / qty[index]).toFixed({{$general_setting->decimal}}) + '</td>';
                cols += '<td>' + tax[index] + '(' + tax_rate[index] + '%)' + '</td>';
                cols += '<td>' + discount[index] + '</td>';
                cols += '<td>' + subtotal[index] + '</td>';
                cols += '<td>' + is_delivered[index] + '</td>';
                total_qty += parseFloat(qty[index]);
                newRow.append(cols);
                newBody.append(newRow);
            });

            var newRow = $("<tr>");
            cols = '';
            cols += '<td colspan=3><strong>{{trans("file.Total")}}:</strong></td>';
            cols += '<td>' + total_qty + '</td>';
            cols += '<td colspan=2></td>';
            cols += '<td>' + sale[14] + '</td>';
            cols += '<td>' + sale[15] + '</td>';
            cols += '<td>' + sale[16] + '</td>';
            cols += '<td></td>';
            newRow.append(cols);
            newBody.append(newRow);

            var newRow = $("<tr>");
            cols = '';
            cols += '<td colspan=9><strong>{{trans("file.Order Tax")}}:</strong></td>';
            cols += '<td>' + sale[17] + '(' + sale[18] + '%)' + '</td>';
            newRow.append(cols);
            newBody.append(newRow);

            var newRow = $("<tr>");
            cols = '';
            cols += '<td colspan=9><strong>{{trans("file.Order Discount")}}:</strong></td>';
            cols += '<td>' + sale[19] + '</td>';
            newRow.append(cols);
            newBody.append(newRow);
            if(sale[28]) {
                var newRow = $("<tr>");
                cols = '';
                cols += '<td colspan=9><strong>{{trans("file.Coupon Discount")}} ['+sale[28]+']:</strong></td>';
                cols += '<td>' + sale[29] + '</td>';
                newRow.append(cols);
                newBody.append(newRow);
            }

            var newRow = $("<tr>");
            cols = '';
            cols += '<td colspan=9><strong>{{trans("file.Shipping Cost")}}:</strong></td>';
            cols += '<td>' + sale[20] + '</td>';
            newRow.append(cols);
            newBody.append(newRow);

            var newRow = $("<tr>");
            cols = '';
            cols += '<td colspan=9><strong>{{trans("file.grand total")}}:</strong></td>';
            cols += '<td>' + sale[21] + '</td>';
            newRow.append(cols);
            newBody.append(newRow);

            var newRow = $("<tr>");
            cols = '';
            cols += '<td colspan=9><strong>{{trans("file.Paid Amount")}}:</strong></td>';
            cols += '<td>' + sale[22] + '</td>';
            newRow.append(cols);
            newBody.append(newRow);

            var newRow = $("<tr>");
            cols = '';
            cols += '<td colspan=9><strong>{{trans("file.Due")}}:</strong></td>';
            cols += '<td>' + parseFloat(sale[21] - sale[22]).toFixed({{$general_setting->decimal}}) + '</td>';
            newRow.append(cols);
            newBody.append(newRow);

            $("table.product-sale-list").append(newBody);
        });
        var htmlfooter = '';
        if (sale[33]) {
            htmlfooter += '<p><strong>Sale Type:</strong> ' + sale[33] + '</p>';
        }
        htmlfooter += '<p><strong>{{trans("file.Sale Note")}}:</strong> '+sale[23]+'</p><p><strong>{{trans("file.Staff Note")}}:</strong> '+sale[24]+'</p><strong>{{trans("file.Created By")}}:</strong><br>'+sale[25]+'<br>'+sale[26];
        $('#sale-content').html(htmltext);
        $('#sale-footer').html(htmlfooter);
        $('#sale-details').modal('show');
    }

    $(document).on('submit', '.payment-form', function(e) {
        if( $('input[name="paying_amount"]').val() < parseFloat($('#amount').val()) ) {
            alert('Paying amount cannot be bigger than recieved amount');
            $('input[name="amount"]').val('');
            $(".change").text(parseFloat( $('input[name="paying_amount"]').val() - $('#amount').val() ).toFixed({{$general_setting->decimal}}));
            e.preventDefault();
        }
        else if( $('input[name="edit_paying_amount"]').val() < parseFloat($('input[name="edit_amount"]').val()) ) {
            alert('Paying amount cannot be bigger than recieved amount');
            $('input[name="edit_amount"]').val('');
            $(".change").text(parseFloat( $('input[name="edit_paying_amount"]').val() - $('input[name="edit_amount"]').val() ).toFixed({{$general_setting->decimal}}));
            e.preventDefault();
        }

        $('#edit-payment select[name="edit_paid_by_id"]').prop('disabled', false);
    });

    if(all_permission.indexOf("sales-delete") == -1)
        $('.buttons-delete').addClass('d-none');

        function confirmDelete() {
            if (confirm("Are you sure want to delete?")) {
                return true;
            }
            return false;
        }

    function confirmPaymentDelete() {
        if (confirm("Are you sure want to delete? If you delete this money will be refunded.")) {
            return true;
        }
        return false;
    }

    $(document).ready(function() {
        $(document).on('click', '.send-sms', function(){
            $("#send-sms input[name='customer_id']").val($(this).data('customer_id'));
            $("#send-sms input[name='reference_no']").val($(this).data('reference_no'));
            $("#send-sms input[name='sale_status']").val($(this).data('sale_status'));
            $("#send-sms input[name='payment_status']").val($(this).data('payment_status'));
        });
    });

    /* ============================================================
       SALE VIEW MEASUREMENTS POPUP
       ============================================================ */
    $(document).on('click', '.view-sale-measurements', function () {
        var customerId    = $(this).data('customer-id');
        var saleId        = $(this).data('sale-id');
        var saleTypeId    = $(this).data('sale-type-id') || '';
        var saleRef       = $(this).data('sale-ref');
        var saleDate      = $(this).data('sale-date');
        var saleStatus    = $(this).data('sale-status');
        var saleType      = $(this).data('sale-type');
        var saleTotal     = $(this).data('sale-total');
        var salePaid      = $(this).data('sale-paid');
        var saleWarehouse = $(this).data('sale-warehouse');

        $('#sm-sale-subtitle').text('Ref: ' + saleRef);
        $('#sm-modal-body').html('<div class="text-center text-muted py-4"><i class="fa fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading...</p></div>');

        var saleInfo = { ref: saleRef, date: saleDate, status: saleStatus, type: saleType,
                         total: saleTotal, paid: salePaid, warehouse: saleWarehouse, saleTypeId: saleTypeId };

        var reqMeasurements = $.get('/customer/' + customerId + '/measurements/all');
        var reqProducts     = $.get('sales/product_sale/' + saleId);

        $.when(reqMeasurements, reqProducts).done(function (mRes, pRes) {
            renderSaleMeasurementsModal(mRes[0], pRes[0], saleInfo);
        }).fail(function () {
            $('#sm-modal-body').html('<div class="alert alert-danger">Failed to load data. Please try again.</div>');
        });
    });

    function renderSaleMeasurementsModal(data, productData, saleInfo) {
        var c = data.customer;
        // Filter measurements: only the one matching this sale's garment_sale_type_id
        var allMeasurements = data.measurements;
        var measurements = saleInfo.saleTypeId
            ? allMeasurements.filter(function(m){ return String(m.sale_type_id) === String(saleInfo.saleTypeId); })
            : allMeasurements;

        var html = '';

        // -- Sale info card --
        html += '<div class="card mb-3 border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">';
        html += '<div class="card-body" style="background:linear-gradient(135deg,#0f3460,#e94560);color:#fff;padding:20px 24px;">';
        html += '<div class="row">';
        html += '<div class="col-md-6">';
        html += '<div style="font-size:11px;opacity:.75;text-transform:uppercase;letter-spacing:.6px;">Sale Reference</div>';
        html += '<div style="font-size:20px;font-weight:700;">' + smEscapeHtml(saleInfo.ref) + '</div>';
        html += '<div class="mt-2" style="font-size:12px;opacity:.85;"><i class="fa fa-calendar mr-1"></i>' + smEscapeHtml(saleInfo.date) + '</div>';
        html += '<div style="font-size:12px;opacity:.85;"><i class="fa fa-flag mr-1"></i>' + smEscapeHtml(saleInfo.status) + '</div>';
        if (saleInfo.type) html += '<div style="font-size:12px;opacity:.85;"><i class="dripicons-scale mr-1"></i>Type: ' + smEscapeHtml(saleInfo.type) + '</div>';
        if (saleInfo.warehouse) html += '<div style="font-size:12px;opacity:.85;"><i class="fa fa-home mr-1"></i>' + smEscapeHtml(saleInfo.warehouse) + '</div>';
        html += '</div></div></div></div>';

        // -- Customer info card --
        html += '<div class="card mb-3 border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">';
        html += '<div class="card-body" style="background:linear-gradient(135deg,#1a1a2e,#0f3460);color:#fff;padding:20px 24px;">';
        html += '<div class="row align-items-center">';
        html += '<div class="col-auto"><div style="width:52px;height:52px;background:rgba(233,69,96,.9);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;color:#fff;">' + c.name.charAt(0).toUpperCase() + '</div></div>';
        html += '<div class="col">';
        html += '<h5 class="mb-0 font-weight-bold" style="color:#fff;">' + smEscapeHtml(c.name) + '</h5>';
        if (c.phone_number) html += '<small style="opacity:.85;"><i class="fa fa-phone mr-1"></i>' + smEscapeHtml(c.phone_number) + '</small>';
        if (c.email)        html += '&nbsp;&nbsp;<small style="opacity:.85;"><i class="fa fa-envelope mr-1"></i>' + smEscapeHtml(c.email) + '</small>';
        if (c.address||c.city) html += '<br><small style="opacity:.8;"><i class="fa fa-map-marker mr-1"></i>' + smEscapeHtml((c.address||'')+(c.city?', '+c.city:'')) + '</small>';
        html += '</div></div></div></div>';

        // -- Products table --
        html += '<div class="card mb-3 border-0 shadow-sm" style="border-radius:10px;overflow:hidden;">';
        html += '<div class="card-header py-2 px-3" style="background:#16213e;color:#fff;font-weight:700;font-size:14px;"><i class="dripicons-box mr-2"></i>Products</div>';
        html += '<div class="table-responsive">';
        html += '<table class="table table-sm table-bordered mb-0" style="font-size:13px;">';
        html += '<thead style="background:#f0f4ff;"><tr>';
        html += '<th>#</th><th>{{trans("file.product")}}</th><th>{{trans("file.Batch No")}}</th>';
        html += '<th>{{trans("file.qty")}}</th><th>{{trans("file.Return")}}</th>';
        html += '<th>{{trans("file.Delivered")}}</th>';
        html += '</tr></thead><tbody>';

        if (productData && productData[0]) {
            var name_code  = productData[0];
            var qty        = productData[1];
            var unit_code  = productData[2];
            var batch_no   = productData[7];
            var return_qty = productData[8];
            var delivered  = productData[9];
            var total_qty  = 0;
            $.each(name_code, function(i) {
                total_qty += parseFloat(qty[i]);
                html += '<tr>';
                html += '<td><strong>' + (parseInt(i)+1) + '</strong></td>';
                html += '<td>' + name_code[i] + '</td>';
                html += '<td>' + batch_no[i] + '</td>';
                html += '<td>' + qty[i] + ' ' + unit_code[i] + '</td>';
                html += '<td>' + return_qty[i] + '</td>';
                html += '<td>' + delivered[i] + '</td>';
                html += '</tr>';
            });
            html += '<tr style="background:#f8f9fc;font-weight:700;">';
            html += '<td colspan=3><strong>{{trans("file.Total")}}</strong></td>';
            html += '<td>' + total_qty + '</td><td colspan=2></td>';
            html += '</tr>';
        } else {
            html += '<tr><td colspan=6 class="text-center text-muted">No products found.</td></tr>';
        }
        html += '</tbody></table></div></div>';

        // -- Measurements accordion (filtered) --
        html += '<div class="mb-2" style="font-size:13px;font-weight:700;color:#0f3460;text-transform:uppercase;letter-spacing:.6px;"><i class="dripicons-scale mr-1"></i>Measurements';
        if (!saleInfo.saleTypeId) html += ' <small style="font-weight:normal;color:#aaa;">(all sale types)</small>';
        html += '</div>';

        if (measurements.length === 0) {
            html += '<div class="text-center py-3" style="color:#aaa;"><i class="dripicons-scale" style="font-size:36px;"></i><p class="mt-2">No measurements recorded for this sale type.</p></div>';
        } else {
            html += '<style>.sm-acc-body{overflow:hidden;transition:max-height .35s ease,opacity .35s ease;max-height:0;opacity:0;}.sm-acc-body.sm-open{max-height:2000px;opacity:1;}.sm-acc-header{cursor:pointer;user-select:none;}.sm-chevron{transition:transform .3s ease;display:inline-block;}.sm-open-header .sm-chevron{transform:rotate(180deg);}</style>';
            measurements.forEach(function (m, idx) {
                var accentColors = ['#0f3460','#1a1a2e','#16213e','#533483','#e94560'];
                var accent = accentColors[idx % accentColors.length];
                var collapseId = 'sm-collapse-' + idx;
                var isOpen = (idx === 0);
                html += '<div class="card mb-3 border-0 shadow-sm" style="border-radius:10px;overflow:hidden;">';
                html += '<div class="sm-acc-header d-flex align-items-center py-2 px-3' + (isOpen?' sm-open-header':'') + '" data-target="' + collapseId + '" style="background:' + accent + ';color:#fff;">';
                html += '<span class="font-weight-bold" style="font-size:15px;"><i class="dripicons-scale mr-2"></i>' + smEscapeHtml(m.sale_type_name) + '</span>';
                if (m.measurement_unit) html += '<span class="ml-2 badge" style="background:rgba(255,255,255,.2);color:#fff;font-size:11px;">Unit: ' + smEscapeHtml(m.measurement_unit) + '</span>';
                html += '<small class="ml-auto mr-2" style="opacity:.75;">Updated: ' + smEscapeHtml(m.updated_at) + '</small>';
                html += '<span class="sm-chevron" style="font-size:16px;line-height:1;">&#8963;</span>';
                html += '</div>';
                html += '<div id="' + collapseId + '" class="sm-acc-body' + (isOpen?' sm-open':'') + '"><div style="padding:16px 20px;">';
                if (m.fields.length > 0) {
                    html += '<div class="row">';
                    m.fields.forEach(function(f){
                        html += '<div class="col-sm-6 col-md-4 mb-2"><div style="background:#f0f4ff;border-radius:8px;padding:10px 14px;">';
                        html += '<small style="color:#888;font-size:11px;text-transform:uppercase;letter-spacing:.6px;">' + smEscapeHtml(f.label) + '</small>';
                        html += '<div style="font-weight:700;font-size:16px;color:#1a1a2e;">' + (f.value!==''?smEscapeHtml(String(f.value)):'<span style="color:#ccc;">—</span>') + '</div>';
                        html += '</div></div>';
                    });
                    html += '</div>';
                } else {
                    html += '<p class="text-muted">No measurement fields defined.</p>';
                }
                if (m.notes) html += '<div class="mt-2" style="background:#fff8e1;border-left:4px solid #ffc107;border-radius:4px;padding:8px 12px;"><small style="color:#888;"><i class="fa fa-sticky-note-o mr-1"></i>Notes:</small><div style="color:#555;">' + smEscapeHtml(m.notes) + '</div></div>';
                html += '</div></div></div>';
            });
        }

        $('#sm-modal-body').html(html);

        $('#sm-modal-body').off('click','.sm-acc-header').on('click','.sm-acc-header',function(){
            var targetId=$(this).data('target');
            var $body=$('#'+targetId);
            var isOpen=$body.hasClass('sm-open');
            $body.toggleClass('sm-open',!isOpen);
            $(this).toggleClass('sm-open-header',!isOpen);
        });

        $('#sm-printable').html(buildSalePrintable(data, productData, saleInfo, measurements));
    }

    function buildSalePrintable(data, productData, saleInfo, measurements) {
        var c = data.customer;
        var html = '';
        html += '<style>body{font-family:Arial,sans-serif;color:#222;padding:24px;}h1{font-size:20px;border-bottom:2px solid #0f3460;padding-bottom:8px;margin-bottom:12px;}.si{display:flex;flex-wrap:wrap;gap:14px;background:#f5f7ff;border-radius:6px;padding:14px 18px;margin-bottom:14px;}.si .f{min-width:120px;}.si .f label{display:block;font-size:11px;color:#666;text-transform:uppercase;}.si .f span{font-size:14px;font-weight:700;}.ci{background:#eef2ff;border-radius:6px;padding:10px 14px;margin-bottom:14px;font-size:14px;}.pt{width:100%;border-collapse:collapse;margin-bottom:18px;font-size:12px;}.pt th{background:#0f3460;color:#fff;padding:6px 8px;text-align:left;}.pt td{padding:5px 8px;border-bottom:1px solid #e0e0e0;}.pt tr:nth-child(even){background:#f5f7ff;}.sec{margin-bottom:20px;}.sec h2{background:#0f3460;color:#fff;padding:7px 12px;border-radius:6px 6px 0 0;font-size:14px;margin:0;}.flds{display:flex;flex-wrap:wrap;gap:8px;padding:10px;border:1px solid #ddd;border-top:none;border-radius:0 0 6px 6px;}.fi{background:#f0f4ff;border-radius:5px;padding:7px 10px;min-width:120px;}.fi label{display:block;font-size:10px;color:#666;text-transform:uppercase;}.fi span{font-size:14px;font-weight:700;}.notes{background:#fff8e1;border-left:4px solid #ffc107;padding:7px 10px;margin-top:7px;font-size:12px;}</style>';
        html += '<h1>Sale Measurements</h1>';

        // Sale info
        html += '<div class="si">';
        html += '<div class="f"><label>Reference</label><span>' + smEscapeHtml(saleInfo.ref) + '</span></div>';
        html += '<div class="f"><label>Date</label><span>' + smEscapeHtml(saleInfo.date) + '</span></div>';
        html += '<div class="f"><label>Status</label><span>' + smEscapeHtml(saleInfo.status) + '</span></div>';
        if (saleInfo.type)      html += '<div class="f"><label>Sale Type</label><span>' + smEscapeHtml(saleInfo.type) + '</span></div>';
        if (saleInfo.warehouse) html += '<div class="f"><label>Warehouse</label><span>' + smEscapeHtml(saleInfo.warehouse) + '</span></div>';
        html += '</div>';

        // Customer info
        html += '<div class="ci"><strong>' + smEscapeHtml(c.name) + '</strong>';
        if (c.phone_number) html += ' | ' + smEscapeHtml(c.phone_number);
        if (c.email)        html += ' | ' + smEscapeHtml(c.email);
        if (c.address||c.city) html += ' | ' + smEscapeHtml((c.address||'')+(c.city?', '+c.city:''));
        html += '</div>';

        // Products table
        if (productData && productData[0]) {
            var nc=productData[0],qty=productData[1],uc=productData[2],bn=productData[7],rq=productData[8],del=productData[9];
            html += '<table class="pt"><thead><tr><th>#</th><th>Product</th><th>Batch</th><th>Qty</th><th>Return</th><th>Delivered</th></tr></thead><tbody>';
            $.each(nc, function(i){
                html += '<tr><td>'+(parseInt(i)+1)+'</td><td>'+nc[i]+'</td><td>'+bn[i]+'</td><td>'+qty[i]+' '+uc[i]+'</td><td>'+rq[i]+'</td><td>'+del[i]+'</td></tr>';
            });
            html += '</tbody></table>';
        }

        // Measurements (already filtered)
        if (measurements.length === 0) {
            html += '<p style="color:#999;">No measurements recorded for this sale type.</p>';
        } else {
            measurements.forEach(function(m){
                html += '<div class="sec"><h2>' + smEscapeHtml(m.sale_type_name);
                if (m.measurement_unit) html += ' <small style="font-size:11px;font-weight:normal;">(Unit: ' + smEscapeHtml(m.measurement_unit) + ')</small>';
                html += ' <small style="font-size:11px;font-weight:normal;float:right;">Updated: ' + smEscapeHtml(m.updated_at) + '</small></h2>';
                html += '<div class="flds">';
                m.fields.forEach(function(f){ html += '<div class="fi"><label>'+smEscapeHtml(f.label)+'</label><span>'+(f.value!==''?smEscapeHtml(String(f.value)):'—')+'</span></div>'; });
                html += '</div>';
                if (m.notes) html += '<div class="notes"><strong>Notes:</strong> '+smEscapeHtml(m.notes)+'</div>';
                html += '</div>';
            });
        }
        return html;
    }

    $('#sm-print-btn').on('click', function () {
        var printContents = $('#sm-printable').html();
        var win = window.open('', '_blank', 'width=900,height=700');
        win.document.write('<html><head><title>Sale Measurements</title></head><body>');
        win.document.write(printContents);
        win.document.write('</body></html>');
        win.document.close();
        win.focus();
        win.print();
    });

    function smEscapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

</script>
<script type="text/javascript" src="https://js.stripe.com/v3/"></script>
@endpush
