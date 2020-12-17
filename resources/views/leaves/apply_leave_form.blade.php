@extends('admins.layouts.app')

@section('content')

    <!-- Content Wrapper. Contains page content -->
    <!-- Bootstrap time Picker -->
    <link rel="stylesheet" href="{{asset('public/admin_assets/plugins/timepicker/bootstrap-timepicker.min.css')}}">

    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1 class="text-center">
                Apply Leave
            </h1>

            <!-- <small>Control panel</small> -->
            <div class="row leave-type1">
            </div>

            <div class="row leave-type1">
                <div class="col-sm-3 col-xs-3 leaves1">
                    <div class="panel panel-danger leave-type-sectn">
                        <div class="panel-heading leave-heading">Paternity Leave:
                            {{--              <!--<span class="label label-info">{{$leaveDetail->balance_paternity_leave}}</span>-->--}}
                            <span class="label label-warning">As per Government of India</span>
                        </div>
                        <!-- <div class="panel-body leave-content"> -->

                        <!-- </div> -->
                    </div>
                </div>

                <div class="col-sm-3 col-xs-3 leaves3">
                    <div class="panel panel-warning leave-type-sectn">
                        <div class="panel-heading leave-heading">Maternity Leave:
                            {{--              <!--<span class="label label-warning">{{$leaveDetail->balance_maternity_leave}}</span>-->--}}
                            <span class="label label-warning">As per Government of India</span>

                        </div>
                        <!-- <div class="panel-body leave-content">

                        </div> -->
                    </div>
                </div>

                <div class="col-sm-3 col-xs-3 leaves2">
                    <div class="panel panel-success leave-type-sectn">
                        <div class="panel-heading leave-heading">Accumulated Casual Leave:
                            <span class="label label-info">
                  {{ isset($leaveDetail) ? $leaveDetail->accumalated_casual_leave : '0' }}
                                {{--                  {{$leaveDetail->accumalated_casual_leave}}--}}
              </span>
                        </div>
                        <!-- <div class="panel-body leave-content"> -->

                        <!-- </div> -->
                    </div>
                </div>
                <div class="col-sm-3 col-xs-3 leaves3">
                    <div class="panel panel-warning leave-type-sectn">
                        <div class="panel-heading leave-heading">Accumulated Sick Leave:
                            <span class="label label-info">
                                    {{ isset($leaveDetail) ? $leaveDetail->accumalated_sick_leave : '0' }}
                  </span>
                        </div>
                        <!-- <div class="panel-body leave-content">

                        </div> -->
                    </div>
                </div>
            </div>


            <div class="row leave-type1">
                <div class="col-sm-3 col-xs-3 leaves1">
                    <div class="panel panel-danger leave-type-sectn">
                        <div class="panel-heading leave-heading">Paid Casual Leave Taken:
                            <span class="label label-info">
                      {{ isset($leaveDetail) ? $leaveDetail->paid_casual : '0' }}
                                          </span>
                        </div>
                        <!-- <div class="panel-body leave-content"> -->

                        <!-- </div> -->
                    </div>
                </div>

                <div class="col-sm-3 col-xs-3 leaves1">
                    <div class="panel panel-danger leave-type-sectn">
                        <div class="panel-heading leave-heading">Paid Sick Leave Taken:
                            <span class="label label-info">
                                {{ isset($leaveDetail) ? $leaveDetail->paid_sick : '0' }}
                            </span>
                        </div>
                        <!-- <div class="panel-body leave-content"> -->

                        <!-- </div> -->
                    </div>
                </div>

                <div class="col-sm-3 col-xs-3 leaves1">
                    <div class="panel panel-danger leave-type-sectn">
                        <div class="panel-heading leave-heading">Paid Compensatory Leave Taken:
                            <span class="label label-info">
                                {{ isset($leaveDetail) ? $leaveDetail->compensatory_count : '0' }}
                            </span>
                        </div>
                        <!-- <div class="panel-body leave-content"> -->

                        <!-- </div> -->
                    </div>
                </div>

                <div class="col-sm-3 col-xs-3 leaves2">
                    <div class="panel panel-success leave-type-sectn">
                        <div class="panel-heading leave-heading">UnPaid Leave Taken:
                            <span class="label label-info">
                                @php
                                echo ( isset($leaveDetail) ? $leaveDetail->unpaid_sick : '0' )  +
                                 ( isset($leaveDetail) ? $leaveDetail->unpaid_casual : '0' )
                               @endphp
                            </span>
                        </div>
                        <!-- <div class="panel-body leave-content"> -->

                        <!-- </div> -->
                    </div>
                </div>

            </div>


            <div class="callout callout-danger apply-lv-alert">
                <strong>Note: </strong> <em>Kindly apply leave atleast two days before for timely approval, last moment applications may get rejected or remain unapproved.</em>
            </div>

            <ol class="breadcrumb breadcrumb-leave-change">
                <li><a href="{{ url('employees/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <!-- Small boxes (Stat box) -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="box box-primary">
                        @include('admins.validation_errors')

                        @if(session()->has('leaveError'))
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                {{ session()->get('leaveError') }}
                            </div>
                        @endif

                        <div class="box-header with-border leave-form-title-bg">
                            <h3 class="box-title">Leave application Form</h3>

                        </div>
                        <!-- /.box-header -->
                        <!-- form start -->

                        <form id="leaveForm" action="{{ url('leaves/create-leave-application') }}" method="POST" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <div class="box-body form-sidechange form-decor">

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-2 col-sm-3 col-xs-3 leave-label-box leave-label-470">
                                            <label for="leaveTypeId" class="apply-leave-label">Leave type</label>
                                        </div>
                                        <div class="col-md-10 col-sm-9 col-xs-9 leave-input-box leave-input-470">
                                            <select class="form-control input-sm basic-detail-input-style" name="leaveTypeId" id="leaveTypeId">
                                                @if(!$data['leave_types']->isEmpty())
                                                    /* if(@$data['probation_data']->leaves_left + @$data['probation_data']->compensatory_count > 0) */
                                                    @foreach($data['leave_types'] as $leave_type)
                                                        @if($leave_type->name != 'Late Coming')
                                                            <option value="{{$leave_type->id}}">{{$leave_type->name}}</option>
                                                        @endif
                                                    @endforeach
                                                    /* else */
                                                <!-- <option value="{{@$data['unpaid_leave']->id}}">{{@$data['unpaid_leave']->name}}</option> -->

                                                    /* endif */
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>


                                <div class="row">
                                    <div class="col-md-2 col-sm-3 col-xs-3 leave-label-box leave-label-470">
                                        <label for="leaveTypeId" class="apply-leave-label">Secondary leave type</label>

                                    </div>
                                    <div class="col-md-2 col-sm-3 col-xs-3 leave-label-470">
                                        <div class="">
                                            <button type="button" id="secondLeaveType_3" class="btn btn-primary secondLeaveType">Full</button>
                                            <button type="button" id="secondLeaveType_2" class="btn btn-primary secondLeaveType">Half</button>

                                        </div>
                                    </div>
                                    <input type="hidden" name="tasks" value="1">
                                    <input type="hidden" name="replacement" value="1">

                                <!--  <div class="col-md-3 col-sm-3 col-xs-3 leave-label-470">
                    <div class="panel panel-danger leave-type-sectn">
                      <div class="panel-heading leave-heading">Taken leave in this month:
                        <span class="label label-danger">{{@$data['taken_monthLeave']}}</span>

                      </div>

                    </div>
                  </div> -->

{{--                                    @if(@$data['user_designation'] ==4)--}}
{{--                                        <div class="col-md-6 col-sm-6 col-xs-6 leave-label-470">--}}
{{--                                            <div class="panel panel-danger leave-type-sectn">--}}
{{--                                                <div class="panel-heading leave-heading">You are not allowed to apply for Casual leave more  than 5 days in a month.--}}

{{--                                                </div>--}}

{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    @endif--}}

                                </div>


                                <!-- Time input section stasts here -->

                                <!-- Time input section ends here -->



                                <!-- Date input section starts here -->
                                <div class="showDates">
                                    <!-- Row Starts here -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-md-4 col-sm-3 col-xs-3 leave-label-box leave-label-470">
                                                        <label for="fromDate" class="apply-leave-label">From Date<span style="color: red">*</span></label>
                                                    </div>
                                                    <div class="col-md-8 col-sm-9 col-xs-9 leave-input-box-right leave-input-470">
                                                        <div class="input-group basic-detail-input-style">
                                                            <input autocomplete="off" type="text" class="form-control selectDate basic-detail-input-style apply-leave-input" id="fromDate" name="fromDate" placeholder="MM/DD/YYYY" value="" readonly>

                                                            <div class="input-group-addon time-icon">
                                                                <i class="fa fa-calendar"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-md-4 col-sm-3 col-xs-3 leave-label-box leave-label-470">
                                                        <label for="toDate" class="apply-leave-label">To Date<span style="color: red">*</span></label>
                                                    </div>
                                                    <div class="col-md-8 col-sm-9 col-xs-9 leave-input-box-left leave-input-470">
                                                        <div class="input-group basic-detail-input-style">
                                                            <input autocomplete="off" type="text" class="form-control selectDate basic-detail-input-style apply-leave-input" id="toDate" name="toDate" placeholder="MM/DD/YYYY" value="" readonly>
                                                            <div class="input-group-addon time-icon">
                                                                <i class="fa fa-calendar"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="dateErrors"></span>
                                    </div>
                                    <!-- Row Ends here -->

                                </div>
                                <input type="hidden" name="secondaryLeaveType" id="secondaryLeaveType" value="Full">
                                <!-- Date input section ends here -->
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-2 col-sm-3 col-xs-3 leave-label-box leave-label-470">
                                            <label for="noDays" class="apply-leave-label">Number of days</label>
                                        </div>
                                        <div class="col-md-10 col-sm-9 col-xs-9 leave-input-box leave-input-470">
                                            <input autocomplete="off" type="text" class="form-control basic-detail-input-style apply-leave-input" id="noDays" name="noDays" placeholder="Number Of Days" value="" readonly>
                                            <span class="noDayErrors"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- <div class="form-group">
                                  <div class="row">
                                      <div class="col-md-2 col-sm-3 col-xs-3 leave-label-box">
                                          <label for="manager" class="apply-leave-label">Sanction Officer 1</label>
                                      </div>
                                      <div class="col-md-10 col-sm-9 col-xs-9 leave-input-box">
                                          <input autocomplete="off" type="text" class="form-control basic-detail-input-style apply-leave-input" id="manager" name="manager" value="" readonly>
                                      </div>
                                  </div>
                                </div> -->




                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-2 col-sm-3 col-xs-3 leave-label-box leave-label-470">
                                            <label for="reasonLeave" class="apply-leave-label">Reason For Leave<span style="color: red">*</span></label>
                                        </div>
                                        <div class="col-md-10 col-sm-9 col-xs-9 leave-input-box leave-input-470">
                                            <textarea class="form-control basic-detail-input-style apply-leave-input" rows="5" name="reasonLeave" id="reasonLeave" maxlength="300"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group attach_doc">
                                    <div class="row">
                                        <div class="col-md-2 col-sm-3 col-xs-3 leave-label-box leave-label-470">
                                            <label for="fileName">Attach document</label>
                                        </div>
                                        <div class="col-md-10 col-sm-9 col-xs-9 leave-file-input leave-input-470">
                                            <input type="file" class="form-control apply-lv-file" id="fileName" name="fileNames[]" multiple>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="newAllDatesArray" id="newAllDatesArray" value="">
                                <input type="hidden" name="excludedDates" id="excludedDates" value="">
                            </div>
                            <!-- /.box-body -->

                            <div class="box-footer form-sidechange apply-leave-btn text-center">
                                <button type="button" class="btn btn-primary" id="applyLeaveSubmit">Apply</button>
                                <a href="{{ url('employees/dashboard') }}" class="btn btn-default">Cancel</a>
                            </div>
                        </form>

                    </div>
                </div>
                <!-- /.box -->
            </div>
            <!-- /.row -->
            <!-- Main row -->

        </section>
        <!-- /.content -->

        <div class="modal fade" id="checkAvailabilityModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Available Employees</h4>
                    </div>
                    <div class="modal-body checkAvailabilityModalBody">

                    </div>

                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->

    </div>
    <!-- /.content-wrapper -->
    <!-- bootstrap time picker -->
    <script src="{{asset('public/admin_assets/plugins/timepicker/bootstrap-timepicker.min.js')}}"></script>
    <script src="{{asset('public/admin_assets/plugins/validations/jquery.validate.js')}}"></script>
    <script src="{{asset('public/admin_assets/plugins/validations/additional-methods.js')}}"></script>
    <script>

        var userId = "{{$data['user']->id}}";
        var allowFormSubmit = {time: 1, date: 1, maternity: 1, tasks: 1, paternity:1};

        $('#department').on('change', function(){
            var department = $(this).val();
            var displayString = "";
            $("#replacement").empty();
            var departments = [];
            departments.push(department);

            // var toDate = $("#toDate").val();
            // var fromDate = $("#fromDate").val();

            $.ajax({
                type: "POST",
                url: "{{url('employees/departments-wise-employees')}}",
                data: {department_ids: departments},
                // url: "{{ url('leaves/leave-replacement-availability') }}",
                // data: {from_date: fromDate, to_date: toDate, department: department},
                success: function(result){
                    if(result.length == 0 || (result.length == 1 && result[0].user_id == userId)){
                        displayString += '<option value="" disabled>None</option>';

                    }else{
                        result.forEach(function(employee){
                            if(employee.user_id != userId && employee.user_id != 1){
                                displayString += '<option value="'+employee.user_id+'">'+employee.fullname+'</option>';
                            }
                        });
                    }

                    $('#replacement').append(displayString);
                }
            });
        }).change();

        $(".checkAvailabilityButton").on('click', function(){
            var toDate = $("#toDate").val();
            var fromDate = $("#fromDate").val();
            var department = $("#department").val();

            if(!department){
                alert("Please select a department.");
                return false;
            }

            if(toDate && fromDate){
                $.ajax({
                    type: 'POST',
                    url: "{{ url('leaves/leave-replacement-availability') }}",
                    data: {from_date: fromDate, to_date: toDate, department: department},
                    success: function (result) {
                        var html = "";

                        if(result.length != 0){
                            $.each(result, function(key, value){

                                html += '<div class="listbody" style="height:50px;border-bottom:1px solid #d7dae0"><img src="'+value.profile_picture+'" class="img-circle" alt="User Image" width="40" height="40"><span style="vertical-align: -webkit-baseline-middle;"><em>'+value.fullname+'</em></span><a href="javascript:void(0)" class="pull-right btn btn-primary availableEmployeeId" style="vertical-align: -webkit-baseline-middle; margin-top: 7px;" data-availableemployeeid="'+value.user_id+'">Select</a></div>'
                            });
                        }else{
                            html += '<span><em>None</em></span>'
                        }
                        $(".checkAvailabilityModalBody").html(html);
                        $('#checkAvailabilityModal').modal('show');
                    }
                });
            }else{
                alert("Please select From Date and To Date");
            }
        });

        $(document).on('click', '.availableEmployeeId', function(){
            $('#checkAvailabilityModal').modal('hide');
            var availableEmployeeId = $(this).data("availableemployeeid");
            $("#replacement").val(availableEmployeeId);
        });


        var totalLeaves = 1;

        $(".taskErrors").hide();

        $('#tasks').wysihtml5({
            "events": {
                "blur": function() {
                    var value = $("#tasks").val();
                    var striped = $(value).text();
                    striped = striped.trim();

                    if(striped.length <= 0){
                        allowFormSubmit.tasks = 0;
                        $(".taskErrors").text("Please enter the tasks.").css("color","#f00");
                        $(".taskErrors").show();
                    }else{
                        allowFormSubmit.tasks = 1;
                        $(".taskErrors").text("");
                        $(".taskErrors").hide();
                    }

                }
            }
        });

        $("#leaveForm").validate({
            rules :{
                "toDate" : {
                    required : true
                },
                "fromDate" : {
                    required : true
                },
                "reasonLeave":{
                    required : true,
                    maxlength: 300
                },

                "fileNames[]" :  {
                    required : true
                }



            },
            messages :{
                "toDate" : {
                    required : 'Please select a date.'
                },
                "fromDate" : {
                    required : 'Please select a date.'
                },
                "reasonLeave":{
                    required : 'Please enter reason for leave.',
                    maxlength : 'Maximum 300 characters are allowed.'
                },
                "fileNames[]" : {
                    required : 'Please attach document.'
                }
            }
        });


    </script>

    <script type="text/javascript">
        var today = new Date();
        var minimumDate = moment().subtract(15, 'days')._d;
        var maximumDate = moment().add(45, 'days')._d;

        //Date picker
        $("#fromDate").datepicker({
            //startDate: minimumDate,
            endDate: maximumDate,
            autoclose: true,
            orientation: "bottom"

        });

        $("#toDate").datepicker({
            //startDate: minimumDate,
            autoclose: true,
            orientation: "bottom"

        });

        function enumerateDaysBetweenDates(startDate, endDate) {
            startDate = moment(startDate);
            endDate = moment(endDate);
            var now = startDate.clone();
            var dates = [];

            while (now.isSameOrBefore(endDate)) {
                dates.push(now.format('YYYY-MM-DD'));
                now.add(1, 'days');
            }
            return dates;
        };



        $("#secondLeaveType_3").addClass("active");
        $("#noDays").val("0");
        $(".dateErrors").hide();
        $(".timeErrors").hide();
        $(".noDayErrors").hide();
        $(".showRadio").hide();
        $(".importantTasks").hide();


        //$("#toDate").prop("disabled",true);
        //$("#fromDate").prop("disabled",true);

        $(".secondLeaveType").click(function(){
            allowFormSubmit.time = 1;
            allowFormSubmit.date = 1;
            allowFormSubmit.maternity = 1;
            allowFormSubmit.tasks = 1;
            //$("#fromDate").prop("disabled",false);

            $(".secondLeaveType").removeClass("active");
            $(this).addClass("active");

            //var text = $(this).text();
            var text = $(this).text();

            $("#secondaryLeaveType").val(text);

            var days=0;

            if(text == "Half"){
                days = 0.5;
            }

            $("#noDays").val(days);

            $("#fromDate").val("");
            $("#toDate").val("");

            if(text == "Full"){
                $("#toDate").prop("disabled",false);



            }else if(text == "Half"){
                //$(".showTimes").show();
                $(".showRadio").hide();
                $(".importantTasks").hide();
                $("#toDate").prop("disabled",true);

            }

        });



        $(".selectDate").on("change",function(){
            var activeButton = "#"+$("button.active").prop('id');
            var text = $(activeButton).text();

            var fromDate = $("#fromDate").val();



            var toDate = $("#toDate").val();

            var dd = minimumDate.getDate();

            var mm = minimumDate.getMonth()+1;
            var yyyy = minimumDate.getFullYear();

            if(dd<10)
            {
                dd='0'+dd;
            }

            if(mm<10)
            {
                mm='0'+mm;
            }

            newMinDate = mm+'/'+dd+'/'+yyyy;

            if(text == "Full"){

                allowFormSubmit.time = 1;

                if(Date.parse(fromDate) > Date.parse(toDate)){
                    allowFormSubmit.date = 0;
                    $(".dateErrors").text("Please select valid dates.");
                    $(".dateErrors").show();
                }else{
                    allowFormSubmit.date = 1;

                    var oneDay = 24*60*60*1000; // hours*minutes*seconds*milliseconds
                    var diffDays = Math.round(Math.abs((Date.parse(toDate) - Date.parse(fromDate))/(oneDay)));

                    if(isNaN(diffDays)){

                        $("#noDays").val("0");

                        allowFormSubmit.date = 0;
                        $(".noDayErrors").text("Number of days cannot be zero.");
                        $(".noDayErrors").show();

                    }else{

                        diffDays = diffDays + 1;
                        console.log("diff1: "+diffDays);
                        //Calculate the sundays between leaves
                        var result = [];
                        if(fromDate != toDate){
                            var startSundayCheck = moment(fromDate);
                            var endSundayCheck   = moment(toDate);
                            var sunday   = 0;                    // Sunday

                            var current = startSundayCheck.clone();
                            var checkStart = startSundayCheck.format('dddd');

                            if(checkStart == 'Sunday'){
                                result.push(current.clone()._d);
                            }

                            while (current.day(7 + sunday).isSameOrBefore(endSundayCheck)) {
                                result.push(current.clone()._d);
                            }

                        }else if(fromDate == toDate){
                            var startSundayCheck = moment(fromDate);
                            var sunday = startSundayCheck.format('dddd');

                            if(sunday == 'Sunday'){
                                result = [startSundayCheck._d];
                            }
                        }

                        diffDays = diffDays - result.length;
                        console.log("diff2: "+diffDays);
                        //End Calculation of sundays between leaves

                        //Calculate holidays that are not Sundays
                        var allDatesArray = enumerateDaysBetweenDates(fromDate, toDate);

                        $.each(result, function(key, value){
                            result[key] = moment(value).format("YYYY-MM-DD");
                        });

                        $.ajax({
                            type : 'POST',
                            url : "{{ url('leaves/between-leave-holidays') }}",
                            data: {all_dates_array: allDatesArray},
                            success: function (res) {
                                resLength = Number(res.length);
                                diffDays = diffDays - resLength;
                                console.log("diff3: "+diffDays);

                                if(diffDays <= 0){
                                    diffDays = 0;
                                    allowFormSubmit.date = 0;
                                    $(".noDayErrors").text("Number of days cannot be zero.");
                                    $(".noDayErrors").show();
                                    var newAllDatesArray = "";

                                }else{
                                    allowFormSubmit.date = 1;
                                    $(".noDayErrors").text("");
                                    $(".noDayErrors").hide();

                                    $.each(res, function(key, value){
                                        result.push(value);
                                    });

                                    var newAllDatesArray = [];

                                    $.each(allDatesArray, function(key, value){
                                        if(!result.includes(value)){
                                            newAllDatesArray.push(value);
                                        }
                                    });

                                    newAllDatesArray = newAllDatesArray.join();
                                    console.log("newAllDatesArray: ",newAllDatesArray);

                                }

                                $("#newAllDatesArray").val(newAllDatesArray);
                                $("#excludedDates").val(result.join());

                                $("#noDays").val(diffDays);

                                if(diffDays > totalLeaves){
                                    var checkMaternity = $("#leaveTypeId").val();

                                    if((checkMaternity == 7) && ((diffDays > 15) || (diffDays < 15))){
                                        allowFormSubmit.paternity = 0;
                                        $(".noDayErrors").text("You cannot take paternity leave for more or less than 15 days.");
                                    }else{
                                        allowFormSubmit.paternity = 1;
                                        // $(".noDayErrors").text("You do not have enough paid leaves.");
                                        // alert('You do not have enough paid leaves.');
                                    }


                                    if((checkMaternity == 4) && ((diffDays > 90) || (diffDays < 90))){
                                        allowFormSubmit.maternity = 0;
                                        $(".noDayErrors").text("You cannot take maternity leave for more or less than 90 days.");
                                    }else{
                                        allowFormSubmit.maternity = 1;
                                        // $(".noDayErrors").text("You do not have enough paid leaves.");
                                        // alert('You do not have enough paid leaves.');
                                    }

                                    $(".noDayErrors").show();
                                }else{
                                    $(".noDayErrors").text("");
                                    $(".noDayErrors").hide();
                                }
                            }
                        });
                        //End Calculate holidays that are not Sundays

                    }

                    $(".dateErrors").text("");
                    $(".dateErrors").hide();
                }
            }

        });

        $(".selectTime").on("change",function(){
            var activeButton = "#"+$("button.active").prop('id');
            var text = $(activeButton).text();

            var fromTime = $("#fromTime").val();

            var today = new Date();
            var dd = today.getDate();

            var mm = today.getMonth()+1;
            var yyyy = today.getFullYear();

            if(dd<10)
            {
                dd='0'+dd;
            }

            if(mm<10)
            {
                mm='0'+mm;
            }

            today = yyyy+'-'+mm+'-'+dd;

            fromTime = today+" "+fromTime;

            if(text == "Short"){
                addHours = 1000 * 60 * 60 * 2;
                newToTime = Date.parse(fromTime) + addHours;
                newToDate = new Date(newToTime);
                newToTime = newToDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                $("#toTime").val(newToTime);
            }

            var toTime = $("#toTime").val();

            toTime = today+" "+toTime;

            if(text == "Short"){

                if(Date.parse(fromTime) >= Date.parse(toTime)){
                    allowFormSubmit.time = 0;
                    $(".timeErrors").text("From time should be less than To time.");
                    $(".timeErrors").show();
                }else{
                    allowFormSubmit.time = 1;
                    $(".timeErrors").text("");
                    $(".timeErrors").hide();
                }

            }

        });

        $("#leaveTypeId").on("change",function(){
            $("#fromDate").val("");
            $("#toDate").val("");
            $("#fromTime").val("");
            $("#toTime").val("");
            //$("#noDays").val("0.25");
            //$(".secondLeaveType").removeClass("active");
            // $("#secondLeaveType_1").addClass("active");
            $(".showTimes").show();
            $(".showRadio").hide();
            $(".importantTasks").hide();
            //$("#toDate").prop("disabled",true);
        });


        $("#fromTime").timepicker({
            showInputs: false
        });

        $('#stateId').on('change', function(){
            var stateId = $(this).val();
            var stateIds = [];
            stateIds.push(stateId);

            $('#cityId').empty();
            var displayString = "";

            $.ajax({
                type: 'POST',
                url: "{{ url('employees/states-wise-cities') }} ",
                data: {stateIds: stateIds},
                success: function(result){
                    if(result.length != 0){
                        result.forEach(function(city){
                            if(city.id == 1110){
                                displayString += '<option value="'+city.id+'" selected>'+city.name+'</option>';
                            }else{
                                displayString += '<option value="'+city.id+'">'+city.name+'</option>';
                            }

                        });
                    }else{
                        displayString += '<option value="" selected disabled>None</option>';
                    }

                    $('#cityId').append(displayString);
                }
            });

        }).change();

    </script>

    <script type="text/javascript">

        var probationEndOrNot = 1;

        $("#applyLeaveSubmit").click(function(){

            console.log(allowFormSubmit);


            if(allowFormSubmit.time == 0 || allowFormSubmit.date == 0 || allowFormSubmit.maternity == 0  || allowFormSubmit.paternity == 0 || allowFormSubmit.tasks == 0){
                return false;
            }

            if((probationEndOrNot == '1' || probationEndOrNot == '0')){
                if(allowFormSubmit.time == 1 && allowFormSubmit.date == 1 && allowFormSubmit.maternity == 1 && allowFormSubmit.tasks == 1){
                    $("#leaveForm").submit();
                }
            }

        });

        $(".attach_doc").hide();
        $('#leaveTypeId').on('change', function(){

            var leaveTypeId = $(this).val();

            if(leaveTypeId==4 || leaveTypeId==7){
                $(".attach_doc").show();

            }else{
                $(".attach_doc").hide();
            }


        });

    </script>

@endsection
