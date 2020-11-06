@extends('admins.layouts.app')
@section('content') 
<style type="text/css">
#status_check {
   background-color : #ec1b1b !important;
}
#created_check {
    background-color: #297b12 !important;
    font-size: x-small;
}
.rejection_reason {
    margin-top: 6px;
}
</style>
<div class="content-wrapper">  
   <section class="content-header">
      <h1> JRF Detail 
         <span class="label label-success" id="created_check">
            @if(@$detail['basic']->created_at)
               Created {{date("Y-m-d",strtotime(@$detail['basic']->created_at))}}
            @endif
         </span>
      </h1>
      <ol class="breadcrumb">
         <li><a href="{{ url('employees/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
      </ol>
   </section>
   <!-- Main content --> 

    <section class="content">
      <div class="row">
         <div class="col-md-3">  
            <!-- About Me Box -->          
            <div class="box box-primary">
               <div class="box-header with-border">
                  <h3 class="box-title">Created By</h3>
               </div>
               <!-- /.box-header -->            
               <div class="box-body">
	              	<p class="text-muted"><span>Name           : </span>{{ @$detail['basic']->fullname}}</p>
	              	<p class="text-muted"><span>Mobile Number  : </span>{{ @$detail['basic']->mobile_number}}</p>
	              	<p class="text-muted"><span>Email          : </span>{{ @$detail['basic']->email}}</p>
                  <hr>
               </div>
               <!-- /.box-body -->          
            </div>

            <!-- test -->
            <div class="box box-primary">
               <div class="box-header with-border">
                  <h3 class="box-title">Current Status </h3>
                  @if($detail['basic']->final_status == 0 && $detail['basic']->jrf_status == '0')
                     <span class="label label-success">In-Progress</span>

                  @elseif(@$detail['basic']->final_status == 1 && $detail['basic']->jrf_status == '3')
                     <span class="label label-info">Closed {{date("Y-m-d",strtotime(@$detail['basic']->close_jrf_date))}}</span>

                  @elseif(@$detail['basic']->final_status == 0  && $detail['basic']->jrf_status == '2')
                     <span class="label label-danger">Rejected</span>
                     @if(!empty(@$detail['basic']->final_status == 0  && $detail['basic']->jrf_status == '2'))
                     <div class="rejection_reason">{{@$detail['basic']->rejection_reason}}</div>
                     @endif

                  @elseif($detail['basic']->final_status == 0 && $detail['basic']->jrf_status == '0' && $detail['basic']->isactive == '0')
                     <span class="label label-warning">Cancelled</span>

                  @elseif($detail['basic']->final_status == 0 && $detail['basic']->jrf_status == '1')
                     <span class="label label-warning">Assigned</span>
                  @endif
               </div>
               <!-- /.box-header -->    
               @if(!empty($detail['basic']->close_jrf_user_name))        
               <div class="box-body">
               <p class="text-muted"><span>Closed By : </span>{{ @$detail['basic']->close_jrf_user_name}}
               </p><hr>
               </div>
               @endif
               <!-- /.box-body -->          
            </div>
            <!-- end test -->
            <!-- /.box -->        
         </div>
         <!-- /.col -->        
         <div class="col-md-9">
            <div class="nav-tabs-custom">
               <ul class="nav nav-tabs edit-nav-styling">
                  <li id="basicDetailsTab" class="active"><a href="#tab_basicDetailsTab" data-toggle="tab">Basic Details</a></li>
                  <li id="RecruitmentDetailTab"><a href="#tab_RecruitmentDetailTab" data-toggle="tab">Recruitment Detail</a></li>
                  <li id="interviewDetailTab"><a href="#tab_interviewDetailTab" data-toggle="tab">Interview Details</a></li>
               </ul>
               <div class="tab-content">
                  <div class="active tab-pane" id="tab_basicDetailsTab">
                     <div class="box-body no-padding">
                        <table class="table table-striped table-bordered">
                           <tr>
                              <th style="width: 30%">Field</th>
                              <th style="width: 70%">Value</th>
                           </tr>

                           <tr>
                              <td><em>Job Designation</em></td>
                              <td>{{ @$detail['basic']->designation}}</td>
                           </tr>

                           <tr>
                              <td><em>Job Role</em></td>
                              <td>{{ @$detail['basic']->role}}</td>
                           </tr>
                           <tr>
                              <td><em>Number of Position</em></td>
                              <td>{{ @$detail['basic']->number_of_positions}}</td>
                           </tr>
                           <tr>
                              <td><em>Department</em></td>
                              <td>{{ @$detail['basic']->name}}</td>
                           </tr>
                           <tr>
                              <td><em>Age </em></td>
                              <td>{{ @$detail['basic']->age_group}}</td>
                           </tr>
                           <tr>
                              <td><em>Gender </em></td>
                              <td>{{ @$detail['basic']->gender}}</td>
                           </tr>
                           <tr>
                              <td><em>Location </em></td>
                              <td>{{ @$detail['location'] }}</td>
                           </tr>
                           <tr>
                              <td><em>Shift Timing From</em></td>
                              <td>{{ @$detail['basic']->shift_timing_from}}</td>
                           </tr>
                           <tr>
                              <td><em>Shift Timing To</em></td>
                              <td>{{ @$detail['basic']->shift_timing_to}}</td>
                           </tr>
                           <tr>
                              <td><em>Qualification </em></td>
                              <td>{{ @$detail['qualification']}}</td>
                           </tr>
                           <tr>
                              <td><em>Skills </em></td>
                              <td>{{ @$detail['skills'] }}</td>
                           </tr>

                           @if(!empty($detail['basic']->additional_requirement))
                           <tr>
                              <td><em>Additional Requirement </em></td>
                              <td>{{ @$detail['basic']->additional_requirement}}</td>
                           </tr>
                           @endif

                           <tr>
                              <td><em>Experience </em></td>
                              <td>{{ @$detail['basic']->experience}}</td>
                           </tr>
                           <tr>
                              <td><em>Salary Range </em></td>
                              <td>{{ @$detail['basic']->salary_range}}</td>
                           </tr>
                           <tr>
                              <td><em>Description </em></td>
                              <td>{{ @$detail['basic']->description}}</td>
                           </tr>
                        </table>
                        </div>
                     </div>

                  <!-- for recuritment task -->
                  <div class="tab-pane" id="tab_RecruitmentDetailTab">                
                     <div class="box-body no-padding">                        
                        <table class="table table-striped table-bordered">                          
                           <tr>                            
                              <th style="width: 30%">Field</th>                            
                              <th style="width: 70%">Value</th>
                           </tr>

                        @if(!$detail['recruitment_detail']->isEmpty())                 
                           @foreach($detail['recruitment_detail'] as  $recruitment)      
                            <tr>                            
                              <td><em>Assigned By</em></td>                            
                              <td>
                                 {{@$recruitment->assigned_by}}                            
                              </td> 
                           </tr>

                           <tr>                            
                              <td><em>Assigned To</em></td>                            
                              <td>{{@$recruitment->fullname}}</td>
                           </tr>                          
                           <tr>   
                              <td><em>Department</em></td>                            
                              <td>{{@$recruitment->name}}</td>         
                           </tr>  

                           <tr>                            
                              <td><em>Last Date of Recruitment</em></td>                            
                              <td>
                                 {{@$recruitment->last_date}}                            
                              </td> 
                           </tr>
                           @endforeach               
                        @else 
                        <tr>  
                           <td><em>Current Status</em></td>                            
                           <td>JRF Not assigned to Any Recruiter.</td>
                        </tr>       
                     @endif 
                  </table> 
               </div>    
            </div>      

            <div class="tab-pane" id="tab_interviewDetailTab">
               <div class="box-body no-padding">
                     <table class="table table-striped table-bordered">                          
                           <tr>                            
                              <th style="width: 30%">Field</th>                            
                              <th style="width: 70%">Value</th>
                           </tr>

                              @if(!$detail['interview_detail']->isEmpty())                 
                                 @foreach($detail['interview_detail'] as  $interview)      
                                 <tr>                            
                                    <td><em>Candidate Name</em></td>                            
                                    <td>{{@$interview->candidate_name}}</td>
                                 </tr>                          
                                 <tr>   
                                 <td><em>Interview Status</em></td> 
                                    @if(!empty($interview->interview_status))
                                       <td>{{@$interview->interview_status}}</td>
                                    @else 
                                       <td>N/A</td>
                                    @endif  
                                 </tr>  
                                 <tr>                            
                                    <td><em>Interview Type</em></td>                            
                                    <td>
                                       {{@$interview->interview_type}}                            
                                    </td> 
                                 </tr>                          

                                 <tr>                            
                                    <td><em>Interviewer Name</em></td>
                                    <td>{{@$interview->fullname}}</td>                          
                                 </tr>

                                 <tr>  
                                    <td><em>Interviewer Department</em></td>                            
                                    <td> {{@$interview->name}} </td>
                                 </tr>

                                 <tr>  
                                    <td><em>Interview Schedule By</em></td>                            
                                    <td> {{@$interview->assigned_by}} </td>
                                 </tr>

                                 <tr>  
                                    <td><em>Interview Date</em></td>                            
                                    <td> {{@$interview->interview_date}} </td>
                                 </tr>

                                 @if(!empty($interview->final_status))
                                 <tr>  
                                    <td><em>Final Status</em></td>                            
                                    <td> {{@$interview->final_status}} </td>
                                 </tr>                          
                                 @endif 

                                 @if(!empty($interview->other_backoff_reason))
                                 <tr>  
                                    <td><em>Other Backoff Reason</em></td>                            
                                    <td> {{@$interview->other_backoff_reason}} </td>
                                 </tr>                          
                                 @endif 

                                 @if(!empty($interview->other_rejected_reason))
                                 <tr>  
                                    <td><em>Other Rejected Reason</em></td>                            
                                    <td> {{@$interview->other_rejected_reason}} </td>
                                 </tr>                          
                                 @endif
                              @endforeach
                           @else 
                           <tr>  
                              <td><em>Current Status</em></td>                            
                              <td>Recruiter Not schedule any interview.</td>
                           </tr>          
                           @endif 
                        </table> 
                     </div>
                        </div>        
                     <!-- /.tab-pane -->            
                   </div>  <!-- end of recuirtment tasks --><!-- /.tab-content -->          
               </div><!-- /.nav-tabs-custom -->        
            </div>
            <!-- /.col -->      
         </div>
      <!-- /.row -->    
   </section>
   <!-- /.modal -->  
</div>
<!-- /.content-wrapper -->  
<script src="{{asset('public/admin_assets/plugins/validations/jquery.validate.js')}}"></script>  
<script src="{{asset('public/admin_assets/plugins/validations/additional-methods.js')}}"></script>  
<script> 
   $(".changeProfilePicture").on('click',function(){        
   	$("#changeProfilePictureModal").modal('show');    
   });    
   
   $("#profilePictureForm").validate({      
   	rules :{          
   		"profilePic" : {              
   			required: true,              
   			accept: "image/*",              
   			filesize: 1048576    //1 MB          
   		}      
   	},      
   
   	messages :{          
   		"profilePic" : {              
   			required : 'Please select an image.',
   			accept : 'Please select a valid image format.',              
   			filesize: 'Filesize should be less than 1 MB.'          
   		}      
   	}    
   });    
   $.validator.addMethod('filesize', function(value, element, param) {        
   	return this.optional(element) || (element.files[0].size <= param)     
   });  
</script>  
@endsection