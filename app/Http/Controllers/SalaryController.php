<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Input;
use Hash;
use Auth;
use DB;
use Validator;
use Carbon\Carbon;

//use App\Exports\UsersExport;
use App\Imports\SalarySheetImport;
use App\SalarySlip;
use App\EmployeeAccount;
use App\Employee;
use App\EmployeeProfile;
use App\User;

class SalaryController extends Controller
{
    
    function uploadSalarySlip()
    {
    	$data = [];
    	return view('salary.upload_salary_slip', $data);
    }

   function saveSalarySlip(Request $request)
    {
        $data = [];

        $rules = array(
            'salary_file' => 'required',
        );

    $validator = Validator::make(Input::all(), $rules);

    // process the form
    if ($validator->fails()) 
    {
        
        return redirect()->back()->withErrors($validator); 
    }
    else 
    {
        try {
            
            $data = Excel::toArray(new SalarySheetImport,request()->file('salary_file')); 
            
            if(count($data)){
                foreach($data[0] as $record){
                    
                   //$user_data = Employee::where("employee_id", $record['emp_code'])->first();

                   $user_data = User::where("employee_code", $record['emp_code'])->first();
                    
                    if(SalarySlip::where(['salary_month'=>$record['salary_month'],'salary_year'=> $record['salary_year'],'emp_code' => $record['emp_code']])->count()){//update
                        SalarySlip::where([
                            'salary_month'=>$record['salary_month'],
                            'salary_year'=> $record['salary_year'],
                            'emp_code' => $record['emp_code']
                        ])
                        ->update([
                            'user_id' => $user_data['id'],                            
                            'emp_name' => $record['emp_name'],
                            'present_days' => $record['present_days'],
                            'pf_no' => $record['pf_no'],
                            'pay_days' => $record['pay_days'],
                            'esi_no' => $record['esi_no'],
                            'uan' => $record['uan'],           
                            'net_pay' => $record['net_pay'],
                            'salary_year' => $record['salary_year'],
                            'basic_rate' => $record['basic_rate'],
                            'ta_rate' => $record['ta_rate'],
                            'basic_amount' => $record['basic_amount'],
                            'ta_amount' => $record['ta_amount'],
                            'deduction_amount1' => $record['tds_deduction_amount1']
                        ]);
                    }
                    else{//insert
                        SalarySlip::create([
                            'salary_month'=>$record['salary_month'],
                            'salary_year'=> $record['salary_year'],
                            'emp_code' => $record['emp_code'],
                            'user_id' => $user_data['id'],                            
                            'emp_name' => $record['emp_name'],
                            'present_days' => $record['present_days'],
                            'pf_no' => $record['pf_no'],
                            'pay_days' => $record['pay_days'],
                            'esi_no' => $record['esi_no'],
                            'uan' => $record['uan'],           
                            'net_pay' => $record['net_pay'],
                            'salary_year' => $record['salary_year'],
                            'basic_rate' => $record['basic_rate'],
                            'ta_rate' => $record['ta_rate'],
                            'basic_amount' => $record['basic_amount'],
                            'ta_amount' => $record['ta_amount'],
                            'deduction_amount1' => $record['tds_deduction_amount1']
                        ]);
                    }
                }
            }

           
            \Session::flash('success', 'Salary sheet imported successfully.');
            return redirect()->back()->with('Error', 'There is some error.');
        } catch (\Exception $e) {
            \Session::flash('error', $e->getMessage());
            return redirect()->back()->with('Error', 'There is some error.');
        }
    } 
        return view('salary.upload_salary_slip', $data);
    }

    function viewSalarySlip()
    {
    	$data = [];

        dd($data);
    	return view('salary.view_salary_slip', $data);    
    }

     function viewSalary(Request $request)
    {
        

        $data = [];
        $user = Auth::user();
      
        $salary_month = $request->salary_month;
        if( $salary_month){
            
            $query = [];
             $query['salary_detail'] = SalarySlip::where(['salary_month'=>$salary_month, 'user_id'=>$user->id])->first();        
       
            if($query['salary_detail']){

                $query['emp_detail'] = Employee::where(['user_id'=>$user->id])->first();

                 $query['emp_acc_detail'] = EmployeeAccount::where(['user_id'=>$user->id])->first(); 

                //$query['designation_detail'] = DB::table('designation_user')->where('user_id', $user->id)->first();

                $query['designation_detail'] = User::where('id',$user->id)

                                        ->with('Designation')                        

                                        ->first();

                $query['department_detail'] = EmployeeProfile::where('user_id',$user->id)

                                        ->with('Department')                        

                                        ->first();


                $number = amountInWords($query['salary_detail']->net_pay);

                $query['num_in_words'] = ucwords(trim($number));

                $query['total_rate'] = $query['salary_detail']->basic_rate + $query['salary_detail']->ta_rate;

                $query['total_amount'] = $query['salary_detail']->basic_amount + $query['salary_detail']->ta_amount;

                if($query['salary_detail']->deduction_amount1==""){

                $query['salary_detail']->deduction_amount1=0;

                }

                if($query['salary_detail']->deduction_amount2==""){

                    $query['salary_detail']->deduction_amount2=0;
                }

                $query['total_deduction'] = $query['salary_detail']->deduction_amount1 + $query['salary_detail']->deduction_amount2;
            }else{

                 return view('salary.null_salary_slip', ['data'=>$query]); 
            }    

			
			if($user->is_consultant==1){
				return view('salary.view_salary_slip_consultant', ['data'=>$query]);
			}else{
				return view('salary.view_salary_slip', ['data'=>$query]); 
			}
              
			 
			 
			 
        }else{

             $data=[];

            return view('salary.view_salary', $data); 
        }
       
       
          
    }
}
