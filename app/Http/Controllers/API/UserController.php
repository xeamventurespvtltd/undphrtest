<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller; 
use App\Mail\ForgotPassword;
use Mail;
use Hash;
use Auth;
use DB;
use Validator;
use Carbon\Carbon;
use DateTime;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\User;
use App\Employee;
use App\EmployeeProfile;
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
use App\EmploymentHistory;
use App\Document;
use App\Company;
use App\SalaryStructure;
use App\SalaryCycle;
use App\LeaveAuthority;
use App\Message;
use App\Notification;
use App\Designation;
use App\AppVersion;

class UserController extends Controller
{
    /*
        Check app version & if old tell them to update the app
    */
    function checkAppVersion(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'version' => 'required',
            'device_type' => 'required', //Android, Ios
        ]);

        if($validator->fails()){
            return response()->json(['validation_error'=>$validator->errors()], 400);
        }

        $current_version = AppVersion::where(['version'=>$request->version,'device_type'=>$request->device_type])->first();

        if(!empty($current_version)){
            $latest_version = AppVersion::where(['device_type'=>$request->device_type])
                                        ->orderBy('id','DESC')
                                        ->first();
            
            if($current_version->version !== $latest_version->version){
                $message = "Please update your app.";
                return response()->json(['error'=>$message], 426);
            }else{
                $message = "Your app is uptodate.";
                return response()->json(['success'=>$message], 200);
            }                            
        }else{
            $message = "Please update your app.";
            return response()->json(['error'=>$message], 426);
        }
    }

    /*
        Generate secret token for a user everytime they login
    */
    function login(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'employee_code' => 'required', 
            'password' => 'required', 
            'device_id' => 'required',
            'device_type' => 'required', //Android, Ios
        ]);

        if($validator->fails()){
            return response()->json(['validation_error'=>$validator->errors()], 400);
        }

        $credentials = $request->only(['employee_code','password']);
        if(Auth::attempt($credentials)){
            $user = Auth::user();
            
            if($user->employee->approval_status == '0'){
                Auth::logout();
                return response()->json(['error'=>'Your account has not been approved yet. Please contact administrator!'], 401);

            }elseif(!$user->employee->isactive){
                Auth::logout();
                return response()->json(['error'=>'Your account has been disabled. Please contact administrator!'], 401);

            }else{
                $other_users = User::where('device_id',$request->device_id)
                                    ->where('id','!=',$user->id)    
                                    ->update(['device_id'=>null,'device_type'=>null]);

                $user->device_id = $request->device_id;
                $user->device_type = $request->device_type;
                $user->save();

                $user_data = User::where('id',$user->id)
                                ->with('employee:id,user_id,fullname,profile_picture,isactive,approval_status,joining_date')
                                ->first();
                $user_data->permissions = $user->permissions()->pluck('name')->toArray();

                if(empty($user_data->employee->profile_picture)){
                    $user_data->employee->profile_picture = config('constants.static.profilePic');
                }else{
                    $user_data->employee->profile_picture = config('constants.uploadPaths.profilePic').$user_data->employee->profile_picture;
                }
    
                $success['secret_token'] =  $user->createToken('MyApp')->accessToken; 
                $success['user'] = $user_data;
                return response()->json(['success' => $success], 200);
            }
        }else{
            return response()->json(['error'=>'Credentials do not match!'], 401);
        }
    }

    /*
        Revoke the secret-token if user logout
    */
    function logout(Request $request)
    {
        $user = $request->user();
        $user->token()->revoke();

        $user->device_id = null;
        $user->device_type = null;
        $user->save();
        return response()->json(['success' => 'Successfully logged out.']);
    }

    /*
        Get the employees of the selected departments
    */
    function departmentsWiseEmployees(Request $request)
    {
        checkDeviceId($request->user());
        $validator = Validator::make($request->all(), [
            'department_ids' => 'required',
        ]);

        if($validator->fails()){
            return response()->json(['validation_error'=>$validator->errors()], 400);
        }

        $department_ids = explode(',',$request->department_ids);
        $employees = DB::table('employees as e')
                ->join('employee_profiles as ep','e.user_id','=','ep.user_id')
                ->join('users as u','e.user_id','=','u.id')
                ->whereIn('ep.department_id',$department_ids)
                ->where(['e.approval_status'=>'1','e.isactive'=>1,'ep.isactive'=>1])
                ->where('e.user_id','!=',1)
                ->select('e.user_id','e.fullname','u.employee_code')
                ->get();

        $success['employees'] = $employees;
        if($employees->isEmpty()){
            $status_code = 204;
        }else{
            $status_code = 200;
        }
        return response()->json(['success' => $success], $status_code);        
    }
    
}//end of class
