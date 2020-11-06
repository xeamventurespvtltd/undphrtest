@extends('admins.layouts.app')
@section('content')

<link rel="stylesheet" href="{{asset('public/admin_assets/plugins/timepicker/bootstrap-timepicker.min.css')}}">
<script src="{{asset('public/admin_assets/plugins/timepicker/bootstrap-timepicker.min.js')}}"></script>
<script src="{{asset('public/admin_assets/plugins/validations/jquery.validate.js')}}"></script>
<script src="{{asset('public/admin_assets/plugins/validations/additional-methods.js')}}"></script>

<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>Job Requisition Form (JRF)</h1>
        <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Locations List</a></li> 
        </ol>
      </section>

    <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-sm-12">
            <div class="box box-primary success">
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

              <div class="alert-dismissible">
                @if(session()->has('success'))
                  <div class="alert {{(session()->get('error')) ? 'alert-danger' : 'alert-success'}}">
                    {{ session()->get('success') }}
                  </div>
                @endif
              </div>
            </div>
        <!-- form start -->
        <form id="jrfRequisitionForm" action="{{ url('jrf/save-jrf') }}" method="POST" enctype="multipart/form-data">
        {{ csrf_field() }}
           <div class="box-body jrf-form-body">
              <div class="row">
                 <div class="col-md-6">
                    <div class="form-group">
                       <div class="row">
                          <div class="col-md-4 label-left-sec">
                             <label for="jrf_department">Department<sup class="ast">*</sup></label>
                          </div>
                          <div class="col-md-8">
                             <select class="form-control basic-detail-input-style regis-input-field" name="department_id" id="jrf_department_id">
                                @if(!$data['departments']->isEmpty())
                                @foreach($data['departments'] as $department)
                                <option value="{{$department->id}}">{{$department->name}}</option>
                                @endforeach
                                @endif
                             </select>
                             @php $user_id = Auth::id(); @endphp
                             <input type="hidden" name="user_id" value="{{@$user_id}}">
                          </div>
                       </div>
                    </div>
                    <div class="form-group">
                       <div class="row">
                          <div class="col-md-4 label-left-sec">
                             <label for="role_id">Job Roles <sup class="ast">*</sup></label>
                          </div>
                          <div class="col-md-8">
                             <select class="form-control select2 input-sm basic-detail-input-style" name="role_id" style="width: 100%;" id="role_id">
                                @if(!$data['roles']->isEmpty())
                                @foreach($data['roles'] as $role)  
                                <option value="{{$role->id}}">{{$role->name}}</option>
                                @endforeach
                                @endif  
                             </select>
                          </div>
                       </div>
                    </div>
                    <div class="form-group">
                       <div class="row">
                          <div class="col-md-4 label-left-sec">
                             <label for="designation_id">Job Designation<sup class="ast">*</sup></label>
                          </div>
                          <div class="col-md-8">
                             <select class="form-control select2 input-sm basic-detail-input-style" name="designation_id" style="width: 100%;" id="designation_id" data-placeholder="Select Designation">
                                @if(!$data['designation']->isEmpty())
                                @foreach($data['designation'] as $desig)  
                                <option value="{{$desig->id}}">{{$desig->name}}</option>
                                @endforeach
                                @endif  
                             </select>
                          </div>
                       </div>
                    </div>
                    <div class="form-group">
                       <div class="row">
                          <div class="col-md-4 label-left-sec">
                             <label for="number_of_positions">Number of Positions<sup class="ast">*</sup></label>
                          </div>
                          <div class="col-md-8">
                             <input type="text" name="number_of_positions" id="number_of_positions" placeholder="Number of positions" class="form-control experiencedata regis-input-field only_numeric">
                          </div>
                       </div>
                    </div>
                    <div class="form-group">
                       <div class="row">
                          <div class="col-md-4 label-left-sec">
                             <label for="">Age Group<sup class="ast">*</sup></label>
                          </div>
                          <div class="col-md-8">
                             <div class="row">
                                <div class="col-xs-6">
                                   <input type="text" name="age_group_from" id="age_group_from" placeholder="From" class="form-control experiencedata regis-input-field only_numeric">
                                </div>
                                <div class="col-xs-6">
                                   <input type="text" name="age_group_to" id="age_group_to" placeholder="To" class="form-control experiencedata regis-input-field only_numeric">
                                </div>
                             </div>
                          </div>
                       </div>
                    </div>
                    <div class="form-group">
                       <div class="row">
                          <div class="col-md-4 label-left-sec">
                             <label for="gender">Gender<sup class="ast">*</sup></label>
                          </div>
                          <div class="col-md-8">
                             <div class="radio">
                                <label>
                                <input type="radio" name="gender" id="optionsRadios1" value="Male" checked="">
                                Male
                                </label>
                                <label>
                                <input type="radio" name="gender" id="optionsRadios2" value="Female">
                                Female
                                </label>
                                <label>
                                <input type="radio" name="gender" id="optionsRadios3" value="Any of Them">
                                Any of Them
                                </label>
                             </div>
                          </div>
                       </div>
                    </div>
                    <div class="form-group">
                       <div class="row">
                          <div class="col-md-4 label-left-sec">
                             <label for="job_location">Job Location<sup class="ast">*</sup></label>
                          </div>
                          <div class="col-md-8">
                             <select class="form-control select2 input-sm basic-detail-input-style" name="city_id[]" multiple="multiple" style="width: 100%;" id="city_id" data-placeholder="Select Location">
                                @if(!$data['cities']->isEmpty())
                                @foreach($data['cities'] as $citi)  
                                <option value="{{$citi->id}}">{{$citi->name}}</option>
                                @endforeach
                                @endif  
                             </select>
                          </div>
                       </div>
                    </div>
                    <div class="form-group">
                       <div class="row">
                          <div class="col-md-4 label-left-sec">
                             <label for="">Shift Timing<sup class="ast">*</sup></label>
                          </div>
                          <div class="col-md-8">
                             <div class="row">
                                <div class="col-xs-6">
                                   <input type="text" name="shift_timing_from" id="shift_timing_from" placeholder="From" class="form-control experiencedata regis-input-field timepicker">
                                </div>
                                <div class="col-xs-6">
                                   <input type="text" name="shift_timing_to" id="shift_timing_to" placeholder="To" class="form-control experiencedata regis-input-field timepicker">
                                </div>
                             </div>
                          </div>
                       </div>
                    </div>
                    <div class="form-group">
                       <div class="row">
                          <div class="col-md-4 label-left-sec">
                             <label for="Job_description">Job Description<sup class="ast">*</sup></label>
                          </div>
                          <div class="col-md-8">
                             <textarea rows="4" cols="50" class="form-control" id="Job_description" name="job_description" placeholder="Brief Description of Duties"></textarea>
                          </div>
                       </div>
                    </div>
                 </div>
                 <div class="col-md-6">
                    <div class="form-group">
                       <div class="row">
                          <div class="col-md-4 label-left-sec">
                             <label for="qualification_id">Qualifications<sup class="ast">*</sup></label>
                          </div>
                          <div class="col-md-8">
                             <select class="form-control select2 input-sm basic-detail-input-style" name="qualification_id[]" multiple="multiple" style="width: 100%;" id="qualification_id" data-placeholder="Select Qualifications">
                                @if(!$data['qualifications']->isEmpty())
                                @foreach($data['qualifications'] as $quali)  
                                <option value="{{$quali->id}}">{{$quali->name}}</option>
                                @endforeach
                                @endif  
                             </select>
                          </div>
                       </div>
                    </div>
                    <div class="form-group">
                       <div class="row">
                          <div class="col-md-4 label-left-sec">
                             <label for="skill_id">Skills<sup class="ast">*</sup></label>
                          </div>
                          <div class="col-md-8">
                             <select class="form-control select2 input-sm basic-detail-input-style" name="skill_id[]" multiple="multiple" style="width: 100%;" id="skill_id" data-placeholder="Select Skills">
                                @if(!$data['skills']->isEmpty())
                                @foreach($data['skills'] as $skill)  
                                <option value="{{$skill->id}}">{{$skill->name}}</option>
                                @endforeach
                                @endif  
                             </select>
                          </div>
                       </div>
                    </div>
                    <div class="form-group">
                       <div class="row">
                          <div class="col-md-4 label-left-sec">
                             <label for="jrf_additional_requirement">Additional Requirement</label>
                          </div>
                          <div class="col-md-8">
                             <input type="text" name="additional_requirement" id="jrf_additional_requirement" placeholder="Please enter Qualifications" class="form-control experiencedata regis-input-field">
                          </div>
                       </div>
                    </div>
                    <div class="form-group">
                       <div class="row">
                          <div class="col-md-4 label-left-sec">
                             <label for="salary_range">Salary Range<sup class="ast">*</sup></label>
                          </div>
                          <div class="col-md-8">
                             <div class="row">
                                <div class="col-xs-6">
                                   <input type="text" name="salary_range_from" id="salary_range_from" placeholder="From" class="form-control experiencedata regis-input-field salary_range_class only_numeric">
                                </div>
                                <div class="col-xs-6">
                                   <input type="text" name="salary_range_to" id="salary_range_to" placeholder="To" class="form-control experiencedata regis-input-field salary_range_class only_numeric">
                                </div>
                             </div>
                          </div>
                       </div>
                    </div>
                    <div class="form-group">
                       <div class="row">
                          <div class="col-md-4 label-left-sec">
                             <label for="salary_range">Year Experience<sup class="ast">*</sup></label>
                          </div>
                          <div class="col-md-8">
                             <div class="row">
                                <div class="col-xs-6">
                                   <input type="text" name="year_experience_from" id="year_experience_from" placeholder="From" class="form-control experiencedata regis-input-field year_experience_class only_numeric">
                                </div>
                                <div class="col-xs-6">
                                   <input type="text" name="year_experience_to" id="year_experience_to" placeholder="To" class="form-control experiencedata regis-input-field year_experience_class only_numeric">
                                </div>
                             </div>
                          </div>
                       </div>
                    </div>
                    <div class="form-group">
                       <div class="row">
                          <div class="col-md-4 label-left-sec">
                             <label for="jrf_industry_type">Industry Types<sup class="ast">*</sup></label>
                          </div>
                          <div class="col-md-8">
                             <input type="text" class="form-control experiencedata regis-input-field" name="industry_type" id="jrf_industry_type" placeholder="Enter Industry">
                          </div>
                       </div>
                    </div>
                 </div>
              </div>
              <div class="text-center">
                 <input type="submit" class="btn btn-info submit-btn-style" id="submit"  value="Submit" name="submit">
              </div>
           </div>
        </form>
        <!-- form end -->
        </div>
      <!-- /.box-body -->
     </div>
  </section>
  </div>
<!-- /.row -->

<script>
    $("#jrfRequisitionForm").validate({
      rules: {
        "jrf_department" : {
          required: true
        },
        "role_id" : {
          required: true
        },
        "number_of_positions" : {
          required: true
        },
        "age_group_from" : {
          required: true
        },
        "age_group_to" : {
          required: true,
          greaterThan: '#age_group_from'
        },
        "gender" : {
          required: true
        },
        "city_id[]" : {
          required: true
        },
        "gender" : {
          required: true
        },
        "shift_timing_from" : {
          required: true
        },
        "shift_timing_to" : {
          required: true
        },
        "job_description" : {
          required: true
        },
        "qualification_id[]" : {
          required: true
        },
        "skill_id[]" : {
          required: true
        },
        "salary_range_from" : {
          required: true
        },
        "salary_range_to" : {
          required: true,
           greaterThan: '#salary_range_from'
        },
        "year_experience_from" : {
          required: true
        },
        "year_experience_to" : {
          required: true,
          greaterThan: '#year_experience_from'
        },
        "industry_type" : {
          required: true
        }
      },
      messages: {
          "jrf_department" : {
            required: 'Select department name'
          },
          "role_id" : {
            required: 'Select Role'
          },
          "number_of_positions" : {
            required: 'Enter number of Positions'
          },
          "age_group_from" : {
            required: 'Minimum age'
          },
          "age_group_to" : {
            required: 'Maximum age'
          },
          "gender" : {
            required: 'Select Gender'
          },
          "city_id[]" : {
            required: 'Select Location'
          },
          "shift_timing_from" : {
            required: 'Shift Timing from'
          },
          "shift_timing_to" : {
            required: 'Shift Timing to'
          },
          "job_description" : {
            required: 'Job Description'
          },
          "qualification_id[]" : {
            required: 'Select Qualification'
          },
          "skill_id[]" : {
            required: 'Select Skill'
          },
          "salary_range_from" : {
            required: 'Salary Range From'
          },
          "salary_range_to" : {
            required: 'Salary Range To'
          },
          "year_experience_from" : {
            required: 'Minimum Experience'
          },
          "year_experience_to" : {
            required: 'Maximum Experience'
          },
          "industry_type" : {
            required: 'Enter Industry Type'
          },
        }
    });

    $.validator.addMethod("greaterThan",function (value, element, param) {
      var $min = $(param);
      if (this.settings.onfocusout) {
        $min.off(".validate-greaterThan").on("blur.validate-greaterThan", function () {
          $(element).valid();
        });
      }
      return parseInt(value) >= parseInt($min.val());
    }, "Max must be greater than min");

    $('.only_numeric').bind('keyup paste', function(){
        this.value = this.value.replace(/[^0-9-]/g, '');
    });

    $("div.alert-dismissible").fadeOut(3000);
    $("#shift_timing_to").timepicker({
      showInputs: false
    });
</script>
@endsection