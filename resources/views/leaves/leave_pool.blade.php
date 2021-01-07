@extends('admins.layouts.app')

@section('style')
    <link rel="stylesheet" href="{{asset('public/admin_assets/plugins/dataTables/jquery.dataTables.min.css')}}">
@endsection
@section('content')
    <link rel="stylesheet" href="{{asset('public/admin_assets/plugins/dataTables/jquery.dataTables.min.css')}}">
    <style>
        #filterFormSubmit {
            margin-top: 2%;
        }
    </style>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Employees List
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
                <div class="box">
                    <div class="box-header">
                        <form id="employee_Attendance" method="GET" action="{{ route('leave.pool.index') }}">
                            <div class="row select-detail-below">
                                <div class="col-md-2 attendance-column1">
                                    <label>Year<sup class="ast">*</sup></label>
                                    <select class="form-control input-sm basic-detail-input-style" id="year" name="year" required>
                                        <option value="" selected disabled>Please select Year</option>
                                        <option value="2021" {{ (isset($_REQUEST["year"]) && $_REQUEST["year"] == 2021) ? 'selected' : ''}}>2021</option>
                                        <option value="2020" {{ (isset($_REQUEST["year"]) && $_REQUEST["year"] == 2020) ? 'selected' : ''}}>2020</option>
                                        <option value="2019" {{ (isset($_REQUEST["year"]) && $_REQUEST["year"] == 2019) ? 'selected' : ''}}>2019</option>
                                        <option value="2018" {{ (isset($_REQUEST["year"]) && $_REQUEST["year"] == 2018) ? 'selected' : ''}}>2018</option>
                                        <option value="2017" {{ (isset($_REQUEST["year"]) && $_REQUEST["year"] == 2017) ? 'selected' : ''}}>2017</option>
                                        <option value="2016" {{ (isset($_REQUEST["year"]) && $_REQUEST["year"] == 2016) ? 'selected' : ''}}>2016</option>
                                    </select>
                                </div>

                                <div class="col-md-2 attendance-column2">
                                    <label>Month<sup class="ast">*</sup></label>
                                    <select class="form-control input-sm basic-detail-input-style" id="month" name="month" required>
                                        <option value="" selected disabled>Please select Month</option>
                                        <option value="1" {{ (isset($_REQUEST["year"]) && $_REQUEST["month"] == 01) ? 'selected' : ''}}>Dec-Jan</option>
                                        <option value="2" {{ (isset($_REQUEST["year"]) && $_REQUEST["month"] == 02) ? 'selected' : ''}}>Jan-Feb</option>
                                        <option value="3" {{ (isset($_REQUEST["year"]) && $_REQUEST["month"] == 03) ? 'selected' : ''}}>Feb-March</option>
                                        <option value="4" {{ (isset($_REQUEST["year"]) && $_REQUEST["month"] == 04) ? 'selected' : ''}}>Mar-Apr</option>
                                        <option value="5" {{ (isset($_REQUEST["year"]) && $_REQUEST["month"] == 05) ? 'selected' : ''}}>Apr-May</option>
                                        <option value="6" {{ (isset($_REQUEST["year"]) && $_REQUEST["month"] == 06) ? 'selected' : ''}}>May-June</option>
                                        <option value="7" {{ (isset($_REQUEST["year"]) && $_REQUEST["month"] == 07) ? 'selected' : ''}}>June-Jul</option>
                                        <option value="8" {{ (isset($_REQUEST["year"]) && $_REQUEST["month"] == "08") ? 'selected' : ''}}>Jul-Aug</option>
                                        <option value="9" {{ (isset($_REQUEST["year"]) && $_REQUEST["month"] == "09") ? 'selected' : ''}}>aug-Sep</option>
                                        <option value="10" {{ (isset($_REQUEST["year"]) && $_REQUEST["month"] == 10) ? 'selected' : ''}}>sep-Oct</option>
                                        <option value="11" {{ (isset($_REQUEST["year"]) && $_REQUEST["month"] == 11) ? 'selected' : ''}}>Oct-Nov</option>
                                        <option value="12" {{ (isset($_REQUEST["year"]) && $_REQUEST["month"] == 12) ? 'selected' : ''}}>Nov-Dec</option>
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

                        <table id="employeesList" class="table table-bordered table-striped">
                            <thead class="table-heading-style">
                            <tr>
                                <th>S.No.</th>
                                <th>User Id</th>
                                <th>Name</th>
                                <th>Balance Casual Leave</th>
                                <th>Balance Sick Leave</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($allEmployees as $key=>$value)
                                <tr>
                                    <td>{{@$loop->iteration}}</td>
                                    <td>{{$value['employee']->employee_code}}</td>
                                    <td>{{$value['employee']->fullname}}</td>
                                    <td>
                                        {{ isset($value['leaveDetail']) ?  $value['leaveDetail']->accumalated_casual_leave : '-' }}
                                    <td>
                                        {{ isset($value['leaveDetail']) ?  $value['leaveDetail']->accumalated_sick_leave : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot class="table-heading-style">
                            <tr>
                                <th>S.No.</th>
                                <th>User Id</th>
                                <th>Name</th>
                                <th>Balance Casual Leave</th>
                                <th>Balance Sick Leave</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('extra_foot')
    <script src="{{asset('public/admin_assets/plugins/dataTables/jquery.dataTables.min.js')}}"></script>
@endsection
