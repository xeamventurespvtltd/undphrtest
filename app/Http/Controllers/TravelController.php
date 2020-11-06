<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use View;

use App\Company;
use App\Location;
use App\State;
use App\EsiRegistration;
use App\PtRegistration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\User;
use App\Employee;
use App\Log;
use App\Project;
use App\ProjectContact;
use App\Country;
use App\City;
use App\SalaryStructure;
use App\SalaryCycle;
use App\DocumentCategory;
use App\Department;
use App\LeaveAuthority;
use App\Conveyance;
use App\Band;

//Travel Module Models
use App\TravelImprest;
use App\TravelApproval;
use App\TravelOtherApproval;
use App\TravelStay;
use App\TravelClaim;
use App\TravelClaimDetail;
use App\TravelClaimAttachment;

use stdClass;
use Validator;

class TravelController extends Controller
{
    function getClaimDefaultValues($id){
        $data['approval']=TravelApproval::where('id', $id)
                            ->with([
                                    'project',
                                    'approved_by_user.employee',
                                    'user.designation.band',
                                    'user.employeeAccount',
                                    'stay', 
                                    'conveyance_travel', 
                                    'conveyance_local', 
                                    'city_from', 
                                    'city_from.state', 
                                    'city_to', 
                                    'city_to.state',
                                    'imprest',
                                    'other_approval',
                                    'user.employee'

                                ])
                            ->first();
        $claimed_amount=$eligible_amount_stay=$stay_sum=0;
        if($data['approval']->stay->count()){
            foreach ($data['approval']->stay as $stay) {
                $days_between_dates=claculateNightsTwoDates($stay->from_date, $stay->to_date);
                $stay_sum+=($stay->rate_per_night*$days_between_dates)+$stay->da;
                $band_class=getBandCityClassDetails($data['approval']->user->designation[0]->band_id, $stay->city->city_class_id);
                //echo '('.$band_class->price.'+'.$data['approval']->user->designation[0]->band->food_allowance.')*'.$days_between_dates.'<br>';
                $eligible_amount_stay+=($band_class->price+$data['approval']->user->designation[0]->band->food_allowance)*$days_between_dates;
            }
        }
        
        $travel_conveyance=implode(",",$data['approval']->conveyance_travel->pluck('name')->toArray());
        $local_conveyances=implode(",",$data['approval']->conveyance_local->pluck('name')->toArray());

        $data['conveyances']=Conveyance::where('isactive', 1)->orderBy('islocal', 'asc')->orderBy('name', 'asc')->get();
        $data['eligible_conveyance']=$travel_conveyance . ','.$local_conveyances;
        $data['eligible_amount_stay']=$eligible_amount_stay;
        $data['stay_sum']=$stay_sum;
        $data['amount_approved']=$data['approval']->expected_amount+$data['approval']->expected_amount_local+$data['stay_sum'];
        if($data['approval']->other_approval)
            $data['amount_approved']+=$data['approval']->other_approval->amount;
        return $data;
    }
    
    function claimView(Request $request){
        if(!$request->id){
            exit;
        }
        
        $id=decrypt($request->id);
        $data=$this->getClaimDefaultValues($id);
        $data['claims']=TravelClaim::where('travel_approval_id', $id)
                        ->where('isactive', 1)
                        ->with([
                            'claim_details', 
                            'claim_attachments', 
                            'claim_details.expense_types', 
                            'claim_attachments.attachment_types'

                        ])
                        ->first();
        if($request->send_it_back){
            TravelClaim::where('id', $data['claims']->id)
                ->update([
                    'status'=>'back',
                    'ispaid'=>0,
                ]);

            if((is_array($request->claim_attachments) && count($request->claim_attachments)>0) || (isset($request->claim_details) && count($request->claim_details)>0)){
                if(is_array($request->claim_details) && count($request->claim_details)){
                    for($i=0;$i<count($request->claim_details); $i++){
                        TravelClaimDetail::where('id', $request->claim_details[$i])
                            ->update([
                                'status'=>'back'
                            ]);
                    }
                }
                if(is_array($request->claim_attachments) && count($request->claim_attachments)){
                    for($i=0;$i<count($request->claim_attachments); $i++){
                        TravelClaimAttachment::where('id', $request->claim_attachments[$i])
                            ->update([
                                'status'=>'back'
                            ]);
                    }
                }
                
                return redirect()->back()->with('success','Claim sent back successfully.');
            }
        }
        elseif($request->pay_approve){
            if(TravelClaim::where('utr', $request->utr)->count()){
                return redirect()->back()->with('danger','UTR already exists.');    
            }
            else{
                TravelClaimDetail::where('travel_claim_id', $data['claims']->id)
                            ->update([
                                'status'=>'paid'
                            ]);
                TravelClaimAttachment::where('travel_claim_id', $data['claims']->id)
                            ->update([
                                'status'=>'paid'
                            ]);
                TravelClaim::where('id', $data['claims']->id)
                    ->update([
                        'ispaid'=>1,
                        'utr'=>$request->utr,
                        'status'=>'paid'
                    ]);
                
                return redirect()->back()->with('success','Claim approved successfully.');
            }
        }
        
        $data['user'] = User::where(['id'=>Auth::id()])->first();
        //return $data;
        return view('travel.claim-view', $data); 
    }

    function claimForm(Request $request){
        
        if(!$request->id){
            exit;
        }

        $id=decrypt($request->id);
        $data=$this->getClaimDefaultValues($id);
        $data['user'] = User::where(['id'=>Auth::id()])->first();

        $data['claims']=TravelClaim::where('travel_approval_id', $id)
                        ->where('isactive', 1)
                        ->where('ispaid', 0)
                        ->with([
                            'claim_details', 
                            'claim_attachments', 
                            'claim_details.expense_types', 
                            'claim_attachments.attachment_types'
                        ])
                        ->first();

        if($request->submit_btn || $request->update_btn){
            if($request->update_btn){
                if($data['claims']->claim_attachments->count()){
                    foreach ($data['claims']->claim_attachments as $c) {
                        $file=public_path('uploads/travel-attachments/'.$c->attachment);
                        if(file_exists($file)){
                            unlink($file);
                        }
                    }
                }
                TravelClaim::where('travel_approval_id', $id)->delete();
            }
            if(!TravelClaim::where('travel_approval_id', $id)->where('isactive', 1)->count()){
                if($data['approval']->imprest){
                    $balance_amount=$data['amount_approved']-$data['approval']->imprest->amount;
                    $imprest_taken=$data['approval']->imprest->amount;
                }
                else{
                    $balance_amount=$data['amount_approved'];
                    $imprest_taken=0;
                }
                $obj=new TravelClaim;
                $obj->travel_approval_id=$data['approval']->id;
                $obj->bank=$data['approval']->user->employeeAccount->bank->name;
                $obj->account_no=$data['approval']->user->employeeAccount->bank_account_number;
                $obj->ifsc=$data['approval']->user->employeeAccount->ifsc_code;
                $obj->project=$data['approval']->project[0]->name;
                $obj->designation=$data['approval']->user->designation[0]->name;
                $obj->eligible_conveyance=$data['eligible_conveyance'];
                $obj->eligible_stay_amount=$data['eligible_amount_stay'];
                $obj->approved_amount=$data['amount_approved'];
                $obj->imprest_taken=$imprest_taken;
                $obj->balance_amount=$balance_amount;
                $obj->save();
                $travel_claim_id=$obj->id;
                for($i=0;$i<count($request->expense_date);$i++){
                    $obj=new TravelClaimDetail;
                    $obj->travel_claim_id=$travel_claim_id;
                    $obj->expense_date=date("Y-m-d", strtotime($request->expense_date[$i]));
                    $obj->from_location=$request->from_location[$i];
                    $obj->to_location=$request->to_location[$i];
                    $obj->expense_type=$request->expense_type[$i];
                    $obj->description=$request->description[$i];
                    $obj->amount=$request->amount[$i];
                    $obj->save();
                }
                for($i=0;$i<count($request->name);$i++){
                    $image_name = time().'-'.$i.'.'.$request->attachment[$i]->getClientOriginalExtension();
                    $request->attachment[$i]->move(public_path('uploads/travel-attachments'), $image_name);

                    $obj=new TravelClaimAttachment;
                    $obj->travel_claim_id=$travel_claim_id;
                    $obj->name=$request->name[$i];
                    $obj->attachment=$image_name;
                    $obj->attachment_type=$request->attachment_type[$i];
                    $obj->save();
                }
            }
            return redirect(url('travel/claim-view/'.encrypt($id)))->with('success','Claim saved successfully.');
        }

        return view('travel.claim-form', $data); 
    }

    function approvalRequestsDetails(Request $request){
        if(!$request->id)
            exit;
        $id=decrypt($request->id);
        $data['user'] = User::where(['id'=>Auth::id()])->first();

        $data['approval']=TravelApproval::where('id', $id)
                            ->with([
                                    'project',
                                    'user.designation.band',
                                    'stay', 
                                    'conveyance_travel', 
                                    'conveyance_local', 
                                    'city_from', 
                                    'city_from.state', 
                                    'city_to', 
                                    'city_to.state',
                                    'imprest',
                                    'other_approval',
                                    'user.employee'

                                ])
                            ->first();

        return view('travel.approval-requests-details', $data); 
    }

    //Display travel approval forma and save
    function approvalRequests(Request $request){

        $data['user'] = User::where(['id'=>Auth::id()])->first();
        $data['filter_status']='new';
        
        if($request->filter_status){
            $data['filter_status']=$request->filter_status;
        }
        $travel_obj=TravelApproval::where('status', $data['filter_status']);
        
        if (!$data['user']->can('approve-travel')) {
            $travel_obj->where('user_id', Auth::id());
        }
        
        $data['approvals']=$travel_obj->with([
                                                'project',
                                                'user.designation.band',
                                                'stay', 
                                                'conveyance_travel', 
                                                'conveyance_local', 
                                                'city_from', 
                                                'city_from.state', 
                                                'city_to', 
                                                'city_to.state',
                                                'imprest',
                                                'other_approval',
                                                'user.employee'
                                            ])
                                        ->orderBy('created_at', 'desc')
                                        ->get();
        
        return view('travel.approval-requests')->with(['data'=>$data]); 
    }

    function approvalForm(Request $request){
    	$data['countries'] = Country::where(['isactive'=>1])->get();
    	$data['states'] = State::where(['isactive'=>1])->get();
    	$data['user'] = User::where(['id'=>Auth::id()])
                            ->with(['designation', 'designation.band.local_conveyances', 'designation.band.travel_conveyances'])
                            ->first();
    	$data['projects']=Project::where('isactive', 1)->orderBy('name', 'asc')->get();
    	$data['conveyances']=Conveyance::where('isactive', 1)->where('islocal', 0)->orderBy('name', 'asc')->get();
    	$data['conveyances_local']=Conveyance::where('isactive', 1)->where('islocal', 1)->orderBy('name', 'asc')->get();

        if($request->btn_submit){
            //return $request->all();
            $travel_date_range_array=explode("-",$request->travel_date_range);
       
            $travel_date_from=date("Y-m-d", strtotime(trim($travel_date_range_array[0])));
            $travel_date_to=date("Y-m-d", strtotime(trim($travel_date_range_array[1])));

            if(TravelApproval::where('user_id', $data['user']->id)
                ->where('isactive', 1)//check if record exists for same dates or including 
                ->whereIn('status', ['new', 'hold', 'discussion', 'approved'])
                ->where(function ($query) use ($travel_date_from, $travel_date_to){
                    $query->whereBetween('date_from', [$travel_date_from, $travel_date_to])
                            ->orWhereBetween('date_to', [$travel_date_from, $travel_date_to]);
                
                })
                ->count()
            ){
                return redirect()->back()->with('error','Travel Approval already exists for selected dates.');
                //return 0;
            }
            else{
                $city=City::where('id', $request->city_id_to_pre)->with('city_class')->first();
                $obj=new TravelApproval;
                $obj->user_id=$data['user']->id;
                $obj->date_from=$travel_date_from;
                $obj->date_to=$travel_date_to;
                $obj->purpose=$request->purpose_pre;
                $obj->isclient=$request->isclient;
                
                $obj->city_id_from=$request->city_id_from_pre;
                $obj->city_id_to=$request->city_id_to_pre;
                $obj->city_class=$city->city_class->name;
                $obj->expected_amount=$request->expected_amount;
                $obj->expected_amount_local=$request->expected_amount_local;
                $obj->under_policy=$request->under_policy;
                $obj->status='new';
                $obj->save();

                $tavel_approval_id=$obj->id;
                $obj->conveyance_all()->attach($request->conveyance_id);
                $obj->conveyance_all()->attach($request->conveyance_id_local);

                DB::table('travel_approvalables')->insert(
                    [
                        'travel_approvalable_type' => 'App\Project', 
                        'travel_approvalable_id' => $request->project_id_pre,
                        'travel_approval_id' => $tavel_approval_id
                    ]
                );

                if($request->stay){

                    for($p=0;$p<count($request->city_id_stay);$p++){
                    $stay_date_range_array=explode("-",$request->stay_date_range[$p]);
                    $stay_date_from=date("Y-m-d", strtotime(trim($stay_date_range_array[0])));
                    $stay_date_to=date("Y-m-d", strtotime(trim($stay_date_range_array[1])));
                    
                    $obj=new TravelStay;
                    $obj->city_id=$request->city_id_stay[$p];
                    $obj->travel_approval_id=$tavel_approval_id;
                    $obj->from_date=$stay_date_from;
                    $obj->to_date=$stay_date_to;
                    $obj->rate_per_night=$request->rate_per_night[$p];
                    $obj->da=$request->da[$p];
                    $obj->save();
                    }
                    }
                    if($request->other_financial_approval){
                    $obj=new TravelOtherApproval;
                    $obj->city_id=$request->city_id_other;
                    $obj->travel_approval_id=$tavel_approval_id;
                    $obj->purpose=$request->purpose_other;
                    $obj->amount=$request->amount_other;
                    $obj->save();
                    
                    DB::table('travel_other_approvalables')->insert(
                    [
                    'travel_other_approvalable_type' => 'App\Project', 
                    'travel_other_approvalable_id' => $request->project_id_other,
                    'travel_other_approval_id' => $obj->id
                    ]
                    );
                    }
                    if($request->imprest_request){
                    $obj=new TravelImprest;
                    $obj->travel_approval_id=$tavel_approval_id;
                    $obj->remarks_by_applicant=$request->remarks;
                    $obj->amount=$request->amount_imprest;
                    $obj->save();
                    
                    DB::table('travel_imprestables')->insert(
                    [
                    'travel_imprestable_type' => 'App\Project', 
                    'travel_imprestable_id' => $request->project_id_imprest,
                    'travel_imprest_id' => $obj->id
                    ]
                    );
                    }
                return redirect()->back()->with('success','Travel Approval saved successfully.');
            }
        }
    	return view('travel.approval_form')->with(['data'=>$data]); 
    }

    function dashboard(Request $request){
        //return $request->all();
        if($request->btn_submit && $request->id){
            $id=decrypt($request->id);
            if($request->mark=='discussion'){
                $status='discussion';
                $message = 'Travel Approval marked for discussion.';
            }
            elseif($request->mark=='hold'){
                $status='hold';
                $message = 'Travel Approval marked on hold.';
            }
            elseif($request->mark=='discarded'){
                $status='discarded';
                $message = 'Travel Approval marked as discarded.';
            }
            elseif($request->mark=='approved'){
                $status='approved';
                $message = 'Travel Approval marked as approved.';
            }
            else 
                exit;

            if(!$request->id)
                exit;
            $id=decrypt($request->id);
            TravelApproval::where('id', $id)
                ->update([
                    'status'=>$status,
                    'approved_by'=>Auth::id(),
                    'remarks'=>$request->remarks
                ]);
            
            return redirect()->back()->with('success',$message);

        }
    	//return $request->all();
    }
}
