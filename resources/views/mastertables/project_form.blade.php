@extends('admins.layouts.app')

@section('content')

<!-- Content Wrapper. Contains page content -->

  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        @if($data['action'] == "add")
        {{"Add"}}
        @else
        {{"Edit"}}
        @endif

        {{"Project"}}

        <!-- <small>Control panel</small> -->
      </h1>

      <ol class="breadcrumb">

        <li><a href="{{ url('employees/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>

        <li><a href="{{ url('mastertables/projects') }}">Projects List</a></li> 

      </ol>

    </section>



    <?php 
          $last_inserted_project = session('last_inserted_project');

          if(empty($last_inserted_project)){
            $last_inserted_project = 0;
          }

          $last_tabname = session('last_tabname');

          if(empty($last_tabname)){
            $last_tabname = "projectDetailsTab";
          }
     ?>



    <!-- Main content -->

    <section class="content">

      <!-- Small boxes (Stat box) -->

      <div class="row">

          <div class="col-sm-12">

            <!-- Custom Tabs -->

          <div class="nav-tabs-custom">

            <ul class="nav nav-tabs edit-nav-styling">

              <li id="projectDetailsTab" class=""><a href="#tab_projectDetailsTab" data-toggle="tab">Project</a></li>
              <li id="contactDetailsTab" class=""><a href="#tab_contactDetailsTab" data-toggle="tab">Contacts</a></li>
              
            </ul>

            <div class="tab-content">

              <!-- Add Project Tab -->

              <div class="tab-pane active" id="tab_projectDetailsTab">

                <div class="box box-primary">

                @include('admins.validation_errors')

                @if(session()->has('projectError'))

                  <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session()->get('projectError') }}
                  </div>

                @endif

            <div class="box-header with-border leave-form-title-bg">
              <h3 class="box-title">Form</h3>
            </div>

            <!-- /.box-header -->
            <!-- form start -->

            <form id="projectForm" action="{{ url('mastertables/save-project') }}" method="POST" enctype="multipart/form-data">

              {{ csrf_field() }}

              <div class="box-body">
                <div class="form-group">
                  <label for="projectName">Project Name</label>

                  <input type="text" class="form-control" id="projectName" name="projectName" placeholder="Project Name" value="{{@$data['project']->name}}">
                </div>



                <div class="form-group">

                  <label for="projectAddress">Project Address</label>

                  <input type="text" class="form-control" id="projectAddress" name="projectAddress" placeholder="Project Address" value="{{@$data['project']->address}}">

                </div>

                

                <div class="form-group">

                  <label>Responsible Persons</label>

                  <select class="form-control select2" name="employeeIds[]" multiple="multiple" style="width:100%;">

                  @if(!$data['employees']->isEmpty())  
                    @foreach($data['employees'] as $employee)  

                      <option value="{{$employee->user_id}}" @if(in_array($employee->user_id,@$data['project']->employees)){{"selected"}} @else{{""}}@endif>{{$employee->fullname}}</option>

                    @endforeach

                  @endif

                  </select>

                </div>



                <div class="form-group">

                  <label>Salary Structure</label>

                  <select class="form-control salaryStructure" name="salaryStructureId">

                  @if(!$data['salary_structures']->isEmpty())  

                    @foreach($data['salary_structures'] as $salary_structure)  

                      <option value="{{$salary_structure->id}}" @if(@$data['project']->salary_structure_id == $salary_structure->id){{"selected"}} @else{{""}}@endif>{{$salary_structure->name}}</option>

                    @endforeach

                  @endif

                  </select>

                </div>



                <div class="form-group">

                  <label>Salary Cycle</label>

                  <select class="form-control salaryCycle" name="salaryCycleId">

                  @if(!$data['salary_cycles']->isEmpty())  

                    @foreach($data['salary_cycles'] as $salary_cycle)  

                      <option value="{{$salary_cycle->id}}" @if(@$data['project']->salary_cycle_id == $salary_cycle->id){{"selected"}} @else{{""}}@endif>{{$salary_cycle->name}}</option>

                    @endforeach

                  @endif

                  </select>

                </div>



                <div class="form-group">

                  <label>Project Type</label>

                  <select class="form-control" name="projectType" id="projectType">

                    <option value="1">Government</option>
                    <option value="2">Corporate</option>
                    <option value="3">International</option>

                  </select>

                </div>



                <div class="form-group">

                  <label>Tenure(in years)</label>

                  <select class="form-control" name="tenureYears" id="tenureYears">
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                    <option value="9">9</option>
                    <option value="10">10</option>
                    <option value="11">11</option>
                    <option value="12">12</option>
                  </select>

                </div>



                <div class="form-group">

                  <label>Tenure(in months)</label>

                  <select class="form-control" name="tenureMonths" id="tenureMonths">
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                    <option value="9">9</option>
                    <option value="10">10</option>
                    <option value="11">11</option>
                  </select>

                </div>



                <div class="form-group">
                  <label for="noOfResources">Number Of Resources</label>
                    <input type="text" class="form-control" name="noOfResources" id="noOfResources" value="{{@$data['project']->number_of_resources}}">
                </div>



                <div class="form-group">
                  <label>Company</label>
                  <select class="form-control company" name="companyId" id="companyId">
                      <option value="" selected disabled>Select a company</option>
                  @if(!$data['companies']->isEmpty())  
                    @foreach($data['companies'] as $company)  

                      <option value="{{$company->id}}" @if(@$data['project']->company_id == $company->id){{"selected"}} @else{{""}}@endif>{{$company->name}}</option>

                    @endforeach

                  @endif

                  </select>
                </div>

                <div class="form-group">
                  <label for="pfNo">PF Number</label>
                    <input type="text" class="form-control" name="pfNo" id="pfNo" value="None" readonly>
                </div>

                <div class="form-group">
                  <label>PT State</label>

                  <select class="form-control ptState select2" style="width:100%;" name="stateId[]" multiple>

                  @if(!$data['states']->isEmpty()) 
                    @foreach($data['states'] as $state)  

                      <option value="{{$state->id}}" @if(in_array($state->id,@$data['project']->proj_states)){{"selected"}} @else{{""}}@endif>{{$state->name}}</option>

                    @endforeach
                  @endif

                  </select>
                </div>

                <div class="form-group" id="certificateBox">
                 
                </div>

                <hr>
                
                <div class="form-group">
                  <label>States</label>

                  <select class="form-control allState select2" style="width:100%;" name="allStateId[]" multiple>

                  @if(!$data['states']->isEmpty()) 
                    @foreach($data['states'] as $state)  

                      <option value="{{$state->id}}" @if(in_array($state->id,@$data['project']->proj_allState)){{"selected"}} @else{{""}}@endif>{{$state->name}}</option>

                    @endforeach
                  @endif

                  </select>
                </div>

                <div class="form-group">
                  <label>ESI Location</label>
                  <select class="form-control esiLocation select2" style="width:100%;" name="locationId[]" multiple>
                  @if(!$data['locations']->isEmpty())  
                    @foreach($data['locations'] as $location)  

                      <option value="{{$location->id}}" @if(in_array($location->id,@$data['project']->proj_locations)){{"selected"}} @else{{""}}@endif>{{$location->name}}</option>

                    @endforeach
                  @endif

                  </select>

                </div>

                <div class="form-group" id="esiNoBox"> 
                </div>

                <div class="form-group">
                  <label for="projectAgreement">Agreement File</label>&nbsp;<span class="viewFileSpan label label-default">@if(!empty(@$data['proj_documents'][0]['name']))<a target="_blank" class="" href="{{config('constants.uploadPaths.projectDocument')}}{{@$data['proj_documents'][0]['name']}}">view</a>@endif</span>

                    <input type="file" class="form-control" name="projectAgreement" id="projectAgreement">
                </div>

                <div class="form-group">
                  <label for="agreementFile">Agreement Upload</label>&nbsp;<span class="viewFileSpan label label-default">@if(!empty(@$data['proj_documents'][1]['name']))<a target="_blank" class="" href="{{config('constants.uploadPaths.projectDocument')}}{{@$data['proj_documents'][1]['name']}}">view</a>@endif</span>

                    <input type="file" class="form-control" name="agreementFile" id="agreementFile">
                </div>

                <div class="form-group">
                  <label for="loiFile">LOI Upload</label>&nbsp;<span class="viewFileSpan label label-default">@if(!empty(@$data['proj_documents'][2]['name']))<a target="_blank" class="" href="{{config('constants.uploadPaths.projectDocument')}}{{@$data['proj_documents'][2]['name']}}">view</a>@endif</span>

                    <input type="file" class="form-control" name="loiFile" id="loiFile">
                    <span class="loiErrors"></span>
                </div>

                <div class="form-group">
                  <label for="offerLetterFile">Offer Letter Upload</label>&nbsp;<span class="viewFileSpan label label-default">@if(!empty(@$data['proj_documents'][3]['name']))<a target="_blank" class="" href="{{config('constants.uploadPaths.projectDocument')}}{{@$data['proj_documents'][3]['name']}}">view</a>@endif</span>

                    <input type="file" class="form-control" name="offerLetterFile" id="offerLetterFile">
                    <span class="offerLetterErrors"></span>
                </div>

                <div class="form-group">
                <label for="employeeContract1File">Employee Contract 1</label>&nbsp;<span class="viewFileSpan label label-default">@if(!empty(@$data['proj_documents'][4]['name']))<a target="_blank" class="" href="{{config('constants.uploadPaths.projectDocument')}}{{@$data['proj_documents'][4]['name']}}">view</a>@endif</span>

                    <input type="file" class="form-control" name="employeeContract1File" id="employeeContract1File">
                </div>

                <div class="form-group">
                  <label for="employeeContract2File">Employee Contract 2</label>&nbsp;<span class="viewFileSpan label label-default">@if(!empty(@$data['proj_documents'][5]['name']))<a target="_blank" class="" href="{{config('constants.uploadPaths.projectDocument')}}{{@$data['proj_documents'][5]['name']}}">view</a>@endif</span>

                    <input type="file" class="form-control" name="employeeContract2File" id="employeeContract2File">
                </div>

                <div class="form-group">
                  <label for="employeeContract3File">Employee Contract 3</label>&nbsp;<span class="viewFileSpan label label-default">@if(!empty(@$data['proj_documents'][6]['name']))<a target="_blank" class="" href="{{config('constants.uploadPaths.projectDocument')}}{{@$data['proj_documents'][6]['name']}}">view</a>@endif</span>

                    <input type="file" class="form-control" name="employeeContract3File" id="employeeContract3File">
                    <span class="employeeContractErrors"></span>
                </div>

                <input type="hidden" name="action" value="{{$data['action']}}">

                @if(!empty(@$data['project']->id))
                  <input type="hidden" name="projectId" value="{{@$data['project']->id}}" id="projectId">
                @endif                  

              </div>

              <!-- /.box-body -->

              <div class="box-footer">
                <button type="button" id="projectFormSubmit" class="btn btn-primary">Submit</button>
              </div>

            </form>

          </div>

        </div> 

    <!-- Add Project Tab end -->



            <div class="tab-pane" id="tab_contactDetailsTab"> 
              <div id="noProject" class="alert alert-danger alert-dismissible">
                {{"Please fill the project details form and then fill the contacts form."}}
              </div>

                @if ($errors->contact->any())
                    <div class="alert alert-danger alert-dismissible">
                      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <ul>
                            @foreach ($errors->contact->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="contactDetailsForm" class="form-horizontal" action="{{ url('mastertables/create-project-contact') }}" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="box-body">
                      <span class="text-primary"><em>Please enter contact details</em></span> :-
                  <hr>

                      <div class="form-group form-sidechange">

                        <div class="row">

                          <div class="col-md-6">

                            <div class="row field-changes-below">
                              <label for="name" class="col-md-4 control-label">Name</label>

                              <div class="col-md-8">

                                <input autocomplete="off" type="text" class="form-control input-sm basic-detail-input-style" name="name" id="name" placeholder="Please Enter Name.">

                              </div>

                            </div>

                            @if($data['action'] == "edit")
                              <input type="hidden" name="projectId" value="{{@$data['project']->id}}" id="projectId">
                            @else
                              <input type="hidden" name="projectId" value="{{@$last_inserted_project}}" id="projectId">
                            @endif

                            <input type="hidden" name="action" value="{{@$data['action']}}">

                            <div class="row field-changes-below">

                              <label for="email" class="col-md-4 control-label">Email</label>

                              <div class="col-md-8">

                                <input autocomplete="off" type="text" class="form-control input-sm basic-detail-input-style checkProjectContact" name="email" id="email" placeholder="Please Enter Valid Email Id.">

                                <span class="emailError"></span>

                              </div>

                            </div>

                          </div>

                          <div class="col-md-6">
                            <div class="row field-changes-below">
                              <label for="mobileNo" class="col-md-4 control-label">Mobile Number</label>
                              <div class="col-md-8">
                                <input autocomplete="off" type="text" class="form-control input-sm basic-detail-input-style checkProjectContact" name="mobileNo" id="mobileNo" placeholder="Please Enter Mobile Number.">

                                <span class="mobileError"></span>
                              </div>
                            </div>

                            <div class="row field-changes-below">
                              <label for="role" class="col-md-4 control-label">Role</label>

                              <div class="col-md-8">
                                <input autocomplete="off" type="text" class="form-control input-sm basic-detail-input-style" name="role" id="role" placeholder="Please Enter Role.">
                              </div>
                            </div>

                          </div>
                      </div>
                  </div>
                    </div>
                    <!-- /.box-body -->

                    <div class="box-footer">
                      <button type="button" class="btn btn-info contactDetailsFormSubmit" id="contactDetailsFormSubmit" name="formSubmitButton">Save</button>
                    </div>
                    <!-- /.box-footer -->
                  </form>

                  <br>
                  <hr>

                  <div class="box">

                      <div class="box-header">
                        <h3 class="box-title">Contacts List</h3>
                      </div>

                      <!-- /.box-header -->

                      <div class="box-body no-padding">

                        <table class="table table-striped table-bordered">
                          
                          <tr>

                            <th style="width: 10%">Name</th>
                            <th style="width: 10%">Role</th>
                            <th style="width: 10%">Email</th>
                            <th style="width: 10%">Mobile</th>

                            @if($data['action'] == 'edit')
                            <th style="width: 10%">Action</th>
                            @endif

                          </tr>

                          
                          @if(!$data['contacts']->isEmpty())
                          @foreach($data['contacts'] as $key => $value)

                          <tr>
                            <td>{{$value->name}}</td>
                            <td>{{$value->role}}</td>
                            <td>{{$value->email}}</td>
                            <td>{{$value->mobile_number}}</td>

                            @if($data['action'] == 'edit')

                            <td>
                              <a class="btn bg-purple editContact" data-name="{{$value->name}}" data-role="{{$value->role}}" data-email="{{$value->email}}" data-mobile="{{$value->mobile_number}}" data-contactid="{{$value->id}}" href='javascript:void(0)' title="edit"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                              </a>
                            </td>

                            @endif

                          </tr>

                          @endforeach

                          @endif
                                                  
                        </table>

                      </div>
                      <!-- /.box-body -->

                    </div>

                    <!-- /.box -->  

            </div>  

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



    <div class="modal fade" id="editContactModal">

        <div class="modal-dialog">

          <div class="modal-content">

            <div class="modal-header">

              <button type="button" class="close" data-dismiss="modal" aria-label="Close">

                <span aria-hidden="true">&times;</span></button>

              <h4 class="modal-title">Edit Contact Form</h4>

            </div>

            <div class="modal-body">

              <form id="editContactForm" action="{{ url('mastertables/edit-project-contact') }}" method="POST" enctype="multipart/form-data">

                {{ csrf_field() }}

                  <div class="box-body">



                    <div class="row">

                      

                      <div class="col-sm-6">

                          <div class="form-group">

                            <label for="name" class="">Name</label>

                            <input type="text" class="form-control" id="nameModal" name="name">

                          </div>



                          <div class="form-group">

                            <label for="email" class="">Email</label>

                            <input type="text" class="form-control checkEditContact" id="emailModal" name="email">

                            <span class="emailErrorModal"></span>

                          </div>

                      </div>



                      <input type="hidden" name="projectId" id="projectIdModal">

                      <input type="hidden" name="contactId" id="contactIdModal">



                      <div class="col-sm-6">

                          <div class="form-group">

                            <label for="mobileNo" class="">Mobile Number</label>

                            <input type="text" class="form-control checkEditContact" id="mobileNoModal" name="mobileNo">

                            <span class="mobileErrorModal"></span>

                          </div>



                          <div class="form-group">

                            <label for="role" class="">Role</label>

                            <input type="text" class="form-control" id="roleModal" name="role">

                          </div>

                      </div>

                      

                    </div>

                                 

                  </div>

                  <!-- /.box-body -->

                  <br>



                  <div class="box-footer">

                    <button type="button" class="btn btn-primary" id="editContactFormSubmit">Submit</button>

                  </div>

            </form>

            </div>

            

          </div>

          <!-- /.modal-content -->

        </div>

      <!-- /.modal-dialog -->

      </div>

        <!-- /.modal -->

  </div>

  <!-- /.content-wrapper -->

  <script src="{{asset('public/admin_assets/plugins/validations/jquery.validate.js')}}"></script>
  <script src="{{asset('public/admin_assets/plugins/validations/additional-methods.js')}}"></script>

  <script>

    $("#noProject").hide();

    var projectType = "{{@$data['project']->type}}";
    var tenureYears = "{{@$data['project']->tenure_years}}";
    var tenureMonths = "{{@$data['project']->tenure_months}}";

    if(projectType){
      $("#projectType").val(projectType);
    }

    if(tenureYears){
      $("#tenureYears").val(tenureYears);
    }

    if(tenureMonths){
      $("#tenureMonths").val(tenureMonths);
    }

    $("#projectForm").validate({

      rules :{

          "projectName" : {

              required : true,

              lettersonly : true

          },

          "projectAddress" : {

              required : true,

          },

          "employeeIds[]" : {

              required : true,

          },

          "companyId" : {

              required : true

          },

          "salaryStructureId" : {

              required : true

          },

          "salaryCycleId" : {

              required : true

          },

          "noOfResources" : {

              required : true,

              digits : true

          },

          "projectAgreement" : {

              extension: "jpeg|jpg|pdf",

              filesize: 1048576   //1 MB

          },

          "offerLetterFile" : {

              extension: "jpeg|jpg|pdf",

              filesize: 1048576   //1 MB

          },

          "stateId[]" : {

              required : true,

          },

          "locationId[]" : {

              //required : true,

          }

      },

      errorPlacement: function(error, element) {
        if (element.hasClass('select2')) {
          error.insertAfter(element.next('span.select2'));
        } else {
          error.insertAfter(element);
        }
      },

      messages :{

          "projectName" : {

              required : 'Please enter project name.',

          },

          "projectAddress" : {

              required : 'Please enter project address.'

          },

          "employeeIds[]" : {

              required : 'Please select a responsible person.'

          },

          "companyId" : {

              required : 'Please select a company.'

          },

          "salaryStructureId" : {

              required : 'Please select a salary structure.'

          },

          "salaryCycleId" : {

              required : 'Please select a salary cycle.'

          },

          "noOfResources" : {

              required : 'Please enter the number of resources.'

          },

          "projectAgreement" : {

              extension : 'Please select a file in jpg, jpeg or pdf format only.',

              filesize: 'Filesize should be less than 1 MB.'  

          },

          "offerLetterFile" : {

              extension : 'Please select a file in jpg, jpeg or pdf format only.',

              filesize: 'Filesize should be less than 1 MB.'  

          },

          "stateId[]" : {

              required : 'Please select PT state.',

          },

          "locationId[]" : {

              //required : 'Please select ESI location.',

          }

      }

    });



    $("#contactDetailsForm").validate({

      rules :{

          "name" : {

              required : true

          },

          "mobileNo" : {

              required : true,

              digits : true

          },

          "email" : {

              required : true,

              email : true

          },

          "role" : {

            required : true

          }

      },

      messages :{

          "name" : {

              required : 'Please enter name.',

          },

          "mobileNo" : {

              required : 'Please enter mobile number.'

          },

          "email" : {

              required : 'Please enter email.'

          },

          "role" :{

              required : 'Please enter role.'

          }

      }

    });



    $("#editContactForm").validate({

      rules :{

          "name" : {

              required : true

          },

          "mobileNo" : {

              required : true,

              digits : true

          },

          "email" : {

              required : true,

              email : true

          },

          "role" : {

            required : true

          }

      },

      messages :{

          "name" : {

              required : 'Please enter name.',

          },

          "mobileNo" : {

              required : 'Please enter mobile number.'

          },

          "email" : {

              required : 'Please enter email.'

          },

          "role" :{

              required : 'Please enter role.'

          }

      }

    });

    $.validator.addMethod("lettersonly", function(value, element) {
      return this.optional(element) || /^[a-z," "]+$/i.test(value);
    }, "Please enter only alphabets and spaces.");

    $.validator.addMethod('filesize', function(value, element, param) {
      return this.optional(element) || (element.files[0].size <= param) 
    });

  </script>



  <script type="text/javascript">

    var allowContactSubmit = {mobile: 1, email: 1};
    var allowEditContactSubmit = {mobile: 1, email: 1};
    var allowProjectSubmit = {loi: 1, employeeContract: 1, offerLetter: 1, pt: 1, esi: 1};

    $(document).ready(function(){

      var tabName = "{{$last_tabname}}";

      $('.nav-tabs a[href="#tab_'+tabName+'"]').tab('show');

      var defaultCompanyId = $('#companyId').val();
      var action = "{{$data['action']}}";

      $(".checkEditContact").on('keyup',function(){

        var email = $("#emailModal").val();
        var mobileNo = $("#mobileNoModal").val();
        var contactId = $("#contactIdModal").val();
        var projectId = $("#projectIdModal").val();

        $.ajax({
              type: 'POST',
              url: "{{ url('mastertables/check-unique-edit-project-contact') }}",
              data: {email: email, mobile_number: mobileNo, contact_id: contactId, project_id: projectId},
              success: function (result) {

                if(result.mobile_number == 0){
                  $(".mobileErrorModal").text("Mobile number already exists for given project.").css("color","#f00");

                }else{
                  $(".mobileErrorModal").text("");

                }

                if(result.email == 0){
                  $(".emailErrorModal").text("Email already exists for given project.").css("color","#f00");

                }else{
                  $(".emailErrorModal").text("");

                }

                if(result.email == 1){
                  allowEditContactSubmit.email = 1;
                }else{
                  allowEditContactSubmit.email = 0;
                }

                if(result.mobile_number == 1){
                  allowEditContactSubmit.mobile = 1;
                }else{
                  allowEditContactSubmit.mobile = 0;
                }

              }

            });

      });



      $(".checkProjectContact").on('keyup',function(){

        var email = $("#email").val();
        var mobileNo = $("#mobileNo").val();
        var projectId = $("#projectId").val();

        if(projectId != "0"){

            $.ajax({
              type: 'POST',
              url: "{{ url('mastertables/check-unique-project-contact') }}",
              data: {email: email, mobile_number: mobileNo, project_id: projectId},
              success: function (result) {

                if(result.mobile_number == 0){
                  $(".mobileError").text("Mobile number already exists for given project.").css("color","#f00");

                }else{
                  $(".mobileError").text("");

                }

                if(result.email == 0){
                  $(".emailError").text("Email already exists for given project.").css("color","#f00");

                }else{
                  $(".emailError").text("");

                }

                if(result.email == 1){
                  allowContactSubmit.email = 1;

                }else{
                  allowContactSubmit.email = 0;

                }

                if(result.mobile_number == 1){
                  allowContactSubmit.mobile = 1;

                }else{
                  allowContactSubmit.mobile = 0;

                }

              }

            });

        }

      });

      if(action == 'add'){
        $('.esiLocation').empty();
      }
      
      if(defaultCompanyId){
        $.ajax({      //on page load ajax
          type: 'POST',
          url: "{{ url('mastertables/company-tan-pf') }}",
          data: {company_id: defaultCompanyId},

          success: function (result) {
            console.log('On load company', result);

            if(result.pf_no != ""){
              $("#pfNo").val(result.pf_no);
            }else{
              $("#pfNo").val("None");
            }

          }

        });
      }
      

      $('.company').on("change",function(){
        var companyId = $(this).val();

          $.ajax({
          type: 'POST',
          url: "{{ url('mastertables/company-tan-pf') }}",
          data: {company_id: companyId},
          success: function (result) {
            console.log('On change company', result);

            if(result.pf_no != ""){
              $("#pfNo").val(result.pf_no);
            }else{
              $("#pfNo").val("None");
            }

            $(".ptState").val("").trigger('change');
            $("#certificateBox").html("");

            $("#esiNoBox").html("");
            $('.esiLocation').val("").trigger('change');

          }

        });

      });

      //on load
      var ptStateIds = $('.ptState').val();  //array
      var companyId = $('#companyId').val();

      if(ptStateIds.length != 0){
        $.ajax({
          type: 'POST',
          url: "{{ url('mastertables/company-pt-certificate-no') }}",
          data: {company_id: companyId,state_ids: ptStateIds},
          success: function (result) {
            console.log('On load pt', result);
            
            $("#certificateBox").html("");
            var displayString = "";

            $.each(result, function(key, value){
                displayString += '<label for="certificateNo">'+value.state.name+' Certificate Number</label>';

                if(value.state.has_pt == 0){
                  displayString += '<input type="text" class="form-control certificateNo" name="certificateNo" value="No PT in this state." readonly><br>';

                }else if(value.pt_data){
                  displayString += '<input type="text" class="form-control certificateNo" name="certificateNo" value="'+value.pt_data.certificate_number+'" readonly><br>';

                }else{
                  displayString += '<input type="text" class="form-control certificateNo error" name="certificateNo" style="color:#f00;" value="Has PT but not added for selected company. Please add it first." readonly><br>';
                }
              
            });

            $('#certificateBox').prepend(displayString);

            if($(".certificateNo").hasClass("error")){
              allowProjectSubmit.pt = 0;
            }else{
              allowProjectSubmit.pt = 1;
            }

          }

        });

      }else{

        $("#certificateBox").html("");

      }

      $('.allState').on('change',function(){
        var allStateIds = $(this).val();
        var companyId = $('#companyId').val();

        //$(".esiLocation").val('').change();
        $('.esiLocation').empty();
        
        if(allStateIds.length != 0){
          $.ajax({
            type: 'POST',
            url: "{{ url('mastertables/states-wise-locations') }}",
            data: {state_ids: allStateIds},
            success: function (result) {
              console.log('On change allState', result);

              if(result.locations.length != 0){
                  var displayString = '';
                  $.each(result.locations, function(key, value){
                    displayString += '<option value="'+value.id+'">'+value.name+'</option>';
                  });

                  $('.esiLocation').append(displayString);
              }

            }

          });
        }
      });

      $('.ptState').on('change',function(){
        var ptStateIds = $(this).val();  //array
        var companyId = $('#companyId').val();

        if(ptStateIds.length != 0){
          $.ajax({
            type: 'POST',
            url: "{{ url('mastertables/company-pt-certificate-no') }}",
            data: {company_id: companyId,state_ids: ptStateIds},
            success: function (result) {
              console.log('On change pt', result);
              
              $("#certificateBox").html("");
              var displayString = "";

              // var appendString = "";
              // $('.esiLocation').empty();

              $.each(result, function(key, value){
                  displayString += '<label for="certificateNo">'+value.state.name+' Certificate Number</label>';

                  if(value.state.has_pt == 0){
                    displayString += '<input type="text" class="form-control certificateNo" name="certificateNo" value="No PT in this state." readonly><br>';

                  }else if(value.pt_data){
                    displayString += '<input type="text" class="form-control certificateNo" name="certificateNo" value="'+value.pt_data.certificate_number+'" readonly><br>';

                  }else{
                    displayString += '<input type="text" class="form-control certificateNo error" name="certificateNo" style="color:#f00;" value="Has PT but not added for selected company. Please add it first." readonly><br>';
                  }

                  /////////////AppendLocations/////////////

                  // if(value.locations.length != 0){
                  //   value.locations.forEach(function(location){
                  //     appendString += '<option value="'+location.id+'">'+location.name+'</option>';                      
                  //   });
                  // }
                
              });

              $('#certificateBox').prepend(displayString);

              // $('.esiLocation').append(appendString);

              if($(".certificateNo").hasClass("error")){
                allowProjectSubmit.pt = 0;
              }else{
                allowProjectSubmit.pt = 1;
              }

            }

          });

        }else{

          $("#certificateBox").html("");

        }

      });

      $('.esiLocation').on('change',function(){
        var esiLocationIds = $(this).val();
        var companyId = $('#companyId').val();

        if(esiLocationIds.length != 0){
          $.ajax({
            type: 'POST',
            url: "{{ url('mastertables/company-esi-no') }}",
            data: {company_id: companyId,location_ids: esiLocationIds},
            success: function (result) {
              console.log('On load esi', result);

              $("#esiNoBox").html("");
              var displayString = "";

              $.each(result, function(key, value){
                  displayString += '<label for="esiNo">'+value.location.name+' ESI Number</label>';

                  if(value.location.has_esi == 0){
                    displayString += '<input type="text" class="form-control esiNo" name="esiNo" value="No ESI in this location." readonly><br>';

                  }else if(value.esi_data){                
                    displayString += '<input type="text" class="form-control esiNo" name="esiNo" value="'+value.esi_data.esi_number+'" readonly><br>';

                  }else{                    
                    displayString += '<input type="text" class="form-control esiNo error" name="esiNo" style="color:#f00;" value="Has ESI but not added for selected company. Please add it first." readonly><br>';
                  }
                
              });

              $('#esiNoBox').prepend(displayString);

              if($(".esiNo").hasClass("error")){
                allowProjectSubmit.esi = 0;
              }else{
                allowProjectSubmit.esi = 1;
              }
            }
          });

        }else{

          $("#esiNoBox").html("");

        }

      }).change();

    });

  </script>



  <script type="text/javascript">
    var action = "{{@$data['action']}}";
    
    $("#projectFormSubmit").on('click',function(){

      if(action == "add"){
          var agreementFile = document.getElementById('agreementFile');
          var loiFile = document.getElementById('loiFile');
          var offerLetterFile = document.getElementById('offerLetterFile');

          var employeeContract1File = document.getElementById('employeeContract1File');
          var employeeContract2File = document.getElementById('employeeContract2File');
          var employeeContract3File = document.getElementById('employeeContract3File');

          if((agreementFile.files.length + loiFile.files.length) == 0){
            allowProjectSubmit.loi = 0;
            $(".loiErrors").text("Please upload either one of agreement file or loi file.").css("color","#f00");
            return false;
          }else{
            allowProjectSubmit.loi = 1;
            $(".loiErrors").text("");
          }

          if((offerLetterFile.files.length) == 0){
            allowProjectSubmit.offerLetter = 0;
            $(".offerLetterErrors").text("Please upload a offer letter file.").css("color","#f00");
            return false;
          }else{
            allowProjectSubmit.offerLetter = 1;
            $(".offerLetterErrors").text("");
          }

          if((employeeContract1File.files.length + employeeContract2File.files.length + employeeContract3File.files.length) == 0){
            allowProjectSubmit.employeeContract = 0;
            $(".employeeContractErrors").text("Please upload either one of employee contract file.").css("color","#f00");
            return false;
          }else{
            allowProjectSubmit.employeeContract = 1;
            $(".employeeContractErrors").text("");
          }

          if(allowProjectSubmit.offerLetter == 0 || allowProjectSubmit.loi == 0 || allowProjectSubmit.employeeContract == 0 || allowProjectSubmit.pt == 0 || allowProjectSubmit.esi == 0){
            return false;
          
          }else if(allowProjectSubmit.offerLetter == 1 && allowProjectSubmit.loi == 1 && allowProjectSubmit.employeeContract == 1 || allowProjectSubmit.pt == 1 || allowProjectSubmit.esi == 1){
            $("#projectForm").submit();
          }
      }else{ //edit
        $("#projectForm").submit();
      }
      
    });

    $("#contactDetailsFormSubmit").on('click',function(){
      var action = "{{$data['action']}}";

      if(action == 'add'){
        var currentProject = "{{@$last_inserted_project}}";
      }else{
        var currentProject = $("#projectId").val();
      }

      if(currentProject == "0"){
        $("#noProject").show();
        $("#noProject").fadeOut(6000);

        return false;

      }else{

        if(allowContactSubmit.email == 1 && allowContactSubmit.mobile == 1){
          $("#noProject").hide();
          $("#contactDetailsForm").submit();

        }else{
          return false;

        }

      }

    });

    $("#editContactFormSubmit").on('click',function(){
        if(allowEditContactSubmit.email == 1 && allowEditContactSubmit.mobile == 1){
          $("#editContactForm").submit();
        }else{
          return false;
        }

    });

    $(".editContact").on('click',function(){
        var name = $(this).data('name');
        var email = $(this).data('email');
        var role = $(this).data('role');
        var mobile = $(this).data('mobile');
        var contactId = $(this).data('contactid');
        var projectId = $("#projectId").val();

        $("#nameModal").val(name);
        $("#roleModal").val(role);
        $("#emailModal").val(email);
        $("#mobileNoModal").val(mobile);
        $("#projectIdModal").val(projectId);
        $("#contactIdModal").val(contactId);
        $("#editContactModal").modal('show');

    });

  </script>

  @endsection