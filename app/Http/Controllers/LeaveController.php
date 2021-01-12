<?php

namespace App\Http\Controllers;

use App\AppliedLeaveSegregation;
use App\AttendanceVerification;
use App\Exports\LeavePoolExport;
use App\Imports\LeaveDetailImport;
use App\Imports\SalarySheetImport;
use Illuminate\Http\Request;
use DB;
use App\Holiday;
use App\LeaveType;
use App\CompensatoryLeave;
use App\Department;
use App\Project;
use App\Country;
use App\State;
use App\User;
use App\Employee;
use App\LeaveDetail;
use App\EmployeeProfile;
use App\AppliedLeave;
use App\AppliedLeaveApproval;
use Carbon\Carbon;
use Auth;
use Maatwebsite\Excel\Excel;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use App\Mail\GeneralMail;
use App\AppliedLeaveDocument;
use App\Attendance;

ini_set('max_execution_time', 180); //3 minutes

class LeaveController extends Controller
{
    /*
        Get current year's holidays list
    */
    function listHolidays()
    {
        $year = date("Y");
        $holidays = Holiday::where(['isactive'=>1])
            ->whereYear('holiday_from',$year)
            ->orderBy('holiday_from')
            ->get();

        return view('leaves.list_holidays')->with(['holidays'=>$holidays]);
    }//end of function

    /*
        Get data to be shown on apply leave form
    */
    function applyLeave()
    {
        $user = Auth::user();

        $user_filled = Employee::where(['user_id'=>$user->id])->first();

        $profile_filled =  $user_filled->is_complete;

        if($profile_filled==0 AND $user->id!=1){

            return redirect('profile-detail-form');
        }
        $user = User::where(['id'=>Auth::id()])->first();
        $data['gender'] = $user->employee->gender;

        if($data['gender'] == 'Male'){

            $data['leave_types'] = LeaveType::where(['isactive'=>1])->where('id','!=',4)->get();
        }elseif($data['gender'] == 'Female'){
            $data['leave_types'] = LeaveType::where(['isactive'=>1])->where('id','!=',7)->get();
        }else{
            $data['leave_types'] = LeaveType::where(['isactive'=>1])->get();
        }

        $data['departments'] = Department::where(['isactive'=>1])->get();
        $data['countries'] = Country::where(['isactive'=>1])->get();
        $data['states'] = State::where(['isactive'=>1])->get();
        $data['user'] = $user;
        $data['probation_data'] = probationCalculations($user);
        /*echo"<PRE>";
       print_r($data['probation_data']);
       exit;*/
        $data['unpaid_leave'] = LeaveType::where(['name'=>'Unpaid Leave'])->first();

        $data['leave_detail'] = LeaveDetail::where(['user_id'=>Auth::id()])->orderBy('id','DESC')->first();

        /* if(empty($data['probation_data'])){
             return redirect()->back()->with('error','Your profile is incomplete. Please contact the HR officer.');
         }*/

        $designation_login_data = DB::table('designation_user as du')

            ->where('du.user_id','=', Auth::id())

            ->select('du.id', 'du.user_id','du.designation_id')->first();

        $data['user_designation'] = $designation_login_data->designation_id;

        $current_date = date('Y-m-d');
        $curr_year =  date('Y');
        $curr_month = date('m');

        if($curr_month==1){
            $start_year= $curr_year-1;
            $startmonth = 12;
        }else{
            $start_year= $curr_year;
            $startmonth = $curr_month-1;
        }

        $from_date = $start_year.'-'.$startmonth.'-'.'26';
        $to_date = $curr_year.'-'.$curr_month.'-'.'25';

        $data['taken_monthLeave'] = $this->calculateMonthLeave($from_date,$to_date);

        $leaveDetail = LeaveDetail::where('user_id', Auth::user()->id)->whereYear('month_info', '2020')
            ->whereMonth
            ('month_info', date('m'))->first();

        return view('leaves.apply_leave_form', compact('leaveDetail'))->with(['data'=>$data]);

    }//end of function

    /*
        Ajax request to get the leave replacements available between given dates
    */
    function leaveReplacementAvailability(Request $request)
    {
        $user = Auth::user();

        $from_date = date("Y-m-d",strtotime($request->from_date));
        $to_date = date("Y-m-d",strtotime($request->to_date));

        $profile_pic_path = config('constants.uploadPaths.profilePic');
        $static_pic_path = config('constants.static.profilePic');

        $employees = EmployeeProfile::where(['department_id'=>$request->department,'isactive'=>1])
            ->where('user_id','!=',$user->id)
            ->whereHas('user.employee',function(Builder $query){
                $query->where('isactive',1);
            })
            ->where('user_id','!=',1)
            ->pluck('user_id')->toArray();

        $took_leaves = AppliedLeave::where('isactive',1)
            ->where('from_date','>=',$from_date)
            ->where('to_date','<=',$to_date)
            ->whereIn('user_id',$employees)
            ->whereHas('appliedLeaveApprovals',function(Builder $query){
                $query->where('leave_status','!=','2');
            })
            ->pluck('user_id')->toArray();

        $exclusions = DB::table('employees as emp')
            ->join('users as u','u.id','=','emp.user_id')
            ->whereIn('emp.user_id',$employees)
            ->whereNotIn('emp.user_id',$took_leaves)
            ->select("emp.user_id","u.employee_code","emp.fullname",DB::raw("CASE WHEN emp.profile_picture = '' OR emp.profile_picture IS NULL THEN '".$static_pic_path."' ELSE CONCAT('".$profile_pic_path."',emp.profile_picture) END AS profile_picture"))
            ->get();

        return $exclusions;

    }//end of function

    /*
        Ajax request to get the official holidays between given dates to be used on apply
        leave page
    */
    function betweenLeaveHolidays(Request $request)
    {
        $result = [];

        if(!empty($request->all_dates_array)){
            foreach ($request->all_dates_array as $key => $value) {
                $date = date("l",strtotime($value));

                if($date != 'Sunday'){
                    $holiday = Holiday::where(['isactive'=>1])
                        ->where('holiday_from','<=',$value)
                        ->where('holiday_to','>=',$value)
                        ->first();

                    if(!empty($holiday)){
                        $result[] = $value;
                    }
                }

            }
        }

        return $result;

    }//end of function


    function calculateMonthLeave($from,$to)
    {

        // dump($from);
        // dump($to);

        $user = Auth::user();
        $userid = $user->id;

        //$from_day = date("Y-m-d",strtotime($request->fromDate));
        $current_date = date('Y-m-d');
        $curr_year =  date('Y');
        $curr_month = date('m');

        if($curr_month==1){
            $start_year= $curr_year-1;
            $startmonth = 12;
        }else{
            $start_year= $curr_year;
            $startmonth = $curr_month-1;
        }

        $date1 = $start_year.'-'.$startmonth.'-'.'26';
        $date2 = $curr_year.'-'.$curr_month.'-'.'25';

        if(strtotime($from)>=strtotime($date1) AND strtotime($to)<=strtotime($date2)){

            $date1 = $start_year.'-'.$startmonth.'-'.'26';
            $date2 = $curr_year.'-'.$curr_month.'-'.'25';


        }elseif(strtotime($from)>=strtotime($date2) OR strtotime($to)>=strtotime($date2)){


            if($curr_month==12){
                $next_year= $curr_year+1;
                $next_month = 1;
            }else{
                $next_year= $curr_year;
                $next_month = $curr_month+1;
            }

            $next_month = $curr_month+1;
            $date1 = $curr_year.'-'.$curr_month.'-'.'26';
            $date2 = $next_year.'-'.$next_month.'-'.'25';

        }elseif(strtotime($to)<=strtotime($date1) AND strtotime($from)<=strtotime($date1)){


            if($curr_month==1){
                $start_year= $curr_year-1;
                $startmonth = 11;
            }else{
                $start_year= $curr_year;
                $startmonth = $curr_month-2;
            }

            $date1 = $start_year.'-'.$startmonth.'-'.'26';
            $date2 = $curr_year.'-'.$curr_month.'-'.'25';


        }

        $current_month_leave = DB::table('applied_leave_segregations as als')
            ->join('applied_leaves as al','al.id','=','als.applied_leave_id')
            ->where(['al.final_status'=>'1','al.user_id'=>$userid])
            ->where('leave_type_id','!=',5)
            ->where(function($query)use($date1, $date2){
                $query->where('als.from_date','>=',$date1)
                    ->where('als.to_date','<=',$date2);
            })
            ->sum('als.number_of_days');
        //dump($current_month_leave);
        return $current_month_leave;

    }


    /*
        Store leave application's data in the database & send notification to the replacement
        & first leave officer
    */
    function createLeaveApplication(Request $request)
    {

        // Validate

        $request->validate([
            'toDate' => "required_if:secondaryLeaveType,==,Full",
            'fromDate' => 'required',
            'reasonLeave' => 'required',
            'secondaryLeaveType' => 'required'
        ]);

        //////////////////////////Checks///////////////////////////
        $current_date = date('Y-m-d');
        $restriction_date = config('constants.restriction.applyLeave');
        $current_month_start_date = date("Y-m-01");



        if($request->noDays == 0){
            $days_error = "The number of days should not be zero.";
            return redirect('leaves/apply-leave')->with('leaveError',$days_error);
        }

        $user = Auth::user();
        $userid = $user->id;

        $user = User::where(['id'=>$userid])
            ->with('employee')
            ->with('userManager')
            ->first();

        $arr_underlaying_emp = array();

        // Chk User in designation_user Table Logged User Id Exists..

        $designation_login_data = DB::table('designation_user as du')
            ->where('du.user_id','=',$userid)
            ->select('du.id', 'du.user_id','du.designation_id')->first();
        $designation_login_user = $designation_login_data->designation_id;

        // Check for PO reporting manager exist

        if($designation_login_user==3 ){

            $state_login_user = EmployeeProfile::where(['user_id' => $designation_login_data->user_id])->first();

            $login_user_state_id = $state_login_user->state_id;

            $employees_under_states = EmployeeProfile::where(['state_id' => $login_user_state_id])
                ->get();

            foreach($employees_under_states as $employees_state){

                $employeeId_under_state = $employees_state->user_id;

                $empDesg_under_state = DB::table('designation_user as du')
                    ->where(['du.user_id'=>$employeeId_under_state, 'du.designation_id'=>2])
                    ->select('du.id', 'du.user_id','du.designation_id')->first();

                if($empDesg_under_state!=""){
                    $arr_underlaying_emp[] = $empDesg_under_state;
                }
            }
        }

        //Check for PO-IT reporting manager exist

        if($designation_login_user==5 ){

            $state_login_user = EmployeeProfile::where(['user_id' => $designation_login_data->user_id])->first();

            $login_user_state_id = $state_login_user->state_id;

            $employees_under_states = EmployeeProfile::where(['state_id' => $login_user_state_id])->get();

            foreach($employees_under_states as $employees_state){

                $employeeId_under_state = $employees_state->user_id;

                $empDesg_under_state = DB::table('designation_user as du')
                    ->where(['du.user_id'=>$employeeId_under_state, 'du.designation_id'=>2])
                    ->select('du.id', 'du.user_id','du.designation_id')->first();

                if($empDesg_under_state!=""){
                    $arr_underlaying_emp[] = $empDesg_under_state;
                }
            }
        }

        //Check Vccm Reporting Manager Exists..

        if($designation_login_user==4){

            // Chk Location id in location_user table by designation_user table user_id match

            $district_login_user = DB::table('location_user as lu')
                ->where('lu.user_id','=',$designation_login_data->user_id)
                ->select('lu.id', 'lu.user_id','lu.location_id')->first();

            $login_user_district_id = $district_login_user->location_id;

            // Check How Many Users In That Location  $login_user_district_id

            $employeesDistrict = DB::table('location_user as lu')
                ->where(['lu.location_id'=>$login_user_district_id])
                ->select('lu.id', 'lu.user_id','lu.location_id')->get();
            //dd($employeesDistrict);

            if($employeesDistrict)
            {
                // Chk Reporting manager Where designton_id 3,5(PO)

                foreach($employeesDistrict as $empDistrict){

                    $user_id = $empDistrict->user_id;
                    $empDesg_under_district = DB::table('designation_user as du')
                        ->where(['du.user_id'=>$user_id])
                        ->where(function($query){
                            $query->where('du.designation_id','=',5)
                                ->orWhere('du.designation_id','=', 3);
                        })
                        ->select('du.id', 'du.user_id','du.designation_id')->first();

                    if($empDesg_under_district!="")
                    {
                        $arr_underlaying_emp[] = $empDesg_under_district;
                    }
                }
            }
        }

        // dd("+++++++++++");
        //check for spo reporting manager exist
        if($designation_login_user==2){


            $empDesg_NPA = DB::table('designation_user as du')

                ->where('du.designation_id','=',1)

                ->select('du.id', 'du.user_id','du.designation_id')->first();

            if($empDesg_NPA!=""){
                $arr_underlaying_emp[] = $empDesg_NPA;
            }
        }

        if(isset($arr_underlaying_emp) AND !empty($arr_underlaying_emp)){
        }else{
            $manager_error = "You do not have reporting manger under Your area.";
            return redirect('leaves/apply-leave')->with('leaveError',$manager_error);
        }

        $pending_leave = AppliedLeaveApproval::where(['user_id'=>$userid,'leave_status'=>'0'])
            ->whereHas('appliedLeave',function(Builder $query){
                $query->where('isactive',1);
            })
            ->first();

        if(!empty($pending_leave)){
            $pending_error = "The approval status of your previously applied leave is pending with one or more authorities. Please contact the concerned person and clear it first.";
            return redirect('leaves/apply-leave')->with('leaveError',$pending_error);
        }

        $last_applied_leave = $user->appliedLeaves()
            ->where(['isactive'=>1])
            ->orderBy('id','DESC')
            ->first();

        if(!empty($last_applied_leave)){
            $created_at = new Carbon($last_applied_leave->created_at);
            $now = Carbon::now();
            $apply_difference = $created_at->diffInHours($now,false);

            if($apply_difference < 2){
                $leave_time_difference = true;
            }else{
                $leave_time_difference = false;
            }
        }

        $from_date = date("Y-m-d",strtotime($request->fromDate));

        if($request->secondaryLeaveType != "Full"){
            $to_date = $from_date;
        }else{
            $to_date = date("Y-m-d",strtotime($request->toDate));
        }


        $check_dates =  [
            'from_date' => $from_date,
            'to_date' => $to_date,
            'isactive' => 1
        ];


        // $already_applied_leave = $user->appliedLeaves()->where($check_dates)->first();

        // if(!empty($already_applied_leave)){
        //     $unique_error = "You have already applied for leave on the given dates.";
        //     return redirect('leaves/apply-leave')->with('leaveError',$unique_error);
        // }

        if(!empty($last_applied_leave)){
            $chk_existing_date = DB::table('applied_leaves')
                ->where('from_date', '<=', $from_date)->where('to_date', '>=', $from_date)
                ->where('user_id', $last_applied_leave->user_id)->where('final_status', '1')
                ->where('isactive', 1)->first();

            if(!empty($chk_existing_date)){
                $unique_error = "You have already applied for leave on given date.";
                return redirect('leaves/apply-leave')->with('leaveError',$unique_error);
            }
        }


        if($request->leaveTypeId == '4'){  //check for maternity leave
            if($request->noDays != 180){
                $maternity_error = "You can take maternity leave for 180 days only.";
            }else{
                $already_applied_leave = $user->appliedLeaves()
                    ->where(['final_status'=>'1','leave_type_id'=>4,'isactive'=>1])
                    ->whereYear('updated_at',date("Y"))
                    ->first();

                if(!empty($already_applied_leave)){
                    $maternity_error = "You have already applied for a maternity leave this year.";

                }else{
                    $maternity_error = "";
                }
            }

            if(!empty($maternity_error)){
                return redirect('leaves/apply-leave')->with('leaveError',$maternity_error);
            }
        }

        // chk for paternity leave not more than 15  days in a month.

        if($request->leaveTypeId == '7'){
            if($request->noDays != 15){
                $paternity_error = "You cannot take Paternity leave for less than or more than 15 days.";
            }else{
                $already_applied_leave = $user->appliedLeaves()
                    ->where(['final_status'=>'1','leave_type_id'=>7,'isactive'=>1])
                    ->whereYear('updated_at',date("Y"))
                    ->first();

                if(!empty($already_applied_leave)){
                    $paternity_error = "You have already applied for a paternity leave this year.";

                }else{
                    $paternity_error = "";
                }
            }

            if(!empty($paternity_error)){
                return redirect('leaves/apply-leave')->with('leaveError',$paternity_error);
            }
        }

        /////////////////////////Create Leave///////////////////////

        $leave_data = [
            'leave_type_id' => $request->leaveTypeId,
            'reason' => $request->reasonLeave,
            'number_of_days' => $request->noDays,
            "secondary_leave_type" => $request->secondaryLeaveType,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'excluded_dates' => $request->excludedDates,
            'final_status' => '0'
        ];

        $applied_leave = $user->appliedLeaves()->create($leave_data);

        if(!empty($request->fileNames)){
            $documents = $request->fileNames;
            foreach($documents as $doc) {
                $document = round(microtime(true)).str_random(5).'.'.$doc->getClientOriginalExtension();
                $doc->move(config('constants.uploadPaths.uploadAppliedLeaveDocument'), $document);

                $document_data['name'] = $document;
                $applied_leave->appliedLeaveDocuments()->create($document_data);
            }
        }

        //////////////////////////Segregation///////////////////////////
        if(!empty($request->newAllDatesArray)){   //for multi-month leave calculations in full day leaves
            if(date("d",strtotime($request->toDate)) > 25 || date("m",strtotime($request->toDate)) > date("m",strtotime($request->fromDate)))
            {
                $date = explode('/',$request->fromDate);
                $year = $date[2];
                $month = $date[0];
                $firstEndDate = $year.'-'.$month.'-25';
                $firstSegregation = $request->fromDate.' - '. $firstEndDate;
                $fromDate = date("Y-m-d", strtotime($request->fromDate));
                $firstEndDate = date("Y-m-d", strtotime($firstEndDate));
                $date1 = Carbon::createFromDate($fromDate);
                $date2 = Carbon::createFromDate($firstEndDate);

                $numberOfDays = $date2->diffInDays($date1) + 1;

                $excludingDates = explode(',', $request->excludedDates);
                foreach ($excludingDates as $excludingDate) {
                    $exist = Carbon::createFromDate($excludingDate)->between($date1, $date2);
                    if($exist == 1){
                        $numberOfDays = $numberOfDays - 1;
                    }
                }

                $segregation_data = [
                    'from_date' => date("Y-m-d", strtotime($request->fromDate)),
                    'to_date' => date("Y-m-d", strtotime($firstEndDate)),
                    'number_of_days' => $numberOfDays,
                    'paid_count' => '0',
                    'unpaid_count' => '0',
                    'compensatory_count' => '0'
                ];

                $applied_leave->appliedLeaveSegregations()->create($segregation_data);

                $secondFromDate = $year.'-'.$month.'-26';
                $secondSegregation = $secondFromDate.' - '. $request->toDate;
                $date3 = Carbon::createFromDate($secondFromDate);
                $date4 = Carbon::createFromDate($request->toDate);

                $numberOfDays = $date4->diffInDays($date3) + 1;
                $excludingDates = explode(',', $request->excludedDates);
                foreach ($excludingDates as $excludingDate) {
                    $exist = Carbon::createFromDate($excludingDate)->between($date3, $date4);
                    if($exist == 1){
                        $numberOfDays = $numberOfDays - 1;
                    }
                }

                $segregation_data = [
                    'from_date' => date("Y-m-d", strtotime($secondFromDate)),
                    'to_date' => date("Y-m-d", strtotime($request->toDate)),
                    'number_of_days' => $numberOfDays,
                    'paid_count' => '0',
                    'unpaid_count' => '0',
                    'compensatory_count' => '0'
                ];
                $applied_leave->appliedLeaveSegregations()->create($segregation_data);

            }
            else {
                $segregation_data = [
                    'from_date' => date("Y-m-d", strtotime($request->fromDate)),
                    'to_date' => date("Y-m-d", strtotime($request->toDate)),
                    'number_of_days' => $request->noDays,
                    'paid_count' => '0',
                    'unpaid_count' => '0',
                    'compensatory_count' => '0'
                ];
                $applied_leave->appliedLeaveSegregations()->create($segregation_data);
            }
        }else{  //For Short and Half Day leave

            $segregation_data =  [
                'from_date' => date("Y-m-d",strtotime($request->fromDate)),
                'to_date' => date("Y-m-d",strtotime($to_date)),
                'number_of_days' => $request->noDays,
                'paid_count' => '0',
                'unpaid_count' => '0',
                'compensatory_count' => '0'
            ];
            $applied_leave->appliedLeaveSegregations()->create($segregation_data);
        }


        //////////////////////////Approval///////////////////////////

        $reporting_manager = $arr_underlaying_emp[0]->user_id;
        $approval_data = [
            'user_id' => $userid,
            'supervisor_id' => $reporting_manager,
            'priority' => '1',
            'leave_status' => '0'
        ];
        $applied_leave->appliedLeaveApprovals()->create($approval_data);

        //////////////////////////Notify///////////////////////////


        $notification_data = [
            'sender_id' => $userid,
            'receiver_id' => $reporting_manager,
            'label' => 'Leave Application',
            'read_status' => '0'
        ];

        $message = $user->employee->fullname." has applied for a leave, from ".date('d/m/Y',strtotime($applied_leave->from_date)).' to '.date('d/m/Y',strtotime($applied_leave->to_date)).'.';

        $notification_data['message'] = $message;
        $applied_leave->notifications()->create($notification_data);

        pushNotification($notification_data['receiver_id'], $notification_data['label'], $notification_data['message']);

        $reporting_manager_data = Employee::where(['user_id'=>$reporting_manager])
            ->with('user')->first();

        $mail_data['to_email'] = $reporting_manager_data->user->email;
        //$mail_data['to_email'] = "xeam.richa@gmail.com";
        $mail_data['subject'] = "Leave Application";
        $mail_data['message'] = $user->employee->fullname." has applied for a leave. Please take an action. Here is the link for website <a href='".url('/')."'>Click here</a>";
        $mail_data['fullname'] = $reporting_manager_data->fullname;

        $this->sendGeneralMail($mail_data);

        return redirect('leaves/applied-leaves');

    }//end of function

    /*
        Save the approval status assigned by leave officer (approved or rejected) & if approved send for approval to next officer until the last one. Then perform leave calculations if everyone has approved
    */
    function saveLeaveApproval(Request $request)
    {

        $request->validate([
            'remark' => 'required',
        ]);

        $leave_approval = AppliedLeaveApproval::find($request->alaId);

        $applied_leave = $leave_approval->appliedLeave;

        if($request->leaveStatus == $leave_approval->leave_status){
            if($leave_approval->leave_status == 1){
                return back()->with('error', 'You Already Approved the leave');
            }elseif($leave_approval->leave_status == 2){
                return back()->with('error', 'You Already Reject the leave');
            }
        }

        $approver = User::where(['id'=>Auth::id()])->first();

        $leave_approval->leave_status = $request->leaveStatus;

        $currentYear = date('Y');
        $year = $currentYear;
        $lastMonth = date('m', strtotime('-1 month',strtotime($applied_leave->from_date)));
        $year = date('Y', strtotime('-1 month',strtotime($applied_leave->from_date)));

        if($lastMonth == 12){
            $year = $year - 1;
        }

         $lastMonthAttendanceVerification = AttendanceVerification::where('user_id', $applied_leave->user_id)
            ->whereYear('on_date', $year)->whereMonth('on_date', $lastMonth)
            ->first();
        if(!isset($lastMonthAttendanceVerification)){
            $lastMonthAttendanceNotVerified = "Last Month Attendance Is not Verified. Kindly verified it first before approving leave.";
            return redirect('leaves/approve-leaves')->with('error',$lastMonthAttendanceNotVerified);
        }

        // Comment By Hitesh
        $leave_approval->save();
        $applier = $leave_approval->user;

        $message_data = [
            'sender_id' => $approver->id,
            'receiver_id' => $leave_approval->user_id,
            'label' => 'Leave Remarks',
            'message' => $request->remark,
            'read_status' => '0'
        ];

        $applied_leave->messages()->create($message_data);

        if($leave_approval->leave_status==1){
            $applied_leave->final_status = '1';
        }else{
            $applied_leave->final_status = '0';
        }

        $applied_leave->save();

        $mail_data['to_email'] = $applier->email;
        $mail_data['fullname'] = $applier->employee->fullname;

        if($applied_leave->final_status == '1'){
            $excluded_dates = $applied_leave->excluded_dates;
            $excluded_date = explode(",",$excluded_dates);

            $from_date = $applied_leave->from_date;
            $to_date = $applied_leave->to_date;
            $user_id = $applied_leave->user_id;

            $i=0;
            while (strtotime($from_date) <= strtotime($to_date)) {

                if (!in_array($from_date, $excluded_date))
                {
                    Attendance::create(['user_id'=>$user_id,'on_date'=>$from_date,'status'=>"Leave"]);
                }

                $from_date = date ("Y-m-d", strtotime("+1 days", strtotime($from_date)));
                $i++;
            }

            $probation_data = probationCalculations($applier);


            /*
             * Leave calculation update segregation
             */
            leaveRelatedCalculations($probation_data,$applied_leave);

            $message = "Your applied leave, from ".date('d/m/Y',strtotime($applied_leave->from_date)).' to '.date('d/m/Y',strtotime($applied_leave->to_date)).' has been approved.';

            $mail_data['subject'] = "Leave Approved";
            $mail_data['message'] = $message;
            $this->sendGeneralMail($mail_data);

            pushNotification($applier->id, $mail_data['subject'], $mail_data['message']);
        }else{
            if($leave_approval->leave_status == '2'){
                Attendance::where('user_id', $applier->id)->where('status', 'Leave')
                    ->where('on_date', '>=', $applied_leave->from_date)->where('on_date', '<=', $applied_leave->to_date)->delete();

                $appliedLeaveSegregation = AppliedLeaveSegregation::where('applied_leave_id', $applied_leave->id)->first();
                $userLeavePool = LeaveDetail::where('user_id', $applier->id)->latest()->first();

                if($applied_leave->leave_type_id == 1) {
                    LeaveDetail::where('id', $userLeavePool->id)->update([
                        'accumalated_casual_leave' => $userLeavePool->accumalated_casual_leave + $appliedLeaveSegregation->paid_count,
                        'paid_casual' => $userLeavePool->paid_casual - $appliedLeaveSegregation->paid_count
                    ]);
                }

                if($applied_leave->leave_type_id == 2) {
                    LeaveDetail::where('id', $userLeavePool->id)->update([
                        'accumalated_sick_leave' => $userLeavePool->accumalated_sick_leave + $appliedLeaveSegregation->paid_count,
                        'paid_sick' => $userLeavePool->paid_sick - $appliedLeaveSegregation->paid_count

                    ]);
                }

                $message = "Your applied leave, from ".date('d/m/Y',strtotime($applied_leave->from_date)).' to '.date('d/m/Y',strtotime($applied_leave->to_date)).' has been rejected.';

                $mail_data['subject'] = "Leave Rejected";
                $mail_data['message'] = $message;
                $this->sendGeneralMail($mail_data);

                pushNotification($applier->id, $mail_data['subject'], $mail_data['message']);
            }
        }

        return redirect("leaves/approve-leaves");

    }//end of function

    /*
        Check whether the leave has been approved by all concerned officers
    */
    function checkLeaveApprovalOnAllLevels($applied_leave)
    {
        $all_supervisors = $applied_leave->appliedLeaveApprovals()->count();
        $all_approved_supervisors = $applied_leave->appliedLeaveApprovals()->where(['leave_status'=>'1'])->count();

        if($all_supervisors == $all_approved_supervisors){
            $applied_leave->final_status = '1';
            $applied_leave->save();
        }

        return $applied_leave;

    }//end of function

    /*
        Get the list of all leave requests a leave officer has to take action on or already has
    */
    function approveLeaves($leave_status = null)
    {
        $user = User::where(['id'=>Auth::id()])->first();

        if(empty($leave_status) || $leave_status == 'pending'){
            $status = '0';
            $leave_status = 'pending';
        }elseif ($leave_status == 'approved') {
            $status = '1';
            $leave_status = 'Approved';
        }elseif ($leave_status == 'rejected') {
            $status = '2';
            $leave_status = 'Rejected';
        }

        $data = DB::table('applied_leave_approvals as ala')
            ->join('applied_leaves as al','al.id','=','ala.applied_leave_id')
            ->leftjoin('leave_replacements as lr','al.id','=','lr.applied_leave_id')
            ->join('employees as emp','emp.user_id','=','ala.user_id')
            ->leftjoin('employees as emp2','emp2.user_id','=','lr.user_id')
            ->join('leave_types as lt','al.leave_type_id','=','lt.id')
            ->where(['ala.supervisor_id' => $user->id,'ala.leave_status'=>$status,'al.isactive'=>1])
            ->select('ala.*','emp.fullname as applier_name','al.number_of_days','al.final_status','lt.name as leave_type_name','al.from_date','al.to_date','emp2.fullname as replacement_name','al.created_at')
            ->orderBy('ala.applied_leave_id','DESC')
            ->get();

        if(!$data->isEmpty()){
            foreach ($data as $key => $value) {
                if($value->final_status == '0'){
                    $check_rejected = DB::table('applied_leave_approvals as ala')
                        ->where(['ala.applied_leave_id' => $value->applied_leave_id,'ala.leave_status'=>'2'])
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

        return view('leaves.list_applied_leave_approvals')->with(['data'=>$data,'selected_status'=>$leave_status]);

    }//end of function

    /*
        Get the list of all leave requests a user has applied for
    */
    function appliedLeaves()
    {
        $user = User::where(['id'=>Auth::id()])->first();
        $data = DB::table('applied_leaves as al')
            ->leftjoin('leave_replacements as lr','al.id','=','lr.applied_leave_id')
            ->leftjoin('employees as emp','emp.user_id','=','lr.user_id')
            ->join('leave_types as lt','al.leave_type_id','=','lt.id')
            ->where(['al.user_id' => $user->id])
            ->select('al.id','al.number_of_days','al.isactive','lt.name as leave_type_name','al.from_date','al.to_date','emp.fullname as replacement','al.final_status','al.created_at')
            ->orderBy('al.id','desc')
            ->get();

        if(!$data->isEmpty()){
            foreach ($data as $key => $value) {
                $priority_wise_status = DB::table('applied_leave_approvals as ala')
                    ->where(['ala.applied_leave_id' => $value->id])
                    ->select('ala.priority','ala.leave_status')
                    ->orderBy('ala.priority')
                    ->get();

                $can_cancel_leave = 0;
                if(count($priority_wise_status) == 1 && $priority_wise_status[0]->leave_status == 0){
                    $can_cancel_leave = 1;
                }

                $value->priority_wise_status = $priority_wise_status;
                $value->can_cancel_leave = $can_cancel_leave;

                if($value->final_status == '0'){
                    $check_rejected = DB::table('applied_leave_approvals as ala')
                        ->where(['ala.applied_leave_id' => $value->id,'ala.leave_status'=>'2'])
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

        return view('leaves.list_applied_leaves')->with(['data'=>$data]);

    }//end of function

    /*
        Ajax request to get details of a specific leave to show in modal
    */
    function appliedLeaveInfo(Request $request)
    {
        $data = DB::table('applied_leaves as al')
            ->where(['al.id' => $request->applied_leave_id])
            ->select('al.*')
            ->first();

        $applied_leave = AppliedLeave::find($request->applied_leave_id);

        $documents = $applied_leave->appliedLeaveDocuments()->get();

        $view = View::make('leaves.applied_leave_info',['data'=>$data,'documents'=>$documents]);
        $contents = $view->render();
        return $contents;

    }//end of function

    /*
        Download the document attached with an applied leave
    */
    function appliedLeaveDocument($id)
    {
        $document = AppliedLeaveDocument::find($id);
        $path_to_file = config('constants.uploadPaths.uploadAppliedLeaveDocument').$document->name;
        return response()->download($path_to_file);
    }//end of function

    /*
        Ajax request to get details of applied leave approvals to show in modal
    */
    function messages(Request $request)
    {
        $applied_leave = AppliedLeave::find($request->applied_leave_id);
        $messages = $applied_leave->messages()
            ->where('label','Leave Remarks')
            ->orderBy('created_at','DESC')
            ->with('sender.employee:id,user_id,fullname')
            ->with('receiver.employee:id,user_id,fullname')
            ->get();

        $view = View::make('leaves.list_messages',['data' => $messages]);
        $contents = $view->render();

        return $contents;
    }//end of function

    /*
        Cancel a leave request before any leave officer has taken an action
    */
    function cancelAppliedLeave($applied_leave_id)
    {
        $applied_leave = AppliedLeave::find($applied_leave_id);
        $user_id = Auth::id();
        $approval = $applied_leave->appliedLeaveApprovals()
            ->where('leave_status','!=','0')
            ->first();

        if(!empty($approval)){
            return redirect()->back()->with('cannot_cancel_error','Reporting manager has taken a decision. You cannot cancel the leave now.');

        }elseif(($applied_leave->user_id == $user_id) && empty($approval)){
            $applied_leave->isactive = 0;
            $applied_leave->save();

            CompensatoryLeave::where('applied_leave_id',$applied_leave_id)
                ->update(['applied_leave_id'=>0]);
        }

        return redirect()->back()->with('success', "Leave Cancel Successfully.");

        //return redirect('leaves/applied-leaves');

    }//end of function

    /*
        Send an email with a very basic formatting
    */
    function sendGeneralMail($mail_data)
    {   //mail_data Keys => to_email, subject, fullname, message

        if(!empty($mail_data['to_email'])){
            Mail::to($mail_data['to_email'])->send(new GeneralMail($mail_data));
        }

        return true;

    }//end of function

    /*
        Get information from database to show it on leave report form page
    */
    function leaveReportForm()
    {
        $data['departments'] = Department::where(['isactive'=>1])->get();
        $data['projects'] = Project::where(['isactive'=>1,'approval_status'=>'1'])->get();

        return view('leaves.leave_report_form')->with(['data'=>$data]);

    }//end of function

    /*
        Filter & generate leave report as per selected parameters & show them in a list
    */
    function createLeaveReport(Request $request)
    {

        $report_data =  [
            'from_date' => $request->fromDate,
            'to_date' => $request->toDate,
            'no_days' => $request->noDays,
            'weekends' => $request->weekends,
            'holidays' => $request->holidays
        ];

        if($request->department == '0'){
            $report_data['department_id'] = "";
            $report_data['department_sign'] = "!=";
        }else{
            $report_data['department_id'] = $request->department;
            $report_data['department_sign'] = "=";
        }

        if($request->project == '0'){
            $report_data['project_id'] = "";
            $report_data['project_sign'] = "!=";
        }else{
            $report_data['project_id'] = $request->project;
            $report_data['project_sign'] = "=";
        }

        $from_date = date("Y-m-d",strtotime($report_data['from_date']));
        $to_date = date("Y-m-d",strtotime($report_data['to_date']));

        $profile_pic_path = config('constants.uploadPaths.profilePic');
        $static_pic = config('constants.static.profilePic');

        //print_r($report_data);die;

        $data = DB::table('applied_leaves as al')
            ->join('applied_leave_segregations as als','al.id','=','als.applied_leave_id')
            ->join('employee_profiles as emp','emp.user_id','=','al.user_id')
            ->join('users as u','al.user_id','=','u.id')
            ->join('employees as e','al.user_id','=','e.user_id')
            ->join('project_user as pu','pu.user_id','=','al.user_id')
            ->where(['al.final_status'=>'1'])
            ->where('als.from_date','>=',$from_date)
            ->where('als.to_date','<=',$to_date)
            ->where('pu.project_id',$report_data['project_sign'],$report_data['project_id'])
            //->where('emp.location_id',$report_data['locationSign'],$report_data['locationId'])
            ->where('emp.department_id',$report_data['department_sign'],$report_data['department_id'])
            ->select("al.user_id","u.employee_code","e.fullname",DB::raw("SUM(als.paid_count) as paid_count,SUM(als.compensatory_count) as compensatory_count,SUM(als.unpaid_count) as unpaid_count, CASE WHEN e.profile_picture = '' OR e.profile_picture IS NULL THEN '".$static_pic."' ELSE CONCAT('".$profile_pic_path."',e.profile_picture) END AS profile_picture"))
            ->groupBy("al.user_id")
            ->orderBy("e.fullname")
            ->get();

        return view('leaves.list_leave_report')->with(['report_data'=>$report_data,'data'=>$data]);

    }//end of function

    /*
        Show the details of a specific person when selected from a leave report list
    */
    function additionalLeaveReportInfo(Request $request)
    {
        $report_data =  [
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'user_id' => $request->id
        ];

        $from_date = date("Y-m-d",strtotime($report_data['from_date']));
        $to_date = date("Y-m-d",strtotime($report_data['to_date']));

        $data = DB::table('applied_leaves as al')
            ->join('applied_leave_segregations as als','al.id','=','als.applied_leave_id')
            ->where(['al.final_status'=>'1','al.user_id'=>$report_data['user_id']])
            ->where('als.from_date','>=',$from_date)
            ->where('als.to_date','<=',$to_date)
            ->select("al.*","als.*")
            ->orderBy("al.created_at","desc")
            ->get();

        $employee_data = User::where(['id'=>$request->id])
            ->with('employee')
            ->first();

        return view('leaves.additional_leave_report_info')->with(['report_data'=>$report_data,'data'=>$data,'employee_data'=>$employee_data]);

    }//end of function


    public function uploadLeavePool(Request $request)
    {
        $data = \Maatwebsite\Excel\Facades\Excel::toArray(new LeaveDetailImport,request()->file('leave_detail'));

        $count = 0;
        if (count($data)) {
            foreach ($data[0] as $key => $record) {
//                return $record;
                $empCode = $record['empcode'];
                $accumulatedCl = $record['casual_leave'];
                $accumulatedSl = $record['sick_leave'];

//                if($key != 0) {
                if($empCode != ''){
                    $user = User::where("employee_code", $empCode)->with('designation')->first();
                    if(isset($user->designation[0])) {
                        $designation = $user->designation[0]->id;
                    }

                    if(!isset($user)) {
                        $employee = Employee::where('employee_id', $empCode)->first();
                        if(isset($employee)){
                            $user = User::where("id", $employee->user_id)->with('designation')->first();
                            if(isset($user->designation[0])) {
                                $designation = $user->designation[0]->id;
                            }
                        }
                    }

                    if (isset($user)) {

                        $leaveDetail = LeaveDetail::where('user_id', $user->id)->whereYear('month_info', '2020')
                            ->whereMonth('month_info', '12')->first();

                        if ($leaveDetail != '') {
                            $userOctLeavePoolUpdate[] = $user->employee_code;
//                            return $leaveDetail->id;
                            LeaveDetail::where('id', $leaveDetail->id)->update([
                                'accumalated_casual_leave' => $accumulatedCl,
                                'accumalated_sick_leave' => $accumulatedSl,
                            ]);
                        }else{

                            $previousMonthLeaveDetail = LeaveDetail::where('user_id', $user->id)->OrderBy('id', 'DESC')->first();

                            if($designation==4){    // for vccm
                                $balance_casual_leave = $previousMonthLeaveDetail->balance_casual_leave - 1.5;
                                $balance_sick_leave = $accumulatedSl;
                            }elseif ($designation==3 || $designation==5) {  // for PO
                                $balance_casual_leave = $previousMonthLeaveDetail->balance_casual_leave - 2;
                                $balance_sick_leave = $accumulatedSl;

                            }elseif ($designation==2){    // for SPO
                                $balance_casual_leave = $previousMonthLeaveDetail->balance_casual_leave;
                                $balance_sick_leave = $accumulatedSl;
                            }
                            if ($designation != 6 ) {    // for TO

                                $approval_data = [
                                    'user_id' => $user->id,
                                    'month_info' => '2020-12-26',
                                    'accumalated_casual_leave' => isset($accumlated_casual) ? $accumlated_casual : 0,
                                    'accumalated_sick_leave' => isset($accumlated_sick) ? $accumlated_sick : 0,
                                    'balance_casual_leave' => isset($balance_casual_leave) ?
                                        $balance_casual_leave : 0,
                                    'balance_sick_leave' => isset($balance_sick_leave) ? $balance_sick_leave : 0,
                                    'balance_maternity_leave' => '180',
                                    'balance_paternity_leave' => '15',
                                    'unpaid_casual' => 0,
                                    'paid_casual' => 0,
                                    'unpaid_sick' => 0,
                                    'paid_sick' => 0,
                                    'compensatory_count' => 0,
                                    'isactive' => 1
                                ];
                                LeaveDetail::create($approval_data);
                                $userOctLeavePoolCreate[] = $user->employee_code;
                            }

                        }


                        $leaveDetail = LeaveDetail::where('user_id', $user->id)->whereYear('month_info', '2021')
                            ->whereMonth('month_info', '01')->first();
                        if ($leaveDetail != '') {
                            if($designation==4){    // for vccm
//                                return $accumulatedCl;
                                $accumlated_casual = $accumulatedCl + 1.5;
                                $accumlated_sick = $accumulatedSl;
                            }elseif ($designation==3 || $designation==5) {  // for PO
                                $accumlated_casual = $accumulatedCl + 2;
                                $accumlated_sick = $accumulatedSl;
                            }elseif ($designation==2){    // for SPO
                                $accumlated_casual = $accumulatedCl;
                                $accumlated_sick = $accumulatedSl;
                            }

                            LeaveDetail::where('id', $leaveDetail->id)->update([
                                'accumalated_casual_leave' => $accumlated_casual,
                                'accumalated_sick_leave' => $accumlated_sick,
                            ]);

                            $userNovLeavePoolUpdate[] = $user->employee_code;

                        }else{
                            $previousMonthLeaveDetail = LeaveDetail::where('user_id', $user->id)->OrderBy('id', 'DESC')->first();

                            if($designation==4){    // for vccm
                                $accumlated_casual = $accumulatedCl + 1.5;
                                $accumlated_sick = $accumulatedSl;
                                $balance_casual_leave = $previousMonthLeaveDetail->balance_casual_leave - 1.5;
                                $balance_sick_leave = $previousMonthLeaveDetail->accumalated_sick_leave;
                            }elseif ($designation==3 || $designation==5) {  // for PO
                                $accumlated_casual = $accumulatedCl + 2;
                                $accumlated_sick = $accumulatedSl;
                                $balance_casual_leave = $previousMonthLeaveDetail->balance_casual_leave - 2;
                                $balance_sick_leave = $previousMonthLeaveDetail->accumalated_sick_leave ;

                            }elseif ($designation==2){    // for SPO
                                $accumlated_casual = $accumulatedCl;
                                $accumlated_sick = $accumulatedSl;
                                $balance_casual_leave = $previousMonthLeaveDetail->balance_casual_leave;
                                $balance_sick_leave = $previousMonthLeaveDetail->accumlated_sick;

                            }
                            if ($designation!=6) {    // for TO
                                $approval_data = [
                                    'user_id' => $user->id,
                                    'month_info' => '2021-01-26',
                                    'accumalated_casual_leave' => isset($accumlated_casual) ? $accumlated_casual : 0,
                                    'accumalated_sick_leave' => isset($accumlated_sick) ? $accumlated_sick : 0,
                                    'balance_casual_leave' => isset($balance_casual_leave) ? $balance_casual_leave : 0,
                                    'balance_sick_leave' => isset($balance_sick_leave) ? $balance_sick_leave : 0,
                                    'balance_maternity_leave' => '180',
                                    'balance_paternity_leave' => '15',
                                    'unpaid_casual' => 0,
                                    'paid_casual' => 0,
                                    'unpaid_sick' => 0,
                                    'paid_sick' => 0,
                                    'compensatory_count' => 0,
                                    'isactive' => 1
                                ];
                                LeaveDetail::create($approval_data);

                                $userNovLeavePoolCreate[] = $user->employee_code;
                            }
                        }
                    }else{
                        $userNotExist[] = $empCode;
                    }
                }
            }
        }
        if(isset($userNotExist)) {
            echo "User Not Exist" . '=>' . ' ';
            print_r($userNotExist);
            echo "<br/>";
        }
        if(isset($userOctLeavePoolUpdate)) {
            echo "User Oct Leave Pool Update" . '=>'. ' ';
            print_r($userOctLeavePoolUpdate);
            echo "<br/>";
        }
        if(isset($userOctLeavePoolCreate)) {
            echo "User Oct Leave Pool Create" . '=>' . ' ';
            print_r($userOctLeavePoolCreate);
            echo "<br/>";
        }
        if(isset($userNovLeavePoolUpdate)) {
            echo "User Nov Leave Pool Update" . '=>' . ' ';
            print_r($userNovLeavePoolUpdate);
            echo "<br/>";
        }
        if(isset($userNovLeavePoolCreate)) {
            echo "User Nov Leave Pool Create" . '=>' . ' ';
            print_r($userNovLeavePoolCreate);
            echo "<br/>";
        }
    }


    public function exportLeavePool(Request $request)
    {
        $data = \Maatwebsite\Excel\Facades\Excel::toArray(new LeaveDetailImport,request()->file('leave_detail'));

        $count = 0;
        if (count($data)) {
            foreach ($data[0] as $key => $record) {
//                if($key != 0) {
                if($record['emp_code'] != ''){
                    $user = User::where("employee_code", $record['emp_code'])->with('employee')->first();

                    if (isset($user)) {

//                        $leaveDetail = LeaveDetail::where('user_id', $user->id)->whereYear('month_info', '2020')->whereMonth('month_info', '10')->first();
//                        if(isset($leaveDetail)) {
//                            $userLeavePool = ['name' => $user->employee['fullname'], 'emp_code' => $user->employee_code, 'accumulate_casual_leave' => $leaveDetail->accumalated_casual_leave, 'accumulate_sick_leave' => $leaveDetail->accumalated_sick_leave];
//                            $leavePools[] = $userLeavePool;
//                        }
                        $users[] = $user->id;
                    }
                }
            }
        }
//        $leaveDetail = DB::table('leave_details')
//            ->join('employees', 'employees.user_id', '=', 'leave_details.user_id')
//            ->whereIn('employees.user_id', $users)->whereYear('leave_details.month_info', '2020')
//            ->whereMonth('leave_details.month_info', '11')
//            ->select('employees.employee_id', 'employees.fullname', 'leave_details.accumalated_casual_leave', 'leave_details.accumalated_sick_leave')->get();

        $export = new LeavePoolExport($users);
        return \Maatwebsite\Excel\Facades\Excel::download($export, 'CG_oct_nov_leave_pool.xlsx');
    }

    public function allLeavePool(Request $request){

        if($request->year == NULL) {
            $year = date('Y');
            $month = date('m');
        }else{
            $year = $request->year;
            $month = $request->month;
        }


        $userid = Auth::user()->id;
        $employees = DB::table('projects as p')
            ->join('project_user as pu', 'p.id', '=', 'pu.project_id')
            ->join('employee_profiles as ep', 'ep.user_id', '=', 'pu.user_id')
            ->join('employees as e', 'ep.user_id', '=', 'e.user_id')
            ->join('users as u', 'ep.user_id', '=', 'u.id')
            ->where('e.user_id', '!=', 1)
            ->where(['pu.isactive' => 1, 'p.isactive' => 1, 'p.approval_status' => '1', 'e.approval_status' => '1']);

        $employees = $employees->select('ep.user_id', 'e.fullname', 'e.employee_id', 'u.employee_code', 'e.joining_date', 'ep.state_id')->get();

        $designation_login_data = DB::table('designation_user as du')
            ->where('du.user_id', '=', $userid)
            ->select('du.id', 'du.user_id', 'du.designation_id')->first();

        $designation_login_user = $designation_login_data->designation_id;

        $token = 0;
        $employees_po = array();

        foreach ($employees as $key => $value) {

            $designation_user_data = DB::table('designation_user as du')
                ->where('du.user_id', '=', $value->user_id)
                ->select('du.id', 'du.user_id', 'du.designation_id')->first();


            if ($designation_user_data) {
                $designation_user = $designation_user_data->designation_id;
                $employees[$key]->designation_id = $designation_user;

                $district_listed_user = DB::table('location_user as lu')
                    ->where('lu.user_id', '=', $designation_user_data->user_id)
                    ->select('lu.id', 'lu.user_id', 'lu.location_id')->first();
            }


            if (!empty($district_listed_user) and $district_listed_user->location_id) {
                $listed_user_district_id = $district_listed_user->location_id;
                $employees[$key]->district_id = $listed_user_district_id;
            } else {
                $employees[$key]->district_id = "";
            }


            $state_listed_user = EmployeeProfile::where(['user_id' => $value->user_id])
                ->first();

            $listed_user_state_id = $state_listed_user->state_id;
            $employees[$key]->state_id = $listed_user_state_id;
        }

        //check for district if po
        if ($designation_login_user == 5) {
            $token = 5;
            $district_login_user = DB::table('location_user as lu')
                ->where('lu.user_id', '=', $designation_login_data->user_id)
                ->select('lu.id', 'lu.user_id', 'lu.location_id')->get();

            $login_user_district_id = array();
            foreach ($district_login_user as $district) {
                if (!empty($district) and $district->location_id) {
                    $login_user_district_id[] = $district->location_id;
                } else {
                    $login_user_district_id[] = "";
                }

            }
            $choose_desg_id = 4;
            $i = 0;
            foreach ($employees as $employee) {

                if (isset($employee->designation_id)) {

                    if ($employee->designation_id == $choose_desg_id) {
                        foreach ($login_user_district_id as $district_id) {
                            if ($employee->district_id == $district_id) {
                                $employees_po[$i] = $employee;
                                $i++;
                                break;
                            }
                        }
                    }

                } else {
                    $user_with_no_designation = $employee->employee_id;
                    $has_no_designation = 1;
                    break;
                }

            }
        }

        if ($designation_login_user == 3) {

            $token = 3;
            $district_login_user = DB::table('location_user as lu')
                ->where('lu.user_id', '=', $designation_login_data->user_id)
                ->select('lu.id', 'lu.user_id', 'lu.location_id')->get();

            $login_user_district_id = array();
            foreach ($district_login_user as $district) {
                if (!empty($district) and $district->location_id) {
                    $login_user_district_id[] = $district->location_id;
                } else {
                    $login_user_district_id[] = "";
                }
            }

            $i = 0;
            $choose_desg_id = 4;

            foreach ($employees as $employee) {

                if ($employee->designation_id == $choose_desg_id) {
                    foreach ($login_user_district_id as $district_id) {
                        if ($employee->district_id == $district_id) {
                            $employees_po[$i] = $employee;
                            $i++;
                            break;
                        }
                    }
                }
            }
        }


        if ($designation_login_user == 4) {
            $token = 4;
            $employees_po = array();
        }

        if ($designation_login_user == 1) {
            $token++;
            $i = 0;

            foreach ($employees as $emp) {
                if (isset($emp->designation_id) and ($emp->designation_id != "")) {


                    if ($emp->designation_id == 2) { //spo
                        $empDesg_NPA = DB::table('designation_user as du')
                            ->where('du.designation_id', '=', 1)
                            ->select('du.id', 'du.user_id', 'du.designation_id')->first();

                        if ($empDesg_NPA != "") {

                            $u_id = $empDesg_NPA->user_id;

                            $emp_data = Employee::where(['user_id' => $u_id])
                                ->first();
                            $emp->reporting_head = $emp_data->fullname;

                            $user_data = User::where(['id' => $u_id])
                                ->first();
                            $emp->reporting_head_uId = $user_data->employee_code;

                        }

                    }

                    if ($emp->designation_id == 3) { //po-op

                        $User_state = EmployeeProfile::where(['user_id' => $emp->user_id])
                            ->first();

                        $user_state_id = $User_state->state_id;
                        $employees_under_states = EmployeeProfile::where(['state_id' => $user_state_id])
                            ->get();

                        foreach ($employees_under_states as $employees_state) {
                            $employeeId_under_state = $employees_state->user_id;
                            $empDesg_under_state = DB::table('designation_user as du')
                                ->where(['du.user_id' => $employeeId_under_state, 'du.designation_id' => 2])
                                ->select('du.id', 'du.user_id', 'du.designation_id')->first();


                            if ($empDesg_under_state != "") {

                                $arr_underlaying_emp[] = $empDesg_under_state;

                                $u_id = $empDesg_under_state->user_id;

                                $emp_data = Employee::where(['user_id' => $u_id])
                                    ->first();
                                $emp->reporting_head = $emp_data->fullname;

                                $user_data = User::where(['id' => $u_id])
                                    ->first();
                                $emp->reporting_head_uId = $user_data->employee_code;
                            }
                        }

                    }

                    if ($emp->designation_id == 5) { //po-it

                        $User_state = EmployeeProfile::where(['user_id' => $emp->user_id])
                            ->first();

                        $user_state_id = $User_state->state_id;
                        $employees_under_states = EmployeeProfile::where(['state_id' => $user_state_id])
                            ->get();

                        foreach ($employees_under_states as $employees_state) {
                            $employeeId_under_state = $employees_state->user_id;
                            $empDesg_under_state = DB::table('designation_user as du')
                                ->where(['du.user_id' => $employeeId_under_state, 'du.designation_id' => 2])
                                ->select('du.id', 'du.user_id', 'du.designation_id')->first();


                            if ($empDesg_under_state != "") {

                                //$arr_underlaying_emp[] = $empDesg_under_state;

                                $u_id = $empDesg_under_state->user_id;

                                $emp_data = Employee::where(['user_id' => $u_id])
                                    ->first();
                                $emp->reporting_head = $emp_data->fullname;

                                $user_data = User::where(['id' => $u_id])
                                    ->first();
                                $emp->reporting_head_uId = $user_data->employee_code;
                            }
                        }

                    }

                    if ($emp->designation_id == 4) { //vccm
                        $user_district = DB::table('location_user as lu')
                            ->where('lu.user_id', '=', $emp->user_id)
                            ->select('lu.id', 'lu.user_id', 'lu.location_id')->first();
                        $user_district_id = $user_district->location_id;

                        if ($user_district_id != "") {
                            $employeesDistrict = DB::table('location_user as lu')
                                ->where(['lu.location_id' => $user_district_id])
                                ->select('lu.id', 'lu.user_id', 'lu.location_id')->get();
                        }


                        if ($employeesDistrict) {
                            foreach ($employeesDistrict as $empDistrict) {
                                $user_id = $empDistrict->user_id;
                                $empDesg_under_district = DB::table('designation_user as du')
                                    ->where(['du.user_id' => $user_id])
                                    ->where(function ($query) {
                                        $query->where('du.designation_id', '=', 5)
                                            ->orWhere('du.designation_id', '=', 3);
                                    })
                                    ->select('du.id', 'du.user_id', 'du.designation_id')->first();


                                if ($empDesg_under_district != "") {
                                    $u_id = $empDesg_under_district->user_id;

                                    $emp_data = Employee::where(['user_id' => $u_id])
                                        ->first();
                                    $emp->reporting_head = $emp_data->fullname;

                                    $user_data = User::where(['id' => $u_id])
                                        ->first();
                                    $emp->reporting_head_uId = $user_data->employee_code;
                                }

                            }

                        }


                    }

                }


            }
            $employees_po = $employees;

        }

        //check for state if spo
        if ($designation_login_user == 2) {


            $token++;
            $state_login_user = EmployeeProfile::where(['user_id' => $designation_login_data->user_id])->first();

            $login_user_state_id = $state_login_user->state_id;

            $i = 0;
            foreach ($employees as $employee) {

                if ($employee->state_id == $login_user_state_id and ($employee->designation_id == 3 or $employee->designation_id == 5)) {
                    if (!empty($employee)) {
                        $employees_po[$i] = $employee;
                    }
                }
                $i++;
            }
        }

        foreach($employees_po as $key => $employee){
            $allEmployees[$key]['employee'] = $employee;
            $allEmployees[$key]['leaveDetail'] =  LeaveDetail::where('user_id', $employee->user_id)->whereYear('month_info', $year)->whereMonth('month_info', $month)->first();
        }

        return view('leaves.leave_pool', compact('allEmployees'));
    }

}//end of class
