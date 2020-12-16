<?php

namespace App\Http\Controllers;

use App\Employee;
use App\Location;
use App\LocationUser;
use Illuminate\Http\Request;

class PoController extends Controller
{
    public function index(){

        $pos = \DB::table('location_user as lu')
            ->join('users as us', 'us.id', '=', 'lu.user_id')
            ->join('employees as emp', 'emp.user_id', '=', 'lu.user_id')
            ->join('designation_user as du', 'du.user_id', '=', 'emp.user_id')
            ->join('designations as dsg', 'dsg.id', '=', 'du.designation_id')
            ->join('locations as lc', 'lc.id', '=', 'lu.location_id')
            ->join('states as st', 'st.id', '=', 'lc.state_id')
            ->select('us.employee_code as employee_code', 'lu.id as location_user_id','emp.fullname','dsg.name as designation', 'lc.name as location', 'st.name as state')
            ->where('du.designation_id', '3')
            ->get();

        $employees = Employee::where('isactive', 1)->get();
        $locations = Location::where('isactive', 1)->get();

        return view('employees.po_list', compact('pos', 'employees', 'locations'));
    }

    public function poWithMultipleLocations(){

        $locations =  \DB::select('SELECT  location_id,count(location_id) as location_count FROM `location_user` WHERE `user_id` in (SELECT `user_id` FROM `designation_user` WHERE `designation_id` = 3) GROUP By `location_id` HAVING `location_count` > 1');
        foreach ($locations as $location){
            $locationIds[] = $location->location_id;
        }

        $pos =  \DB::table('location_user as lu')
            ->join('employees as emp', 'emp.user_id', '=', 'lu.user_id')
            ->join('designation_user as du', 'du.user_id', '=', 'emp.user_id')
            ->join('designations as dsg', 'dsg.id', '=', 'du.designation_id')
            ->join('locations as lc', 'lc.id', '=', 'lu.location_id')
            ->join('states as st', 'st.id', '=', 'lc.state_id')
            ->select('lu.id as location_user_id','emp.fullname','dsg.name as designation', 'lc.name as location', 'st.name as state')
            ->where('du.designation_id', '3')
            ->whereIn('lu.location_id', $locationIds)->get();
        return view('employees.duplicate_po_list', compact('pos'));
    }

    public function destroy($id)
    {
        \DB::table('location_user')->where('id', $id)->delete();
        return back()->with('success', 'Duplicate Entry Deleted Successfully');
    }

    public function store(Request $request){
        $locationId = $request->location_id;
       $locationUser = \DB::select("SELECT * FROM `location_user`  WHERE `user_id` in (SELECT `user_id` FROM `designation_user` WHERE `designation_id` = 3) AND `location_id` = $locationId");
        if(isset($locationUser)) {
            $employee = Employee::where('user_id', $locationUser[0]->user_id)->first();
            return back()->with('error', $employee->fullname.' Is already assigned as PO At Selected Location');
        }else {

            LocationUser::create([
                'user_id' => $request->user_id,
                'location_id' => $request->location_id
            ]);
            return back()->with('success', 'Location allot to user successfully');
        }
    }
}
