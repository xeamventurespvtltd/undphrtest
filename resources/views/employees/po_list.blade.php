@extends('admins.layouts.app')

@section('content')
    <style>
        .heading2_form {
            font-size: 20px;
            text-decoration: underline;
            text-align: center;
        }
        .basic-detail-label {
            padding-right: 0px;
            padding-top: 4px;
        }
    </style>
    <link rel="stylesheet" href="{{asset('public/admin_assets/plugins/dataTables/jquery.dataTables.min.css')}}">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>PO List</h1>
        </section>

        <!-- Main content -->
        <section class="content">
            <!-- Small boxes (Stat box) -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="box box-primary">
                        <!-- form start -->
                        @include('admins.validation_errors')

                        <div class="box-body jrf-form-body">
                            <form id="salary_cycle" method="POST" action="{{ route('po.location.store') }}"
                                  autocomplete="off">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row field-changes-below">
                                            <div class="col-md-4 text-right">
                                                <label for="name"
                                                       class="basic-detail-label">Employee's List<span style="color: red">*</span></label>
                                            </div>
                                            <div class="col-md-4">
                                                <select name="user_id" id=""
                                                        class="form-control" required>
                                                    <option value="">Select
                                                        Employee</option>
                                                    @foreach($employees as $employee)
                                                        <option
                                                            value="{{$employee->user_id}}">{{$employee->fullname}} </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row field-changes-below">
                                            <div class="col-md-4 text-right">
                                                <label for="from"
                                                       class="basic-detail-label">Select Location<span style="color: red">*</span></label>
                                            </div>
                                            <div class="col-md-4">
                                                <select name="location_id" required
                                                        id="from" class="form-control input-sm basic-detail-input-style">
                                                    <option selected disabled>Select
                                                        Location</option>
                                                    @foreach($locations as $location)
                                                        <option value="{{ $location->id
                                                                                        }}">{{
                                                                                        $location->name
                                                                                        }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-md-6"></div>
                                </div>
                                <div class="box-footer create-footer text-center">
                                    <input type="submit" class="btn btn-primary" id="submit3" value="Add" name="submit">
                                </div>
                            </form>

                            {{--                            @can('create-salary-cycle')--}}
                            {{--                                <form id="salary_cycle" method="POST" action="{{ route('payroll.salary.cycle.store') }}" autocomplete="off">--}}
                            {{--                                    @csrf--}}
                            {{--                                    <div class="row">--}}
                            {{--                                        <div class="col-md-12">--}}
                            {{--                                            <div class="row field-changes-below">--}}
                            {{--                                                <div class="col-md-4 text-right">--}}
                            {{--                                                    <label for="name" class="basic-detail-label">Project<span style="color: red">*</span></label>--}}
                            {{--                                                </div>--}}
                            {{--                                                <div class="col-md-4">--}}
                            {{--                                                    <select name="project_id" id="" class="form-control" required>--}}
                            {{--                                                        <option value="">Select Project</option>--}}
                            {{--                                                        @foreach($projects as $p)--}}
                            {{--                                                            <option value="{{$p->id}}">{{$p->name}}</option>--}}
                            {{--                                                        @endforeach--}}
                            {{--                                                    </select>--}}
                            {{--                                                </div>--}}
                            {{--                                            </div>--}}
                            {{--                                            <div class="row field-changes-below">--}}
                            {{--                                                <div class="col-md-4 text-right">--}}
                            {{--                                                    <label for="salary_cycle_name" class="basic-detail-label">Name<span style="color: red">*</span></label>--}}
                            {{--                                                </div>--}}
                            {{--                                                <div class="col-md-4">--}}
                            {{--                                                    <input type="text" name="salary_cycle_name" id="salary_cycle_name" class="form-control input-sm basic-detail-input-style"placeholder="Enter Salary Cycle Name" required>--}}
                            {{--                                                </div>--}}
                            {{--                                            </div>--}}
                            {{--                                            <div class="row field-changes-below">--}}
                            {{--                                                <div class="col-md-4 text-right">--}}
                            {{--                                                    <label for="from" class="basic-detail-label">Day From<span style="color: red">*</span></label>--}}
                            {{--                                                </div>--}}
                            {{--                                                <div class="col-md-4">--}}
                            {{--                                                    <select type="date" name="salary_from" required  id="from" class="form-control input-sm basic-detail-input-style">--}}
                            {{--                                                        <option selected disabled>Select Start Date</option>--}}
                            {{--                                                        @foreach($dates as $date)--}}
                            {{--                                                            <option>{{ $date }}</option>--}}
                            {{--                                                        @endforeach--}}
                            {{--                                                    </select>--}}
                            {{--                                                </div>--}}
                            {{--                                            </div>--}}
                            {{--                                            <div class="row field-changes-below">--}}
                            {{--                                                <div class="col-md-4 text-right">--}}
                            {{--                                                    <label for="salary_to" class="basic-detail-label">Day To<span style="color: red">*</span></label>--}}
                            {{--                                                </div>--}}
                            {{--                                                <div class="col-md-4">--}}
                            {{--                                                    <select type="date" name="salary_to" id="to" required class="form-control input-sm basic-detail-input-style">--}}
                            {{--                                                        <option selected disabled>Select Start Date</option>--}}
                            {{--                                                        @foreach($dates as $date)--}}
                            {{--                                                            <option>{{ $date }}</option>--}}
                            {{--                                                        @endforeach--}}
                            {{--                                                    </select>--}}
                            {{--                                                </div>--}}
                            {{--                                            </div>--}}
                            {{--                                        </div>--}}
                            {{--                                        <div class="col-md-6"></div>--}}
                            {{--                                    </div>--}}
                            {{--                                    <div class="box-footer create-footer text-center">--}}
                            {{--                                        <input type="submit" class="btn btn-primary" id="submit3" value="Add" name="submit">--}}
                            {{--                                    </div>--}}
                            {{--                                </form>--}}
                            {{--                            @endcan--}}
                            <h2 class="heading2_form">All PO's List</h2>
                            <!--KRA Table Starts here-->
                            <table class="table table-striped table-responsive table-bordered" id="salary_heads_table">
                                <thead class="table-heading-style">
                                <tr>
                                    <th>S No.</th>
                                    <th>Name</th>
                                    <th>Designation</th>
                                    <th>State</th>
                                    <th>Location</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                </tr>
                                </thead>
                                <tbody class="kra_tbody">
                                @foreach($pos as $po)
                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>
                                            <span>{{$po->fullname}} ({{ $po->employee_code }})</span>
                                        </td>
                                        <td>
                                            <span>{{$po->designation}}</span>
                                        </td>
                                        <td>
                                            <span>{{$po->state}}</span>
                                        </td>
                                        <td>
                                            <span>{{$po->location}}</span>
                                        </td>
                                        <td>
{{--                                            <button class="btn bg-purple" data-toggle="modal"--}}
{{--                                                    data-target="#editSalaryCycleModal{{ $po->id }}">--}}
{{--                                                <i class="fa fa-edit"></i>--}}
{{--                                            </button>--}}

                                        </td>
                                        <td>
                                            <form method="post" action="{{ route('po.location.destroy',
                                                $po->location_user_id) }}" onclick="return confirm('Are you sure you want to delete' +
                                                 ' ' +
                                                 'this keyword?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>


                                    <!-- The Modal -->
                                    {{--                                    <div class="modal" id="editSalaryCycleModal{{ $po->id }}">--}}
                                    {{--                                        <div class="modal-dialog">--}}
                                    {{--                                            <div class="modal-content">--}}

                                    {{--                                                <!-- Modal Header -->--}}
                                    {{--                                                <div class="modal-header">--}}
                                    {{--                                                    <h4 class="modal-title">Edit Salary Cycle</h4>--}}
                                    {{--                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>--}}
                                    {{--                                                </div>--}}

                                    {{--                                                <!-- Modal body -->--}}
                                    {{--                                                <div class="modal-body">--}}
                                    {{--                                                    <form id="salary_cycle_name" method="POST" action="{{ route--}}
                                    {{--                                                    ('payroll.salary.cycle.update', $po->id) }}" autocomplete="off">--}}
                                    {{--                                                    @csrf--}}
                                    {{--                                                    @method('PATCH')--}}
                                    {{--                                                    <!-- Box Body Starts here -->--}}
                                    {{--                                                        <div class="box-body jrf-form-body">--}}
                                    {{--                                                            <div class="row">--}}
                                    {{--                                                                <div class="col-md-12">--}}
                                    {{--                                                                    <div class="row field-changes-below">--}}
                                    {{--                                                                        <div class="col-md-4 text-right">--}}
                                    {{--                                                                            <label for="name" class="basic-detail-label">Project<span style="color: red">*</span></label>--}}
                                    {{--                                                                        </div>--}}
                                    {{--                                                                        <div class="col-md-4">--}}
                                    {{--                                                                            <select name="project_id" id="" class="form-control ">--}}
                                    {{--                                                                                <option value="">Select Project</option>--}}
                                    {{--                                                                                @foreach($projects as $project)--}}
                                    {{--                                                                                    <option value="{{$project->id}}" --}}
                                    {{--                                                                                        {{ $project->id == --}}
                                    {{--                                                                                        $po->project_id  ? 'selected'--}}
                                    {{--                                                                                         : '' }}>{{$project->name}}</option>--}}
                                    {{--                                                                                @endforeach--}}
                                    {{--                                                                            </select>--}}
                                    {{--                                                                        </div>--}}
                                    {{--                                                                    </div>--}}
                                    {{--                                                                    <div class="row field-changes-below">--}}
                                    {{--                                                                        <div class="col-md-4 text-right">--}}
                                    {{--                                                                            <label for="salary_cycle_name" class="basic-detail-label">Name<span style="color: red">*</span></label>--}}
                                    {{--                                                                        </div>--}}
                                    {{--                                                                        <div class="col-md-4">--}}
                                    {{--                                                                            <input type="text" --}}
                                    {{--                                                                                   name="salary_cycle_name" --}}
                                    {{--                                                                                   id="salary_cycle_name" --}}
                                    {{--                                                                                   class="form-control input-sm basic-detail-input-style"placeholder="Enter Salary Cycle Name" required="" value="{{$po->name}}">--}}
                                    {{--                                                                        </div>--}}
                                    {{--                                                                    </div>--}}
                                    {{--                                                                    <div class="row field-changes-below">--}}
                                    {{--                                                                        <div class="col-md-4 text-right">--}}
                                    {{--                                                                            <label for="from" class="basic-detail-label">Day From<span style="color: red">*</span></label>--}}
                                    {{--                                                                        </div>--}}
                                    {{--                                                                        <div class="col-md-4">--}}
                                    {{--                                                                            <input type="date" name="salary_from" --}}
                                    {{--                                                                                   required=""  id="from" --}}
                                    {{--                                                                                   class="form-control input-sm --}}
                                    {{--                                                                                   basic-detail-input-style" value="{{$po->salary_from}}">--}}
                                    {{--                                                                        </div>--}}
                                    {{--                                                                    </div>--}}
                                    {{--                                                                    <div class="row field-changes-below">--}}
                                    {{--                                                                        <div class="col-md-4 text-right">--}}
                                    {{--                                                                            <label for="salary_to" class="basic-detail-label">Day To<span style="color: red">*</span></label>--}}
                                    {{--                                                                        </div>--}}
                                    {{--                                                                        <div class="col-md-4">--}}
                                    {{--                                                                            <input type="date" name="salary_to" --}}
                                    {{--                                                                                   id="to" required="" --}}
                                    {{--                                                                                   class="form-control input-sm --}}
                                    {{--                                                                                   basic-detail-input-style" value="{{$po->salary_to}}">--}}
                                    {{--                                                                        </div>--}}
                                    {{--                                                                    </div>--}}
                                    {{--                                                                </div>--}}
                                    {{--                                                                <div class="col-md-6"></div>--}}
                                    {{--                                                            </div>--}}
                                    {{--                                                        </div>--}}
                                    {{--                                                        <!-- Box Body Ends here -->--}}
                                    {{--                                                        <!-- Box Footer Starts here -->--}}
                                    {{--                                                        <div class="box-footer text-center">--}}
                                    {{--                                                            <input type="submit" class="btn btn-primary submit-btn-style" id="submit2" value="Update" name="submit">--}}
                                    {{--                                                        </div>--}}
                                    {{--                                                        <!-- Box Footer Ends here -->--}}
                                    {{--                                                    </form>--}}
                                    {{--                                                    <!-- Form Ends here -->--}}
                                    {{--                                                </div>--}}

                                    {{--                                                <!-- Modal footer -->--}}
                                    {{--                                                <div class="modal-footer">--}}
                                    {{--                                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>--}}
                                    {{--                                                </div>--}}

                                    {{--                                            </div>--}}
                                    {{--                                        </div>--}}
                                    {{--                                    </div>--}}

                                @endforeach
                                </tbody>
                                <tfoot class="table-heading-style">
                                <th>S No.</th>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Location</th>
                                <th>Edit</th>
                                <th>Delete</th>
                                </tfoot>
                            </table>
                            <!--KRA Table Ends here-->
                        </div>
                    </div>
                </div>
                <!-- /.box -->
            </div>
            <!-- /.row -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <script src="{{asset('public/admin_assets/plugins/dataTables/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('public/admin_assets/validations/additional-methods.js')}}"></script>
    <script type="text/javascript">
        $('#salary_heads_table').DataTable({
            "scrollX": true,
            responsive: true
        });
    </script>
@endsection



