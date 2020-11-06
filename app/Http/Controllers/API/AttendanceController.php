<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
use Validator;
use DateTime;
use stdClass;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Exports\ConsolidatedAttendanceExport;
use App\Exports\AttendancePunchExport;
use App\Exports\SaralAttendanceExport;
use Maatwebsite\Excel\Facades\Excel;
use View;
use App\LeaveType;
use App\AppliedLeave;
use App\CompensatoryLeave;

ini_set('max_execution_time', 180);

class AttendanceController extends Controller
{
    /*
        Get the attendance details of a user between specific dates
    */
    function userAttendanceDetail(Request $request)
    {
        checkDeviceId($request->user());

        $validator = Validator::make($request->all(), [
            'from_date' => 'required', 
            'to_date' => 'required', 
            'user_id' => 'required', 
        ]);

        if($validator->fails()){
            return response()->json(['validation_error'=>$validator->errors()], 400);

        }elseif(strtotime($request->from_date) > strtotime($request->to_date)){
            return response()->json(['validation_error'=>'From date should be less than or equal to To date'], 400);
        }

        $auth_user = Auth::user();
        if(!userHasPermissions($auth_user,['view-attendance'])){
            return response()->json(['error' => 'You do not have permission to access this module!'], 403);
        }else{
            $period = CarbonPeriod::create($request->from_date, $request->to_date);

            $attendances = [];
            $user = User::where('id',$request->user_id)
                        ->with('employee:id,user_id,fullname,profile_picture,isactive,approval_status')
                        ->first();
            
            $counter_data = [
                'Present' => 0,
                'Absent' => 0,
                'Holiday' => 0,
                'Leave' => 0,
                'Travel' => 0,
                'Week-Off' => 0,
                'Late' => 0,
                'N/A' => 0,
            ];            
            // Iterate over the period
            foreach ($period as $date) {
                $attendance = $this->ondateAttendanceData($date->format('Y-m-d'), $user->id);
                if(!empty($attendance)){
                    if($attendance['status'] == ''){
                        $counter_data['Absent'] += 1;
                    }else{
                        $counter_data[$attendance['status']] += 1;
                    }
                    if($attendance['late']){
                        $counter_data['Late'] += 1;
                    }
                }
                $attendances[] = $attendance;
            }
            
            if(empty($user->employee->profile_picture)){
                $user->employee->profile_picture = config('constants.static.profilePic');
            }else{
                $user->employee->profile_picture = config('constants.uploadPaths.profilePic').$user->employee->profile_picture;
            }

            $success['attendance_data'] = $attendances;
            $success['counter_data'] = $counter_data;
            $success['user'] = $user;
            return response()->json(['success' => $success], 200);
        }
    }

    /*
        Get the attendance details of a user between specific dates
    */
    function attendanceDetail(Request $request)
    {
        checkDeviceId($request->user());

        $validator = Validator::make($request->all(), [
            'attendance_type' => 'required', //self or team
        ]);

        if($validator->fails()){
            return response()->json(['validation_error'=>$validator->errors()], 400);
        }
        
        $user = Auth::user();

        if(empty($request->on_date)){
            $on_date = date("Y-m-d");
        }else{
            $on_date = date("Y-m-d",strtotime($request->on_date));
        }

        if($request->attendance_type == 'self'){
            $success['attendance_data'] = $this->ondateAttendanceData($on_date, $user->id);
            return response()->json(['success' => $success], 200);

        }else{ //Team list
            if(!userHasPermissions($user,['view-attendance'])){
                return response()->json(['error' => 'You do not have permission to access this module!'], 403);
            }else{
                $month_last_date = date("Y-m-t",strtotime($on_date));
                $employees = DB::table('projects as p')
                            ->join('project_user as pu','p.id','=','pu.project_id')
                            ->join('employee_profiles as ep','ep.user_id','=','pu.user_id')
                            ->join('employees as e','ep.user_id','=','e.user_id')
                            ->join('users as u','ep.user_id','=','u.id')
                            ->join('departments as d','d.id','=','ep.department_id')
                            ->join('leave_authorities as la','la.user_id','=','ep.user_id')
                            ->where('e.user_id','!=',1)
                            ->where(['la.isactive'=>1,'la.priority'=>'2','la.manager_id'=>$user->id])
                            ->whereDate('e.joining_date','<=',$month_last_date)
                            ->where(['pu.isactive'=>1,'p.isactive'=>1,'p.approval_status'=>'1','e.approval_status'=>'1','e.isactive'=>1])
                            ->select('ep.user_id','d.name as department_name','e.fullname','u.employee_code','e.joining_date',DB::raw("CASE WHEN e.profile_picture IS NULL OR e.profile_picture = '' THEN CONCAT('".config('constants.static.profilePic')."') ELSE CONCAT('".config('constants.uploadPaths.profilePic')."', e.profile_picture) END AS profile_picture"))
                            ->get();

                $counter_data = [
                    'Present' => 0,
                    'Absent' => 0,
                    'Holiday' => 0,
                    'Leave' => 0,
                    'Travel' => 0,
                    'Week-Off' => 0,
                    'Late' => 0,
                    'N/A' => 0,
                ];            

                if(!$employees->isEmpty()){
                    foreach ($employees as $employee) {
                        $employee->attendance_data = $this->ondateAttendanceData($on_date, $employee->user_id);
                        if(!empty($employee->attendance_data)){
                            if($employee->attendance_data['status'] == ''){
                                $counter_data['Absent'] += 1;
                            }else{
                                $counter_data[$employee->attendance_data['status']] += 1;
                            }
                            if($employee->attendance_data['late']){
                                $counter_data['Late'] += 1;
                            }
                        }
                    }
                    $status_code = 200;
                }else{
                    $status_code = 204;
                }

                $success['employees'] = $employees;
                $success['counter_data'] = $counter_data;
                return response()->json(['success' => $success], $status_code);
            }
        }
    }

    /*
        Get the monthly attendance details of a user
    */
    function monthlyAttendanceReport(Request $request)
    {
        checkDeviceId($request->user());

        $validator = Validator::make($request->all(),[
                        'user_id' => 'required',
                        'month' => 'required',
                        'year' => 'required',
                    ]);

        if($validator->fails()){
            return response()->json(['validation_error'=>$validator->errors()], 400);
        }

        $auth_user = Auth::user();
        $user = User::where(['id'=>$request->user_id])
                    ->with('employee:id,user_id,fullname,profile_picture,joining_date')
                    ->first();

        if($auth_user->id != $user->id){
            if(!userHasPermissions($auth_user,['view-attendance'])){
                return response()->json(['error' => 'You do not have permission to access this module!'], 403);
            }
        }

        if(empty($user->employee->profile_picture)){
            $user->employee->profile_picture = config('constants.static.profilePic');
        }else{
            $user->employee->profile_picture = config('constants.uploadPaths.profilePic').$user->employee->profile_picture;
        }
        
        $first_date = $request->year.'-'.$request->month.'-01';
        $last_day = date("t",strtotime($first_date)); 
        $last_date = date("Y-m-t",strtotime($first_date));
        $current_date = date("Y-m-d");
        $attendance_data = [];
        $counter_data = [
            'Present' => 0,
            'Absent' => 0,
            'Holiday' => 0,
            'Leave' => 0,
            'Travel' => 0,
            'Week-Off' => 0,
            'Late' => 0,
            'N/A' => 0,
        ];
        
        for($i = 1; $i <= $last_day; $i++){
            $new_date = date('Y-m-d',strtotime($request->year.'-'.$request->month.'-'.$i));
            if(strtotime($new_date) <= strtotime($current_date)){
                $data = $this->ondateAttendanceData($new_date, $user->id);
                if(!empty($data)){
                    if($data['status'] == ''){
                        $counter_data['Absent'] += 1;
                    }else{
                        $counter_data[$data['status']] += 1;
                    }
                    if($data['late']){
                        $counter_data['Late'] += 1;
                    }
                }
                $attendance_data[] = $data;
            }else{
                break;
            }
        }
        $user->monthly_data = $attendance_data;
        $user->counter_data = $counter_data;
        $success['user'] = $user;

        return response()->json(['success'=>$success], 200);
    }

    /*
        Get the attendance details of a user for a specific date 
    */
    function ondateAttendanceData($on_date, $user_id)
    {
        $attendance_data = getAttendanceInfo($on_date, $user_id);
        $attendance_data['on_date'] = date("d M, Y",strtotime($on_date));
        $attendance_data['replacement'] = "";
            
        if($attendance_data['status'] == "" && date("l",strtotime($on_date)) == 'Sunday'){
            $attendance_data['status'] = 'Week-Off';

        }elseif($attendance_data['status'] == 'Leave'){
            $leave = AppliedLeave::where('from_date','<=',$on_date)
                                ->where('to_date','>=',$on_date)
                                ->where(['final_status'=>'1','user_id'=> $user_id])
                                ->first();

            if(!empty($leave->leaveReplacement)){
                $attendance_data['replacement'] = $leave->leaveReplacement->user->employee->fullname; 
            }                    
        }

        if(!empty($attendance_data['first_punch']) && !empty($attendance_data['last_punch'])){
            $first_punch = $on_date.' '.$attendance_data['first_punch'];
            $last_punch = $on_date.' '.$attendance_data['last_punch'];

            $datetime1 = new DateTime($first_punch);
            $datetime2 = new DateTime($last_punch);
            $interval = $datetime1->diff($datetime2);
            $attendance_data['total_time'] = $interval->format('%H')." hr ".$interval->format('%i')." min";
        }else{
            $attendance_data['total_time'] = "";
        }

        return $attendance_data;
    }

    /*
        Get the attendance punches of a user for a specific date 
    */
    function attendancePunches(Request $request)
    {
        checkDeviceId($request->user());

        $validator = Validator::make($request->all(), [
            'on_date' => 'required', 
            'user_id' => 'required', // self id or other user's id
        ]);

        if($validator->fails()){
            return response()->json(['validation_error'=>$validator->errors()], 400);
        }

        $on_date = date("Y-m-d", strtotime($request->on_date));

        if($request->user_id == Auth::id()){ //self
            $user = Auth::user();             
        }else{ //other
            $user = User::findOrFail($request->user_id);
            $auth_user = Auth::user();
            
            if(!userHasPermissions($auth_user,['view-attendance'])){
                return response()->json(['error' => 'You do not have permission to access this module!'], 403);
            }
        }

        $attendance = $user->attendances()
                            ->where('on_date', $on_date)
                            ->first();
        
        $success['attendance_data'] = $attendance;                    
        
        if(!empty($attendance)){
            $attendance->punches = $attendance->attendancePunches()->orderBy('on_time')->get();    
            $status_code = 200;
        }else{
            $status_code = 204;
        }   
            
        return response()->json(['success'=>$success], $status_code);
    }

    /*
        Save the current location of a user with comment & picture & mark them as present 
    */
    function storeAttendanceLocation(Request $request)
    {
        checkDeviceId($request->user());

        $validator = Validator::make($request->all(), [
            'latitude' => 'required',
            'longitude' => 'required',
            'comment' => 'required',
            'type' => 'required', //Check-In, Check-Out
            'file' => 'required|image|max:1024', //in kilobytes
        ]);
        
        if($validator->fails()){
            return response()->json(['validation_error'=>$validator->errors()], 400);
        }

        $step=0;
        $user = Auth::user();
        DB::beginTransaction();
        try {

            $location_data = [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'comment' => $request->comment,
                'on_time' => date("H:i:s"),
            ];

            $attendance = $user->attendances()->where(['on_date'=>date("Y-m-d")])->first();
            if(empty($attendance)){
                $attendance = $user->attendances()->create(['on_date'=>date("Y-m-d"),'status'=>'Present']);
            }

            
        
            $punch_data = [
                'on_time' => $location_data['on_time'],
                'punched_by' => $user->id,
                'type' => $request->type,
            ];
            $attendance->attendancePunches()->create($punch_data);

            
         
            if($request->hasFile('file')) {
                $file = round(microtime(true)).str_random(5).'.'.$request->file('file')->getClientOriginalExtension();
                $request->file('file')->move(config('constants.uploadPaths.uploadAttendancePic'), $file);
                $location_data['filename'] = $file;
            }
            $user->attendanceLocations()->create($location_data);

            $success['attendance'] = $attendance;
            DB::commit();
        }catch(Exception $e){
            DB::rollback();
            //echo $e->getMessage();
            return response()->json(['save_error'=>$e->getMessage()], 400);
        } 
        return response()->json(['success'=>$success], 200);
    }

}//end of class

