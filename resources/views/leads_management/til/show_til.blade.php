@extends('admins.layouts.app')

@section('content')
  <link rel="stylesheet" href="{!! asset('public/admin_assets/plugins/jquery-toast/jquery.toast.min.css') !!}">
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>TIL: {{$tilDraft->til_code}}</h1>
    <ol class="breadcrumb">
      <li>
        <a href="{{ url('employees/dashboard') }}">
          <i class="fa fa-dashboard"></i> Home
        </a>
      </li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <!-- Small boxes (Stat box) -->
    <div class="row">
      <div class="col-sm-12">
        <div class="box box-primary">
          <!-- form start -->
          @include('admins.validation_errors')
          <div class="box-body">
            <div class="row">
              <div class="col-md-12">
                @php 
                  $businessTypeArr = [
                    1 => 'Govt. Business', 2 => 'Corporate Business', 3 => 'International Business'
                  ];
                @endphp
                
                <div class="col-md-3 form-group">
                  <label class="col-md-12 businessType1">Tender Owner:</label>
                  <div class="col-md-12">
                    {!! (!empty($tilDraft->tender_owner))? $tilDraft->tender_owner : '--' !!}
                  </div>
                </div>

                <div class="form-group col-md-3">
                  <label class="control-label col-md-12">Name of Department:</label>
                  <div class="col-md-12">
                    {!! (!empty($tilDraft->department))? $tilDraft->department : '--' !!}
                  </div>
                </div>

                <div class="form-group col-md-3">
                  <label class="control-label col-md-12">Location:</label>
                  <div class="col-md-12">
                    {!! (!empty($tilDraft->tender_location))? $tilDraft->tender_location : '--' !!}
                  </div>
                </div>

                <div class="form-group col-md-3">
                  <label class="control-label col-md-12">Due Date:</label>
                  <div class="col-md-12">
                    @if(!empty($tilDraft->due_date) && $tilDraft->due_date != '0000-00-00 00:00:00')
                        {!! date('m/d/Y H:i A', strtotime($tilDraft->due_date)) !!}
                      @else 
                        --
                    @endif
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group col-md-3">
                  <label class="control-label col-md-12">Contact Person detail:</label>
                </div>

                <div class="form-group col-md-3">
                  <div class="col-md-12">
                    <a href="#" data-toggle="modal" data-target="#add_contact_details">
                      View Details
                    </a>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group col-md-3">
                  <label class="control-label col-md-12">Vertical:</label>
                  <div class="col-md-12">
                    @if(!empty($tilDraft->vertical_id))
                        {!! $verticalOptions[$tilDraft->vertical_id] !!}
                      @else 
                        --
                    @endif
                  </div>
                </div>

                <div class="col-md-3 form-group">
                  <label class="control-label col-md-12">Vertical Others:</label>
                  <div class="col-md-12">
                    @if(!empty($tilDraft->other_vertical))
                        {!! $tilDraft->other_vertical !!}
                      @else 
                        --
                    @endif
                  </div>
                </div>

                <div class="form-group col-md-3">
                  <label class="control-label col-md-12">Value of work: <small>(In Lakhs)</small></label>
                  <div class="col-md-12">
                    {!! (!empty($tilDraft->value_of_work))? numberFormat($tilDraft->value_of_work) : '--' !!}
                  </div>
                </div>
                
                <div class="col-md-3 form-group">
                  <label class="contorl-label col-md-12">Bid System:</label>
                  <div class="col-md-12">
                    {!! ucfirst($tilDraft->bid_system) !!}
                  </div>
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-12">
                <div class="col-md-3 form-group">
                  <label class="control-label col-md-12">Volume:</label>
                  <div class="col-md-12">
                    {!! (!empty($tilDraft->volume))? numberFormat($tilDraft->volume) : '--' !!}
                  </div>
                </div>

                <div class="col-md-3 form-group">
                  <label class="control-label col-md-12">
                    Tenure: <small>(In Months)</small>
                  </label>
                  <div class="col-md-12">
                    @if(!empty($tilDraft->tenure_one))
                        {!! $tilDraft->tenure_one !!} Months
                      @else 
                        --
                    @endif
                  </div>
                </div>

                <div class="col-md-3 form-group">
                  <label class="control-label col-md-12">
                    Tenure Extension: <small>(If applicable)</small>
                  </label>
                  <div class="col-md-12">
                    @if(!empty($tilDraft->tenure_two))
                        {!! $tilDraft->tenure_two !!} Months
                      @else 
                        --
                    @endif
                  </div>
                </div>

                <div class="col-md-3 form-group">
                  <label class="control-label col-md-12">Special Eligibility Clause:</label>
                  <div class="col-md-12">
                    @if(!empty($tilDraft->tilSpecialEligibility)) 
                      @foreach($tilDraft->tilSpecialEligibility as $eKey => $eligibility)
                        <span class="label label-primary">{!! $eligibility->special_eligibility !!}</span>
                      @endforeach
                    @else 
                      --
                    @endif
                  </div>
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-12">
                <div class="col-md-3 form-group">
                  <label class="control-label col-md-12">EMD Date:</label>
                  <div class="col-md-12">
                    @if(!empty($tilDraft->emd_date) && $tilDraft->emd_date != '0000-00-00 00:00:00')
                        {!! date('m/d/Y', strtotime($tilDraft->emd_date)) !!}
                    @else 
                      --
                    @endif
                  </div>
                </div>

                <div class="col-md-3 form-group">
                  <label class="control-label col-md-12">EMD:</label>
                  <div class="col-md-12">
                    @php 
                      if(array_key_exists('', $emdOptions)) {
                        unset($emdOptions['']);
                      }
                    @endphp
                    @if(!empty($tilDraft->emd))
                      @php 
                        $draftEmd = explode(',', $tilDraft->emd);
                      @endphp

                      @foreach($draftEmd as $emdKey => $emdVal)
                        <span class="label label-primary">{!! $emdOptions[$emdVal] !!}</span>
                      @endforeach
                    @else                       
                      --
                    @endif
                  </div>
                </div>

                <div class="col-md-3 form-group">
                  <label class="control-label col-md-12">EMD Amount:</label>
                  <div class="col-md-12">
                    @if(!empty($tilDraft->emd_amount))
                      <span class="fa fa-inr">{!! numberFormat($tilDraft->emd_amount) !!}</span>
                    @else
                      --
                    @endif
                  </div>
                </div>

                <div class="col-md-3 form-group">
                  <label class="control-label col-md-12">EMD Exempted:</label>
                  <div class="col-md-12">
                    @if(!empty($tilDraft->emd_exempted))
                      {!! nl2br($tilDraft->emd_exempted) !!}
                    @else
                    --
                    @endif
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-sm-12">
                <div class="col-md-3 form-group">
                  <label class="control-label col-md-12">Tender Fee:</label>
                  <div class="col-md-12">
                    @php 
                      if(array_key_exists('', $tenderFeeOptions)) {
                        unset($tenderFeeOptions['']);
                      }
                    @endphp
                    @if(!empty($tilDraft->tender_fee))
                      @php 
                        $draftTenderFee = explode(',', $tilDraft->tender_fee);
                      @endphp

                      @foreach($draftTenderFee as $tfKey => $tfVal)
                        <span class="label label-primary">{!! $tenderFeeOptions[$tfVal] !!}</span>
                      @endforeach
                    @else 
                      --
                    @endif
                  </div>
                </div>

                <div class="col-md-3 form-group">
                  <label class="control-label col-md-12">Tender Fee Amount:</label>
                  <div class="col-md-12">
                    @if(!empty($tilDraft->tender_fee_amount))
                      <span class="fa fa-inr">{!! $tilDraft->tender_fee_amount !!}</span>
                    @else
                      --
                    @endif
                  </div>
                </div>

                <div class="col-md-3 form-group">
                  <label class="control-label col-md-12">Tender Fee Exempted:</label>
                  <div class="col-md-12">
                    @if(!empty($tilDraft->tender_fee_exempted))
                      {!! nl2br($tilDraft->tender_fee_exempted) !!}
                    @else
                      --
                    @endif
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-sm-12">
                <div class="col-md-3 form-group">
                  <label class="control-label col-md-12">Processing Fee:</label>
                  <div class="col-md-12">
                    @php 
                      if(array_key_exists('', $processingFeeOptions)) {
                        unset($processingFeeOptions['']);
                      }
                    @endphp
                    @if(!empty($tilDraft->processing_fee))
                      @php 
                        $draftProcessingFee = explode(',', $tilDraft->processing_fee);
                      @endphp

                      @foreach($draftProcessingFee as $pKey => $pVal)
                        <span class="label label-primary">{!! $processingFeeOptions[$pVal] !!}</span>
                      @endforeach
                    @else 
                      --
                    @endif
                  </div>
                </div>

                <div class="col-md-3 form-group">
                  <label class="control-label col-md-12">Processing Fee Amount:</label>
                  <div class="col-md-12">
                    @if(!empty($tilDraft->processing_fee_amount))
                      <span class="fa fa-inr">{!! $tilDraft->processing_fee_amount !!}</span>
                    @else
                      --
                    @endif
                  </div>
                </div>

                <div class="col-md-3 form-group">
                  <label class="control-label col-md-12">Processing Fee Exempted:</label>
                  <div class="col-md-12">
                    @if(!empty($tilDraft->processing_fee_exempted))
                      {!! nl2br($tilDraft->processing_fee_exempted) !!}
                    @else
                      --
                    @endif
                  </div>
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-sm-12">
                <div class="col-md-3 form-group">
                  <label class="control-label col-md-12">Performance Guaranty & Security:</label>
                  <div class="col-md-12">
                    <!-- performance_guarantee_typ, performance_guarantee--->
                    @php
                      $performanceTypeArr = [1 => 'Fixed', 2 => 'Percent'];
                    @endphp
                    <span class="label label-primary">{!! $performanceTypeArr[$tilDraft->performance_guarantee_type] !!}</span>

                    @if(!empty($tilDraft->performance_guarantee))
                      <span class="label label-success">
                        @php
                          $performanceGuarantee = $tilDraft->performance_guarantee;
                          if($tilDraft->performance_guarantee_type == 1) {
                            $performanceGuarantee = numberFormat($tilDraft->performance_guarantee);
                          }
                        @endphp
                        {!! $performanceGuarantee !!}
                        @if($tilDraft->performance_guarantee_type == 2)
                          %
                        @endif
                      </span>
                    @endif
                  </div>
                </div>

                <div class="col-md-3 form-group performance_guarantee_clause_div">
                  <label for="performance_guarantee_clause" class="control-label col-md-12">Performance Guaranty & Security Clause:</label>
                  <div class="col-md-12">
                    @if(!empty($tilDraft->performance_guarantee_clause))
                      {!! nl2br($tilDraft->performance_guarantee_clause) !!}
                    @else
                    --
                    @endif
                  </div>
                </div>

                <div class="col-md-3 form-group">
                  <label class="control-label col-md-12">Pre Bid Meeting:</label>
                  <div class="col-md-12">
                    @if(!empty($tilDraft->pre_bid_meeting) && $tilDraft->pre_bid_meeting != '0000-00-00 00:00:00')
                        {!! date('m/d/Y H:i A', strtotime($tilDraft->pre_bid_meeting)) !!}
                      @else
                      --
                    @endif
                  </div>
                </div>

                <div class="col-md-3 form-group">
                  <label class="control-label col-md-12">Payment Terms:</label>
                  <div class="col-md-12">
                    @php
                      $paymentTermArr = [1 => 'Pay & Collect', 2 => 'Collect & Pay'];
                      // pay_and_collect, collect_and_pay, payAndCollectOptions, collectAndPayOptions
                      if(array_key_exists('', $payAndCollectOptions)) {
                        unset($payAndCollectOptions['']);
                      }
                      
                      if(array_key_exists('', $collectAndPayOptions)) {
                        unset($collectAndPayOptions['']);
                      }
                    @endphp
                    <span class="label label-primary">{!! $paymentTermArr[$tilDraft->payment_terms] !!}</span>
                    @if(!empty($tilDraft->payment_terms))

                      @if($tilDraft->payment_terms == 1)
                        @if(isset($payAndCollectOptions[$tilDraft->pay_and_collect]))
                          <span class="label label-success">
                            {!! $payAndCollectOptions[$tilDraft->pay_and_collect] !!}
                          </span>
                        @endif
                      @elseif($tilDraft->payment_terms == 2)

                        @if(isset($collectAndPayOptions[$tilDraft->collect_and_pay]))
                          <span class="label label-success">
                            {!! $collectAndPayOptions[$tilDraft->collect_and_pay] !!}
                          </span>
                        @endif
                      @endif
                    @endif
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group col-md-3">
                  <label class="control-label col-md-12">Obligations:</label>
                  <div class="col-md-12">
                    @php
                      if(array_key_exists('', $obligationOptions)) {
                        unset($obligationOptions['']);
                      }
                    @endphp

                    @if(!empty($tilDraft->obligation_id) && isset($obligationOptions[$tilDraft->obligation_id]))
                      <span class="label label-primary">{!! $obligationOptions[$tilDraft->obligation_id] !!}</span>
                    @endif

                    @if(!empty($tilDraft->tilObligation))
                      @foreach($tilDraft->tilObligation as $oKey => $obligation)
                        <span class="label label-primary">{!! $obligation->obligation !!}</span>
                      @endforeach
                    @else
                      --
                    @endif
                  </div>
                </div>

                @if(auth()->user()->can('leads-management.view-cost-estimation'))
                  <div class="form-group col-md-3">
                    <label class="control-label col-md-12">Total Investments:</label>
                    <div class="col-md-12">
                      @if(!empty($tilDraft->total_investments))
                          {!! numberFormat($tilDraft->total_investments) !!}
                        @else 
                          --
                      @endif
                    </div>
                    @if(!empty($tilDraft->costEstimationDraft))
                      <div class="col-md-12">
                        @php
                          $costEstimationUrl = 'leads-management.view-cost-estimation';
                        @endphp

                        <a href="{!! route($costEstimationUrl, $tilDraft->id) !!}" id="cost-estimation" class="pull-left" target="_blank"> Cost Estimation Sheet </a>
                      </div>
                    @endif
                  </div>
                @endif

                <div class="form-group col-md-3">
                  <label class="control-label col-md-12">Penalties:</label>
                  <div class="col-md-12">
                    @if(!empty($tilDraft->penalties))
                        {!! numberFormat($tilDraft->penalties) !!}
                      @else
                        --
                    @endif
                  </div>
                </div>

                <div class="form-group col-md-3">
                  <label class="control-label col-md-12">Financial Opening Date:</label>
                  <div class="col-md-12">
                    @if(!empty($tilDraft->financial_opening_date) && $tilDraft->financial_opening_date != '0000-00-00 00:00:00')
                        {!! date('m/d/Y H:i A', strtotime($tilDraft->financial_opening_date)) !!}
                      @else 
                      --
                    @endif
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-sm-12">
                <div class="form-group col-md-3">
                  <label class="control-label col-md-12">Assigned to (SD Group):</label>
                  <div class="col-md-12">
                    @if(!empty($tilDraft->assigned_to_group))
                        {!! $tilDraft->assigned_to_group !!}
                      @else
                        --
                    @endif
                  </div>
                </div>

                <div class="form-group col-md-3">
                  <label class="control-label col-md-12">Technical Opening Date:</label>
                  <div class="col-md-12">
                    @if(!empty($tilDraft->technical_opening_date) && $tilDraft->technical_opening_date != '0000-00-00 00:00:00')
                        {!! date('m/d/Y H:i A', strtotime($tilDraft->technical_opening_date)) !!}
                      @else
                      --
                    @endif
                  </div>
                </div>

                <div class="form-group col-md-3">
                  <label class="control-label col-md-12">Technical Criteria:</label>
                  <div class="col-md-12">
                    @if(!empty($tilDraft->technical_criteria))
                        {!! $tilDraft->technical_criteria !!}
                      @else 
                      --
                    @endif
                  </div>
                </div>

                <div class="form-group col-md-3">
                  <label class="control-label col-md-12">Financial Criteria:</label>
                  <div class="col-md-12">
                    @if(!empty($tilDraft->financial_criteria))
                        {!! $tilDraft->financial_criteria !!}
                      @else 
                      --
                    @endif
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-sm-12">
                <div class="form-group col-md-3">
                  <label class="control-label col-md-12">Award Criteria:</label>
                  <div class="col-md-12">
                    @if(!empty($tilDraft->award_criteria))
                        {!! $tilDraft->award_criteria !!}
                      @else 
                      --
                    @endif
                  </div>
                </div>
              </div>
            </div>
            @php
              $statusArr = [
                1 => 'New', 2 => 'Open', 3 => 'Complete',
                4 => 'Sent for Remarks', 5 => 'Sent For Approval',
                6 => 'Rejected by Hod',  7 => 'Abandoned', 
                8 => 'Closed'
              ];
            @endphp
            @if(in_array($tilDraft->status, [5, 6]))
              <form id="view-til-form" class="form-vertical" action="{{ route('leads-management.til-approval') }}" method="POST">
                @csrf()
                  <div class="row">
                    <div class="col-sm-12">
                      <div class="form-group col-md-12">
                        <div class="col-md-12">
                          <input type="hidden" name="til_id" value="{!! $tilDraft->id !!}">
                          <label class="control-label">Comments:</label>
                          
                          <div class="three-icon-box display-inline-block">
                            <div class="info-tooltip cursor-pointer get-comments" data-til_id="{!! $tilDraft->id !!}">
                              <i class="fa fa-info-circle a-icon1"></i>
                              <span class="info-tooltiptext">Click here to see previous comments.</span>
                            </div>
                          </div>

                          <div class="">
                            <textarea name="comments" id="comments" cols="30" rows="4" class="form-control" required></textarea>
                            <input type="hidden" name="status" value="{!! $tilDraft->status !!}">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- /.box-body -->
                  <div class="box-footer">
                    <div class="col-md-12">
                      <button type="submit" class="btn btn-danger reject-btn">Reject</button>
                      <button type="submit" class="btn btn-primary m-l-10 approve-btn">Approve</button>
                    </div>
                  </div>
              </form>
              @else
                <div class="row">
                  <div class="col-sm-12">
                    <div class="form-group col-md-12">
                      <div class="col-md-12">
                        <label class="control-label">Comments:</label>

                        <div class="three-icon-box display-inline-block">          
                          <div class="info-tooltip cursor-pointer get-comments" data-til_id="{!! $tilDraft->id !!}">
                            <i class="fa fa-info-circle a-icon1"></i>
                            <span class="info-tooltiptext">Click here to see previous comments.</span>
                          </div>
                        </div>
                      </div>                                            
                    </div>
                  </div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                  <div class="col-md-12">
                    <a href="{!! route('leads-management.get-list-til') !!}" class="btn btn-default m-l-10">Back</a>
                  </div>
                </div>
            @endif
          </div>
          <!-- /.box-body -->
          <!-- Main row -->
        </div>
      </div>
    </div>
  </section>
</div>
<div class="modal fade bs-example-modal-sm" id="add_contact_details" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title" id="mySmallModalLabel">View Details:</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="table-responsive" id="til_contact_form">
              <table class="table table-bordered table-striped table-hover til_contacts_table">
                <thead>
                  <tr>
                    <th> Name </th>
                    <th> Designation </th>
                    <th> Mobile Number </th>
                    <th> Email </th>
                  </tr>
                </thead>
                <tbody class="til_contacts">
                  @if(!empty($tilDraft->tilContact) && count($tilDraft->tilContact) > 0)
                    @foreach($tilDraft->tilContact as $cKey => $contact)
                      <tr>
                        <td>{!! (!empty($contact->name))? $contact->name : '--' !!}</td>
                        <td>{!! (!empty($contact->designation))? $contact->designation : '--' !!}</td>
                        <td>{!! (!empty($contact->phone))? $contact->phone : '--' !!}</td>
                        <td>{!! (!empty($contact->email))? $contact->email : '--' !!}</td>
                      </tr>
                    @endforeach
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <div class="col-md-12">
          <button type="button" class="btn btn-success" data-dismiss="modal" aria-label="Close">Ok</button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script src="{!! asset('public/admin_assets/plugins/sweetalert/sweetalert.min.js') !!}"></script>
<script src="{{asset('public/admin_assets/plugins/validations/jquery.validate.js')}}"></script>
<script src="{{asset('public/admin_assets/plugins/validations/additional-methods.js')}}"></script>
<script src="{!! asset('public/admin_assets/plugins/jquery-toast/jquery.toast.min.js') !!}"></script>

<script type="text/javascript">
$(document).ready(function() {

  $(document).on('click', '.get-comments', function(event) {
    event.preventDefault();  event.stopPropagation();
    getComments($(this));
  });

  $(document).on('click', '.reject-btn', function(event) {
    event.preventDefault();  event.stopPropagation();

    if($('#view-til-form').valid()) {
      swal({
        closeOnClickOutside: false,
        closeOnEsc: false,
        title: "Are you sure?",
        text: "You want to reject this lead. You will not be able to edit this.",
        icon: "warning",
        buttons: {
          'cancel': {
            text: "Cancel",
            value: null,
            visible: true,
            className: "btn btn-danger",
            closeModal: true,
          },
          'confirm': {
            text: "Confirm",
            value: true,
            visible: true,
            className: "btn btn-success",
            closeModal: true
          }
        },
      }).then(function(isConfirm) {
        if (isConfirm) {
          $('input[name="status"]').val(7);
          $('#view-til-form').submit();
        }
      });
    }
  });

  $(document).on('click', '.approve-btn', function(event) {
    event.preventDefault();  event.stopPropagation();
    var statusVal = $('input[name="status"]').val();

    statusVal = (statusVal == 5)? 8 : 2;

    if($('#view-til-form').valid()) {
      $('input[name="status"]').val(statusVal);
      $('#view-til-form').submit();
    }
  });

  $('#view-til-form').validate({
    ignore: ':hidden, input[type=hidden], .select2-search__field', //[type="search"]
    errorElement: 'span',
    // debug: true,
    // the errorPlacement has to take the table layout into account
    errorPlacement: function(error, element) {
      error.appendTo(element.parent()); // element.parent().next()
    },
    rules: {
      comments: { required: true, },
    },
  });
});

function getComments(obj) {

  var til_id  = $(obj).data('til_id');
  var _token  = '{!! csrf_token() !!}';
  var objdata = {'_token': _token, 'til_id': til_id};

  $.ajax({
    url: "{!! route('leads-management.get-comments') !!}",
    type: "GET",
    data: objdata,
    dataType: 'json',
    success: function (res) {
      if(res.status == 1) {
        // swal("Done!", res.msg, "success");
        if(typeof res != 'undefined' && res != '') {
          var commentsHtml = '';
          
          if(typeof res.data != 'undefined' && res.data != '') {
              $(res.data).each(function(k, v) {
                commentsHtml += '<li>'+
                                  '<i class="fa fa-envelope bg-blue"></i>'+
                                  '<div class="timeline-item">'+
                                    '<h5 class="timeline-header">'+
                                      '<span class="leaveMessageList">'+
                                        '<strong class="text-success">Send by:</strong> '+ v.user_employee.fullname +
                                      '</span>'+
                                       '<span class="leaveMessageList pull-right">'+
                                        '<strong class="text-success">Date/Time:</strong> '+ moment(v.created_at).format('D/M/Y h:mm a') +
                                      '</span>'+
                                    '</h5>'+
                                    '<div class="timeline-body">'+ v.comments + '</div>'+
                                  '</div>'+
                                '</li>';
              });
              commentsHtml += '<li>'+
                                '<i class="fa fa-clock-o bg-gray"></i>'+
                              '</li>';

              $('.commentshtml').html(commentsHtml);
              setTimeout(function() {
                $('.comments-modal').modal('show');
              }, 300);
          } else {
            $.toast({
              heading: 'Error',
              text: 'No prevoius comments were found.',
              showHideTransition: 'plain',
              icon: 'error',
              hideAfter: 3000,
              position: 'top-right', 
              stack: 3, 
              loader: true,
              loaderBg: '#b50505',
            });
            return false;
          }
        }
      } else {
        $.toast({
          heading: 'Error',
          text: res.msg,
          showHideTransition: 'plain',
          icon: 'error',
          hideAfter: 3000,
          position: 'top-right', 
          stack: 3, 
          loader: true,
          loaderBg: '#b50505',
        });
        return false;
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      swal("Error Code:", 'Internal server error.', "error");
    }
  });
}
</script>
@endsection