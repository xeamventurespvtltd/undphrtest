@extends('admins.layouts.app')

@section('content')

<style>
    .radio { margin: 6px 0 0 0; }
    .radio label input { position: relative; top: -2px; }
</style>

<!-- Content Wrapper Starts here -->
<div class="content-wrapper">

    <!-- Content Header Starts here -->
    <section class="content-header">


      @if(session()->has('profileSuccess'))

        <div class="alert alert-success alert-dismissible">

          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>

          {{ session()->get('profileSuccess') }}

        </div>

      @endif


       @if(session()->has('profileError'))

        <div class="alert alert-danger alert-dismissible">

          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>

          {{ session()->get('profileError') }}

        </div>

      @endif
        <h1>Your Profile has not been completed yet, Please fill this form first</h1>
        <ol class="breadcrumb">
            <li>
                <a href="#">
                    <i class="fa fa-dashboard"></i> Home
                </a>
            </li>
            <li class="active">Dashboard</li>
        </ol>
    </section>
    <!-- Content Header Ends here -->
  
    <!-- Main content Starts here -->
    <section class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="box box-primary">
                    <!-- Form Starts here -->
                    <form id="profile_detail" method="POST" action="{{ url('profile-detail-submit') }}">
                      {{ csrf_field() }}
                        <div class="box-body jrf-form-body">
                            <!-- Row starts here -->
                            <div class="row">
                                <!-- Left column starts here -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-4 col-sm-4 col-xs-4 leave-label-box label-470">
                                                <label for="name" class="apply-leave-label">Name</label>
                                            </div>
                                            <div class="col-md-8 col-sm-8 col-xs-8 leave-input-box input-470">
                                                <input type="text" class="form-control input-sm basic-detail-input-style" name="name" id="name" placeholder="Enter name here">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-4 col-sm-4 col-xs-4 leave-label-box label-470">
                                                <label for="father_name" class="apply-leave-label">Father Name</label>
                                            </div>
                                            <div class="col-md-8 col-sm-8 col-xs-8 leave-input-box input-470">
                                                <input type="text" class="form-control input-sm basic-detail-input-style" name="father_name" id="father_name" placeholder="Enter Father name">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-4 col-sm-4 col-xs-4 leave-label-box label-470">
                                                <label for="" class="apply-leave-label">Gender</label>
                                            </div>
                                            <div class="col-md-8 col-sm-8 col-xs-8 leave-input-box input-470">
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" name="gender" id="" value="Male" checked>Male

                                                    </label>&nbsp;&nbsp;

                                                    <label>
                                                    <input type="radio" name="gender" id="" value="Female">Female

                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-4 col-sm-4 col-xs-4 leave-label-box label-470">
                                                <label for="date_of_birth" class="apply-leave-label">Date of Birth</label>
                                            </div>
                                            <div class="col-md-8 col-sm-8 col-xs-8 leave-input-box input-470">
                                                <input type="text" class="form-control input-sm basic-detail-input-style datepicker" name="date_of_birth" id="date_of_birth" placeholder="2020/05/20">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-4 col-sm-4 col-xs-4 leave-label-box label-470">
                                                <label for="official_mobile_number" class="apply-leave-label">Official Mobile Number</label>
                                            </div>
                                            <div class="col-md-8 col-sm-8 col-xs-8 leave-input-box input-470">
                                                <input type="number" class="form-control input-sm basic-detail-input-style" name="official_mobile_number" id="official_mobile_number" placeholder="For Ex. 9876543210">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-4 col-sm-4 col-xs-4 leave-label-box label-470">
                                                <label for="personal_mobile_number" class="apply-leave-label">Personal Mobile Number</label>
                                            </div>
                                            <div class="col-md-8 col-sm-8 col-xs-8 leave-input-box input-470">
                                                <input type="number" class="form-control input-sm basic-detail-input-style" name="personal_mobile_number" id="personal_mobile_number" placeholder="For Ex. 9876543210">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Left column Ends here -->

                                <!-- Right column Starts here -->
                                <div class="col-md-6">
                                    <div class="form-group hide">
                                        <div class="row">
                                            <div class="col-md-4 col-sm-4 col-xs-4 leave-label-box label-470">
                                              <label for="designation" class="apply-leave-label">Designation</label>
                                            </div>
                                            <div class="col-md-8 col-sm-8 col-xs-8 leave-input-box input-470">
                                                <select name="designation" id="designation" class="form-control input-sm basic-detail-input-style designation_class">
                                                  <option value="" selected disabled>Select Designation</option>
                                                  <option value="2">SPO</option>
                                                  <option value="3">PO</option>
                                                  <option value="4">VCCM</option>
                                                  <option value="5">PO-IT</option>                                                   
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                  
                                    <div class="form-group hide">
                                        <div class="row">
                                            <div class="col-md-4 col-sm-4 col-xs-4 leave-label-box label-470">
                                              <label for="state" class="apply-leave-label">State</label>
                                            </div>
                                            <div class="col-md-8 col-sm-8 col-xs-8 leave-input-box input-470">
                                                <select name="state" id="state" class="form-control input-sm basic-detail-input-style">
                                                    <option value="" selected disabled>Select State</option>
                                                     @foreach($data['states'] as $state)
                                                      <option value="{{$state->id}}">{{$state->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    
                                      <div class="form-group hide">
                                        <div class="row">
                                            <div class="col-md-4 col-sm-4 col-xs-4 leave-label-box label-470">
                                              <label for="work_location" class="apply-leave-label">Work Location</label>
                                            </div>
                                            <div class="col-md-8 col-sm-8 col-xs-8 leave-input-box input-470">
                                                <select name="work_location[]" id="work_location" class="form-control input-sm basic-detail-input-style">
                                                    <option value="" selected disabled>Select Work Location</option>
                                                    @foreach($data['locations'] as $location)
                                                      <option value="{{$location->id}}">{{$location->name}}</option>
                                                    @endforeach
                                                   
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    

                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-4 col-sm-4 col-xs-4 leave-label-box label-470">
                                              <label for="official_email" class="apply-leave-label">Official Email ID</label>
                                            </div>
                                            <div class="col-md-8 col-sm-8 col-xs-8 leave-input-box input-470">
                                                <input type="email" class="form-control input-sm basic-detail-input-style" name="official_email" id="official_email" placeholder="For Ex. abc@gmail.com">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-4 col-sm-4 col-xs-4 leave-label-box label-470">
                                              <label for="personal_email" class="apply-leave-label">Personal Email ID</label>
                                            </div>
                                            <div class="col-md-8 col-sm-8 col-xs-8 leave-input-box input-470">
                                                <input type="email" class="form-control input-sm basic-detail-input-style" name="personal_email" id="personal_email" placeholder="For Ex. abc@gmail.com">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Right column Ends here -->
                            </div>
                            <!-- Row Ends here -->
                        </div>

                        <div class="box-footer create-footer text-center">
                          <input type="submit" class="btn btn-primary" name ="submit" id="save" value="Submit">
                        </div>

                        <br>

                    </form>
                    <!-- Form Ends here -->
                </div>
            </div>
        </div>
    </section>
    <!-- Main content Ends Here-->
    
</div>
<!-- Content Wrapper Ends here -->

<!-- Script Source Files Starts here -->
  <script src="{{asset('public/admin_assets/plugins/validations/jquery.validate.js')}}"></script>

  <script src="{{asset('public/admin_assets/plugins/validations/additional-methods.js')}}"></script>
<!-- Script Source Files Ends here -->

<!-- Custom Script Starts here -->
<script>



$(".designation_class").on('change', function(){
   
    
   var designation_val = $(this).val();
   
   if(designation_val==3 || designation_val==5){
       
       $("#work_location").addClass("select2");
        $('#work_location').attr("multiple", "multiple");
   }else{
   
       $("#work_location").removeClass( "select2");
       $('#work_location').removeAttr( "multiple" );
   }
   
  
});

  //Datepicker Starts here
  $(function () {
    //Date picker
    $('.datepicker').datepicker({
      autoclose: true,
      orientation: "bottom",
      format: 'yyyy/mm/dd'
    });
  }); 
  //Datepicker Ends here

  //Validation Starts here
  $("#profile_detail").validate({
    rules: {
      "name" : {
        required: true
      },
      "father_name" : {
        required: true
      },
      "date_of_birth" : {
        required: true
      },
      "official_mobile_number" : {
        required: true
      },
      "personal_mobile_number" : {
        required: true
      },
      "designation" : {
        required: true
      },
     
      "official_email" : {
        required: true
      },
      "personal_email" : {
        required: true
      }
    },
    errorPlacement: function(error, element) {
    if (element.hasClass('select2')) {
     error.insertAfter(element.next('span.select2'));
    } else {
     error.insertAfter(element);
    }
   },
    messages: {
      "name" : {
        required: 'Enter Your Name'
      },
      "father_name" : {
        required: 'Enter Father Name'
      },
      "date_of_birth" : {
        required: 'Select Date'
      },
      "official_mobile_number" : {
        required: 'Enter Official Mobile Number'
      },
      "personal_mobile_number" : {
        required: 'Enter Personal Mobile Number'
      },
      "designation" : {
        required: 'Select Designation'
      },
    
     
      "official_email" : {
        required: 'Enter Offiial Email ID'
      },
      "personal_email" : {
        required: 'Enter Personal Email ID'
      }
    }
  });
  //Validation Ends here

</script>
<!-- Custom Script Ends here -->

  @endsection
  
