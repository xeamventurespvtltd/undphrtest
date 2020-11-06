@extends('admins.layouts.app')

@section('content')

<!-- Content Wrapper. Contains page content -->

  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
       Upload file
        <!-- <small>Control panel</small> -->
      </h1>

      <ol class="breadcrumb">

        <li><a href="{{ url('employees/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>

        <li><a href="{{ url('mastertables/projects') }}">Projects List</a></li> 

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
              <h3 class="box-title">Form</h3>
            </div>

            <!-- /.box-header -->
            <!-- form start -->

            <form id="salarySheet" action="{{ url('salary/save-salary-slip') }}" method="POST" enctype="multipart/form-data">

              {{ csrf_field() }}

              <div class="box-body">
               


                <hr>
              
                <div class="form-group">
                    <input type="file" class="form-control" name="salary_file" id="salary_file">
                    <span class="salary_file_error"></span>
                </div>


                    

              </div>

              <!-- /.box-body -->

              <div class="box-footer">
                <input type="submit" name="salarySheetSubmit" id="salarySheetSubmit" class="btn btn-primary" value="Submit">
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