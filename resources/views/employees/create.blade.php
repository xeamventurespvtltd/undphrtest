@extends('admins.layouts.app')
@section('content')
    <style>
        .es-first-col {
            padding: 0;
        }
        .es-second-col {
            padding: 0 5px;
        }
        .es-third-col {
            padding: 0;
        }
        .es-fourth-col {
            padding: 0 0px 0 10px;
        }
        .es-third-col button, .es-fourth-col button {
            padding: 4px 10px;
        }
        .appended-shiftss {
            margin-top: 10px;
        }
    </style>
    <!-- Content Wrapper. Contains page content -->
    <!-- content wrapper starts here -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1 class="text-center">
                Employee Registration Forms
                <!-- <small>Control panel</small> -->
            </h1>
            <ol class="breadcrumb">
                <li><a href="{{ url('employees/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                <!-- <li class="active">Dashboard</li> -->
            </ol>
        </section>
    @php
        $last_inserted_employee = session('last_inserted_employee');
        if(empty($last_inserted_employee)){
          $last_inserted_employee = 0;
        }
    @endphp
    <!-- Main content -->
        <section class="content">
            <!-- Small boxes (Stat box) -->
            <div class="row">
                <div class="col-md-12">
                    <!-- Custom Tabs -->
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs edit-nav-styling">
                            <li id="basicDetailsTab" class=""><a href="#tab_basicDetailsTab" data-toggle="tab">Register Employee</a></li></ul>
                        <!-- Tab content starts here -->
                        <div class="tab-content">
                            <!-- Basic Details starts here -->
                            <div class="tab-pane active" id="tab_basicDetailsTab">
                                @if(session()->has('basicSuccess'))
                                    <div class="alert alert-success alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        {{ session()->get('basicSuccess') }}
                                    </div>
                                @endif
                                <div class="alert-dismissible">
                                    @if(session()->has('success'))
                                        <div class="alert {{(session()->get('error')) ? 'alert-danger' : 'alert-success'}}">
                                            {{ session()->get('success') }}
                                        </div>
                                    @endif
                                </div>
                                @if ($errors->basic->any())
                                    <div class="alert alert-danger alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        <ul>
                                            @foreach ($errors->basic->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if(session()->has('profileError'))
                                    <div class="alert alert-danger alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        {{ session()->get('profileError') }}
                                    </div>
                                @endif
                                @if(session()->has('profileSuccess'))
                                    <div class="alert alert-success alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        {{ session()->get('profileSuccess') }}
                                    </div>
                                @endif
                                @if ($errors->profile->any())
                                    <div class="alert alert-danger alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        <ul>
                                            @foreach ($errors->profile->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if(session()->has('poError'))
                                    <div class="alert alert-danger alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        {{ session()->get('poError') }}
                                    </div>
                                @endif
                                <button class="btn btn-primary">UID {{$data['next_available_uid']}}</button>
                                <!-- form start -->
                                <form id="basicDetailsForm" class="form-horizontal" action="{{ url('employees/create-basic-details') }}" method="POST" enctype="multipart/form-data">
                                    {{ csrf_field() }}

                                    <hr/>
                                    <h3>Profile Details</h3>
                                    <div class="box-body">

                                        <div class="form-group">
                                            <label for="employeeName" class="col-md-2 control-label basic-detail-label">Employee Name<span style="color: red">*</span></label>
                                            <div class="col-md-1 salu-change">
                                                <select class="form-control input-sm basic-detail-input-style" name="salutation" required>
                                                    <option ></option>
                                                    <option value="Mr.">Mr.</option>
                                                    <option value="Ms.">Ms.</option>
                                                    <option value="Mrs.">Mrs.</option>
                                                </select>
                                            </div>
                                            <div class="names123">
                                                <div class="col-md-3 col-sm-4 first-name-basic">
                                                    <input autocomplete="off" type="text" class="form-control input-sm basic-detail-input-style text-capitalize" name="employeeName" id="employeeName" placeholder="Please Enter Alphabets In First Name">
                                                </div>
                                                <div class="col-md-3 col-sm-4 middle-name-basic">
                                                    <input autocomplete="off" type="text" class="form-control input-sm basic-detail-input-style text-capitalize" name="employeeMiddleName" id="employeeMiddleName" placeholder="Please Enter Alphabets In Middle Name">
                                                </div>
                                                <div class="col-md-3 col-sm-4 last-name-basic">
                                                    <input autocomplete="off" type="text" class="form-control input-sm basic-detail-input-style text-capitalize" name="employeeLastName" id="employeeLastName" placeholder="Please Enter Alphabets In Last Name">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group form-sidechange">
                                            <div class="row">
                                                <div class="col-md-6 basic-detail-left">
                                                    <div class="row field-changes-below">
                                                        <label for="employeeXeamCode" class="col-md-4 control-label basic-detail-label">User Id<br/>(For login)<span style="color: red">*</span></label>
                                                        <div class="col-md-8 basic-input-left">
                                                            <input autocomplete="off" type="text" class="form-control checkAjax input-sm basic-detail-input-style" name="employeeXeamCode" id="employeeXeamCode" placeholder="Eg. 1234)" value="{{@$data['next_available_empCode']}}">
                                                            <span class="checkEmployeeXeamCode"></span>
                                                        </div>
                                                    </div>
                                                    <div class="row field-changes-below">
                                                        <label for="email" class="col-md-4 control-label basic-detail-label">Official Email<span style="color: red">*</span></label>
                                                        <div class="col-md-8 basic-input-left">
                                                            <input autocomplete="off" type="email" class="form-control checkAjax input-sm basic-detail-input-style" id="email" name="email" placeholder="Please Enter Valid Email Id" required>
                                                            <span class="checkEmail"></span>
                                                        </div>
                                                    </div>
                                                    <div class="row field-changes-below">
                                                        <label for="mobile" class="col-md-4 control-label basic-detail-label">Mobile Number<span style="color: red">*</span></label>
                                                        <div class="col-md-8 basic-input-left">
                                                            <div class="row">
                                                                <div class="col-md-4 basic-detail-mob-left">
                                                                    <select class="form-control input-sm basic-detail-input-style" name="mobileStdId">
                                                                        @if(!$data['countries']->isEmpty())
                                                                            @foreach($data['countries'] as $country)
                                                                                <option value="{{$country->id}}" @if(@$country->phone_code == '91'){{'selected'}}@endif>(+{{@$country->phone_code}}) {{@$country->iso3}}
                                                                                </option>
                                                                            @endforeach
                                                                        @endif
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-8 basic-detail-mob-right">
                                                                    <input autocomplete="off" type="text" class="form-control checkAjax input-sm basic-detail-input-style" id="mobile" name="mobile" placeholder="Please Enter 10 Digits Numeric Value In Mobile Number">
                                                                    <span class="checkMobile"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row field-changes-below">
                                                        <label for="xeamCode" class="col-md-4 control-label basic-detail-label">Xeam employee code<br/>(For salary slip)</label>
                                                        <div class="col-md-8 basic-input-left">
                                                            <input autocomplete="off" type="text" class="form-control input-sm basic-detail-input-style" name="xeam_emp_code" id="xeamCode" placeholder="Please Enter Employee's Code." value="{{@$data['user']->employee->employee_id}}" >
                                                        </div>
                                                    </div>

                                                    <div class="row field-changes-below">
                                                        <label for="sickLeave" class="col-md-4 control-label basic-detail-label bdl-exp">Sick Leave Pool*</label>
                                                        <div class="col-md-8 basic-input-right">
                                                            <input type="text" class="form-control input-sm
                                                            basic-detail-input-style text-capitalize" id="leavePool_sick" name="leavePool_sick" placeholder="Please Enter Sick Leave pool" value="" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 basic-detail-right">
                                                    <div class="row field-changes-below">
                                                        <label for="birthDate" class="col-md-4 control-label basic-detail-label bdl-exp">Date Of Birth</label>
                                                        <div class="col-md-8 basic-input-right">
                                                            <input autocomplete="off" type="text" class="form-control
                                                             input-sm basic-detail-input-style" id="birthDate" name="birthDate" placeholder="MM/DD/YYYY" value="" readonly required>
                                                            <span class="birthDateErrors"></span>
                                                        </div>
                                                    </div>

                                                    <div class="row field-changes-below">
                                                        <label for="fatherName" class="col-md-4 control-label
                                                        basic-detail-label bdl-exp">Father's Name</label>
                                                        <div class="col-md-8 basic-input-right">
                                                            <input autocomplete="off" type="text" class="form-control
                                                             input-sm basic-detail-input-style" id="fatherName"
                                                                   name="fatherName" placeholder="Enter fatherName" value=""
                                                                   required>
                                                            <span class="fatherName"></span>
                                                        </div>
                                                    </div>
                                                    <br/>
                                                    <div class="row field-changes-below">
                                                        <span class="col-md-4 control-label radio basic-detail-label basic-radio-label bdl-exp"><strong>Gender</strong></span>
                                                        <div class="col-md-8 basic-input-right">
                                                            <label class="basicradio1">
                                                                <input type="radio" name="gender" class="radio-style-basic" id="optionsRadios1" value="Male" checked="">
                                                                Male
                                                            </label>
                                                            <label class="basicradio2-gender">
                                                                <input type="radio" name="gender" class="radio-style-basic2" id="optionsRadios2" value="Female">
                                                                Female
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="row field-changes-below">
                                                        <label for="joiningDate" class="col-md-4 control-label basic-detail-label bdl-exp">Date Of Joining*</label>
                                                        <div class="col-md-8 basic-input-right">
                                                            <input autocomplete="off" type="text" class="form-control
                                                             input-sm basic-detail-input-style" id="joiningDate" name="joiningDate" placeholder="MM/DD/YYYY" value="" readonly required>
                                                        </div>
                                                    </div>
                                                    <div class="row field-changes-below">
                                                        <label for="profilePic" class="col-md-4 control-label basic-detail-label bdl-exp">Profile Picture</label>
                                                        <div class="col-md-8 basic-input-right">
                                                            <input autocomplete="off" type="file" id="profilePic" name="profilePic" class="input-sm">
                                                        </div>
                                                    </div>
                                                    <div class="row field-changes-below">
                                                        <label for="casualLeave" class="col-md-4 control-label basic-detail-label bdl-exp">Casual Leave Pool*</label>
                                                        <div class="col-md-8 basic-input-right">
                                                            <input type="text" class="form-control input-sm
                                                            basic-detail-input-style text-capitalize"
                                                                   id="leavePool_casual" name="leavePool_casual"
                                                                   placeholder="Please Enter Casual Leave pool" value="" required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr/>
                                    <h3>Project Details</h3>
                                    <div class="box-body">
                                        <div class="form-group form-sidechange">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="row field-changes-below">
                                                        <label class="col-md-4 control-label basic-detail-label">Project<span style="color: red">*</span></label>
                                                        <div class="col-md-8 basic-input-left">
                                                            <select class="form-control input-sm basic-detail-input-style" id="projectId" name="projectId">
                                                                <option value="" selected disabled>Please Select Employee's Project.</option>
                                                                @if(!$data['projects']->isEmpty())
                                                                    @foreach($data['projects'] as $project)
                                                                        <option value="{{$project->id}}">{{$project->name}}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row field-changes-below">
                                                        <label class="col-md-4 control-label basic-detail-label">State</label>
                                                        <div class="col-md-8 basic-input-right">
                                                            <select class="form-control input-sm basic-detail-input-style" name="stateId" id="state_field">
                                                                <option value="" selected disabled>Please Select Employee's State.</option>
                                                                @if(!$data['states']->isEmpty())
                                                                    @foreach($data['states'] as $state)
                                                                        <option value="{{$state->id}}">{{$state->name}}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row field-changes-below">
                                                        <label class="col-md-4 control-label basic-detail-label">Zone</label>
                                                        <div class="col-md-8 basic-input-right">
                                                            <select class="form-control input-sm basic-detail-input-style" name="departmentId" id="departmentId">
                                                                <option value="" selected disabled>Please Select Employee's Department</option>
                                                                @if(!$data['departments']->isEmpty())
                                                                    @foreach($data['departments'] as $department)
                                                                        <option value="{{$department->id}}">{{$department->name}}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <!-- </div>   -->
                                                    <div class="row field-changes-below">
                                                        <label class="col-md-4 control-label basic-detail-label">Designation<span style="color: red">*</span></label>
                                                        <div class="col-md-8 basic-input-right">
                                                            <select class="form-control input-sm basic-detail-input-style" name="designation" id="designation_id">
                                                                <option value="" selected disabled>Please Select Employee's Designation.</option>
                                                                @if(!$data['designations']->isEmpty())
                                                                    @foreach($data['designations'] as $designation)
                                                                        <option value="{{$designation->id}}">{{$designation->name}}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row field-changes-below">
                                                        <label class="col-md-4 control-label basic-detail-label">City</label>
                                                        <div class="col-md-8 basic-input-right">
                                                            <select required="" class="form-control input-sm basic-detail-input-style select2" multiple="" name="locationId[]" style="width: 300px; height: 200px;" id="city_field">
                                                                <option value=""  disabled>Please Select Employee's Location.</option>
                                                                @if(!$data['locations']->isEmpty())
                                                                    @foreach($data['locations'] as $location)
                                                                        <option value="{{$location->id}}">{{$location->name}}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row field-changes-below">
                                                        <label class="col-md-4 control-label
                                                        basic-detail-label">Consultant</label>
                                                        <div class="col-md-8 basic-input-right">
                                                            <label class="basicradio1">
                                                                <input type="radio" name="is_consultant"
                                                                       class="radio-style-basic" id="optionsRadios1"
                                                                       value="1" checked="">
                                                                Is Consultant
                                                            </label>
                                                            <label class="basicradio2-gender">
                                                                <input type="radio" name="is_consultant"
                                                                       class="radio-style-basic2" id="optionsRadios2"
                                                                       value="0"> Not Consultant
                                                            </label>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <hr/>
                                    <h3>Bank Details</h3>
                                    <div class="box-body">
                                        <div class="form-group form-sidechange">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="row field-changes-below">
                                                        <label class="col-md-4 control-label
                                                        basic-detail-label">Bank<span style="color:
                                                        red">*</span></label>
                                                        <div class="col-md-8 basic-input-left">
                                                            <select class="form-control input-sm
                                                            basic-detail-input-style" id="bankId" name="bankId" required>
                                                                <option value="" selected disabled>Please Select
                                                                    Employee's Bank.</option>
                                                                @if(!$data['banks']->isEmpty())
                                                                    @foreach($data['banks'] as  $bank)
                                                                        <option value="{{ $bank->id }}">
                                                                            {{ $bank->name }}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row field-changes-below">
                                                        <label class="col-md-4 control-label
                                                        basic-detail-label">Bank Account Number <span style="color:
                                                        red">*</span></label>
                                                        <div class="col-md-8 basic-input-right">
                                                            <input class="form-control input-sm
                                                            basic-detail-input-style" type="text" name="bankAccNo"
                                                                   id="bankAccNo" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row field-changes-below">
                                                        <label class="col-md-4 control-label
                                                        basic-detail-label">Bank IFSC Code <span style="color:
                                                        red">*</span></label>
                                                        <div class="col-md-8 basic-input-right">
                                                            <input class="form-control input-sm
                                                            basic-detail-input-style" type="text" name="ifsc"
                                                                   id="ifsc" required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <!-- /.box-body -->
                                    <div class="box-footer create-footer">
                                        <button type="button" class="btn btn-info basicFormSubmit" id="basicFormSubmit" value="sc">Save</button>
                                        {{--                                        <button type="button" class="btn btn-default basicFormSubmit" value="sc">Save & Exit</button>--}}
                                    </div>
                                    <!-- /.box-footer -->
                                </form>
                            </div>
                            <!-- Basic Details Ends here -->
                        </div>
                        <!-- Tab content Ends here -->
                    </div>
                    <!-- nav-tabs-custom -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
            <!-- Main row -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper ends here -->
    <script src="{!!asset('public/admin_assets/plugins/sweetalert/sweetalert.min.js')!!}"></script>
    <script src="{{asset('public/admin_assets/plugins/validations/jquery.validate.js')}}"></script>
    <script src="{{asset('public/admin_assets/plugins/validations/additional-methods.js')}}"></script>
    <script type="text/javascript">
        $("#noEmployeeProfile").hide();
        $("#noEmployeeDocument").hide();
        $("#noEmployeeAddress").hide();
        $("#noEmployeeAccount").hide();
        $("#noEmployeeHistory").hide();
        $("#noEmployeeReference").hide();
        $("#noEmployeeSecurity").hide();
        $(".spouseWorking").hide();
        $("select").on("select2:close", function (e) {
            $(this).valid();
        });
        $(".contractSigned").on('click',function(){
            var value = $(this).val();
            if(value == 0){
                $(".contractSignedDateDiv").hide();
            }else{
                $(".contractSignedDateDiv").show();
            }
        });
        $(".nomineeInsurance").hide();
        $(".nomineeType").on('click',function(){
            var value = $(this).val();
            if(value == 'Insurance'){
                $(".nomineeInsurance").show();
                $(".noNomineeType").show();
            }else if(value == 'PF'){
                $(".nomineeInsurance").hide();
                $(".noNomineeType").show();
            }else{
                $(".nomineeInsurance").hide();
                $(".noNomineeType").hide();
            }
        });
        $('.uploadFile').on('click',function(){
            var docTypeId = $(this).data("doctypeid");
            var docTypeName = $(this).data("doctypename");
            $("#docTypeId").val(docTypeId);
            $("#docTypeName").val(docTypeName);
            $('#uploadModal').modal('show');
        });
        $('.uploadQualificationFile').on('click',function(){
            var empQualificationId = $(this).data("empqualificationid");
            var docName = $(this).data("docname");
            $("#empQualificationId").val(empQualificationId);
            $("#docName").val(docName);
            $('#uploadQualificationModal').modal('show');
        });
        var allowFormSubmit = {referralCode: 1, email: 1, mobile: 1, employeeXeamCode: 1, oldXeamCode: 1, marriageDate: 1, birthDate: 1};
        var employeeHistorySubmit = {fromDate: 1, toDate: 1};
        var allowProfileFormSubmit = {contractDate: 1};
        $("div.alert-dismissible").fadeOut(6000);
        $("#basicDetailsForm").validate({
            rules :{
                "employeeName" : {
                    required : true,
                    maxlength: 20,
                    minlength: 1,
                    spacespecial: true,
                    lettersonly: true
                },
                "employeeMiddleName" : {
                    maxlength: 20,
                    minlength: 1,
                    spacespecial: true,
                    lettersonly: true
                },
                "employeeXeamCode" : {
                    required : true,
                    maxlength: 25,
                    lettersonlyforxeamcode : true
                },
                "email" : {
                    required : true,
                    email: true,
                },
                "joiningDate" : {
                    required : true
                },
                "leavePool_casual" : {
                    required : true
                },
                "leavePool_sick" : {
                    required : true
                },
                "password" : {
                    required : true,
                    nowhitespace: true,
                    maxlength: 20,
                    minlength: 6
                },
                "mobile" : {
                    required : true,
                    digits: true,
                    exactlengthdigits : 10
                },
            },
            errorPlacement: function(error, element) {
                if (element.hasClass('select2')) {
                    error.insertAfter(element.next('span.select2'));
                } else {
                    error.insertAfter(element);
                }
            },
            messages :{
                "employeeName" : {
                    required : 'Please enter employee first name.',
                    maxlength: 'Maximum 20 characters are allowed.',
                    minlength: 'Minimum 3 characters are allowed.'
                },
                "employeeMiddleName" : {
                    required : 'Please enter employee middle name.',
                    maxlength: 'Maximum 20 characters are allowed.',
                    minlength: 'Minimum 3 characters are allowed.'
                },
                "employeeXeamCode" : {
                    required : 'Please enter employee code.',
                    maxlength: 'Maximum 20 characters are allowed.',
                    minlength: 'Minimum 6 characters are allowed.'
                },
                "joiningDate":{
                    required : 'Please enter Joining Date.',
                },
                "email" : {
                    required : 'Please enter email.',
                },
                "password" : {
                    required : 'Please enter password.',
                    maxlength: 'Maximum 20 characters are allowed.',
                    minlength: 'Minimum 6 characters are allowed.'
                },
                "leavePool_casual":{
                    required : 'Please enter Casual levae Pool.',
                },
                "leavePool_sick":{
                    required : 'Please enter Sick leave Pool.',
                },
                "mobile" : {
                    required : 'Please enter mobile number.',
                }
            }
        });
        $("#profileDetailsForm").validate({
            rules :{
                "permissionIds[]" : {
                    required : true,
                },
                "profilePic" : {
                    accept: "image/*",
                    filesize: 1048576    //1 MB
                },
                "employeeIds" : {
                    required : true,
                },
                "projectId" : {
                    required : true
                },
                "stateId" : {
                    required : true
                },
                "locationId" : {
                    required : true
                },
                "designation" : {
                    required : true
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
                "permissionIds[]" : {
                    required : 'Please select a permission.',
                },
                "profilePic" : {
                    accept : 'Please select a valid image format.',
                    filesize: 'Filesize should be less than 1 MB.'
                },
                "employeeIds" : {
                    required : 'Please select a reporting manager.'
                },
                "projectId" : {
                    required : 'Please select a project.'
                },
                "roleId" : {
                    required : 'Please select a role.'
                },
                "stateId" : {
                    required : 'Please select a State'
                },
                "locationId" : {
                    required : 'Please select a Location'
                },
                "designation" : {
                    required : 'Please select a Designation'
                }
            }
        });
        $("#securityDetailsForm").validate({
            rules :{
                "ddDate" : {
                    required : true,
                },
                "ddNo" : {
                    required : true,
                    digits : true
                },
                "bankName" : {
                    required : true,
                    lettersonly : true,
                    spacespecial : true
                },
                "accNo" : {
                    required : true,
                    digits : true
                },
                "receiptNo" : {
                    required : true,
                    digits : true
                },
                "amount" : {
                    required : true,
                    digitscoveramount: true
                }
            },
            messages :{
                "ddDate" : {
                    required : 'Please select a date.',
                },
                "ddNo" : {
                    required : 'Please enter DD number.',
                },
                "bankName" : {
                    required : 'Please enter bank name.',
                },
                "accNo" : {
                    required : 'Please enter account number.',
                },
                "receiptNo" : {
                    required : 'Please enter receipt number.',
                },
                "amount" : {
                    required : 'Please enter amount.',
                }
            }
        });
        $("#accountDetailsForm").validate({
            rules :{
                "adhaar" : {
                    required : true,
                    digits: true,
                    exactlengthdigits : 12
                },
                "panNo" : {
                    required : true,
                    exactlengthpan : 10,
                    checkpanno : true
                },
                "empEsiNo":{
                    digits: true
                },
                "prevEmpEsiNo":{
                    digits: true
                },
                "empDispensary":{
                    alphanumeric: true
                },
                "uanNo" : {
                    digits: true,
                    exactlengthdigits : 12
                },
                "prevUanNo":{
                    digits: true,
                    exactlengthdigits : 12
                },
                "accHolderName" : {
                    required : true,
                    minlength: 3,
                    spacespecial: true,
                    lettersonly: true
                },
                "bankAccNo" : {
                    required : true,
                    digits : true
                },
                "ifsc" : {
                    required : true,
                    exactlength : 11
                },
                "pfNoDepartment" : {
                    PFAccount: true
                }
            },
            messages :{
                "adhaar" : {
                    required : 'Please enter adhaar number.',
                },
                "panNo" : {
                    required : 'Please enter pan number.',
                },
                // "uanNo" : {
                //     required : 'Please enter uan number.',
                // },
                "accHolderName" : {
                    required : 'Please enter account holder name.',
                    maxlength: 'Maximum 20 characters are allowed.',
                    minlength: 'Minimum 3 characters are allowed.'
                },
                "bankAccNo" : {
                    required : 'Please enter bank account number.',
                },
                "ifsc" : {
                    required : 'Please enter ifsc code.',
                },
                "pfNoDepartment" : {
                    required : 'Please enter pf number for department file.',
                }
            }
        });
        $("#addressDetailsForm").validate({
            rules :{
                "preHouseNo" : {
                    required : true
                },
                "preRoadStreet" : {
                    required : true,
                    locality : true
                },
                "preLocalityArea" : {
                    required : true,
                    locality : true
                },
                "perHouseNo" : {
                    required : true
                },
                "perRoadStreet" : {
                    required : true,
                    locality : true
                },
                "perLocalityArea" : {
                    required : true,
                    locality : true
                },
                "perPinCode" : {
                    required : true,
                    digits : true,
                    maxlength :6
                },
                "prePinCode" : {
                    required : true,
                    digits : true,
                    maxlength :6
                },
                "perEmergencyNumber": {
                    digits : true,
                    exactlengthdigits : 10
                },
                "preEmergencyNumber": {
                    digits : true,
                    exactlengthdigits : 10
                }
            },
            messages :{
                "preHouseNo" : {
                    required : 'Please enter house number.',
                },
                "preRoadStreet" : {
                    required : 'Please enter road/street name.',
                },
                "preLocalityArea" : {
                    required :'Please enter locality/area name.',
                },
                "perHouseNo" : {
                    required : 'Please enter house number.',
                },
                "perRoadStreet" : {
                    required : 'Please enter road/street name.',
                },
                "perLocalityArea" : {
                    required : 'Please enter locality/area name.',
                },
                "perPinCode" : {
                    required : 'Please enter pincode.',
                },
                "prePinCode" : {
                    required : 'Please enter pincode.',
                }
            }
        });
        $("#documentDetailsForm").validate({
            rules :{
                "docs2[]" : {
                    required: true,
                    extension: "jpeg|jpg|png|pdf|doc",
                    filesize: 2097152  //2 MB
                }
            },
            messages :{
                "docs2[]" : {
                    required: 'Please select a file',
                    extension : 'Please select a file in jpg, jpeg, png, pdf or doc format only.',
                    filesize: 'Filesize should be less than 2 MB.'
                }
            }
        });
        $("#qualificationDocumentDetailsForm").validate({
            rules :{
                "qualificationDocs[]" : {
                    required: true,
                    extension: "jpeg|jpg|png|pdf|doc",
                    filesize: 2097152  //2 MB
                }
            },
            messages :{
                "qualificationDocs[]" : {
                    required: 'Please select a file',
                    extension : 'Please select a file in jpg, jpeg, png, pdf or doc format only.',
                    filesize: 'Filesize should be less than 2 MB.'
                },
            }
        });
        $("#historyDetailsForm").validate({
            rules :{
                "fromDate" : {
                    required : true,
                },
                "toDate" : {
                    required : true,
                },
                "orgName" : {
                    required : true,
                    alphanumericWithSpace : true
                },
                "orgPhone" : {
                    required : true,
                    Digits : true
                },
                "orgEmail" : {
                    required : true,
                    email : true
                },
                "orgWebsite" : {
                    url : true
                },
                "responsibilities" : {
                    required : true,
                },
                "reportTo" : {
                    required : true,
                },
                "salaryPerMonth" : {
                    required : true,
                    digitscoveramount: true
                },
                "perks" : {
                    required : true,
                    spacespecial : true,
                    lettersonly : true
                },
                "leavingReason" : {
                    required : true,
                    spacespecial : true,
                    lettersonly : true
                },
                "orgPhoneStdCode" : {
                    required : true,
                    digits : true
                }
            },
            messages :{
                "fromDate" : {
                    required : 'Please select from date.',
                },
                "toDate" : {
                    required : 'Please select to date.',
                },
                "orgName" : {
                    required : 'Please enter organization name.',
                },
                "orgPhone" : {
                    required : 'Please enter organization phone number.',
                    minlength : 'Phone number should be of minimum 10 digits.',
                    maxlength : 'Phone number should be of maximum 12 digits.'
                },
                "orgPhoneStdCode" : {
                    required : 'Please enter STD code.',
                },
                "orgEmail" : {
                    required : 'Please enter organization email.',
                },
                "responsibilities" : {
                    required : 'Please enter responsibilities.',
                },
                "reportTo" : {
                    required : "Please enter the person's name you reported to.",
                },
                "salaryPerMonth" : {
                    required : "Please enter the salary per month.",
                    maxlength : 'Salary per month should be of maximum 7 digits.'
                },
                "perks" : {
                    required : "Please enter the perks.",
                },
                "leavingReason" : {
                    required : "Please enter the reason for leaving.",
                },
                "orgWebsite" : {
                    url : 'Please enter full website url with http:// or https://.'
                }
            }
        });
        $("#referenceDetailsForm").validate({
            rules :{
                "ref1Name" : {
                    required : true,
                    lettersonly : true,
                    spacespecial : true
                },
                "ref1Address" : {
                    required : true,
                },
                "ref1Email" : {
                    required : true,
                    email : true
                },
                "ref1Phone" : {
                    required : true,
                    digits : true,
                    exactlengthdigits : 10
                },
                "ref2Email" : {
                    required : true,
                    email : true
                },
                "ref2Name" : {
                    required : true,
                    lettersonly : true,
                    spacespecial : true
                },
                "ref2Address" : {
                    required : true,
                },
                "ref2Phone" : {
                    required : true,
                    digits : true,
                    exactlengthdigits : 10
                }
            },
            messages :{
                "ref1Name" : {
                    required : 'Please enter name.',
                },
                "ref1Phone" : {
                    required : 'Please enter mobile number.',
                },
                "ref1Email" : {
                    required : 'Please enter email.',
                },
                "ref1Address" : {
                    required : 'Please enter address.',
                },
                "ref2Name" : {
                    required : 'Please enter name.',
                },
                "ref2Phone" : {
                    required : 'Please enter mobile number.',
                },
                "ref2Email" : {
                    required : 'Please enter email.',
                },
                "ref2Address" : {
                    required : 'Please enter address.',
                }
            }
        });
        $.validator.addMethod('filesize', function(value, element, param) {
            return this.optional(element) || (element.files[0].size <= param)
        });
        $.validator.addMethod("checkpanno", function(value, element) {
            return this.optional(element) || /^[a-zA-Z]+[0-9]+[a-zA-Z]+$/i.test(value);
        }, "Please enter only alphanumeric value.");
        $.validator.addMethod("alphanumericWithSpace", function(value, element) {
            return this.optional(element) || /^[A-Za-z][A-Za-z. \d]*$/i.test(value);
        }, "Please enter only alphanumeric value.");
        $.validator.addMethod("digitHifun", function(value, element) {
            return this.optional(element) || /^[0-9- ]+$/i.test(value);
        }, "Please enter only digits and -.");
        $.validator.addMethod("locality", function(value, element) {
            return this.optional(element) || /^[A-Za-z][A-Za-z.\ \d]*$/i.test(value);
        }, "Please enter only alphanumeric value.");
        $.validator.addMethod("email", function(value, element) {
            return this.optional(element) || /^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+\.[a-zA-Z.]{2,5}$/i.test(value);
        }, "Please enter a valid email address.");
        $.validator.addMethod("spacespecial", function(value, element) {
            return this.optional(element) || /^[a-zA-Z0-9-,]+(\s{0,1}[a-zA-Z0-9-, ])*$/i.test(value);
        },"Please do not start with space or special characters.");
        $.validator.addMethod("lettersonly", function(value, element) {
            return this.optional(element) || /^[a-z," "]+$/i.test(value);
        }, "Please enter only alphabets and spaces.");
        $.validator.addMethod( "nowhitespace", function( value, element ) {
            return this.optional( element ) || /^\S+$/i.test( value );
        }, "Please do not enter white space." );
        jQuery.validator.addMethod("exactlength", function(value, element, param) {
            return this.optional(element) || value.length == param;
        }, $.validator.format("Please enter exactly {0} characters."));
        jQuery.validator.addMethod("exactlengthpan", function(value, element, param) {
            return this.optional(element) || value.length == param;
        }, $.validator.format("Please enter exactly {0} Alphanumeric value."));
        jQuery.validator.addMethod("exactlengthdigits", function(value, element, param) {
            return this.optional(element) || value.length == param;
        }, $.validator.format("Please enter exactly {0} digits."));
        $.validator.addMethod("digitscoveramount", function(value, element) {
            return this.optional(element) || /^[0-9, .]+$/i.test(value);
        }, "Please enter only digits");
        $.validator.addMethod("PFAccount", function(value, element) {
            return this.optional(element) || /^[a-zA-Z]+[/]+[a-zA-Z]+[/]+[0-9]+$/i.test(value);
        }, "Please enter Region/Sub-regional Office code/EPF account number.");
        $.validator.addMethod("lettersonlyforcode", function(value, element) {
            return this.optional(element) || /^[a-z,-]+[0-9]+$/i.test(value);
        }, "Please enter valid pattern. (Eg. [XEAM-1234] OR [XEAM-TR-1234])");
        $.validator.addMethod("lettersonlyforxeamcode", function(value, element) {
            return this.optional(element) || /[0-9A-Za-z-]+$/i.test(value);
        }, "Please enter valid pattern. (Eg. [1234] OR [TR-1234])");
    </script>
    <script type="text/javascript">
        //On change
        $("#languageIds.select2").on('change',function(){
            var arr = $(this).val();
            length = arr.length;
            var display = '';
            for(var i=0; i < length; i++){
                var langName = $("#languageIds option[value='"+ arr[i] + "']").text();
                var checkBoxes = '<div class="row field-changes-below"><div class="col-sm-4"><strong class="basic-lang-label">'+langName+'</strong></div><div class="col-sm-8 langright"><label class="checkbox-inline"><input type="checkbox" value="1" name="lang'+arr[i]+'[]">Read</label><label class="checkbox-inline"><input type="checkbox" value="2" name="lang'+arr[i]+'[]">Write</label><label class="checkbox-inline"><input type="checkbox" value="3" name="lang'+arr[i]+'[]">Speak</label></div></div>';
                display += checkBoxes;
            }
            $(".languageCheckboxes").html("");
            $(".languageCheckboxes").append(display);
        });
    </script>
    <script type="text/javascript">
        var tabname = "{{@$data['tabname']}}";
        var last_inserted_employee = "{{$last_inserted_employee}}";
        console.log('last_inserted_employee: ',last_inserted_employee);
        $(document).ready(function(){
            $('.nav-tabs a[href="#tab_'+tabname+'"]').tab('show');
            $('#projectId').on('change',function(){
                var project_id = $(this).val();
                $.ajax({
                    type: 'POST',
                    url: "{{ url('employees/project-information') }}",
                    data: {project_id: project_id},
                    success: function (result) {
                        $("#companyId").val(result.project.company.name);
                        $("#pfNo").val(result.project.company.pf_account_number);
                        $("#tanNo").val(result.project.company.tan_number);
                        //$("#certificateNo").val(result.certificateNo);
                        $("#ptStateId").val(result.states);
                        $("#esiLocationId").val(result.locations);
                        //$("#esiNo").val(result.esiNo);
                        $("#salaryHeadId").val(result.project.salary_structure.name);
                        $("#salaryCycleId").val(result.project.salary_cycle.name);
                    }
                });
            });
            $('.maritalStatus').on('click',function(){
                var marStatus = $(this).val();
                if(marStatus == "Unmarried"){
                    $(".spouseName").hide();
                    $(".spouseWorking").hide();
                }else if(marStatus == "Married"){
                    $(".spouseName").show();
                    $("#spouseWorkingStatus").show();
                    $("#spouseWorkingStatus1").prop("checked", true);
                    $(".spouseWorking").hide();
                }else{
                    $(".spouseName").show();
                    $(".spouseWorking").hide();
                    $("#spouseWorkingStatus").hide();
                }
            });
            $('.spouseWorkingStatus').on('click',function(){
                var marStatus = $(".maritalStatus").val();
                var workingStatus = $('input[name=spouseWorkingStatus]:checked').val();
                if(marStatus == "Married" && workingStatus == "Yes"){
                    $(".spouseWorking").show();
                }else{
                    $(".spouseWorking").hide();
                }
            });
            // var mdDataName = 'ghdfgh';
            // var mdDataId = '0';
            // $("#departmentId").on('change', function(){
            // 	var departmentId = $(this).val();
            // 	var mdFlag = 0;
            // 	if(departmentId){
            // 		$.ajax({
            // 			type: 'POST',
            // 			url: "{{ url('employees/departments-wise-employees')}} ",
            // 			data: {departmentId: departmentId},
            // 			success: function (result) {
            // 				$("#employeeIds").empty();
            // 				if(result.length != 0){
            // 					$("#employeeIds").append("<option value='' selected disabled>Please Select Employee's Reporting Manager</option>");
            // 					$.each(result,function(key,value){
            // 						if(value == mdDataId){
            //                mdFlag = 1;
            //              }
            // 	$("#employeeIds").append('<option value="'+value+'">'+key+'</option>');
            // });
            // if(mdFlag == 0){
            //              if(mdDataName){
            //                $("#employeeIds").append('<option value="'+mdDataId+'">'+mdDataName+'</option>');
            //              }
            //          }
            //          if(departmentId == 12){
            //          	$("#employeeIds").append('<option value="37">'+"Amit Setia"+'</option>');
            //          }
            // 				}else{
            // $("#employeeIds").append("<option value='' selected disabled>None</option>");
            // if(mdFlag == 0){
            //              if(mdDataName){
            //                $("#employeeIds").append('<option value="'+mdDataId+'">'+mdDataName+'</option>');
            //              }
            //          }
            // 				}
            // 			}
            // 		});
            // 	}
            // });
            $('#perStateId').on('change', function(){
                var stateId = $(this).val();
                var stateIds = [];
                stateIds.push(stateId);
                $('#perCityId').empty();
                var displayString = "";
                $.ajax({
                    type: 'POST',
                    url: "{{ url('employees/states-wise-cities') }} ",
                    data: {stateIds: stateIds},
                    success: function(result){
                        if(result.length != 0){
                            result.forEach(function(city){
                                displayString += '<option value="'+city.id+'">'+city.name+'</option>';
                            });
                        }else{
                            displayString += '<option value="" selected disabled>None</option>';
                        }
                        $('#perCityId').append(displayString);
                    }
                });
            }).change();
            $('#preStateId').on('change', function(){
                var stateId = $(this).val();
                var stateIds = [];
                stateIds.push(stateId);
                $('#preCityId').empty();
                var displayString = "";
                $.ajax({
                    type: 'POST',
                    url: "{{ url('employees/states-wise-cities') }} ",
                    data: {stateIds: stateIds},
                    success: function(result){
                        if(result.length != 0){
                            result.forEach(function(city){
                                displayString += '<option value="'+city.id+'">'+city.name+'</option>';
                            });
                        }else{
                            displayString += '<option value="" selected disabled>None</option>';
                        }
                        $('#preCityId').append(displayString);
                    }
                });
            }).change();
            $(".checkAjax").on("keyup",function(event){
                var referralCode = $("#referralCode").val();
                var email = $("#email").val();
                var mobile = $("#mobile").val();
                var employeeXeamCode = $("#employeeXeamCode").val();
                var oldXeamCode = $("#oldXeamCode").val();
                $.ajax({
                    type: 'POST',
                    url: "{{ url('employees/check-unique-employee') }}",
                    data: {referralCode: referralCode,email: email,mobile: mobile, employeeXeamCode: employeeXeamCode, oldXeamCode: oldXeamCode},
                    success: function (result) {
                        console.log(result);
                        if(result.referralMatch == "yes"){
                            $(".checkReferral").removeClass("text-warning");
                            $(".checkReferral").addClass("text-success").text("Referral code matched successfully.");
                            allowFormSubmit.referralCode = 1;
                        }else if(result.referralMatch == "no"){
                            $(".checkReferral").removeClass("text-success");
                            $(".checkReferral").addClass("text-warning").text("Referral code does not matches.");
                            allowFormSubmit.referralCode = 0;
                        }else if(result.referralMatch == "blank"){
                            $(".checkReferral").text("");
                            allowFormSubmit.referralCode = 1;
                        }
                        if(result.emailUnique == "no"){
                            $(".checkEmail").addClass("text-warning").text("Email already exists.");
                            allowFormSubmit.email = 0;
                        }else if(result.emailUnique == "yes"){
                            $(".checkEmail").text("");
                            allowFormSubmit.email = 1;
                        }else if(result.emailUnique == "blank"){
                            $(".checkEmail").text("");
                            allowFormSubmit.email = 0;
                        }
                        if(result.employeeXeamCodeUnique == "no"){
                            $(".checkEmployeeXeamCode").addClass("text-warning").text("Xeam Code already exists.");
                            allowFormSubmit.employeeXeamCode = 0;
                        }else if(result.employeeXeamCodeUnique == "yes"){
                            $(".checkEmployeeXeamCode").text("");
                            allowFormSubmit.employeeXeamCode = 1;
                        }else if(result.employeeXeamCodeUnique == "blank"){
                            $(".checkEmployeeXeamCode").text("");
                            allowFormSubmit.employeeXeamCode = 0;
                        }
                        if(result.oldXeamCodeUnique == "no"){
                            $(".checkOldXeamCode").addClass("text-warning").text("Punch ID already exists.");
                            allowFormSubmit.oldXeamCode = 0;
                        }else if(result.oldXeamCodeUnique == "yes"){
                            $(".checkOldXeamCode").text("");
                            allowFormSubmit.oldXeamCode = 1;
                        }else if(result.oldXeamCodeUnique == "blank"){
                            $(".checkOldXeamCode").text("");
                            allowFormSubmit.oldXeamCode = 0;
                        }
                        if(result.mobileUnique == "no"){
                            $(".checkMobile").addClass("text-warning").text("Mobile already exists.");
                            allowFormSubmit.mobile = 0;
                        }else if(result.mobileUnique == "yes"){
                            $(".checkMobile").text("");
                            allowFormSubmit.mobile = 1;
                        }else if(result.mobileUnique == "blank"){
                            $(".checkMobile").text("");
                            allowFormSubmit.mobile = 0;
                        }
                    }
                });
            });
            $(".basicFormSubmit").click(function(){
                var value = $(this).val();
                $(".basicFormSubmitButton").val(value);
                if(allowFormSubmit.mobile == 1 && allowFormSubmit.email == 1 && allowFormSubmit.employeeXeamCode == 1 ){
                    $("#basicDetailsForm").submit();
                }else{
                    return false;
                }
            });
            $(".profileFormSubmit").click(function(){
                var value = $(this).val();
                $(".profileFormSubmitButton").val(value);
                if(last_inserted_employee != "0"){
                    if(allowProfileFormSubmit.contractDate == 1){
                        $("#noEmployeeProfile").hide();
                        $("#profileDetailsForm").submit();
                    }else{
                        return false;
                    }
                }else{
                    $("#noEmployeeProfile").show();
                    $("#noEmployeeProfile").fadeOut(6000);
                    return false;
                }
            });
            $("#documentFormSubmit").click(function(){
                if(last_inserted_employee != "0"){
                    $("#noEmployeeDocument").hide();
                    $("#documentDetailsForm").submit();
                }else{
                    $('#uploadModal').modal('hide');
                    $("#noEmployeeDocument").show();
                    $("#noEmployeeDocument").fadeOut(6000);
                    return false;
                }
            });
            $("#qualificationDocumentFormSubmit").click(function(){
                if(last_inserted_employee != "0"){
                    $("#noEmployeeDocument").hide();
                    $("#qualificationDocumentDetailsForm").submit();
                }else{
                    $('#uploadQualificationModal').modal('hide');
                    $("#noEmployeeDocument").show();
                    $("#noEmployeeDocument").fadeOut(6000);
                    return false;
                }
            });
            $(".addressFormSubmit").click(function(){
                var value = $(this).val();
                $(".addressFormSubmitButton").val(value);
                if(last_inserted_employee != "0"){
                    $("#noEmployeeAddress").hide();
                    $("#addressDetailsForm").submit();
                }else{
                    $("#noEmployeeAddress").show();
                    $("#noEmployeeAddress").fadeOut(6000);
                    return false;
                }
            });
            $(".accountFormSubmit").click(function(){
                var value = $(this).val();
                $(".accountFormSubmitButton").val(value);
                if(last_inserted_employee != "0"){
                    $("#noEmployeeAccount").hide();
                    $("#accountDetailsForm").submit();
                }else{
                    $("#noEmployeeAccount").show();
                    $("#noEmployeeAccount").fadeOut(6000);
                    return false;
                }
            });
            $(".historyFormSubmit").click(function(){
                var value = $(this).val();
                $(".historyFormSubmitButton").val(value);
                if(last_inserted_employee != "0"){
                    if(employeeHistorySubmit.fromDate == 1 && employeeHistorySubmit.toDate == 1){
                        $("#noEmployeeHistory").hide();
                        $("#historyDetailsForm").submit();
                    }else{
                        return false;
                    }
                }else{
                    $("#noEmployeeHistory").show();
                    $("#noEmployeeHistory").fadeOut(6000);
                    return false;
                }
            });
            $(".referenceFormSubmit").click(function(){
                var value = $(this).val();
                $(".referenceFormSubmitButton").val(value);
                if(last_inserted_employee != "0"){
                    $("#noEmployeeReference").hide();
                    $("#referenceDetailsForm").submit();
                }else{
                    $("#noEmployeeReference").show();
                    $("#noEmployeeReference").fadeOut(6000);
                    return false;
                }
            });
            $("#securityFormSubmit").click(function(){
                if(last_inserted_employee != "0"){
                    $("#noEmployeeSecurity").hide();
                    $("#securityDetailsForm").submit();
                }else{
                    $("#noEmployeeSecurity").show();
                    $("#noEmployeeSecurity").fadeOut(6000);
                    return false;
                }
            });
            $('#checkboxAbove').change(function() {
                if($(this).is(":checked")) {
                    $("#preHouseNo").val($("#perHouseNo").val());
                    //$("#perHouseName").val($("#preHouseName").val());
                    $("#preRoadStreet").val($("#perRoadStreet").val());
                    $("#preLocalityArea").val($("#perLocalityArea").val());
                    $("#prePinCode").val($("#perPinCode").val());
                    $(".preEmergencyNumberStdId").val($(".perEmergencyNumberStdId").val());
                    $("#preEmergencyNumber").val($("#perEmergencyNumber").val());
                    $(".preCountryId").val($('.perCountryId').val());
                    $("#preStateId").val($('#perStateId').val()).trigger('change');
                    setTimeout(() => {
                        $("#preCityId").val($('#perCityId').val());
                    }, 1000);
                }else{
                    $("#preHouseNo").val("");
                    //$("#perHouseName").val("");
                    $("#preRoadStreet").val("");
                    $("#preLocalityArea").val("");
                    $("#prePinCode").val("");
                    $(".preEmergencyNumberStdId").val($(".perEmergencyNumberStdId").val());
                    $("#preEmergencyNumber").val("");
                    $(".preCountryId").val($('.perCountryId').val());
                    $("#preStateId").val($('#perStateId').val()).trigger('change');
                    setTimeout(() => {
                        $("#preCityId").val($('#perCityId').val());
                    }, 1000);
                }
            });
        });
    </script>
    <script type="text/javascript">
        var today = new Date();
        var yesterday = moment().subtract(1, 'days')._d;
        //Date picker
        $("#birthDate").datepicker({
            //format: 'dd/mm/yyyy',
            endDate: yesterday,
            autoclose: true,
            orientation: "bottom"
        });
        $("#marriageDate").datepicker({
            //format: 'dd/mm/yyyy',
            endDate: yesterday,
            autoclose: true,
            orientation: "bottom"
        });
        $("#insuranceExpiryDate").datepicker({
            //format: 'dd/mm/yyyy',
            startDate: today,
            autoclose: true,
            orientation: "bottom"
        });
        $("#fromDate").datepicker({
            //format: 'dd/mm/yyyy',
            endDate: yesterday,
            autoclose: true,
            orientation: "bottom"
        });
        $("#toDate").datepicker({
            //format: 'dd/mm/yyyy',
            endDate: yesterday,
            autoclose: true,
            orientation: "bottom"
        });
        $("#joiningDate").datepicker({
            autoclose: true,
            orientation: "bottom",
            //format: 'dd/mm/yyyy'
        });
        $("#contractSignedDate").datepicker({
            //format: 'dd/mm/yyyy',
            endDate: today,
            autoclose: true,
            orientation: "bottom"
        });
        $("#ddDate").datepicker({
            autoclose: true,
            orientation: "bottom",
            //format: 'dd/mm/yyyy'
        });
        // $("#contractSignedDate").on('change',function(){
        // 	var date = $(this).val();
        // 	if(Date.parse(date) > Date.parse(today)){
        // 		allowProfileFormSubmit.contractDate = 0;
        // 		$(".contractSignedDateErrors").text("Please select a valid date.").css("color","#f00");
        //     	$(".contractSignedDateErrors").show();
        //     	return false;
        // 	}else{
        // 		allowProfileFormSubmit.contractDate = 1;
        // 		$(".contractSignedDateErrors").text("");
        //     	$(".contractSignedDateErrors").hide();
        // 	}
        // });
        $("#birthDate").on('change',function(){
            var date = $(this).val();
            var marriageDate = $('#marriageDate').val();
            // if(Date.parse(date) > Date.parse(yesterday)){
            //   allowFormSubmit.birthDate = 0;
            //   $(".birthDateErrors").text("Please select a valid date.");
            //   $(".birthDateErrors").show();
            //   return false;
            // }
            if(Date.parse(date) >= Date.parse(marriageDate)){
                allowFormSubmit.birthDate = 0;
                $(".birthDateErrors").text("Birth date should be less than marriage date.");
                $(".birthDateErrors").show();
            }else{
                allowFormSubmit.birthDate = 1;
                $(".birthDateErrors").text("");
                $(".birthDateErrors").hide("");
            }
        });
        $("#fromDate").on('change',function(){
            var date = $(this).val();
            var toDate = $('#toDate').val();
            // if(Date.parse(date) > Date.parse(yesterday)){
            //   employeeHistorySubmit.fromDate = 0;
            //   $(".fromDateErrors").text("Please select a valid date.").css('color','#f00');
            //   $(".fromDateErrors").show();
            //   return false;
            // }
            if(Date.parse(date) >= Date.parse(toDate)){
                employeeHistorySubmit.fromDate = 0;
                $(".fromDateErrors").text("From date should be less than To date.").css('color','#f00');
                $(".fromDateErrors").show();
            }else{
                employeeHistorySubmit.fromDate = 1;
                $(".fromDateErrors").text("");
                $(".fromDateErrors").hide("");
            }
        });
        $("#toDate").on('change',function(){
            var date = $(this).val();
            var fromDate = $('#fromDate').val();
            // if(Date.parse(date) > Date.parse(yesterday)){
            //   employeeHistorySubmit.toDate = 0;
            //   $(".toDateErrors").text("Please select a valid date.").css('color','#f00');
            //   $(".toDateErrors").show();
            //   return false;
            // }
            if(Date.parse(date) <= Date.parse(fromDate)){
                employeeHistorySubmit.toDate = 0;
                $(".toDateErrors").text("To date should be greater than From date.").css('color','#f00');
                $(".toDateErrors").show();
            }else{
                employeeHistorySubmit.toDate = 1;
                $(".toDateErrors").text("");
                $(".toDateErrors").hide("");
            }
        });
        $("#marriageDate").on('change',function(){
            var date = $(this).val();
            var birthDate = $('#birthDate').val();
            // if(Date.parse(date) > Date.parse(yesterday)){
            //   allowFormSubmit.marriageDate = 0;
            //   $(".marriageDateErrors").text("Please select a valid date.")
            //   $(".marriageDateErrors").show();
            //   return false;
            // }
            if(Date.parse(date) <= Date.parse(birthDate)){
                allowFormSubmit.marriageDate = 0;
                $(".marriageDateErrors").text("Marriage date should be greater than birth date.")
                $(".marriageDateErrors").show();
            }else{
                allowFormSubmit.marriageDate = 1;
                $(".marriageDateErrors").text("");
                $(".marriageDateErrors").hide("");
            }
        });
        $(document).ready(function(){
            var i=1;
            $('#add').click(function(){
                i++;
                <?php
                $data['days']=array("Sunday", "Monday", "Tuesday", "Wednesday", "Thrusday", "Friday", "Saturday");
                ?>
                $('#dynamic_field').append('<div id="row'+i+'"><div class="col-md-4"></div><div class="col-md-8 basic-input-right appended-shiftss"><div class="col-md-5 es-first-col"><select class="form-control input-sm basic-detail-input-style" name="exceptionshiftTimingId[]"><option value="" selected disabled>Select Shift</option>  <?php foreach($data['shifts'] as $shift){ ?><option value="<?php echo $shift->id; ?> "> <?php echo $shift->name; ?> </option> <?php } ?></select></div> <div class="col-md-4 es-second-col"><select class="form-control input-sm basic-detail-input-style" name="exceptionshiftday[]"><option value="" selected disabled>Select Day.</option><?php   foreach($data['days'] as $key=>$day){  ?>
                    <option value="<?php echo $key; ?>"><?php echo $day; ?></option> <?php } ?>
                    </select></div><div class="col-md-1 es-third-col"><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove"><i class="fa fa-minus"></i></button></div></div></div>'); }); });
        $(document).on('click', '.btn_remove', function(){
            var button_id = $(this).attr("id");
            $('#row'+button_id+'').fadeOut("fast");
        });
        $('#state_field').on('change',function(){
            var stateId = $(this).val();
            $('#city_field').val();
            $.ajax({
                type: 'POST',
                url: "{{ url('employees/state-wise-locations') }}",
                data: {state_id: stateId},
                success: function (result) {
                    console.log('On change state', result);
                    if(result.locations.length != 0){
                        var displayString = '';
                        $.each(result.locations, function(key, value){
                            displayString += '<option value="'+value.id+'">'+value.name+'</option>';
                        });
                        $('#city_field').html(displayString);
                    }
                }
            });
        });
    </script>
@endsection
