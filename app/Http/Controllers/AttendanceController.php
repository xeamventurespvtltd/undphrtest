<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Employee;
use App\Attendance;
use App\AttendancePunch;
use App\AttendanceRemark;
use App\AttendanceChange;
use App\AttendanceChangeApproval;
use App\AttendanceChangeDate;
use App\AttendanceVerification;
use App\AttendanceResult;
use App\Company;
use App\Holiday;
use App\Project;
use App\Department;
use App\TravelApproval;
use App\TbltTimesheet;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Exports\ConsolidatedAttendanceExport;
use App\Exports\AttendancePunchExport;
use App\Exports\AttendanceExport;
use App\Exports\SaralAttendanceExport;
use Maatwebsite\Excel\Facades\Excel;
use View;
use App\LeaveType;
use App\LeaveDetail;
use App\AppliedLeave;
use App\CompensatoryLeave;
use App\ShiftException;
use App\Shift;
use App\EmployeeProfile;
use App\State;
use Illuminate\Support\Facades\Mail;
use App\Mail\GeneralMail;

class AttendanceController extends Controller
{
    /*
        Get all the punches of a user marked from app of a given date
    */
    function viewMap(Request $request){

        if(!$request->has('id')){
            $user = Auth::user();
        }else{
            $user = User::find($request->id);
        }

        if(!$request->has('date')){
            $date = date("Y-m-d");
        }else{
            $date = date("Y-m-d",strtotime($request->date));
        }

        $username = $user->employee->fullname;

        $attendance = $user->attendances()
            ->where(['on_date' => $date])
            ->with(['attendancePunches'=>function($query){
                $query->where('type','!=','NA');
            }])
            ->first();

        $attendance_locations = $user->attendanceLocations()
            ->whereDate('created_at',$date)
            ->get();

        return view('attendances.view_map')->with(['attendance'=>$attendance,'attendance_locations'=>$attendance_locations,'date' => $date,'username'=>$username]);
   
    }

    /*
        Custom function used once to deduct leaves
    */
    function setToFeb()
    {
        $date = date("Y-m-d",strtotime('2019-12-27'));
        $applied_leaves = AppliedLeave::where('from_date',$date)
            ->where('to_date',$date)
            ->whereDate('created_at',$date)
            ->where(['final_status'=>'1','isactive'=>1])
            ->whereHas('appliedLeaveSegregations', function(Builder $query)use($date){
                $query->where('from_date',$date)
                    ->where('to_date',$date)
                    ->whereDate('created_at',$date)
                    ->where('unpaid_count','0');
            })->with('appliedLeaveSegregations')
            ->with('appliedLeaveApprovals')
            ->get();

        //dd($applied_leaves);

        $newdate = date("Y-m-d",strtotime('2019-02-28'));
        $newdatetime = date("Y-m-d H:i:s",strtotime('2019-02-28'));
        foreach ($applied_leaves as $applied_leave) {
            $applied_leave->from_date = $newdate;
            $applied_leave->to_date = $newdate;
            $applied_leave->created_at = $newdatetime;
            $applied_leave->updated_at = $newdatetime;
            $applied_leave->save();
            $applied_leave->appliedLeaveSegregations[0]->to_date = $newdate;
            $applied_leave->appliedLeaveSegregations[0]->created_at = $newdatetime;
            $applied_leave->appliedLeaveSegregations[0]->updated_at = $newdatetime;
            $applied_leave->appliedLeaveSegregations[0]->from_date = $newdate;
            $applied_leave->appliedLeaveSegregations[0]->save();
            $applied_leave->appliedLeaveApprovals[0]->created_at = $newdatetime;
            $applied_leave->appliedLeaveApprovals[0]->updated_at = $newdatetime;
            $applied_leave->appliedLeaveApprovals[0]->save();
        }
        echo "done";
    }

    function saveAttendancePunch(Request $request)  //Biometric
    {
        $user = User::where(['employee_code'=>$request->employee_code])->first();
        $on_date = date("Y-m-d",strtotime($request->on_date));

        $attendance = $user->attendances()->where(['on_date'=>$on_date])->first();

        if(empty($attendance)){
            $data = [
                'on_date' => $on_date,
                'status' => 'Present'
            ];

            $attendance = $user->attendances()->create($data);
        }

        $on_time = date("H:i:s",strtotime($request->on_time));
        $punch = $attendance->attendancePunches()->create(['on_time'=>$on_time]);

    }//end of function

    /*
     * Show the user calendar display of his/her own attendance
    */
    function myAttendance(Request $request)
    {
        $user = Auth::user();

        $user_filled = Employee::where(['user_id'=>$user->id])->first();

        $profile_filled =  $user_filled->is_complete;

        if($profile_filled==0 AND $user->id!=1){

            return redirect('profile-detail-form');
        }
        $user = User::where(['id'=>Auth::id()])
            ->with('employee')
            ->first();

        $user_designation_data = DB::table('designation_user as du')

            ->where('du.user_id','=',$user->id)

            ->select('du.id', 'du.user_id','du.designation_id')->first();
        $user_designation = $user_designation_data->designation_id;
        $user->user_designation = $user_designation;
        //$user->designation;

        $curr_year =  date('Y');
        $curr_month = date('m');

        if($curr_month==1){
            $start_year= $curr_year-1;
            $startmonth = 12;
        }else{
            $start_year= $curr_year;
            $startmonth = $curr_month-1;
        }

        $date1 = $curr_year.'-'.$curr_month.'-'.'25';
        $on_date = date('Y-m-d',strtotime($date1));
        $verification = $user->attendanceVerifications()
            ->where(['on_date'=>$on_date])
            ->first();

        if(!empty($verification) && $verification->isverified == 1){
            $verify['isverified'] = 1;  //verified
        }else{
            $verify['isverified'] = 0;  //not verified
        }

        $req['year'] = 0;
        $req['month'] = 0;

        if($request->month){
            $req['month'] = $request->month;
        }

        if($request->year){
            $req['year'] = $request->year;
        }

        return view('attendances.my_attendance')->with(['user'=>$user,'req'=>$req,'verify'=>$verify]);

    }//end of function

    /*
     * Show the user with permission of view-attendance, calendar display of other's attendance
    */
    function viewEmployeeAttendance(Request $request)
    {
        $user = User::where(['id'=>$request->id])
            ->with('employee')
            ->with(['leaveAuthorities'=>function($query){
                $query->where('priority','2');
            }])
            ->first();

        $user_designation_data = DB::table('designation_user as du')

            ->where('du.user_id','=',$user->id)

            ->select('du.id', 'du.user_id','du.designation_id')->first();

        $user_designation = $user_designation_data->designation_id;
        $user->user_designation = $user_designation;


        $req['year'] = 0;
        $req['month'] = 0;
        if($request->year){
            $req['year'] = $request->year;
        }
        if($request->month){
            $req['month'] = $request->month;
        }
        $verify['isverified'] = 0; //not verified
        $verify['verifier'] = 0;
        $on_date = $req['year'].'-'.$req['month'].'-'.'25';
        $on_date = date('Y-m-d',strtotime($on_date));
        $verification = $user->attendanceVerifications()
            ->where(['on_date'=>$on_date])
            ->first();

        if(!empty($verification) && $verification->isverified == 1){
            $verify['isverified'] = 1;  //verified
        }else{
            $verify['isverified'] = 0;  //not verified
        }
        /*  if(!$user->leaveAuthorities->isEmpty()){
             if($user->leaveAuthorities[0]->manager_id == Auth::id()){
                 $verify['verifier'] = $user->leaveAuthorities[0]->manager_id;
             }
         } */

        $verify['verifier'] = Auth::id();
        $leaveDetail = LeaveDetail::where('user_id', $user->id)->whereYear('month_info', $req['year'])->whereMonth
        ('month_info', $req['month'])->first();
        return view('attendances.view_attendance', compact('leaveDetail'))->with(['user'=>$user,'req'=>$req,
            'verify'=>$verify,
            'on_date'=>$on_date]);

    }//end of function

    /*
     * For changing or creating the attendance status of a user of a day.
     * Used by employee having the view-attendance permission.
    */
    function changeAttendancOffeStatus(Request $request)
    {
        $user_id = Auth::id();
        $on_date = date("Y-m-d");
        if($request->status=='Holiday'){
            $status='Holiday';
            $attendance = Attendance::where(['user_id'=>$user_id,'on_date'=>$on_date])->first();

            if(!empty($attendance)){
                $attendance->update(['status'=>$status]);

            }else{
                $attendance = Attendance::create(['user_id'=>$user_id,'on_date'=>$on_date,'status'=>$status]);
            }
            return redirect()->back()->withSuccess('Holiday added successfully.');
        }
        elseif($request->status=='Week-Off'){
            $status='Week-Off';
            $attendance = Attendance::where(['user_id'=>$user_id,'on_date'=>$on_date])->first();
            if(!empty($attendance)){
                $attendance->update(['status'=>$status]);
            }else{
                $attendance = Attendance::create(['user_id'=>$user_id,'on_date'=>$on_date,'status'=>$status]);
            }
            return redirect()->back()->withSuccess('Week-Off added successfully.');
        }
    }

    function changeAttendanceStatus(Request $request)
    {
        $date = date("Y-m-d",strtotime($request->on_date));
        $attendance = Attendance::where(['user_id'=>$request->user_id,'on_date'=>$date])->first();

        if(!empty($attendance)){
            $attendance->update(['status'=>$request->attendanceStatus]);

        }else{
            $attendance = Attendance::create(['user_id'=>$request->user_id,'on_date'=>$date,'status'=>$request->attendanceStatus]);
        }

        if(!empty($request->on_time)){
            $time = $date.' '.$request->on_time;
            $time = date("H:i:s",strtotime($time));
            $punch = $attendance->attendancePunches()->where(['on_time'=>$time])->first();
            if(empty($punch)){
                $attendance->attendancePunches()->create(['on_time'=>$time,'punched_by'=>Auth::id()]);
            }
        }
        return redirect($request->url)->with('leave_success','Attendance has been added successfully.');
    }//end of function
    /*
     * Cron functionality for taking data from tblt timesheet table to
     * the attendance and attendance punches table. And also insert the
     * other status like Leave and Travel as well in attendance table.
    */
    function addBiometricToPunchesCron()
    {
        TbltTimesheet::where('ispunched',0)->chunk(50, function($biometrics){
            foreach ($biometrics as $biometric) {

                $user = User::whereHas('employee',function(Builder $query)use($biometric){
                    $query->where('employee_id',$biometric->punchingcode)
                        ->where('isactive',1);
                })->first();
                if(!empty($user)){
                    $attendance = $user->attendances()->where(['on_date'=>$biometric->date])->first();
                    $datetimeString = $biometric->date.' '.$biometric->time;
                    $datetime = date("H:i:s",strtotime($datetimeString));
                    if(!empty($attendance)){

                        $attendance_punch = $attendance->attendancePunches()->create(['on_time'=>$datetime]);
                        $biometric->ispunched = 1;
                        $biometric->save();

                        if($attendance->status == 'Absent'){
                            $attendance->status = 'Present';
                            $attendance->save();
                        }
                    }else {
                        $holiday = Holiday::where('holiday_from','<=',$biometric->date)
                            ->where('holiday_to','>=',$biometric->date)
                            ->where('isactive',1)
                            ->first();

                        if(!empty($holiday) && strtotime(date("Y-m-d H:i:s")) > strtotime($datetimeString)){
                            $attendance = $user->attendances()->create(['on_date'=>$biometric->date,'status'=>'Holiday']);
                        }else{
                            $leave = AppliedLeave::where('from_date','<=',$biometric->date)
                                ->where('to_date','>=',$biometric->date)
                                ->where(['final_status'=>'1','user_id'=>$user->id])
                                ->first();

                            if(!empty($leave) && strtotime(date("Y-m-d H:i:s")) > strtotime($datetimeString)){
                                $attendance = $user->attendances()->create(['on_date'=>$biometric->date,'status'=>'Leave']);
                            }else{
                                $travel = TravelApproval::where(['isactive'=>1,'status'=>'approved','user_id'=>$user->id])
                                    ->where('date_from','<=',$biometric->date)
                                    ->where('date_to','>=',$biometric->date)
                                    ->first();

                                if(!empty($travel) && strtotime(date("Y-m-d H:i:s")) > strtotime($datetimeString)){
                                    $attendance = $user->attendances()->create(['on_date'=>$biometric->date,'status'=>'Travel']);
                                }elseif(strtotime(date("Y-m-d H:i:s")) >= strtotime($datetimeString)){
                                    $attendance = $user->attendances()->create(['on_date'=>$biometric->date,'status'=>'Present']);
                                }
                            }
                        }
                        if(!empty($attendance)){
                            $attendance_punch = $attendance->attendancePunches()
                                ->create(['on_time'=>$datetime]);
                            $biometric->ispunched = 1;
                            $biometric->save();
                        }
                    }
                }
            }
        });
        echo "cron ran successfully!";
    }//end of cron function
    /*
     * Cron functionality for looping over the last month all dates and mark
     * the status Absent in attendance table. Also create an entry for month's
     * first date in the attendance verification table.
    */
    function checkAbsentCron()
    {
        $current_date = date('Y-m-d');
        $current_month_second_date = config('constants.restriction.checkAbsentCron');
        $last_month_start_date = date('Y-m-01', strtotime('-1 months', strtotime($current_date)));
        $last_month_end_date = date('Y-m-t', strtotime($last_month_start_date));

        if(strtotime(date("Y-m-d")) == strtotime($current_month_second_date)){
            $period = CarbonPeriod::create($last_month_start_date, $last_month_end_date);

            $dates = [];
            // Iterate over the period
            foreach ($period as $date) {
                $dates[] = $date->format('Y-m-d');
            }
            User::whereHas('employee',function(Builder $query){
                $query->where('isactive',1);
            })->chunk(50, function($users)use($dates){
                foreach ($users as $key => $user) {
                    foreach ($dates as $key => $date) {
                        $attendance = $user->attendances()->where(['on_date'=>$date])->first();

                        if(!empty($attendance)){
                            if($attendance->status == 'Present' || $attendance->status == 'Absent'){
                                $leave = AppliedLeave::where('from_date','<=',$date)
                                    ->where('to_date','>=',$date)
                                    ->where(['final_status'=>'1','user_id'=>$user->id])
                                    ->first();

                                if(!empty($leave) && ($leave->leave_type_id != 6) && strtotime(date("Y-m-d H:i:s")) > strtotime($date)){
                                    $attendance->status = 'Leave';
                                    $attendance->save();
                                }else{
                                    $travel = TravelApproval::where(['isactive'=>1,'status'=>'approved','user_id'=>$user->id])
                                        ->where('date_from','<=',$date)
                                        ->where('date_to','>=',$date)
                                        ->first();

                                    if(!empty($travel) && strtotime(date("Y-m-d H:i:s")) > strtotime($date)){
                                        $attendance->status = 'Travel';
                                        $attendance->save();
                                    }
                                }
                            }
                        }else{
                            $holiday = Holiday::where('holiday_from','<=',$date)
                                ->where('holiday_to','>=',$date)
                                ->where('isactive',1)
                                ->first();
                            if(!empty($holiday) && strtotime(date("Y-m-d H:i:s")) > strtotime($date)){
                                $attendance = $user->attendances()->create(['on_date'=>$date,'status'=>'Holiday']);
                            }else{
                                $leave = AppliedLeave::where('from_date','<=',$date)
                                    ->where('to_date','>=',$date)
                                    ->where(['final_status'=>'1','user_id'=>$user->id])
                                    ->first();
                                if(!empty($leave) && strtotime(date("Y-m-d H:i:s")) > strtotime($date)){
                                    $attendance = $user->attendances()->create(['on_date'=>$date,'status'=>'Leave']);
                                }else{
                                    $travel = TravelApproval::where(['isactive'=>1,'status'=>'approved','user_id'=>$user->id])
                                        ->where('date_from','<=',$date)
                                        ->where('date_to','>=',$date)
                                        ->first();

                                    if(!empty($travel) && strtotime(date("Y-m-d H:i:s")) > strtotime($date)){
                                        $attendance = $user->attendances()->create(['on_date'=>$date,'status'=>'Travel']);
                                    }elseif(strtotime(date("Y-m-d H:i:s")) > strtotime($date)){
                                        if(date("l",strtotime($date)) == 'Sunday'){
                                            $day_status = 'Week-Off';
                                        }else{
                                            $day_status = 'Absent';
                                        }
                                        $attendance = $user->attendances()->create(['on_date'=>$date,'status'=>$day_status]);
                                    }
                                }
                            }
                        }

                        $start_of_month = date("Y-m-01",strtotime($date));

                        if(!$user->leaveAuthorities->isEmpty()){
                            $hod_id = $user->leaveAuthorities[0]->manager_id;
                        }else{
                            $hod_id = 1;
                        }

                        $verification = $user->attendanceVerifications()
                            ->where(['on_date'=>$start_of_month])
                            ->first();

                        if(empty($verification) && strtotime(date("Y-m-d h:i:s")) > strtotime($start_of_month)){
                            $verification = $user->attendanceVerifications()->create(['manager_id'=>$hod_id,'on_date'=>$start_of_month]);
                        }
                    }
                }
            });
            echo "Cron ran";
        }else{
            echo "Cron did not ran";
        }

    }//end of function

    /*
     * Ajax request for showing multiple attendance punches of a user.
    */
    function multiplePunches(Request $request)
    {
        $date = date("Y-m-d",strtotime($request->date));
        $attendance = Attendance::where(['on_date'=>$date,'user_id'=>$request->user_id])->first();

        $punches = $attendance->attendancePunches()->orderBy('on_time')->get();

        if(!$punches->isEmpty()){
            foreach ($punches as $key => $value) {
                $value->on_time = date("h:i A",strtotime($value->on_time));
            }
        }

        return $punches;

    }//end of function

    /*
     * For saving the remarks added by user on a given date.
    */
    function saveRemarks(Request $request)
    {
        $url = $request->url;
        $date = date("Y-m-d",strtotime($request->on_date));

        $remark = AttendanceRemark::where(['user_id'=>$request->user_id,'on_date'=>$date])->first();

        if(!empty($remark)){
            $remark->remarks = $request->remarks;
            $remark->save();
        }else{
            AttendanceRemark::create(['user_id'=>$request->user_id,'on_date'=>$date,'remarks'=>$request->remarks]);
        }

        return redirect($url);

    }//end of function

    /*
     * For filtering and displaying the monthly attendance of those employees only for
     * which Auth User is responsible.
    */
    function verifyAttendanceList(Request $request)
    {
        if($request->month){
            $req['month'] = $request->month;
        }else{
            $req['month'] = date("n");
        }

        if($request->year){
            $req['year'] = $request->year;
        }else{
            $req['year'] = date("Y");
        }

        if($request->employee_status != ""){
            $req['employee_status'] = $request->employee_status;
        }else{
            $req['employee_status'] = 1;
        }

        $month_last_date = date("Y-m-t",strtotime($req['year'].'-'.$req['month'].'-01'));

        $employees = DB::table('projects as p')
            ->join('project_user as pu','p.id','=','pu.project_id')
            ->join('employee_profiles as ep','ep.user_id','=','pu.user_id')
            ->join('employees as e','ep.user_id','=','e.user_id')
            ->join('users as u','ep.user_id','=','u.id')
            ->join('departments as d','d.id','=','ep.department_id')
            //->join('attendance_verifications as av','av.user_id','=','ep.user_id')
            ->join('leave_authorities as la','la.user_id','=','ep.user_id')
            ->where('e.user_id','!=',1)
            //->where(['av.manager_id'=>Auth::id()])
            ->where(['la.isactive'=>1,'la.priority'=>'2','la.manager_id'=>Auth::id()])
            //->whereYear('av.on_date',$req['year'])
            //->whereMonth('av.on_date',$req['month'])
            ->whereDate('e.joining_date','<=',$month_last_date)
            ->where(['pu.isactive'=>1,'p.isactive'=>1,'p.approval_status'=>'1','e.approval_status'=>'1','e.isactive'=>(int)$req['employee_status']])
            ->select('ep.user_id','d.name as department_name','e.fullname','u.employee_code','e.joining_date')
            ->get();

        if(!empty($req['year'])){
            $year = $req['year'];
            $month = $req['month'];
            $date = $year.'-'.$month.'-'.'01';
            $total_days = (int)date("t",strtotime($date));
            $holiday_counter = 0;
            $sunday_counter = 0;
            $holiday_array = [];
            $sunday_array = [];

            for ($i=1; $i <= $total_days ; $i++) {
                if($i >= 10){
                    $date = $year.'-'.$month.'-'.$i;
                }else{
                    $date = $year.'-'.$month.'-'.'0'.$i;
                }
                $holiday = Holiday::where('holiday_from','<=',$date)
                    ->where('holiday_to','>=',$date)
                    ->where('isactive',1)
                    ->first();

                if(!empty($holiday) && date("l",strtotime($date)) != "Sunday"){
                    $holiday_counter += 1;
                    $holiday_array[] = $date;
                }elseif (date("l",strtotime($date)) == "Sunday") {
                    $sunday_counter += 1;
                    $sunday_array[] = $date;
                }
            }

            $data['holidays'] = $holiday_counter;
            $data['sundays'] = $sunday_counter;
            $data['workdays'] = $total_days - ($sunday_counter + $holiday_counter);
        }

        if(!$employees->isEmpty()){
            foreach ($employees as $key => $value) {
                $attendance_result = AttendanceResult::where(['user_id'=>$value->user_id,'on_date'=>date("Y-m-d",strtotime($req['year'].'-'.$req['month'].'-'.'01'))])->first();

                if(empty($attendance_result)){
                    $value->on_date = date("d/m/Y",strtotime($req['year'].'-'.$req['month'].'-'.'01'));
                    $value->holidays = effectiveHolidays($value->joining_date,$holiday_array);
                    $value->sundays = effectiveHolidays($value->joining_date,$sunday_array);
                    $value->workdays = $data['workdays'];

                    $value->late = $this->calculateLateAttendance($value->user_id,$req['year'],$req['month']);

                    $value->absent_days = $this->calculateAbsentAttendance($value->user_id,$req['year'],$req['month']); //- ($value->holidays);

                    $value->absent_days = ($value->workdays < $value->absent_days) ? $value->workdays : $value->absent_days;

                    $value->absent_days = ($value->absent_days < 0) ? 0 : $value->absent_days;

                    $travels = TravelApproval::where(['isactive'=>1,'status'=>'approved','user_id'=>$value->user_id])
                        ->where(function($query)use($req,$value){
                            $query->orWhere(function($query)use($req,$value){
                                $query->whereYear('date_from',$req['year'])
                                    ->whereMonth('date_from',$req['month']);
                            })
                                ->orWhere(function($query)use($req,$value){
                                    $query->whereYear('date_from',$req['year'])
                                        ->whereMonth('date_to',$req['month']);
                                })
                                ->orWhere(function($query)use($req,$value){
                                    $query->whereYear('date_from',$req['year'])
                                        ->whereMonth('date_from','<',$req['month'])
                                        ->whereMonth('date_to','>',$req['month']);
                                });
                        })
                        ->get();

                    if(!$travels->isEmpty()){
                        $value->travel_days = $this->calculateTotalTravelDuration($travels, $req);
                    }else{
                        $value->travel_days = 0;
                    }

                    $value->paid_leaves = DB::table('applied_leave_segregations as als')
                        ->join('applied_leaves as al','al.id','=','als.applied_leave_id')
                        ->where(['al.final_status'=>'1','al.user_id'=>$value->user_id])
                        ->where(function($query)use($req){
                            $query->whereYear('als.to_date',$req['year'])
                                ->whereMonth('als.to_date',$req['month']);
                        })
                        ->sum('als.paid_count');

                    $value->unpaid_leaves = DB::table('applied_leave_segregations as als')
                        ->join('applied_leaves as al','al.id','=','als.applied_leave_id')
                        ->where(['al.final_status'=>'1','al.user_id'=>$value->user_id])
                        ->where(function($query)use($req){
                            $query->whereYear('als.to_date',$req['year'])
                                ->whereMonth('als.to_date',$req['month']);
                        })
                        ->sum('als.unpaid_count');

                    $value->total = ($value->workdays+$value->holidays+$value->sundays) - ($value->absent_days + $value->unpaid_leaves);
                }else{
                    $value->on_date = date("d/m/Y",strtotime($attendance_result->on_date));
                    $value->holidays = $attendance_result->holidays;
                    $value->sundays = $attendance_result->week_offs;
                    $value->workdays = $attendance_result->workdays;
                    $value->late = $attendance_result->late;
                    $value->absent_days = $attendance_result->absent_days;
                    $value->travel_days = $attendance_result->travel_days;
                    $value->paid_leaves = $attendance_result->paid_leaves;
                    $value->unpaid_leaves = $attendance_result->unpaid_leaves;
                    $value->total = $attendance_result->total_present_days;
                }

                $verification = AttendanceVerification::where(['on_date'=>date("Y-m-d",strtotime($req['year'].'-'.$req['month'].'-'.'01')),'user_id'=>$value->user_id])->first();

                if(empty($verification) || $verification->isverified == 0){
                    //$data['isverified'] = 0;
                    $value->isverified = 0;
                }else{
                    $value->isverified = 1;
                }
            }
        }

        return view('attendances.list_verify_attendance')->with(['data'=>$data,'employees'=>$employees,'req'=>$req]);

    }//end of function


    function singleAttendanceSheets(Request $request)
    {
        $req['year'] = $request->year;
        $req['month'] = $request->month;
        $emp = Employee::where('user_id', $request->user_id)->first();

        $users = [];
        $heading_array = array('S. no', 'Employee Name', 'Employee Code', 'Designation', 'State', 'Reporting head', 'Reporting head Employee Code', 'Verified', 'Month');


        array_push($users,$emp->user_id);

        $data = [];

        $key=0;

        if($req['month']==1){
            $start_year = $req['year']-1;
            $last_month = 12;
        }

        else{
            $start_year = $req['year'];
            $last_month = $req['month']-1;
        }


        $start_month = date("Y-m-d", strtotime($start_year . '-' . $last_month . '-26'));

        $end_month = date("Y-m-d", strtotime($req['year'] . '-' . $req['month'] . '-25'));

        $start_date = date("Y-m-d", strtotime($start_year . '-' . $last_month . '-26'));

        $user = $emp->user_id;

        $punches = Attendance::where('user_id', $user)
            ->where('on_date', '>=', $start_month)
            ->where('on_date', '<=', $end_month)
            ->with('user')
            ->with('user.employee')
            ->with('user.designation')
            ->orderBy('id', 'DESC')
            ->get()->toArray();

        $new_array = array();
        foreach($punches as $value){
            if(!isset($new_array[$value['on_date']])){
                $new_array[$value['on_date']] = $value;
            }
        }

        $new_array = array_values($new_array);

        $punches = array_reverse($new_array);

        $column = array_column($punches, 'on_date');

        array_multisort($column, SORT_ASC, $punches);

        $data[$key]['#'] = $key + 1;

        $emp_data = Employee::where(['user_id' => $user])->first();
        $emp_profile_data = EmployeeProfile::where(['user_id' => $user])->with('state')->first();

        if (isset($emp_profile_data->state)) {
            $emp_state = $emp_profile_data->state->name;
        } else {
            $emp_state = "NULL";
        }

        $prev_month = date("F", strtotime("-1 month", strtotime($end_month)));
        $month = date('F', strtotime($end_month));

        $verified_data = AttendanceVerification::where(['user_id' => $user])
            ->whereYear('on_date', $req['year'])
            ->whereMonth('on_date', $req['month'])
            ->first();

        if ($verified_data) {
            $verified = $verified_data->isverified;
            if ($verified == 1) {
                $verify_status = "Yes";
            } else {
                $verify_status = "No";
            }
        } else {
            $verify_status = "No";
        }

        // Get LoggedIn User Detailed...

        $logged_in_user = User::where(['id' => Auth::id()]) ->with('employee')->first();

        $user_data = User::where(['id' => $user])->with('designation')->first();


        $data[$key]['fullname'] = $emp_data->fullname;
        $data[$key]['employee_code'] = $user_data->employee_code;
        $data[$key]['designation'] = $user_data->designation[0]->name;
        $data[$key]['State'] = $emp_state;

        $emp->designation_id = $user_data->designation[0]->id;
        if ($emp->designation_id == 2) { //spo
            $empDesg_NPA = DB::table('designation_user as du')
                ->where('du.designation_id', '=', 1)
                ->select('du.id', 'du.user_id', 'du.designation_id')->first();

            if ($empDesg_NPA != "") {

                $u_id = $empDesg_NPA->user_id;

                $emp_data = Employee::where(['user_id' => $u_id])->first();
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

            if(isset($user_district->location_id)){
                $user_district_id = $user_district->location_id;
            }else{
                $userDonthaveLocation[] = $emp->employee_code;
            }


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


        if(isset($emp->reporting_head)){
            $data[$key]['Reporting head'] = $emp->reporting_head;
        }else{
            $data[$key]['Reporting head'] = $logged_in_user->reporting_head;
        }
        if(isset($emp->reporting_head)){
            $data[$key]['Reporting head UId'] = $emp->reporting_head_uId;
        }else{
            $data[$key]['Reporting head UId'] = $logged_in_user->employee->employee_id;
        }

        $data[$key]['Verified'] = $verify_status;
        $data[$key]['Month'] = $prev_month . "-" . $month;

        while (strtotime($start_date) <= strtotime($end_month)) {
            $j = 1;

            $presentstatus = false;

            if (count($punches) != 0) {
                $allPunches = $punches;

                foreach ($allPunches as $punch) {
                    $punch = (object)$punch;
                    if(isset($punch->on_date)) {
                        if ($start_date == date("Y-m-d", strtotime($punch->on_date))) {

                            if ($punch->status == "Present") {

                                $data[$key][$start_date] = "P";
                                $presentstatus = true;
                                break;
                            } elseif ($punch->status == "Holiday") {
                                $data[$key][$start_date] = "H";
                                $presentstatus = true;
                                break;
                            } elseif ($punch->status == "Week-Off") {
                                $data[$key][$start_date] = "WO";
                                $presentstatus = true;
                                break;
                            } elseif ($punch->status == "Leave") {
                                $punch_date = $punch->on_date;

//                                echo $punch_date;
//                                $leaves = DB::table('applied_leaves as al')
//                                    ->join('leave_types as lt', 'lt.id', '=', 'al.leave_type_id')
//                                    ->where('al.user_id', $user)
//                                    ->whereMonth('al.from_date', date('m', strtotime($punch_date)))
//                                    ->orwhereMonth('al.to_date', date('m', strtotime($punch_date)))
//                                    ->where(function ($query) use ($punch_date) {
//                                        $query->where('al.from_date', '<=', $punch_date)
//                                            ->Where('al.to_date', '>=', $punch_date);
//                                    })->orderBY('al.id', 'DESC')->first();

                                $leaves = DB::table('applied_leaves as al')
                                    ->join('leave_types as lt', 'lt.id', '=', 'al.leave_type_id')
                                    ->where('al.user_id', $user)
                                    ->where(function ($query) use ($punch_date) {
                                        $query->where('al.from_date', '<=', $punch_date)
                                            ->Where('al.to_date', '>=', $punch_date);
                                    })->orderBY('al.id', 'DESC')->first();

                                if ($leaves) {
                                    if ($leaves->name == "Sick Leave") {
                                        $data[$key][$start_date] = "SL";
                                        $presentstatus = true;
                                        break;
                                    } elseif ($leaves->name == "Casual Leave") {
                                        $data[$key][$start_date] = "CL";
                                        $presentstatus = true;
                                        break;
                                    } elseif ($leaves->name == "Maternity Leave") {
                                        $data[$key][$start_date] = "ML";
                                        $presentstatus = true;
                                        break;
                                    } elseif ($leaves->name == "Paternity Leave") {
                                        $data[$key][$start_date] = "PL";
                                        $presentstatus = true;
                                        break;
                                    } elseif ($leaves->name == "Half Leave") {
                                        $data[$key][$start_date] = "HL";
                                        $presentstatus = true;
                                        break;
                                    } elseif ($leaves->name == "Compensatory Leave") {
                                        $data[$key][$start_date] = "CompL";
                                        $presentstatus = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    $j++;
                }

            }


            if (!$presentstatus) {
                if ($start_date < date("Y-m-d", strtotime($req['year'] . '-04-01'))) {
                    $data[$key][$start_date] = "NA";
                } else {
                    $data[$key][$start_date] = "A";
                }

            }
            if ($key == 0) {
                $start_date = date("d-m-Y", strtotime($start_date));
                $start_date_heading = date("d", strtotime($start_date));
//                        $heading_array[] = $start_date_heading;
            }
            $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));

        }

        $key = $key + 1;




        for($i = 26; $i <= cal_days_in_month(CAL_GREGORIAN, $last_month, $start_year); $i++){
            $heading_array[] = $i;
        }

        for($i = 1; $i < 26; $i++){
            $heading_array[] = $i;
        }
        $data = collect($data);
        $export = new AttendanceExport($data, $heading_array);
        return Excel::download($export, 'attendance-punch.xlsx');
    }
    /*
     * For filtering, exporting and displaying the monthly attendance report of employees.
    */
    function consolidatedAttendanceSheets(Request $request)
    {

        $user_with_no_designation = $has_no_designation = 0;

        $data['projects'] = Project::where(['isactive' => 1, 'approval_status' => '1'])->select('id', 'name')->get();
        $data['departments'] = Department::where(['isactive' => 1])->select('id', 'name')->get();
        $user = Auth::user();
        $userid = $user->id;

        if (isset($user->employeeProfile)) {
            $data['user_department'] = $user->employeeProfile->department_id;
        } else {
            $data['user_department'] = "";
        }


        $req['emp_records'] = '!=';
        $req['company_sign'] = '!=';
        $req['project'] = 0;
        $req['project_sign'] = '!=';
        $req['department'] = 0;
        $req['department_sign'] = '!=';
        $req['year'] = 0;
        $req['year_sign'] = '!=';
        $req['month'] = 0;
        $req['month_sign'] = '!=';
        $req['submit'] = "";
        $req['employee_status'] = $request->employee_status;
        $req['attendance_type'] = 'All';

        if ($request->emp_records) {
            $req['emp_records'] = $request->emp_records;
        }


        if ($request->submit) {
            $req['submit'] = $request->submit;
        }

        if ($request->project) {
            $req['project'] = $request->project;
            $req['project_sign'] = '=';
        }

        $department_name = 'All';
        if ($request->department) {
            $req['department'] = $request->department;
            $req['department_sign'] = '=';
            $department_name = Department::where('id', $request->department)->value('name');
        }

        if ($request->month) {
            $req['month'] = $request->month;
            $req['month_sign'] = '=';
        }

        if ($request->year) {
            $req['year'] = $request->year;
            $req['year_sign'] = '=';
        }

        if (!empty($request->all())) {
            $month_last_date = date("Y-m-t", strtotime($req['year'] . '-' . $req['month'] . '-01'));
            $employees = DB::table('projects as p')
                ->join('project_user as pu', 'p.id', '=', 'pu.project_id')
                ->join('employee_profiles as ep', 'ep.user_id', '=', 'pu.user_id')
                ->join('employees as e', 'ep.user_id', '=', 'e.user_id')
                ->join('users as u', 'ep.user_id', '=', 'u.id')
                ->where('p.id', $req['project_sign'], $req['project'])
                ->where('e.user_id', '!=', 1)
                ->whereDate('e.joining_date', '<=', $month_last_date)
                ->where(['pu.isactive' => 1, 'p.isactive' => 1, 'p.approval_status' => '1', 'e.approval_status' => '1', 'e.isactive' => (int)$req['employee_status']]);


            $employees = $employees->select('ep.user_id', 'e.fullname', 'e.employee_id', 'u.employee_code', 'e.joining_date', 'ep.state_id')->get();

        } else {
            $employees = collect();
        }

        if (!empty($req['year'])) {
            $year = $req['year'];
            $month = $req['month'];

            if ($month == 1) {
                $date1_year = $year - 1;
                $prevmonth = 12;
            } else {
                $date1_year = $year;
                $prevmonth = $month - 1;
            }

            $date1 = $date1_year . '-' . $prevmonth . '-' . '26';
            $date2 = $year . '-' . $month . '-' . '25';

            $diff = strtotime($date2) - strtotime($date1);

            // 1 day = 24 hours
            // 24 * 60 * 60 = 86400 seconds
            $total_days = abs(round($diff / 86400));

            $holiday_counter = 0;
            $sunday_counter = 0;
            $holiday_array = [];
            $sunday_array = [];


            while (strtotime($date1) <= strtotime($date2)) {

                $date1 = date("Y-m-d", strtotime("+1 days", strtotime($date1)));

                $holiday = Attendance::where('on_date', $date1)
                    ->where('status', 'Holiday')
                    ->first();

                if (!empty($holiday)) {
                    $holiday_counter += 1;
                    $holiday_array[] = $date1;
                } else {
                    $Week_off = Attendance::where('on_date', $date1)
                        ->where('status', 'Week-Off')
                        ->first();

                    if (!empty($Week_off)) {
                        $sunday_counter += 1;
                        $sunday_array[] = $date1;
                    }
                }

            }
            $data['department_name'] = $department_name;
        }

        $data['isverified'] = 1;

        if (!$employees->isEmpty()) {
            foreach ($employees as $key => $value) {

                $value->on_date = date("d/m/Y", strtotime($req['year'] . '-' . $req['month'] . '-' . '25'));

                if ($req['month'] == 1) {
                    $start_year = $req['year'] - 1;
                    $last_month = 12;
                } else {
                    $start_year = $req['year'];
                    $last_month = $req['month'] - 1;
                }

                $state_data = State::where('id', $value->state_id)
                    ->first();

                if ($state_data) {
                    $value->state_name = $state_data->name;
                }

                $start_month = date("Y-m-d", strtotime($start_year . '-' . $last_month . '-26'));

                $end_month = date("Y-m-d", strtotime($req['year'] . '-' . $req['month'] . '-25'));

                $paid_leaves_count = DB::table('applied_leave_segregations as als')
                    ->join('applied_leaves as al', 'al.id', '=', 'als.applied_leave_id')
                    ->where(['al.final_status' => '1', 'al.user_id' => $value->user_id])
                    ->where(function ($query) use ($start_month, $end_month) {
                        $query->where('als.to_date', '>=', $start_month)
                            ->where('als.to_date', '<=', $end_month);
                    })
                    ->select('als.paid_count')
                    ->orderBy('als.applied_leave_id', 'DESC')
                    ->sum('paid_count');

                $compensatory_leaves_count =  DB::table('applied_leave_segregations as als')
                    ->join('applied_leaves as al', 'al.id', '=', 'als.applied_leave_id')
                    ->where(['al.final_status' => '1', 'al.user_id' => $value->user_id])
                    ->where(function ($query) use ($start_month, $end_month) {
                        $query->where('als.to_date', '>=', $start_month)
                            ->where('als.to_date', '<=', $end_month);
                    })
                    ->select('als.compensatory_count')
                    ->orderBy('als.applied_leave_id', 'DESC')
                    ->sum('compensatory_count');

                $value->paid_leaves = $paid_leaves_count + $compensatory_leaves_count;

                $value->unpaid_leaves = DB::table('applied_leave_segregations as als')
                    ->join('applied_leaves as al', 'al.id', '=', 'als.applied_leave_id')
                    ->where(['al.final_status' => '1', 'al.user_id' => $value->user_id])
                    ->where(function ($query) use ($start_month, $end_month) {
                        $query->where('als.to_date', '>=', $start_month)
                            ->where('als.to_date', '<=', $end_month);
                    })
                    ->select('als.unpaid_count')
                    ->orderBy('als.applied_leave_id', 'DESC')->sum('unpaid_count');
                $value->verification = "No";

                $verification = AttendanceVerification::where(['on_date' => date("Y-m-d", strtotime($req['year'] . '-' . $req['month'] . '-' . '25')), 'user_id' => $value->user_id])->first();

                if (empty($verification) || $verification->isverified == 0) {
                    //$data['isverified'] = 0;
                    $value->isverified = 0;
                    $value->verification = "No";
                } else {
                    $value->isverified = 1;
                    $value->verification = "Yes";
                }
            }
        }

        //exit;
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
            if ($req['emp_records'] == "Self") {
                $choose_desg_id = 5;
            } else {
                $choose_desg_id = 4;
            }
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
            if ($req['emp_records'] == "Self") {
                $choose_desg_id = 3;
            } else {
                $choose_desg_id = 4;
            }

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

                            $emp_data = Employee::where(['user_id' => $u_id])->first();
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

                        if(isset($user_district->location_id)){
                            $user_district_id = $user_district->location_id;
                        }else{
                            $userDonthaveLocation[] = $emp->employee_code;
                        }


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
            if ($req['emp_records'] == "Self") {

                // dd("self");

                foreach ($employees as $employee) {
                    if ($employee->state_id == $login_user_state_id and $employee->designation_id == 2) {
                        if (!empty($employee)) {
                            $employees_po[$i] = $employee;
                        }
                    }
                    $i++;
                }

            } else {
                foreach ($employees as $employee) {

                    if ($employee->state_id == $login_user_state_id and ($employee->designation_id == 3 or $employee->designation_id == 5)) {
                        if (!empty($employee)) {
                            $employees_po[$i] = $employee;
                        }
                    }
                    $i++;
                }
            }


        }


        if ($has_no_designation) {
            return redirect()->back()->with('error_msg', 'Employee Code - ' . $user_with_no_designation . '. No designation found for some of your team members or your self.');
        }

        if ((!isset($employees_po) or empty($employees_po)) and $token == 0) {
            $employees_po = $employees;
        }

        if($request->submit == 'Attendance sheet'){

            $users = [];
            $heading_array = array('S. no', 'Employee Name', 'Employee Code', 'Designation', 'State', 'Reporting head', 'Reporting head Employee Code', 'Verified', 'Month');

            foreach($employees_po as $emp){
                array_push($users,$emp->user_id);
            }

            $data = [];

            $key=0;

            if($req['month']==1){
                $start_year = $req['year']-1;
                $last_month = 12;
            }

            else{
                $start_year = $req['year'];
                $last_month = $req['month']-1;
            }

            foreach($employees_po as $emp) {
//                return $employees_po;

                $user = $emp->user_id;
                $start_date = date("Y-m-d", strtotime($start_year . '-' . $last_month . '-26'));
                $end_month = date("Y-m-d", strtotime($req['year'] . '-' . $req['month'] . '-25'));

                $punches = Attendance::where('user_id', $user)
                    ->where('on_date', '>=', $start_month)
                    ->where('on_date', '<=', $end_month)
                    ->with('user')
                    ->with('user.employee')
                    ->with('user.designation')
                    ->orderBy('id', 'DESC')
                    ->get()->toArray();

                $new_array = array();
                foreach($punches as $value){
                    if(!isset($new_array[$value['on_date']])){
                        $new_array[$value['on_date']] = $value;
                    }
                }

                $new_array = array_values($new_array);

                $punches = array_reverse($new_array);

                $column = array_column($punches, 'on_date');

                array_multisort($column, SORT_ASC, $punches);

                $data[$key]['#'] = $key + 1;


                // echo "<br/> key  $key";
                $emp_data = Employee::where(['user_id' => $user])->first();
                $emp_profile_data = EmployeeProfile::where(['user_id' => $user])->with('state')->first();
                if (isset($emp_profile_data->state)) {
                    $emp_state = $emp_profile_data->state->name;
                } else {
                    $emp_state = "NULL";
                }


                $prev_month = date("F", strtotime("-1 month", strtotime($end_month)));

                $month = date('F', strtotime($end_month));


                $verified_data = AttendanceVerification::where(['user_id' => $user])
                    ->whereYear('on_date', $req['year'])
                    ->whereMonth('on_date', $req['month'])
                    ->first();
                if ($verified_data) {
                    $verified = $verified_data->isverified;
                    if ($verified == 1) {
                        $verify_status = "Yes";
                    } else {
                        $verify_status = "No";
                    }
                } else {
                    $verify_status = "No";
                }

                // Get LoggedIn User Detailed...

                $logged_in_user = User::where(['id' => Auth::id()])
                    ->with('employee')
                    ->first();

                $user_data = User::where(['id' => $user])->with('designation')->first();


                $data[$key]['fullname'] = $emp_data->fullname;
                $data[$key]['employee_code'] = $user_data->employee_code;
                $data[$key]['designation'] = $user_data->designation[0]->name;
                $data[$key]['State'] = $emp_state;
//                dd($emp);
                if(isset($emp->reporting_head)){
                    $data[$key]['Reporting head'] = $emp->reporting_head;
                }else{
                    $data[$key]['Reporting head'] = $logged_in_user->reporting_head;
                }
                if(isset($emp->reporting_head)){
                    $data[$key]['Reporting head UId'] = $emp->reporting_head_uId;
                }else{
                    $data[$key]['Reporting head UId'] = $logged_in_user->employee->employee_id;
                }

                $data[$key]['Verified'] = $verify_status;
                $data[$key]['Month'] = $prev_month . "-" . $month;



                while (strtotime($start_date) <= strtotime($end_month)) {
                    $j = 1;

                    $presentstatus = false;

                    if (count($punches) != 0) {
                        $allPunches = $punches;

                        foreach ($allPunches as $punch) {
                            $punch = (object)$punch;
                            if(isset($punch->on_date)) {
                                if ($start_date == date("Y-m-d", strtotime($punch->on_date))) {

                                    if ($punch->status == "Present") {

                                        $data[$key][$start_date] = "P";
                                        $presentstatus = true;
                                        break;
                                    } elseif ($punch->status == "Holiday") {
                                        $data[$key][$start_date] = "H";
                                        $presentstatus = true;
                                        break;
                                    } elseif ($punch->status == "Week-Off") {
                                        $data[$key][$start_date] = "WO";
                                        $presentstatus = true;
                                        break;
                                    } elseif ($punch->status == "Leave") {
                                        $punch_date = $punch->on_date;
//                                        $leaves = DB::table('applied_leaves as al')
//                                            ->join('leave_types as lt', 'lt.id', '=', 'al.leave_type_id')
//                                            ->where('al.user_id', $user)
//                                            ->whereMonth('al.from_date', date('m', strtotime($punch_date)))
//                                            ->orwhereMonth('al.to_date', date('m', strtotime($punch_date)))
//                                            ->where(function ($query) use ($punch_date) {
//                                                $query->where('al.from_date', '<=', $punch_date)
//                                                    ->Where('al.to_date', '>=', $punch_date);
//                                            })->orderBY('al.id', 'DESC')->first();

                                        $leaves = DB::table('applied_leaves as al')
                                            ->join('leave_types as lt', 'lt.id', '=', 'al.leave_type_id')
                                            ->where('al.user_id', $user)
                                            ->where(function ($query) use ($punch_date) {
                                                $query->where('al.from_date', '<=', $punch_date)
                                                    ->Where('al.to_date', '>=', $punch_date);
                                            })->orderBY('al.id', 'DESC')->first();


                                        if ($leaves) {
                                            if ($leaves->name == "Sick Leave") {
                                                $data[$key][$start_date] = "SL";
                                                $presentstatus = true;
                                                break;
                                            } elseif ($leaves->name == "Casual Leave") {
                                                $data[$key][$start_date] = "CL";
                                                $presentstatus = true;
                                                break;
                                            } elseif ($leaves->name == "Maternity Leave") {
                                                $data[$key][$start_date] = "ML";
                                                $presentstatus = true;
                                                break;
                                            } elseif ($leaves->name == "Paternity Leave") {
                                                $data[$key][$start_date] = "PL";
                                                $presentstatus = true;
                                                break;
                                            } elseif ($leaves->name == "Half Leave") {
                                                $data[$key][$start_date] = "HL";
                                                $presentstatus = true;
                                                break;
                                            } elseif ($leaves->name == "Compensatory Leave") {
                                                $data[$key][$start_date] = "CompL";
                                                $presentstatus = true;
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                            $j++;
                        }

                    }


                    if (!$presentstatus) {
                        if ($start_date < date("Y-m-d", strtotime($req['year'] . '-04-01'))) {
                            $data[$key][$start_date] = "NA";
                        } else {
                            $data[$key][$start_date] = "A";
                        }

                    }
                    if ($key == 0) {
                        $start_date = date("d-m-Y", strtotime($start_date));
                        $start_date_heading = date("d", strtotime($start_date));
//                        $heading_array[] = $start_date_heading;
                    }
                    $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));

                }

                $key = $key + 1;

            }



            for($i = 26; $i <= cal_days_in_month(CAL_GREGORIAN, $last_month, $start_year); $i++){
                $heading_array[] = $i;
            }

            for($i = 1; $i < 26; $i++){
                $heading_array[] = $i;
            }
            $data = collect($data);
            $export = new AttendanceExport($data, $heading_array);
            return Excel::download($export, 'attendance-punch.xlsx');
        }

// return $userDonthaveLocation;
        return view('attendances.consolidated_attendance_sheets')->with(['data'=>$data,'employees'=>$employees_po,'req'=>$req, 'login_user'=>$userid]);

    }  //end of function

    /*
     * For exporting attendance punches of an employee of a given month.
    */
    function exportPunches(Request $request)
    {
        $users = [];
        array_push($users,$request->id);
        $punches = Attendance::whereIn('user_id',$users)
            ->whereYear('on_date',$request->year)
            ->whereMonth('on_date',$request->month)
            ->has('attendancePunches')
            ->with('user')
            ->with('user.employee')
            ->with('user.designation')
            ->orderBy('on_date','ASC')
            ->get();

        $user = User::find($request->id);
        $data = [];

        if(!$punches->isEmpty()){
            foreach ($punches as $key => $value) {
                $value->attendance_punches = $value->attendancePunches()->orderBy('on_time','ASC')
                    ->get();

                $data[$key]['#'] = $key+1;
                $data[$key]['employee_code'] = $value->user->employee_code;
                $data[$key]['designation'] = "";

                if(!$value->user->designation->isEmpty()){
                    $data[$key]['designation'] = $value->user->designation[0]->name;
                }

                $data[$key]['fullname'] = $value->user->employee->fullname;
                $data[$key]['date'] = date("d/m/Y",strtotime($value->on_date));

                $count = $value->attendance_punches->count();
                $data[$key]['punch_count'] = $count;

                $data[$key]['first'] = $value->attendance_punches[0]->on_time;

                $data[$key]['second'] = "";
                if(!empty($value->attendance_punches[1])){
                    $data[$key]['second'] = $value->attendance_punches[1]->on_time;
                }

                $data[$key]['third'] = "";
                if(!empty($value->attendance_punches[2])){
                    $data[$key]['third'] = $value->attendance_punches[2]->on_time;
                }

                $data[$key]['fourth'] = "";
                if(!empty($value->attendance_punches[3])){
                    $data[$key]['fourth'] = $value->attendance_punches[3]->on_time;
                }

                $data[$key]['fifth'] = "";
                if(!empty($value->attendance_punches[4])){
                    $data[$key]['fifth'] = $value->attendance_punches[4]->on_time;
                }

                $data[$key]['sixth'] = "";
                if(!empty($value->attendance_punches[5])){
                    $data[$key]['sixth'] = $value->attendance_punches[5]->on_time;
                }

                $data[$key]['seventh'] = "";
                if(!empty($value->attendance_punches[6])){
                    $data[$key]['seventh'] = $value->attendance_punches[6]->on_time;
                }

                $data[$key]['last'] = $value->attendance_punches[$count-1]->on_time;
            }
            $data = collect($data);
            $export = new AttendancePunchExport($data);
            return Excel::download($export, 'attendance-punch.xlsx');
        }

    }//end of function

    /*
    * Calculate the total absents of an employee for a month.
    */
    function calculateAbsentAttendance($user_id,$year,$month)
    {
        if($month ==1){
            $year_val = $year-1;
            $prevmonth = 12;
        }else{
            $year_val = $year;
            $prevmonth = $month -1;
        }


        $first_date = date("Y-m-d",strtotime($year_val.'-'.$prevmonth.'-'.'26'));
        $end_date = date("Y-m-d",strtotime($year.'-'.$month.'-'.'25'));
        //echo $month_end_date = date('Y-m-t', strtotime($first_date));


        $period = CarbonPeriod::create($first_date, $end_date);

        $absent_count = 0;
        // Iterate over the period
        foreach ($period as $date) {
            $current_date = $date->format('Y-m-d');


            $attendance =  Attendance::where(['user_id'=>$user_id])
                ->where('on_date',$current_date)
                ->first();

            /* echo $user_id;
            print_r ($attendance);
            exit; */


            if(empty($attendance)){

                $leave = DB::table('applied_leaves as al')
                    ->where('al.from_date','<=',$current_date)
                    ->where('al.to_date','>=',$current_date)
                    ->where(['al.final_status'=>'1','al.user_id'=>$user_id])
                    ->first();


                if(empty($leave)){
                    $absent_count += 1;
                }


            }elseif($attendance->status == 'Absent') {
                $absent_count += 1;
            }
        }

        return $absent_count;
    }//end of function

    /*
    * Calculate the total late comings of an employee for a month.
    */
    function calculateLateAttendance($user_id,$year,$month)
    {
        $user = User::find($user_id);
        //$shift_from_time = date("Y-m-d")." ".$user->employeeProfile->shift->from_time;
        $shift_from_time = date("Y-m-d")."09:30:00";
        $attendances = Attendance::where(['user_id'=>$user_id])
            ->where('status','!=','Absent')
            ->whereYear('on_date',$year)
            ->whereMonth('on_date',$month)
            ->get();
        $late_count = 0;

        if(!$attendances->isEmpty()){
            foreach ($attendances as $attendance) {
                $att_date = $attendance->on_date;

                $att_day = date('w', strtotime($att_date));

                $exception_shift_info = ShiftException::where(['user_id'=>$user_id, 'week_day'=>$att_day])
                    ->first();

                if($exception_shift_info){

                    $shift_id = $exception_shift_info['shift_id'];

                    $shift_details = Shift::where(['id'=>$shift_id])
                        ->first();

                    $shift_from_time = date("Y-m-d")." ".$shift_details['from_time'];

                }else{

                    //$shift_from_time = date("Y-m-d")." ".$user->employeeProfile->shift->from_time;
                    $shift_from_time = date("Y-m-d")."09:30:00";

                }
                $punch = $attendance->attendancePunches()->orderBy('on_time','asc')->first();

                if(!empty($punch)){
                    if($attendance->status == 'Leave'){
                        $leave = AppliedLeave::where('from_date','<=',$attendance->on_date)
                            ->where('to_date','>=',$attendance->on_date)
                            ->where(['final_status'=>'1','user_id'=>$user_id])
                            ->first();

                        if(empty($leave)){
                            $attendance->status = 'Present';
                            $attendance->save();
                        }
                    }

                    $holiday = Holiday::where('holiday_from','<=',$attendance->on_date)
                        ->where('holiday_to','>=',$attendance->on_date)
                        ->where('isactive',1)
                        ->first();

                    if(strtotime(date("Y-m-d H:i",strtotime($punch->on_time))) > strtotime(date('Y-m-d H:i',strtotime($shift_from_time)))){
                        if($attendance->status == 'Leave' && $leave->secondary_leave_type == 'Half' && $leave->leave_half == 'First'){
                            continue;
                        }elseif($attendance->status == 'Leave' && $leave->secondary_leave_type == 'Short' && $leave->from_time == date('g:i A',strtotime($shift_from_time))){
                            continue;
                        }elseif($attendance->status == 'Leave' && $leave->secondary_leave_type == 'Full'){
                            continue;
                        }elseif(date("l",strtotime($attendance->on_date)) == 'Sunday' || !empty($holiday)){
                            continue;
                        }else{
                            $late_count += 1;
                        }
                    }
                }
            }
        }

        return $late_count;

    }//end of function

    /*
    * Calculate the total travel duration of an employee for a month.
    */
    function calculateTotalTravelDuration($travels, $req)
    {
        $difference = 0;

        foreach ($travels as $key => $travel) {
            $from_date = Carbon::create($travel->date_from);
            $to_date = Carbon::create($travel->date_to);

            if(date("Ym",strtotime($travel->date_to)) == date("Ym",strtotime($travel->date_from))){
                $difference += $from_date->diffInDays($to_date) + 1;
            }else{

                //if from_month is less than requested month and to_month is greater than requested month
                //it means full month travel
                if(date("m",strtotime($travel->date_from)) < $req['month'] && date("m",strtotime($travel->date_to)) > $req['month']){
                    $difference += cal_days_in_month(CAL_GREGORIAN, $req['month'], $req['year']);
                }

                //if from month is less than requested month and to month is equals to requested month
                //it means travel started in previous months, ending in current month
                if(date("m",strtotime($travel->date_from)) < $req['month'] && date("m",strtotime($travel->date_to)) == $req['month']){
                    $start_of_month = date('Y-m-01', strtotime($travel->date_to));
                    $start_of_month = Carbon::create($start_of_month);
                    $difference += $to_date->diffInDays($start_of_month) + 1;
                }

                //if from month is equals to requested month and to month is greater than requested month
                //it means travel started in current months, ending in future months
                if(date("m",strtotime($travel->date_from)) == $req['month'] && date("m",strtotime($travel->date_to)) > $req['month']){
                    $end_of_month = date('Y-m-t',strtotime($travel->date_from));
                    $end_of_month = Carbon::create($end_of_month);
                    $difference += $from_date->diffInDays($end_of_month) + 1;
                }
            }
        }

        return $difference;

    }//end of function

    /*
    * Get the data to store the attendance result of an employee of a particular month.
    */

    function getAttendanceResult($user,$on_date)
    {
        $total_days = (int)date("t",strtotime($on_date));
        $split_date = explode("-",$on_date);
        $year = $split_date[0];
        $month = $split_date[1];
        $holiday_counter = 0;
        $sunday_counter = 0;
        $holiday_array = [];
        $sunday_array = [];
        $data = [];


        for ($i=1; $i <= $total_days ; $i++) {
            if($i >= 10){
                $date = $year.'-'.$month.'-'.$i;
            }else{
                $date = $year.'-'.$month.'-'.'0'.$i;
            }
            $holiday = Holiday::where('holiday_from','<=',$date)
                ->where('holiday_to','>=',$date)
                ->where('isactive',1)
                ->first();

            if(!empty($holiday) && date("l",strtotime($date)) != "Sunday"){
                $holiday_counter += 1;
                $holiday_array[] = $date;
            }elseif (date("l",strtotime($date)) == "Sunday") {
                $sunday_counter += 1;
                $sunday_array[] = $date;
            }
        }

        $data['user_id'] = $user->id;
        $data['department'] = $user->employeeProfile->department->name;
        $data['employee_name'] = $user->employee->fullname;
        $data['employee_code'] = $user->employee_code;
        $data['on_date'] = $on_date;
        $data['workdays'] = $total_days - ($sunday_counter + $holiday_counter);
        $data['holidays'] = effectiveHolidays($user->employee->joining_date,$holiday_array);
        $data['week_offs'] = effectiveSundays($user->employee->joining_date,$sunday_array);
        $data['late'] = $this->calculateLateAttendance($user->id,$year,$month);
        $data['absent_days'] = $this->calculateAbsentAttendance($user->id,$year,$month); // - ($holiday_counter);
        $data['absent_days'] = ($data['workdays'] < $data['absent_days']) ? $data['workdays'] : $data['absent_days'];
        $data['absent_days'] = ($data['absent_days'] < 0) ? 0 : $data['absent_days'];

        $travels = TravelApproval::where(['isactive'=>1,'status'=>'approved','user_id'=>$user->id])
            ->where(function($query)use($year,$month){
                $query->orWhere(function($query)use($year,$month){
                    $query->whereYear('date_from',$year)
                        ->whereMonth('date_from',$month);
                })
                    ->orWhere(function($query)use($year,$month){
                        $query->whereYear('date_from',$year)
                            ->whereMonth('date_to',$month);
                    })
                    ->orWhere(function($query)use($year,$month){
                        $query->whereYear('date_from',$year)
                            ->whereMonth('date_from','<',$month)
                            ->whereMonth('date_to','>',$month);
                    });
            })
            ->get();

        $req['month'] = $month;
        $req['year'] = $year;
        if(!$travels->isEmpty()){
            $data['travel_days'] = $this->calculateTotalTravelDuration($travels, $req);
        }else{
            $data['travel_days'] = '0';
        }



        $data['paid_leaves'] = DB::table('applied_leave_segregations as als')
            ->join('applied_leaves as al','al.id','=','als.applied_leave_id')
            ->where(['al.final_status'=>'1','al.user_id'=>$user->id])
            ->where(function($query)use($req){
                $query->whereYear('als.to_date',$req['year'])
                    ->whereMonth('als.to_date',$req['month']);
            })
            ->sum('als.paid_count');

        $data['unpaid_leaves'] = DB::table('applied_leave_segregations as als')
            ->join('applied_leaves as al','al.id','=','als.applied_leave_id')
            ->where(['al.final_status'=>'1','al.user_id'=>$user->id])
            ->where(function($query)use($req){
                $query->whereYear('als.to_date',$req['year'])
                    ->whereMonth('als.to_date',$req['month']);
            })
            ->sum('als.unpaid_count');

        $data['total_present_days'] = ($data['workdays']+$data['holidays']+$data['week_offs']) - ($data['absent_days'] + $data['unpaid_leaves']);
        //$data['absent_days'] = ($data['absent_days'] < 0) ? 0 : $data['absent_days'];
        return $data;
    }//end of function


    /*
    save in attendanceresult after verification
    */
    function getAttendanceResult_info($user,$on_date, $total_days)
    {
        //$total_days = (int)date("t",strtotime($on_date));
        $split_date = explode("-",$on_date);
        $year = $split_date[0];
        $month = $split_date[1];

        $holiday_counter = 0;
        $weekOff_counter = 0;
        $leave_counter = 0;
        $absent_counter = 0;
        $present_counter = 0;

        $holiday_array = [];
        $weekOff_array = [];
        $leave_array = [];
        $absent_array = [];
        $present_array = [];
        $data = [];

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
        $dates_count=1;
        while (strtotime($date1) <= strtotime($date2)) {

            $present = Attendance::where('on_date',$date1)
                ->where('user_id',$user->id)
                ->where('status','Present')
                ->first();

            if(!empty($present)){
                $present_counter += 1;
                $present_array[] = $date1;
            }


            $holiday = Attendance::where('on_date',$date1)
                ->where('user_id',$user->id)
                ->where('status','Holiday')
                ->first();

            if(!empty($holiday)){
                $holiday_counter += 1;
                $holiday_array[] = $date1;
            }

            $Week_off = Attendance::where('on_date',$date1)
                ->where('user_id',$user->id)
                ->where('status','Week-Off')
                ->first();

            if(!empty($Week_off)){
                $weekOff_counter += 1;
                $weekOff_array[] = $date1;
            }

            $leave = Attendance::where('on_date',$date1)
                ->where('user_id',$user->id)
                ->where('status','Leave')
                ->first();

            if(!empty($leave)){
                $leave_counter += 1;
                $leave_array[] = $date1;
            }

            $absent = Attendance::where('on_date',$date1)
                ->where('user_id',$user->id)
                ->where('status','Absent')
                ->first();

            if(!empty($absent)){
                $absent_counter += 1;
                $absent_array[] = $date1;
            }

            $date1 = date ("Y-m-d", strtotime("+1 days", strtotime($date1)));
        }



        $data['user_id'] = $user->id;
        $data['department'] = $user->employeeProfile->department->name;
        $data['employee_name'] = $user->employee->fullname;
        $data['employee_code'] = $user->employee_code;
        $data['on_date'] = $on_date;
        $data['workdays'] = $total_days - ($weekOff_counter + $holiday_counter);
        //$data['holidays'] = effectiveHolidays($user->employee->joining_date,$holiday_array);
        $data['holidays'] = $holiday_counter;
        //$data['week_offs'] = effectiveSundays($user->employee->joining_date,$sunday_array);
        $data['week_offs'] = $weekOff_counter;
        //$data['late'] = $this->calculateLateAttendance($user->id,$year,$month);
        $data['late'] = 0;
        //$data['absent_days'] = $this->calculateAbsentAttendance($user->id,$year,$month); // - ($holiday_counter);
        $absent_days = ($data['workdays'] - $present_counter - $leave_counter );
        $data['absent_days'] = $absent_days + $absent_counter;
        $data['absent_days'] = ($data['absent_days'] < 0) ? 0 : $data['absent_days'];

        $date1 = $start_year.'-'.$startmonth.'-'.'26';
        $date2 = $curr_year.'-'.$curr_month.'-'.'25';

        $data['paid_leaves'] = DB::table('applied_leave_segregations as als')
            ->join('applied_leaves as al','al.id','=','als.applied_leave_id')
            ->where(['al.final_status'=>'1','al.user_id'=>$user->id])
            ->where(function($query)use($date1, $date2){
                $query	->whereBetween('als.to_date', [$date1, $date2])
                    ->orWhereBetween('als.from_date', [$date1, $date2]);
            })
            ->sum('als.paid_count');

        $data['unpaid_leaves'] = DB::table('applied_leave_segregations as als')
            ->join('applied_leaves as al','al.id','=','als.applied_leave_id')
            ->where(['al.final_status'=>'1','al.user_id'=>$user->id])
            ->where(function($query)use($date1, $date2){
                $query	->whereBetween('als.to_date', [$date1, $date2])
                    ->orWhereBetween('als.from_date', [$date1, $date2]) ;
            })
            ->sum('als.unpaid_count');


        $data['total_present_days'] = ($data['workdays']+$data['holidays']+$data['week_offs']) - ($data['absent_days'] + $data['unpaid_leaves']);
        //$data['absent_days'] = ($data['absent_days'] < 0) ? 0 : $data['absent_days'];
        return $data;
    }



    /*
    * Verify the attendance of an employee of a particular month, create late coming leaves.
    * Also create an entry in the attendance results table.
    */

    function verifyMonthAttendance(Request $request){

//        return date("Y-m-d", strtotime("+1 month", strtotime('2020-11-25')));
//        dd($request->all());

        //$on_date =  date("Y-m-d", strtotime( date( "Y-m-d", strtotime( $request->on_date ) ) . "+1 month" ) );

        $on_date =  date("Y-m-d", strtotime( date( "Y-m-d", strtotime( $request->on_date ) )) );
        //dump($on_date);

        $user = User::where('id',$request->user_id)
            ->with('employee')
            ->with('employeeProfile')
            ->first();

        $user_id = $request->user_id;


        $get_user_designation = DB::table('designation_user as du')
            ->where('du.user_id','=',$user_id)
            ->select('du.id', 'du.user_id','du.designation_id')
            ->first();

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
        //dump($date1);
        //dump($date2);
        //dd("+++++");
        $dates_count=1;
        while (strtotime($date1) <= strtotime($date2)) {

            $attendance = $user->attendances()
                ->where(['on_date'=>$date1])
                ->has('attendancePunches')
                ->with('attendancePunches')
                ->first();

            $onAttendanceHoliday = Attendance::where(['on_date'=> $date1,'user_id' =>$user_id])->where('status', 'Holiday')->first();

//            return $date1;
            $attendance_status = $user->attendances()
                ->where(['on_date'=>$date1])
                ->first();
            // print_r('dumping attendance status');
            // dump($attendance_status);

            /*if($date1=='2020-10-02'){
                echo "##############";
                dump($onAttendanceHoliday);
            }*/
//            return $onAttendanceHoliday;

            if(strtotime($user->employee['joining_date']) <= strtotime($date1)) {
                if ((is_null($attendance_status) || empty($attendance_status)) && date('D', strtotime($date1)) != 'Sun' && is_null($onAttendanceHoliday)) {
                    $result['error'] = "Some days are missing with any of status for this month " . $date1;
                    return $result;
                }
            }

            $date1 = date ("Y-m-d", strtotime("+1 days", strtotime($date1)));
            //print_r("duumping date1");
            //dump($date1);
            $dates_count++;
        }

        $verification = $user->attendanceVerifications()
            ->where(['on_date'=>$on_date])
            ->first();

        // dd($verification);

        //if(empty($verification) && strtotime(date("Y-m-d")) ==strtotime($on_date)){ //do =
        if(empty($verification)){
            $verification_create = $user->attendanceVerifications()->create(['manager_id'=>$request->manager_id,'on_date'=>$on_date]);

            $verification_exist= $user->attendanceVerifications()->where(['on_date'=>$on_date])->first();

            if($verification_exist){
                $verification_exist->isverified = 1;
                $verification_exist->save();
            }

            $result['error'] = "";
            $dates_count = $dates_count-1;
        }

        //dd("Break");


        $check_result = $user->attendanceResults()->where('on_date',$on_date)->first();
        if(empty($check_result)){
            $result = $this->getAttendanceResult_info($user, $on_date, $dates_count);
            $user->attendanceResults()->create($result);
        }else{
            $result = $this->getAttendanceResult_info($user,$on_date, $dates_count);
            $check_result->update($result);
        }

        $current_month = date("n");

        $leave_status_current= LeaveDetail::where('user_id', $user_id)
            ->whereMonth('month_info',$current_month)
            ->first();

//        return $on_date;
//        return $leave_status_current;

        if(isset($leave_status_current)){

            $leave_status_prev = LeaveDetail::where('user_id', $user_id)
                ->whereMonth('month_info', $current_month-1)
                ->first();

            /* $accumlated_casual = $leave_status_prev->accumalated_casual_leave +2;
             $accumlated_sick = $leave_status_prev->accumalated_sick_leave +1;

             $balance_casual  = $leave_status_prev->balance_casual_leave - 2;
             $balance_sick = $leave_status_prev->balance_sick_leave - 1;

             $balance_maternity = $leave_status_prev->balance_maternity_leave;
             $balance_paternity = $leave_status_prev->balance_paternity_leave;

             $unpaid_casual = $leave_status_prev->unpaid_casual;
             $paid_casual = $leave_status_prev->paid_casual;
             $unpaid_sick = $leave_status_prev->unpaid_sick;
             $paid_sick = $leave_status_prev->paid_sick;
             $compensatory_count = $leave_status_prev->compensatory_count;
           */

            // Change $leave_status_prev to $leave_status_current to get current month leave detail

            if($get_user_designation->designation_id  == 4){
                $accumlated_casual = $leave_status_current->accumalated_casual_leave +1.5;
                $accumlated_sick = $leave_status_current->accumalated_sick_leave;
            }else{
                $accumlated_casual = $leave_status_current->accumalated_casual_leave +2;
                $accumlated_sick = $leave_status_current->accumalated_sick_leave;
            }

            $balance_casual  = $leave_status_current->balance_casual_leave;
            $balance_sick = $leave_status_current->balance_sick_leave;

            $balance_maternity = $leave_status_current->balance_maternity_leave;
            $balance_paternity = $leave_status_current->balance_paternity_leave;

            $unpaid_casual = 0;
            $paid_casual = 0;
            $unpaid_sick = 0;
            $paid_sick = 0;
            $compensatory_count = 0;

//            return $date2;
            // Next Month Date
            $nextMonth = date("Y-m-d", strtotime("+1 month", strtotime($date2)));
            $nextMonth = date("Y-m-d", strtotime("+1 day", strtotime($nextMonth)));

            // Create Accumulation of leave for next month
            $approval_data = [
                'user_id' => $user_id,
                'month_info' => $nextMonth,
                'accumalated_casual_leave' => $accumlated_casual,
                'accumalated_sick_leave' => $accumlated_sick,
                'balance_casual_leave' => $balance_casual,
                'balance_sick_leave' => $balance_sick,
                'balance_maternity_leave' => $balance_maternity,
                'balance_paternity_leave' => $balance_paternity,
                'unpaid_casual' => $unpaid_casual,
                'paid_casual' => $paid_casual,
                'unpaid_sick' => $unpaid_sick,
                'paid_sick' => $paid_sick,
                'compensatory_count' => $compensatory_count,
                'isactive' => 1
            ];

            LeaveDetail::create($approval_data);
            $result['error'] = "";
        }


        /* }else{
            $result['error'] = "You can verify current month's attendance only on month end last day.";
        }    */

        return $result;
    }
    /*
    * Apply the system generated late coming leave.
    */
    function applyLeave($user,$leave_type,$number_of_days,$late_dates,$last_month_end_date)
    {
        $leave_data = [
            'leave_type_id' => $leave_type->id,
            'country_id' => 1,
            'state_id' => 28,  //Punjab
            'city_id' => 1110, //Mohali
            'reason' => 'Deducted by system due to late comings on '.implode(", ",$late_dates),
            'number_of_days' => $number_of_days,
            'from_time' => "",
            'to_time' => "",
            'mobile_country_id' => 1,
            'mobile_number' => $user->employee->mobile_number,
            'from_date' => $last_month_end_date,
            'to_date' => $last_month_end_date,
            'excluded_dates' => "",
            'tasks' => "",
            'leave_half' => '',
            'final_status' => '1'
        ];

        if($number_of_days == 0.25){
            $leave_data['secondary_leave_type'] = 'Short';
        }elseif($number_of_days == 0.5){
            $leave_data['secondary_leave_type'] = 'Half';
        }else{
            $leave_data['secondary_leave_type'] = 'Full';
        }

        $applied_leave = $user->appliedLeaves()->create($leave_data);

        $segregation_data = [
            'from_date' => $leave_data['from_date'],
            'to_date' => $leave_data['to_date'],
            'number_of_days' => $number_of_days,
            'paid_count' => '0',
            'unpaid_count' => '0',
            'compensatory_count' => '0'
        ];
        $applied_leave->appliedLeaveSegregations()->create($segregation_data);

        $approval_data = [
            'user_id' => $user->id,
            'supervisor_id' => $user->userManager->manager_id,
            'priority' => '1',
            'leave_status' => '1'
        ];
        $applied_leave->appliedLeaveApprovals()->create($approval_data);

        $notification_data = [
            'sender_id' => 1,
            'receiver_id' => $user->id,
            'label' => 'Leave Deduction',
            'read_status' => '0'
        ];
        $notification_data['message'] = "Your leaves have been deducted due to late comings. Please check your applied leaves section for more details.";
        $applied_leave->notifications()->create($notification_data);

        return $applied_leave;

    }//end of function

    /*
    * Get the attendance change request form.
    */
    function requestChange()
    {
        return view('attendances.request_change_form');

    }//end of function

    /*
    * Ajax request to check for a valid date status from attendance change request form.
    */
    function checkDateStatus(Request $request)
    {
        $user = Auth::user();
        $result = ['error'=>""];

        $hod = $user->leaveAuthorities()->where(['priority'=>'2','isactive'=>1])->first();
        if(empty($hod)){
            $result['error'] .= "You do not have a HOD. Please contact the HR.<br>";
        }

        if(!empty($request->dates)){
            foreach ($request->dates as $key => $date) {
                $date = date("Y-m-d",strtotime($date));
                $holiday = Holiday::where('holiday_from','<=',$date)
                    ->where('holiday_to','>=',$date)
                    ->where('isactive',1)
                    ->first();

                if(!empty($holiday)){
                    $result['error'] .= date("d/m/Y",strtotime($date))." is marked as a holiday.<br>";
                }else{
                    $leave = AppliedLeave::where('from_date','<=',$date)
                        ->where('to_date','>=',$date)
                        ->where(['final_status'=>'1','user_id'=>$user->id])
                        ->first();

                    if(!empty($leave) && $leave->secondary_leave_type == 'Full'){
                        $result['error'] .= date("d/m/Y",strtotime($date))." is marked as a leave day.<br>";
                    }else{
                        $travel = TravelApproval::where(['isactive'=>1,'status'=>'approved','user_id'=>$user->id])
                            ->where('date_from','<=',$date)
                            ->where('date_to','>=',$date)
                            ->first();

                        if(!empty($travel)){
                            $result['error'] .= date("d/m/Y",strtotime($date))." is marked as a travel day.<br>";
                        }else{
                            $attendance = Attendance::where(['user_id'=>$user->id,'on_date'=>$date,'status'=>'Present'])->first();

                            if(!empty($attendance)){
                                //$result['error'] .= date("d/m/Y",strtotime($date))." is marked as a Present.<br>";
                            }else{
                                $change_date = AttendanceChangeDate::where(['user_id'=>$user->id,'on_date'=>$date])->whereHas('attendanceChange',function(Builder $query){
                                    $query->where('isactive',1);
                                })->first();

                                if(!empty($change_date)){
                                    $check_approval = AttendanceChangeApproval::where(['attendance_change_id'=>$change_date->attendance_change_id,'status'=>'2'])->first();

                                    if(empty($check_approval)){
                                        $result['error'] .= date("d/m/Y",strtotime($date))." is already marked as a change request.<br>";
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $result;

    }//end of function

    /*
    * Save the attendance change request of a user in database. Send for approval to the HOD.
    */
    function saveChangeRequest(Request $request)
    {
        $request->validate([
            'remarks' => 'required',
            'dates' => 'required',
            'select_option' => 'required'
        ]);

        $dates = explode(",",$request->dates);
        //////////////Checks///////////////////
        $current_date = date('Y-m-d');
        $restriction_date = config('constants.restriction.applyLeave');
        $current_month_start_date = date("Y-m-01");

        $request_date = date('Y-m-d', strtotime($request->dates));
        $prev_two_days_date = date('Y-m-d', strtotime($current_date. ' - 2 days'));

        if((strtotime($request_date) > strtotime($current_date)) || (strtotime($request_date)<strtotime($prev_two_days_date))){

            $error = "You cannot request for an attendance change of dates before 2 days. ";
            return redirect()->back()->with('error',$error);

        }

        if(strtotime($current_date) > strtotime($restriction_date)){
            if(strtotime(date("Y-m-d",strtotime($dates[0]))) < strtotime($current_month_start_date)){
                $restriction_error = "You cannot request for an attendance change for a previous month's date now.";
                return redirect()->back()->with('error',$restriction_error);
            }
        }

        $user = Auth::user();
        $hod = $user->leaveAuthorities()->where(['priority'=>'2','isactive'=>1])->first();
        $next_user = User::permission('it-attendance-approver')
            ->whereHas('employeeProfile',function(Builder $query){
                $query->where('department_id',2); //IT
            })
            ->first();

        if(empty($hod)){
            $error = 'You do not have a HOD. Please contact the HR.';
            return redirect()->back()->with('error',$error);
        }else{
            $change_data = ['remarks'=> $request->remarks];
            $attendance_change = $user->attendanceChanges()->create($change_data);

            $change_date_data = [
                'user_id' => $user->id
            ];


            foreach ($dates as $key => $value) {
                $change_date_data['on_date'] = date("Y-m-d",strtotime($value));

                if($request->intime){
                    $change_date_data['on_time'] = date("H:i:s",strtotime($request->intime));
                }

                if($request->outtime){
                    $change_date_data['out_time'] = date("H:i:s",strtotime($request->outtime));
                }
                $attendance_change->attendanceChangeDates()->create($change_date_data);
            }

            $change_approval_data = [
                'user_id' => $user->id,
                'manager_id' => $hod->manager_id,
                'status' => '0',
                'priority' => '1'
            ];

            $attendance_change->attendanceChangeApprovals()->create($change_approval_data);
            $notification_data = [
                'sender_id' => $user->id,
                'receiver_id' => $hod->manager_id,
                'label' => 'Change Attendance Application',
                'read_status' => '0'
            ];
            $notification_data['message'] = $user->employee->fullname." has requested for a change in attendance.";
            $attendance_change->notifications()->create($notification_data);

            $title = 'Change Attendance Application';
            $body = $notification_data['message'];
            pushNotification($hod->manager_id, $title, $body);

            return redirect('attendances/requested-changes')->with('success','Change request sent successfully.');
        }

    }//end of function

    /*
    * Get the list of all the attendance change requests made by a person.
    */
    function requestedChanges($final_status = "approved")
    {
        if($final_status == "not-approved"){
            $status = 0;
        }elseif ($final_status == "approved") {
            $status = 1;
        }

        $user = Auth::user();

        $changes = $user->attendanceChanges()->where(['final_status'=>$status])
            ->with('attendanceChangeDates')
            ->with('attendanceChangeApprovals')
            ->orderBy('created_at','DESC')
            ->get();

        if(!$changes->isEmpty()){
            foreach ($changes as $key => $change) {
                $rejected = $change->attendanceChangeApprovals()->where('status','2')->first();
                if(!empty($rejected)){
                    $change->is_rejected = true;
                }else{
                    $change->is_rejected = false;
                }
            }
        }

        return view('attendances.list_requested_changes')->with(['changes'=>$changes,'final_status'=>$final_status]);

    }//end of function

    /*
    * Cancel the attendance change request before any action is taken by the HOD.
    */
    function cancelRequestedChange($attendance_change_id)
    {
        $user = Auth::user();
        $change = $user->attendanceChanges()->where('id',$attendance_change_id)->first();

        if(empty($change)){
            return redirect()->back()->with('cannot_cancel_error','You cannot cancel somebodies else request');
        }else{
            if($change->attendanceChangeApprovals[0]->status != "0"){
                return redirect()->back()->with('cannot_cancel_error','Concerned authority has taken an action, you cannot cancel it now.');
            }else{
                $change->final_status = 0;
                $change->isactive = 0;
                $change->save();

                return redirect()->back();
            }
        }
    }//end of function

    /*
    * Get the list of all the attendance change requests send to a person for approval.
    */
    function changeApprovals($approval_status = "pending")
    {
        if($approval_status == "pending"){
            $status = '0';
        }elseif ($approval_status == "approved") {
            $status = '1';
        }elseif ($approval_status == "rejected") {
            $status = '2';
        }

        $user = Auth::user();
        $approvals = AttendanceChangeApproval::where(['manager_id'=>$user->id,'status'=>$status])
            ->whereHas('attendanceChange', function(Builder $query){
                $query->where(['isactive'=>1]);
            })
            ->with('attendanceChange.attendanceChangeDates')
            ->with('user.employee')
            ->orderBy('created_at','DESC')
            ->get();

        if(!$approvals->isEmpty()){
            foreach ($approvals as $key => $approval) {
                $user_id = $approval->user_id;
                foreach ($approval->attendanceChange->attendanceChangeDates as $key2 => $value) {
                    $attendance = Attendance::where(['on_date'=>$value->on_date,'user_id'=>$user_id])
                        ->first();

                    if(!empty($attendance) && !$attendance->attendancePunches->isEmpty()){
                        $value->first_punch = $attendance->attendancePunches()
                            ->orderBy('on_time','asc')
                            ->value('on_time');

                        $value->last_punch = $attendance->attendancePunches()
                            ->orderBy('on_time','desc')
                            ->value('on_time');

                        $value->first_punch = date("h:i A",strtotime($value->first_punch));
                        $value->last_punch = date("h:i A",strtotime($value->last_punch));

                        if($value->last_punch == $value->first_punch){
                            $value->last_punch = "";
                        }
                    }else{
                        $value->first_punch = "";
                        $value->last_punch = "";
                    }



                }
            }
        }

        return view('attendances.list_change_approvals')->with(['approvals'=>$approvals,'selected_status'=>$approval_status]);

    }//end of function

    /*
    * Save the concerned authorities action taken on any attendance change request. Then finally add
    * the punch to database if approved on all levels or send it for further approval.
    */
    function changeAttendance(Request $request)
    {
        $request->validate([
            'comment' => 'required'
        ]);
        $session_message='';
        $attendance_change_approval = AttendanceChangeApproval::where('id',$request->acaId)
            ->with('attendanceChange')
            ->with('user')
            ->with('manager')
            ->first();

        $user = $attendance_change_approval->user;
        $attendance_change = $attendance_change_approval->attendanceChange;
        $attendance_dates = $attendance_change->attendanceChangeDates;

        //////////////Checks///////////////////
        $current_date = date('Y-m-d');
        $restriction_date = config('constants.restriction.approveLeave');
        $current_month_start_date = date("Y-m-01");

        if(strtotime($current_date) > strtotime($restriction_date)){
            if(strtotime($attendance_dates[0]->on_date) < strtotime($current_month_start_date)){
                $restriction_error = "You cannot approve an attendance change request for a previous month's date now.";
                return redirect()->back()->with('error',$restriction_error);
            }
        }

        if($attendance_change_approval->priority == '1'){  //HOD
            if($request->status == '1'){
                $next_user = User::permission('it-attendance-approver')
                    ->whereHas('employeeProfile',function(Builder $query){
                        $query->where('department_id',2); //IT
                    })
                    ->first();

                if(!empty($next_user)){
                    $change_approval_data = [
                        'user_id' => $user->id,
                        'manager_id' => $next_user->id,
                        'status' => '0',
                        'priority' => '2'
                    ];
                    $attendance_change->attendanceChangeApprovals()->create($change_approval_data);
                    $notification_data = [
                        'sender_id' => $user->id,
                        'receiver_id' => $next_user->id,
                        'label' => 'Change Attendance Application',
                        'read_status' => '0'
                    ];
                    $notification_data['message'] = $user->employee->fullname." has requested for a change in attendance.";
                    $attendance_change->notifications()->create($notification_data);
                }
                $hod_approval = $attendance_change->attendanceChangeApprovals()->where(['priority'=>'1'])->first();
                foreach ($attendance_dates as $key => $value) {
                    $attendance = $user->attendances()->where(['on_date'=>$value->on_date])->first();
                    if(empty($attendance)){
                        $data = [
                            'on_date' => $value->on_date,
                            'status' => 'Present'
                        ];
                        $attendance = $user->attendances()->create($data);
                    }else{
                        $attendance->status = 'Present';
                        $attendance->save();
                    }

                    if($value->on_time){
                        $attendance->attendancePunches()->create(['on_time'=>$value->on_time,'punched_by'=>$hod_approval->manager_id]);
                    }

                    if($value->out_time){
                        $attendance->attendancePunches()->create(['on_time'=>$value->out_time,'punched_by'=>$hod_approval->manager_id]);
                    }
                }

                $attendance_change->final_status = 1;
                $attendance_change->save();

                $title = 'Attandance Change Approved';
                $body = 'Your attendance change request for '.date('d/m/Y',strtotime($attendance_dates[0]->on_date)).' has been approved.';
                pushNotification($attendance_change_approval->user_id, $title, $body);

                $session_message='data updated successfully';

            }else{
                $title = 'Attendance Change Rejected';
                $body = 'Your attendance change request for '.date('d/m/Y',strtotime($attendance_dates[0]->on_date)).' has been rejected.';
                pushNotification($user->id, $title, $body);
            }
        }else{ //IT user
            echo 'Not allowed';
            exit;
            //below code is not required as they dont have any IT approval so after some time its better to remove this code.
            //comented on  20-3-2020
            if($request->status == '1'){
                $hod_approval = $attendance_change->attendanceChangeApprovals()->where(['priority'=>'1'])->first();
                foreach ($attendance_dates as $key => $value) {
                    $attendance = $user->attendances()->where(['on_date'=>$value->on_date])->first();
                    if(empty($attendance)){
                        $data = [
                            'on_date' => $value->on_date,
                            'status' => 'Present'
                        ];
                        $attendance = $user->attendances()->create($data);
                    }else{
                        $attendance->status = 'Present';
                        $attendance->save();
                    }

                    if($value->on_time){
                        $attendance->attendancePunches()->create(['on_time'=>$value->on_time,'punched_by'=>$hod_approval->manager_id]);
                    }

                    if($value->out_time){
                        $attendance->attendancePunches()->create(['on_time'=>$value->out_time,'punched_by'=>$hod_approval->manager_id]);
                    }
                }

                $attendance_change->final_status = 1;
                $attendance_change->save();

                $title = 'Attandance Change Approved';
                $body = 'Your attendance change request for '.date('d/m/Y',strtotime($attendance_dates[0]->on_date)).' has been approved.';
                pushNotification($attendance_change_approval->user_id, $title, $body);
            }else{
                $title = 'Attendance Change Rejected';
                $body = 'Your attendance change request for '.date('d/m/Y',strtotime($attendance_dates[0]->on_date)).' has been rejected.';
                pushNotification($user->id, $title, $body);
            }
        }

        $message_data = [
            'sender_id' => $attendance_change_approval->manager_id,
            'receiver_id' => $attendance_change_approval->user_id,
            'label' => 'Change Attendance Comment',
            'message' => $request->comment,
            'read_status' => '0'
        ];
        $attendance_change->messages()->create($message_data);

        $attendance_change_approval->status = $request->status;                                             $attendance_change_approval->save();

        return redirect()->back()->with('success', $session_message);

    }//end of function

    /*
    * Ajax request to get the list of all approval history messages to show in a modal.
    */
    function listComments(Request $request)
    {
        $attendance_change = AttendanceChange::find($request->attendance_change_id);
        $messages = $attendance_change->messages()
            ->where('label','Change Attendance Comment')
            ->orderBy('created_at','DESC')
            ->with('sender.employee:id,user_id,fullname')
            ->with('receiver.employee:id,user_id,fullname')
            ->get();

        $view = View::make('attendances.list_messages',['data' => $messages]);
        $contents = $view->render();

        return $contents;

    }//end of function

    /**********add leave and approve from calander by reporting manager***********/

    function addLeave(Request $request){

        $t_date = date("Y-m-d",strtotime($request->to_date));

        $userid = $request->user_id;

        $user = User::where(['id'=>$userid])
            ->with('employee')
            ->with('userManager')
            ->first();

        $arr_underlaying_emp = array();

        $designation_user_data = DB::table('designation_user as du')

            ->where('du.user_id','=',$userid)

            ->select('du.id', 'du.user_id','du.designation_id')->first();

        $designation_user = $designation_user_data->designation_id;



        //check for po reporting manager exist
        if($designation_user==3 ){

            $user_state = EmployeeProfile::where(['user_id' => $designation_user_data->user_id])
                ->first();

            $user_state_id = $user_state->state_id;

            $employees_under_states = EmployeeProfile::where(['state_id' => $user_state_id])
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

        //check for po-IT reporting manager exist
        if($designation_user==5 ){
            $user_state = EmployeeProfile::where(['user_id' => $designation_user_data->user_id])
                ->first();

            $user_state_id = $user_state->state_id;
            $employees_under_states = EmployeeProfile::where(['state_id' => $user_state_id])
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

        //check for vccm reporting manager exist
        if($designation_user==4){

            $user_district = DB::table('location_user as lu')

                ->where('lu.user_id','=',$designation_user_data->user_id)

                ->select('lu.id', 'lu.user_id','lu.location_id')->first();

            $user_district_id = $user_district->location_id;

            $employeesDistrict = DB::table('location_user as lu')

                ->where(['lu.location_id'=>$user_district_id])

                ->select('lu.id', 'lu.user_id','lu.location_id')->get();

            if($employeesDistrict)
            {
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

        //check for spo reporting manager exist
        if($designation_user==2){


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
//            return redirect()->back()->with('error',$manager_error);
        }


        $check_dates =  [
            'from_date' => $request->on_date,
            'to_date' => $request->on_date,
            'isactive' => 1
        ];

        $already_applied_leave = $user->appliedLeaves()->where($check_dates)->first();

        if(!empty($already_applied_leave)){
            $unique_error = "You have already applied for leave on the given dates.";
            return redirect('leaves/apply-leave')->with('leaveError',$unique_error);
        }
        if($request->secondaryLeaveType=="Half"){
            $days = 0.5;

        }else{
            $days = 1;

        }


        if($request->leaveTypeId == '1' || $request->leaveTypeId == '2' || $request->leaveTypeId == '5'){
            $leave_data = [
                'leave_type_id' => $request->leaveTypeId,
                'reason' => $request->reasonLeave,
                'number_of_days' => $days,
                "secondary_leave_type" => $request->secondaryLeaveType,
                'from_date' => $request->on_date,
                'to_date' => $request->on_date,
                'final_status' => '1'
            ];

            $segregation_data = [
                'from_date' => $request->on_date,
                'to_date' => $request->on_date,
                'number_of_days' => $days,
                'paid_count' => '0',
                'unpaid_count' => '0',
                'compensatory_count' => '0'
            ];
        }

        if($request->leaveTypeId == '7' || $request->leaveTypeId == '4' ){

            $leave_data = [
                'leave_type_id' => $request->leaveTypeId,
                'reason' => $request->reasonLeave,
                'number_of_days' => $request->noDays,
                "secondary_leave_type" => $request->secondaryLeaveType,
                'from_date' => $request->on_date,
                'to_date' => $t_date,
                'final_status' => '1'
            ];

            $segregation_data = [
                'from_date' => $request->on_date,
                'to_date' => $t_date,
                'number_of_days' =>  $request->noDays,
                'paid_count' => '0',
                'unpaid_count' => '0',
                'compensatory_count' => '0'
            ];

        }

        $applied_leave = $user->appliedLeaves()->create($leave_data);



        // comment by hitesh...
        $applied_leave->appliedLeaveSegregations()->create($segregation_data);

        $reporting_manager = $arr_underlaying_emp[0]->user_id;

        $approval_data = [
            'user_id' => $userid,
            'supervisor_id' => $reporting_manager,
            'priority' => '1',
            'leave_status' => '1'
        ];
        $applied_leave->appliedLeaveApprovals()->create($approval_data);

        if($applied_leave->final_status == '1'){

            //Attendance::create(['user_id'=>$userid,'on_date'=>$request->on_date,'status'=>"Leave"]);

            // comment by hitesh...

            $excluded_dates = $applied_leave->excluded_dates;
            $excluded_date = explode(",",$excluded_dates);

            $i=0;

            if(!empty($request->to_date)){

                while (strtotime($request->on_date) <= strtotime($t_date)) {

                    Attendance::create(['user_id'=>$userid,'on_date'=>$request->on_date,'status'=>"Leave"]);

                    $request->on_date = date ("Y-m-d", strtotime("+1 days", strtotime($request->on_date)));
                    $i++;
                }
            }else{
                //  when po add casual or sick leave..
                $Attendance_entry = Attendance::updateOrCreate(['user_id' =>  $userid, 'on_date'=>$request->on_date], ['status' => "Leave"] );
            }
        }

        $probation_data = array();

        leaveRelatedCalculations($probation_data,$applied_leave);

        /************************notify ************************/

        $message = "Your applied leave, of ".date('d/m/Y',strtotime($applied_leave->from_date)).' has been approved.';

        $user_data = Employee::where(['user_id'=>$userid])
            ->with('user')->first();

        //$mail_data['to_email'] = $user_data->user->email;
        $mail_data['to_email'] = "xeam.richa@gmail.com";

        $mail_data['subject'] = "Leave Approved";
        $mail_data['message'] = $message;
        $this->sendGeneralMail($mail_data);

        //to manager

        $reporting_manager_data = Employee::where(['user_id'=>$reporting_manager])
            ->with('user')->first();
        //$mail_data['to_email'] = $reporting_manager_data->user->email;
        $mail_data['to_email'] = "xeam.richa@gmail.com";
        $mail_data['subject'] = "Leave Application Approval";
        $mail_data['message'] = $user->employee->fullname." has applied for a leave. Please took for an approval.";
        $mail_data['fullname'] = $reporting_manager_data->fullname;

        return redirect($request->url)->with('leave_success','Leave has been added successfully.');

    }

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

    function addAttendance(){
        $users_po_vccm = User::with('designation')->get();
        /*echo"<PRE>";
        print_r($users_po_vccm);
        exit;*/
        foreach($users_po_vccm as $user){
            if(isset($user->designation[0])){
                $designation = $user->designation[0]->id;
            }else{
                echo $user->id;
                echo"<br/>";
                continue;
            }

            if($designation!=1 AND $designation!=2 AND $designation!=6){
                $emp_exist = Attendance::where(['user_id'=>$user->id, 'on_date'=>'2020-08-19'])->first();
                if(!$emp_exist){
                    $attendance_data = [
                        'user_id' => $user->id,
                        'on_date' => "2020-08-19",
                        'status' => "Present"
                    ];
                    $attendance_create = Attendance::create($attendance_data);
                    echo $user->id." attendance has been created";
                    echo"<br/>";

                    $attendance_data = [
                        'attendance_id' => $attendance_create->id,
                        'on_time' => "10:14:28",
                        'punched_by' => "Present",
                        'type' => 'Check-in'
                    ];
                    $attendance_punch_create = AttendancePunch::create($attendance_data);
                }
            }




        }



    }

    function markAttendance(Request $request){

        $on_date = date("Y-m-d");

        if($request->type){
            $attendance=Attendance::where('user_id',Auth::id())
                ->where('on_date', $on_date)
                ->first();
            $attendance_id=0;
            if(isset($attendance->id)){
                $attendance_id=$attendance->id;
            }
            else{
                $obj= new Attendance;
                $obj->on_date=$on_date;
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

        $user_id = Auth::id();

        if($request->status=='Holiday'){
            $status='Holiday';
            $attendance = Attendance::where(['user_id'=>$user_id,'on_date'=>$on_date])->first();

            if(!empty($attendance)){
                $attendance->update(['status'=>$status]);

            }else{
                $attendance = Attendance::create(['user_id'=>$user_id,'on_date'=>$on_date,'status'=>$status]);
            }
            return redirect()->back()->withSuccess('Holiday added successfully.');
        }

        elseif($request->status=='Week-Off'){
            $status='Week-Off';
            $attendance = Attendance::where(['user_id'=>$user_id,'on_date'=>$on_date])->first();
            if(!empty($attendance)){
                $attendance->update(['status'=>$status]);
            }else{
                $attendance = Attendance::create(['user_id'=>$user_id,'on_date'=>$on_date,'status'=>$status]);
            }
            return redirect()->back()->withSuccess('Week-Off added successfully.');
        }
    }

}//end of class
