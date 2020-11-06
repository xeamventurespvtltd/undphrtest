@extends('admins.layouts.app')

@section('content')
<link rel="stylesheet" href="{{asset('public/admin_assets/plugins/timepicker/bootstrap-timepicker.min.css')}}">

<style type="text/css">
  /*Css changes on 12 August, 2019*/
.travel-input2 {
    width: 96px;
}
.travel-input3 {
    width: 250px;
}
.travel-input4 {
    width: 45px;
}
.travel-input6 {
    width: 100px;
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
.select2-container .select2-selection--single
{
  height: 30px;
  border: 1px solid #d2d6de;
  box-shadow: 0px 1px 2px lightgrey;
  font-size: 12px;
  padding: 5px 0px;
}
h2.important_note-heading {
    font-size: 20px;
    margin-left: 25px;
}
.important_note_sec {
    padding-bottom: 20px;
}
</style>
<link rel="stylesheet" href="{{asset('public/admin_assets/plugins/dataTables/jquery.dataTables.min.css')}}">

<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Expense Claim Form
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
              <form id="localClaimForm">
                  <div class="row select-detail-below">
                    <div class="col-md-3 attendance-column1">   
                        <div class="form-group">
                            <label for="expense_form_name">Name<sup class="ast">*</sup></label>
                            <input type="text" name="expense_form_name" id="" class="form-control input-sm basic-detail-input-style" placeholder="Enter Name">
                        </div>
                    </div>
                    <div class="col-md-3 attendance-column2">
                        <div class="form-group">
                            <label for="expense_form_location">Location<sup class="ast">*</sup></label>
                            <select class="form-control select2 input-sm basic-detail-input-style" id="" name="expense_form_location">
                                <option value="">Please select Location</option>
                                <option value="Location 1">Location 1</option>
                                <option value="Location 2">Location 2</option>
                                <option value="Location 3">Location 3</option>
                                <option value="Location 4">Location 4</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 attendance-column3">
                        <div class="form-group">
                            <label for="expense_form_project">Project<sup class="ast">*</sup></label>
                            <select class="form-control select2 input-sm basic-detail-input-style" id="" name="expense_form_project">
                                <option value="">Please select Project</option>
                                <option value="Project 1">Project 1</option>
                                <option value="Project 2">Project 2</option>
                                <option value="Project 3">Project 3</option>
                                <option value="Project 4">Project 4</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 attendance-column4">
                        <div class="form-group">
                            <label for="expense_form_designation">Designation<sup class="ast">*</sup></label>
                            <select class="form-control select2 input-sm basic-detail-input-style" name="expense_form_designation">
                                <option value="">Please select Designation</option>
                                <option value="Designation 1">Designation 1</option>
                                <option value="Designation 2">Designation 2</option>
                                <option value="Designation 3">Designation 3</option>
                                <option value="Designation 4">Designation 4</option>
                            </select>
                        </div>
                    </div>
                  </div>

                  <div class="row select-detail-below2">
                    <div class="col-md-3 attendance-column1">
                        <div class="form-group">
                            <label for="expense_form_period">Period<sup class="ast">*</sup></label>
                            <select class="form-control input-sm basic-detail-input-style" name="expense_form_period">
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 attendance-column2">
                        <div class="form-group basic-detail-label">
                            <label for="expense_form_account">Account No.<sup class="ast">*</sup></label>
                            <input type="text" class="form-control input-sm basic-detail-input-style" name="expense_form_account" id="" placeholder="Please enter Account Number">
                        </div>
                    </div>

                    <div class="col-md-3 attendance-column3">
                        <div class="form-group basic-detail-label">
                            <label for="expense_form_ifsc_code">IFSC Code<sup class="ast">*</sup></label>
                            <input type="text" name="expense_form_ifsc_code" id="" class="form-control input-sm basic-detail-input-style" placeholder="Enter IFSC Code">
                        </div>
                    </div>
                  </div>


<table id="localClaimTable" class="table table-bordered table-striped travel-table-inner" style="height:150px;">
    <thead class="table-heading-style">
        <tr>
            <th>Date</th>
            <th>From</th>
            <th>To</th>
            <th>Time of Reach</th>
            <th>Reason of Visit</th>
            <th>Mode of Transport</th>
            <th>KM</th>
            <th>Rate/ Fare</th>
            <th>Amount</th>
            <th>Add/Rem</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <input type="text" class="form-control selectDate datepicker travel-table-input-style travel-only-inputss travel-input2" id="expense_table_date" name="expense_table_date" placeholder="MM/DD/YYYY" value="">
            </td>
            <td>
                <input type="text" name="expense_from_location" class="form-control travel-input2 travel-table-input-style travel-only-inputss" required="" value="">
            </td>
            <td>
                <input type="text" name="expense_to_location" class="form-control travel-input2 travel-table-input-style travel-only-inputss" required="" value="">
            </td>
            <td>
                <input type="text" class="form-control timepicker travel-table-input-style travel-only-inputss travel-input2" id="time_reach" name="time_reach" data-placeholder="11:15 PM" value="" readonly>
            </td>
            <td>
                <input type="text" name="expense_visit" class="form-control travel-input3 travel-table-input-style travel-only-inputss" required="" value="">
            </td>
            <td>
                <select class="form-control select2 travel-input6 travel-table-input-style travel-only-inputss" name="expense_transport" required="">
                    <option value="">Select Mode</option>
                    <option value="Mode 1">Mode 1</option>
                    <option value="Mode 2">Mode 2</option>
                    <option value="Mode 3">Mode 3</option>
                    <option value="Mode 4">Mode 4</option>
                </select>
            </td>
            <td>
                <input type="text" name="expense_km" class="form-control travel-input4 travel-table-input-style travel-only-inputss" required="" value="">
            </td>
            <td>
                <input type="text" name="expense_rates" class="form-control travel-input5 travel-table-input-style travel-only-inputss" required="" value="">
            </td>
            <td>
                <input type="number" name="expense_amount" required="" max="20000" min="1" class="form-control travel-input5 travel-table-input-style travel-only-inputss" placeholder="330" value="">
            </td>
            <td class="text-center">
                <a href="javascript:void(0);" class="addNewRows"><i class="fa fa-plus addtravel-row"></i></a>
            </td>
        </tr>
    </tbody>
    <tfoot class="table-heading-style">
        <tr>
            <th>Date</th>
            <th>From</th>
            <th>To</th>
            <th>Time of Reach</th>
            <th>Reason of Visit</th>
            <th>Mode of Transport</th>
            <th>KM</th>
            <th>Rate/ Fare</th>
            <th>Amount</th>
            <th>Add/Rem</th>
        </tr>
    </tfoot>
</table>

    <div class="submit-btn-box">
    <button type="submit" class="btn btn-primary">Submit</button>
  </div>
</form>

<div class="important_note_sec">
    <h2 class="important_note-heading">Important Note:</h2>
    <ul>
        <li>Expenses shall be claimed as per company's policy.</li>
        <li>For every project different bills should be raised.</li>
        <li>All expenses should supported by original bills/ claim documents.</li>
        <li>All bills should be in name of Xeam Ventures Pvt Ltd. Except Bus/ Train/ Air tickets.</li>
        <li>Local conveyance bills should be as per Companies policy for two/ four wheelers.</li>
        <li>Bill should be duly approved by departmental head.</li>
    </ul>
</div>


    </div>
  </div>
</div>
<!-- /.row -->
<!-- Main row -->

</section>
<!-- /.content --> 
</div>

<!-- bootstrap time picker -->
<script src="{{asset('public/admin_assets/plugins/timepicker/bootstrap-timepicker.min.js')}}"></script>
<script src="{{asset('public/admin_assets/plugins/validations/jquery.validate.js')}}"></script>
<script src="{{asset('public/admin_assets/plugins/validations/additional-methods.js')}}"></script>    

<script type="text/javascript">
    $(document).ready(function(){
        $("#localClaimForm").validate({
            rules : {
                "expense_form_name" : {
                    required : true
                },
                "expense_form_location" : {
                    required : true
                },
                "expense_form_project" : {
                    required : true
                },
                "expense_form_designation" : {
                    required : true
                },
                "expense_form_period" : {
                    required : true
                },
                "expense_form_account" : {
                    required : true
                },
                "expense_form_ifsc_code" : {
                    required : true
                },
                "expense_table_date" : {
                    required : true,
                },
                "expense_from_location" : {
                    required : true,
                },
                "expense_to_location" : {
                    required : true,
                },
                "time_reach" : {
                    required : true,
                },          
                "expense_visit" : {
                    required : true,
                },
                "expense_transport" : {
                    required : true,
                },
                "expense_km" : {
                    required : true,
                },
                "expense_rates" : {
                    required : true,
                },
                "expense_amount" : {
                    required : true,
                }
            },
            errorPlacement: function(error, element) {
            if (element.hasClass('select2')) {
              error.insertAfter(element.next('span.select2'));
            }
            else {
              error.insertAfter(element);
            }
          },
            messages : {
                "expense_form_name" : {
                    required : "Please enter your Name"
                },
                "expense_form_location" : {
                    required : "Please choose location"
                },
                "expense_form_project" : {
                    required : "Please choose Project"
                },
                "expense_form_designation" : {
                    required : "Please select Designation"
                },
                "expense_form_period" : {
                    required : "Please select period"
                },
                "expense_form_account" : {
                    required : "Please enter account number"
                },
                "expense_form_ifsc_code" : {
                    required : "Please enter IFSC Code"
                },
                "expense_table_date" : {
                    required : "Please select Date"
                },
                "expense_from_location" : {
                    required : "Please select location"
                },
                "expense_to_location" : {
                    required : "Please select location"
                },
                "time_reach" : {
                    required : "Please select time"
                },
                "expense_visit" : {
                    required : "Please enter reason."
                },
                "expense_transport" : {
                    required : "Please select mode"
                },
                "expense_km" : {
                    required : "Please enter km"
                },
                "expense_rates" : {
                    required : "Please enter rates"
                },
                "expense_amount" : {
                    required : "Please enter amount"
                }
            }
        });
    });
</script>

<script type="text/javascript">
jQuery(document).ready(function(){
    //Date picker
    $(".datepicker").datepicker({
      //startDate: minimumDate,
      endDate: maximumDate,
      autoclose: true,
      orientation: "bottom"
    });

    $("#time_reach").timepicker({
      showInputs: false
    });
});


</script>

<script>
$(document).ready(function(){
  $(".addNewRows").click(function(){
    date_id = $(".selectDate").length+1;
    time_id = $(".timepicker").length+1;
    $("#localClaimTable").append('<tr><td> <input type="text" class="form-control selectDate datepicker travel-table-input-style travel-only-inputss travel-input2" id="expense_table_date'+date_id+'" name="expense_table_date" placeholder="MM/DD/YYYY" value=""></td><td> <input type="text" name="expense_from_location" class="form-control travel-input2 travel-table-input-style travel-only-inputss" required="" value=""></td><td> <input type="text" name="expense_to_location" class="form-control travel-input2 travel-table-input-style travel-only-inputss" required="" value=""></td><td> <input type="text" class="form-control timepicker travel-table-input-style travel-only-inputss travel-input2" id="time_reach'+time_id+'" name="time_reach" data-placeholder="11:15 PM" value="" readonly></td><td> <input type="text" name="expense_visit" class="form-control travel-input3 travel-table-input-style travel-only-inputss" required="" value=""></td><td><select class="form-control select2 travel-input6 travel-table-input-style travel-only-inputss" name="expense_transport" required=""><option value="">Select Mode</option><option value="Mode 1">Mode 1</option><option value="Mode 2">Mode 2</option><option value="Mode 3">Mode 3</option><option value="Mode 4">Mode 4</option></select></td><td> <input type="text" name="expense_km" class="form-control travel-input4 travel-table-input-style travel-only-inputss" required="" value=""></td><td> <input type="text" name="expense_rates" class="form-control travel-input5 travel-table-input-style travel-only-inputss" required="" value=""></td><td> <input type="number" name="expense_amount" required="" max="20000" min="1" class="form-control travel-input5 travel-table-input-style travel-only-inputss" placeholder="330" value=""></td><td class="text-center"> <a href="javascript:void(0);" class="subtractNewRows"><i class="fa fa-minus remtravel-row"></i></a></td></tr>');
        $("#expense_table_date"+date_id).datepicker({
          autoclose: true,
          orientation: "bottom"
        });
        $("#time_reach"+time_id).timepicker({
          showInputs: false
        });

  });
    $("#localClaimTable").on('click','.subtractNewRows',function(){
        $(this).parent().parent().remove();
    });
});
</script>
@endsection