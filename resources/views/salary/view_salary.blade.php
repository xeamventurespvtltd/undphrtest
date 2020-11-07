@extends('admins.layouts.app')

@section('content')

<!-- Content Wrapper. Contains page content -->

  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        View Salary
        <!-- <small>Control panel</small> -->
      </h1>

      <ol class="breadcrumb">

        <li><a href="{{ url('employees/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>

        <li><a href="{{ url('salary/view-salary') }}">View salary</a></li>

      </ol>

    </section>





    <!-- Main content -->

    <section class="content">

      <!-- Small boxes (Stat box) -->

      <div class="row">

          <div class="col-sm-12">

            <!-- Custom Tabs -->

          <div class="nav-tabs-custom">



            <div class="tab-content">

              <!-- Add Project Tab -->

              <div class="tab-pane active" id="tab_projectDetailsTab">

                <div class="box box-primary">

                @include('admins.validation_errors')



            <div class="box-header with-border leave-form-title-bg">
              <h3 class="box-title"> View Salary Slip</h3>
            </div>

            <!-- /.box-header -->
            <!-- form start -->

           <form id="viewTasksList" method="GET">

              {{ csrf_field() }}

              <div class="box-body">

                 <h4>Select Month to view salary slip</h4>

                <hr>

                <div class="form-group">
                   <select class="form-control input-sm basic-detail-input-style" name="salary_month" id="task_type">

                        <option value="" selected disabled>Please select Month</option>
                        <option value="1">Dec-Jan</option>
                        <option value="2">Jan-Feb</option>
                        <option value="3">Feb-Mar</option>
                        <option value="4">Mar-apr</option>
                        <option value="5">Apr-May</option>
                        <option value="6">May-Jun</option>
                        <option value="7">Jun-Jul</option>
                        <option value="8">Jul-Aug</option>
                        <option value="9">Aug-Sep</option>
                        <option value="10">Sep-Oct</option>
                        <option value="11">oct-Nov</option>
                        <option value="12">Nov-Dec</option>


                    </select>
                </div>




              </div>

              <!-- /.box-body -->

              <div class="box-footer">
                <input type="submit" name="salarySheetSubmit" id="salarydateSubmit" class="btn btn-primary" value="Submit">
              </div>

            </form>

          </div>

        </div>

    <!-- Add Project Tab end -->




                <!-- Add Contact Tab end -->

            </div>

            <!-- tab-content End -->

          </div>

          <!-- Custom Tabs End -->

      </div>

          <!-- /.box -->

      </div>

      <!-- /.row -->

      <!-- Main row -->

    </section>

    <!-- /.content -->




        <!-- /.modal -->

  </div>

  <!-- /.content-wrapper -->

  <script src="{{asset('public/admin_assets/plugins/validations/jquery.validate.js')}}"></script>
  <script src="{{asset('public/admin_assets/plugins/validations/additional-methods.js')}}"></script>

  <script>



  </script>

  @endsection
