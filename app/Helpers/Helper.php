<?php

use App\Attendance;
use App\Holiday;
use App\AppliedLeave;
use App\AttendanceRemark;
use App\AttendancePunch;
use App\TravelApproval;
use App\TbltTimesheet;
use Carbon\Carbon;
use App\CompensatoryLeave;
use App\User;
use App\ShiftException;
use App\Shift;
use App\LeaveDetail;


/*
    Revoke accessToken if device id is null
*/
function checkDeviceId($user)
{
    if($user->device_id == null){
        $user->token()->revoke();
    }
    return true;
}

/*
    Check whether user has a specific permission
*/
function userHasPermissions($user, $check_permissions){
    $permissions = $user->permissions()->pluck('name')->toArray();

    foreach($check_permissions as $permission){
        if(!in_array($permission, $permissions)){
            $flag = false;
            break;
        }else{
            $flag = true;
        }
    }

    return $flag;
}

function pushNotification($user_id, $title, $body)
{
    $user = User::find($user_id);
    try{
        if(!empty($user->device_id)){
            $url = "https://fcm.googleapis.com/fcm/send";
            $device_ids = array();
            array_push($device_ids, $user->device_id);

            $serverKey = env('FCM_KEY'); //your server token of FCM project

            $notification = array(
                'title' =>$title ,
                'body' => $body,
                'sound' => 'default',
                'badge' => '1',
            );
            $arrayToSend = array(
                'registration_ids' => $device_ids,
                'notification' => $notification,
                'priority'=>'high',
            );

            $json = json_encode($arrayToSend);
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Authorization: key='. $serverKey;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            //Send the request
            $response = curl_exec($ch);
            //Close request
            // if ($response === FALSE) {
            //     die('FCM Send Error: ' . curl_error($ch));
            // }
            curl_close($ch);
        }
    }catch(\Exception $e){
        return $e->getMessage();
    }
}

function sms($phone, $message){

    try{
        $ch = curl_init();

        $url='http://180.151.98.11/Api.aspx?usr=xeam&pwd=welcome123&smstype=TextSMS&to='.$phone.'&msg='.urlencode($message).'&rout=Transactional&from=XEAMHR';
        //$url='http://180.151.98.11/SecureApi.aspx?usr=xeam&key=7B55BE6D-540D-478C-91E7-A72378823ADA&smstype=TextSMS&to='.$phone.'&msg='.$message.'&rout=Transactional&from=XEAMHR'

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close ($ch);

        return $server_output;

    }catch(\Exception $e){
        return false;
    }
}

function leaveRelatedCalculations($probation_data,$applied_leave){

    // Get Leave applier designation id

    $designation_data = User::where(['id' =>  $applied_leave->user_id])
        ->with('designation')
        ->first();

    $designation = $designation_data->designation[0]->id;

    // leave applier user id
    $userId = $applied_leave->user_id;
    $current_year = date("Y");
    $current_month = date("n");
    // get previous Month
    $prev_month = date("n")-1;

    // Chk user apply for leave in current month

    $leave_status_current_month = LeaveDetail::where('user_id', $userId)
        ->whereMonth('month_info',$current_month)  //works with to_date as well
        ->first();
    //dump($leave_status_current_month);

    // Chk entry in previous month in leave detail table

    $leave_status_prev = LeaveDetail::where('user_id', $userId)
        ->whereMonth('month_info',$prev_month)
        ->first();
    //dd($leave_status_prev);

    // chk Previous month leave detailed set and not empty...

    // previous month entry set h...

    if(isset($leave_status_prev) && !empty($leave_status_prev)){
        // IF Date of Joining not in current month

        //if no leaves in current month..
        if(!$leave_status_current_month){
            $count = $leave_status_prev->count();

            if($designation==4){    // for vccm

                $prev_accumlated_casual = $leave_status_prev->accumalated_casual_leave;
                $accumlated_casual = $prev_accumlated_casual + 1.5;
                $prev_accumlated_sick = $leave_status_prev->accumalated_sick_leave;
                $accumlated_sick = $prev_accumlated_sick + 0.5;

            }
            elseif ($designation==3 || $designation==5) {  // for PO

                $prev_accumlated_casual = $leave_status_prev->accumalated_casual_leave;
                $accumlated_casual = $prev_accumlated_casual + 2;
                $prev_accumlated_sick = $leave_status_prev->accumalated_sick_leave;
                $accumlated_sick = $prev_accumlated_sick + 1;

            }
            elseif ($designation==2){    // for SPO

                $prev_accumlated_casual = $leave_status_prev->accumalated_casual_leave;
                $accumlated_casual = $prev_accumlated_casual;
                $prev_accumlated_sick = $leave_status_prev->accumalated_sick_leave;
                $accumlated_sick = $prev_accumlated_sick;
            }

            $prev_balance_casual_leave = $leave_status_prev->balance_casual_leave;
            $prev_balance_sick_leave = $leave_status_prev->balance_sick_leave;

            //$count is previous month leave
            if($count==1){
                if($designation==4){
                    $balance_casual_leave  = $prev_balance_casual_leave - 3;
                    $balance_sick_leave = $prev_balance_sick_leave - 1;
                }else{
                    $balance_casual_leave  = $prev_balance_casual_leave - 4;
                    $balance_sick_leave = $prev_balance_sick_leave - 2;
                }
            }else{
                if($designation==4){     //  4 => VCCM
                    // Balance Casual  '1.5' Vccm Take Every Month
                    $balance_casual_leave  = $prev_balance_casual_leave;
                    //Balance Sick '0.5' Vccm Take Every Month
                    $balance_sick_leave = $prev_balance_sick_leave;
                }else{
                    // PO
                    // Balance Casual  '2' PO Take Every Month
                    $balance_casual_leave  = $prev_balance_casual_leave;
                    //Balance Sick '1' Vccm Take Every Month
                    $balance_sick_leave = $prev_balance_sick_leave;
                }
            }

            $unpaid_casual = 0;
            $paid_casual = 0;

            $unpaid_sick = 0;
            $paid_sick = 0;

            $compensatory_count = 0;

            $approval_data = [
                'user_id' => $userId,
                'month_info' => $applied_leave->from_date,
                'accumalated_casual_leave' => $accumlated_casual,
                'accumalated_sick_leave' => $accumlated_sick,
                'balance_casual_leave' => $balance_casual_leave,
                'balance_sick_leave' => $balance_sick_leave,
                'balance_maternity_leave' => '180',
                'balance_paternity_leave' => '15',
                'unpaid_casual' => $unpaid_casual,
                'paid_casual' => $paid_casual,
                'unpaid_sick' => $unpaid_sick,
                'paid_sick' => $paid_sick,
                'compensatory_count' => $compensatory_count,
                'isactive' => 1
            ];
            LeaveDetail::create($approval_data);

            // update
            $leave_status_after = LeaveDetail::where('user_id', $userId)
                ->whereMonth('month_info',$current_month)  //works with to_date as well
                ->first();

            $id_to_update = $leave_status_after->id;

            // accumlated casual and sickleave
            $accumlated_casual_after = $leave_status_after->accumalated_casual_leave;
            $accumlated_sick_after = $leave_status_after->accumalated_sick_leave;

            $balance_maternity = $leave_status_after->balance_maternity_leave;
            $balance_paternity = $leave_status_after->balance_paternity_leave;

            // Unpaid casual and sick leave
            $unpaid_casual = $leave_status_after->unpaid_casual;
            $paid_casual = $leave_status_after->paid_casual;

            // Paid Sick and sick leave
            $unpaid_sick = $leave_status_after->unpaid_sick;
            $paid_sick = $leave_status_after->paid_sick;

            $compensatory_count = $leave_status_after->compensatory_count;


            if($applied_leave->leave_type_id==1){ // For Casual Leave
                // Remain Casual
                $remain_casual = $accumlated_casual_after - $applied_leave->number_of_days;

                // if remain casual greater than equals to 0
                if($remain_casual>=0){
                    // insert paid casual no of days
                    $paid_casual = $applied_leave->number_of_days;
                    // unpaid casual not changed.
                    $unpaid_casual = $unpaid_casual;
                    //accumulated casual
                    $accumlated_casual = $remain_casual;
                }

                // if remain casual less than 0
                if($remain_casual<0){
                    //in that case accumulated casual not changed..
                    $accumlated_casual = 0;
                    // unpaid casual - to +
                    $unpaid_casual = abs($remain_casual);
                    //pai casual
                    $paid_casual = $applied_leave->number_of_days - $unpaid_casual;
                }

                $paidCount = $paid_casual;
                $unpaidCount = $unpaid_casual;

            }
            elseif($applied_leave->leave_type_id==2){ //sick leave
                $remain_sick = $accumlated_sick_after - $applied_leave->number_of_days;

                if($remain_sick>=0){
                    $paid_sick = $applied_leave->number_of_days;
                    $unpaid_sick = $unpaid_sick;
                    $accumlated_sick = $remain_sick;
                }

                if($remain_sick<0){
                    $accumlated_sick = 0;
                    $unpaid_sick = abs($remain_sick);
                    $paid_sick = $applied_leave->number_of_days - $unpaid_sick;
                }

                $paidCount = $paid_sick;
                $unpaidCount = $unpaid_sick;
            }
            elseif($applied_leave->leave_type_id==4){ //maternity leave
                $balance_maternity = 0;
                $paidCount = 0;
                $unpaidCount = 0;
            }
            elseif($applied_leave->leave_type_id==7){ //Paternity leave
                $balance_paternity = 0;
                $paidCount = 0;
                $unpaidCount = 0;
            }
            elseif($applied_leave->leave_type_id==5){ //compensatory
                $compensatory_count =  $compensatory_count+$applied_leave->number_of_days;
                $paidCount = $applied_leave->number_of_days;
                $unpaidCount = 0;
            }


            saveLeaveSegration($applied_leave, $paidCount, $unpaidCount);


            $update_approval_data = [

                'accumalated_casual_leave' => $accumlated_casual,
                'accumalated_sick_leave' => $accumlated_sick,
                'balance_casual_leave' => $balance_casual_leave,
                'balance_sick_leave' => $balance_sick_leave,
                'balance_maternity_leave' => $balance_maternity,
                'balance_paternity_leave' => $balance_paternity,
                'unpaid_casual' => $unpaid_casual,
                'paid_casual' => $paid_casual,
                'unpaid_sick' => $unpaid_sick,
                'paid_sick' => $paid_sick,
                'compensatory_count' => $compensatory_count,
                'isactive' => 1
            ];
            LeaveDetail::where('id', $id_to_update)->update($update_approval_data);


        }else{

            $leave_status_after = LeaveDetail::where('user_id', $userId)
                ->whereMonth('month_info',$current_month)  //works with to_date as well
                ->first();

            $id_to_update = $leave_status_after->id;

            // chk accumulated casual
            $accumlated_casual = $leave_status_after->accumalated_casual_leave;

            // chk accumulated Sick
            $accumlated_sick = $leave_status_after->accumalated_sick_leave;

            // Balance Casual and and Sick Leave

            $balance_casual_leave = $leave_status_after->balance_casual_leave;
            $balance_sick_leave = $leave_status_after->balance_sick_leave;

            // Unpaid and Paid Casual
            $unpaid_casual = $leave_status_after->unpaid_casual;
            $paid_casual = $leave_status_after->paid_casual;

            // Unpaid and Paid Sick
            $unpaid_sick = $leave_status_after->unpaid_sick;
            $paid_sick = $leave_status_after->paid_sick;

            // Balance Maternity and paternity
            $balance_maternity = $leave_status_after->balance_maternity_leave;
            $balance_paternity = $leave_status_after->balance_paternity_leave;

            // Compensatory
            $compensatory_count = $leave_status_after->compensatory_count;


            //For casual leave

            if($applied_leave->leave_type_id==1){
                // In Casual Condition 	Accumulated Sick are not Changed
                $current_accumlated_sick = $leave_status_after->accumalated_sick_leave;
                $accumlated_sick= $current_accumlated_sick;

                $accumlated_casual_old_val = $accumlated_casual;

                // Chk Remaining Accumulated Casual Leaves
                $remain_accumulate_casual = $accumlated_casual - $applied_leave->number_of_days;


                // when remaining accumulated casula greater than equals to zero
                if($remain_accumulate_casual>=0){

                    // chk paid casual
                    $paid_casual = $paid_casual + $applied_leave->number_of_days;
                    $paid_count  = $applied_leave->number_of_days;
                    $unpaid_count = 0;
                    // chk Unpaid casual
                    $unpaid_casual = $unpaid_casual;

                    // Remaining accumulated casual
                    $accumlated_casual = $remain_accumulate_casual;
                }


                // In that Case Remaining Accumulated Casual Leaves less than 0 bole to -0.5 or -1
                if($remain_accumulate_casual<0){

                    // In that Case accumlated_casual leaves 0
                    //$accumlated_casual = 0;



                    // Unpaid casual
                    $unpaid_casual_now = abs($remain_accumulate_casual);
                    $unpaid_casual = $unpaid_casual + $unpaid_casual_now;
                    $paid_count  = $accumlated_casual;
                    $unpaid_count = $unpaid_casual_now;
                    $accumlated_casual = 0;

                    if($unpaid_casual_now == abs($remain_accumulate_casual)){
                        $paid_casual = $accumlated_casual_old_val + $paid_casual;
                    }

                }

                $paidCount = $paid_count;
                $unpaidCount = $unpaid_count;

            }
            elseif($applied_leave->leave_type_id==2){ // For Sick Leave Condition

                // In Casual Condition 	Accumulated Casual are not Changed
                $current_accumlated_casual = $leave_status_after->accumalated_casual_leave;
                $accumlated_casual = $current_accumlated_casual;

                $accumlated_sick_old_val = $accumlated_sick;

                // Chk Remaining Accumulated Sick Leaves
                $remain_accumulate_sick = $accumlated_sick - $applied_leave->number_of_days;
                //remain_accumulated_sick =-2
                //accumulated_sick =2
                // when remaining accumulated Sick greater than equals to zero

                if($remain_accumulate_sick>=0){

                    // chk paid Sick
                    $paid_sick = $paid_sick + $applied_leave->number_of_days;

                    // chk Unpaid Sick
                    $unpaid_sick = $unpaid_sick;

                    // Remaining accumulated sick
                    $accumlated_sick = $remain_accumulate_sick;

                }

                if($remain_accumulate_sick<0){
                    // In that Case accumlated_sick leaves 0
                    $accumlated_sick = 0;

                    // Unpaid Sick
                    $unpaid_sick_now = abs($remain_accumulate_sick);
                    $unpaid_sick = $unpaid_sick + $unpaid_sick_now;


                    if($unpaid_sick_now == abs($remain_accumulate_sick)){
                        $paid_sick = $accumlated_sick_old_val + $paid_sick;
                    }

                }

                $paidCount = $paid_sick;
                $unpaidCount = $unpaid_sick;
            }
            elseif($applied_leave->leave_type_id==4){
                // Balance Maternity Leave
                $paidCount = $balance_maternity-$applied_leave->number_of_days;
                $unpaidCount = 0;
            }
            elseif($applied_leave->leave_type_id==7){

                // Balance Paternity Leave
                $balance_paternity = $balance_paternity - $applied_leave->number_of_days;
                $paidCount = $applied_leave->number_of_days;
                $unpaidCount = 0;

            }
            elseif($applied_leave->leave_type_id==5){
                $compensatory_count = $compensatory_count + $applied_leave->number_of_days;
                $paidCount = $applied_leave->number_of_days;
                $unpaidCount = 0;
            }

            saveLeaveSegration($applied_leave, $paidCount, $unpaidCount);

            $update_approval_data = [

                'accumalated_casual_leave' => $accumlated_casual,
                'accumalated_sick_leave' => $accumlated_sick,
                'balance_casual_leave' => $balance_casual_leave,
                'balance_sick_leave' => $balance_sick_leave,
                'balance_maternity_leave' => $balance_maternity,
                'balance_paternity_leave' => $balance_paternity,
                'unpaid_casual' => $unpaid_casual,
                'paid_casual' => $paid_casual,
                'unpaid_sick' => $unpaid_sick,
                'paid_sick' => $paid_sick,
                'compensatory_count' => $compensatory_count,
                'isactive' => 1
            ];


            LeaveDetail::where('id', $id_to_update)->update($update_approval_data);
        }
    }

    else{

        // get Current Month Info..

        if(isset($leave_status_current_month) ){

            $leave_status = LeaveDetail::where('user_id', $userId)
                ->whereMonth('month_info',$current_month)  //works with to_date as well
                ->first();


            $id_to_update = $leave_status->id;

            // For Accumulated Casual and Sick Casual Leave
            $accumlated_casual = $leave_status->accumalated_casual_leave;
            $accumlated_sick = $leave_status->accumalated_sick_leave;

            // For balace Casual and Sick Casual Leave
            $balance_casual =  $leave_status->balance_casual_leave;
            $balance_sick =  $leave_status->balance_sick_leave;

            // For Goverment Policy Maternity and Paternity Leave
            $balance_maternity = $leave_status->balance_maternity_leave;
            $balance_paternity = $leave_status->balance_paternity_leave;

            // For paid Casual and unpaid Casual Leave
            $paid_casual = $leave_status->paid_casual;
            $unpaid_casual = $leave_status->unpaid_casual;

            // For paid Sick and unpaid Leave
            $paid_sick = $leave_status->paid_sick;
            $unpaid_sick = $leave_status->unpaid_sick;

            $compensatory_count = $leave_status->compensatory_count;

            //  Leave Type Casual
            if($applied_leave->leave_type_id==1){

                // Chk remaining accumulated casual leave
                $remain_casual = $accumlated_casual - $applied_leave->number_of_days;

                // if remaining accumulated casual leave > 0
                if($remain_casual>=0){

                    // add leave to paid casual bc remain_casual > 0
                    $paid_casual = $paid_casual + $applied_leave->number_of_days;

                    // unpaid 0
                    $unpaid_casual = $unpaid_casual;

                    // final leave reflect in accumulated causuall after deduction
                    $accumlated_casual = $remain_casual;

                }

                // chk case remaining accumulated casual leave < 0

                if($remain_casual<0){

                    $accumlated_casual = 0;
                    // remain casual change value '-' to '+'  and it goes to unpaid.
                    $unpaid_casual_now = abs($remain_casual);

                    $unpaid_casual = $unpaid_casual_now + $unpaid_casual;

                    // chk apply no of leaves - unpaid casual leave
                    $paid_casual_now = $applied_leave->number_of_days - $unpaid_casual;

                    $paid_casual = $paid_casual_now + $paid_casual;
                    if($accumlated_casual == 0){
                        $paid_casual = 0;
                    }else{
                        $paid_casual = $paid_casual_now + $paid_casual;
                    }

                }

                $paidCount = $paid_casual;
                $unpaidCount = $unpaid_casual;

            }
            elseif($applied_leave->leave_type_id==2){ //  Leave Type Sick

                // Chk remaining accumulated Sick leave

                $remain_sick = $accumlated_sick - $applied_leave->number_of_days;

                // if remaining accumulated sick leave > 0
                if($remain_sick>0){

                    // add leave to paid Sick bc remain_casual > 0
                    $paid_sick = $paid_sick + $applied_leave->number_of_days;

                    // unpaid 0
                    $unpaid_sick = $unpaid_sick;

                    // final leave reflect in accumulated causuall after deduction
                    $accumlated_sick = $remain_sick;

                }

                // chk case remaining accumulated casual leave < 0

                if($remain_sick<0){
                    $accumlated_sick = 0;

                    // remain Sick change value '-' to '+'  and it goes to unpaid.
                    $unpaid_sick_now = abs($remain_sick);
                    $unpaid_sick = $unpaid_sick + $unpaid_sick_now;

                    // chk apply no of leaves - unpaid casual leave
                    $paid_sick_now = $applied_leave->number_of_days - $unpaid_sick;
                    $paid_sick = $paid_sick + $paid_sick_now;
                }

                $paidCount = $paid_sick;
                $unpaidCount = $unpaid_sick;

            }
            elseif($applied_leave->leave_type_id==4){ // For maternity Leave Type
                $balance_maternity = $balance_maternity-$applied_leave->number_of_days;
                $paidCount = $applied_leave->number_of_days;
                $unpaidCount = 0;
            }
            elseif($applied_leave->leave_type_id==7){ // For paternity Leave Type
                $balance_paternity = $balance_paternity-$applied_leave->number_of_days;
                $paidCount = $applied_leave->number_of_days;
                $unpaidCount = 0;
            }
            elseif($applied_leave->leave_type_id==5){ // For compensatory
                $compensatory_count = $compensatory_count + $applied_leave->number_of_days;
                $paidCount = $applied_leave->number_of_days;
                $unpaidCount = 0;
            }

            saveLeaveSegration($applied_leave, $paidCount, $unpaidCount);

            // Update  a Leave Record On Leave Detailed Table

            $update_approval_data = [
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
                'compensatory_count'=> $compensatory_count,
                'isactive' => 1
            ];

            LeaveDetail::where('id', $id_to_update)->update($update_approval_data);
        }
    }
}


function saveLeaveSegration($applied_leave, $paid_count, $unpaid_count){
    $leaveSegregations = $applied_leave->appliedLeaveSegregations;

    if(count($leaveSegregations) > 1) {
        foreach ($leaveSegregations as $key => $leaveSegregation) {
            // if($key != 0){
            $leaveRequire = $leaveSegregation->number_of_days;
            $remainingLeave = $leaveRequire;
            $paidLeaveUse = 0;
            if ($paid_count > 0) {
                if ($leaveRequire <= $paid_count) {
                    $paidLeaveUse = $leaveRequire;
                    $remainingLeave = 0;
                    $paid_count = $paid_count - $leaveRequire;
                } elseif ($paid_count < $leaveRequire) {
                    $paidLeaveUse = $paid_count;
                    $remainingLeave = $leaveRequire - $paid_count;
                    $paid_count = 0;
                }
            }

            $unpaidLeave = $remainingLeave;

            if($applied_leave->leave_type_id == 5){
                \App\AppliedLeaveSegregation::where('id', $leaveSegregation->id)->update([
                    'compensatory_count' => $applied_leave->number_of_days
                ]);
            }else{
                \App\AppliedLeaveSegregation::where('id', $leaveSegregation->id)->update([
                    'paid_count' => $paidLeaveUse,
                    'unpaid_count' => $unpaidLeave,
                    'compensatory_count' => 0
                ]);
            }
        }
    }else {
        if($applied_leave->leave_type_id == 5){
            $update_leave_data = [
                'compensatory_count' => $applied_leave->number_of_days
            ];
        }else {
            $update_leave_data = [
                'paid_count' => $paid_count,
                'unpaid_count' => $unpaid_count,
                'compensatory_count' => 0
            ];
        }
        $applied_leave->appliedLeaveSegregations()->update($update_leave_data);
    }
}

function probationCalculations($user)
{
    $probation_data = DB::table('employee_profiles as emp')
        ->join('employees as e','e.user_id','=','emp.user_id')
        ->leftJoin("probation_periods as pp",'emp.probation_period_id','=','pp.id')
        ->where(['emp.user_id'=>$user->id])
        ->select('e.joining_date','pp.no_of_days')
        ->first();

    $current_year = date("Y");
    $current_month = date("n");

    $sum_data = DB::table('applied_leaves as al')
        ->join('applied_leave_segregations as als','als.applied_leave_id','=','al.id')
        ->where(['al.isactive'=>1,'al.final_status'=>'1','al.user_id'=>$user->id])
        ->whereYear('als.from_date',$current_year)
        ->select(DB::raw("SUM(als.paid_count) as paid_count,SUM(als.unpaid_count) as unpaid_count"))
        ->first();

    $compensatory_leaves = CompensatoryLeave::where(['hr_verification'=>'1','hod_verification'=>'1','isactive'=>1,'user_id'=>$user->id,'applied_leave_id'=>0])
        ->orderBy('from_date','ASC')
        ->get();

    if($current_month == '1'){
        $total_leaves = 24;

    }
    /* elseif($current_month == '12'){
        $total_leaves = 2;

    }
    */
    else{
        $remaining_months = 12 - $current_month;
        $total_leaves = 2 * $remaining_months;

    }

    if(!empty($probation_data)){
        if($sum_data->paid_count == ""){
            $probation_data->paid_count = 0;
        }else{
            $probation_data->paid_count = $sum_data->paid_count;
        }
        if($sum_data->unpaid_count == ""){
            $probation_data->unpaid_count = 0;
        }else{
            $probation_data->unpaid_count = $sum_data->unpaid_count;
        }

        if($probation_data->no_of_days !== '' || $probation_data->no_of_days !== null){
            $end_date = Carbon::parse($probation_data->joining_date)->addDays($probation_data->no_of_days)->toDateString();
            $probation_data->probation_end_date = $end_date;

            $probation_end_year = date("Y",strtotime($end_date));
            $probation_end_month = date("n",strtotime($end_date));

            if(strtotime(date("Y-m-d")) > strtotime($end_date)){
                $probation_data->probation_end_or_not = '1';
                $remaining_months = 12 - ($probation_end_month - 1);
                $total_leaves = 2 * $remaining_months;
                $probation_data->total_leaves = $total_leaves;

                if($current_year > $probation_end_year){
                    $probation_data->total_leaves = 24;
                }
            }else{
                $probation_data->probation_end_or_not = '0';
                $probation_data->total_leaves = 0;

            }
        }else{
            $probation_data->probation_end_date = 'NA';
            $probation_data->probation_end_or_not = 'NA';  //Not Applicable
            $probation_data->total_leaves = 0;
        }

        $leaves_left = $probation_data->total_leaves - $probation_data->paid_count;

        if($leaves_left <= 0){
            $leaves_left = 0;
        }

        $probation_data->leaves_left = $leaves_left;
        $probation_data->compensatory_leaves_count = $compensatory_leaves->count();
        $probation_data->compensatory_leaves = $compensatory_leaves;
    }//end probation if
    return $probation_data;
}//end of function

if(!function_exists('getEmployeeProfile'))
{
    function getEmployeeProfileData($user_id)
    {
        $data = DB::table('employees')
            ->where('user_id',$user_id)
            ->first();

        return $data;
    }
}

if(!function_exists('getMyLimitedMessages'))
{
    function getMyLimitedMessages($staticPic,$profilePicPath,$employeeId,$limit = false)
    {
        $messages = DB::table('messages as m')
            ->join("employees as emp",'m.sender_id','=','emp.user_id')
            ->where(['m.receiver_id'=>$employeeId,'m.isactive'=>1])
            ->select('m.id','m.read_status',"emp.fullname",'m.label','m.message','m.created_at',DB::raw('CASE WHEN emp.profile_picture = "" OR emp.profile_picture IS NULL THEN "'.$staticPic.'" ELSE CONCAT("'.$profilePicPath.'",emp.profile_picture) END AS profile_pic'));

        if(empty($limit)) {
            $data = $messages->orderBy('m.created_at','desc')->get();
        }else{
            $data = $messages->orderBy('m.created_at','desc')->limit($limit)->get();
        }
        return $data;
    }
}

if(!function_exists('getMyLimitedNotifications'))
{
    function getMyLimitedNotifications($staticPic,$profilePicPath,$employeeId,$limit = false)
    {
        $notifications = DB::table('notifications as m')
            ->join("employees as emp",'m.sender_id','=','emp.user_id')
            ->where(['m.receiver_id'=>$employeeId,'m.isactive'=>1])
            ->select('m.id','m.read_status',"emp.fullname",'m.label','m.message','m.created_at',DB::raw('CASE WHEN emp.profile_picture = "" OR emp.profile_picture IS NULL THEN "'.$staticPic.'" ELSE CONCAT("'.$profilePicPath.'",emp.profile_picture) END AS profile_pic'));

        if(empty($limit)) {
            $data = $notifications->orderBy('m.created_at','desc')->get();
        }else {
            $data = $notifications->orderBy('m.created_at','desc')->limit($limit)->get();
        }
        return $data;
    }
}

function claculateNightsTwoDates($d1, $d2)
{
    $date1 = new DateTime($d1);
    $date2 = new DateTime($d2);
    $numberOfNights= $date2->diff($date1)->format("%a");
    return $numberOfNights+1;
}

function moneyFormat($val,$symbol='Rs. ',$r=2)
{
    return $symbol . number_format($val,$r);
}

function formatDate($date)
{
    return date("d-m-Y", strtotime($date));
}

function numberToWords($number = null)
{
    $value = new \NumberFormatter("en", NumberFormatter::SPELLOUT);
    return  $value->format($number);
}

function amountInWords(float $number)
{
    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $hundred = null;
    $digits_length = strlen($no);
    $i = 0;
    $str = array();
    $words = array(0 => '', 1 => 'one', 2 => 'two',
        3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six',
        7 => 'seven', 8 => 'eight', 9 => 'nine',
        10 => 'ten', 11 => 'eleven', 12 => 'twelve',
        13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen',
        16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen',
        19 => 'nineteen', 20 => 'twenty', 30 => 'thirty',
        40 => 'forty', 50 => 'fifty', 60 => 'sixty',
        70 => 'seventy', 80 => 'eighty', 90 => 'ninety');
    $digits = array('', 'hundred','thousand','lakh', 'crore');

    while( $i < $digits_length ) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;

        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;

            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;

            $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;

        } else $str[] = null;

    }

    $Rupees = implode('', array_reverse($str));
    $paise = ($decimal) ? "." . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
    return ($Rupees ? $Rupees . 'Rupees ' : '') . $paise;
}

function getAttendanceInfo($date,$user_id)
{

   $user = User::where(['id'=>$user_id])->with('employee')
        ->with('employeeProfile')
        ->with('employee')
        ->first();

    $dayofweek = date('w', strtotime($date));

    $exception_shift_info = ShiftException::where(['user_id'=>$user_id, 'week_day'=>$dayofweek])
        ->first();
    if($exception_shift_info){

        $shift_id = $exception_shift_info['shift_id'];

        $shift_details = Shift::where(['id'=>$shift_id])
            ->first();
        $shift_from_time = date("Y-m-d")." ".$shift_details['from_time'];

    }else{
        if(isset($user->employeeProfile->shift)){
            $shift_from_time = date("Y-m-d")." ".$user->employeeProfile->shift->from_time;
        }else{
            $shift_from_time ="";
        }


    }

    $date = date("Y-m-d",strtotime($date));
    $attendance = Attendance::where(['on_date'=>$date,'user_id'=>$user_id])
        ->latest()->first();

    $data['late'] = 0;
    $data['status'] = "";
    $data['first_punch'] = "";
    $data['last_punch'] = "";
    $data['first_punch_type'] = 'NA';
    $data['last_punch_type'] = 'NA';
    $data['secondary_leave_type'] = "";
    $data['description'] = "";
   $data['remarks'] = AttendanceRemark::where(['on_date'=>$date,'user_id'=>$user_id])
        ->value('remarks');

    if(!empty($attendance)){
        $data['status'] = $attendance->status;

        if($attendance->status == 'Leave'){
            $leave = DB::table('applied_leaves as al')
                ->where('al.from_date','<=',$date)
                ->where('al.to_date','>=',$date)
                ->where(['al.final_status'=>'1','al.user_id'=>$user_id])
                ->select('al.*',DB::raw("SUM(al.number_of_days) as total_days"))
                ->first();

            if(!empty($leave->total_days)){
                $data['secondary_leave_type'] = ($leave->secondary_leave_type == 'Full') ? 1 : $leave->total_days;

                $data['description'] = $leave->reason;
            }else{
                $attendance_punch = $attendance->attendancePunches()->first();
                if(!empty($attendance_punch)){
                    $attendance->status = 'Present';
                    $attendance->save();
                    $data['status'] = 'Present';
                }
            }

        }elseif($attendance->status == 'Travel') {
            $travel = TravelApproval::where(['isactive'=>1,'status'=>'approved','user_id'=>$user_id])
                ->where('date_from','<=',$date)
                ->where('date_to','>=',$date)
                ->first();

            $data['description'] = $travel->purpose;
        }

        if($data['status'] != 'Absent'){
            if(!$attendance->attendancePunches->isEmpty()){
                $first_punch = $attendance->attendancePunches()
                    ->orderBy('on_time','asc')->first();

                $last_punch = $attendance->attendancePunches()
                    ->orderBy('on_time','desc')
                    ->first();

                $data['first_punch'] = $first_punch->on_time;
                $data['last_punch'] = $last_punch->on_time;
                $data['first_punch_type'] = $first_punch->type;
                $data['last_punch_type'] = $last_punch->type;

                $holiday = Holiday::where('holiday_from','<=',$date)
                    ->where('holiday_to','>=',$date)
                    ->where('isactive',1)
                    ->first();

                if(strtotime(date("Y-m-d H:i",strtotime($data['first_punch']))) > strtotime(date('Y-m-d H:i',strtotime($shift_from_time)))){
                    if($attendance->status == 'Leave' && $leave->secondary_leave_type == 'Half' && $leave->leave_half == 'First'){
                        $data['late'] = 0;
                    }elseif($attendance->status == 'Leave' && $leave->secondary_leave_type == 'Short' && $leave->from_time == date('g:i A',strtotime($shift_from_time))){
                        $data['late'] = 0;

                    }elseif($attendance->status == 'Leave' && $leave->secondary_leave_type == 'Full'){
                        $data['late'] = 0;
                    }elseif(date("l",strtotime($attendance->on_date)) == 'Sunday' || !empty($holiday)){
                        $data['late'] = 0;
                    }else{
                        $data['late'] = 1;
                    }

                }

                $data['first_punch'] = date("h:i A",strtotime($data['first_punch']));
                $data['last_punch'] = date("h:i A",strtotime($data['last_punch']));

                if($data['last_punch'] == $data['first_punch']){
                    $data['last_punch'] = "";
                }
            }
        }

    }else{

        $holiday = Holiday::where('holiday_from','<=',$date)
            ->where('holiday_to','>=',$date)
            ->where('isactive',1)
            ->first();

        if(!empty($holiday)){
            $data['status'] = 'Holiday';
            $data['description'] = $holiday->name;
        }else{

            $leave = DB::table('applied_leaves as al')
                ->where('al.from_date','<=',$date)
                ->where('al.to_date','>=',$date)
                ->where(['al.final_status'=>'1','al.user_id'=>$user_id])
                ->select('al.*',DB::raw("SUM(al.number_of_days) as total_days"))
                ->first();

            if(!empty($leave->total_days)){
                $data['status'] = 'Leave';
                $data['description'] = $leave->reason;

                $data['secondary_leave_type'] = ($leave->secondary_leave_type == 'Full') ? 1 : $leave->total_days;
            }else{
                $travel = TravelApproval::where(['isactive'=>1,'status'=>'approved','user_id'=>$user_id])
                    ->where('date_from','<=',$date)
                    ->where('date_to','>=',$date)
                    ->first();

                if(!empty($travel)){
                    $data['status'] = 'Travel';
                    $data['description'] = $travel->purpose;
                }
            }
        }

    }

    if(strtotime($user->employee->joining_date) > strtotime($date)){
        $data['status'] = "N/A";
    }


    return $data;

}//end of function

function getBandCityClassDetails($band_id, $city_class_id){

    $data = DB::table('band_city_class')
        ->where('band_id', $band_id)
        ->where('city_class_id', $city_class_id)
        ->where('isactive',1)
        ->first();

    return $data;
}//end of function

function effectiveHolidays($joining_date,$holiday_array){
    $count = 0;
    if(count($holiday_array)){
        foreach ($holiday_array as $key => $value) {
            if(strtotime($joining_date) <= strtotime($value)){
                $count += 1;
            }
        }
    }
    return $count;
}//end of function

function effectiveSundays($joining_date,$sunday_array){
    $count = 0;
    if(count($sunday_array)){
        foreach ($sunday_array as $key => $value) {
            if(strtotime($joining_date) <= strtotime($value)){
                $count += 1;
            }
        }
    }
    return $count;
}//end of function

function numberFormat($val = 0, $decimals = 2, $decimalPoint = '.', $separator = '')
{
    return number_format($val, $decimals, $decimalPoint, $separator);
}

function bytesToSize($bytes = 0, $precision = 2) {
    $units = array('Bytes', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow   = min($pow, count($units) - 1);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * @param $oldSerialNo
 * @return mixed
 */
function generateSerialNumber($exstingCount)
{
    $year   = date('y');
    $month  = date('m');
    $number = str_pad($exstingCount, 5, '0', STR_PAD_LEFT);
    return $year . $month . $number;
}
function timeleft($to){


    // $fdate = \Carbon\Carbon::createFromFormat('Y-m-d', $from);
    $current = Carbon::now();
    // exit;
    //$fdate = Carbon::parse($from);
    $tdate = Carbon::parse($to);


    // $tdate = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $to);
    // return $diff_in_hours_mins = $current->diff($tdate, false)->format('%H hours  %I Mins');
    return $diff_in_hours_mins = $tdate->diffForHumans($current);

}

function file_get_contents_curl($url) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

?>
