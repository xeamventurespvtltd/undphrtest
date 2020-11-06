<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Mail\ForgotPassword;
use Mail;
use Hash;
use Auth;
use DB;
use Validator;
use Carbon\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\User;
use App\Employee;
use App\EmployeeAccount;
use App\EmployeeProfile;
use App\EmployeeAddress;
use App\EmployeeSecurity;
use App\EmployeeReference;
use App\EmploymentHistory;
use App\Country;
use App\State;
use App\City;
use App\Band;
use App\Skill;
use App\Language;
use App\Department;
use App\Location;
use App\Shift;
use App\Bank;
use App\Project;
use App\ProbationPeriod;
use App\Perk;
use App\Qualification;
use App\Document;
use App\Company;
use App\SalaryStructure;
use App\SalaryCycle;
use App\LeaveAuthority;
use App\Message;
use App\Notification;
use App\Designation;
use App\Log;
use App\PtRegistration;
use App\EsiRegistration;
use App\Mail\GeneralMail;
use App\PrintDocument;
use App\UserManager;
use App\Holiday;
use App\Task;
use App\TaskUser;
use App\ShiftException;
use App\Attendance;
use App\AttendancePunch;
use App\AttendanceRemark;
use App\AttendanceChange;
use App\AttendanceChangeApproval;
use App\AttendanceChangeDate;
use App\AttendanceVerification;
use App\AttendanceResult;
use App\locationUser;

class bkp_v2_UserController extends Controller

{
    /*
        Get the data to show on replace authority form
    */
    function replaceAuthority()
    {
        $all_users = Employee::whereNotIn('user_id',[1])
                            ->with('user:id,employee_code')
                            ->get();

        $active_users = Employee::where(['isactive'=>1,'approval_status'=>'1'])
                                ->whereNotIn('user_id',[1])
                                ->with('user:id,employee_code')
                                ->get();

        return view('employees.replace_authority_form')->with(['all_users'=>$all_users,'active_users'=>$active_users]);
    }

    /*
        Replace the previous authority with the new authority in database
    */
    function saveReplaceAuthority(Request $request)
    {
        $request->validate([
            'previous_user' => 'required',
            'authority' => 'required',
            'new_user' => 'required|different:previous_user',
        ]);

        $log = Log::where(['name'=>'User-Updated'])->first();
        $flag = 0;

        if(in_array($request->authority, ['SO1','SO2','SO3'])){
            if($request->authority === 'SO1'){
                $query = UserManager::where('manager_id',$request->previous_user);

                $updated_users = $query->pluck('user_id')
                                        ->toArray();

                if(!empty($updated_users)){
                    $update = $query->update(['manager_id'=>$request->new_user]);
                    $log_data = [
                                'log_id' => $log->id,
                                'data' => 'replaced manager_id of users '.implode(',',$updated_users).' from '.$request->previous_user.' to '.$request->new_user
                            ];
                    $flag = 1;
                }
            }elseif ($request->authority === 'SO2') {
                $query = LeaveAuthority::where(['manager_id'=>$request->previous_user,'priority'=>'2']);

                $updated_users = $query->pluck('user_id')
                                        ->toArray();

                if(!empty($updated_users)){
                    $update = $query->update(['manager_id'=>$request->new_user]);
                    $log_data = [
                                'log_id' => $log->id,
                                'data' => 'replaced priority level 2 manager_id of users '.implode(',',$updated_users).' from '.$request->previous_user.' to '.$request->new_user
                            ];
                    $flag = 1;
                }
            }elseif ($request->authority === 'SO3') {
                $query = LeaveAuthority::where(['manager_id'=>$request->previous_user,'priority'=>'3']);

                $updated_users = $query->pluck('user_id')
                                        ->toArray();

                if(!empty($updated_users)){
                    $update = $query->update(['manager_id'=>$request->new_user]);
                    $log_data = [
                                'log_id' => $log->id,
                                'data' => 'replaced priority level 3 manager_id of users '.implode(',',$updated_users).' from '.$request->previous_user.' to '.$request->new_user
                            ];
                    $flag = 1;
                }
            }
        }


        if($flag){
            $updated_by = Auth::user();
            $username = $updated_by->employee->fullname;
            $log_data['message'] = $log->name. " by ".$username."(".$updated_by->id.").";
            $updated_by->logDetails()->create($log_data);

            return redirect()->back()->with('success', 'User replaced successfully.');
        }else{
            return redirect()->back()->with('success', 'There is nothing to replace.');
        }
    }

    /*
        Assign a specific permission to a specific user
    */
    function givePermission()
    {
        $user = User::find(1);

        $user->givePermissionTo(['view-attendance']);

        echo "Permission given";
    }


    /*
        Revoke a specific permission from a specific user
    */
    function revokePermission()
    {
        $user = User::find(1);

        $user->revokePermissionTo('view-attendance');

        echo "Permission revoked";
    }


    /*
        Create new permission and assign it to super-admin
    */
    function createPermission()
    {

        $data = [
                    'name' => 'it-attendance-approver',

                    'guard_name' => 'web',
                ];

        Permission::firstOrCreate($data);

        $user = User::find(1);

        $user->givePermissionTo(['it-attendance-approver']);

        echo "Permission created";

    }


    /*
        Send forgot-password email to the user
    */
    function forgotPassword(Request $request)

    {

    	$request->validate([

	        'email' => 'required|email'

	    ]);



	    $user = User::where(['email'=>$request->email])->with('employee')->first();



	    if(empty($user)){

	    	return redirect()->back()->with('error_attempt',"Email is incorrect!");



	    }else{



	    	$new_forgot_token = str_random(20);



	    	if(!$user->employee->isactive){

	          return redirect()->back()->with('error_attempt',"Your account has been disabled. Please contact administrator.");



	        }elseif($user->employee->approval_status == '0'){

	          return redirect()->back()->with('error_attempt',"Your account has not been approved yet. Please contact administrator.");



	        }else{



	          $forgot_data = ['forgot_password_token' => $new_forgot_token];

	          $user->update($forgot_data);



	          $new_forgot_token = encrypt($new_forgot_token);



	          $user->url = url('/forgot-password')."/".$new_forgot_token;



	          Mail::to($user->email)->send(new ForgotPassword($user));



	          return redirect('/')->with('error_attempt',"Your forgot password email has been sent successfully.");



	        }

	    }

    }// end of function


    /*
        Show the reset-password form to the user
    */
    function forgotPasswordForm($encrypted_token)
    {

    	$token = decrypt($encrypted_token);



        $user = User::where(['forgot_password_token'=>$token])->first();



        if(!empty($user)){



          $data['token'] = $encrypted_token;

          $data['expire_status'] = "no";

          $data['url'] = "";



        }else{



          $expire_token = "NA";

          $data['token'] = encrypt($expire_token);

          $data['expire_status'] = "yes";

          $data['url'] = url("/forgot-password");



        }

        return view('reset_password_form')->with(['data'=>$data]);

    }//end of function


    /*
        Save the new password from the reset password form
    */
    function resetPassword(Request $request)

    {

    	$request->validate([

            'new_password'  => 'bail|required|max:20|min:6',
            'confirm_password'  => 'bail|required|max:20|min:6|same:new_password'

        ]);



        $token = decrypt($request->forgot_token);



        $user = User::where(['forgot_password_token'=>$token])->first();



        if(!empty($user)){

            $new_password = Hash::make($request->new_password);

            $user->password = $new_password;

            $user->forgot_password_token = "";

      		$user->save();



            return redirect('/')->with('error_attempt',"Your password has been changed successfully.");



        }else{



            return redirect()->back()->with(['password_error'=>"Your reset password link has expired. Please send the email again."]);



        }



    }//end of function


    /*
        Authenticate a user with employee code & password & then redirect to dashboard
    */
    function login(Request $request)

    {

    	$request->validate([

	        'employee_code' => 'required',

	        'password' => 'bail|required|min:6|max:20'

	    ]);

        if($request->has('remember_me')){
            $remember = true;
        }else{
            $remember = false;
        }

    	if(Auth::attempt(['employee_code'=>$request->employee_code,'password'=>$request->password], $remember)){

	        $user = Auth::user();

	        $employee = $user->employee->first();



	        if($employee->isactive && $employee->approval_status == '1'){

	         //  	$employeeMixedData = $this->employeeModel->mixedEmployeeData($employeeId);

	         //  	$probationData =  $this->commonModel->probationCalculations($employeeId);



	         //  	if(!empty($probationData->probationEndDate)){

		        //     if($probationData->probationEndOrNot == '0' && $empProfile->probation_status == '0'){

		        //       $this->commonModel->notifyProbationApprovers($probationData,$employeeMixedData);

		        //     }

		        // }



		        return redirect('employees/dashboard');



	        }elseif($employee->approval_status == '0'){

	          	Auth::logout();

	          	return redirect('/')->with('error_attempt',"Your account has not been approved yet. Please contact administrator.");



	        }elseif(!$employee->isactive){

	          	Auth::logout();

	          	return redirect('/')->with('error_attempt',"Your account has been disabled. Please contact administrator.");



	        }



    	}else{

    		return redirect('/')->with('error_attempt',"Employee Code or Password is incorrect!");

    	}

    }//end of function


    /*
        Show the dashboard page with necessary data
    */

     function dashboard(Request $request)

    {
        $user_info = User::where(['id'=>Auth::id()])->first();

        if($request->type){
            $attendance=Attendance::where('user_id',Auth::id())
                                ->where('on_date', date('Y-m-d'))
                                ->first();
            $attendance_id=0;
            if(isset($attendance->id)){
                $attendance_id=$attendance->id;
            }
            else{
                $obj= new Attendance;
                $obj->on_date=date("Y-m-d");
                $obj->user_id=Auth::id();
                $obj->status='Present';
                $obj->save();

                $attendance_id=$obj->id;
            }

            if($request->type=='checkin'){
                $type='Check-In';
            }
            elseif($request->type=='checkout'){
                $type='Check-Out';
            }
            else
                exit;


            $obj=new AttendancePunch;
            $obj->attendance_id=$attendance_id;
            $obj->on_time=date('H:i:s');
            $obj->punched_by=Auth::id();
            $obj->type=$type;
            $obj->save();

            return redirect()->back()->withSuccess('Attendance marked successfully.');
        }

        $data['user'] = User::where(['id'=>Auth::id()])
                        ->with(['employee', 'employeeProfile', 'roles:id,name'])
                        ->first();

        $data['tasks'] =  TaskUser::where('user_id', Auth::id())
                        ->with('task')
                        ->where('status', 'Inprogress')
                        ->paginate(4);

        $data['holidays'] = Holiday::orderBy('holiday_from', 'asc')
                            ->take(4)
                            ->where('isactive', '1')
                            ->whereDate('holiday_from', '>', Carbon::now())
                            ->get();

        $data['birthdays'] = Employee::whereRaw('DAYOFYEAR(curdate()) <= DAYOFYEAR(birth_date) AND DAYOFYEAR(curdate()) + 30 >=  dayofyear(birth_date)' )
        ->orderByRaw('DATE_FORMAT(birth_date, "%m-%d")', 'asc')
        ->where('isactive', '1')
        ->get();

        $data['independence_day'] = Holiday::whereRaw('YEAR(curdate()) = YEAR(holiday_from) AND DAY(holiday_from)=15' )->first();

        $data['probation_data'] = probationCalculations($user_info);
        $data['attendances_info'] = DB::table("employees")->select('id', 'user_id', 'fullname', 'mobile_number')->whereNotIn('user_id',function($query) {
                                $query->select('user_id')->where('on_date', date("Y-m-d"))->from('attendances');
                                })
                                ->where('isactive', 1)
                                ->get();

        $data['missed_punch_count'] =   count($data['attendances_info']);

        return view('admins.dashboard', $data);//->with(['user'=>$user, 'holidays'=>$holidays]);


    }//end of function


    /*
        End user session & redirect to the landing page
    */
    function logout()

    {

      	session(['last_inserted_employee' => 0,'last_inserted_project' => 0,'last_tabname' => ""]);

      	Auth::logout();

    	return redirect('/');

    }

    /*
        List the employees as per roles & other filters like department & project
    */
    function list(Request $request)
    {
    	$user = Auth::user();

    	if(empty($request->project_id)){
            $req['project_id'] = 1;
        }else{
            $req['project_id'] = $request->project_id;
        }

        if(empty($request->department_id)){
            $req['department_id'] = "";
        }else{
            $req['department_id'] = $request->department_id;
        }

    	if($user->hasRole('MD') || $user->hasRole('AGM') || $user->id == 1){
            if($req['project_id'] == 'All'){
                $query = DB::table('employees as emp')
                ->join('users as u','emp.user_id','=','u.id');
            }else{
                $query = DB::table('employees as emp')
                ->join('users as u','emp.user_id','=','u.id')
                ->join('project_user as pu','emp.user_id','=','pu.user_id');

                if(!empty($request->department_id)){
                    $query = $query->join('employee_profiles as empp','empp.user_id','=','u.id')
                                    ->where(['empp.department_id'=>$request->department_id]);
                }

                $query = $query->where('pu.project_id',$req['project_id']);
            }
            $employees_po = $query->select('u.*','emp.*')
                            ->orderBy('emp.created_at','DESC')
                            ->get();


        }else{



            $data = DB::table('employees as emp')

            	->join('users as u','emp.user_id','=','u.id')

            	->join('employee_profiles as empp','empp.user_id','=','u.id')

                ->where(['empp.department_id'=>$user->employeeProfile->department_id,'emp.isactive'=>1])

                ->select('u.*','emp.*')

                ->orderBy('emp.created_at','DESC')

                ->get();
				$userid = Auth::id();
				$designation_login_data = DB::table('designation_user as du')

							->where('du.user_id','=',$userid)

							->select('du.id', 'du.user_id','du.designation_id')->first();

				$designation_login_user = $designation_login_data->designation_id;
				$token = 0;
				$employees_po = array();
				foreach($data as $key=>$value){
					$designation_user_data = DB::table('designation_user as du')

										->where('du.user_id','=',$value->user_id)

										->select('du.id', 'du.user_id','du.designation_id')->first();

					$designation_user = $designation_user_data->designation_id;

					$data[$key]->designation_id = $designation_user;

					$district_listed_user = DB::table('location_user as lu')

									->where('lu.user_id','=',$designation_user_data->user_id)

									->select('lu.id', 'lu.user_id','lu.location_id')->first();

					$listed_user_district_id = $district_listed_user->location_id;
					$data[$key]->district_id = $listed_user_district_id;


					$state_listed_user = EmployeeProfile::where(['user_id' => $value->user_id])
											->first();

					$listed_user_state_id = $state_listed_user->state_id;
					$data[$key]->state_id = $listed_user_state_id;
				}
				 if($designation_login_user==5){
					$token=5;
					$district_login_user = DB::table('location_user as lu')

									->where('lu.user_id','=',$designation_login_data->user_id)

									->select('lu.id', 'lu.user_id','lu.location_id')->first();
					$login_user_district_id = $district_login_user->location_id;
					$i=0;
					 foreach($data as $employee){
						 if($employee->district_id==$login_user_district_id AND $employee->designation_id==4){
							 $employees_po[$i] = $employee;
							 $i++;
						 }

					}
				}
				if($designation_login_user==3){

					$token=3;
					$district_login_user = DB::table('location_user as lu')

									->where('lu.user_id','=',$designation_login_data->user_id)

									->select('lu.id', 'lu.user_id','lu.location_id')->first();
					$login_user_district_id = $district_login_user->location_id;

					$i=0;

					 foreach($data as $employee){

						 if($employee->district_id==$login_user_district_id AND $employee->designation_id==4){

							if(!empty($employee)){
								$employees_po[$i] = $employee;
							}

							 $i++;
						 }

					}
				}
				if($designation_login_user==4){
					$token=4;
					$employees_po=array();
				}
				if($designation_login_user==1){
					$token++;
					$employees_po=$data;

				}
				//check for state if spo
				if($designation_login_user==2){
					$token++;
					$state_login_user = EmployeeProfile::where(['user_id' => $designation_login_data->user_id])
											->first();

					$login_user_state_id = $state_login_user->state_id;

					$i=0;
					 foreach($data as $employee){
						 if($employee->state_id==$login_user_state_id AND $employee->designation_id==3){
							 if(!empty($employee)){
								$employees_po[$i] = $employee;
							}

						 }
					$i++;
					}
				}
				if((!isset($employees_po) OR empty($employees_po)) AND $token==0){
					$employees_po=$data;
				}

        }



    	$projects = Project::where(['isactive'=>1,'approval_status'=>'1'])->get();
        $departments = Department::where(['isactive'=>1])->select('id','name')->get();

    	return view('employees.list')->with(['departments'=>$departments,'projects'=>$projects,'req'=>$req, 'data_emp'=>$employees_po]);



    }//end of function


    /*
        Activate or Deactivate an employee with relieve/rejoin data
    */
    function changeEmployeeStatus(Request $request)

    {

    	$employee = Employee::where(['user_id'=>$request->user_id])->first();



	    if($request->action == "activate"){



	        $data = [

	                    'isactive' => 1,

	                    'rejoin_date' => date("Y-m-d",strtotime($request->action_date)),

	                    'rejoin_description' => $request->description

	                ];



	    }elseif($request->action == "deactivate"){



	        $data = [

	                    'isactive' => 0,

	                    'relieve_date' => date("Y-m-d",strtotime($request->action_date)),

	                    'relieve_description' => $request->description

	                ];



	    }



	    $employee->update($data);



	    return redirect('employees/list');



    }//end of function

    function bandCity(Request $request){

        $city=City::where('id', $request->city)

            ->with(['city_class', 'city_class.bands'])

            ->first();



        return Band::where('id', $request->band)

                ->with(['city_class' => function($query) use ($request, $city){

                    $query->where('city_class_id',$city->city_class_id);

                }])

                ->first();



    }


    /*
        Get the information required to show on create employee form
    */
    function create($tabname = null)

    {

        $data = array();



        if(empty($tabname)){

            $data['tabname'] = "basicDetailsTab";

        }else{

            $data['tabname'] = $tabname;

        }



        $data['roles'] = Role::select('id','name')->get();

        $data['permissions'] = Permission::select('id','name')->get();

        $data['countries'] = Country::where(['isactive'=>1])->get();



        $data['states'] = State::where(['isactive'=>1])->get();

        //$data['cities'] = City::where(['isactive'=>1])->get();

        $data['skills'] = Skill::where(['isactive'=>1])->select('id','name')->get();



        $data['languages'] = Language::where(['isactive'=>1])->select('id','name')->get();

        $data['departments'] = Department::where(['isactive'=>1])->select('id','name')->get();

        $data['locations'] = Location::where(['isactive'=>1])->select('id','name')->get();



        $data['shifts'] = Shift::where(['isactive'=>1])->select('id','name')->get();

        $data['projects'] = Project::where(['isactive'=>1,'approval_status'=>'1'])->get();

        $data['financial_institutions'] = Bank::where(['isactive'=>1])->select('id','name')->get();



        $data['probation_periods'] = ProbationPeriod::where(['isactive'=>1])->get();

        $data['perks'] = Perk::where(['isactive'=>1])->select('id','name')->get();

        $data['qualifications'] = Qualification::where(['isactive'=>1])->select('id','name')->get();



        $data['designations'] = Designation::where(['isactive'=>1])->select('id','name')->get();



        $data['next_available_uid'] = User::max('id') + 1;

        $data['salary_cycles'] = SalaryCycle::where(['isactive'=>1])->get();

        $data['salary_structures'] = SalaryStructure::where(['isactive'=>1])->get();



        $last_inserted_employee = session('last_inserted_employee');



        if(empty($last_inserted_employee)){

          $last_inserted_employee = 0;

          $data['employment_histories'] = collect();

          $data['qualification_documents'] = collect();



        }else{

          $data['employment_histories'] = EmploymentHistory::where(['user_id'=>$last_inserted_employee,'isactive'=>1])->get();

          $data['qualification_documents'] = DB::table('qualification_user as qu')

                                    ->join('qualifications as q','q.id','=','qu.qualification_id')

                                    ->where(['qu.user_id'=>$last_inserted_employee,'qu.isactive'=>1])

                                    ->select('qu.id','qu.qualification_id','qu.filename','q.name')

                                    ->get();

        }



        $data['documents'] = Document::where(['document_category_id'=>1,'isactive'=>1])

                                    ->get();



        foreach ($data['documents'] as $key => $value) {

            $value->filenames = DB::table('document_user')

                                ->where(['document_id'=>$value->id,'user_id'=>$last_inserted_employee])

                                ->pluck('name')->toArray();

        }



        return view('employees.create')->with(['data'=>$data]);



    }//end of function


    /*
        Get the information required to show on edit employee form
    */
    function edit($user_id,$tabname = null)

    {

        $data = array();



        if(empty($tabname)){

            $data['tabname'] = "basicDetailsTab";

        }else{

            $data['tabname'] = $tabname;

        }



        $data['roles'] = Role::select('id','name')->get();

        $data['permissions'] = Permission::select('id','name')->get();

        $data['countries'] = Country::where(['isactive'=>1])->get();



        $data['states'] = State::where(['isactive'=>1])->get();

        $data['skills'] = Skill::where(['isactive'=>1])->select('id','name')->get();



        $data['languages'] = Language::where(['isactive'=>1])->select('id','name')->get();

        $data['departments'] = Department::where(['isactive'=>1])->select('id','name')->get();

        $data['locations'] = Location::where(['isactive'=>1])->select('id','name')->get();



        $data['shifts'] = Shift::where(['isactive'=>1])->select('id','name')->get();

        $data['projects'] = Project::where(['isactive'=>1,'approval_status'=>'1'])->get();

        $data['financial_institutions'] = Bank::where(['isactive'=>1])->select('id','name')->get();



        $data['probation_periods'] = ProbationPeriod::where(['isactive'=>1])->get();

        $data['perks'] = Perk::where(['isactive'=>1])->select('id','name')->get();

        $data['qualifications'] = Qualification::where(['isactive'=>1])->select('id','name')->get();



        $data['designations'] = Designation::where(['isactive'=>1])->select('id','name')->get();



        //$data['next_available_uid'] = User::max('id') + 1;

        $data['salary_cycles'] = SalaryCycle::where(['isactive'=>1])->get();

        $data['salary_structures'] = SalaryStructure::where(['isactive'=>1])->get();



        $data['employment_histories'] = EmploymentHistory::where(['user_id'=>$user_id,'isactive'=>1])->get();

        $data['qualification_documents'] = DB::table('qualification_user as qu')

                                    ->join('qualifications as q','q.id','=','qu.qualification_id')

                                    ->where(['qu.user_id'=>$user_id,'qu.isactive'=>1])

                                    ->select('qu.id','qu.qualification_id','qu.filename','q.name')

                                    ->get();



        $data['documents'] = Document::where(['document_category_id'=>1,'isactive'=>1])

                                        ->get();



        foreach ($data['documents'] as $key => $value) {

            $value->filenames = DB::table('document_user')

                                ->where(['document_id'=>$value->id,'user_id'=>$user_id])

                                ->pluck('name')->toArray();

        }



        $data['user'] = User::where(['id'=>$user_id])

                            ->with('employee')

                            ->with('employeeProfile')

                            ->with('approval.approver.employee:id,user_id,fullname')

                            ->with('roles:id,name')

                            ->with('languages')

                            ->with('locations')

                            ->with('skills')

                            ->with('qualifications')

                            ->with('permissions:id,name')

                            ->with('perks')

                            ->with('projects')

                            ->with('userManager.manager.employee:id,user_id,fullname')

                            ->with('employeeAddresses')

                            ->with('employeeAccount')

                            ->with('employeeReferences')

                            ->with('employeeSecurity')

                            ->first();


//return $data;
        if($data['user']->employee->approval_status == '0'){

            $data['approve_url'] = url("employees/approve");

            $data['approver_name'] = "";

        }else{

            $data['approve_url'] = "";

            $data['approver_name'] = $data['user']->approval->approver->employee->fullname;

        }



        $data['language_check_boxes'] = $data['user']->languages()

                                        ->select('language_id','read_language','write_language','speak_language')

                                        ->get()->toArray();



        $leave_authorities = $data['user']->leaveAuthorities()

                            ->where('isactive',1)

                            ->orderBy('priority')

                            ->pluck('manager_id')

                            ->toArray();



        if(@$data['user']->userManager->manager->employee->user_id){

            array_unshift($leave_authorities,$data['user']->userManager->manager->employee->user_id);

        }



        if(!empty($leave_authorities)){

            $distinct_leave_authorities = array_unique($leave_authorities);

            $so_departments = EmployeeProfile::whereIn('user_id',$distinct_leave_authorities)

                                        ->pluck('department_id')

                                        ->toArray();



            $distinct_so_departments = array_unique($so_departments);

            $data['leave_authorities'] = $leave_authorities;

            $data['so_departments'] = $distinct_so_departments;

        }else{

            $data['leave_authorities'] = [];

            $data['so_departments'] = [];

        }

        $data['shift_exception_details'] = ShiftException::where(['user_id'=>$user_id])
                                            ->with('Shift')
                                            ->get();

        return view('employees.edit')->with(['data'=>$data]);



    }//end of function


    /*
        Approve a specific employee after creating the employee
    */
    function approveEmployee(Request $request)

    {

        $user = User::find($request->user_id);

        $employee = $user->employee;



        $approver = Auth::user();



        if($employee->approval_status == '0'){

            $employee->approval_status = '1';

            $employee->save();



            $user->approval()->create(['approver_id'=>$approver->id]);

        }



        $result['approver_name'] = $approver->employee->fullname;

        $result['approved'] = 1;

        return $result;



    }//end of function



    /*
        Save the details of basic details tab of create employee form
    */
    function createBasicDetails(Request $request)

    {
		/* echo"test";
		exit; */

        $validator = Validator::make($request->all(), [

            'email' => 'bail|required|unique:users,email',

            'mobile' => 'bail|required|unique:employees,mobile_number',

            'password' => 'bail|required',

            'employeeName' => 'bail|required',

            'employeeLastName' => 'bail|required',

            'employeeXeamCode' => 'bail|required|unique:users,employee_code'

        ]);



        if($validator->fails()) {

            return redirect("employees/create")

                        ->withErrors($validator,'basic')

                        ->withInput();

        }



        $user_data = [

                        'email' => $request->email,

                        'employee_code'  => $request->employeeXeamCode,

                        'password' => Hash::make($request->password),

                     ];



        $user = User::create($user_data);



        if(empty($request->employeeMiddleName)){

          $employee_middle_name = "";

        }else{

          $employee_middle_name = $request->employeeMiddleName;

        }



        $fullname = $request->employeeName." ".$employee_middle_name." ".$request->employeeLastName;



        if($request->expYrs == 0){

            $experience = "0-0";

            $experience_status = '0';

        }else{

            $experience = $request->expYrs."-".$request->expMns;

            $experience_status = '1';

        }



        $employee_data = [

                            'user_id' => $user->id,

                            'creator_id' => Auth::id(),

                            'employee_id' => $request->oldXeamCode,

                            'salutation' => $request->salutation,

                            'fullname' => $fullname,

                            'first_name' => $request->employeeName,

                            'middle_name' => $employee_middle_name,

                            'last_name' => $request->employeeLastName,

                            'personal_email' => $request->personalEmail,

                            'attendance_type' => $request->attendanceType,

                            'mobile_number' => $request->mobile,

                            'country_id' => $request->mobileStdId,

                            'alternative_mobile_number' => $request->altMobile,

                            'alt_country_id' => $request->altMobileStdId,

                            'experience_year_month' => $experience,

                            'experience_status' => $experience_status,

                            'marital_status' => $request->maritalStatus,

                            'gender' => $request->gender,

                            'approval_status' => '0',

                            'father_name' => $request->fatherName,

                            'mother_name' => $request->motherName,

                            'spouse_name' => "",

                            'birth_date'  => date("Y-m-d",strtotime($request->birthDate)),

                            'joining_date' => date("Y-m-d",strtotime($request->joiningDate)),

                            'nominee_name'  => $request->nominee,

                            'relation'  => $request->relation,

                            'nominee_type' => $request->nomineeType,

                            'registration_fees'=> $request->registrationFees,

                            'application_number' => $request->applicationNumber,

                            'spouse_working_status' => 'No',

                            'spouse_company_name' => '',

                            'spouse_designation' => '0',

                            'spouse_contact_number' => '',

                        ];



        if(empty($request->referralCode)){

            $employee_data['referral_code'] = strtoupper(str_random(8));

        }else{

            $employee_data['referral_code'] = $request->referralCode;

        }



        if($request->nomineeType == 'Insurance'){

          $employee_data['insurance_company_name'] = $request->insuranceCompanyName;

          $employee_data['cover_amount'] = $request->coverAmount;

          $employee_data['type_of_insurance'] = $request->typeOfInsurance;

          $employee_data['insurance_expiry_date'] = date("Y-m-d",strtotime($request->insuranceExpiryDate));

        }



        if($request->maritalStatus == "Married" || $request->maritalStatus == "Widowed"){

          $employee_data['spouse_name'] = $request->spouseName;

          $employee_data['marriage_date'] = date("Y-m-d",strtotime($request->marriageDate));



          if($request->maritalStatus == "Married" && !empty($request->spouseWorkingStatus) && $request->spouseWorkingStatus == "Yes"){

            $employee_data['spouse_working_status'] = "Yes";

            $employee_data['spouse_company_name'] = $request->spouseCompanyName;

            $employee_data['spouse_designation'] = $request->spouseDesignation;

            $employee_data['spouse_contact_number'] = $request->spouseContactNumber;

          }

        }



        if($request->hasFile('profilePic')) {

            $profile_pic = time().'.'.$request->file('profilePic')->getClientOriginalExtension();

            $request->file('profilePic')->move(config('constants.uploadPaths.uploadPic'), $profile_pic);



            $employee_data['profile_picture'] = $profile_pic;

        }



        $employee = Employee::create($employee_data);



        $referrer = Employee::where('referral_code',$request->referralCode)->first();



        if(!empty($request->referralCode) && !empty($referrer)){

            $referral_data = [

                                'referrer_id' => $referrer->user_id

                             ];



            $user->employeeReferral()->create($referral_data);

        }



        if(!empty($request->skillIds)){

            $user->skills()->sync($request->skillIds);

        }



        if(!empty($request->qualificationIds)){

            $user->qualifications()->sync($request->qualificationIds);

        }



        if(!empty($request->languageIds)){

            $user->languages()->sync($request->languageIds);

        }



        $post_array = $request->all();

        $language_check_boxes = [];


	if($request->languageIds){
        foreach ($request->languageIds as $key => $value) {

              $key2 = 'lang'.$value;



              if(!empty($post_array[$key2])){

                $language_check_boxes[$value] = $post_array[$key2];

              }else{

                $language_check_boxes[$value] = array();

              }



              if(in_array('1',$language_check_boxes[$value])){

                $check_box_data['read_language'] = true;

              }else{

                $check_box_data['read_language'] = false;

              }



              if(in_array('2',$language_check_boxes[$value])){

                $check_box_data['write_language'] = true;

              }else{

                $check_box_data['write_language'] = false;

              }



              if(in_array('3',$language_check_boxes[$value])){

                $check_box_data['speak_language'] = true;

              }else{

                $check_box_data['speak_language'] = false;

              }



              $find_language = DB::table('language_user')

                                ->where(['user_id'=>$user->id,'language_id'=>$value])

                                ->update($check_box_data);





        }
	}


        session(['last_inserted_employee' => $user->id]);



        if($request->formSubmitButton == 'sc'){

            return redirect("employees/create/projectDetailsTab")->with('profileSuccess',"Details saved successfully.");

        }else{

            return redirect("employees/dashboard");

        }

    }//end of function


    /*
        Save the details of basic details tab of edit employee form
    */
    function editBasicDetails(Request $request)

    {

        $validator = Validator::make($request->all(), [
            'employeeName' => 'bail|required',

            'employeeLastName' => 'bail|required'

        ]);



        if($validator->fails()) {

            return redirect("/employees/edit/$request->employeeId")

                        ->withErrors($validator,'basic')

                        ->withInput();

        }



        $user_data = Employee::where(['user_id'=>$request->employeeId])

                    ->with('user')

                    ->with('user.languages')

                    ->with('user.skills')

                    ->with('user.qualifications')

                    ->first();



        $log = Log::where(['name'=>'User-Updated'])->first();

        $log_data = [

                        'log_id' => $log->id,

                        'data' => $user_data->toJson()

                    ];



        $updated_by = Auth::user();

        $username = $updated_by->employee->fullname;

        $log_data['message'] = $log->name. " by ".$username."(".$updated_by->id.").";

        $user_data->logDetails()->create($log_data);



        $user = User::find($request->employeeId);



        if(empty($request->employeeMiddleName)){

            $employee_middle_name = "";

        }else{

            $employee_middle_name = $request->employeeMiddleName;

        }



        $fullname = $request->employeeName." ".$employee_middle_name." ".$request->employeeLastName;



        if($request->expYrs == 0){

            $experience = "0-0";

            $experience_status = '0';

        }else{

            $experience = $request->expYrs."-".$request->expMns;

            $experience_status = '1';

        }



        $employee_data = [

                            'salutation' => $request->salutation,

                            'fullname' => $fullname,

                            'first_name' => $request->employeeName,

                            'middle_name' => $employee_middle_name,

                            'last_name' => $request->employeeLastName,

                            'personal_email' => $request->personalEmail,

                            'attendance_type' => $request->attendanceType,

                            'alternative_mobile_number' => $request->altMobile,

                            'alt_country_id' => $request->altMobileStdId,

                            'experience_year_month' => $experience,

                            'experience_status' => $experience_status,

                            'marital_status' => $request->maritalStatus,

                            'gender' => $request->gender,

                            'father_name' => $request->fatherName,

                            'mother_name' => $request->motherName,

                            'spouse_name' => "",

                            'birth_date'  => date("Y-m-d",strtotime($request->birthDate)),

                            'joining_date' => date("Y-m-d",strtotime($request->joiningDate)),

                            'nominee_name'  => $request->nominee,

                            'relation'  => $request->relation,

                            'nominee_type' => $request->nomineeType,

                            'registration_fees'=> $request->registrationFees,

                            'application_number' => $request->applicationNumber,

                            'spouse_working_status' => 'No',

                            'spouse_company_name' => '',

                            'spouse_designation' => '0',

                            'spouse_contact_number' => '',

                        ];



        if($request->nomineeType == 'Insurance'){

            $employee_data['insurance_company_name'] = $request->insuranceCompanyName;

            $employee_data['cover_amount'] = $request->coverAmount;

            $employee_data['type_of_insurance'] = $request->typeOfInsurance;

            $employee_data['insurance_expiry_date'] = date("Y-m-d",strtotime($request->insuranceExpiryDate));

        }



        if($request->maritalStatus == "Married" || $request->maritalStatus == "Widowed"){

            $employee_data['spouse_name'] = $request->spouseName;

            $employee_data['marriage_date'] = date("Y-m-d",strtotime($request->marriageDate));



            if($request->maritalStatus == "Married" && !empty($request->spouseWorkingStatus) &&             $request->spouseWorkingStatus == "Yes"){

                $employee_data['spouse_working_status'] = "Yes";

                $employee_data['spouse_company_name'] = $request->spouseCompanyName;

                $employee_data['spouse_designation'] = $request->spouseDesignation;

                $employee_data['spouse_contact_number'] = $request->spouseContactNumber;

            }

        }



        if($request->hasFile('profilePic')) {

            $profile_pic = time().'.'.$request->file('profilePic')->getClientOriginalExtension();

            $request->file('profilePic')->move(config('constants.uploadPaths.uploadPic'), $profile_pic);



            $employee_data['profile_picture'] = $profile_pic;

        }



        $employee = Employee::where(['user_id'=>$user->id])->update($employee_data);



        if(!empty($request->skillIds)){

            $user->skills()->sync($request->skillIds);

        }



        if(!empty($request->qualificationIds)){

            $user->qualifications()->sync($request->qualificationIds);

        }



        if(!empty($request->languageIds)){

            $user->languages()->sync($request->languageIds);

        }



        $post_array = $request->all();

        $language_check_boxes = [];


	if($request->languageIds){
        foreach ($request->languageIds as $key => $value) {

              $key2 = 'lang'.$value;



              if(!empty($post_array[$key2])){

                $language_check_boxes[$value] = $post_array[$key2];

              }else{

                $language_check_boxes[$value] = array();

              }



              if(in_array('1',$language_check_boxes[$value])){

                $check_box_data['read_language'] = true;

              }else{

                $check_box_data['read_language'] = false;

              }



              if(in_array('2',$language_check_boxes[$value])){

                $check_box_data['write_language'] = true;

              }else{

                $check_box_data['write_language'] = false;

              }



              if(in_array('3',$language_check_boxes[$value])){

                $check_box_data['speak_language'] = true;

              }else{

                $check_box_data['speak_language'] = false;

              }



              $find_language = DB::table('language_user')

                                ->where(['user_id'=>$user->id,'language_id'=>$value])

                                ->update($check_box_data);

        }
	}



        if($request->formSubmitButton == 'sc'){

            return redirect("/employees/edit/$request->employeeId/projectDetailsTab")->with('profileSuccess',"Details updated successfully.");

        }else{

            return redirect("/employees/dashboard");

        }



    }//end of function


    /*
        Save the details of profile details tab of create employee form
    */
    function createProfileDetails(Request $request)

    {

        $validator = Validator::make($request->all(), [

            'projectId' => 'bail|required',

            'permissionIds' => 'bail|required',

            'employeeIds' => 'bail|required'

            //'designation' => 'bail|required'

        ]);



        if($validator->fails()) {

            return redirect('/employees/create/projectDetailsTab')

                        ->withErrors($validator,'profile')

                        ->withInput();

        }



        $employee_profile_data =   [

                                   'shift_id'  => $request->shiftTimingId,

                                   'department_id' => $request->departmentId,

                                   "probation_period_id" => $request->probationPeriodId,

                                   'state_id' => $request->stateId,

                                   'probation_approval_status' => '0',

                                   'probation_hod_approval' => '0',

                                   'probation_hr_approval' => '0'

                                ];



        $last_inserted_employee = session('last_inserted_employee');

        $check_unique = EmployeeProfile::where(['user_id'=>$last_inserted_employee])->first();



        if(!empty($check_unique->user_id)){

          return redirect('employees/create')->with('profileError',"Details of this employee have already been saved. Please create a new employee.");



        }else{


            $user = User::find($last_inserted_employee);

            $role = Role::find($request->roleId);

            $user->assignRole($role->name);

            $user->syncPermissions($request->permissionIds);



            $employee = $user->employee()->first();

            $probation = ProbationPeriod::find($request->probationPeriodId);


			if($probation)
			{
				$employee_profile_data['probation_end_date'] = Carbon::parse($employee->joining_date)->addDays($probation->no_of_days)->toDateString();
			}



            $user->employeeProfile()->create($employee_profile_data);

            if(is_array($request->exceptionshiftTimingId) && is_array($request->exceptionshiftday)){
                for($i=0;$i<count($request->exceptionshiftTimingId); $i++){
                    $ShiftExcept = new ShiftException;
                    $ShiftExcept->user_id       = $last_inserted_employee;
                    $ShiftExcept->shift_id      = $request->exceptionshiftTimingId[$i];
                    $ShiftExcept->week_day = $request->exceptionshiftday[$i];;
                    $ShiftExcept->save();

                }
            }

            $user->userManager()->create(['manager_id'=>$request->employeeIds]);


            $manager = User::find($request->employeeIds);

            if(!$manager->hasPermissionTo('approve-leave')){

                $manager->givePermissionTo(['approve-leave']);

            }



            if(!empty($request->hodId)){

                $user->leaveAuthorities()->create(['manager_id'=>$request->hodId,'priority'=>'2']);

                $manager = User::find($request->hodId);

                if(!$manager->hasPermissionTo('approve-leave')){

                    $manager->givePermissionTo(['approve-leave']);

                }

            }



            if(!empty($request->hrId)){

                $user->leaveAuthorities()->create(['manager_id'=>$request->hrId,'priority'=>'3']);

                $manager = User::find($request->hrId);

                if(!$manager->hasPermissionTo('approve-leave')){

                    $manager->givePermissionTo(['approve-leave']);

                }

            }



            if(!empty($request->mdId)){

                $user->leaveAuthorities()->create(['manager_id'=>$request->mdId,'priority'=>'4']);

                $manager = User::find($request->mdId);

                if(!$manager->hasPermissionTo('approve-leave')){

                    $manager->givePermissionTo(['approve-leave']);

                }

            }



            if(!empty($request->perkIds)){

                $user->perks()->sync($request->perkIds);

            }



            if(!empty($request->locationId)){

                $locations = [];

                array_push($locations,$request->locationId);

                $user->locations()->sync($locations);

            }



            $projects = [];

            array_push($projects,$request->projectId);



            if(!empty($request->projectId)){

                $user->projects()->sync($projects);

            }



            if(!empty($request->designation)){

                $designations = [];

                array_push($designations,$request->designation);

                $user->designation()->sync($designations);

            }



            if($request->formSubmitButton == 'sc'){

              return redirect("employees/create/")->with('documentSuccess',"Details saved successfully.");

            }else{

              return redirect("employees/dashboard");

            }



        }



    }//end of function


    /*
        Save the details of profile details tab of edit employee form
    */
    function editProfileDetails(Request $request)

    {

        $validator = Validator::make($request->all(), [

            'projectId' => 'bail|required',

            //'probationPeriodId' => 'bail|required',

            'permissionIds' => 'bail|required',

            'employeeIds' => 'bail|required',

            //'designation' => 'bail|required'

        ]);

        if($request->delete_id!="")
        {
            $message = '';
            $del_id=$request->delete_id;
            $del_info=ShiftException::where('id',$del_id)->delete();

            if($del_info) {
                return response()->json(['status'=>'1', 'msg'=>'Shift deleted successfully']);

            } else {
                 return response()->json(['status'=>'0', 'msg'=>'Shift deletion failed']);
            }

        }


        if($validator->fails()) {

            return redirect("/employees/edit/$request->employeeId/projectDetailsTab")

                        ->withErrors($validator,'profile')

                        ->withInput();

        }



        $user_data = User::where(['id'=>$request->employeeId])

                    ->with('roles:id,name')

                    ->with('locations')

                    ->with('permissions:id,name')

                    ->with('perks')

                    ->with('projects')

                    ->with('userManager')

                    ->with('leaveAuthorities')

                    ->with('designation')

                    ->first();



        if(!empty($user_data)){

            $employeeProfile = $user_data->employeeProfile()->first();



            $log = Log::where(['name'=>'User-Updated'])->first();

            $log_data = [

                            'log_id' => $log->id,

                            'data' => $user_data->toJson()

                        ];



            $updated_by = Auth::user();

            $username = $updated_by->employee->fullname;

            $log_data['message'] = $log->name. " by ".$username."(".$updated_by->id.").";

            if(!empty($employeeProfile)){
                $employeeProfile->logDetails()->create($log_data);
            }


        }



        $employee_profile_data = [

            'shift_id'  => $request->shiftTimingId,

            'department_id' => $request->departmentId,

            'state_id' => $request->stateId

        ];



        $user = User::find($request->employeeId);

        $role = Role::find($request->roleId);

        $roles = [];

        $roles[0] = $role->name;

        $user->syncRoles($roles);

        $user->syncPermissions($request->permissionIds);

        $employee = $user->employee()->first();



        $check_unique = EmployeeProfile::where(['user_id'=>$request->employeeId])->first();



        if(empty($check_unique)){

            $employee_profile_data['probation_period_id'] = $request->probationPeriodId;

            $employee_profile_data['probation_approval_status'] = '0';

            $employee_profile_data['probation_hod_approval'] = '0';

            $employee_profile_data['probation_hr_approval'] = '0';



            $probation = ProbationPeriod::find($request->probationPeriodId);

            $employee_profile_data['probation_end_date'] = Carbon::parse($employee->joining_date)->addDays($probation->no_of_days)->toDateString();



            $user->employeeProfile()->create($employee_profile_data);

            $user->userManager()->create(['manager_id'=>$request->employeeIds]);



        }else{

            $user->employeeProfile()->update($employee_profile_data);

            if(is_array($request->exceptionshiftTimingId) && is_array($request->exceptionshiftday)){

                for($i=0;$i<count($request->shiftexcept); $i++){
                    ShiftException::where('id', $request->shiftexcept[$i])
                        ->update([
                            'user_id'=> $request->employeeId,
                            'shift_id'=>$request->exceptionshiftTimingId[$i],
                            'week_day'=> $request->exceptionshiftday[$i]
                            ]);
                    }

            }

            if(!empty($request->exceptionshiftTimingId_new) && !empty($request->exceptionshiftday_new)){
                        for($j=0;$j<count($request->exceptionshiftTimingId_new); $j++){
                            $Shift_Except = new ShiftException;
                            $Shift_Except->user_id       = $request->employeeId;
                            $Shift_Except->shift_id      = $request->exceptionshiftTimingId_new[$j];
                            $Shift_Except->week_day = $request->exceptionshiftday_new[$j];
                            $Shift_Except->save();

                        }
                    }

            $user->userManager()->update(['manager_id'=>$request->employeeIds]);

        }



        $manager = User::find($request->employeeIds);

        if(!$manager->hasPermissionTo('approve-leave')){

            $manager->givePermissionTo(['approve-leave']);

        }



        if(!empty($request->hodId)){

            LeaveAuthority::updateOrCreate(['user_id'=>$user->id,'priority'=>'2'],['manager_id'=>$request->hodId]);

            $manager = User::find($request->hodId);

            if(!$manager->hasPermissionTo('approve-leave')){

                $manager->givePermissionTo(['approve-leave']);

            }

        }



        if(!empty($request->hrId)){

            LeaveAuthority::updateOrCreate(['user_id'=>$user->id,'priority'=>'3'],['manager_id'=>$request->hrId]);

            $manager = User::find($request->hrId);

            if(!$manager->hasPermissionTo('approve-leave')){

                $manager->givePermissionTo(['approve-leave']);

            }

        }



        if(!empty($request->mdId)){

            LeaveAuthority::updateOrCreate(['user_id'=>$user->id,'priority'=>'4'],['manager_id'=>$request->mdId]);

            $manager = User::find($request->mdId);

            if(!$manager->hasPermissionTo('approve-leave')){

                $manager->givePermissionTo(['approve-leave']);

            }

        }



        if(!empty($request->perkIds)){

            $user->perks()->sync($request->perkIds);

        }



        if(!empty($request->locationId)){

            $locations = [];

            array_push($locations,$request->locationId);

            $user->locations()->sync($locations);

        }



        $projects = [];

        array_push($projects,$request->projectId);



        if(!empty($request->projectId)){

            $user->projects()->sync($projects);

        }



        if(!empty($request->designation)){

            $designations = [];

            array_push($designations,$request->designation);

            $user->designation()->sync($designations);

        }



        if($request->formSubmitButton == 'sc'){

            return redirect("/employees/edit/$request->employeeId/documentDetailsTab")->with('documentSuccess',"Details updated successfully.");

        }else{

            return redirect("/employees/dashboard");

        }

    }//end of function


    /*
        Save the details of document details tab of create employee form
    */
    function storeDocumentDetails(Request $request)

    {

        $validator = Validator::make($request->all(), [

            'docTypeId' => 'required',

            'docs2' => 'required'

        ]);



        if($validator->fails()) {

            return redirect('/employees/create/documentDetailsTab')

                        ->withErrors($validator,'document')

                        ->withInput();

        }



        $last_inserted_employee = session('last_inserted_employee');

        $user = User::find($last_inserted_employee);



        if(!empty($request->docs2) && !empty($user)){

            $documents = $request->docs2;

            $document_info = Document::find($request->docTypeId);



            foreach ($documents as $doc) {

                $document = round(microtime(true)).str_random(5).'.'.$doc->getClientOriginalExtension();

                $doc->move(config('constants.uploadPaths.uploadDocument'), $document);



                $document_data['name'] = $document;

                $user->documents()->attach($document_info,$document_data);

            }

        }



        return redirect('employees/create/documentDetailsTab')->with('documentSuccess',"Documents saved successfully.");



    }//end of function


    /*
        Save the details of document details tab of edit employee form
    */
    function editDocumentDetails(Request $request)

    {

        $validator = Validator::make($request->all(), [

            'docTypeId' => 'required',

            'docs2' => 'required'

        ]);



        if ($validator->fails()) {

            return redirect("/employees/edit/$request->employeeId/documentDetailsTab")

                        ->withErrors($validator,'document')

                        ->withInput();

        }



        $user = User::find($request->employeeId);



        if(!empty($request->docs2) && !empty($user)){

            $documents = $request->docs2;

            $document_info = Document::find($request->docTypeId);



            foreach ($documents as $doc) {

                $document = round(microtime(true)).str_random(5).'.'.$doc->getClientOriginalExtension();

                $doc->move(config('constants.uploadPaths.uploadDocument'), $document);



                $document_data['name'] = $document;

                $user->documents()->attach($document_info,$document_data);

            }

        }



        return redirect("/employees/edit/$request->employeeId/documentDetailsTab")->with('documentSuccess',"Documents saved successfully.");



    }//end of function


    /*
        Save the details of qualification document of create employee form
    */
    function storeQualificationDocumentDetails(Request $request)

    {

        $validator = Validator::make($request->all(), [

            'empQualificationId' => 'required',

            'qualificationDocs' => 'required'

        ]);



        if($validator->fails()) {

            return redirect("/employees/create/documentDetailsTab")

                        ->withErrors($validator,'document')

                        ->withInput();

        }



        if(!empty($request->qualificationDocs)){

            $where = ["id" => $request->empQualificationId];



            $documents = $request->qualificationDocs;



            foreach ($documents as $doc) {

                $document = round(microtime(true)).str_random(5).'.'.$doc->getClientOriginalExtension();

                $doc->move(config('constants.uploadPaths.uploadQualificationDocument'), $document);



                $document_data['filename'] = $document;



                DB::table('qualification_user')

                ->where($where)

                ->update($document_data);

            }

        }



        return redirect("employees/create/documentDetailsTab")->with('documentSuccess',"Documents saved successfully.");



    }//end of function


    /*
        Save the details of qualification document of edit employee form
    */
    function editQualificationDocumentDetails(Request $request)

    {

        $validator = Validator::make($request->all(), [

            'empQualificationId' => 'required',

            'qualificationDocs' => 'required'

        ]);



        if ($validator->fails()) {

            return redirect("/employees/edit/$request->employeeId/documentDetailsTab")

                        ->withErrors($validator,'document')

                        ->withInput();

        }



        if(!empty($request->qualificationDocs)){

            $where = ["id" => $request->empQualificationId];



            $documents = $request->qualificationDocs;



            foreach ($documents as $doc) {

                $document = round(microtime(true)).str_random(5).'.'.$doc->getClientOriginalExtension();

                $doc->move(config('constants.uploadPaths.uploadQualificationDocument'), $document);



                $document_data['filename'] = $document;



                DB::table('qualification_user')

                ->where($where)

                ->update($document_data);

            }

        }



        return redirect("/employees/edit/$request->employeeId/documentDetailsTab")->with('documentSuccess',"Documents saved successfully.");



    }//end of function


    /*
        Save the details of account details tab of create employee form
    */
    function createAccountDetails(Request $request)

    {

        $validator = Validator::make($request->all(), [

            'bankAccNo' => 'required',

            'adhaar' => 'required',

            'panNo' => 'required',

            'accHolderName' => 'required',

            'ifsc' => 'required'

        ]);



        if($validator->fails()) {

            return redirect('/employees/create/accountDetailsTab')

                        ->withErrors($validator,'account')

                        ->withInput();

        }



        $last_inserted_employee = session('last_inserted_employee');

        $user = User::find($last_inserted_employee);



        $check_unique = $user->employeeAccount()->first();



        if(!empty($check_unique)){

            return redirect('employees/create')->with('profileError',"Details of this employee have already been saved. Please create a new employee.");

        }else{



            $data = [

                        'adhaar'  => $request->adhaar,

                        'pan_number'        => $request->panNo,

                        'uan_number'   => $request->uanNo,

                        'account_holder_name'   => $request->accHolderName,

                        'bank_account_number'   => $request->bankAccNo,

                        'ifsc_code'   => $request->ifsc,

                        'pf_number_department'   => $request->pfNoDepartment,

                        'bank_id'   => $request->financialInstitutionId,

                        'esi_number' => $request->empEsiNo,

                        'dispensary' => $request->empDispensary,

                        'remarks' => $request->remarks,

                        'contract_signed' => $request->contractSigned

                    ];

            if(!$request->has('contractSigned')){
                $data['contract_signed'] = '0';
            }

            if($request->contractSigned == '1' && !empty($request->contractSignedDate)){

                $data['contract_signed_date'] = date("Y-m-d",strtotime($request->contractSignedDate));

            }



            if(!empty($request->employmentVerification)){

                $data['employment_verification'] = '1';

            }else{

                $data['employment_verification'] = '0';

            }



            if(!empty($request->addressVerification)){

                $data['address_verification'] = '1';

            }else{

                $data['address_verification'] = '0';

            }



            if(!empty($request->policeVerification)){

                $data['police_verification'] = '1';

            }else{

                $data['police_verification'] = '0';

            }



            $user->employeeAccount()->create($data);



            if($request->formSubmitButton == 'sc'){

                return redirect("employees/create/addressDetailsTab")->with('addressSuccess',"Details saved successfully.");

            }else{

                return redirect("employees/dashboard");

            }



        }

    }//end of function


    /*
        Save the details of account details tab of edit employee form
    */
    function editAccountDetails(Request $request)

    {

        $validator = Validator::make($request->all(), [

            'bankAccNo' => 'required',

            'adhaar' => 'required',

            'panNo' => 'required',

            'accHolderName' => 'required',

            'ifsc' => 'required'

        ]);



        if($validator->fails()) {

            return redirect("/employees/edit/$request->employeeId/accountDetailsTab")

                        ->withErrors($validator,'account')

                        ->withInput();

        }



        $user_data = EmployeeAccount::where(['user_id'=>$request->employeeId])

                                    ->with('user')

                                    ->first();



        if(!empty($user_data)){

            $log = Log::where(['name'=>'User-Updated'])->first();

            $log_data = [

                            'log_id' => $log->id,

                            'data' => $user_data->toJson()

                        ];



            $updated_by = Auth::user();

            $username = $updated_by->employee->fullname;

            $log_data['message'] = $log->name. " by ".$username."(".$updated_by->id.").";

            $user_data->logDetails()->create($log_data);

        }



        $user = User::find($request->employeeId);

        $data = [

                    'adhaar'  => $request->adhaar,

                    'pan_number'        => $request->panNo,

                    'uan_number'   => $request->uanNo,

                    'account_holder_name'   => $request->accHolderName,

                    'bank_account_number'   => $request->bankAccNo,

                    'ifsc_code'   => $request->ifsc,

                    'pf_number_department'   => $request->pfNoDepartment,

                    'bank_id'   => $request->financialInstitutionId,

                    'esi_number' => $request->empEsiNo,

                    'dispensary' => $request->empDispensary,

                    'remarks' => $request->remarks,

                    'contract_signed' => $request->contractSigned

                ];

        if(!$request->has('contractSigned')){
            $data['contract_signed'] = '0';
        }

        if($request->contractSigned == '1' && !empty($request->contractSignedDate)){

            $data['contract_signed_date'] = date("Y-m-d",strtotime($request->contractSignedDate));

        }



        if(!empty($request->employmentVerification)){

            $data['employment_verification'] = '1';

        }else{

            $data['employment_verification'] = '0';

        }



        if(!empty($request->addressVerification)){

            $data['address_verification'] = '1';

        }else{

            $data['address_verification'] = '0';

        }



        if(!empty($request->policeVerification)){

            $data['police_verification'] = '1';

        }else{

            $data['police_verification'] = '0';

        }



        EmployeeAccount::updateOrCreate(['user_id'=>$user->id],$data);



        if($request->formSubmitButton == 'sc'){

            return redirect("/employees/edit/$request->employeeId/addressDetailsTab")->with('addressSuccess',"Details updated successfully.");

        }else{

            return redirect("/employees/dashboard");

        }



    }//end of function


    /*
        Save the details of address details tab of create employee form
    */
    function createAddressDetails(Request $request)

    {

        $validator = Validator::make($request->all(), [

            'perHouseNo' => 'required',

            'perRoadStreet' => 'required',

            'perLocalityArea' => 'required',

            'perPinCode' => 'required',

            'preHouseNo' => 'required',

            'preRoadStreet' => 'required',

            'preLocalityArea' => 'required',

            'prePinCode' => 'required',

            'perCountryId' => 'required',

            'perStateId' => 'required',

            'perCityId' => 'required',

            'preCountryId' => 'required',

            'preStateId' => 'required',

            'preCityId' => 'required'

        ]);



        if($validator->fails()) {

            return redirect('employees/create/addressDetailsTab')

                        ->withErrors($validator,'address')

                        ->withInput();

        }



        $last_inserted_employee = session('last_inserted_employee');



        $user = User::find($last_inserted_employee);

        $check_unique = $user->employeeAddresses()->first();



        if(!empty($check_unique)){

            return redirect('employees/create')->with('profileError',"Details of this employee have already been saved. Please create a new employee.");

        }else{

            $permanent_data =  [

                            'type'  => '2',

                            'house_number' => $request->perHouseNo,

                            'road_street'   => $request->perRoadStreet,

                            'locality_area'   => $request->perLocalityArea,

                            'emergency_number'   => $request->perEmergencyNumber,

                            'emergency_number_country_id'   => $request->perEmergencyNumberStdId,

                            'pincode'   => $request->perPinCode,

                            'country_id'   => $request->perCountryId,

                            'state_id'   => $request->perStateId,

                            'city_id'   => $request->perCityId

                        ];



            $present_data =  [

                            'type'  => '1',

                            'house_number' => $request->preHouseNo,

                            'road_street'   => $request->preRoadStreet,

                            'locality_area'   => $request->preLocalityArea,

                            'emergency_number'   => $request->preEmergencyNumber,

                            'emergency_number_country_id'   => $request->preEmergencyNumberStdId,

                            'pincode'   => $request->prePinCode,

                            'country_id'   => $request->preCountryId,

                            'state_id'   => $request->preStateId,

                            'city_id'   => $request->preCityId

                        ];



            $user->employeeAddresses()->create($present_data);

            $user->employeeAddresses()->create($permanent_data);



            if($request->formSubmitButton == 'sc'){

                return redirect("employees/create/historyDetailsTab")->with('historySuccess',"Details saved successfully.");

            }else{

                return redirect("employees/dashboard");

            }

        }



    }//end of function


    /*
        Save the details of address details tab of edit employee form
    */
    function editAddressDetails(Request $request)

    {

        $validator = Validator::make($request->all(), [

            'perHouseNo' => 'required',

            'perRoadStreet' => 'required',

            'perLocalityArea' => 'required',

            'perPinCode' => 'required',

            'preHouseNo' => 'required',

            'preRoadStreet' => 'required',

            'preLocalityArea' => 'required',

            'prePinCode' => 'required',

            'perCountryId' => 'required',

            'perStateId' => 'required',

            'perCityId' => 'required',

            'preCountryId' => 'required',

            'preStateId' => 'required',

            'preCityId' => 'required'

        ]);



        if($validator->fails()) {

            return redirect("/employees/edit/$request->employeeId/addressDetailsTab")

                        ->withErrors($validator,'address')

                        ->withInput();

        }



        $user_data = EmployeeAddress::where(['user_id'=>$request->employeeId])

                                    ->with('user')

                                    ->get();



        if(!$user_data->isEmpty()){

            $log = Log::where(['name'=>'User-Updated'])->first();

            $log_data = [

                            'log_id' => $log->id,

                            'data' => $user_data->toJson()

                        ];



            $updated_by = Auth::user();

            $username = $updated_by->employee->fullname;

            $log_data['message'] = $log->name. " by ".$username."(".$updated_by->id.").";

            $user_data[0]->logDetails()->create($log_data);

        }



        $user = User::find($request->employeeId);

        $permanent_data =   [

                                'type'  => '2',

                                'house_number' => $request->perHouseNo,

                                'road_street'   => $request->perRoadStreet,

                                'locality_area'   => $request->perLocalityArea,

                                'emergency_number'   => $request->perEmergencyNumber,

                                'emergency_number_country_id'   => $request->perEmergencyNumberStdId,

                                'pincode'   => $request->perPinCode,

                                'country_id'   => $request->perCountryId,

                                'state_id'   => $request->perStateId,

                                'city_id'   => $request->perCityId

                            ];



        $present_data = [

                            'type'  => '1',

                            'house_number' => $request->preHouseNo,

                            'road_street'   => $request->preRoadStreet,

                            'locality_area'   => $request->preLocalityArea,

                            'emergency_number'   => $request->preEmergencyNumber,

                            'emergency_number_country_id'   => $request->preEmergencyNumberStdId,

                            'pincode'   => $request->prePinCode,

                            'country_id'   => $request->preCountryId,

                            'state_id'   => $request->preStateId,

                            'city_id'   => $request->preCityId

                        ];



        EmployeeAddress::updateOrCreate(['user_id'=>$user->id,'type'=>'1'],$present_data);

        EmployeeAddress::updateOrCreate(['user_id'=>$user->id,'type'=>'2'],$permanent_data);



        if($request->formSubmitButton == 'sc'){

            return redirect("/employees/edit/$request->employeeId/historyDetailsTab")->with('historySuccess',"Details updated successfully.");

        }else{

            return redirect("/employees/dashboard");

        }



    }//end of function


    /*
        Save the details of history details tab of create employee form
    */
    function storeHistoryDetails(Request $request)

    {

        $validator = Validator::make($request->all(), [

            'orgName' => 'required',

            'orgEmail' => 'required',

            'fromDate' => 'required',

            'toDate' => 'required',

            'reportTo' => 'required',

            'salaryPerMonth' => 'required',

            'responsibilities' => 'required'

        ]);



        if($validator->fails()){

            return redirect('employees/create/historyDetailsTab')

                        ->withErrors($validator,'history')

                        ->withInput();

        }



        $last_inserted_employee = session('last_inserted_employee');

        $user = User::find($last_inserted_employee);



        $data = [

                   'employment_from'  => date("Y-m-d",strtotime($request->fromDate)),

                   'employment_to'  => date("Y-m-d",strtotime($request->toDate)),

                   'organization_name' => $request->orgName,

                   'organization_email' => $request->orgEmail,

                   'organization_phone' => $request->orgPhone,

                   'country_id' => $request->orgPhoneStdId,

                   'organization_phone_stdcode' => $request->orgPhoneStdCode,

                   'organization_website' => $request->orgWebsite,

                   'responsibilities' => $request->responsibilities,

                   'report_to_position' => $request->reportTo,

                   'salary_per_month' => $request->salaryPerMonth,

                   'perks' => $request->perks,

                   'reason_for_leaving' => $request->leavingReason,

                ];



        $user->employmentHistories()->create($data);



        if($request->formSubmitButton == 's'){

          return redirect("employees/create/historyDetailsTab")->with('historySuccess',"Details saved successfully.");

        }else{

          return redirect("employees/dashboard");

        }



    }//end of function


    /*
        Save the details of history details tab of edit employee form
    */
    function editHistoryDetails(Request $request)

    {

        $validator = Validator::make($request->all(), [

            'orgName' => 'required',

            'orgEmail' => 'required',

            'fromDate' => 'required',

            'toDate' => 'required',

            'reportTo' => 'required',

            'salaryPerMonth' => 'required',

            'responsibilities' => 'required'

        ]);



        if($validator->fails()) {

            return redirect("/employees/edit/$request->employeeId/historyDetailsTab")

                        ->withErrors($validator,'history')

                        ->withInput();

        }



        $user = User::find($request->employeeId);



        $data = [

                    'employment_from'  => date("Y-m-d",strtotime($request->fromDate)),

                    'employment_to'  => date("Y-m-d",strtotime($request->toDate)),

                    'organization_name' => $request->orgName,

                    'organization_email' => $request->orgEmail,

                    'organization_phone' => $request->orgPhone,

                    'country_id' => $request->orgPhoneStdId,

                    'organization_phone_stdcode' => $request->orgPhoneStdCode,

                    'organization_website' => $request->orgWebsite,

                    'responsibilities' => $request->responsibilities,

                    'report_to_position' => $request->reportTo,

                    'salary_per_month' => $request->salaryPerMonth,

                    'perks' => $request->perks,

                    'reason_for_leaving' => $request->leavingReason,

                ];



        $user->employmentHistories()->create($data);



        if($request->formSubmitButton == 's'){

            return redirect("/employees/edit/$request->employeeId/historyDetailsTab")->with('historySuccess',"Details updated successfully.");

        }else{

            return redirect("/employees/dashboard");

        }



    }//end of function


    /*
        Save the details of reference details tab of create employee form
    */
    function createReferenceDetails(Request $request)

    {

        $validator = Validator::make($request->all(), [

            'ref1Name' => 'required',

            'ref1Phone' => 'required',

            'ref1Email' => 'required',

            'ref1Address' => 'required',

            'ref2Name' => 'required',

            'ref2Phone' => 'required',

            'ref2Email' => 'required',

            'ref2Address' => 'required'

        ]);



        if($validator->fails()){

            return redirect('/employees/create/referenceDetailsTab')

                        ->withErrors($validator,'reference')

                        ->withInput();

        }



        $last_inserted_employee = session('last_inserted_employee');

        $user = User::find($last_inserted_employee);

        $check_unique = $user->employeeReferences()->first();



        if(!empty($check_unique)){

            return redirect('employees/create')->with('profileError',"Details of this employee have already been saved. Please create a new employee.");

        }else{



            $data1 = [

                       'type' => '1',

                       'name'  => $request->ref1Name,

                       'phone'  => $request->ref1Phone,

                       'country_id'  => $request->ref1PhoneStdId,

                       'email' => $request->ref1Email,

                       'address' => $request->ref1Address,

                    ];



            $data2 = [

                       'type' => '2',

                       'name'  => $request->ref2Name,

                       'phone'  => $request->ref2Phone,

                       'country_id'  => $request->ref2PhoneStdId,

                       'email' => $request->ref2Email,

                       'address' => $request->ref2Address,

                    ];



            $user->employeeReferences()->create($data1);

            $user->employeeReferences()->create($data2);



            if($request->formSubmitButton == 'sc'){

              return redirect("employees/create/securityDetailsTab")->with('securitySuccess',"Details saved successfully.");

            }else{

              return redirect("employees/dashboard");

            }

        }

    }//end of function


    /*
        Save the details of reference details tab of edit employee form
    */
    function editReferenceDetails(Request $request)

    {

        $validator = Validator::make($request->all(), [

            'ref1Name' => 'required',

            'ref1Phone' => 'required',

            'ref1Email' => 'required',

            'ref1Address' => 'required',

            'ref2Name' => 'required',

            'ref2Phone' => 'required',

            'ref2Email' => 'required',

            'ref2Address' => 'required'

        ]);



        if ($validator->fails()) {

            return redirect("/employees/edit/$request->employeeId/referenceDetailsTab")

                        ->withErrors($validator,'reference')

                        ->withInput();

        }



        $user_data = EmployeeReference::where(['user_id'=>$request->employeeId])

                                    ->with('user')

                                    ->get();



        if(!$user_data->isEmpty()){

            $log = Log::where(['name'=>'User-Updated'])->first();

            $log_data = [

                        'log_id' => $log->id,

                        'data' => $user_data->toJson()

                    ];



            $updated_by = Auth::user();

            $username = $updated_by->employee->fullname;

            $log_data['message'] = $log->name. " by ".$username."(".$updated_by->id.").";

            $user_data[0]->logDetails()->create($log_data);

        }



        $user = User::find($request->employeeId);

        $data1 = [

                    'type' => '1',

                    'name'  => $request->ref1Name,

                    'phone'  => $request->ref1Phone,

                    'country_id'  => $request->ref1PhoneStdId,

                    'email' => $request->ref1Email,

                    'address' => $request->ref1Address,

                ];



        $data2 = [

                    'type' => '2',

                    'name'  => $request->ref2Name,

                    'phone'  => $request->ref2Phone,

                    'country_id'  => $request->ref2PhoneStdId,

                    'email' => $request->ref2Email,

                    'address' => $request->ref2Address,

                ];



        EmployeeReference::updateOrCreate(['user_id'=>$user->id,'type'=>'1'],$data1);

        EmployeeReference::updateOrCreate(['user_id'=>$user->id,'type'=>'2'],$data2);



        if($request->formSubmitButton == 'sc'){

            return redirect("/employees/edit/$request->employeeId/securityDetailsTab")->with('securitySuccess',"Details updated successfully.");

        }else{

            return redirect("/employees/dashboard");

        }



    }//end of function


    /*
        Save the details of security details tab of create employee form
    */
    function createSecurityDetails(Request $request)

    {

        $validator = Validator::make($request->all(), [

            'bankName' => 'bail|required',

            'ddNo' => 'bail|required',

            'accNo' => 'bail|required',

            'receiptNo' => 'bail|required',

            'amount' => 'bail|required'

        ]);



        if($validator->fails()) {

            return redirect('/employees/create/securityDetailsTab')

                        ->withErrors($validator,'security')

                        ->withInput();

        }



        $last_inserted_employee = session('last_inserted_employee');

        $user = User::find($last_inserted_employee);

        $check_unique = $user->employeeSecurity()->first();



        if(!empty($check_unique)){

            return redirect('employees/create')->with('profileError',"Details of this employee have already been saved. Please create a new employee.");

        }else{

            $data = [

                        'dd_number' => $request->ddNo,

                        'account_number'  => $request->accNo,

                        'bank_name'  => $request->bankName,

                        'receipt_number' => $request->receiptNo,

                        'dd_date' => date("Y-m-d",strtotime($request->ddDate)),

                        'amount' => $request->amount,

                    ];



            $user->employeeSecurity()->create($data);



            return redirect('employees/create/securityDetailsTab')->with('securitySuccess',"Details saved successfully.");

        }

    }//end of function


    /*
        Save the details of security details tab of edit employee form
    */
    function editSecurityDetails(Request $request)

    {

        $validator = Validator::make($request->all(), [

            'bankName' => 'bail|required',

            'ddNo' => 'bail|required',

            'accNo' => 'bail|required',

            'receiptNo' => 'bail|required',

            'amount' => 'bail|required'

        ]);



        if($validator->fails()) {

            return redirect("/employees/edit/$request->employeeId/securityDetailsTab")

                        ->withErrors($validator,'security')

                        ->withInput();

        }



        $user_data = EmployeeSecurity::where(['user_id'=>$request->employeeId])

                                    ->with('user')

                                    ->first();



        if(!empty($user_data)){

            $log = Log::where(['name'=>'User-Updated'])->first();

            $log_data = [

                            'log_id' => $log->id,

                            'data' => $user_data->toJson()

                        ];



            $updated_by = Auth::user();

            $username = $updated_by->employee->fullname;

            $log_data['message'] = $log->name. " by ".$username."(".$updated_by->id.").";

            $user_data->logDetails()->create($log_data);

        }



        $user = User::find($request->employeeId);

        $data = [

                    'dd_number' => $request->ddNo,

                    'account_number'  => $request->accNo,

                    'bank_name'  => $request->bankName,

                    'receipt_number' => $request->receiptNo,

                    'dd_date' => date("Y-m-d",strtotime($request->ddDate)),

                    'amount' => $request->amount,

                ];



        EmployeeSecurity::updateOrCreate(['user_id'=>$user->id],$data);



        return redirect("/employees/edit/$request->employeeId/securityDetailsTab")->with('securitySuccess',"Details updated successfully.");



    }//end of function


    /*
        Ajax request to get departments wise employees
    */
    function departmentsWiseEmployees(Request $request)

    {

        $department_ids = $request->department_ids;



        $data = DB::table('employees as e')

                ->join('employee_profiles as ep','e.user_id','=','ep.user_id')

                ->join('users as u','e.user_id','=','u.id')

                ->whereIn('ep.department_id',$department_ids)

                ->where(['e.approval_status'=>'1','e.isactive'=>1,'ep.isactive'=>1])

                ->select('e.user_id','e.fullname','u.employee_code')

                ->get();



        return $data;



    }//end of function


    /*
        Ajax request to get states wise cities
    */
    function statesWiseCities(Request $request)

    {

        $state_ids = $request->stateIds;



        $cities = City::where(['isactive'=>1])

                        ->whereIn('state_id',$state_ids)

                        ->select('id','name')

                        ->get();



        return $cities;



    }//end of function


    /*
        Ajax request to get a specific project's information
    */
    function projectInformation(Request $request)

    {

        $data['project'] = Project::where(['id'=>$request->project_id,'isactive'=>1,'approval_status'=>'1'])->with('salaryStructure:id,name')

            ->with('salaryCycle:id,name')

            ->with('company:id,name,pf_account_number,tan_number')

            ->first();



        $state_ids = $data['project']->states()->pluck('state_id')->toArray();

        $states = State::whereIn('id',$state_ids)->pluck('name')->toArray();

        $data['states'] = implode(",",$states);



        $location_ids = $data['project']->locations()->pluck('location_id')->toArray();

        $locations = Location::whereIn('id',$location_ids)->pluck('name')->toArray();

        $data['locations'] = implode(",",$locations);



        return $data;

    }//end of function


    /*
        Ajax request to check whether the sent parameters are unique for an employee
    */
    function checkUniqueEmployee(Request $request)

    {

        $result = [

                     'referralMatch' => "no",

                     'emailUnique'   => "yes",

                     'mobileUnique'  => "yes",

                     'employeeXeamCodeUnique' => "yes",

                     'oldXeamCodeUnique' => "yes"

                  ];



        if(!empty($request->referralCode)){

            $employee = Employee::where(['referral_code' => $request->referralCode])->first();



            if(!empty($employee)){

                $result['referralMatch'] = "yes";

            }

        }else{

            $result['referralMatch'] = "blank";

        }



        if(!empty($request->email)){

            $employee = User::where(['email' => $request->email])->first();



            if(!empty($employee)){

                $result['emailUnique'] = "no";

            }

        }else{

            $result['emailUnique'] = "blank";

        }



        if(!empty($request->employeeXeamCode)){

            $employee = User::where(['employee_code' => $request->employeeXeamCode])->first();



            if(!empty($employee)){

                $result['employeeXeamCodeUnique'] = "no";

            }

        }else{

            $result['employeeXeamCodeUnique'] = "blank";

        }



        if(!empty($request->oldXeamCode)){

            $employee = Employee::where(['employee_id' => $request->oldXeamCode])->first();



            if(!empty($employee)){

                $result['oldXeamCodeUnique'] = "no";

            }

        }else{

            $result['oldXeamCodeUnique'] = "blank";

        }



        if(!empty($request->mobile)){

             $employee = Employee::where(['mobile_number' => $request->mobile])->first();



            if(!empty($employee)){

                $result['mobileUnique'] = "no";

            }

        }else{

            $result['mobileUnique'] = "blank";

        }



        return $result;



    }//end of function


    /*
        Get relevant information to show on my profile page
    */
    function myProfile()

    {

        $user = User::where(['id'=>Auth::id()])

                    ->with('employee')

                    ->with('employeeProfile')

                    ->with('roles:id,name')

                    ->with('languages')

                    ->with('skills')

                    ->with('qualifications')

                    ->with('permissions:id,name')

                    ->with('perks')

                    ->with('projects')

                    ->with('userManager.manager.employee:id,user_id,fullname')

                    ->with('employeeAddresses')

                    ->with('employeeAccount')

                    ->with('employeeReferences')

                    ->first();



        $leave_authorities = $user->leaveAuthorities()

                                  ->where(['isactive'=>1])

                                  ->with('manager.employee:id,user_id,fullname')

                                  ->orderBy('priority')

                                  ->get();



        $documents = DB::table('documents as d')

                    ->where(['d.document_category_id'=>1,'d.isactive'=>1])

                    ->select('d.id','d.name as document_name')

                    ->get();



        foreach ($documents as $key => $value) {

            $value->name = DB::table('document_user')

                           ->where(['document_id'=>$value->id,'user_id'=>$user->id])

                           ->value('name');

        }



        return view('employees.my_profile')->with(['user'=>$user,'leave_authorities'=>$leave_authorities,'documents'=>$documents]);



    }//end of function


    /*
        Get relevant information to show on other user's profile page
    */
    function otherUserProfile($user_id){

        $user = User::where(['id'=>$user_id])
                    ->with('employee')
                    ->with('employeeProfile')
                    ->with('roles:id,name')
                    ->with('languages')
                    ->with('skills')
                    ->with('qualifications')
                    ->with('permissions:id,name')
                    ->with('perks')
                    ->with('projects')
                    ->with('userManager.manager.employee:id,user_id,fullname')
                    ->with('employeeAddresses')
                    ->with('employeeAccount')
                    ->with('employeeReferences')
                    ->with('printDocument')
                    ->first();

        $leave_authorities = $user->leaveAuthorities()
                      ->where(['isactive'=>1])
                      ->with('manager.employee:id,user_id,fullname')
                      ->orderBy('priority')
                      ->get();

        $documents = DB::table('documents as d')
                    ->where(['d.document_category_id'=>1,'d.isactive'=>1])
                    ->select('d.id','d.name as document_name')
                    ->get();

        foreach($documents as $key => $value) {
                $value->name = DB::table('document_user')
                    ->where(['document_id'=>$value->id,'user_id'=>$user->id])
                    ->value('name');
        }

        session()->put('employeeId',$user_id);
        return view('employees.other_user_profile')->with(['user'=>$user,'leave_authorities'=>$leave_authorities,'documents'=>$documents]);

    }//end of function


    /*
        Upload your profile picture
    */
    function saveProfilePicture(Request $request)

    {

        if ($request->hasFile('profilePic')) {

            $profile_pic = time().'.'.$request->file('profilePic')->getClientOriginalExtension();

            $request->file('profilePic')->move(config('constants.uploadPaths.uploadPic'), $profile_pic);



            $user = Auth::user();

            $user->employee()->update(['profile_picture'=>$profile_pic]);



        }



        return redirect("employees/my-profile");



    }//end of function


    /*
        Get the change password form after login
    */
    function changePassword()

    {

        return view('employees.change_password_form');

    }//end of function


    /*
        Change your password after login
    */
    function saveNewPassword(Request $request)

    {

        $request->validate([

            'oldPassword' => 'bail|required|max:20|min:6',

            'newPassword'  => 'bail|required|max:20|min:6',

            'confirmPassword'  => 'bail|required|max:20|min:6|same:newPassword'

        ]);



        $user = Auth::user();

        $old_password = $user->password;



        if(Hash::check("$request->oldPassword", $old_password)) {

            $user->password = Hash::make($request->newPassword);

            $user->save();



            return redirect()->back()->with(['password_success'=>"Your password has been changed successfully."]);

        }else{

            return redirect()->back()->with(['password_error'=>"Please enter your old password correctly."]);

        }



    }//end of function


    /*
        List of employees whose probation has been approved or is pending from HR/HOD
    */
    function probationApprovals()

    {

        $probation_periods = ProbationPeriod::where(['isactive'=>1])->get();

        $user = User::where(['id'=>Auth::id()])

                      ->whereHas('employeeProfile')

                      ->with('employeeProfile')

                      ->first();



        if(!empty($user)){

            $user_id = $user->id;

        }else{

            $user_id = 0;

        }



        $leave_authorities = LeaveAuthority::where(['manager_id'=>$user_id])

                                            ->whereIn('priority',['2','3'])

                                            ->select('user_id','manager_id','priority')

                                            ->get();



        if(!$leave_authorities->isEmpty()){



            foreach ($leave_authorities as $key => $value) {

                $value->list = EmployeeProfile::where(['probation_approval_status'=>'0'])

                    ->where('user_id',$value->user_id)

                    ->with('probationPeriod')

                    ->with('user.employee')

                    ->get();



                if(!$value->list->isEmpty()){

                    foreach ($value->list as $key2 => $value2) {

                        $end_date = Carbon::parse($value2->user->employee->joining_date)->addDays($value2->probationPeriod->no_of_days)->toDateString();



                        if(strtotime(date("Y-m-d")) >= strtotime($end_date)){

                            $update_data =  [

                                                'probation_hod_approval' => '1',

                                                'probation_hr_approval' => '1',

                                                'probation_approval_status' => '1',

                                                'probation_end_date' => date("Y-m-d",strtotime($end_date))

                                            ];



                            $value2->update($update_data);

                            unset($value->list[$key2]);

                        }



                    }

                }

            }

        }



        return view('employees.list_probation_approvals')->with(['probation_periods'=>$probation_periods,'leave_authorities'=>$leave_authorities]);



    }//end of function


    /*
        Approve/Disapprove the probation of a specific employee
    */
    function probationApproval($action,$user_id,$priority)

    {

        if($action == 'approve'){

            if($priority == '2'){

                $data['probation_hod_approval'] = '1';

            }else{

                $data['probation_hr_approval'] = '1';

            }

        }elseif($action == 'disapprove') {
            if($priority == '2'){
                $data['probation_hod_approval'] = '0';
            }else{
                $data['probation_hr_approval'] = '0';
            }
        }
        $user = User::find($user_id);
        $user->employeeProfile()->update($data);
        $profile = $user->employeeProfile;
        if($profile->probation_hod_approval == '1' && $profile->probation_hr_approval == '1'){
            $profile->update(['probation_approval_status'=>'1']);
        }else{
            $profile->update(['probation_approval_status'=>'0']);
        }
        return redirect("employees/probation-approvals");
    }//end of function


    /*
        Change the probation period of a specific employee
    */
    function changeProbationPeriod(Request $request){

        $user = User::find($request->userId);
        $probation_period = ProbationPeriod::find($request->probationPeriodId);
        $end_date = Carbon::parse($user->employee->joining_date)->addDays($probation_period->no_of_days)->toDateString();
        $prev_probation_end_date_time = strtotime($user->employeeProfile->probation_end_date);
        $end_date_time = strtotime($end_date);
        $profile_data['probation_period_id'] = $request->probationPeriodId;
        if($end_date_time > $prev_probation_end_date_time){
            $profile_data['probation_extended_date'] = $end_date;
            $profile_data['probation_reduced_date'] = null;
            $message = "Your probation period has been extended till ".date("d/m/Y",strtotime($end_date));

        }elseif($end_date_time < $prev_probation_end_date_time){
            $profile_data['probation_reduced_date'] = $end_date;
            $profile_data['probation_extended_date'] = null;
            $message = "Your probation period has been reduced till ".date("d/m/Y",strtotime($end_date));
        }else{
            $profile_data['probation_reduced_date'] = null;
            $profile_data['probation_extended_date'] = null;
            $message = "";
        }

        $user->employeeProfile()->update($profile_data);
        if(!empty($message)){
            $mail_data['to_email'] = $user->email;
            $mail_data['fullname'] = $user->employee->fullname;
            $mail_data['subject'] = "Probation Period Changed";
            $mail_data['message'] = $message;
            $this->sendGeneralMail($mail_data);
        }
        return redirect("employees/probation-approvals");
    }//end of function


    function sendGeneralMail($mail_data)
    {   //mail_data Keys => to_email, subject, fullname, message

        if(!empty($mail_data['to_email'])){
            Mail::to($mail_data['to_email'])->send(new GeneralMail($mail_data));
        }
        return true;
    }//end of function

    /*
        Get all messages received by an employee
    */
    function allMessages(){

        $user_id = Auth::id();
        $messages = Message::where(['isactive'=>1,'receiver_id'=>$user_id])
                                     ->with(['sender.employee:id,user_id,fullname'])
                                     ->orderBy('created_at','DESC')
                                     ->paginate(15);
        return view('employees.all_messages')->with(['messages'=>$messages]);

    }//end of function


    /*
        Get all notifications received by an employee
    */
    function allNotifications(){

        $user_id = Auth::id();
        $notifications = Notification::where(['isactive'=>1,'receiver_id'=>$user_id])
                                     ->with(['sender.employee:id,user_id,fullname'])
                                     ->orderBy('created_at','DESC')
                                     ->paginate(15);
        return view('employees.all_notifications')->with(['notifications'=>$notifications]);

    }//end of function

    /*
        Ajax request to mark the messages as read
    */
    function unreadMessages(Request $request){

        $message_ids = $request->message_ids;
        Message::whereIn('id',$message_ids)->update(['read_status'=>'1']);
        $result['status'] = true;
        return $result;

    }//end of function

    /*
        Ajax request to mark the notifications as read
    */
    function unreadNotifications(Request $request){

        $notification_ids = $request->notification_ids;
        Notification::whereIn('id',$notification_ids)->update(['read_status'=>'1']);
        $result['status'] = true;
        return $result;

    }//end of function


    // new print-offer-letter
    function printOfferLetter(Request $request){

        $data = [
            'offer_letter'  => $request->count+1,
            'user_id'       => $request->employeeId,
        ];
        PrintDocument::updateOrCreate(['user_id'=>$request->employeeId],$data);
    }


    function viewOfferLetter(){

        $employeeId = session('employeeId');
        $user = User::where(['id'=>$employeeId])
                    ->with('employee')
                    ->with('employeeProfile')
                    ->with('employeeAddresses')
                    ->with('roles:id,name')
                    ->with('printDocument')
                    ->first();
        return view('employees.offer_letter')->with(['user'=>$user]);
    }

    function getMissedPunchToday(Request $request){

        $todays_date = date("Y-m-d");
        $data['attendances_info'] = DB::table("employees")->select('id', 'user_id', 'fullname', 'mobile_number')->whereNotIn('user_id',function($query)  {
                            $query->select('user_id')->where('on_date', date("Y-m-d"))->from('attendances');

                            })
                        ->where('isactive', 1)
                        ->get();
        $employee_arr=[];

        foreach($data['attendances_info'] as $attendance_info){

            $dep = EmployeeProfile::where(['user_id' => $attendance_info->user_id])
                                    ->with('department')
                                    ->first();

           $designation = User::where(['id' => $attendance_info->user_id])
                            ->with('designation')
                            ->first();

            $employee_arr[]=[

                "attendance_info"=>$attendance_info,
                "dep"=>$dep,
                "designation" =>$designation
            ];

        }

        return view('employees.missed_punch')->with(['data'=>$employee_arr, 'punch_date'=>$todays_date]);

    }

    function getMissedPunchData(Request $request){

        $punch_date = $request->miss_punch_date;

        $data['attendances_info'] = DB::table("employees")->select('id', 'user_id', 'fullname', 'mobile_number')->whereNotIn('user_id',function($query) use($punch_date) {
        $query->select('user_id')->where('on_date', $punch_date)->from('attendances');
        })

        ->where('isactive', 1)
        ->get();

        $employee_arr=[];
        foreach($data['attendances_info'] as $attendance_info){

        $dep = EmployeeProfile::where(['user_id' => $attendance_info->user_id])
                                ->with('department')
                                ->first();

        $designation = User::where(['id' => $attendance_info->user_id])
                            ->with('designation')
                            ->first();

        $employee_arr[]=[

                "attendance_info"=>$attendance_info,
                "dep"=>$dep,
                "designation" =>$designation
            ];

        }

        return view('employees.missed_punch')->with(['data'=>$employee_arr, 'punch_date'=>$punch_date]);

    }
	function saveuserdata_bkp(Request $request){

		 $users_data = DB::table('table_saveuser')->get();
		 /* echo"<PRE>";
		 print_r($users_data);  */
		 $i=1000;
		 $j=100000;

		 foreach($users_data as $user){
			$SNo = $user->SNo;
			$State = $user->State;
			$Region = $user->Region;


			$Designation = $user->Designation;

			$Email = $user->Email;
			$emp_code = $i;
			$password = $j;

			$Prefix = $user->Prefix;
			$Name = $user->Name;
			$state_data = DB::table('states')->where('name', $State)->get();

			$user_data = [

                        'email' => $Email,

                        'employee_code'  => $emp_code,

                        'password' => Hash::make($password),

                     ];

			$user = User::create($user_data);
			echo "   Name: ".$Name;
			echo "     Employee code: ".$emp_code;
			echo "   ";
			echo "    Password: ".$j;
			echo"<br/>";

			if($user){
				$user_id = $user->id;
			}

			$name_arr = explode(" ",$Name);

			$firstname = $name_arr[0];
			if( sizeof($name_arr)>1){
				$lastname = $name_arr[1];
			}


			 $employee_data = [

                            'user_id' => $user_id,

                            'creator_id' => Auth::id(),

                            'employee_id' => $emp_code,

                            'salutation' => $Prefix,

                            'fullname' => $Name,

                            'first_name' => $firstname,

                            'middle_name' => "",

                            'last_name' => $lastname,

                            'personal_email' => "",

                            'attendance_type' => "",

                            'mobile_number' => "0987654321",

                            'country_id' => 1,

                            'alternative_mobile_number' =>"",

                            'alt_country_id' => 1,

                            'experience_year_month' => "",

                            'experience_status' => "",

                            'marital_status' => "",

                            'gender' => "",

                            'approval_status' => '0',

                            'father_name' => "",

                            'mother_name' => "",

                            'spouse_name' => "",

                            'birth_date'  => "",

                            'joining_date' => "",

                            'nominee_name'  => "",

                            'relation'  => "",

                            'nominee_type' => "",

                            'registration_fees'=> "",

                            'application_number' => "",

                            'spouse_working_status' => 'No',

                            'spouse_company_name' => '',

                            'spouse_designation' => '0',

                            'spouse_contact_number' => '',

                        ];
			$employee = Employee::create($employee_data);
			 $state_data = DB::table('states')->where('name', $State)->first();
			 if($state_data){
				 $state_id = $state_data->id;
			 }else{
				  $state_id = "";
			 }
			if($employee){
				$userid = $employee->user_id;
			}
			$employee_profile_data =   [

                                   'user_id'  => $userid,

								   'shift_id'  => 1,

                                   'department_id' => 6,

                                   "probation_period_id" => 1,

                                   'state_id' => $state_id,

                                   'probation_approval_status' => '0',

                                   'probation_hod_approval' => '0',

                                   'probation_hr_approval' => '0'

                                ];
			$employee_profile = EmployeeProfile::create($employee_profile_data);

			 $location_data = DB::table('locations')->where('name', $Region)->first();
			 if($location_data){
				 $location_id = $location_data->id;
			 }else{

				 $region_data =   [

                                   'name' => $Region,

                                   "state_id" => $state_id,

								   "has_esi" => 1,

								   "isactive" => 1,
									];
				 $location_data = DB::table('locations')->insert($region_data);
				 $inserted_location_data = DB::table('locations')->where('name', $Region)->first();
				 $location_id = $inserted_location_data->id;
			 }

			$employee_location_data =   [

                                   'user_id'  => $userid,

                                   'location_id' => $location_id,

                                   "isactive" => 1
									];
			 //$loc_profile = LocationUser::create($employee_location_data);
			 $loc_profile = DB::table('location_user')->insert($employee_location_data);

			 /*  if(!empty($location_id)){

                $locations = [];

                array_push($locations,$location_id);

                $user->locations()->sync($locations);

            } */

			$employee_designation_data =   [

                                   'user_id'  => $userid,

                                   'designation_id' => 3,

								   'remarks' => "",

                                   "isactive" => 1
									];
			 //$loc_profile = LocationUser::create($employee_location_data);
			 $designation_data = DB::table('designation_user')->insert($employee_designation_data);

			/* if(!empty($Designation)){

                $designations = [];

                array_push($designations,3);

                $user->designation()->sync($designations);


            } */

			$i++;
			$j++;
		 }


	}
		function saveuserdata_final(Request $request){

		 $users_data = DB::table('table_po_it')->get();
/* echo"<PRE>";
print_r($users_data);
exit;
 */
		 $i=2070;
		 $j=100036;
		  $c=0;
		  $k=0;
		 foreach($users_data as $user){
			//$SNo = $user->SNo;
			$State = $user->State;
			$Region = $user->Region;
			$phone = $user->phone;


			$Designation = $user->Designation;

			//$Email = $user->Email;
			$Email = "dummy_po_it@gmail.com";
			$emp_code = $i;
			$password = 123456;

			//$Prefix = $user->Prefix;
			$Prefix = "Mr.";
			//$prefix = 	array("Ms.", "Mr.", "Mr.", "Mr.","Mr.", "Mr.", "Ms.", "Mr.", "Ms.");
			$Name = $user->Name;
			$state_data = DB::table('states')->where('name', $State)->get();

			$user_data = [

                        'email' => $Email,

                        'employee_code'  => $emp_code,

                        'password' => Hash::make($password),

                     ];

			$user = User::create($user_data);
			echo "   Name: ".$Name;
			echo "     Employee code: ".$emp_code;
			echo "   ";
			echo "    Password: ".$password;
			echo"<br/>";

			if($user){
				$user_id = $user->id;
			}

			$name_arr = explode(" ",$Name);

			$firstname = $name_arr[0];
			if( sizeof($name_arr)>1){
				$lastname = $name_arr[1];
			}


			 $employee_data = [

                            'user_id' => $user_id,

                            'creator_id' => Auth::id(),

                            'employee_id' => $emp_code,

                            'salutation' => $Prefix,

                            'fullname' => $Name,

                            'first_name' => $firstname,

                            'middle_name' => "",

                            'last_name' => $lastname,

                            'personal_email' => "",

                            'attendance_type' => "",

                            'mobile_number' => $phone,

                            'country_id' => 1,

                            'alternative_mobile_number' =>"",

                            'alt_country_id' => 1,

                            'experience_year_month' => "",

                            'experience_status' => "",

                            'marital_status' => "",

                            'gender' => "",

                            'approval_status' => '0',

                            'father_name' => "",

                            'mother_name' => "",

                            'spouse_name' => "",

                            'birth_date'  => "",

                            'joining_date' => "",

                            'nominee_name'  => "",

                            'relation'  => "",

                            'nominee_type' => "",

                            'registration_fees'=> "",

                            'application_number' => "",

                            'spouse_working_status' => 'No',

                            'spouse_company_name' => '',

                            'spouse_designation' => '0',

                            'spouse_contact_number' => '',

                        ];
			$employee = Employee::create($employee_data);
			 $state_data = DB::table('states')->where('name', $State)->first();
			 if($state_data){
				 $state_id = $state_data->id;
			 }else{

				  $states_data =   [

                                   'country_id' => 1,

                                   "name" => $State,

								   "has_pt" => 1,

								   "isactive" => 1,
									];
				 $state_data = DB::table('states')->insert($states_data);
				 $inserted_state_data = DB::table('states')->where('name', $State)->first();
				 $state_id = $inserted_state_data->id;

			 }
			if($employee){
				$userid = $employee->user_id;
			}
			$employee_profile_data =   [

                                   'user_id'  => $userid,

								   'shift_id'  => 1,

                                   'department_id' => 6,

                                   "probation_period_id" => 1,

                                   'state_id' => $state_id,

                                   'probation_approval_status' => '0',

                                   'probation_hod_approval' => '0',

                                   'probation_hr_approval' => '0'

                                ];
			$employee_profile = EmployeeProfile::create($employee_profile_data);

			$location_data = DB::table('locations')->where('name', $Region)->first();
			 if($location_data){
				 $location_id = $location_data->id;
			 }else{

				 $region_data =   [

                                   'name' => $Region,

                                   "state_id" => $state_id,

								   "has_esi" => 1,

								   "isactive" => 1,
									];
				 $location_data = DB::table('locations')->insert($region_data);
				 $inserted_location_data = DB::table('locations')->where('name', $Region)->first();
				 $location_id = $inserted_location_data->id;
			 }

			$employee_location_data =   [

                                   'user_id'  => $userid,

                                   'location_id' => $location_id,

                                   "isactive" => 1
									];
			 //$loc_profile = LocationUser::create($employee_location_data);
			 $loc_profile = DB::table('location_user')->insert($employee_location_data);




			$employee_designation_data =   [

                                   'user_id'  => $userid,

                                   'designation_id' => 5,

								   'remarks' => "",

                                   "isactive" => 1
									];
			 //$loc_profile = LocationUser::create($employee_location_data);
			 $designation_data = DB::table('designation_user')->insert($employee_designation_data);

			/* if(!empty($Designation)){

                $designations = [];

                array_push($designations,3);

                $user->designation()->sync($designations);


            } */

			$i++;
			$j++;
		 }


	}
	function saveuserdata_chnagePassword(Request $request){
		$pass = 123456;
		$password = Hash::make($pass);
		for($i=1000; $i<2000; $i++){




								 $user_update = DB::table('users')

                                ->where(['employee_code'=>$i])

                                ->update([
										'password'=> $password
										]);
if($user_update){
	echo "updated";
}


		}

	}
	function saveuserdata_insert_project(Request $request){
		for($i=6;$i<=52;$i++){
					$employee_project_data =   [

                                   'project_id'  => 4,

                                   'user_id' => $i,

								   'isactive' => 1,

                                   "isactive" => 1
									];

					$project_data = DB::table('project_user')->insert($employee_project_data);

		}

	}
	function saveuserdata(Request $request){
		//echo"text";

		  $Employee_data = Employee::get();

		foreach($Employee_data as $Emp){
			$id = $Emp->user_id;
			if($id==1 || $id==2 || $id==3 || $id==4 || $id==34 || $id==31 || $id==292){


			} else{
				$employee_data =   [
									'approvalable_id' => $id,
									'approvalable_type' => "App\User",
									'approver_id'=>1
									];

				$employee_data_inserted = DB::table('approvals')->insert($employee_data);
				if($employee_data_inserted){
					echo $id."updated";
					echo"<BR/>";
				}
			}

		}


	}


}//end of class

