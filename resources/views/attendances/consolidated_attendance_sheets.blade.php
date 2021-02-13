@extends('admins.layouts.app')

@section('content')

<link rel="stylesheet" href="{{asset('public/admin_assets/plugins/dataTables/jquery.dataTables.min.css')}}">

<style>
i.fa.fa-download {
    font-size: 19px;
    vertical-align: middle;
    margin-top: 5px;
}
i.fa.fa-calendar {
    font-size: 18px;
    vertical-align: middle;
}
strong {
  font-weight: 900;
}
</style>

<!-- Content Wrapper. Contains page content -->

  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">

      <h1>Consolidated Attendance Report</h1>

      <ol class="breadcrumb">
        <li><a href="{{url('employees/dashboard')}}"><i class="fa fa-dashboard"></i> Home</a></li>
      </ol>

    </section>


 @if(session()->has('error_msg'))

    <div class="alert alert-danger alert-dismissible">

      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>

      {{ session()->get('error_msg') }}

    </div>

  @endif


    <!-- Main content -->

    <section class="content">

      <!-- Small boxes (Stat box) -->

      <div class="row">
        <div class="col-md-12">
          <div class="box box-primary">
              <form id="filters" class="search-field-box" method="GET">
                  <div class="row select-detail-below">
                    <div class="col-md-3 attendance-column1">
                        <div class="form-group">
                            <label>Year<sup class="ast">*</sup></label>
                            <select class="form-control input-sm basic-detail-input-style" id="year" name="year">
                                <option value="" disabled>Please select Year</option>
                                @for($year = date("Y"); $year >=2020; $year--)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 attendance-column2">
                        <div class="form-group">
                            <label>Month<sup class="ast">*</sup></label>
                            <select class="form-control input-sm basic-detail-input-style" id="month" name="month">
                                <option value="" disabled>Please select Month</option>
                                <option value="1">Dec-Jan</option>
                                <option value="2">Jan-Feb</option>
                                <option value="3">Feb-Mar</option>
                                <option value="4">Mar-Apr</option>
                                <option value="5">Apr-May</option>
                                <option value="6">May-Jun</option>
                                <option value="7">Jun-Jul</option>
                                <option value="8">Jul-Aug</option>
                                <option value="9">Aug-Sep</option>
                                <option value="10">Sep-Oct</option>
                                <option value="11">Oct-Nov</option>
                                <option value="12">Nov-Dec</option>
                            </select>
                        </div>
                    </div>
					  <div class="col-md-3 attendance-column1">
                        <div class="form-group">
                            <label>Zone</label>
                            <select class="form-control input-sm basic-detail-input-style" name="department" id="department">
                                @if(!$data['departments']->isEmpty())
                                @foreach($data['departments'] as $department)
                                  <option value="{{$department->id}}" @if($department->id == $req['department']){{'selected'}}@elseif($department->id == $data['user_department'] && empty($req['department'])){{'selected'}}@endif>{{$department->name}}</option>
                                @endforeach
                                @endif
                                <option value="0">All</option>
                            </select>
                        </div>
                    </div>


                    <div class="col-md-3 attendance-column4">
                        <div class="form-group">
                            <label>Project<sup class="ast">*</sup></label>
                            <select class="form-control input-sm basic-detail-input-style" name="project">
                                <option value="" disabled>Please select Project</option>
                                @if(!$data['projects']->isEmpty())
                                @foreach($data['projects'] as $project)
                                  <option value="{{$project->id}}" @if($project->id == $req['project']){{'selected'}}@endif>{{$project->name}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                  </div>

                  <div class="row select-detail-below2">
                    <!-- <div class="col-md-3 attendance-column1">
                        <div class="form-group">
                            <label>Employee Name</label>
                            <input type="text" class="form-control input-sm basic-detail-input-style" name="employeeName" placeholder="Employee Name">
                        </div>
                    </div>
                    <div class="col-md-3 attendance-column2">
                        <div class="form-group">
                            <label>Employee Code</label>
                            <input type="text" class="form-control input-sm basic-detail-input-style" name="employeeName" placeholder="Employee Name">
                        </div>
                    </div> -->


                    <div class="col-md-3 attendance-column2">
                        <div class="form-group">
                            <label>Employee Status</label>
                            <select class="form-control input-sm basic-detail-input-style" name="employee_status" id="employee_status">
                                <option value="1">Active</option>
                                <option value="0">In-Active</option>
                            </select>
                        </div>
                    </div>

					<div class="col-md-3 attendance-column2">
                        <div class="form-group">
                            <label>Choose Records</label>
                            <select class="form-control input-sm basic-detail-input-style" name="emp_records" id="emp_records">
							<option value="Team" @if($req['emp_records']=="Team"){{'selected'}}@endif>Team</option>
                                <option value="Self"  @if($req['emp_records']=="Self"){{'selected'}}@endif>Self</option>
                            </select>
                        </div>
                    </div>


                  </div>
                  <div class="submit-btn-box">
                    <input type="submit" name="submit" value="search" class="btn submit-btn"><i class="fa fa search"></i>




					@if(is_array($employees) && !empty($req['submit']) && $employees && $data['isverified'] == 1)

						<!--<input type="submit" name="submit" value="export excel sheet" class="btn submit-btn"><i class="fa fa search"></i>-->

						<input type="submit" name="submit" value="Attendance sheet" class="btn submit-btn"><i class="fa fa search"></i>

					@endif
					@if(!is_array($employees) && !empty($req['submit']) &&  !$employees->isEmpty() && $data['isverified'] == 1)

						<!--<input type="submit" name="submit" value="export excel sheet" class="btn submit-btn"><i class="fa fa search"></i>-->

						<input type="submit" name="submit" value="Attendance sheet" class="btn submit-btn"><i class="fa fa search"></i>

					@endif

                  </div>
              </form>


             @if(!empty($req['year']))
             <!--<div class="constants-container">
                <div class="constants">
                  <div class="item alert alert-info">Department: <strong>{{@$data['department_name']}}</strong></div>
                  <div class="item alert alert-danger">Week-Offs: <strong>{{@$data['sundays']}}</strong></div>
                  <div class="item alert alert-warning">Holidays: <strong>{{@$data['holidays']}}</strong></div>
                  <div class="item alert alert-success">Workdays: <strong>{{@$data['workdays']}}</strong></div>
                </div>
             </div>-->
             @endif

              <div class="row">
                  <div class="col-md-12">
                    <!-- <p class="found-results">We Found Below <span><b>6</b></span> Results Related to your Search</p> -->
                      <table id="employeesList" class="table table-bordered table-striped hello">
                          <thead class="table-heading-style">
                              <tr>
                                <th class="text-center" rowspan="2">S.No.</th>
                                <th class="text-center" rowspan="2">User Id</th>
                                <th class="text-center" rowspan="2">Name</th>
								<th class="text-center" rowspan="2">State</th>
								<th class="text-center" rowspan="2">Verified</th>
                                <th class="text-center" colspan="7">Attendance</th>
                              </tr>
                              <tr>

                                <th class="text-center">Paid Leaves</th>
                                <th class="text-center">Unpaid Leaves</th>


                                <th class="text-center">Action</th>
                              </tr>
                          </thead>
                          <tbody class="attendance-table-body">

							@if(sizeof($employees)!=0)
                            @foreach($employees as $key => $value)

                              @php
                                $redirect_url = '?id='.$value->user_id.'&year='.$req['year'].'&month='.$req['month'];

                              @endphp
                              <tr>
                                <td>{{$loop->iteration}}</td>
                                <td title="export punches">{{@$value->employee_code}}</td>
                                <td title="view calendar">{{@$value->fullname}}</td>
                								<td title="view calendar">{{@$value->state_name}}</td>
                								<td title="view calendar">{{@$value->verification}}</td>
                                <td>{{$value->paid_leaves ?? '0'}}</td>
                                <td><strong>{{$value->unpaid_leaves ?? '0'}}</strong></td>


                                <td style="min-width: 200px">
                                    <div class="constants-container">
                                        <a title="View Attendance & Verify" class="m-l-xs m-r-xs" target="_blank" href="{{url('attendances/view').$redirect_url}}">
                                            <button class="btn btn-info btn-xs">
                                                <i class="fa fa-calendar fa-sm" aria-hidden="true"></i>
                                            </button>
                                        </a>
                                        <form method="post" action="{{ route('attendance.download') }}">
                                            @csrf
                                            <input type="hidden" value="{{ $value->user_id }}" name="user_id">
                                            <input type="hidden" value="{{ $req['year'] }}" name="year">
                                            <input type="hidden" value="{{ $req['month'] }}" name="month">
                                            <button type="submit" class="btn btn-success btn-xs m-l-xs m-r-xs" ><i class="fa fa-download fa-xs" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </div>
                                <div>
                                  <!--@if($value->isverified)
                                    <span class="label label-success">Verified</span>
                                  @else
                                    <span class="label label-danger">Unverified</span>
                                  @endif -->
                                </div>
                                </td>
                              </tr>
                            @endforeach

							@endif
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>
        </div>
      </div>

      <!-- /.row -->

      <!-- Main row -->

    </section>

    <!-- /.content -->

  </div>

  <!-- /.content-wrapper -->


  <script src="{{asset('public/admin_assets/plugins/dataTables/jquery.dataTables.min.js')}}"></script>
  <script src="{{asset('public/admin_assets/plugins/validations/jquery.validate.js')}}"></script>
<script src="{{asset('public/admin_assets/plugins/validations/additional-methods.js')}}"></script>
  <script type="text/javascript">
    $("#filters").validate({
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

    var defYear = "{{$req['year']}}";
    var defMonth = "{{$req['month']}}";
    var defEmployeeStatus = "{{$req['employee_status']}}";
    var defAttendanceType = "{{$req['attendance_type']}}";

    var defDepartment = "{{$req['department']}}";
    if(defDepartment){
      $("#department").val(defDepartment);
    }

    if(defYear != '0'){
      $('#year').val(defYear);
    }else{
      defYear = "{{date('Y')}}";
      $('#year').val(defYear);
    }

    if(defMonth != '0'){
      $('#month').val(defMonth);
    }else{
      defMonth = "{{date('n')}}";
      $('#month').val(defMonth);
    }

    if(defEmployeeStatus){
      $("#employee_status").val(defEmployeeStatus);
    }

    if(defAttendanceType){
      $("#attendance_type").val(defAttendanceType);
    }
  </script>

  <script type="text/javascript">
    $('#employeesList').DataTable({
        "scrollX": true,
        responsive: true
      });
  </script>

  @endsection
