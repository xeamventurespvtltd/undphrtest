<?php

namespace App\Imports;

use App\SalarySlip;
use App\Employee;
use App\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SalarySheetImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
       /* echo"<PRE>";
        print_r($row);
        exit;*/
        
       $user_data = Employee::where("employee_id", $row['emp_code'])->first();

       if($user_data){

        $user_id = $user_data->id;
        
        return new SalarySlip([
            
            'user_id' => $user_id,
            'emp_code' => $row['emp_code'],
            'emp_name' => $row['emp_name'],
            //'father_name' => $row['father_name'],
            //'designation' => $row['designation'],
            //'department' => $row['department'],
            'present_days' => $row['present_days'],
            //'bank_account' => $row['bank_account'],
            'pf_no' => $row['pf_no'],
            'pay_days' => $row['pay_days'],
            'esi_no' => $row['esi_no'],
            //'pay_mode' => $row['pay_mode'],
            'uan' => $row['uan'],           
            'net_pay' => $row['net_pay'],
            'salary_month' => $row['salary_month'],
            'salary_year' => $row['salary_year'],
            'basic_rate' => $row['basic_rate'],
            'ta_rate' => $row['ta_rate'],
            'basic_amount' => $row['basic_amount'],
            'ta_amount' => $row['ta_amount'],
            'deduction_amount1' => $row['tds_deduction_amount1']
            //'deduction_amount2' => $row['deduction_amount2']
            //
        ]);

       }else{
        echo $row['emp_code'];
       
        
        
       

       }
      

    }
}
