<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use DB;
use Auth;
use View;
use App\Designation;
use App\Jrf;
use App\User;
use App\Department;
use App\Skill;
use App\City;
use App\Qualification;
use App\JrfRecruitmentTasks;
use App\JrfInterviewerDetail;
use App\JrfApprovals;
use App\JrfHierarchy;
use App\Notification;
use App\Employee;
use App\JrfSkill;
use App\JrfCity;
use App\JrfQualification;
use Illuminate\Support\Facades\Mail;
use App\Mail\JrfGeneralMail;
use App\Mail\GeneralMail;
use Validator;
use DateTime;

class JrfController extends Controller
{
    
		public function create(){

			if(Auth::guest()){
        		return redirect('/');
      		}
			$data['departments'] = Department::where(['isactive'=>1])->get();
			$data['roles'] = Role::select('id','name')->get();
			$data['cities'] = City::where(['isactive'=>1])->get();
			$data['skills'] = Skill::where(['isactive'=>1])->select('id','name')->get();
			$data['qualifications'] = Qualification::where(['isactive'=>1])->select('id','name')->get();
			$data['designation'] = Designation::where(['isactive'=>1])->select('id','name')->get();
			return view('jrf.create_jrf')->with(['data'=>$data]);
		}


		public function saveJrf(Request $request){

				if(Auth::guest()){
            		return redirect('/');
          		}

          		$validator = Validator::make($request->all(), [
		            'department_id' => 'required',
		            'role_id' => 'required',
		            'number_of_positions' => 'required',
		            'gender' => 'required',
		            'city_id' => 'bail|required',
		            'job_description' => 'required',
		            'qualification_id' => 'bail|required',
		            'industry_type' => 'required',
		            'shift_timing_from' => 'required',
		            'age_group_from' => 'required',
		            'age_group_to' => 'required',
		            'salary_range_from' => 'required',
		            'salary_range_to' => 'required',
		            'year_experience_from' => 'required',
		            'year_experience_to' => 'required',
		            'skill_id' => 'bail|required',
		            'designation_id' => 'bail|required',
		        ]);

		        if($validator->fails()) {
		            return redirect("jrf/create")
		                ->withErrors($validator,'basic')
		                ->withInput();
		        }

				$data = [
						'department_id'						=> $request->department_id,
						'user_id'							=> $request->user_id, //jrf created user_id
						'role_id' 							=> $request->role_id,
						'designation_id' 					=> $request->designation_id,
						'number_of_positions' 				=> $request->number_of_positions,
						'gender' 							=> $request->gender,
						'description' 						=> $request->job_description,
						'industry_type' 					=> $request->industry_type,
						'shift_timing_from' 				=> $request->shift_timing_from,
						'shift_timing_to' 					=> $request->shift_timing_to,
						'final_status' 					    =>  '0'
				];

				if(!empty($request->age_group_from)){
		            $data['age_group'] = $request->age_group_from."-".$request->age_group_to;
        		}

        		if(!empty($request->additional_requirement)){
		           $data['additional_requirement'] = $request->additional_requirement;
        		}else {
        		  $data['additional_requirement']  = "";
        		}

        		if(!empty($request->salary_range_from)){
		            $data['salary_range'] = $request->salary_range_from."-".$request->salary_range_to;
        		}

        		if(!empty($request->year_experience_from) || $request->year_experience_from == 0 || $request->year_experience_to == 0 ){
		            $data['experience'] = $request->year_experience_from."-".$request->year_experience_to;
        		}

        		$user = User::where(['id'=>Auth::id()])->with('employee')->first();
	            $saved_jrf = $user->jrf()->create($data);
	            
	            if(!empty($request->skill_id)){
            		$saved_jrf->jrfskills()->sync($request->skill_id);
        		} 

        		if(!empty($request->qualification_id)){
            		$saved_jrf->jrfqualifications()->sync($request->qualification_id);
        		} 

        		if(!empty($request->city_id)){
            		$saved_jrf->jrfcity()->sync($request->city_id);
        		} 

				$jrf_hierarchy = JrfHierarchy::where('isactive',1)->where('type','approval')->orderBy('id','DESC')->first();
				
				$jrf_approval = [
						'user_id'				=> 	$request->user_id,
						'jrf_id'				=>  $saved_jrf->id,
						'supervisor_id'			=>  $jrf_hierarchy->user_id,
						'jrf_status'			=>  '0' //inprogress
				];

				$saved_jrf = $user->jrfapprovals()->create($jrf_approval);


				//////////////////////////Notify///////////////////////////
	            //$url = '<a href="'.url('jrf/view-jrf').'/'.$saved_jrf->id.'">Click </a>';

        		$notification_data = [
                                 'sender_id' 		=> $request->user_id,
                                 'receiver_id'	 	=> $jrf_hierarchy->user_id,
                                 'label' 			=> 'JRF Approval',
                                 'read_status'		=> '0'
                             ]; 

                $notification_data['message'] = $user->employee->fullname." created JRF";
                $saved_jrf->notifications()->create($notification_data);

                //////////////////////////Mail///////////////////////////
                $reporting_manager = Employee::where(['user_id'=>$jrf_hierarchy->user_id])
                                ->with('user')->first();
		        $mail_data['to_email'] = $reporting_manager->user->email;
		        $mail_data['subject'] = "JRF Approval";
		        $mail_data['message'] = $user->employee->fullname." created JRF. Please take an action. Here is the link for website <a href='".url('/')."'>Click here</a>";
		        $mail_data['fullname'] = 'Sir';
		        $this->sendJrfGeneralMail($mail_data); //working

				return redirect()->back()->with('success',"JRF created successfully.");
		}


		public function listJrf(){

			if(Auth::guest()){
            		return redirect('/');
          	}
			$user 	= User::where(['id'=>Auth::id()])->first();

        	$jrfs = DB::table('jrfs as jrf')
        				->join('designations as des','jrf.designation_id','des.id')
        				->join('roles as r','jrf.role_id','r.id')
        				->where('jrf.user_id',$user->id)
        				->select('jrf.*','des.name as designation','r.name as role')
        				->orderBy('jrf.id','DESC')
        				->get();

    		if(!$jrfs->isEmpty()){
            	foreach ($jrfs as $key => $value) {

	            	$jrf_approval_status = DB::table('jrf_approvals as ja')->where(['ja.jrf_id' => $value->id])->get();

	            	$can_cancel_jrf = 0;
	                if(count($jrf_approval_status) == 1 && $jrf_approval_status[0]->jrf_status == 0){
	                    $can_cancel_jrf = 1;
	                }                      
	                $value->jrf_approval_status = $jrf_approval_status;  
	                $value->can_cancel_jrf = $can_cancel_jrf;  
					if($value->final_status == '0'){
                    	$check_rejected = DB::table('jrf_approvals as ja')
                                ->where(['ja.jrf_id' => $value->id,'ja.jrf_status'=>'2'])
                                ->first();
                    if(!empty($check_rejected)){
                        $value->secondary_final_status = 'Rejected'; 
                    }else{
                        $value->secondary_final_status = 'In-Progress'; 
                    }
	                }else{
	                    $value->secondary_final_status = 'Approved';
	                }

            	}
    		}              
			return view('jrf.list_jrf')->with(['jrfs'=>$jrfs]);
		}


		public function viewJrfs($id){

			if(Auth::guest()){
            		return redirect('/');
          	}

			$user = User::where(['id'=>Auth::id()])->first();
			$detail['basic'] = DB::table('jrfs')
				->join('employees as emp','jrfs.user_id','=','emp.user_id')
				->leftjoin('jrf_approvals as ja','jrfs.id','ja.jrf_id')
				->leftjoin('employees as emp2','jrfs.close_jrf_user_id','=','emp2.user_id')
				->join('users as u','emp.user_id','=','u.id')
				->join('roles as r','jrfs.role_id','r.id')
				->join('designations as des','jrfs.designation_id','des.id')
				->join('departments as dept','jrfs.department_id','=','dept.id')
				->where('jrfs.id',$id)
				->select('jrfs.*','emp.user_id','emp.fullname','emp.mobile_number','dept.name','u.email','emp2.fullname as 	close_jrf_user_name','jrfs.close_jrf_date','des.name as designation','r.name as role','ja.*')
				->first();

			// new //
			$skills = DB::table("jrf_skill as js")
                    ->join('skills as s', 'js.id', '=', 's.id')
                    ->where('js.jrf_id', $id)
                    ->pluck('s.name')->toArray();
            $detail['skills'] = implode(' , ', $skills);       

            $qualification = DB::table("jrf_qualification as jq")
                    ->join('qualifications as q', 'jq.id', '=', 'q.id')
                    ->where('jq.jrf_id', $id)
                    ->pluck('q.name')->toArray();
            $detail['qualification'] = implode(' , ', $qualification); 


            $location = DB::table("city_jrf as cj")
                    ->join('cities as c', 'cj.id', '=', 'c.id')
                    ->where('cj.jrf_id', $id)
                    ->pluck('c.name')->toArray();
            $detail['location'] = implode(' , ', $location);          
			// end new //

			$detail['interview_detail'] = DB::table('jrf_interviewer_details as jid')
				->join('jrfs','jid.jrf_id','=','jrfs.id')
				->join('departments as dept','jid.department_id','=','dept.id')
				->join('employees as emp','jid.user_id','=','emp.user_id')
				->join('employees as emp2','jid.assigned_by','=','emp2.user_id')
				->where('jid.jrf_id',$id)
				->select('jid.*','jrfs.id','dept.name','emp.fullname','emp2.fullname as assigned_by')
				->get();

			$detail['recruitment_detail'] = DB::table('jrf_recruitment_tasks as jrt')
				->join('jrfs','jrt.jrf_id','=','jrfs.id')
				->join('departments as dept','jrt.department_id','=','dept.id')
				->join('employees as emp','jrt.user_id','=','emp.user_id')
				->leftjoin('employees as emp2','jrt.assigned_by','=','emp2.user_id')
				->where('jrt.jrf_id',$id)
				->select('jrt.*','jrfs.id','dept.name','emp.fullname','emp2.fullname as assigned_by')
				->get();

			return view('jrf.view_jrf')->with(['detail'=>$detail]);
		}


		public function editJrfs($id){

			if(Auth::guest()){
            		return redirect('/');
          	}
			$user = User::where(['id'=>Auth::id()])->first();
			$data['departments'] 	= Department::where(['isactive'=>1])->get();
			$data['roles'] = Role::select('id','name')->get();
			$data['cities'] = City::where(['isactive'=>1])->get();
			$data['skills'] = Skill::where(['isactive'=>1])->select('id','name')->get();
			$data['qualifications'] = Qualification::where(['isactive'=>1])->select('id','name')->get();
			$data['designation'] = Designation::where(['isactive'=>1])->select('id','name')->get();

			$roles = Jrf::where('id', $id)->pluck('role_id')->toArray();
            $data['saved_role'] = $roles;    

            $designation = Jrf::where('id', $id)->pluck('designation_id')->toArray();
            $data['saved_designation'] = $designation;    

            $skills = JrfSkill::where('jrf_id',$id)->pluck('skill_id')->toArray();
            $data['saved_skills'] = $skills;     

            $qualification = JrfQualification::where('jrf_id',$id)->pluck('qualification_id')->toArray();
            $data['saved_qualification'] = $qualification; 

           	$location = JrfCity::where('jrf_id', $id)->pluck('city_id')->toArray();
            $data['saved_location'] = $location;         
			
			$data['detail']	= Jrf::where('id',$id)->first();
			$data['recruitment_task_id'] = JrfRecruitmentTasks::where('jrf_id',$id)->value('id');
			$data['recruiters'] = Jrf::where(['id'=>$id])->with('JrfRecruitmentTasks')->get();
			$data['interviewer_details'] = DB::table('jrf_interviewer_details as jnd')
					->leftjoin('employees as emp','jnd.user_id','=','emp.user_id')
					->leftjoin('departments as dept','jnd.department_id','=','dept.id')
					->where('jnd.assigned_by',$user->id)
					->where('jnd.jrf_id',$id)
					->select('jnd.*','jnd.id as interview_id','emp.fullname','dept.*')
					->get();
			$data['recruitment_task']	= DB::table('jrf_recruitment_tasks as jrt')
					->leftjoin('employees as emp','jrt.user_id','=','emp.user_id')
					->leftjoin('departments as dept','jrt.department_id','=','dept.id')
					->where('jrt.assigned_by',$user->id)
					->where('jrt.jrf_id',$id)
					->select('jrt.*','emp.fullname','dept.*')
					->get();
			$data['hierarchy'] = JrfHierarchy::where('isactive',1)->where('type','approval')->orderBy('id','DESC')->first();
			if($data['hierarchy']->user_id == $user->id){
				$data['check_editable']	= "can_edit";
			}else {
				$data['check_editable'] = "not_edit";
			}
			$data['last_date_recruitment'] = JrfRecruitmentTasks::where('jrf_id',$id)->where('user_id',$user->id)->orderBy('id','DESC')->value('last_date');
			if(!empty($data['last_date_recruitment'])){
				$find_date = DateTime::createFromFormat('Y-m-d',$data['last_date_recruitment']);
				$data['last_date'] = $find_date->format('d/m/Y');
			}
			$data['approval_status'] = JrfApprovals::where('jrf_id',$id)->where('supervisor_id',$user->id)->orderBy('id','DESC')->first();
			return view('jrf.edit_jrf')->with(['data'=>$data]);
		}


		public function updateJrfs(Request $request){

				if(Auth::guest()){
            		return redirect('/');
          		}

          		$validator = Validator::make($request->all(), [
		            'department_id' => 'required',
		            'role_id' => 'required',
		            'number_of_positions' => 'required',
		            'gender' => 'required',
		            'city_id' => 'bail|required',
		            'job_description' => 'required',
		            'qualification_id' => 'bail|required',
		            'industry_type' => 'required',
		            'shift_timing_from' => 'required',
		            'age_group_from' => 'required',
		            'age_group_to' => 'required',
		            'salary_range_from' => 'required',
		            'salary_range_to' => 'required',
		            'year_experience_from' => 'required',
		            'year_experience_to' => 'required',
		            'skill_id' => 'bail|required',
		            'designation_id' => 'bail|required',
		        ]);

		        if($validator->fails()) {
		            return redirect("jrf/edit-jrf/".$request->jrf_id)
		                ->withErrors($validator,'basic')
		                ->withInput();
		        }
				$data = [
						'department_id'						=> $request->department_id,
						'role_id' 							=> $request->role_id,
						'designation_id' 					=> $request->designation_id,
						'number_of_positions' 				=> $request->number_of_positions,
						'gender' 							=> $request->gender,
						'description' 						=> $request->job_description,
						'industry_type' 					=> $request->industry_type,
						'shift_timing_from' 				=> $request->shift_timing_from,
						'shift_timing_to' 					=> $request->shift_timing_to,
						'final_status' 					    =>  '0'
				];

				if(!empty($request->age_group_from)){
		            $data['age_group'] = $request->age_group_from."-".$request->age_group_to;
        		}

        		if(!empty($request->additional_requirement)){
		           $data['additional_requirement'] = $request->additional_requirement;
        		}else {
        		  $data['additional_requirement']  = "";
        		}

        		if(!empty($request->salary_range_from)){
		            $data['salary_range'] = $request->salary_range_from."-".$request->salary_range_to;
        		}

        		if(!empty($request->year_experience_from)){
		            $data['experience'] = $request->year_experience_from."-".$request->year_experience_to;
        		}

        		$saved_jrf =Jrf::updateOrCreate(['id'=>$request->jrf_id],$data);

	            if(!empty($request->skill_id)){
            		$saved_jrf->jrfskills()->sync($request->skill_id);
        		} 

        		if(!empty($request->qualification_id)){
            		$saved_jrf->jrfqualifications()->sync($request->qualification_id);
        		} 

        		if(!empty($request->city_id)){
            		$saved_jrf->jrfcity()->sync($request->city_id);
        		} 				
        		return redirect()->back()->with('success',"JRF updated successfully.");
		}


		public function saveRecruitmentTasks(Request $request){

			if(Auth::guest()){
            		return redirect('/');
          	}

          	$validator = Validator::make($request->all(), [
		            'assigned_by' => 'required'
		    ]); 

		    if($validator->fails()) {
		            return redirect("jrf/edit-jrf/".$request->jrf_hidden_id)
		                ->withErrors($validator,'basic')
		                ->withInput();
		    }

			$date = DateTime::createFromFormat('d/m/Y',$request->recruitment_last_date);
			$last_date = $date->format('Y-m-d');

			$data = [
				'user_id' 			=> $request->recruitment_interviewer_employee, //is user id 
				'department_id'		=> $request->recruitment_department,
				'jrf_id'			=> $request->jrf_hidden_id,
				'assigned_by'		=> $request->assigned_by,				
				'last_date' 		=> $last_date
			];

			$user = User::where(['id'=>Auth::id()])->with('employee')->first();
			$saved_recruitment_tasks = $user->jrfRecruitmentTasks()->create($data);
			// for next approval and jrf asssigned to person name
			$get_jrf_approvded_status = JrfApprovals::where('jrf_id',$request->jrf_hidden_id)->where('supervisor_id',$request->assigned_by)->first();

			$update_approval_status	= [
				'jrf_status' => '1'
			];

			JrfApprovals::updateOrCreate(['supervisor_id'=>$request->assigned_by,'jrf_id'=>$request->jrf_hidden_id],$update_approval_status); 

			$jrf_approval = [
				'user_id'				=> 	$request->assigned_by,
				'jrf_id'				=>  $request->jrf_hidden_id,
				'supervisor_id'			=>  $request->recruitment_interviewer_employee, // work assigned to 
				'jrf_status'			=>  '0' //inprogress
			];

			$saved_recruitment_tasks = $user->jrfapprovals()->create($jrf_approval);
			// end of jrf assigned to person name

			//////////////////////////Notify///////////////////////////
			$notification_data = [
			                 'sender_id' 		=> $request->assigned_by,
			                 'receiver_id'	 	=> $request->recruitment_interviewer_employee,
			                 'label' 			=> 'JRF Assigned',
			                 'read_status'		=> '0'
			             ]; 

			$notification_data['message'] = "JRF assigned by ".$user->employee->fullname;
			$saved_recruitment_tasks->notifications()->create($notification_data);

			//////////////////////////Mail///////////////////////////
            $reporting_manager = Employee::where(['user_id'=>$request->recruitment_interviewer_employee])
                            ->with('user')->first();

	        $mail_data['to_email'] = $reporting_manager->user->email;
	        $mail_data['subject'] = "JRF Assigned";
	        $mail_data['message'] = $user->employee->fullname. " assigned JRF. Please take an action. Here is the link for website <a href='".url('/')."'>Click here</a>";
	        $mail_data['fullname'] = $reporting_manager->fullname;
	        $this->sendGeneralMail($mail_data);
			return redirect()->back()->with('success',"Recruitment task assigned successfully.");
		}


		function sendGeneralMail($mail_data)
	    {   //mail_data Keys => to_email, subject, fullname, message

	        if(!empty($mail_data['to_email'])){
	            Mail::to($mail_data['to_email'])->send(new GeneralMail($mail_data));
	        }
	        return true;
	    }//end of function



		public function saveInterviewerDetails(Request $request){

			if(Auth::guest()){
            		return redirect('/');
          	}

          	$validator = Validator::make($request->all(), [
		            'candidate_name' => 'required',
		            'recruitment_task_id' => 'required',
		            'interview_type' => 'required',
		            'assigned_by' => 'required',
		            'interview_date' => 'required'
		    ]); 

		    if($validator->fails()) {
		            return redirect("jrf/edit-jrf/".$request->jrf_hidden_name)
		                ->withErrors($validator,'basic')
		                ->withInput();
		    }

		    $date = DateTime::createFromFormat('d/m/Y',$request->interview_date);
			$interview_date = $date->format('Y-m-d');

			$data = [
					'user_id' 				=> $request->interviewer_employee, //is user id 
					'department_id'			=> $request->interviewer_department,
					'jrf_id'				=> $request->jrf_hidden_name,
					'recruitment_task_id'	=> $request->recruitment_task_id,
					'candidate_name'		=> $request->candidate_name,
					'interview_type'		=> $request->interview_type,
					'assigned_by'			=> $request->assigned_by, // logined in user assigned tasks\ 
					'interview_date' 		=> $interview_date,
					'interview_time' 		=> $request->interview_time
			];

			// update jrd approval status
			$get_jrf_approvded_status = JrfApprovals::where('jrf_id',$request->jrf_hidden_name)->where('supervisor_id',$request->assigned_by)->first();

			$update_approval_status	= [
				'jrf_status' => '1'
			];

			JrfApprovals::updateOrCreate(['supervisor_id'=>$request->assigned_by,'jrf_id'=>$request->jrf_hidden_name],$update_approval_status); 
			// end of jrf approval status

			//JrfInterviewerDetail::firstOrCreate($data);
			$user = User::where(['id'=>Auth::id()])->with('employee')->first();
			//$saved_interview_detail = $user->jrfinterviewerdetail()->create($data);
			$jrf = Jrf::where('id',$data['jrf_id'])->first();

			$saved_interview_detail = $jrf->jrfInterviewerDetail()->create($data);
			//////////////////////////Notify///////////////////////////
        		
    		/*$notification_data = [
                             'sender_id' 		=> $request->assigned_by,
                             'receiver_id'	 	=> $request->interviewer_employee,
                             'label' 			=> 'Interview Scheduled',
                             'read_status'		=> '0'
                         ]; 

            $notification_data['message'] = $user->employee->fullname." scheduled the Interview of  ".$request->candidate_name." ";
           	$saved_interview_detail = $jrf->notifications()->create($notification_data);*/


            //////////////////////////Mail///////////////////////////
            /*$reporting_manager = Employee::where(['user_id'=>$request->interviewer_employee])
                            ->with('user')->first();
	        $mail_data['to_email'] = $reporting_manager->user->email;
	        $mail_data['subject'] = "JRF Interview Scheduled";
	        $mail_data['message'] = $user->employee->fullname. " scheduled the Interview of  ".$request->candidate_name.". Please check detail of interview. Here is the link for website <a href='".url('/')."'>Click here</a>";
	        $mail_data['fullname'] = $reporting_manager->fullname;
	        $this->sendJrfGeneralMail($mail_data);*/ //working
	    	//////////////////////////End///////////////////////////
			return redirect()->back()->with('success','Interview detail saved successfully');
		}


		public function approveJrf($jrf_status = null){

			if(Auth::guest()){
            	return redirect('/');
          	}
			$user = User::where(['id'=>Auth::id()])->first();
	        if(empty($jrf_status) || $jrf_status == 'pending'){
	            $status = '0';
	            $jrf_status = 'pending'; 			//pending as a inprogress //
	        }elseif ($jrf_status == 'assigned') {
	            $status = '1';
	            $jrf_status = 'assigned';
	        }elseif ($jrf_status == 'rejected') {
	            $status = '2';
	            $jrf_status = 'Rejected';
	        }elseif ($jrf_status == 'closed') {
	            $status = '3';                    // created custom status //
	            $jrf_status = 'closed';
	        }

	        $data = DB::table('jrf_approvals as ja')
				->join('jrfs as jrf','ja.jrf_id','=','jrf.id')
				->join('employees as emp','emp.user_id','=','jrf.user_id')
				->join('roles as r','jrf.role_id','r.id')
				->join('designations as des','jrf.designation_id','des.id')
				->leftjoin('jrf_hierarchies as jh','ja.supervisor_id','jh.user_id')
				->where(['ja.supervisor_id'=>$user->id,'ja.jrf_status'=>$status,'jrf.isactive'=>1])
				->select('ja.*','emp.fullname as jrf_creater_name','ja.jrf_status as jrf_approval_status','jrf.final_status','jrf.created_at','jrf.number_of_positions','jrf.salary_range','jrf.experience','jrf.gender','des.name as designation','r.name as role','jh.user_id as hierarchy_user_id')
				->orderBy('ja.jrf_id','DESC')
				->get();

			if(!$data->isEmpty()){
	            foreach ($data as $key => $value) {

	                if($value->jrf_status == '0'){
						$value->secondary_final_status = 'In-Progress'; 
					} elseif($value->jrf_status == '3' && $value->final_status == 1) {

						$value->secondary_final_status = 'closed'; 
					} elseif($value->jrf_status == '2') {

						$value->secondary_final_status = 'Rejected'; 
					}elseif($value->jrf_status == '1' && $value->final_status == 0) {

						$value->secondary_final_status = 'assigned'; 
					}
	            }
        	} 
	        return view('jrf.list_jrf_approvals')->with(['data'=>$data,'selected_status'=>$jrf_status]);
		}

		public function cancelCreatedJrf($jrf_id){

			if(Auth::guest()){
            		return redirect('/');
          	}
			$created_jrf = Jrf::find($jrf_id);
    		$user_id = Auth::id();

    		$approval = $created_jrf->jrfapprovals() 
	                      ->where('jrf_status','!=','0')
	                      ->first();
	        if(!empty($approval)){
	            return redirect()->back()->with('cannot_cancel_error','Reporting manager has taken a decision. You cannot cancel the JRF now.');
	        }elseif(($created_jrf->user_id == $user_id) && empty($approval)){
	            $created_jrf->final_status 	= "0";
	            $created_jrf->isactive 		= "0";
	            $created_jrf->save();
        	}       
		    return redirect('jrf/list-jrf')->with('success','Cancelled JRF successfully');
		}


		public function updateInterviewStatusDetail(Request $request){

			if(Auth::guest()){
            	return redirect('/');
          	}
			$data = [
				'interview_status' 					=> $request->interview_status, //is user id
				'updated_at' 						=> date('Y-m-d H:i:s')
			];

			if(!empty($request->interview_status) && $request->interview_status == "Backoff"){
				if($request->backoff_reason == 'other_backout_reason'){
					$data['other_backoff_reason'] 		=	$request->other_backoff_reason;
					$data['final_status'] 				=	$request->backoff_reason;
					$data['other_rejected_reason'] 		=	"";
				}else{
					$data['final_status'] 				=	$request->backoff_reason;
					$data['other_backoff_reason']		=   "";
				}
			}

			if(!empty($request->interview_status) && $request->interview_status == "Rejected"){
				if($request->rejected_reason =='other_rejection_reason'){
					$data['other_rejected_reason'] 		=	$request->other_rejected_reason;
					$data['final_status'] 				=	$request->rejected_reason;
					$data['other_backoff_reason'] 		=	"";
				}else{
					$data['final_status'] 				=	$request->rejected_reason;
					$data['other_rejected_reason']		= 	"";
				}
			}

			if($request->interview_status == "Selected"){
				$data['final_status'] 				= 	"";
				$data['other_rejected_reason'] 		=	"";
				$data['other_backoff_reason'] 		=	"";
			}

			DB::table('jrf_interviewer_details')->where('id',$request->interview_detail_id)->update($data);
			//JrfInterviewerDetail::update(['id'=>$request->interview_detail_id,'assigned_by'=>$request->interview_assigned_by_id],$data);
			return redirect()->back()->with('success','Current Status of interview  saved successfully');
		}


		public function interviewStatusInfo(Request $request){

			if(Auth::guest()){
            		return redirect('/');
          	}
			$data = DB::table('jrf_interviewer_details as jid')
					->leftjoin('jrfs as jrf','jid.jrf_id','=','jrf.id')
					->leftjoin('departments as dept','jid.department_id','=','dept.id')
					->where('jid.id',$request->id)
					->select('jid.*','dept.name as department_name','jid.interview_date')
					->first();

			$view = View::make('jrf.recruitment_info',['data'=>$data]);
        	$contents = $view->render();
        	return $contents;
		}

		public function closeJrfPermanently(Request $request){

			if(Auth::guest()){
            	return redirect('/');
          	}
    		$data = [
    			'id'					=>	$request->id,        // JRF ID
    			'close_jrf_user_id'		=>	$request->employeeId,
    			'final_status'			=>  '1',
    			'close_jrf_date'		=>  date('Y-m-d H:i:s')
    		];

    		$user = User::where(['id'=>Auth::id()])->with('employee')->first();
            $close_jrf = jrf::where('id',$data['id'])->update($data);
            $jrf = Jrf::where('id',$data['id'])->first();

            // update status jrf_status = 3(close) of jrf_approvals
            $update_approval = [
    			'jrf_status'			=>  '3',
    			'updated_at'		=>  date('Y-m-d H:i:s')
    		];
            $close_jrf = $jrf->jrfapprovals()->where('jrf_id',$data['id'])->update($update_approval);
            // end 

            $approval_user_id = JrfApprovals::where('jrf_id',$request->id)->where('user_id','!=',$user->id)->pluck('user_id')->toArray();
         	$approval_supervisor_id = JrfApprovals::where('jrf_id',$request->id)->where('supervisor_id','!=',$user->id)->pluck('supervisor_id')->toArray();
           	$user_ids =  array_unique(array_merge($approval_user_id,$approval_supervisor_id));

           	//dd($user_ids);die;

            //////////////////////////Notify///////////////////////////           
            $jrf_hierarchy = JrfHierarchy::where('isactive',1)->where('type','approval')->orderBy('id','DESC')->first();

			foreach ($user_ids as $key => $value) {

				$notification_data = [
                 'sender_id' 		=> $request->employeeId,
                 'receiver_id'	 	=> $value,
                 'label' 			=> 'JRF Closed',
                 'read_status'		=> '0'
                ]; 
			}

            $notification_data['message'] = "JRF Closed By ".$user->employee->fullname;
            $close_jrf = $jrf->notifications()->create($notification_data); // inprogress

            //////////////////////////Mail///////////////////////////
            $reporting_manager = Employee::where(['user_id'=>$jrf_hierarchy->user_id])
            						->with('user')->first();
	        $mail_data['to_email'] = $reporting_manager->user->email;
	        $mail_data['subject'] = "JRF Closed";
	        $mail_data['message'] = "JRF closed By ".$user->employee->fullname." Here is the link for website <a href='".url('/')."'>Click here</a>";
	        $mail_data['fullname'] = 'Sir';
	        //$mail_data['fullname'] = $reporting_manager->fullname;
	        $this->sendJrfGeneralMail($mail_data);
        }

		function sendJrfGeneralMail($mail_data)
    	{   //mail_data Keys => to_email, subject, fullname, message
	        if(!empty($mail_data['to_email'])){
	            Mail::to($mail_data['to_email'])->send(new JrfGeneralMail($mail_data));
	        }
        return true;
    	}//end of function


    	function saveJrfRejection(Request $request){

    		if(Auth::guest()){
            	return redirect('/');
          	}

    		$data = [
    			'final_status'			=>	"0",
    			'rejection_reason'		=>	$request->rejection_reason
    		]; 	//save rejection status

    		$update_approval = [
    			'jrf_status'	=> $request->final_status
    		]; //update rejection status


    		$user = User::where(['id'=>Auth::id()])->with('employee')->first();
    		$update_jrf = Jrf::where('id',$request->jrf_id)->update($data);
    		JrfApprovals::where('jrf_id',$request->jrf_id)->update($update_approval);

    		if(!empty($update_jrf)){

    			$get_jrf = Jrf::where('id',$request->jrf_id)->first();

				//////////////////////////Notify///////////////////////////
        		$notification_data = [
		                                 'sender_id' 		=> $request->userId,
		                                 'receiver_id'	 	=> $get_jrf->user_id,
		                                 'label' 			=> 'JRF Rejected',
		                                 'read_status'		=> '0'
		                				]; 

		        $notification_data['message'] = "JRF Rejected by ".$user->employee->fullname;
		                
		        $update_jrf->notifications()->create($notification_data);


                //////////////////////////Mail///////////////////////////
                $reporting_manager = Employee::where(['user_id'=>$request->userId])
                                ->with('user')->first();
		        $mail_data['to_email'] = $reporting_manager->user->email;
		        $mail_data['subject'] = "JRF Rejected";
		        $mail_data['message'] = "JRF Rejected by ".$user->employee->fullname." Please take an action. Here is the link for website <a href='".url('/')."'>Click here</a>";
		        //$mail_data['fullname'] = 'Sir';
		        $mail_data['fullname'] = $reporting_manager->fullname;
		        $this->sendGeneralMail($mail_data);
    			return redirect()->back()->with('success','JRF Rejected successfully');
    		}
    	}


    	function interviewList(){

			$user = User::where(['id'=>Auth::id()])->first();
			$data = DB::table('jrf_interviewer_details as jnd')
					->join('jrfs as jrf','jnd.jrf_id','jrf.id')
					->join('roles as r','jrf.role_id','r.id')
					->join('designations as des','jrf.designation_id','des.id')
					->join('departments as dept','jrf.department_id','=','dept.id')
					->where('jnd.user_id',$user->id)
					->select('jnd.*','jrf.*','r.name as role','des.name as designation','dept.name')
					->get();
    		return view('jrf.interview_list')->with(['datas'=>$data]);
    	}
    	
}