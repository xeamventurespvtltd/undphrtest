<?php

namespace App\Exports;

use App\User;
use App\LeaveDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use DB;

class LeavePoolExport implements FromCollection, WithHeadings
{
    protected $users;

    public function __construct($users)
    {
        $this->users = $users;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $leaveDetail = DB::table('leave_details')
            ->join('employees', 'employees.user_id', '=', 'leave_details.user_id')
            ->whereIn('employees.user_id', $this->users)->whereYear('leave_details.month_info', '2020')
            ->whereMonth('leave_details.month_info', '11')
            ->select('employees.fullname', 'employees.employee_id', 'leave_details.accumalated_casual_leave', 'leave_details.accumalated_sick_leave')->get();
    }

    public function headings(): array
    {
        return [
            'Employee Name',
            'Employee Code',
            'Accumulated Casual Leave',
            'Accumulated Sick Leave'
        ];
    }
}
