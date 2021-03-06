@extends('admins.layouts.app')

@section('content')

    <link rel="stylesheet" href="{{asset('public/admin_assets/bower_components/fullcalendar/dist/fullcalendar.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/admin_assets/bower_components/fullcalendar/dist/fullcalendar.print.min.css')}}" media="print">

    <!-- Content Wrapper. Contains page content -->

    <div class="content-wrapper">

        <!-- Content Header (Page header) -->

        <section class="content-header">

            <h1><i class="fa fa-tasks"></i> Attendance</h1>

            <ol class="breadcrumb">

                <li><a href="{{url('employees/dashboard')}}"><i class="fa fa-dashboard"></i> Home</a></li>

            </ol>

        </section>


        <!-- Main content -->

        <section class="content">

            @if(session()->has('leave_success'))

                <div class="alert alert-success alert-dismissible">

                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>

                    {{ session()->get('leave_success') }}

                </div>

        @endif

        <!-- Small boxes (Stat box) -->


            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <form id="employee_Attendance" method="GET">
                            <div class="row select-detail-below">

                                <input type="hidden" name="id" value="{{$user->id}}">

                                <div class="col-md-2 attendance-column1">
                                    <label>Year<sup class="ast">*</sup></label>
                                    <select class="form-control input-sm basic-detail-input-style" id="year" name="year">
                                        <option value="" selected disabled>Please select Year</option>
                                        @for($year = date("Y"); $year >=2020; $year--)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="col-md-2 attendance-column2">
                                    <label>Month<sup class="ast">*</sup></label>
                                    <select class="form-control input-sm basic-detail-input-style" id="month" name="month">
                                        <option value="" selected disabled>Please select Month</option>
                                        <option value="1">Dec-Jan</option>
                                        <option value="2">Jan-Feb</option>
                                        <option value="3">Feb-March</option>
                                        <option value="4">Mar-Apr</option>
                                        <option value="5">Apr-May</option>
                                        <option value="6">May-June</option>
                                        <option value="7">June-Jul</option>
                                        <option value="8">Jul-Aug</option>
                                        <option value="9">aug-Sep</option>
                                        <option value="10">sep-Oct</option>
                                        <option value="11">Oct-Nov</option>
                                        <option value="12">Nov-Dec</option>
                                    </select>
                                </div>

                                <div class="col-md-2 attendance-column4">
                                    <div class="form-group">
                                        <button type="submit" class="btn searchbtn-attendance">Search <i class="fa fa-search"></i></button>
                                    </div>
                                </div>
                            </div>
                            <br>
                        </form>

                        <?php
                        $monthNames = Array('1' => "January", '2' => "February", '3' => "March", '4' => "April", '5' => "May", '6' => "June", '7' => "July",
                            '8' => "August", '9' => "September", '10' => "October", '11' => "November", '12' => "December");

                        if (!isset($_REQUEST["month"])) $_REQUEST["month"] = date("n");
                        if (!isset($_REQUEST["year"])) $_REQUEST["year"] = date("Y");

                        $cMonth = $_REQUEST["month"];
                        $cYear = $_REQUEST["year"];

                        $prev_year = $cYear;
                        $next_year = $cYear;
                        $prev_month = $cMonth-1;
                        $next_month = $cMonth+1;

                        if ($prev_month == 0 ) {
                            $prev_month = 12;
                            $prev_year = $cYear - 1;
                        }
                        if ($next_month == 13 ) {
                            $next_month = 1;
                            $next_year = $cYear + 1;
                        }

                        $date1 = $prev_year.'-'.$prev_month.'-'.'26';
                        $date2 = $cYear.'-'.$cMonth.'-'.'25';

                        ?>
                        <hr class="attendance-hr">
                        <!-- Attendance guide section starts here -->
                        <div class="emp-name-and-color">
                            <div class="attendance-guide">
                                <ul>
                                    <li class="attendance-rectangle holiday-only">Holiday</li>
                                    <li class="attendance-rectangle present-only">Present</li>
                                    <li class="attendance-rectangle absent-only">Absent</li>
                                    <li class="attendance-rectangle check-in-only">Check-In</li>

                                    <li class="attendance-rectangle ">Balance Casual : @if(isset($leaveDetail)){{ $leaveDetail->accumalated_casual_leave }} @else 0 @endif</li>
                                    <li class="attendance-rectangle ">Balance Sick : @if(isset($leaveDetail)){{ $leaveDetail->accumalated_sick_leave }} @else 0 @endif</li>

                                </ul>
                            </div>
                            <div class="a-last-absent">
                                <h3 class="a-employe-name">{{$user->employee->fullname}}</h3>
                                <!-- <span class="a-last-absent-span1">bsenLast At:</span> -->
                                <!-- <span class="a-last-absent-span2">25 Days Ago</span> -->
                            </div>
                        </div>
                        <!-- Attendance guide section ends here -->



                        <!-- Calender starts here -->
                        <div class="box">
                            <div class="box-body no-padding">
                                <!-- THE CALENDAR -->
                                <div id="calendar" class="fc fc-unthemed fc-ltr">
                                    <div class="fc-toolbar fc-header-toolbar">
                                        <div class="fc-left">
                                            <div class="fc-button-group">
                                                <a href="<?php echo url()->current() . "?month=". $prev_month . "&year=" . $prev_year. "&id=". $user->id; ?>"><button type="button" class="fc-prev-button fc-button fc-state-default fc-corner-left" aria-label="prev"><span class="fc-icon fc-icon-left-single-arrow"></span></button></a>
                                                <a href="<?php echo url()->current() . "?month=". $next_month . "&year=" . $next_year. "&id=". $user->id; ?>"><button type="button" class="fc-next-button fc-button fc-state-default fc-corner-right" aria-label="next"><span class="fc-icon fc-icon-right-single-arrow"></span></button></a>
                                            </div>
                                            <!-- <button type="button" class="fc-today-button fc-button fc-state-default fc-corner-left fc-corner-right fc-state-disabled" disabled="">today</button> -->
                                        </div>


                                        <div class="fc-center">
                                            <h2>
                                                <?php
                                                echo $monthNames[$prev_month];
                                                echo "-";
                                                echo $monthNames[$cMonth];
                                                echo " ".$cYear;
                                                ?>
                                            </h2>
                                        </div>


                                        <!--&& $verify['verifier'] != 0 -->
                                        <!--if(strtotime($on_date) < strtotime(date("Y-m-d")) && $verify['isverified'] == 0  ) unverified  -->


                                        @if($_REQUEST["month"] == date('n'))
                                            @if($verify['isverified'] == 0 )
                                                @if($verify['verifier']!=$user->id)

                                                    <button type="button" class="btn btn-primary verify-btn-calender verifyMonthAttendance" data-userid="{{$user->id}}"
                                                    data-managerid="{{$verify['verifier']}}" data-ondate="{{$cYear.'-'.$cMonth.'-'.'25'}}">Verify Attendance</button>
                                                @endif
                                            @elseif($verify['isverified'] == 1)
                                                <span class="verify-btn-calender attendance-verified label-success">Verified</span>
                                            @endif
                                        @else
                                            @if($verify['isverified'] == 0)
                                                <span  class="label label-info">Attendance not Verified.</span>
                                            @else
                                                <span class="verify-btn-calender attendance-verified label-success">Verified</span>
                                            @endif
                                        @endif
                                        <div class="fc-clear"></div>
                                    </div>


                                    <div class="fc-view-container" style="">
                                        <div class="fc-view fc-month-view fc-basic-view" style="">
                                            <table class="">
                                                <thead class="fc-head">
                                                <tr>
                                                    <td class="fc-head-container fc-widget-header">
                                                        <div class="fc-row fc-widget-header">
                                                            <table class="">
                                                                <thead>
                                                                <tr>
                                                                    <th class="fc-day-header fc-widget-header fc-sun"><span>Sun</span></th>
                                                                    <th class="fc-day-header fc-widget-header fc-mon"><span>Mon</span></th>
                                                                    <th class="fc-day-header fc-widget-header fc-tue"><span>Tue</span></th>
                                                                    <th class="fc-day-header fc-widget-header fc-wed"><span>Wed</span></th>
                                                                    <th class="fc-day-header fc-widget-header fc-thu"><span>Thu</span></th>
                                                                    <th class="fc-day-header fc-widget-header fc-fri"><span>Fri</span></th>
                                                                    <th class="fc-day-header fc-widget-header fc-sat"><span>Sat</span></th>
                                                                </tr>
                                                                </thead>
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                                </thead>
                                                <tbody class="fc-body">
                                                <tr>
                                                    <td class="fc-widget-content">
                                                        <div class="fc-scroller fc-day-grid-container" style="overflow: hidden; height: 666px;">
                                                            <div class="fc-day-grid fc-unselectable">
                                                                <div class="fc-row fc-week fc-widget-content" style="height: 96px;">

                                                                    <div class="fc-bg">
                                                                        <table>
                                                                            <thead>

                                                                            <?php

                                                                            $timestamp = mktime(0,0,0,$cMonth,1,$cYear);
                                                                            $thismonth = getdate ($timestamp);

                                                                            $startday = $thismonth['wday'];

                                                                            $timestamp = strtotime($date1);
                                                                            $thismonth = getdate ($timestamp);
                                                                            $startday = $thismonth['wday'];


                                                                            $allowAttendanceVerification = 1;
                                                                            $j=array();
                                                                            $i=0;
                                                                            while (strtotime($date1) <= strtotime($date2)) {
                                                                            $day = $i - $startday + 1;
                                                                            $cDayTime = $cYear.'-'.$cMonth.'-'.$day;

                                                                            $attendanceArray = getAttendanceInfo($date1, $user->id);


                                                                            $status = $attendanceArray['status'];

                                                                            $mapUrl = url('attendances/view-map').'?id='.$user->id.'&date='.$date1;


                                                                            if(($i % 7) == 0 ){

                                                                            // if(empty($status)){
                                                                            //   $status = "Week-Off";
                                                                            // }else{
                                                                            //$status = "Week-Off";
                                                                            //}


                                                                            //$status = "Week-Off";
                                                                            //$attendanceArray['secondary_leave_type'] = "";

                                                                            ?>
                                                                            <tr>

                                                                                <?php }if($i < $startday) { ?>
                                                                                <td class="fc-day-top fc-sun fc-other-month fc-past" data-date=""></td>

                                                                                <?php


                                                                                }else{

                                                                                if(empty($status)){
                                                                                    $allowAttendanceVerification = 0;  //one of the dates do not have status
                                                                                }

                                                                                ?>


                                                                                <td class="fc-day-top fc-sun fc-future attendance-tds" data-date="2019-09-01">
                                                                                    <?php $date_month = Date('d M',strtotime($date1)); ?>
                                                                                    <span class="fc-day-number"><?php echo $date_month; ?></span>
                                                                                    <div class="three-icon-box">

                                                                                        @if(!empty($attendanceArray['description']))
                                                                                            <div class="info-tooltip">
                                                                                                <i class="fa fa-info-circle a-icon1"></i>
                                                                                                <span class="info-tooltiptext">{{$attendanceArray['description']}}</span>
                                                                                            </div>
                                                                                        @endif

                                                                                        @if(!empty($attendanceArray['first_punch']))
                                                                                            <div class="eye-tooltip" data-date="{{$date1}}" data-userid="{{$user->id}}">
                                                                                                <i class="fa fa-eye a-icon2"></i>
                                                                                                <span class="eye-tooltiptext">View In-Outs</span>
                                                                                            </div>
                                                                                        @endif

                                                                                        <div class="edit-tooltip" data-date="{{$date1}}" data-remarks="{{$attendanceArray['remarks']}}" data-userid="{{$user->id}}">
                                                                                            <i class="fa @if(!empty($attendanceArray['remarks'])){{'fa-edit a-icon4'}}@endif"></i>
                                                                                            <span class="edit-tooltiptext">@if(!empty($attendanceArray['remarks'])){{'View Remarks'}}@endif</span>
                                                                                        </div>

                                                                                        @if($date1 >= $user->employee->joining_date)
                                                                                            @if($verify['isverified'] == 0)
                                                                                                @if($attendanceArray['status'] == 'Leave')

                                                                                                @else
                                                                                                    @if(strtotime($date1) <= strtotime(date("Y-m-d")))
                                                                                                        <div class="status-tooltip" data-date="{{date('d-m-Y',strtotime($date1))}}" data-userid="{{$user->id}}" data-status="{{$status}}">
                                                                                                            <!--<i class="fa fa-clock-o a-icon5"></i>-->
                                                                                                            <i class="fa fa-user-times" aria-hidden="true"></i>
                                                                                                            <span class="status-tooltiptext">Change Status</span>
                                                                                                        </div>
                                                                                                    @endif
                                                                                                @endif
                                                                                            @endif
                                                                                        @endif

                                                                                        @if($date1 >= $user->employee->joining_date)
                                                                                            @if($verify['isverified'] == 0)
                                                                                                @if($attendanceArray['status'] == 'Leave')

                                                                                                @else
                                                                                                    <div class="leave-tooltip" data-date="{{date('Y-m-d',strtotime($date1))}}" data-userid="{{$user->id}}" data-status="{{$status}}">
                                                                                                        <!--<i class="fa fa-clock-o a-icon5"></i>-->
                                                                                                        <i class="fa fa-user-secret" aria-hidden="true"></i><span class="leave-tooltiptext">Add Leave</span>
                                                                                                    </div>

                                                                                                @endif
                                                                                            @endif
                                                                                        @endif
                                                                                    </div>

                                                                                    <?php if($status) { ?>
                                                                                    <div class="leave-type-onnly">
                                                                                        <a class="fc-day-grid-event fc-h-event fc-event fc-start fc-end fc-draggable @if($status == 'Present'){{'calender-day-present'}}@elseif($status == 'Holiday'){{'calender-day-holiday'}}@elseif($status == 'Absent' || $status == 'Week-Off'){{'calender-day-absent'}}@endif">
                                                                                            <span><?php echo  $status; ?></span>
                                                                                        </a>
                                                                                        <div>
                                                                                            <a><span class="label label-warning full-short-half"><?php echo  $attendanceArray['secondary_leave_type']; ?></span></a>
                                                                                        </div>
                                                                                    </div>

                                                                                    <?php } ?>

                                                                                </td>

                                                                                <?php
                                                                                $date1 = date ("Y-m-d", strtotime("+1 days", strtotime($date1)));
                                                                                } if(($i % 7) == 6 ){

                                                                                ?>

                                                                            </tr>

                                                                            <?php
                                                                            }

                                                                            $i++;
                                                                            }
                                                                            ?>



                                                                            </thead>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Calender ends here -->

                                </div>
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- Calender starts here -->
                    </div>
                </div>
            </div>

            <!-- /.row -->
            <div class="modal fade" id="modal-in-outs">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close attendance-close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title text-center">Multiple In-Outs &nbsp;&nbsp;<span class="multiple-in-outs"></span></h4>
                        </div>
                        <div class="modal-body attendance-present-modal append-punches">

                        </div>
                        <div class="modal-footer in-out-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->

            <div class="modal fade" id="modal-remarks">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close attendance-close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title text-center">User's Remarks &nbsp;&nbsp;<span class="user-remarks"></span></h4>
                        </div>

                        <form id="attendanceRemarkForm" method="POST" action="{{url('attendances/save-remarks')}}">
                            {{ csrf_field() }}
                            <div class="modal-body">

                                <p>Remark:</p>
                                <textarea rows="2" cols="4" placeholder="Your remarks" id="remarks" name="remarks" class="add-remark-textarea" disabled></textarea>

                                <input type="hidden" name="user_id" value="{{$user->id}}">
                                <input type="hidden" id="on_date" name="on_date" value="">
                                <input type="hidden" id="url" name="url" value="">

                            </div>
                            <div class="modal-footer in-out-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->

            <div class="modal fade" id="modal-changeStatus">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close attendance-close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title text-center">Change Status</h4>
                        </div>

                        <form id="changeStatusForm" method="POST" action="{{url('attendances/change-status')}}">
                            {{ csrf_field() }}
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-4 attendance-column1">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select name="attendanceStatus" id="attendanceStatus" class="form-control input-sm basic-detail-input-style">
                                                <option value="Present">Present</option>
                                                <option value="Absent">Absent</option>
                                                <option value="Holiday">Holiday</option>
                                                <option value="Week-Off">Week-Off</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 attendance-column2">
                                        <div class="form-group">
                                            <label>On-Date</label>
                                            <input type="text" class="form-control input-sm basic-detail-input-style" name="on_date" id="change_on_date" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-4 attendance-column4">
                                        <div class="form-group">
                                            <div class="change_on_time">
                                                <!--<label>On-Time</label>-->
                                                <input type="text" class="form-control input-sm basic-detail-input-style hide" name="on_time" id="change_on_time" placeholder="Ex: 09:30 AM" value="09:30 AM">
                                            </div>
                                        </div>
                                        <input type="hidden" name="user_id" value="{{$user->id}}">
                                        <input type="hidden" id="change_url" name="url" value="">
                                    </div>
                                </div>

                            </div>
                            <div class="modal-footer in-out-footer">
                                <button type="submit" id="saveStatus" class="btn btn-primary" name="saveStatus" value="Save">Save</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->

            <!-- add leave model starts -->
            <div class="modal fade" id="modal-addLeave">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close attendance-close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title text-center">Add Leave</h4>
                        </div>

                        <form id="changeStatusFormLeave" method="POST" action="{{url('attendances/add-leave')}}">
                            {{ csrf_field() }}
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 attendance-column1">
                                        <div class="form-group">
                                            <label>Leave Type</label>
                                            <select name="leaveTypeId" id="attendanceStatusLeave" class="form-control input-sm basic-detail-input-style"  onchange="changeFunc();">
                                                <option value="1">Casual Leave</option>
                                                <option value="2">Sick Leave</option>
                                                <option value="7">Paternity Leave</option>
                                                <option value="5">Compensatory Leave</option>
                                                <!-- <option value="4">Maternity Leave</option>-->
                                            </select>
                                        </div>

                                        <div class="form-group" id="cas_sick">
                                            <input type="radio" name="secondaryLeaveType" value="Full" checked>Full
                                            <input type="radio" name="secondaryLeaveType" value="Half">Half
                                        </div>

                                        <div class="form-group" id="totalDays">
                                            <label for="noDays" class="apply-leave-label">Days </label>
                                            <input autocomplete="off" type="text" class="form-control basic-detail-input-style apply-leave-input" id="numberOfDays" name="noDays" placeholder="Number Of Days" value="" readonly>
                                            <p id="errorNumberOfDays" class="text-red"></p>
                                        </div>
                                    </div>

                                    <div class="col-md-6 attendance-column4" style="padding-left: 5px;">
                                        <div class="form-group">
                                            <label>On-Date</label>
                                            <input type="text" class="form-control input-sm basic-detail-input-style paternity-leave" name="on_date" id="leave_date" readonly>
                                        </div>

                                        <div class="form-group" id="pat_mat" style="display: none;">
                                            <label>To-Date</label>
                                            <input type="text" class="form-control datepicker paternity-leave" name="to_date" id="paternity-to-date" autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Reason</label><br>
                                            <textarea name="reasonLeave" id="reasonLeave" style="width: 100%"></textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-4 attendance-column4">
                                        <div class="form-group">
                                            <div class="change_on_time">
                                                <!--<label>On-Time</label>-->
                                                <input type="text" class="form-control input-sm basic-detail-input-style hide" name="on_time" id="change_on_time" placeholder="Ex: 09:30 AM" value="09:30 AM">
                                            </div>
                                        </div>
                                        <input type="hidden" name="user_id" value="{{$user->id}}">
                                        <input type="hidden" id="change_url_leave" name="url" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer in-out-footer">
                                <button type="submit" id="addLeaveButton" class="btn btn-primary" name="saveStatus" value="Save">Save</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /model end -->


            <!-- Main row -->
            <!-- /.row (main row) -->
        </section>

        <!-- /.content -->

    </div>
    <!-- /.content-wrapper -->
    <script src="{!!asset('public/admin_assets/plugins/sweetalert/sweetalert.min.js')!!}"></script>
    <script src="{{asset('public/admin_assets/plugins/validations/jquery.validate.js')}}"></script>
    <script src="{{asset('public/admin_assets/plugins/validations/additional-methods.js')}}"></script>

    <script type="text/javascript">
        $(document).ready(function(){
            $('#totalDays').hide();

            //Date picker
            $('.datepicker').datepicker({
                autoclose: true,
                orientation: "bottom",
            });

            $("#employee_Attendance").validate({
                rules :{
                    "year" : {
                        required : true,
                    },
                    "month" : {
                        required : true,
                    }
                },
                messages :{
                    "year" : {
                        required : 'Please select Year.'
                    },
                    "month" : {
                        required : 'Please select Month.'
                    }
                }
            });

            $("#attendanceRemarkForm").validate({
                rules :{
                    "remarks" : {
                        required : true,
                    }
                },
                messages :{
                    "remarks" : {
                        required : 'Please select remarks.'
                    }
                }
            });

            $("#changeStatusForm").validate({
                rules :{
                    "attendanceStatus" : {
                        required : true,
                    },
                    "on_date" : {
                        required : true,
                    },
                    "on_time" : {
                        required : true,
                    }
                },
                messages :{
                    "attendanceStatus" : {
                        required : 'Please select status.'
                    },
                    "on_date" : {
                        required : 'Please enter date.'
                    },
                    "on_time" : {
                        required : 'Please enter time.'
                    }
                }
            });

            $("#changeStatusFormLeave").validate({
                rules :{
                    "reasonLeave" : {
                        required : true,
                    },
                    "to_date" : {
                        required : true,
                    }
                },
                messages :{
                    "reasonLeave" : {
                        required : 'Please Fill Reason.'
                    },
                    "to_date":{
                        required : 'Please Fill To Date.'
                    }
                }
            });

        });
    </script>

    <script type="text/javascript">
        var allowFormSubmit = {time: 1, date: 1, maternity: 1, tasks: 1, paternity:1};

        $(".leave-tooltip").on('click',function(){

            var date = $(this).data('date');

            var user_id = $(this).data('userid');

            $("#leave_date").val(date);

            var leave_url = window.location.href;
            $("#change_url_leave").val(leave_url);


            $("#modal-addLeave").modal('show');
        });

        var defYear = "{{$req['year']}}";
        var defMonth = "{{$req['month']}}";

        if(defYear != '0'){
            $('#year').val(defYear);
        }

        if(defMonth != '0'){
            $('#month').val(defMonth);
        }

        $(".eye-tooltip").on('click',function(){
            var date = $(this).data('date');
            var user_id = $(this).data('userid');

            $(".append-punches").empty("");
            var info = "";

            var params = '?id='+user_id+'&date='+date;
            var mapUrl = "{{url('attendances/view-map')}}" + params;

            $.ajax({
                type: 'POST',
                url: '{{url("/attendances/multiple-punches")}}',
                data: {date: date, user_id: user_id},
                success: function(result){
                    result.forEach(function(item, index){
                        if(index % 2 == 0){
                            info += '<div><button type="button" class="btn modal-check-in">'+item.on_time;
                        }else{
                            info += '<div><button type="button" class="btn modal-check-out">'+item.on_time;
                        }

                        if(item.punched_by == 0){
                            info += ' (Biometric)</button>';
                        }else if(item.punched_by == user_id){
                            info += ' (App)</button>';
                        }else if(item.punched_by != user_id){
                            info += ' (Verifier)</button>';
                        }

                        if(item.type == 'Check-In'){
                            info += '<a href="'+mapUrl+'" target="_blank"><span class="label label-warning"> Check-In  &nbsp;<i class="fa fa-map-marker" aria-hidden="true"></i></span></a></div>';
                        }else if(item.type == 'Check-Out'){
                            info += '<a href="'+mapUrl+'" target="_blank"><span class="label label-danger"> Check-Out  &nbsp;<i class="fa fa-map-marker" aria-hidden="true"></i></span></a></div>';
                        }else{
                            info += '</div>';
                        }
                    });

                    date = new Date(date);
                    date = moment(date).format('DD-MM-YYYY');
                    $(".multiple-in-outs").text(date);
                    $(".append-punches").append(info);
                }
            });

            $("#modal-in-outs").modal('show');
        });


        $(".edit-tooltip").on('click',function(){
            var date = $(this).data('date');
            var user_id = $(this).data('userid');
            var remarks = $(this).data('remarks');

            $('#on_date').val(date);
            $('#remarks').val(remarks);
            var url = window.location.href;
            $("#url").val(url);

            date = new Date(date);
            date = moment(date).format('DD-MM-YYYY');
            $(".user-remarks").text(date);

            $("#modal-remarks").modal('show');
        });


        $(".status-tooltip").on('click',function(){
            var date = $(this).data('date');
            var user_id = $(this).data('userid');
            var status = $(this).data('status');

            $("#change_on_date").val(date);

            if(status){
                if(status == 'Absent'){
                    $(".change_on_time").hide();
                }else if(status == 'Present'){
                    $(".change_on_time").show();
                }else if(status == 'Holiday'){
                    $(".change_on_time").hide();
                }else if($(this).val() == 'Week-Off'){
                    $(".change_on_time").hide();
                }

                $("#attendanceStatus").val(status);
            }

            var url = window.location.href;
            $("#change_url").val(url);

            $("#modal-changeStatus").modal('show');
        });


        $("#attendanceStatus").on('change',function(){
            if($(this).val() == 'Absent'){
                $(".change_on_time").hide();
            }else if($(this).val() == 'Present'){
                $(".change_on_time").show();
            }else if($(this).val() == 'Holiday'){
                $(".change_on_time").hide();
            }else if($(this).val() == 'Week-Off'){
                $(".change_on_time").hide();
            }
        });

        $(".verifyMonthAttendance").on('click',function(){

            var allowAttendanceVerification = "{{$allowAttendanceVerification}}";
            var user_id = $(this).data('userid');
            var manager_id = $(this).data('managerid');
            var on_date = $(this).data('ondate');

            // if(allowAttendanceVerification == '0'){
            //alert("One or more date(s) of the selected month do not have a status. Please check and add status first.");
            // return false;
            // }else{
            if(!confirm("Are you sure you want to verify the attendance of the selected month? Once it is verified you cannot change the attendance status.")){
                return false;
            }else{
                $.ajax({
                    type: 'POST',
                    url: "{{ url('attendances/verify-month-attendance') }}",
                    data: {user_id: user_id, manager_id: manager_id, on_date: on_date},
                    success: function(result){
                        if(result.error){
                            swal( result.error)
                        }else{
                            // console.log(result.success);
                            // swal( result.success);
                            swal( "Attendance verified successfully", 'success')
                            location.reload(true);
                        }
                    }
                });
            }
            // }
        });

        function changeFunc() {

            var selectBox = document.getElementById("attendanceStatusLeave");
            var selectedValue = selectBox.options[selectBox.selectedIndex].value;

            if(selectedValue == 1 ||  selectedValue == 2){
                $('#cas_sick').show();
                $('#pat_mat').hide();
                $('#totalDays').hide();
            }

            if(selectedValue == 7 || selectedValue == 4){
                $('#cas_sick').hide();
                $('#pat_mat').show();
                $('#totalDays').show();
            }

        }

        $('.paternity-leave').on('change', function(){

            allowFormSubmit.paternity = 1;

            var fromDate = $("#leave_date").val();
            var toDate = $("#paternity-to-date").val();

            var date1 = fromDate;
            var date2 = new Date(toDate);

            var d = new Date(date2);
            date2 = [
                d.getFullYear(),
                ('0' + (d.getMonth() + 1)).slice(-2),
                ('0' + d.getDate()).slice(-2)
            ].join('-');

            var date1 = new Date(date1);
            var date2 = new Date(date2);
            var timeDiff = Math.abs(date2.getTime() - date1.getTime());
            var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));

            var dayMilliseconds = 1000 * 60 * 60 * 24;
            var weekendDays = 0;
            while (date1 <= date2) {
                var day = date1.getDay()
                if (day == 0) {
                    weekendDays++;
                }
                date1 = new Date(+date1 + dayMilliseconds);
            }

            var totalDays = diffDays + 1 - weekendDays;
            $('#numberOfDays').val(totalDays);

            if(totalDays != 15) {
                allowFormSubmit.paternity = 0;
                document.getElementById("addLeaveButton").disabled = true;
                // alert("You cannot take paternity leave for more or less than 15 days.");
                document.getElementById("errorNumberOfDays").innerHTML
                    = "You cannot take paternity leave for more or less than 15 days.";
            }else{
                document.getElementById("errorNumberOfDays").innerHTML = '';
                document.getElementById("addLeaveButton").disabled = false;
            }
        });

        $("#addLeaveButton").click(function() {
            if(allowFormSubmit.paternity == 0 ){
                document.getElementById("errorNumberOfDays").innerHTML
                    = "You cannot take paternity leave for more or less than 15 days.";
                return false;
            }
        });


    </script>

@endsection
