@extends('admins.layouts.app')

@section('content')

<!-- Content Wrapper. Contains page content -->  
<style type="text/css">
	.link_button {
	    -webkit-border-radius: 4px;
	    -moz-border-radius: 4px;
	    border-radius: 4px;
	    border: solid 1px #00a65a;
	    text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.4);
	    -webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 1px 1px rgba(0, 0, 0, 0.2);
	    -moz-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 1px 1px rgba(0, 0, 0, 0.2);
	    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 1px 1px rgba(0, 0, 0, 0.2);
	    background: #00a65a;
	    color: #FFF;
	    padding: 8px 12px;
	    text-decoration: none;
	}
</style>
<div class="content-wrapper">    

	<!-- Content Header (Page header) -->    

	<section class="content-header">      

		<h1>        

			My Profile        

			<!-- <small>Control panel</small> -->      

		</h1>      

		<ol class="breadcrumb">        

			<li><a href="{{ url('employees/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>        

			<!-- <li class="active">Dashboard</li> -->      

		</ol>    

	</section>    



	@php

		if($user->employee->profile_picture){

			$profile_picture = config('constants.uploadPaths.profilePic').$user->employee->profile_picture;

		}else{

			$profile_picture = config('constants.static.profilePic');

		}

		 

	@endphp

	<!-- Main content -->    

	<section class="content">      

		<div class="row">        

			<div class="col-md-3">          

				<!-- Profile Image -->          

				<div class="box box-primary">            

					<div class="box-body box-profile">              

						<img class="profile-user-img img-responsive img-circle" src="{{@$profile_picture}}" alt="User profile picture">              

						<h3 class="profile-username text-center">{{@$user->employee->fullname}}</h3>              

						<p class="text-muted text-center">
							@if(@$user->roles[0]->name != 'MD')
								{{@$user->roles[0]->name}}
							@else
								Managing Director
							@endif
						</p>              

						<!-- <ul class="list-group list-group-unbordered">                

						<li class="list-group-item">                  

						<b>Followers</b> <a class="pull-right">1,322</a>                

						</li>                

						<li class="list-group-item">                  

						<b>Following</b> <a class="pull-right">543</a>                

						</li>                

						<li class="list-group-item">                  

						<b>Friends</b> <a class="pull-right">13,287</a>                

						</li>              

						</ul> -->                          

					</div>            

					<!-- /.box-body -->          

				</div>          

				<!-- /.box -->          

				<!-- About Me Box -->          

				         
				<!-- /.box -->        
			</div>        <!-- /.col -->        
			<div class="col-md-9">          

				<div class="nav-tabs-custom">            

					<ul class="nav nav-tabs edit-nav-styling">              

						<li id="basicDetailsTab" class="active"><a href="#tab_basicDetailsTab" data-toggle="tab">Basic Details</a></li>             

						<li id="profileDetailsTab"><a href="#tab_profileDetailsTab" data-toggle="tab">Project Details</a></li>

           

					</ul>            

					<div class="tab-content">              

						<div class="active tab-pane" id="tab_basicDetailsTab">                                          

							<div class="box-body no-padding">                        

								<table class="table table-striped table-bordered">                          

									<tr>                            

										<th style="width: 30%">Field</th>                            

										<th style="width: 70%">Value</th>

									</tr>                                                    

									<tr><td><em>Employee Name</em></td>                            

										<td>{{@$user->employee->fullname}}</td>                          

									</tr>

									                    

								

									<tr>

										<td><em>Employee user Id</em></td>

										<td>{{@$user->employee_code}}

										</td>

									</tr>                          

									<tr>

										<td><em>Email</em></td>

										<td>{{@$user->email}}</td>

									</tr>                          

								
								
									<tr>                            

										<td><em>Mobile Number</em></td>

										<td>{{@$user->employee->mobile_number}}</td>                          

									</tr>                          

								

							
								<tr>                            

									<td><em>Birth Date</em></td>                            

									<td>

										@if(!empty(@$user->employee->birth_date) && @$user->employee->birth_date != '0000-00-00' )                                

											{{date("d/m/Y",strtotime(@$user->employee->birth_date))}}

										@else                              

											{{"None"}}                            

										@endif

									</td>

								</tr>

								<tr>                            

									<td><em>Joining Date</em></td>                            

									<td>

										@if(!empty(@$user->employee->joining_date) && @$user->employee->joining_date != '0000-00-00' )                                

											{{date("d/m/Y",strtotime(@$user->employee->joining_date))}}

										@else                              

											{{"None"}}                            

										@endif

									</td>

								</tr>                          
                               
								<tr>                            

									<td><em>Gender</em></td>                            

									<td>{{@$user->employee->gender}}</td>                          

								</tr>                          

						
						</table>                      

					</div>                                    

				</div>              

				<!-- /.tab-pane -->


				<!--    Project Detailed  By HK    -->              

				<div class="tab-pane" id="tab_profileDetailsTab">
					<div class="box-body no-padding">                        

						<table class="table table-striped table-bordered">                          
							<tr>                            
								<th style="width: 30%">Field</th>                            
								<th style="width: 70%">Value</th>
							</tr>
							<tr>                            
								<td><em>Project</em></td>                            
								<td>{{@$user->projects[0]->name}}</td>
							</tr>                                                    
							<tr>                            
								<td><em>Zone</em></td>                            
								<td>{{@$user->employeeProfile->department->name}}</td>
							</tr>
							<tr>                            
								<td><em>State</em></td>                            
								<td>{{@$user->employeeProfile->state->name}}</td>
							</tr>
							
							<tr>                            
								<td><em>Location</em></td>
								<td>{{@$user->locations[0]->name}}</td>
							</tr>

							<tr>                            
								<td><em>Designation</em></td>
								<td>{{@$user->designation[0]->name}}</td>
							</tr>
		           		</table>
		       		</div>              
		   		</div>              <!-- /.tab-pane -->              

		   <div class="tab-pane" id="tab_documentDetailsTab">                

		   	<div class="box-body no-padding">                        

		   		<table class="table table-striped table-bordered">                          

		   			<tr>                            

		   				<th style="width: 10%">#</th>                            

		   				<th style="width: 30%">Type</th>                            

		   				<th style="width: 30%">Uploaded</th>                            

		   				<th style="width: 30%">File</th>                          

		   			</tr>                            

		   			    @foreach($documents as $document)

		   			        <tr>  

		   			        	<td>{{$loop->iteration}}</td>                              

		   			        	<td><em>{{$document->document_name}}</em></td>                              

		   			        	<td>

		   			        		@if(empty($document->name))                                  

		   			        		<span class="badge bg-red">No</span>                                

		   			        		@else

		   			        		<span class="badge bg-green">Yes</span>                                @endif                              

		   			        	</td>                              

		   			        	<td>                                

		   			        		@if(!empty($document->name))                                  

		   			        		<span><a target="_blank" href="{{config('constants.uploadPaths.document').$document->name}}"><i class="fa fa-file-text-o" aria-hidden="true"></i></a></span>@endif                              

		   			        	</td>                            

		   			        	</tr>                            

		   			        @endforeach                                                  

		   			    </table>                      

		   			</div>              

		   		</div>              <!-- /.tab-pane -->              

		   		<div class="tab-pane" id="tab_addressDetailsTab">                

		   			<div class="box-body no-padding">                        

		   				<table class="table table-striped table-bordered">                          

		   					<tr>                            

		   						<th style="width: 30%">Present Address :-</th>                            

		   						<th style="width: 70%"></th>                          

		   					</tr>                                                    

		   					<tr>                            

		   						<td><em>House Number</em></td>                            

		   						<td>{{@$user->employeeAddresses[0]->house_number}}</td>

		   					</tr>                          

		   					<tr>                            

		   						<td><em>Road/Street</em></td>                            

		   						<td>{{@$user->employeeAddresses[0]->road_street}}</td>                          

		   					</tr>                          

		   					<tr>                            

		   						<td><em>Locality/Area</em></td>                            

		   						<td>{{@$user->employeeAddresses[0]->locality_area}}</td>

		   					</tr>                          

		   					<tr>                            

		   						<td><em>Pincode</em></td>                            

		   						<td>{{@$user->employeeAddresses[0]->pincode}}</td>                          

		   					</tr> 

		   					<tr>                            

		   						<td><em>Emergency Number</em></td>                            

		   						<td>{{@$user->employeeAddresses[0]->emergency_number}}</td>                          

		   					</tr>                         

		   					<tr>                            

		   						<td><em>Country</em></td>                            

		   						<td>{{@$user->employeeAddresses[0]->country->name}}</td>                          

		   					</tr>                          

		   					<tr>                            

		   						<td><em>State</em></td>                            

		   						<td>{{@$user->employeeAddresses[0]->state->name}}</td>                          

		   					</tr>                          

		   					<tr>                            

		   						<td><em>City</em></td>                            

		   						<td>{{@$user->employeeAddresses[0]->city->name}}</td>                         

		   					</tr>                                                  

		   				</table>                      

		   			</div>                      

		   			<br>                      

		   			<div class="box-body no-padding">                        

		   				<table class="table table-striped table-bordered">                          

		   					<tr>                            

		   						<th style="width: 30%">Permanent Address :-</th>                            

		   						<th style="width: 70%"></th>                          

		   					</tr>                                                    

		   					<tr>                            

		   						<td><em>House Number</em></td>                            

		   						<td>{{@$user->employeeAddresses[1]->house_number}}</td>

		   					</tr>                          

		   					<tr>                            

		   						<td><em>Road/Street</em></td>                            

		   						<td>{{@$user->employeeAddresses[1]->road_street}}</td>                          

		   					</tr>                          

		   					<tr>                            

		   						<td><em>Locality/Area</em></td>                            

		   						<td>{{@$user->employeeAddresses[1]->locality_area}}</td>

		   					</tr>                          

		   					<tr>                            

		   						<td><em>Pincode</em></td>                            

		   						<td>{{@$user->employeeAddresses[1]->pincode}}</td>                          

		   					</tr> 

		   					<tr>                            

		   						<td><em>Emergency Number</em></td>                            

		   						<td>{{@$user->employeeAddresses[1]->emergency_number}}</td>                          

		   					</tr>                         

		   					<tr>                            

		   						<td><em>Country</em></td>                            

		   						<td>{{@$user->employeeAddresses[1]->country->name}}</td>                          

		   					</tr>                          

		   					<tr>                            

		   						<td><em>State</em></td>                            

		   						<td>{{@$user->employeeAddresses[1]->state->name}}</td>                          

		   					</tr>                          

		   					<tr>                            

		   						<td><em>City</em></td>                            

		   						<td>{{@$user->employeeAddresses[1]->city->name}}</td>                         

		   					</tr>                                                  

		   				</table>                      

		   			</div>              

		   		</div>              <!-- /.tab-pane -->              

		   		<div class="tab-pane" id="tab_accountDetailsTab">                

		   			<div class="box-body no-padding">                        

		   				<table class="table table-striped table-bordered">                          

		   					<tr>                            

		   						<th style="width: 30%">Field</th>                            

		   						<th style="width: 70%">Value</th>                          

		   					</tr>                                                    

		   					<tr>                            

		   						<td><em>Adhaar Number</em></td>                            

		   						<td>{{@$user->employeeAccount->adhaar}}</td>                          

		   					</tr>                          

		   					<tr>                            

		   						<td><em>PAN Number</em></td>                            

		   						<td>{{@$user->employeeAccount->pan_number}}</td>                          

		   					</tr>                          

		   					<tr>                            

		   						<td><em>Employee ESI Number</em></td>                            

		   						<td>{{@$user->employeeAccount->esi_number}}</td>                          

		   					</tr>                          

		   					<tr>                            

		   						<td><em>Employee Dispensary</em></td>                            

		   						<td>{{@$user->employeeAccount->dispensary}}</td>                          

		   					</tr>                          

		   					<tr>                            

		   						<td><em>PF Number for department file</em></td>                            

		   						<td>{{@$user->employeeAccount->pf_number_department}}</td>                          

		   					</tr>                          

		   					<tr>                            

		   						<td><em>UAN Number</em></td>                            

		   						<td>{{@$user->employeeAccount->uan_number}}</td>                          

		   					</tr>                          

		   					<tr>                            

		   						<td><em>Bank Name</em></td>                            

		   						<td>{{@$user->employeeAccount->bank->name}}</td>                          

		   					</tr>                          

		   					<tr>                            

		   						<td><em>Account Holder Name</em></td>                            

		   						<td>{{@$user->employeeAccount->account_holder_name}}</td>                          

		   					</tr>                          

		   					<tr>                            

		   						<td><em>Bank Account Number</em></td>                            

		   						<td>{{@$user->employeeAccount->bank_account_number}}</td>                          

		   					</tr>                          

		   					<tr>                            

		   						<td><em>IFSC Code</em></td>                            

		   						<td>{{@$user->employeeAccount->ifsc_code}}</td>                          

		   					</tr>                          

		   					<tr>                            

		   						<td><em>Contract Signed</em></td>                            

		   						<td>

		   						@if(@$user->employeeAccount->contract_signed == '1')                              	

		   							{{"Yes"}} 

		   						@else                              

		   							{{"No"}}                              

		   						@endif                            

		   						</td>                          

		   					</tr>                          

		   					@if(@$user->employeeAccount->contract_signed == '1')                            

		   					<tr>                              

		   						<td><em>Contract Signed Date</em></td>                              

		   						<td>{{date("d/m/Y",strtotime(@$user->employeeAccount->contract_signed_date))}}</td>                            

		   					</tr>                          

		   					@endif 

		   					<tr>                            

		   						<td><em>Employment Verification</em></td>                            

		   						<td>

		   						@if(@$user->employeeAccount->employment_verification == '1')                              	

		   							{{"Yes"}} 

		   						@else                              

		   							{{"No"}}                              

		   						@endif

		   						</td>                          

		   					</tr>  

		   					<tr>                            

		   						<td><em>Address Verification</em></td>                            

		   						<td>
		   						@if(@$user->employeeAccount->address_verification == '1')                              	
		   							{{"Yes"}} 
		   						@else                              
		   							{{"No"}}                              
		   						@endif
		   						</td>                          
		   					</tr> 
		   					<tr>                            
		   						<td><em>Police Verification</em></td>                            
		   						<td>
		   						@if(@$user->employeeAccount->police_verification == '1')                              	
		   							{{"Yes"}} 
		   						@else                              
		   							{{"No"}}                              
		   						@endif
		   						</td>                          
		   					</tr>                                              
		   				</table>                      
		   			</div>              
		   		</div>              <!-- /.tab-pane -->              

		   		<div class="tab-pane" id="tab_referenceDetailsTab">                
		   			<div class="box-body no-padding">                        
		   				<table class="table table-striped table-bordered">                          
		   					<tr>                            
		   						<th style="width: 30%">Reference 1 :-</th>                            
		   						<th style="width: 70%"></th>                          
		   					</tr>                                                    
		   					<tr>                            
		   						<td><em>Name</em></td>                            
		   						<td>{{@$user->employeeReferences[0]->name}}</td>                          
		   					</tr>                          
		   					<tr>                            
		   						<td><em>Address</em></td>                            
		   						<td>{{@$user->employeeReferences[0]->address}}</td>                          
		   					</tr>                         
		   					<tr>                            
		   					 	<td><em>Phone Number</em></td>                            
		   					 	<td>{{@$user->employeeReferences[0]->phone}}</td>                          
		   					</tr>                          
		   					<tr>                            
		   					 	<td><em>Email</em></td>                            
		   					 	<td>{{@$user->employeeReferences[0]->email}}</td>                          
		   					</tr>                                                  
		   				</table>                  
		   			</div><br> 

		   			<div class="box-body no-padding">                        
		   				<table class="table table-striped table-bordered">                          
			   					<tr>                            
			   						<th style="width: 30%">Reference 2 :-</th>                            
			   						<th style="width: 70%"></th>                          
			   					</tr>                                                    

			   					<tr>                            
			   						<td><em>Name</em></td>                            
			   						<td>{{@$user->employeeReferences[1]->name}}</td>                          
			   					</tr>                          

			   					<tr>                            
			   						<td><em>Address</em></td>                            
			   						<td>{{@$user->employeeReferences[1]->address}}</td>   
			   					</tr>                         
			   					<tr>                            
			   					 	<td><em>Phone Number</em></td>                            
			   					 	<td>{{@$user->employeeReferences[1]->phone}}</td>                          
			   					</tr>                          
			   					<tr>                            
			   					 	<td><em>Email</em></td>                            
			   					 	<td>{{@$user->employeeReferences[1]->email}}</td>                          
				   				</tr>                                                  
			   				</table>                  
			   			</div>              
			   		</div>              <!-- /.tab-pane -->            
			   	</div>            <!-- /.tab-content -->          
			   </div>          <!-- /.nav-tabs-custom -->        
			</div>        <!-- /.col -->      
		</div>      <!-- /.row -->    
</section>    <!-- /.content -->    

			<div class="modal fade" id="changeProfilePictureModal">        
				<div class="modal-dialog">          
					<div class="modal-content">            
						<div class="modal-header">              
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">                
							<span aria-hidden="true">&times;</span></button>              
							<h4 class="modal-title">Change Profile Picture</h4>            
						</div>            

						<div class="modal-body">              
							<form id="profilePictureForm" action="{{ url('employees/profile-picture') }}" method="POST" enctype="multipart/form-data">
								{{ csrf_field() }}                  
								<div class="box-body">                                        
									<div class="form-group">                      
										<label for="profilePic" class="">Select Picture</label>                      
										<input type="file" class="form-control" id="profilePic" name="profilePic">                    
									</div>                                                   
								</div>                  <!-- /.box-body -->                  
								<br>                  
								<div class="box-footer">                    
									<button type="submit" class="btn btn-primary" id="profilePictureFormSubmit">Submit</button>               
								</div>            
							</form>            
						</div>                      
					</div>          <!-- /.modal-content -->        
				</div>      <!-- /.modal-dialog -->      
			</div>        <!-- /.modal -->  
		</div>  <!-- /.content-wrapper -->  
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

	$(document).on('click', '#download_count', function download_count() {
        	var count = $(this).attr("data-count");
        	var employeeId = $(this).attr("data-employeeId");
        	$.ajax({
	            type: 'POST',
	            url: "{{ url('/employees/print-offer-letter-document') }}",
	            data: {
	                count: count, employeeId:employeeId
	            },
	        success: function(data) {
                console.log(data);
            }
    	});
    });
</script>  
@endsection