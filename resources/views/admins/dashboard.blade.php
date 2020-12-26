@extends('admins.layouts.app')
@section('content')
    <link rel="stylesheet" href="{{asset('public/admin_assets/dist/css/mobiscroll.javascript.min.css')}}">
    <script src="{{asset('public/admin_assets/dist/js/mobiscroll.javascript.min.js')}}"></script>
    <link rel="stylesheet" href="{{asset('public/admin_assets/bower_components/fullcalendar/dist/fullcalendar.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/admin_assets/bower_components/fullcalendar/dist/fullcalendar.print.min.css')}}" media="print">

    <!-- Content Wrapper. Contains page content -->

    <style type="text/css">

        .cal_toolbar_dashboard {margin-bottom: 0px !important;padding-top: 0px;}

        #calendar_dash{ margin-top:0px!important;}

        .draggable_status {
            cursor: initial !important;
            padding-left: 3px !important;
            padding-right: 5px !important;
            margin-left: 7px;
            margin-bottom: 2px;
        }

        .text-muted {color: #000; font-weight: 700;}

        .profile-user-img {
            width: 130px;
            height: 130px;
            padding: 3px;
            border: 3px solid #d2d6de;
            margin: 10px auto;
        }
        .dashboardProfile {
            background-color: white;
            min-height: 295px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border: 1px solid #3c8dbc;
            border-radius: 4px;
        }
        .datepicker .datepicker-switch:hover, .datepicker .next:hover, .datepicker .prev:hover, .datepicker tfoot tr th:hover {
            background: #3c8dbc !important;
            color: white;
            transition: all ease 0.2s;
        }
        .btn-success { background-color: #f39c12 !important;}

        .info-box-content.event{ padding: 24px 4px;}

        div#upcomingEvents {background: #00c0ef !important; color: #fff; }

        .mbsc-padding.md-header {
            font-size: 1.25em;
            font-weight: 400;
            padding: .8em .8em .4em .8em;
        }
        .md-dateslider .mbsc-padding {
            text-align: center;
            padding-bottom: 16px !important;
        }
        .md-check-cont {
            padding: 8px 0 16px 0;
        }
        .md-check {
            display: inline-block;
            width: 32px;
            height: 32px;
            font-size: 26px;
            padding: .2em;
            line-height: 23px;
            border-radius: 33px;
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
        }
        .mbsc-padding.md-header {
            font-size: 1.25em;
            font-weight: 400;
            padding: .8em .8em .4em .8em;
        }
        .md-dateslider .mbsc-padding {
            text-align: center;
        }
        .md-check-cont {
            padding: 8px 0 16px 0;
        }
        .md-check {
            display: inline-block;
            width: 32px;
            height: 32px;
            font-size: 26px;
            padding: .2em;
            line-height: 23px;
            border-radius: 33px;
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
        }
        .demo-theme-mobiscroll .md-check {
            background: #4ECCC4;
            color: #f7f7f7;
        }
        .demo-theme-mobiscroll-dark .md-check {
            background: #4ECCC4;
            color: #263238;
        }
        .demo-theme-material .md-check {
            background: #009688;
            color: #eee;
        }
        .demo-theme-material-dark .md-check {
            background: #81ccc4;
            color: #f7f7f7;
        }
        .demo-theme-ios .md-check {
            background: #1272dc;
            color: #f5f5f5;
        }
        .demo-theme-ios-dark .md-check {
            background: #ff8400;
            color: #1a1a1a;
        }
        .demo-theme-android-holo .md-check {
            background: #31c6e7;
            color: #000;
        }
        .demo-theme-android-holo-light .md-check {
            background: #31c6e7;
            color: #f5f5f5;
        }
        .demo-theme-wp .md-check {
            background: #1a9fe0;
            color: #000;
        }
        .demo-theme-wp-light .md-check {
            background: #1a9fe0;
            color: #fff;
        }
        .mbsc-form-group-inset {
            margin: 0px;
        }
        .mbsc-ios.mbsc-form {
            background: #fff;
            color: #000;
        }
        .mbsc-ios.mbsc-progress {
            min-height: 0px !important;
            padding: 0px !important;
        }
        .mbsc-mobiscroll.mbsc-page{
            margin-left: 10px;
        }
        .col-sm-6.eventCalendar {
            padding-left: 7px;
            padding-right: 10px;
        }
        .col-sm-3.attendanceCalendar {
            padding-right: 0px;
        }
        .mbsc-padding.md-header.mbsc-control-w.mbsc-input {
            margin-top: 0px;
            margin-bottom: 0px;
        }
        .todo-list{
            padding-bottom: 12px;
        }
        .col-sm-3.dashboardProfileCol {
            background: #fff;
        }
        .calendar-bg {
            border: 1px solid #3c8dbc !important;
        }
        .box.box-solid > .box-header .btn:hover, .box.box-solid > .box-header a:hover {
            color: #3c8dbc;
            transition: ease 0.2s;
        }
        div#calendar_dashboard td, div#calendar_dashboard th {
            padding: 8px;
        }
        div#calendar_dashboard td:hover {
            background-color: rgb(210, 214, 222);
        }
        h2 {
            font-size: 15px;
            margin: 8px;
        }
        td.day.vip_day {
            border: 1px solid #3c8dbc !important;
            color: #3c8dbc;
            border-radius: 0px !important;
        }
        td.day.vip_day:hover {
            background-color: #3c8dbc !important;
            color: white;
            transition: all ease 0.2s;
        }
        .vip_day {
            position: relative;
        }

        .vip_day .tooltiptext {
            visibility: hidden;
            width: 120px;
            background-color: black;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 10px 5px;
            position: absolute;
            z-index: 1;
            top: 125%;
            height: auto;
            line-height: 1;
            left: 50%;
            margin-left: -60px;
        }

        .vip_day .tooltiptext::after {
            content: "";
            position: absolute;
            bottom: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: transparent transparent black transparent;
        }

        .vip_day:hover .tooltiptext {
            visibility: visible;
        }

        .leave-detail-sec {
            padding: 15px 15px;
            border: 1px solid #3c8dbc;
            margin: 0 15px;
            border-radius: 8px;
            background-color: white;
        }
        .leave-detail-sec h3 {
            margin-top: 0px;
            font-size: 20px;
        }
        .card-header {
            display: flex;
            padding: 10px;
            margin: 0 10px;
            align-items: center;
            border-bottom: 1px solid #3c8dbc;
        }
        .card-header i {
            font-size: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 12px;
            color: #3c8dbc;
        }
        .card-header-content h3, .card-header-content p {
            margin: 0;
        }
        .card-header-content h3 {
            font-size: 16px;
        }
        .card-body {
            padding-bottom: 10px;
        }
        .card-body ul {
            list-style: none;
            padding-left: 0px;
        }
        .card-body ul li {
            margin: 15px 10px;
        }
        .image-box {
            display: flex;
        }
        .card-body ul li img {
            width: 30px;
            height: 30px;
            border: 1px solid #b2b1b0;
            border-radius: 100%;
            padding: 2px;
            margin-right: 5px;
        }
        .img-content h4 {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            color: #3c8dbc;
        }
        .img-content p {
            margin: 0;
        }
        .card-header img {
            margin-right: 15px;
            width: 50px;
            height: 50px;
        }
        .card-body.birthday_card_body ul li:not(:last-child) { border-bottom: 1px solid #3c8dbc; padding-bottom: 10px;}

        .border-info { border: 1px solid #00c0ef;}
        .border-danger { border: 1px solid #dd4b39;}
        .border-success { border: 1px solid #00a65a;}
        .border-warning { border: 1px solid #f39c12;}

        .border-info, .border-danger, .border-success, .border-warning  { min-height: 92px; }

        span.label{
            font-size: 0.9em;
            color: black;
        }
        .label-h5{ background-color: red; }
        .label-h4{ background-color: orange; }
        .label-h3{ background-color: aqua; }
        .label-h2{ background-color: yellow; }
        .label-h1{ background-color: #fffdd0; }
        .todo-list > li .text { display: inline; }

        .outer-border  {
            padding: 15px 15px;
            border: 1px solid #3c8dbc;
            margin: 0 15px;
            border-radius: 8px;
            background-color: white;
        }

        .outer-border h3 {
            padding-top: 0;
        }

        @media (max-width: 991px) {
            .dashboardProfile { margin-bottom: 15px; }
            /* .cal_leave_boxes td.fc-day-top, .fc-widget-header {width: 98px;} */
        }

        @media (min-width: 768px) {
            .modal-dialog { width: 400px;}
        }

        @media (max-width: 768px) {
            .fc-widget-header, .cal_leave_boxes td.fc-day-top { width: 90px; }
            #calendar_dash { overflow-x: auto; }
            .cal_leave_boxes { border: none !important;  }
        }

        @media (max-width: 470px) {
            .any-col-470 {
                width: 100%;
                margin-left: 0;
            }
        }

    </style>



    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            @if(session()->has('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session()->get('error') }}
                </div>
            @endif

            @if(session()->has('profileSuccess'))

                <div class="alert alert-success alert-dismissible">

                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>

                    {{ session()->get('profileSuccess') }}

                </div>

            @endif

            @include('admins.validation_errors')
            <h1>
                Dashboard
                <small>Control panel</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Dashboard</li>
            </ol>
        </section>
    @php


        if($user->employee->profile_picture){

          $profile_picture = config('constants.uploadPaths.profilePic').$user->employee->profile_picture;

        }else{

          $profile_picture = config('constants.static.profilePic');

        }



    @endphp


    <!-- Main content -->
        <section class="content">

            @can('edit-employee')
                <div class="row date-container">
                    <form action="{{ url('employees/get_missed_punch_data') }}" method="get" enctype="multipart/form-data" target="_blank" id="missed_punch_Form">
                        <div class="col-lg-4 col-md-4 col-sm-6">
                            <div class="form-group">
                                <label for="miss_punch_date">Date:</label>
                                <div class="input-group">
                                    {{ csrf_field() }}
                                    @php

                                        $current_date = date("Y-m-d");
                                    @endphp
                                    <input type="text" name="miss_punch_date" class="form-control datepick" value="{{ $current_date}}" required>
                                    <span class="input-group-btn">
                <button type="submit" class="btn btn-info btn-flat" id="submit3" name="submit_date">View</button>
              </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <a  target="_blank" href="{{ url('employees/get_missed_punch_today') }}">{{$missed_punch_count}} Missed Punches Today</a>
                            </div>
                        </div>
                    </form>
                </div>
            @endcan

            <div class="row">
                <div class="col-sm-12">
                    <div class="row">
                        <!-- col-3 starts here -->
                        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6 col-lg-offset-0 col-md-offset-0 col-sm-offset-3 col-xs-offset-3 any-col-470">
                            <div class="dashboardProfile">
                                <img class="profile-user-img img-responsive img-circle" src="{{@$profile_picture}}" alt="User profile picture">
                                <h2 class="text-muted text-center">{{@$user->designation[0]->name}}</h2>
                                <p class="text-center text-primary" style="font-size: 15px; margin-top: 3px;">Name: {{@$user->employee->fullname}}</p>
                                <p class="text-center text-primary" style="font-size: 14px; margin-top: 2px;">Employee code: {{$user->employee_code}}</p>
                            </div>
                        </div>
                        <!-- col-3 ends here -->

                        <!-- col-9 starts here -->
                        <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 col-lg-offset-0 col-md-offset-0 col-sm-offset-0 col-xs-offset-0">
                            <div class="box box-primary">
                            <?php
                            $monthNames = Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");

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

                            ?>

                            <!-- Calender starts here -->

                                <div class="box-body no-padding">
                                    <div class="emp-name-and-color">
                                        <div class="attendance-guide">
                                            <ul>
                                                <li class="attendance-rectangle holiday-only">H- Holiday</li>
                                                <li class="attendance-rectangle present-only">P- Present</li>
                                                <li class="attendance-rectangle absent-only">WO- Week-Off</li>
                                                <li class="attendance-rectangle holiday-only">L- Leave</li>
                                                <li class="attendance-rectangle absent-only">A- Absent</li>
                                            </ul>
                                        </div>
                                        <div class="a-last-absent">
                                            <h3 class="a-employe-name">
                                                <?php
                                                $employee = getEmployeeProfileData(Auth::user()->id);
                                                ?>

                                                @if(!empty($employee))
                                                    {{ $employee->fullname }} - {{@$user->designation[0]->name}}
                                                @endif
                                            </h3>
                                            <!-- <span class="a-last-absent-span1">Last Absent:</span>
                                            <span class="a-last-absent-span2">25 Days Ago</span> -->
                                        </div>
                                    </div>

                                    <!-- THE CALENDAR -->
                                    <div id="calendar_dash" class="fc fc-unthemed fc-ltr">
                                        <div class="fc-toolbar fc-header-toolbar cal_toolbar_dashboard">
                                            <div class="fc-left">
                                                <div class="fc-button-group nav_class">
                                                    <a href="<?php echo url()->current() . "?month=". $prev_month . "&year=" . $prev_year; ?>"><button type="button" class="fc-prev-button fc-button fc-state-default fc-corner-left" aria-label="prev"><span class="fc-icon fc-icon-left-single-arrow"></span></button></a>
                                                    <a href="<?php echo url()->current() . "?month=". $next_month . "&year=" . $next_year; ?>"><button type="button" class="fc-next-button fc-button fc-state-default fc-corner-right" aria-label="next"><span class="fc-icon fc-icon-right-single-arrow"></span></button></a>
                                                </div>
                                                <!-- <button type="button" class="fc-today-button fc-button fc-state-default fc-corner-left fc-corner-right fc-state-disabled" disabled="">today</button> -->
                                            </div>
                                            <div class="fc-center">
                                                <h2>
                                                    @if($cMonth == 1)
                                                        <?php echo $monthNames[11]."-".$monthNames[$cMonth - 1]." ".$cYear; ?>
                                                    @else
                                                        <?php echo $monthNames[$cMonth-2]."-".$monthNames[$cMonth - 1]." ".$cYear; ?>
                                                    @endif
                                                </h2>
                                            </div>
                                            <div class="fc-clear"></div>
                                        </div>


                                        <div class="fc-view-container" style="">
                                            <div class="fc-view fc-month-view fc-basic-view" style="">
                                                <table class="table-responsive cal_leave_boxes">
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
                                                            <div class="fc-scroller fc-day-grid-container" style="min-height: 190px;">
                                                                <div class="fc-day-grid fc-unselectable">
                                                                    <div class="fc-row fc-week fc-widget-content" style="height: 96px;">
                                                                        <div class="fc-bg cal_table_bg">
                                                                            <table>
                                                                                <thead>

                                                                                <?php
                                                                                $timestamp = mktime(0,0,0,$cMonth,1,$cYear);
                                                                                $maxday = date("t",$timestamp);
                                                                                $thismonth = getdate ($timestamp);
                                                                                $startday = $thismonth['wday'];

                                                                                if (!isset($_REQUEST["month"])) $_REQUEST["month"] = date("n");
                                                                                if (!isset($_REQUEST["year"])) $_REQUEST["year"] = date("Y");

                                                                                $curr_month = $_REQUEST["month"];
                                                                                $curr_year = $_REQUEST["year"];

                                                                                if($curr_month==1){
                                                                                    $start_year= $curr_year-1;
                                                                                    $startmonth = 12;
                                                                                }else{
                                                                                    $start_year= $curr_year;
                                                                                    $startmonth = $curr_month-1;
                                                                                }
                                                                                $date1 = $start_year.'-'.$startmonth.'-'.'26';
                                                                                $date2 = $curr_year.'-'.$curr_month.'-'.'25';
                                                                                $timestamp = strtotime($date1);
                                                                                $thismonth = getdate ($timestamp);
                                                                                $startday = $thismonth['wday'];

                                                                                $i=0;

                                                                                while (strtotime($date1) <= strtotime($date2)) {
                                                                                $day = $i - $startday + 1;
                                                                                $cDayTime = $cYear.'-'.$cMonth.'-'.$day;

                                                                                //if(strtotime(date("Y-m-d")) >= mktime(0,0,0,$cMonth,$day,$cYear)){
                                                                                $attendanceArray = getAttendanceInfo($date1, $user->id);

                                                                                //}

                                                                                $status = $attendanceArray['status'];

                                                                                if(($i % 7) == 0 ){
                                                                                // if(empty($status)){
                                                                                //   $status = "Week-Off";
                                                                                // }else{
                                                                                //$status = "Week-Off";
                                                                                //}
                                                                                $attendanceArray['secondary_leave_type'] = "";
                                                                                ?>
                                                                                <tr>

                                                                                    <?php }if($i < $startday) { ?>
                                                                                    <td class="fc-day-top fc-sun fc-other-month fc-past" data-date=""></td>

                                                                                    <?php }else{ ?>
                                                                                    <td class="fc-day-top fc-sun fc-future attendance-tds" data-date="2019-09-01">
                                                                                        <?php $date_month = Date('d M',strtotime($date1)); ?>
                                                                                        <span class="fc-day-number"><?php echo $date_month; ?></span>
                                                                                        <div class="three-icon-box">

                                                                                            @if(!empty($attendanceArray['description']))
                                                                                                <div class="info-tooltip tooltip_class">
                                                                                                    <i class="fa fa-info-circle a-icon1"></i>
                                                                                                    <span class="info-tooltiptext">{{$attendanceArray['description']}}</span>
                                                                                                </div>
                                                                                            @endif


                                                                                            <?php if($status) { ?>
                                                                                            <div class="leave-type-onnly">
                                                                                                <a class="fc-day-grid-event fc-h-event fc-event fc-start fc-end draggable_status cal_leave_custom @if($status == 'Present'){{'calender-day-present'}}@elseif($status == 'Holiday'){{'calender-day-holiday'}}@elseif($status == 'Absent' || $status == 'Week-Off'){{'calender-day-absent'}}@endif">
                                                                                                    <span>
                                                                                                         <?php
                                                                                                        if($status == 'Absent'){
                                                                                                            $status = "A";
                                                                                                        }elseif($status == 'Week-Off'){
                                                                                                            $status = "WO";
                                                                                                        }elseif($status == 'Holiday'){
                                                                                                            $status = "H";
                                                                                                        }elseif($status == 'Present'){
                                                                                                            $status = "P";
                                                                                                        }
                                                                                                        echo  $status;
                                                                                                        ?>
                                                                                                  </span>
                                                                                                </a>
                                                                                            </div>

                                                                                            <?php } ?>
                                                                                            @if(@$user->designation[0]->id == 3)
                                                                                                {{--                                                                                                    @if($status == 'Leave')--}}
                                                                                                {{--                                                                                                    {{ $status }}--}}
                                                                                                {{--                                                                                                    @endif--}}
                                                                                                @if(strtotime($date1) <= strtotime(date("Y-m-d")))
                                                                                                    @if($status != 'Leave')

                                                                                                        <div class="status-tooltip" data-date="{{date('d-m-Y',strtotime($date1))}}" data-userid="{{$user->id}}" data-status="{{$status}}">
                                                                                                            <!--<i class="fa fa-clock-o a-icon5"></i>-->
                                                                                                            <i class="fa fa-user-times" aria-hidden="true"></i>
                                                                                                            <span class="status-tooltiptext">Change Status</span>
                                                                                                        </div>
                                                                                                    @endif
                                                                                                @endif
                                                                                            @endif

                                                                                        </div>
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

                                                                                {{--                                                                                </thead>--}}
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


                                            <!-- Calender ends here -->
                                            <!--script for calender starts here-->
                                            <script src="{{asset('public/admin_assets/plugins/validations/jquery.validate.js')}}"></script>
                                            <script src="{{asset('public/admin_assets/plugins/validations/additional-methods.js')}}"></script>

                                            <script type="text/javascript">
                                                $(document).ready(function(){
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


                                                });
                                            </script>


                                            <!--script for calender ends here..-->
                                        </div>
                                    </div>
                                    <!-- /.box-body -->
                                </div>
                                <!-- Calender starts here -->
                            </div>
                        </div>
                    </div>


                    <div class="modal fade" id="birthday_modal">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close attendance-close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title text-center">List of Employee Birthday's&nbsp;&nbsp;<span class="user-remarks"></span></h4>
                                </div>
                                <div class="modal-body">
                                    <div class="card-body birthday_card_body">
                                        <ul>
                                            @foreach($birthdays as $birthday)
                                                @php

                                                    if($birthday->profile_picture){

                                                      $profile_pic =config('constants.uploadPaths.profilePic').$birthday->profile_picture;

                                                    }else{

                                                      $profile_pic = config('constants.static.profilePic');

                                                    }
                                                @endphp
                                                <li>
                                                    <div class="image-box">
                                                        <img src="{{@$profile_pic}}" alt="">
                                                        <div class="img-content">
                                                            <h4>{{$birthday->fullname}}</h4>
                                                            <p><small><label class="label label-danger">{{date("d-M. ", strtotime($birthday->birth_date))}}</label></small></p>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach

                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <!-- col-9 ends here -->
                        </div>
                    </div>
                </div>
        </section>

        @if($status != 'Leave')
            <section>
                <!-- Leave detail starts here -->
                <form method="post" action="{{ route('mark.attendance') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-6 any-col-470">
                            <div class="outer-border text-center">
                                <h3>Attendance</h3>
                                <button class="btn btn-danger" type="submit" name="type" value="checkin" style="cursor: pointer;">

                                    Check In
                                </button>

                                {{--                            <button class="btn btn-danger" onclick="window.location.href='/employees/dashboard?type=checkin';" title="Click to checkin" style="cursor: pointer;" style="padding: 20px 50px;">Check In</button>--}}
                                <button class="btn btn-info hide" onclick="window.location.href='/employees/dashboard?type=checkout';" title="Click to checkout" style="cursor: pointer;" style="padding: 20px 50px;">Check Out</button>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-6 any-col-470">
                            <div class="outer-border text-center">
                                <h3 class="text-center">Mark Your Days Off</h3>
                                <button class="btn btn-danger" type="submit" name="status" value="Holiday" style="cursor: pointer;">Holiday</button>
                                <button class="btn btn-info" type="submit" name="status" value="Week-Off" style="cursor: pointer;">Week-off</button>

                                {{--                            <button class="btn btn-danger" onclick="window.location.href='/attendances/change-offs-status?status=Holiday';" title="Click to add Holiday" style="cursor: pointer;" style="padding: 20px 50px;">Holiday</button>--}}
                                {{--                            <button class="btn btn-info" onclick="window.location.href='/attendances/change-offs-status?status=Week-Off';" title="Click to add Week-Off" style="cursor: pointer;" style="padding: 20px 50px;">Week-off</button>--}}
                            </div>
                        </div>
                    </div>
                </form>
                <!-- Leave detail ends here -->
            </section>
        @else
            <div>
                <h3>Today Your Mark Attendance Is Leave</h3>
            </div>
        @endif
        <section class="leave-detail-sec hide">
            <!-- Leave detail starts here -->
            <div class="row">
                <h3 class="text-center">Leave Details</h3>
                <div class="col-md-12">
                    <div class="outer-border">
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="info-box border-info">
                                <span class="info-box-icon bg-aqua"><i class="fa fa-list"></i></span>

                                <div class="info-box-content">
                                    <span class="info-box-text">Total Leaves</span>
                                </div>
                                <!-- /.info-box-content -->
                            </div>
                            <!-- /.info-box -->
                        </div>
                        <!-- /.col -->
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="info-box border-danger">
                                <span class="info-box-icon bg-red"><i class="fa fa-list"></i></span>

                                <div class="info-box-content">
                                    <span class="info-box-text">Balance Leave</span>

                                </div>
                                <!-- /.info-box-content -->
                            </div>
                            <!-- /.info-box -->
                        </div>
                        <!-- /.col -->

                        <!-- fix for small devices only -->
                        <div class="clearfix visible-sm-block"></div>

                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="info-box border-success">
                                <span class="info-box-icon bg-green"><i class="fa fa-list"></i></span>

                                <div class="info-box-content">
                                    <span class="info-box-text">Paid Leaves</span>
                                    <span class="info-box-number">{{@$probation_data->paid_count}}</span>
                                </div>
                                <!-- /.info-box-content -->
                            </div>
                            <!-- /.info-box -->
                        </div>
                        <!-- /.col -->
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="info-box border-warning">
                                <span class="info-box-icon bg-yellow"><i class="fa fa-list"></i></span>

                                <div class="info-box-content">
                                    <span class="info-box-text">Unpaid Leaves</span>
                                    <span class="info-box-number">{{@$probation_data->unpaid_count}}</span>
                                </div>
                                <!-- /.info-box-content -->
                            </div>
                            <!-- /.info-box -->
                        </div>
                        <!-- /.col -->
                    </div>
                </div>
            </div>
            <!-- Leave detail ends here -->
        </section>

        <section class="content hide">
            <div class="row">
                <div class="col-sm-12">
                    <div class="row">

                        <div class="col-sm-6">
                            <div class="box box-primary">
                                <div class="card">
                                    <div class="card-header">
                                        <img src="{{asset('public/uploads/dashboard/birthday_gif_1.gif')}}" alt="birthday Image">
                                        <div class="card-header-content">
                                            <h3>Happy Birthday</h3>
                                            <p>List of employees whose birthdays are in the next <b>30</b> Days</p>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <ul>
                                            <li>
                                                <div class="row">
                                                    @php
                                                        $count=0;
                                                    @endphp
                                                    @foreach($birthdays as $birthday)
                                                        @php
                                                            if($birthday->profile_picture){

                                                              $prof_pic =config('constants.uploadPaths.profilePic').$birthday->profile_picture;

                                                            }else{

                                                              $prof_pic = config('constants.static.profilePic');

                                                            }
                                                            if($count<4){ @endphp

                                                        <div class="col-sm-6">
                                                            <div class="image-box">
                                                                <img src="{{@$prof_pic}}" alt="{{$birthday->fullname}}">
                                                                <div class="img-content">
                                                                    <h4>{{$birthday->fullname}}</h4>
                                                                    <p><label class="label label-danger">{{date("d-M. ", strtotime($birthday->birth_date))}}</label></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @php
                                                            }
                                                              $count++;

                                                              if($count%2==0 && $count<=4){ @endphp
                                                </div>
                                            </li><li><div class="row">

                                                    @php
                                                        }
                                                    @endphp

                                                    @endforeach

                                                </div>
                                            </li>

                                        </ul>
                                        <div class="text-center">
                                            <a href="#" class="btn btn-info btn-sm" data-toggle="modal" data-target="#birthday_modal">Show All</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="box box-primary">
                                <div class="card">
                                    <div class="card-header">
                                        <img src="{{asset('public/uploads/dashboard/holiday1.gif')}}" alt="birthday Image">
                                        <div class="card-header-content">
                                            <h3>Event Holidays</h3>
                                            <p>Check out Holiday List</p>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <ul>

                                            @foreach($holidays as $holiday)
                                                <li>
                                                    <div>
                                                        <div class="img-content" style="display: flex; justify-content: space-between;">
                                                            <h4>
                                                                <i class="fa fa-calendar-check-o" style="display: inline-block"></i>&nbsp;{{$holiday->name}}</h4>
                                                            <label class="label label-info">{{date("d-M.",strtotime($holiday->holiday_from))}}</label>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <div class="text-center">
                                            <a href="{{url('leaves/holidays')}}" class="btn btn-info btn-sm">All Holidays</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- /.content -->

    </div>

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

    <script type="text/javascript">

        var dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],

            monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],

            d = new Date(),

            diff = d.getDate() - d.getDay(),

            nextSunday = new Date(d.setDate(diff)),

            slider = document.getElementById('slider'),

            nextWeek = {};



        function setText(d) {

            document.querySelector('.md-date').innerHTML = monthNames[d.getMonth()] + " " + d.getDate();

        }



        slider

            .addEventListener('change', function (ev) {

                setText(nextWeek[Math.round(this.value)]);

            });





        mobiscroll.form('#demo', {

            theme: 'ios'

        });



        mobiscroll.slider('#slider', {

            theme: 'ios',

            onInit: function (event, inst) {

                var labels = slider.parentNode.querySelectorAll('.mbsc-progress-step-label');



                for (var i = 0; i < labels.length; ++i) {

                    nextWeek[Math.round(labels[i].innerHTML)] = new Date(nextSunday.getFullYear(), nextSunday.getMonth(), nextSunday.getDate() + i); // generate nextWeek object

                    labels[i].innerHTML = dayNames[i];

                }
                setText(nextSunday);
            }
        });

    </script>

    <!-- /.content-wrapper -->

@endsection
@section('extra_foot')
    <script type="text/javascript">

        $(".leave-tooltip").on('click',function(){

            var date = $(this).data('date');

            var user_id = $(this).data('userid');

            $("#leave_date").val(date);

            var leave_url = window.location.href;
            $("#change_url_leave").val(leave_url);


            $("#modal-addLeave").modal('show');
        });

        var defYear = "{{$_REQUEST["year"]}}";
        var defMonth = "{{$_REQUEST["month"]}}";

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

        function changeFunc() {

            var selectBox = document.getElementById("attendanceStatusLeave");
            var selectedValue = selectBox.options[selectBox.selectedIndex].value;
            if(selectedValue == 1 ||  selectedValue == 2){
                $('#cas_sick').show();
                $('#pat_mat').hide();

            }
            if(selectedValue == 7 || selectedValue == 4){
                $('#cas_sick').hide();
                $('#pat_mat').show();
            }

        }

    </script>

    <script type="text/javascript">
        $(function () {
            $('.datepick').datepicker({
                autoclose: true,
                orientation: "bottom",
                format: 'yyyy-mm-dd'
            });
        });
    </script>
@endsection
