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
    width: 100px;
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
i.fa.fa-minus.remtravel-row.red{
  background-color: #dd4b39;
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
        Claim Form
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
        @include('admins.validation_errors')
        
          <form accept="" action="" method="post" enctype="multipart/form-data">
          <div class="box box-primary">
            <h2 class="travel-expense-title2">{{$approval->city_from->name}} ({{$approval->city_from->state->name}}) to {{$approval->city_to->name}} ({{$approval->city_to->state->name}}),  {{formatDate($approval->date_from)}}  to  {{formatDate($approval->date_to)}}</h2>
            <h4></h4>
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

            <div class="box-body">
              <table id="" class="table table-bordered table-striped travel-table-inner" style="height:150px;">
                <thead class="table-heading-style">
                <tr>
                  <th>Date</th>
                  <th>From</th>
                  <th>To</th>
                  <th>Expense Type</th>
                  <th>Description</th>
                  
                  <th>Amount</th>
                  <th>Add/Rem</th>
                </tr>
                </thead>
                <tbody id="travelExpenseTable">
                @if(isset($claims->id)) 
                  @foreach($claims->claim_details as $cd)
                <tr>
                  <td>
                     <input autocomplete="off" type="text" class="form-control selectDate travel-table-input-style travel-only-inputss travel-input2" id="travelDate" name="expense_date[]" placeholder="MM/DD/YYYY" value="{{date("m/d/Y",strtotime($cd->expense_date))}}" onkeypress="return false;" required="">
                  </td>
                  <td>
                    <input type="text" name="from_location[]" class="form-control travel-input2 travel-table-input-style travel-only-inputss" required="" value="{{$cd->from_location}}">
                  </td>
                  <td>
                    <input type="text" name="to_location[]" class="form-control travel-input2 travel-table-input-style travel-only-inputss" required="" value="{{$cd->to_location}}">
                  </td>
                  <td>
                    <select class="form-control travel-input6 travel-table-input-style travel-only-inputss" name="expense_type[]" required="">
                      <option value="">Select</option>
                      @foreach($conveyances as $conveyance)
                      <option @if($cd->expense_type==$conveyance->id) selected @endif value="{{$conveyance->id}}">{{$conveyance->name}}</option>
                      @endforeach
                    </select>
                  </td>
                  <td>
                    <input type="text" name="description[]" class="form-control travel-input3 travel-table-input-style travel-only-inputss" required="" value="{{$cd->description}}">
                  </td>
                  <td>
                    <input type="number" name="amount[]" required=""  max="20000" min="1" class="form-control travel-input5 travel-table-input-style travel-only-inputss" placeholder="330" value="{{$cd->amount}}">
                  </td>
                  <td class="text-center">
                    @if($loop->iteration==1)
                    <a href="javascript:void(0);" class="addtravel"><i class="fa fa-plus addtravel-row @if($cd->status=='back') red @endif"></i></a>
                    @else
                    <a href="javascript:void(0);" onclick="removeTr($(this))" class="remtravel"><i class="fa fa-minus remtravel-row @if($cd->status=='back') red @endif"></i></a>
                    @endif
                  </td>
                </tr>
                 @endforeach
                @else
                 <tr>
                  <td>
                     <input autocomplete="off" type="text" class="form-control selectDate travel-table-input-style travel-only-inputss travel-input2" id="travelDate" name="expense_date[]" placeholder="MM/DD/YYYY" value="" onkeypress="return false;" required="">
                  </td>
                  <td>
                    <input type="text" name="from_location[]" class="form-control travel-input2 travel-table-input-style travel-only-inputss" required="" value="">
                  </td>
                  <td>
                    <input type="text" name="to_location[]" class="form-control travel-input2 travel-table-input-style travel-only-inputss" required="">
                  </td>
                  <td>
                    <select class="form-control travel-input6 travel-table-input-style travel-only-inputss" name="expense_type[]" required="">
                      <option value="">Select</option>
                      @foreach($conveyances as $conveyance)
                      <option value="{{$conveyance->id}}">{{$conveyance->name}}</option>
                      @endforeach
                    </select>
                  </td>
                  <td>
                    <input type="text" name="description[]" class="form-control travel-input3 travel-table-input-style travel-only-inputss" required="">
                  </td>
                  <td>
                    <input type="number" name="amount[]" required=""  max="20000" min="1" class="form-control travel-input5 travel-table-input-style travel-only-inputss" placeholder="330">
                  </td>
                  <td class="text-center">
                    <a href="javascript:void(0);" class="addtravel"><i class="fa fa-plus addtravel-row"></i></a>
                  </td>
                </tr>
                @endif
                </tbody>
                <tfoot class="table-heading-style">
                <tr>
                  <th>Date</th>
                  <th>From</th>
                  <th>To</th>
                  <th>Expense Type</th>
                  <th>Description</th>
                  <th>Amount</th>
                  <th>Add/Rem</th>
                </tr>

                </tfoot>
              </table>

              <br>




              <h2 class="travel-expense-title2" style="padding-left: 0px;">Upload Supporting Documents</h2>
              <table id="travelAttachmentTable" class="table table-bordered table-striped travel-table-inner" style="height:150px;">
                <thead class="table-heading-style">
                <tr>
                  <th>Type of Attachment</th>
                  <th>Name</th>
                  <th>Choose Attachment</th>
                  <th>Add/Remove</th>
                </tr>
                </thead>
                <tbody> 
                @if(isset($claims->id)) 
                  @foreach($claims->claim_attachments as $cd)
                <tr>
                  <td>
                      <select required="" class="form-control travel-table-input-style travel-only-inputss" name="attachment_type[]">
                        <option>Select</option>
                        @foreach($conveyances as $conveyance)
                        <option @if($cd->attachment_type==$conveyance->id) selected @endif  value="{{$conveyance->id}}">{{$conveyance->name}}</option>
                        @endforeach
                      </select>
                  </td>
                  <td>
                    <input required="" type="text" name="name[]" class="form-control travel-table-input-style travel-only-inputss" value="{{$cd->name}}">
                    <!-- form-control travel-table-input-style travel-only-inputss -->
                  </td>
                  <td>
                    <input required="" type="file" name="attachment[]" class="chooseAttachment-travel pull-left">
                    <a href="{{url('public/uploads/travel-attachments/' . $cd->attachment)}}" target="_blank" class="btn btn-xs btn-success pull-right">
                      Attachment
                    </a>
                  </td>
                  <td class="text-center">
                    @if($loop->iteration==1)
                     <a href="javascript:void(0);" class="add-attachment"><i class="fa fa-plus addtravel-row @if($cd->status=='back') red @endif"></i></a>
                    @else
                     <a href="javascript:void(0);" onclick="removeTr($(this))" class="remove-attachment"><i class="fa fa-minus remtravel-row @if($cd->status=='back') red @endif"></i></a>
                    @endif
                  </td>
                </tr>
                  @endforeach
                @else
                <tr>
                  <td>
                      <select required="" class="form-control travel-table-input-style travel-only-inputss" name="attachment_type[]">
                        <option>Select</option>
                        @foreach($conveyances as $conveyance)
                        <option value="{{$conveyance->id}}">{{$conveyance->name}}</option>
                        @endforeach
                      </select>
                  </td>
                  <td>
                    <input required="" type="text" name="name[]" class="form-control travel-table-input-style travel-only-inputss">
                    <!-- form-control travel-table-input-style travel-only-inputss -->
                  </td>
                  <td>
                    <input required="" type="file" name="attachment[]" class="chooseAttachment-travel">
                  </td>
                  <td class="text-center">
                     <a href="javascript:void(0);" class="add-attachment"><i class="fa fa-plus addtravel-row"></i></a>
                  </td>
                </tr>
                @endif
                </tbody>
                <tfoot class="table-heading-style">
                <tr>
                  <th>Type of Attachment</th>
                  <th>Name</th>
                  <th>Choose Attachment</th>
                  <th>Add/Remove</th>
                </tr>
                </tfoot>
              </table>
              <div class="row">
                <div class="col-md-12 text-center">
                  @if(!isset($claims->id)) 
                  <input type="submit" name="submit_btn" class="btn btn-success" value="Submit">
                  @elseif($claims->status=='back')
                  <input type="submit" name="update_btn" class="btn btn-success" value="Update & Submit">
                  @else
                  <label class="label label-info">{{$claims->status}}</label>
                  @endif
                </div>
              </div>
            </div>
            <!-- /.box-body -->
          </div>
          {{ csrf_field() }}
          </form>
          <!-- /.box -->
        </div>
      </div>
      <!-- /.row -->
      <!-- Main row -->

    </section>
    <!-- /.content --> 
  </div>
<div class="hide">
  <table id="tobecloned">
    <tr class="">
        <td>
           <input autocomplete="off" type="text" class="form-control selectDate travel-table-input-style travel-only-inputss travel-input2" id="" name="expense_date[]" placeholder="MM/DD/YYYY"  onkeypress="return false;" required="">
        </td>
        <td>
          <input type="text" name="from_location[]" class="form-control travel-input2 travel-table-input-style travel-only-inputss" required="">
          <!-- form-control travel-table-input-style travel-only-inputss -->
        </td>
        <td>
          <input type="text" name="to_location[]" class="form-control travel-input2 travel-table-input-style travel-only-inputss" required="">
        </td>
        <td>
          <select class="form-control travel-input6 travel-table-input-style travel-only-inputss" name="expense_type[]">
            <option>Select</option>
            @foreach($conveyances as $conveyance)
            <option value="{{$conveyance->id}}">{{$conveyance->name}}</option>
            @endforeach
          </select>
        </td>
        <td>
          <input type="text" name="description[]" class="form-control travel-input3 travel-table-input-style travel-only-inputss">
        </td>
        
        <td>
          <input type="number" name="amount[]" required=""  max="20000" min="1" class="form-control travel-input5 travel-table-input-style travel-only-inputss" placeholder="330">
        </td>
        <td class="text-center">
          <a href="javascript:void(0);" onclick="removeTr($(this))" class="remtravel"><i class="fa fa-minus remtravel-row"></i></a>
        </td>
      </tr>
  </table>
  <table id="attachment_table">
    <tr>
      <td>
          <select required="" class="form-control travel-table-input-style travel-only-inputss" name="attachment_type[]">
            <option>Select</option>
            @foreach($conveyances as $conveyance)
            <option value="{{$conveyance->id}}">{{$conveyance->name}}</option>
            @endforeach
          </select>
      </td>
      <td>
        <input required="" type="text" name="name[]" class="form-control travel-table-input-style travel-only-inputss">
        <!-- form-control travel-table-input-style travel-only-inputss -->
      </td>
      <td>
        <input required="" type="file" name="attachment[]" class="chooseAttachment-travel">
      </td>
      <td class="text-center">
         <a href="javascript:void(0);" onclick="removeTr($(this))" class="remove-attachment"><i class="fa fa-minus remtravel-row"></i></a>
      </td>
    </tr>
  </table>
</div>
<script type="text/javascript">
//Date picker
    $("#travelDate").datepicker({
      //startDate: minimumDate,
      endDate: '<?php echo date('m/d/Y'); ?>',/**/
      autoclose: true,
      orientation: "bottom"
    });
</script>

<script>
$('body').on('focus',".selectDate", function(){
    $(this).datepicker({
      //startDate: minimumDate,
      endDate: '<?php echo date('m/d/Y'); ?>',/**/
      autoclose: true,
      orientation: "bottom"
    });
});
$(document).ready(function(){
    $(".addtravel").click(function(){
        date_id=$(".selectDate").length+1;
        $("#travelExpenseTable").append($("#tobecloned tr").clone());
        
        
    });
});
function removeTr(obj){
  obj.parent().parent().remove();
}
</script>

<script>
$(document).ready(function(){
    $(".add-attachment").click(function(){
      $("#travelAttachmentTable").append($("#attachment_table tr").clone());
    });
});
</script>

  
  @endsection