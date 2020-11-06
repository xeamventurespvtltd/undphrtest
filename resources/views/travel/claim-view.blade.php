@extends('admins.layouts.app')

@section('content')
<style type="text/css">
  /*Css changes on 12 August, 2019*/
.travel-input2 {
    width: 105px;    
}
.travel-input3 {
    width: 300px;
}
.travel-input4 {
    width: 45px;
}
.travel-input5 {
    width: 60px;
}
.travel-input6 {
    width: 115px;
}
h2.travel-expense-title2 {
    font-size: 20px;
    padding: 0 10px;
}
.travel-box-border {
    border: 1px solid lightgrey;
    margin: 10px;
    padding: 10px 0;
    border-radius: 8px;
}
h3.travel-expense-title3 {
    font-size: 15px;
    font-weight: 600;
    margin: 0px 0 5px 0;
    text-align: center;
    color: white;
    background-color: #00728e;
    padding: 5px 0;
}
.remove-travel-left6 {
    padding-left: 0px;
}
.travel-table-input-style {
    border-radius: 4px;
    box-shadow: 0px 1px 2px lightgrey;
}
.travel-only-inputss {
    font-size: 12px;
    padding: 5px 5px !important;
    height: 30px;
}
.travel-table-inner>tbody>tr>td {
    padding: 5px !important;
}
i.fa.fa-plus.addtravel-row {
    background-color: #00728e;
    color: white;
    padding: 7px 9px;
    border-radius: 50%;
}
i.fa.fa-minus.remtravel-row {
    background-color: #00728e;
    color: white;
    padding: 7px 9px;
    border-radius: 50%;
}
.add-remark-textarea {
    width: 100%;
    min-height: 100px;
    padding: 5px;
}
input.chooseAttachment-travel {
    position: relative;
    top: 4px;
}
.footer-travel {
    margin-left: 0px;
}
input.travel-check1 {
    position: relative;
    top: 5px;
}
.attendance-tds {
    height: 110px;
}
.travel-bottom-btns {
    display: flex;
    justify-content: center;
    margin: 20px 0;
}
a.travel-btn-style {
    padding: 10px 10px;
    margin: 0 5px;
    border-radius: 7px;
    border: 1px solid #00728e;
    color: #00728e;
    transition: all ease 0.2s;
}
a.travel-btn-style:hover {
    background-color: #00728e;
    color: white;
}
.utr-outerbox {
    padding: 20px;
    border: 1px solid lightgrey;
    border-top: 3px solid #00728e;
}
input.utrNumber {
    width: 100%;
}
label.utr-label {
    margin-bottom: 0px;
}
</style>
<link rel="stylesheet" href="{{asset('public/admin_assets/plugins/dataTables/jquery.dataTables.min.css')}}">

<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Travel Module
        <!-- <small>Control panel</small> -->
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{ url('employees/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <div class="col-md-12">
          <div class="box box-primary">
          
          @include('admins.validation_errors')

            <h2 class="travel-expense-title2">{{$approval->city_from->name}} ({{$approval->city_from->state->name}}) to {{$approval->city_to->name}} ({{$approval->city_to->state->name}}),  {{formatDate($approval->date_from)}}  to  {{formatDate($approval->date_to)}}</h2>
            <div class="row travel-box-border">
              <div class="col-md-8">
                <h3 class="travel-expense-title3">Basic Details</h3>
                <div class="col-md-6 remove-travel-left6">
                  <div class="row">
                    <div class="col-md-5 col-sm-5 col-xs-5">
                      <label for="">Name</label>
                    </div>
                    <div class="col-md-7 col-sm-7 col-xs-7">
                      <label>{{$approval->user->employee->salutation}}{{$approval->user->employee->fullname}}</label>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-5 col-sm-5 col-xs-5">
                      <label for="">Employee Code</label>
                    </div>
                    <div class="col-md-7 col-sm-7 col-xs-7">
                      <label>{{$approval->user->employee_code}}</label>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-5 col-sm-5 col-xs-5">
                      <label for="">Designation</label>
                    </div>
                    <div class="col-md-7 col-sm-7 col-xs-7">
                      <label>{{$approval->user->designation[0]->name}}</label>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-5 col-sm-5 col-xs-5">
                      <label for="">Bank Name</label>
                    </div>
                    <div class="col-md-7 col-sm-7 col-xs-7">
                      <label>{{$approval->user->employeeAccount->bank->name}}</label>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-5 col-sm-5 col-xs-5">
                      <label for="">Account No</label>
                    </div>
                    <div class="col-md-7 col-sm-7 col-xs-7">
                      <label>{{$approval->user->employeeAccount->bank_account_number}}</label>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-5 col-sm-5 col-xs-5">
                      <label for="">IFSC CODE</label>
                    </div>
                    <div class="col-md-7 col-sm-7 col-xs-7">
                      <label>{{$approval->user->employeeAccount->ifsc_code}}</label>
                    </div>
                  </div>
                </div>

                <div class="col-md-6 remove-travel-left6">
                  <div class="row">
                    <div class="col-md-5 col-sm-5 col-xs-5">
                      <label for="">Project</label>
                    </div>
                    <div class="col-md-7 col-sm-7 col-xs-7">
                      <label>{{$approval->project[0]->name}}</label>
                    </div>
                  </div>
                  @if($approval->approved_by_user)
                  <div class="row">
                    <div class="col-md-5 col-sm-5 col-xs-5">
                      <label for="">Approved By</label>
                    </div>
                    <div class="col-md-7 col-sm-7 col-xs-7">
                      <label>{{$approval->approved_by_user->employee->salutation}}{{$approval->approved_by_user->employee->fullname}}</label>
                    </div>
                  </div>
                  @endif
                  @if(isset($claims->id))
                  <div class="row">
                    <div class="col-md-5 col-sm-5 col-xs-5">
                      <label for="">Payment Status</label>
                    </div>
                    <div class="col-md-7 col-sm-7 col-xs-7">
                      <label>
                        {{ucfirst($claims->status)}}
                      </label>
                    </div>
                  </div>
                  @endif
                  @if(isset($claims->id))
                  <div class="row">
                    <div class="col-md-5 col-sm-5 col-xs-5">
                      <label for="">UTR No.</label>
                    </div>
                    <div class="col-md-7 col-sm-7 col-xs-7">
                      <label>{{$claims->utr}}</label>
                    </div>
                  </div>
                  @endif
                </div>
              </div>
              <div class="col-md-4">
                  <h3 class="travel-expense-title3">Amount Details</h3>
                  <div class="row">
                    <div class="col-md-6 col-sm-6 col-xs-6">
                      <label for="">Eligible Conveyances  </label>
                    </div>
                    <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                      <label>{{$eligible_conveyance}}</label>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-8 col-sm-8 col-xs-8">
                      <label for="">Eligible (Stay + DA)/night </label>
                    </div>
                    <div class="col-md-4 col-sm-4 col-xs-4 text-right">
                      <label>{{moneyFormat($eligible_amount_stay)}}</label>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-5 col-sm-5 col-xs-5">
                      <label for="">Approved Amount</label>
                    </div>
                    <div class="col-md-7 col-sm-7 col-xs-7 text-right">
                      <label>{{moneyFormat($amount_approved)}}</label>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-5 col-sm-5 col-xs-5">
                      <label for="">Imprest taken</label>
                    </div>
                    <div class="col-md-7 col-sm-7 col-xs-7 text-right">
                      <label>@if(isset($approval->imprest)){{moneyFormat($approval->imprest->amount)}}@else -- @endif

</label>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-5 col-sm-5 col-xs-5">
                      <label for="">Balance Amount</label>
                    </div>
                    <div class="col-md-7 col-sm-7 col-xs-7 text-right">
                      <label>
                      @if(isset($approval->imprest))
                      {{moneyFormat($amount_approved-$approval->imprest->amount)}}
                      @else
                      {{moneyFormat($amount_approved)}}
                      @endif
                      </label>
                    </div>
                  </div>
              </div>
              </div>
            <!-- <div class="box-header">
            </div> -->
            <!-- /.box-header -->
            <form action="" method="post">
            <div class="box-body">
              <table id="travelExpenseTable" class="table table-bordered table-striped travel-table-inner" >
                <thead class="table-heading-style">
                <tr>
                  <th>S No.</th>
                  <th>Date</th>
                  <th>From</th>
                  <th>To</th>
                  <th>Expense Type</th>
                  <th>Description</th>
                  <th class="text-right">Amount</th>
                  @can('verify-travel-claim')
                  <th class="text-center">Send Back</th>
                  @endcan
                </tr>
                </thead>
                <tbody> 
                @foreach($claims->claim_details as $cd)
                <tr>
                  <td>{{$loop->iteration}}.</td>
                  <td>{{formatDate($cd->expense_date)}}</td>
                  <td>{{$cd->from_location}}</td>
                  <td>{{$cd->to_location}}</td>
                  <td>@if($cd->expense_types){{$cd->expense_types->name}}@endif</td>
                  <td>{{$cd->description}}</td>
                  <td class="text-right">{{moneyFormat($cd->amount)}}</td>
                  @can('verify-travel-claim')
                  <td class="text-center">
                    @if($cd->status=='new')
                    <input type="checkbox" name="claim_details[]" class="claim_check" value="{{$cd->id}}">
                    @else
                    NA
                    @endif
                  </td>
                  @endcan
                </tr>
                @endforeach
                </tbody>
                <tfoot class="table-heading-style">
                <tr>
                  <th>S No.</th>
                  <th>Date</th>
                  <th>From</th>
                  <th>To</th>
                  <th>Expense Type</th>
                  <th>Description</th>
                  <th class="text-right">Amount</th>
                  @can('verify-travel-claim')
                  <th class="text-center">Send Back</th>
                  @endcan
                </tr>
                </tfoot>
              </table>

              <br>

              <h2 class="travel-expense-title2" style="padding-left: 0px;">Supporting Documents</h2>
              <table id="travelAttachmentTable" class="table table-bordered table-striped travel-table-inner" style="height:150px;">
                <thead class="table-heading-style">
                <tr>
                  <th>S No.</th>
                  <th>Type of Attachment</th>
                  <th>Name</th>
                  <th>Link</th>
                  @can('verify-travel-claim')
                  <th class="text-center">Send Back</th>
                  @endcan
                </tr>
                </thead>
                <tbody> 
                @foreach($claims->claim_attachments as $cd)
                <tr>
                  <td>{{$loop->iteration}}.</td>
                  
                  <td>@if($cd->attachment_types){{$cd->attachment_types->name}}@endif</td>
                  <td>{{$cd->name}}</td>
                  <td>
                    <a href="{{url('public/uploads/travel-attachments/' . $cd->attachment)}}" target="_blank" class="btn btn-xs btn-success">
                      Attachment
                    </a>
                  </td>
                  @can('verify-travel-claim')
                  <td class="text-center">
                    @if($cd->status=='new')
                    <input type="checkbox" name="claim_attachments[]" class="claim_check" value="{{$cd->id}}">
                    @else
                    NA
                    @endif
                  </td>
                  @endcan
                </tr>
                @endforeach
                </tbody>
                <tfoot class="table-heading-style">
                <tr>
                  <th>S No.</th>
                  <th>Type of Attachment</th>
                  <th>Name</th>
                  <th>Link</th>
                  @can('verify-travel-claim')
                  <th class="text-center">Send Back</th>
                  @endcan
                </tr>
                </tfoot>
              </table>
              @if($claims->status=='new' && auth()->user()->can('verify-travel-claim'))
              <div class="row">
                <div class="col-md-12 text-center">
                  <input type="submit" class="btn btn-danger" name="send_it_back" value="Send Back" onclick="return ValiadateThisForm();" />
                  <a href="javascript:void(0)" class="btn btn-success" id="approveAndPay">Approve & Pay</a>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4 col-md-offset-4">
                  <div class="utr-outerbox" style="display: none;">
                    <div class="row">
                      <div class="col-md-4 utr-left-col">
                        <label class="utr-label" for="utrNumber">UTR No. :</label>
                      </div>
                      <div class="col-md-8 utr-right-col">
                        <input type="text" name="utr" id="utr" class="utrNumber form-control travel-input2 travel-table-input-style travel-only-inputss">
                      </div>
                    </div>
                    <div class="utr-submit-box">
                      <button type="submit" class="btn btn-success" value="Pay" name="pay_approve">Submit</button>
                    </div>
                    {{ csrf_field() }}
                  </div>
                </div>
              </div>
              @elseif($claims->status=='paid')
              <div class="row">
                <div class="col-md-4 col-md-offset-4">
                  <div class="utr-outerbox">
                    <div class="row">
                      <div class="col-md-4 utr-left-col">
                        <label class="utr-label" for="utrNumber">UTR No. :</label>
                      </div>
                      <div class="col-md-8 utr-right-col">{{$claims->utr}}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              @endif
            </div>
            </form>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
      </div>
      <!-- /.row -->
      <!-- Main row -->

    </section>
  </div>
    <!-- /.content --> 
  <!-- /.content-wrapper -->

  <script src="{{asset('public/admin_assets/plugins/dataTables/jquery.dataTables.min.js')}}"></script>
  <script src="{{asset('public/admin_assets/plugins/validations/jquery.validate.js')}}"></script>
  <script src="{{asset('public/admin_assets/plugins/validations/additional-methods.js')}}"></script>

<script type="text/javascript">
  $("#approveAndPay").on('click',function(){  
    $(".utr-outerbox").show();
    $("#utr").prop('required', true);
  });
  function ValiadateThisForm(){
    $("#utr").prop('required', false);
    if($(".claim_check:checked").length==0){
      alert("Please check any claims or attachments before sending it back.")
      return false;
    }
  }
</script>
  @endsection