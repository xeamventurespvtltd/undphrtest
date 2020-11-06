<?php

namespace App\Imports;

use App\LeaveDetail;
use App\SalarySlip;
use App\Employee;
use App\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LeaveDetailImport implements ToModel, WithHeadingRow
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

        return $row;
    }
}
