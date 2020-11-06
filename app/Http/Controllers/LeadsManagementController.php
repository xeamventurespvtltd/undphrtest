<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Employee;
use App\LeadSource;
use App\LeadIndustry;
use App\LeadApproval;
use App\LeadAuthority;
use App\Lead;
use App\AssignedUsers;
use App\BdTeam;
use App\BdTeamMember;
use App\Comments;
use App\LeaveAuthority;
use App\FeeType;
use App\Obligation;
use App\Vertical;
use App\PaymentTerm;

use App\Til;
use App\TilContact;
use App\TilObligations;
use App\TilSpecialEligibility;
use App\TilInputs;

use App\TilDraft;
use App\TilDraftContact;
use App\TilDraftObligation;
use App\TilDraftSpecialEligibility;
use App\TilDraftInputs;

use App\CostFactorMaster;
use App\CostFactorTypes;
use App\CostEstimation;
use App\CostEstimationDraft;

use App\Department;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Builder;

class LeadsManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        $authUser  = \Auth::user();
        $userId    = $authUser['id'];

        if(in_array($userId, [1, 13])) {
          $message = 'Request not allowed.';
          return redirect()->route('leads-management.get-leads')->withError($message);
        }
        
        $hodId = $teamRoleId = null; $filter = $teamUsers = [];
        $leadType = 'all';
        if($request->isMethod('get') && !empty($request->all())) {
            $inputs = $request->all();

            if(isset($inputs['lead_type']) && $inputs['lead_type'] == 'created') {
                $leadType = 'created';
            } else if(isset($inputs['lead_type']) && $inputs['lead_type'] == 'assigned') {
                $leadType = 'assigned';
            } else {
                $leadType = 'all';
            }
        }

        $userDetails = $authUser->employeeProfile()->first();
        $bdMember = BdTeamMember::Where('user_id', $userId)->first();

        $bdUser = User::with('employee')->whereHas('employeeProfile', function (Builder $query) use($userDetails) {
            $departmentId = $userDetails->department_id; // B.D department id
            $query->where(['department_id' => $departmentId]);
        })->first();

        $teamHodId = $bdUser->leaveAuthorities()->where(['leave_authorities.priority' => '2'])->pluck('manager_id')->first();

        $existingUsers=BdTeamMember::where('isactive',1)->pluck('team_role_id','user_id')->all();

        if(!empty($existingUsers) && isset($existingUsers[$userId]) && $existingUsers[$userId]) {
            $teamRoleId = $existingUsers[$userId]; // 1->executive, 2 -> manager

            if($teamRoleId == 1) {
                $filter['executive_id'] = $userId;
            }
            $teamUsers = array_keys($existingUsers);
        } else if(!empty($teamHodId) && $teamHodId == $userId) {
            $hodId = $teamHodId;
            $teamUsers = array_keys($existingUsers);
        } else {
            $filter['user_id'] = $userId;
        }

        $leadList = Lead::with(['source', 'industry', 'leadExecutives'])->where('isactive', 1);
        // $leadType == 1 created Leads, 2 assigned Leads
        if(!empty($filter) && isset($filter['executive_id']) || !empty($teamUsers)) {
            
            if($leadType == 'created') {
                $leadList->where('user_id', $userId);
            } else if($leadType == 'assigned') {

                if(!empty($filter['executive_id'])) {
                    $leadList->where('executive_id', $filter['executive_id']);
                } else {
                    $leadList->where('executive_id', $userId);
                }
            }/* else {
                $leadList->whereIn('executive_id', $teamUsers);
            }*/
        } else {
            $leadList->where($filter);
        }

        $leadList->whereIn('status', [1, 2, 3, 4]); //5, 6;
        $leadList = $leadList->orderBy('id', 'desc')->get();
        $leadList = $leadList->all();

        return view('leads_management.index', compact('leadList', 'bdMember', 'userId', 'teamRoleId', 'hodId', 'leadType'));
    }//end of function

    /**
     * Display a listing of the resource to admin.
     *
     * @return \Illuminate\Http\Response
    */
    public function getLeads(Request $request)
    {
        $userId = \Auth::user()->id;
        if(!in_array($userId, [1, 13])) {
            $message = 'Request not allowed.';
            return redirect()->back()->withError($message);
        }

        $leadType = 'all';
        if($request->isMethod('get') && !empty($request->all())) {
            $inputs = $request->all();

            if(isset($inputs['lead_type']) && $inputs['lead_type'] == 'created') {
                $leadType = 'created';
            } else {
                $leadType = 'all';
            }
        }

        $filter = ['isactive' => 1];
        // status == 1 New, 2 Open, 3 Complete, 4 Rejected by Hod, 5 Closed, 6 Abandoned,
        $leadList = Lead::with(['source', 'industry'])->where($filter)->whereIn('status', [1, 2, 3, 4, 6]); // , 5

        if($leadType == 'created') {
            $leadList->where('user_id', $userId);
        }
        $leadList = $leadList->orderBy('id', 'desc')->get();
        $leadList = $leadList->all();

        return view('leads_management.get_leads', compact('leadList', 'leadType'));
    }//end of function

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function create()
    {
        $leadSourceOptions   = (new LeadSource)->getListLeadSource();
        $leadIndustryOptions = (new LeadIndustry)->getListLeadIndustry();
        
        return view('leads_management.create', compact('leadSourceOptions', 'leadIndustryOptions'));
    }//end of function

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {
        $authUser = \Auth::user();
        $userId = $authUser->id;

        if($request->isMethod('post')) {

            $data = $request->all();
            $inputs = $request->except('file_name');
            
            $validator = (new Lead)->validateLeads($data);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->messages())->withInput($inputs);
            }

            $fileNameArr  = [];
            /*$exstingCount = Lead::max('lead_code');
              $exstingCount = (empty($exstingCount))? Lead::count() + 1 : $exstingCount + 1;
            */
            $leadCount = Lead::count() + 1;
            $serialNumber = generateSerialNumber($leadCount);

            $teamTypeArr = [1 => 'govt', 2 => 'corp'];
            try {
                \DB::beginTransaction();
                unset($inputs['_token']);

                if(!empty($inputs['due_date']) && $inputs['due_date'] != '0000-00-00 00:00:00') {
                   $inputs['due_date'] = date('Y-m-d H:i:s', strtotime($inputs['due_date']));
                }
                
                $inputs['user_id']   = $userId;
                $inputs['lead_code']   = $serialNumber;
                $inputs['source_id'] = $inputs['sources'];

                if ($request->hasFile('file_name')) {
                    
                    $fileOzName      = str_replace(' ', '', $request->file('file_name')->getClientOriginalName());
                    $fileOzExtension = $request->file('file_name')->getClientOriginalExtension();

                    $fileName = time() . '_' . pathinfo(strtolower($fileOzName), PATHINFO_FILENAME) . '.' . $fileOzExtension;

                    $leadServiceDir = \Config::get('constants.uploadPaths.leadDocuments');

                    if(!is_dir($leadServiceDir)) {
                        mkdir($leadServiceDir, 0775);
                    }

                    $request->file('file_name')->move($leadServiceDir, $fileName);

                    $fileNameArr['file'] = $leadServiceDir.DIRECTORY_SEPARATOR.$fileName;
                    $inputs['file_name'] = $fileName;
                }

                if(isset($inputs['contact_person_mobile'])) {
                    $inputs['contact_person_no'] = $inputs['contact_person_mobile'];
                }

                if(isset($inputs['contact_person_alternate'])) {
                    $inputs['alternate_contact_no'] = $inputs['contact_person_alternate'];
                }

                if(isset($inputs['contact_person_email'])) {
                    $inputs['email'] = $inputs['contact_person_email'];
                }               
                
                $inputs['isactive'] = 1;
                $inputs['status']   = 1;

                $teamType    = $teamTypeArr[$inputs['business_type']];
                $bdExecutive = BdTeam::where(['bd_teams.isactive' => 1, 'team_type' => $teamType])
                            ->leftjoin('bd_team_members', function ($join) {
                            $join->on('bd_teams.id', '=', 'bd_team_members.bd_team_id')
                            ->where(['team_role_id' => 1, 'bd_team_members.isactive' => 1]);
                            })->select('bd_team_members.*')->orderBy('leads_counter', 'ASC')->first();
                
                if(empty($bdExecutive)) {
                    throw new \Exception("B.d team or executive not found. Please create a B.d team to continue.", 151);
                }

                $inputs['executive_id'] = $bdExecutive->user_id;

                $lead = (new Lead)->store($inputs);

                if(!empty($lead)) {
                    $executiveInputs = new AssignedUsers;
                    $executiveInputs->user_id = $inputs['executive_id'];
                    $executiveInputs->type    = 1;
                    $executiveInputs->wef     = date('Y-m-d H:i');
                    $executiveInputs->is_active = 1;
                    $executiveUser = $lead->assignedUsers()->save($executiveInputs);

                    $executive = BdTeamMember::find($bdExecutive->id);
                    $executive->leads_counter = ($executive->leads_counter + 1);
                    $executive->update();

                    $notificationMessage = $authUser->employee->fullname . " has assigned you a new lead with lead number".$serialNumber.".";

                    $notificationData = [
                        'sender_id' => $lead->user_id,
                        'receiver_id' => $inputs['executive_id'],
                        'label' => 'Lead Assigned',
                        'read_status' => '0',
                        'redirect_url' => 'leads-management/view-leads/'.$lead->id,
                        'message' => $notificationMessage
                    ];
                    $lead->notifications()->create($notificationData);
                    sms($lead->leadExecutives->mobile_number, $notificationMessage);

                } else {
                    throw new \Exception("Error occurs please try again.", 151);
                }

                \DB::commit();

                $route = 'leads-management.index';
                if($userId == 13) {
                  $route = 'leads-management.get-leads';
                }

                $message = 'Lead Created Successfully.';
                return redirect()->route($route)->withSuccess($message);
            } catch (\PDOException $e) {
                \DB::rollBack();

                if(isset($fileNameArr['file'])) {
                    unlink($fileNameArr['file']);
                }

                return redirect()->back()->withError('Database Error: The lead could not be saved.')->withInput($inputs);
            } catch (\Exception $e) {
                \DB::rollBack();

                if(isset($fileNameArr['file'])) {
                    unlink($fileNameArr['file']);
                }

                $message = 'Error code 500: internal server error.';
                if($e->getCode() == 151) {
                    $message = $e->getMessage();
                }
                // $e->getMessage()
                return redirect()->back()->withError($message)->withInput($inputs);
            }
        }
    }//end of function

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function view($id = null)
    {
        if(!$id) {
            $message = 'Invalid id provided';
            return redirect()->back()->withError($message);
        }

        $userId = \Auth::user()->id;

        if(!in_array($userId, [1, 13])) {
          $message = 'Request not allowed.';
          return redirect()->back()->withError($message);
        }

        $lead                = Lead::findOrFail($id);
        $leadSourceOptions   = (new LeadSource)->getListLeadSource();
        $leadIndustryOptions = (new LeadIndustry)->getListLeadIndustry();

        return view('leads_management.view', compact('lead', 'leadSourceOptions', 'leadIndustryOptions'));
    }//end of function

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function viewLeads($id = null)
    {
        if(!$id) {
            $message = 'Invalid id provided';
            return redirect()->back()->withError($message);
        }

        $authUser = \Auth::user();
        $userId   = $authUser['id'];

        if(in_array($userId, [1, 13])) {
          $message = 'Request not allowed.';
          return redirect()->route('leads-management.get-leads')->withError($message);
        }

        $rawQry = $teamRoleId = $teamUsers = $hodId = null;
        $filter = "id = ". $id;

        $userDetails = $authUser->employeeProfile()->first();
        $bdMember = BdTeamMember::Where('user_id', $userId)->first();//1->executive, 2->manager
        
        $bdUser = User::with('employee')->whereHas('employeeProfile', function (Builder $query) use($userDetails) {
            $departmentId = $userDetails->department_id; // B.D department id
            $query->where(['department_id' => $departmentId]);
        })->first();

        $teamHodId = $bdUser->leaveAuthorities()->where(['leave_authorities.priority' => '2'])->pluck('manager_id')->first();

        $existingUsers=BdTeamMember::where('isactive',1)->pluck('team_role_id','user_id')->all();

        if(!empty($existingUsers) && isset($existingUsers[$userId]) && $existingUsers[$userId]) {
            $teamRoleId = $existingUsers[$userId]; // 1->executive, 2 -> manager

            /*if($teamRoleId == 1) {
                $filter .= " AND (user_id = ". $userId ." OR executive_id = ". $userId ." )";
            }*/
        } else if(!empty($teamHodId) && $teamHodId == $userId) {
            $hodId   = $teamHodId;
           /* $teamUsers = array_keys($existingUsers);
            $filter .= " AND (executive_id IN(". implode(',', $teamUsers) ."))";*/
        } else {
            $hodId = null;
            $filter .= " AND (user_id = ". $userId .")";
        }

        $filter .= ' AND status IN (1,2,3,4,5)'; // 6
        $lead = Lead::whereRaw($filter)->first();

        if(!$lead) {
            $message = 'No data found.';
            return redirect()->back()->withError($message);
        }

        $leadSourceOptions   = (new LeadSource)->getListLeadSource();
        $leadIndustryOptions = (new LeadIndustry)->getListLeadIndustry();

        return view('leads_management.view_leads', compact('lead', 'leadSourceOptions', 'leadIndustryOptions', 'bdMember', 'hodId', 'authUser'));
    }//end of function

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function edit($id)
    {
       if(!$id) {
            $message = 'Invalid id provided';
            return redirect()->back()->withError($message);
        }

        $authUser = \Auth::user();
        $userId   = $authUser['id'];

        if($userId == 13) {
          $message = 'Request not allowed.';
          return redirect()->route('leads-management.get-leads')->withError($message);
        }

        $lead = Lead::where(['executive_id' => $userId])->find($id);
        if(!$lead) {
            $message = 'No data found.';
            return redirect()->back()->withError($message);
        }
        if($lead->is_completed == 1) {
            $message = 'You don\'t have permission to edit this.';
            return redirect()->back()->withError($message);
        }

        $leadSourceOptions   = (new LeadSource)->getListLeadSource();
        $leadIndustryOptions = (new LeadIndustry)->getListLeadIndustry();

        return view('leads_management.edit', compact('lead', 'leadSourceOptions', 'leadIndustryOptions'));
    }//end of function

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $id = null)
    {
        if(!$id) {
            $message = 'Invalid Lead id provided.';
            return redirect()->back()->withError($message);
        }

        $lead = Lead::findOrFail($id); // leadApproval
        if(!$lead) {
            $message = 'Error: Lead data not found.';
            return redirect()->back()->withError($message);
        }

        if($request->isMethod('patch')) {
            $data = $request->except('_method', '_token');
            $data['file_name'] = $lead->file_name;

            $inputs = $request->except('file_name', '_method', '_token');
            
            if(isset($inputs['is_completed']) && $inputs['is_completed'] == 1) {
                $validator = (new Lead)->validateLeads($data, $id);
                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator->messages())->withInput($inputs);
                }
            }

            $teamTypeArr = [1 => 'govt', 2 => 'corp'];
            $fileNameArr = [];
            
            try {
                \DB::beginTransaction();

                $userId = \Auth::id();
                /*$inputs['user_id'] = \Auth::id();*/
                $inputs['source_id'] =  $inputs['sources'];                

                if ($request->hasFile('file_name')) {
                    $leadServiceDir = \Config::get('constants.uploadPaths.leadDocuments');

                    if(!empty($lead->file_name) && file_exists($leadServiceDir . $lead->file_name)) {
                        unlink($leadServiceDir . $lead->file_name);
                    }

                    $fileOzName      = str_replace(' ', '', $request->file('file_name')->getClientOriginalName());
                    $fileOzExtension = $request->file('file_name')->getClientOriginalExtension();

                    $fileName=time().'_'.pathinfo(strtolower($fileOzName), PATHINFO_FILENAME).'.'.$fileOzExtension;

                    $request->file('file_name')->move($leadServiceDir, $fileName);

                    $fileNameArr['file'][] = $leadServiceDir . DIRECTORY_SEPARATOR . $fileName;
                    $inputs['file_name'] = $fileName;
                }

                if(!empty($inputs['due_date'])) {
                   $inputs['due_date'] = date('Y-m-d H:i:s', strtotime($inputs['due_date']));
                }

                if(isset($inputs['contact_person_mobile'])) {
                    $inputs['contact_person_no'] = $inputs['contact_person_mobile'];
                }

                if(isset($inputs['contact_person_alternate'])) {
                    $inputs['alternate_contact_no'] = $inputs['contact_person_alternate'];
                }

                if(isset($inputs['contact_person_email'])) {
                    $inputs['email'] = $inputs['contact_person_email'];
                }
                
                $inputs['isactive'] = 1;
                if(isset($inputs['is_completed']) && $inputs['is_completed'] == 1) {
                    $inputs['status'] = 3;
                } else {
                    $inputs['status'] = 2;
                }

                if($lead->update($inputs)) {
                    if(isset($inputs['comments']) && !empty($inputs['comments'])) {
                        $commentsInputs = new Comments;
                        $commentsInputs->user_id  = $userId;
                        $commentsInputs->comments = $inputs['comments'];

                        if($request->hasFile('attachment')) {
                            $leadCommentsDir = \Config::get('constants.uploadPaths.leadComments');

                            $fileOzName = $request->file('attachment')->getClientOriginalName();
                            $fileOzName = str_replace(' ', '', $fileOzName);

                            $fileOzExtension = $request->file('attachment')
                                               ->getClientOriginalExtension();

                            $fileName = time().'_'.pathinfo(strtolower($fileOzName), PATHINFO_FILENAME).'.'.$fileOzExtension;

                            if(!is_dir($leadCommentsDir)) {
                                mkdir($leadCommentsDir, 0775);
                            }

                            $request->file('attachment')->move($leadCommentsDir, $fileName);

                            $fileNameArr['file'][] = $leadCommentsDir . DIRECTORY_SEPARATOR . $fileName;
                            $commentsInputs->attachment = $fileName;
                        }
                        $comments = $lead->comments()->save($commentsInputs);
                    }
                } else {
                    throw new \Exception("Error occurs please try again.", 151);
                }

                \DB::commit();

                $route   = 'leads-management.index';
                $message = 'Lead Updated Successfully.';
                return redirect()->route($route)->withSuccess($message);
            } catch (\PDOException $e) {
                \DB::rollBack();

                if(isset($fileNameArr['file']) && !empty($fileNameArr['file']) && count($fileNameArr['file']) > 0) {
                    $this->removeFiles($fileNameArr['file']);
                }
                return redirect()->back()->withError('Database Error: The lead could not be saved.')
                ->withInput($inputs);
            } catch (\Exception $e) {
                \DB::rollBack();

                if(isset($fileNameArr['file']) && !empty($fileNameArr['file']) && count($fileNameArr['file']) > 0) {
                    $this->removeFiles($fileNameArr['file']);
                }
                // $e->getMessage() 'Error code 500: internal server error.'
                return redirect()->back()->withError($e->getMessage())->withInput($inputs);
            }
        }
    }//end of function

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        $message = 'This action is not allowd.';
        return redirect()->back()->withError($message);
    }//end of function 

    /**
     * Rejecting the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function rejectLead(Request $request, $id = null)
    {
        $authUser = \Auth::user();
        $userId   = $authUser['id'];

        if(!$id) {
            $message = 'Invalid id provided';
            return redirect()->back()->withError($message);
        }

        $lead = Lead::find($id);
        if(!$lead) {
            $message = 'Error: Lead data not found.';
            $result = ['status' => 0, 'msg' => $message];
            return response()->json($result);
        }

        if($request->isMethod('POST') && $request->ajax()) {

            try {
                \DB::beginTransaction();

                $inputs = ['status' =>  6];
                $res = $lead->update($inputs);

                \DB::commit();

                $message = 'Error in rejecting lead, please try again later.';
                $result = ['status' => 0, 'msg' => $message];
                if($res) {
                    $message = 'Lead Rejected Successfully.';
                    $result  = ['status' => 1, 'msg' => $message];
                }
                return response()->json($result);
            } catch (\PDOException $e) {
                \DB::rollBack();

                $message = 'Database Error: The lead could not be rejected.';
                $result  = ['status' => 0, 'msg' => $message];
                return response()->json($result);
            } catch (\Exception $e) {
                \DB::rollBack();

                $message = 'Error code 500: internal server error.';
                $result  = ['status' => 0, 'msg' => $message];
                return response()->json($result);
            }
        }
    }//end of function 

    /**
     * Display a listing of the resource to admin.
     *
     * @return \Illuminate\Http\Response
    */
    public function unassinedLeads(Request $request)
    {
        $teamRoleId = null; $filter = [];
        $user = \Auth::user();
        $userId = $user->id;        
        $userDetails = $user->employeeProfile()->first();
        $memberDetails = BdTeamMember::Where('user_id', $userId)->first();

        $bdUser = User::with('employee')->whereHas('employeeProfile', function (Builder $query) use($userDetails) {
            $departmentId = $userDetails->department_id; // B.D department id
            $query->where(['department_id' => $departmentId]);
        })->first();

        $teamHodId = $bdUser->leaveAuthorities()->where(['leave_authorities.priority' => '2'])->pluck('manager_id')->first();

        if((!empty($memberDetails) && $memberDetails->team_role_id != 2) && ($userId != $teamHodId)) {
            $message = 'Request not allowed.';
            return redirect()->back()->withError($message);
        }

        $departmentId = $userDetails->department_id;
        $bdEmployees  = \DB::table('bd_team_members as tm')
                        ->join('employees as e', 'tm.user_id', '=', 'e.user_id')
                        ->join('employee_profiles as ep', 'e.user_id', '=', 'ep.user_id')
                        ->where(['ep.department_id' => $departmentId, 'e.approval_status' => '1', 'e.isactive' => 1, 'ep.isactive' => 1])
                        ->when($teamHodId, function ($query, $teamHodId) {
                            return $query->where('e.id', '!=', $teamHodId);
                        })->select('e.user_id', 'e.fullname')
                        ->get()->all();


        $filter = ['isactive' => 1, 'executive_id' => 0];
        // status == 1 New, 2 Open, 3 Complete, 4 Rejected by Hod, 5 Closed, 6 Abandoned,
        $leadList = Lead::with(['source', 'industry'])->where($filter);
        //->whereIn('status', [4, 5, 6]);
        $leadList = $leadList->orderBy('id', 'desc')->get();
        $leadList = $leadList->all();

        return view('leads_management.unassigned_leads', compact('leadList', 'bdEmployees'));
    }//end of function

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function approveLead(Request $request)
    {
        $authUser = \Auth::user();
        $userId   = $authUser['id']; 

        $hodId = $rawQry = $teamRoleId = null; $teamUsers = [];

        $leadType = 'all';
        if($request->isMethod('get') && !empty($request->all())) {
            $inputs = $request->all();

            if($userId == 13) {
                if(isset($inputs['lead_type']) && $inputs['lead_type'] == 'created') {
                    $leadType = 'created';
                } else {
                    $leadType = 'all';
                }
            } else {
                if(isset($inputs['lead_type']) && $inputs['lead_type'] == 'created') {
                    $leadType = 'created';
                } else if(isset($inputs['lead_type']) && $inputs['lead_type'] == 'assigned') {
                    $leadType = 'assigned';
                } else {
                    $leadType = 'all';
                }
            }
        }

        $filter = [];

        $userDetails = $authUser->employeeProfile()->first();
        $bdMember = BdTeamMember::Where('user_id', $userId)->first();

        $bdUser = User::with('employee')->whereHas('employeeProfile', function (Builder $query) use($userDetails) {
            $departmentId = $userDetails->department_id; // B.D department id
            $query->where(['department_id' => $departmentId]);
        })->first();

        $teamHodId = $bdUser->leaveAuthorities()->where(['leave_authorities.priority' => '2'])->pluck('manager_id')->first();

        $existingUsers = BdTeamMember::where('isactive', 1)->pluck('team_role_id', 'user_id')->all();

       if(!empty($existingUsers) && isset($existingUsers[$userId]) && $existingUsers[$userId]) {
            $teamRoleId = $existingUsers[$userId]; // 1->executive, 2 -> manager

            if($teamRoleId == 1) {
                $filter['executive_id'] = $userId;
            } else if($teamRoleId == 2) {
                $teamUsers = array_keys($existingUsers);
            }
        } else if(!empty($teamHodId) && $teamHodId == $userId) {
            $hodId = $teamHodId;
            $teamUsers = array_keys($existingUsers);
        } else {
            if($userId != 13) {
                $filter['user_id'] = $userId;
            }
        }
        // status == 1 New, 2 Open, 3 Complete, 4 Rejected by Hod, 5 Closed, 6 Abandoned,
        $leadList = Lead::with(['userEmployee', 'source', 'industry', 'tilDraft']);
        
        if(!empty($filter) && isset($filter['executive_id']) || !empty($teamUsers)) {
            
            if($leadType == 'created') {
                $leadList->where('user_id', $userId);
            } else if($leadType == 'assigned') {
                
                if(!empty($filter['executive_id'])) {
                    $leadList->where('executive_id', $filter['executive_id']);
                } else {
                    $leadList->where('executive_id', $userId);
                }

            }/* else {
                $leadList->whereIn('executive_id', $teamUsers);
            }*/
        } else {

            if($userId == 13 && $leadType == 'created') {
                $leadList->where('user_id', $userId);
            } else {
                $leadList->where($filter);
            }
        }

        $leadList->where(['isactive' => 1, 'is_completed' => 1, 'status' => 5]);
        
        $leadList = $leadList->orderBy('id', 'desc')->get();
        $leadList = $leadList->all();
        
        return view('leads_management.list_lead_approvals', compact('leadList', 'userId', 'hodId', 'bdMember', 'leadType'));
    }//end of function

    /**
     * Performing actions like approving leads, change status etc.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function listAction(Request $request)
    {
        $authUser = \Auth::user();
        $userId = $authUser->id;

        if($request->isMethod('post')) {

            $inputs = $request->except('_token');
            
            try {
                \DB::beginTransaction();

                $leadIds  = explode(',', $inputs['lead_ids']);
                $assignTo = $inputs['assign_to'];

                $leadInputs = [];
                foreach ($leadIds as $key => $id) {
                    $lead = Lead::find($id);
                    $leadInputs = ['executive_id' => $assignTo];
                    $lead->update($leadInputs);
                    
                    $assignedUsers = $lead->assignedUsers()->where(['user_id' => $assignTo])->whereNull('wet')->first();

                    if(empty($assignedUsers)) {
                        $assignedInputs = new AssignedUsers;
                        $assignedInputs->user_id = $inputs['assign_to'];
                        $assignedInputs->type    = 1;
                        $assignedInputs->wef     = date('Y-m-d H:i');
                        $assignedInputs->is_active = 1;

                        $assignedUser = $lead->assignedUsers()->save($assignedInputs);

                        $notificationMessage = $authUser->employee->fullname . " has assigned you a new lead with lead number".$lead->lead_code.".";

                        $notificationData = [
                            'sender_id' => $lead->user_id,
                            'receiver_id' => $assignTo,
                            'label' => 'Lead Assigned',
                            'read_status' => '0',
                            'redirect_url' => 'leads-management/view-leads/'.$lead->id,
                            'message' => $notificationMessage
                        ];
                        $lead->notifications()->create($notificationData);
                        
                        sms($lead->leadExecutives->mobile_number, $notificationMessage);
                    }
                }

                \DB::commit();

                $route = 'leads-management.index';
                /*if($userId == 13) {
                  $route = 'leads-management.get-leads';
                }*/
                $message = 'The user id assigned to lead successfully.';
                return redirect()->route($route)->withSuccess($message);
            } catch (\PDOException $e) {
                \DB::rollBack();

                return redirect()->back()->withError('Database Error: The user id could not be assigned to lead.')->withInput($inputs);
            } catch (\Exception $e) {
                \DB::rollBack();

                return redirect()->back()->withError('Error code 500: internal server error.')->withInput($inputs);
            }
        }
    }//end of function

    public function changeLeadStatus($id = null)
    {
        if(!$id) {
            $message = 'Invalid Lead id provided.';
            return redirect()->back()->withError($message);
        }

        $lead = Lead::find($id);

        if(!$lead) {
            $message = 'Invalid Lead id provided.';
            return redirect()->back()->withError($message);
        }

        $leadInputs = ['isactive' => 1];
        if($lead->isactive == 1) {
            $leadInputs = ['isactive' => 0];
        }

        if($lead->update($leadInputs)) {
            return redirect()->back()->withSuccess('Lead status changed Successfully.');
        } else {
            return redirect()->back()->withError('Error occurs please try again.');
        }
    }//end of function

    public function leadApproval(Request $request)
    {
        if($request->isMethod('post')) {
            $userId = \Auth::user()->id;
            $inputs = $request->except('_token');
            
            if(!$inputs['lead_id']) {
                $message = 'Lead id not found.';
                return redirect()->back()->withError($message);
            }

            $lead = Lead::find($inputs['lead_id']);

            if(!$lead) {
                $message = 'Invalid Lead id provided.';
                return redirect()->back()->withError($message);
            }

            try {
                \DB::beginTransaction();

                //status== 1 New, 2 Open, 3 Complete, 4 Rejected by Hod, 5 Closed, 6 Abandoned
                $leadInputs  = ['status' => $inputs['status']];

                $leadInputs['is_completed'] = 0;
                if(in_array($inputs['status'], [3, 5])) {
                    $leadInputs['is_completed'] = 1;
                }

                if($lead->update($leadInputs)) {
                    $commentsInputs = new Comments;
                    $commentsInputs->user_id  = $userId;
                    $commentsInputs->comments = $inputs['comments'];
                    $comments = $lead->comments()->save($commentsInputs);
                } else {
                    return redirect()->back()->withError('Error occurs please try again.');
                }

                \DB::commit();

                $messageText = 'Lead successfully approved.';
                if($inputs['status'] == 4) {
                    $messageText = 'Lead successfully rejected.';
                }
                return redirect()->back()->withSuccess($messageText);
            } catch (\PDOException $e) {
                \DB::rollBack();

                $messageText = 'approved.';
                if($inputs['status'] == 4) {
                    $messageText = 'rejected.';
                }
                return redirect()->back()->withError('Database Error: The lead could not be '.$messageText.'.')->withInput($inputs);
            } catch (\Exception $e) {
                \DB::rollBack();
                return redirect()->back()->withError('Error code 500: internal server error.')->withInput($inputs);
            }
        }
    }//end of function

    public function unassignUser(Request $request)
    {
        if($request->isMethod('post')) {

            $userId = \Auth::user()->id;
            $inputs = $request->except('_token');

            try {
                \DB::beginTransaction();
                unset($inputs['_token']);

                $res  = false;
                $lead = Lead::find($inputs['lead_id']);
                $executiveId = $lead->executive_id;

                if(!empty($lead)) {
                    $leadInputs = ['executive_id' => null];
                    $res = $lead->update($leadInputs);

                    $assignedInputs = $lead->assignedUsers()->where(['user_id' => $executiveId])->whereNull('wet')->first();

                    $assignedInputs->wet = date('Y-m-d H:i');
                    $assignedUser = $assignedInputs->update();
                }

                \DB::commit();

                $message = 'Error in unassigning executive, please try again later.';
                $result = ['status' => 0, 'msg' => $message];
                if($res) {
                    $message = 'Executive unassigned successfully.';
                    $result  = ['status' => 1, 'msg' => $message];
                }
                return response()->json($result);
            } catch (\PDOException $e) {
                \DB::rollBack();

                $message = 'Database Error: unassign Executive, please try again later.';
                $result = ['status' => 0, 'msg' => $message];
                return response()->json($result);
            } catch (\Exception $e) {
                \DB::rollBack();

                $result = ['status' => 0, 'msg' => $e->getMessage()];
                return response()->json($result);
            }
        }
    }//end of function

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function listTil()
    {
        $authUser = \Auth::user();
        $userId   = $authUser->id;

        if(!$authUser->can('leads-management.view-til')) {
            abort(403);
        }

        if($userId == 13) {
          $message = 'Request not allowed.';
          return redirect()->route('leads-management.get-leads')->withError($message);
        }
        
        $hodId = $teamRoleId = null; $filter = $teamUsers = []; $qryFilter = [];

        $userDetails = $authUser->employeeProfile()->first();
        $bdMember = BdTeamMember::Where('user_id', $userId)->first();

        $bdUser = User::with('employee')->whereHas('employeeProfile', function (Builder $query) use($userDetails) {
            $departmentId = $userDetails->department_id; // B.D department id
            $query->where(['department_id' => $departmentId]);
        })->first();

        $teamHodId = $bdUser->leaveAuthorities()->where(['leave_authorities.priority' => '2'])->pluck('manager_id')->first();

        $existingUsers = BdTeamMember::where('isactive', 1)->pluck('team_role_id', 'user_id')->all();

        if(!empty($existingUsers) && isset($existingUsers[$userId]) && $existingUsers[$userId]) {
            $teamRoleId = $existingUsers[$userId]; // 1->executive, 2 -> manager

            if($teamRoleId == 1) {
                $filter['user_id'] = $userId;
            } else if($teamRoleId == 2) {
                $teamUsers = array_keys($existingUsers);
            }
        } else if(!empty($teamHodId) && $teamHodId == $userId) {
            $hodId = $teamHodId;
            $teamUsers = array_keys($existingUsers);
        }
        /*1=> New, 2=> Open, 3=> Complete, 4=> Sent for Remarks, 5=> Sent for Approval,6=> Rejected by Hod,7 => Abandoned,8 => Closed*/
        $tilDraftList = TilDraft::with(['tilSpecialEligibility', 'tilObligation', 'tilContact'])->where('isactive', 1);

        if(!empty($filter) && isset($filter['user_id'])) {
            /*$leadList->orWhere(['user_id' => $userId] + $filter);*/
            $tilDraftList->where('user_id', '=', $userId);
        } else if(!empty($teamUsers)) {
            $tilDraftList->whereIn('user_id', $teamUsers);
        } else {
            if($userId > 1) {
                dd('condition failed. unknown user.'); // cons
            }                
        }

        $tilDraftList->whereIn('status', [1, 2, 3, 4, 5, 6]);// 7, 8;
        $tilDraftList = $tilDraftList->orderBy('id', 'desc')->get();

        $tilDraftList = $tilDraftList->all();

        $tilCount     = TilDraft::count() + 1;
        $serialNumber = 'T' . generateSerialNumber($tilCount);

        return view('leads_management.til.index', compact('tilDraftList', 'bdMember', 'userId', 'teamRoleId', 'hodId'));
    }//end of function

    /**
     * Display a listing of the resource to admin.
     *
     * @return \Illuminate\Http\Response
    */
    public function getListTil(Request $request)
    {
        $user   = \Auth::user();
        $userId = $user->id;

        if(!$user->can('leads-management.view-til')) {
            abort(403);
        }

        if(!in_array($userId, [1, 13])) {
            $message = 'Request not allowed.';
            return redirect()->back()->withError($message);
        }

        $filter = ['isactive' => 1];
        /*1=> New, 2=> Open, 3=> Complete, 4=> Sent for Remarks, 5=> Sent for Approval,6=> Rejected by Hod,7 => Abandoned,8 => Closed*/
        $listTil = TilDraft::with(['tilSpecialEligibility', 'tilObligation', 'tilContact'])->where($filter)->whereIn('status', [1, 2, 3, 4, 5, 6, 8]); //7, 8
        $listTil = $listTil->orderBy('id', 'desc')->get();
        $listTil = $listTil->all();
        
        return view('leads_management.til.get_list_til', compact('listTil'));
    }//end of function

    /**
     * Display a listing of the resource to admin.
     *
     * @return \Illuminate\Http\Response
    */
    public function listClosedTils(Request $request)
    {
        $user   = \Auth::user();
        $userId = $user->id;
        
        if(!$user->can('leads-management.list-closed-tils')) {
            abort(403);
        }

        if(in_array($userId, [1, 13])) {
            $message = 'Request not allowed.';
            return redirect()->route('leads-management.list-til')->withError($message);
        }

        $filter = ['isactive' => 1, 'status' => 8];
        /*1=> New, 2=> Open, 3=> Complete, 4=> Sent for Remarks, 5=> Sent for Approval,6=> Rejected by Hod,7 => Abandoned,8 => Closed*/
        $listTil = TilDraft::with(['tilSpecialEligibility', 'tilObligation', 'tilContact'])->where($filter);
        $listTil = $listTil->orderBy('id', 'desc')->get();
        $listTil = $listTil->all();
        
        return view('leads_management.til.list_closed_til', compact('listTil'));
    }//end of function

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function tilRemarksList()
    {
        $authUser = \Auth::user();
        $userId   = $authUser->id;

        if(!$authUser->can('leads-management.til-remarks-list')) {
            abort(403);
        }
        /*1=> New, 2=> Open, 3=> Complete, 4=> Sent for Remarks, 5=> Sent for Approval,6=> Rejected by Hod,7 => Abandoned,8 => Closed*/
        $tilDraftList = TilDraft::with(['tilSpecialEligibility', 'tilObligation', 'tilContact'])
                        ->whereHas('tilDraftInputs', function (Builder $query) use($userId){
                            $query->where('user_id', '=', $userId);
                        })->with('tilDraftInputs')->where('isactive', 1)->whereIn('status', [3, 4, 5, 6]);

        $tilDraftList = $tilDraftList->orderBy('id', 'desc')->get();
        $tilDraftList = $tilDraftList->all();

        return view('leads_management.til.til_remarks_list', compact('tilDraftList', 'userId'));
    }//end of function

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function createTil(Request $request, $id = null)
    {
        if(!$id) {
            // $message = 'Invalid lead id provided';
            $message = 'Unable to create til, lead id not found';
            return redirect()->route('leads-management.approve-lead')->withError($message);
        }

        $lead = Lead::where(['is_completed'=> 1, 'status'=> 5])->find($id);
        if(!$lead) {
            $message = 'Invalid lead id provided';
            return redirect()->route('leads-management.approve-lead')->withError($message);
        }

        if(!empty($lead->tilDraft)) {
            $message = 'Invalid request, til already created.';
            return redirect()->route('leads-management.list-til')->withError($message);
        }

        $authUser = \Auth::user();
        $authUser = $authUser->employee()->first();
        $userId   = $authUser['id'];

        $emdOptions = (new FeeType)->getListFeeTypes(['is_emd' => 1, 'isactive' => 1], true);
        $processingFeeOptions = (new FeeType)->getListFeeTypes(['is_processing_fee' => 1, 'isactive' => 1], true);
        $tenderFeeOptions = (new FeeType)->getListFeeTypes(['is_tender_fee' => 1, 'isactive' => 1], true);

        $obligationOptions = (new Obligation)->getListObligationTypes(['isactive' => 1]);
        $verticalOptions   = (new Vertical)->getListVerticalTypes(['isactive' => 1]);

        $payAndCollectOptions = (new PaymentTerm)->getListPaymentTerm(['payment_type_id' => 1, 'isactive' => 1]);
        $collectAndPayOptions = (new PaymentTerm)->getListPaymentTerm(['payment_type_id' => 2, 'isactive' => 1]);

        return view('leads_management.til.create', compact('authUser', 'lead', 'emdOptions', 'processingFeeOptions', 'tenderFeeOptions', 'obligationOptions', 'verticalOptions', 'payAndCollectOptions', 'collectAndPayOptions'));
    }//end of function

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function saveTil(Request $request, $leadId = null)
    {
        if(!$leadId) {
            // $message = 'Invalid lead id provided';
            $message = 'Unable to create til, lead id not found';
            return redirect()->route('leads-management.approve-lead')->withError($message);
        }

        $authUser = \Auth::user();
        $lead     = Lead::find($leadId);
        if(!$lead) {
            $message = 'Invalid lead id provided';
            return redirect()->route('leads-management.approve-lead')->withError($message);
        }
        
        $tilCount     = TilDraft::count() + 1;
        $serialNumber = 'T' . generateSerialNumber($tilCount);

        if($request->isMethod('post')) {

            $inputs = $request->except('_token', 'special_eligibility_clause', 'obligation_text', 'til_contact');

            $specialEligibilityInputs = $request->only('special_eligibility_clause');
            $obligationTextInputs     = $request->only('obligation_text');
            $tilContactInputs         = $request->only('til_contact');

            $inputs['lead_id'] = $leadId;
            if(array_key_exists('vertical', $inputs)) {
                $inputs['vertical_id'] = $inputs['vertical'];
            }
            if(!empty($inputs['emd_date'])) {
                $inputs['emd_date'] = date('Y-m-d', strtotime($inputs['emd_date']));
            }
            if(!empty($inputs['due_date'])) {
                $inputs['due_date'] = date('Y-m-d H:i:s', strtotime($inputs['due_date']));
            }
            if(!empty($inputs['pre_bid_meeting'])) {
                $inputs['pre_bid_meeting'] = date('Y-m-d', strtotime($inputs['pre_bid_meeting']));
            }
            if(!empty($inputs['technical_opening_date'])) {
                $inputs['technical_opening_date'] = date('Y-m-d H:i:s', strtotime($inputs['technical_opening_date']));
            }
            if(!empty($inputs['financial_opening_date'])) {
                $inputs['financial_opening_date'] = date('Y-m-d H:i:s', strtotime($inputs['financial_opening_date']));
            }            
            if(isset($inputs['emd']) && !empty($inputs['emd'])) {
                $inputs['emd'] = implode(',', $inputs['emd']);
            }
            if(isset($inputs['processing_fee']) && !empty($inputs['processing_fee'])) {
                $inputs['processing_fee'] = implode(',', $inputs['processing_fee']);
            }
            if(isset($inputs['tender_fee']) && !empty($inputs['tender_fee'])) {
                $inputs['tender_fee'] = implode(',', $inputs['tender_fee']);
            }
            if(empty($inputs['tender_fee_amount'])) {
                $inputs['tender_fee_amount'] = 0;
            }

            $validator = (new TilDraft)->validateTil($inputs);
            if ($validator->fails()) {
               return redirect()->back()->withErrors($validator->messages())->withInput($inputs);
            }

            try {
                \DB::beginTransaction();

                $inputs['user_id']  = $authUser['id'];
                $inputs['til_code'] = $serialNumber;
                $inputs['isactive'] = 1;
                $inputs['status']   = 1;

                if(isset($inputs['obligations']) && !empty($inputs['obligations'])) {
                    $inputs['obligation_id'] = $inputs['obligations'];
                }

                $tilDraft = TilDraft::create($inputs);
                if(!empty($tilDraft)) {

                    if(!empty($specialEligibilityInputs) && isset($specialEligibilityInputs['special_eligibility_clause'])) {
                        $specialEligibilities = $specialEligibilityInputs['special_eligibility_clause']; //special_eligibility_clause
                        foreach ($specialEligibilities as $key => $specialEligibility) {
                            if(!empty($specialEligibility)) {
                                $objTilSpecialEligibility = new TilDraftSpecialEligibility;
                                $objTilSpecialEligibility->special_eligibility = $specialEligibility;

                                $tilSpecialEligibility = $tilDraft->tilSpecialEligibility()->save($objTilSpecialEligibility);
                            }
                        }
                    }

                    if(!empty($obligationTextInputs) && isset($obligationTextInputs['obligation_text'])) {
                        $obligations = $obligationTextInputs['obligation_text']; //obligation_text

                        foreach ($obligations as $key => $obligation) {
                            if(!empty($obligation)) {
                                $objTilObligations = new TilDraftObligation;
                                $objTilObligations->obligation = $obligation;
                                $tilObligation = $tilDraft->tilObligation()->save($objTilObligations);
                            }
                        }
                    }
                    
                    if(!empty($tilContactInputs) && isset($tilContactInputs['til_contact']['name'])) {
                        
                        $tilContacts    = $tilContactInputs['til_contact'];
                        $tilContactName = $tilContactInputs['til_contact']['name'];
                        foreach ($tilContactName as $key => $contact) {
                            
                            if(!empty($contact)) {
                                $objTilContact = new TilDraftContact;
                                $objTilContact->name = $contact;

                                if(isset($tilContacts['designation'][$key])) {
                                    $objTilContact->designation = $tilContacts['designation'][$key];
                                }

                                if(isset($tilContacts['phone'][$key])) {
                                    $objTilContact->phone = $tilContacts['phone'][$key];
                                }

                                if(isset($tilContacts['email'][$key])) {
                                    $objTilContact->email = $tilContacts['email'][$key];
                                }

                                $tilContact = $tilDraft->tilContact()->save($objTilContact);
                            }
                        }
                    }

                    $assignedInputs = new AssignedUsers;
                    $assignedInputs->user_id   = $inputs['user_id'];
                    $assignedInputs->type      = 1;
                    $assignedInputs->wef       = date('Y-m-d H:i:s');
                    $assignedInputs->is_active = 1;
                    $assignedUser = $tilDraft->assignedUsers()->save($assignedInputs);

                } else {
                    throw new \Exception("Error occurs please try again.", 151);
                }

                \DB::commit();
                $message = 'Til saved as a draft Successfully.';
                return redirect()->route('leads-management.list-til')->withSuccess($message);
            } catch (\PDOException $e) {
                \DB::rollBack();

                // $message = 'Database Error: The til could not be saved.';
                $message = $e->getMessage();
                return redirect()->back()->withErrors($message)->withInput($inputs);
            } catch (\Exception $e) {
                \DB::rollBack();

                // $message = 'Error code 500: internal server error.';
                $message = $e->getMessage();
                return redirect()->back()->withErrors($message)->withInput($inputs);
            }
        }
    }//end of function

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function viewTil($id = null)
    {
        $authUser = $user = \Auth::user();
        $userId = $user->id;

        if(!$authUser->can('leads-management.view-til')) {
            abort(403);
        }

        if(!$id) {
            $message = 'Invalid id provided';
            return redirect()->back()->withError($message);
        }

        if($userId == 13) {
          $message = 'Request not allowed.';
          return redirect()->route('leads-management.get-list-til')->withError($message);
        }

        $teamRoleId = null; $filter = "id = ". $id; $hodId  = null;

        $userDetails   = $user->employeeProfile()->first();
        $memberDetails = $bdMember = BdTeamMember::Where('user_id', $userId)->first();//1->executive, 2->manager

        $bdUser = User::with('employee')->whereHas('employeeProfile', function (Builder $query) use($userDetails) {
            $departmentId = $userDetails->department_id; // B.D department id
            $query->where(['department_id' => $departmentId]);
        })->first();

        $teamHodId = $bdUser->leaveAuthorities()->where(['leave_authorities.priority' => '2'])->pluck('manager_id')->first();
        if(!empty($teamHodId)) {
            $hodId = $teamHodId;
        }

        $existingUsers = BdTeamMember::where('isactive', 1)->pluck('team_role_id', 'user_id')->all();
        if(!empty($existingUsers) && isset($existingUsers[$userId]) && $existingUsers[$userId]) {
            $teamRoleId = $existingUsers[$userId]; // 1->executive, 2 -> manager

            if($teamRoleId == 1) {
                $filter .= " AND (user_id = ". $userId .")";
            } else if($teamRoleId == 2) {
                $teamUsers = array_keys($existingUsers);
                $filter .= " AND (user_id IN(". implode(',', $teamUsers) ."))";
            }
        } else if(!empty($teamHodId) && $teamHodId == $userId) {
            $hodId = $teamHodId;
            $teamUsers = array_keys($existingUsers);
            $filter .= " AND (user_id IN(". implode(',', $teamUsers) ."))";
        }
        /*1=> New, 2=> Open, 3=> Complete, 4=> Sent for Remarks, 5=> Sent for Approval,6=> Rejected by Hod,7 => Abandoned,8 => Closed*/
        $filter .= ' AND status IN (1,2,3,4,5,6,8)'; //7
        
        $tilDraft = TilDraft::with(['tilSpecialEligibility', 'tilObligation', 'tilContact', 'tilDraftInputs'])->whereRaw($filter)->first();
        if(!$tilDraft) {
            $message = 'Invalid Request, no data found.';
            return redirect()->back()->withError($message);
        }

        $emdOptions = (new FeeType)->getListFeeTypes(['is_emd' => 1, 'isactive' => 1]);
        $processingFeeOptions = (new FeeType)->getListFeeTypes(['is_processing_fee' => 1, 'isactive' => 1]);
        $tenderFeeOptions = (new FeeType)->getListFeeTypes(['is_tender_fee' => 1, 'isactive' => 1]);
        $obligationOptions = (new Obligation)->getListObligationTypes(['isactive' => 1]);
        $verticalOptions = (new Vertical)->getListVerticalTypes(['isactive' => 1]);
        
        $payAndCollectOptions = (new PaymentTerm)->getListPaymentTerm(['payment_type_id' => 1, 'isactive' => 1]);
        $collectAndPayOptions = (new PaymentTerm)->getListPaymentTerm(['payment_type_id' => 2, 'isactive' => 1]);
        $departments = Department::where(['isactive' => 1])->get();

        return view('leads_management.til.view', compact('tilDraft', 'authUser', 'userId', 'memberDetails', 'hodId', 'emdOptions', 'processingFeeOptions', 'tenderFeeOptions', 'obligationOptions', 'verticalOptions', 'payAndCollectOptions', 'collectAndPayOptions', 'departments', 'employees'));
    }//end of function

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function viewTilRemarks($id = null)
    {
        $authUser = \Auth::user();
        $userId   = $authUser->id;

        if(!$id) {
            $message = 'Invalid id provided';
            return redirect()->back()->withError($message);
        }

        if(!$authUser->can('leads-management.view-til-remarks')) {
            abort(403);
        }
        /*1=> New, 2=> Open, 3=> Complete, 4=> Sent for Remarks, 5=> Sent for Approval,6=> Rejected by Hod,7 => Abandoned,8 => Closed*/
        $tilDraft = TilDraft::with(['tilSpecialEligibility', 'tilObligation', 'tilContact'])
                    ->whereHas('tilDraftInputs', function (Builder $query) use($userId){
                        $query->where('user_id', '=', $userId);
                    })->where('isactive', 1)->whereIn('status', [3, 4, 5, 6])
                    ->where('id', $id)->first();

        if(!$tilDraft) {
            $message = 'Invalid Request, no data found.';
            return redirect()->back()->withError($message);
        }

        $tilDraftInputs = TilDraftInputs::where(['til_draft_id' => $tilDraft->id, 'user_id' => $userId])->get();

        $emdOptions           = (new FeeType)->getListFeeTypes(['is_emd' => 1, 'isactive' => 1]);
        $processingFeeOptions = (new FeeType)->getListFeeTypes(['is_processing_fee' => 1, 'isactive' => 1]);
        $tenderFeeOptions     = (new FeeType)->getListFeeTypes(['is_tender_fee' => 1, 'isactive' => 1]);
        $obligationOptions    = (new Obligation)->getListObligationTypes(['isactive' => 1]);
        $verticalOptions      = (new Vertical)->getListVerticalTypes(['isactive' => 1]);        
        $payAndCollectOptions = (new PaymentTerm)->getListPaymentTerm(['payment_type_id' => 1, 'isactive' => 1]);
        $collectAndPayOptions = (new PaymentTerm)->getListPaymentTerm(['payment_type_id' => 2, 'isactive' => 1]);
        $departments          = Department::where(['isactive' => 1])->get();

        return view('leads_management.til.view_til_remarks', compact('tilDraft', 'tilDraftInputs', 'authUser', 'userId', 'emdOptions', 'processingFeeOptions', 'tenderFeeOptions', 'obligationOptions', 'verticalOptions', 'payAndCollectOptions', 'collectAndPayOptions', 'departments'));
    }//end of function

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function showTil($id = null)
    {
        if(!$id) {
            $message = 'Invalid id provided';
            return redirect()->back()->withError($message);
        }

        $userId = \Auth::user()->id;

        if($userId != 13) {
          $message = 'Request not allowed.';
          return redirect()->back()->withError($message);
        }
        /*1=> New, 2=> Open, 3=> Complete, 4=> Sent for Remarks, 5=> Sent for Approval,6=> Rejected by Hod,7 => Abandoned,8 => Closed*/
        $tilDraft = TilDraft::with(['tilSpecialEligibility', 'tilObligation', 'tilContact'])->where('til_drafts.status', '<>', 7)->find($id);

        if(!$tilDraft) {
            $message = 'Invalid Request, no data found.';
            return redirect()->back()->withError($message);
        }

        $emdOptions = (new FeeType)->getListFeeTypes(['is_emd' => 1, 'isactive' => 1]);
        $processingFeeOptions = (new FeeType)->getListFeeTypes(['is_processing_fee' => 1, 'isactive' => 1]);
        $tenderFeeOptions = (new FeeType)->getListFeeTypes(['is_tender_fee' => 1, 'isactive' => 1]);
        $obligationOptions = (new Obligation)->getListObligationTypes(['isactive' => 1]);
        $verticalOptions = (new Vertical)->getListVerticalTypes(['isactive' => 1]);
        
        $payAndCollectOptions = (new PaymentTerm)->getListPaymentTerm(['payment_type_id' => 1, 'isactive' => 1]);
        $collectAndPayOptions = (new PaymentTerm)->getListPaymentTerm(['payment_type_id' => 2, 'isactive' => 1]);

        return view('leads_management.til.show_til', compact('tilDraft', 'emdOptions', 'processingFeeOptions', 'tenderFeeOptions', 'obligationOptions', 'verticalOptions', 'payAndCollectOptions', 'collectAndPayOptions'));
    }//end of function

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function editTil(Request $request, $id = null)
    {
        if(!$id) {
            // $message = 'Invalid lead id provided';
            $message = 'Unable to find til, id not found';
            return redirect()->route('leads-management.list-til')->withError($message);
        }
        
        $authUser = \Auth::user();
        $userId   = $authUser['id'];

        $tilDraft = TilDraft::with(['tilSpecialEligibility','tilObligation','tilContact'])
        ->where(['user_id' => $userId])->find($id);

        if(!$tilDraft) {
            $message = 'Invalid Request, no data found.';
            return redirect()->back()->withError($message);
        }
        /*1=> New, 2=> Open, 3=> Complete, 4=> Sent for Remarks, 5=> Sent for Approval,6=> Rejected by Hod,7 => Abandoned,8 => Closed*/
        if(in_array($tilDraft->status, [3, 4, 5, 6, 7, 8])) {
            $message = 'You don\'t have permission to edit this.';
            return redirect()->back()->withError($message);
        }

        $authUser = $authUser->employee()->first();

        $emdOptions = (new FeeType)->getListFeeTypes(['is_emd' => 1, 'isactive' => 1], true);
        $processingFeeOptions = (new FeeType)->getListFeeTypes(['is_processing_fee' => 1, 'isactive' => 1], true);
        $tenderFeeOptions = (new FeeType)->getListFeeTypes(['is_tender_fee' => 1, 'isactive' => 1], true);
        $obligationOptions = (new Obligation)->getListObligationTypes(['isactive' => 1]);
        $verticalOptions   = (new Vertical)->getListVerticalTypes(['isactive' => 1]);
        $payAndCollectOptions = (new PaymentTerm)->getListPaymentTerm(['payment_type_id' => 1, 'isactive' => 1]);
        $collectAndPayOptions = (new PaymentTerm)->getListPaymentTerm(['payment_type_id' => 2, 'isactive' => 1]);

        return view('leads_management.til.edit', compact('authUser', 'tilDraft', 'emdOptions', 'processingFeeOptions', 'tenderFeeOptions', 'obligationOptions', 'verticalOptions', 'payAndCollectOptions', 'collectAndPayOptions'));
    }//end of function

    /**
     * updateTil
     * update a resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function updateTil(Request $request, $tilId = null)
    {
        if(!$tilId) {
            $message = 'Unable to find til, id not found';
            return redirect()->route('leads-management.list-til')->withError($message);
        }

        $tilDraft = TilDraft::find($tilId);
        if(!$tilDraft) {
            $message = 'Invalid til id provided';
            return redirect()->route('leads-management.list-til')->withError($message);
        }

        $authUser = \Auth::user();
        $userId   = $authUser->id;

        if($request->isMethod('post')) {

            $inputs = $request->except('_token', 'special_eligibility_clause', 'obligation_text', 'til_contact');

            $specialEligibilityInputs = $request->only('special_eligibility_clause');
            $obligationTextInputs     = $request->only('obligation_text');
            $tilContactInputs         = $request->only('til_contact');

            if(array_key_exists('vertical', $inputs)) {
                $inputs['vertical_id'] = $inputs['vertical'];
            }
            if(!empty($inputs['emd_date'])) {
               $inputs['emd_date'] = date('Y-m-d', strtotime($inputs['emd_date']));
            }
            if(!empty($inputs['due_date'])) {
               $inputs['due_date'] = date('Y-m-d H:i:s', strtotime($inputs['due_date']));
            }
            if(!empty($inputs['pre_bid_meeting'])) {
               $inputs['pre_bid_meeting'] = date('Y-m-d H:i:s', strtotime($inputs['pre_bid_meeting']));
            }
            if(!empty($inputs['technical_opening_date'])) {
               $inputs['technical_opening_date'] = date('Y-m-d H:i:s', strtotime($inputs['technical_opening_date']));
            }
            if(!empty($inputs['financial_opening_date'])) {
               $inputs['financial_opening_date'] = date('Y-m-d H:i:s', strtotime($inputs['financial_opening_date']));
            }
            if(isset($inputs['collect_and_pay']) && !empty($inputs['collect_and_pay'])) {
                $inputs['pay_and_collect'] = $inputs['complete_clause'] = null;
            }
            if(isset($inputs['emd']) && !empty($inputs['emd'])) {
                $inputs['emd'] = implode(',', $inputs['emd']);
            }
            if(isset($inputs['processing_fee']) && !empty($inputs['processing_fee'])) {
                $inputs['processing_fee'] = implode(',', $inputs['processing_fee']);
            }
            if(isset($inputs['tender_fee']) && !empty($inputs['tender_fee'])) {
                $inputs['tender_fee'] = implode(',', $inputs['tender_fee']);
            }
            if(empty($inputs['tender_fee_amount'])) {
                $inputs['tender_fee_amount'] = 0;
            }

            $validator = (new TilDraft)->validateTil($inputs, $tilId);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->messages())->withInput($inputs);
            }
            
            try {
                \DB::beginTransaction();
                // $inputs['user_id']  = $authUser['id'];
                $inputs['isactive'] = 1;

                if(isset($inputs['obligations']) && !empty($inputs['obligations'])) {
                    $inputs['obligation_id'] = $inputs['obligations'];
                } else {
                    $inputs['obligation_id'] = null;
                    $tilObligationIds = $tilDraft->tilObligation()->pluck('id')->all();

                    if(!empty($tilObligationIds)) {
                        $obData = TilDraftObligation::whereIn('id', $tilObligationIds)->delete();

                        if(isset($obligationTextInputs['obligation_text'])) {
                            unset($obligationTextInputs['obligation_text']);
                        }
                    }
                }

                $leadHodId = $tilDraft->lead->hod_id;
                $inputs['status']   = 2;
                if(isset($inputs['submit_type']) && $inputs['submit_type'] == 'save') {
                  $inputs['status'] = 3;
                  $inputs['is_editable'] = 0;
                }
                
                $tilDraft->update($inputs);

                if(!empty($tilDraft)) {

                    if(isset($inputs['submit_type']) && $inputs['submit_type'] == 'save') {

                        if(empty($tilDraft->costEstimationDraft)) {
                            $message = "Cost Estimation Data is missing, Please fill the details and submit before saving til.";

                            throw new \Exception($message, 151);
                        } else {
                            $tilDraft->costEstimationDraft->update(['is_complete' => 1, 'is_editable' => 0]);
                        }

                        $teamHodId = $authUser->leaveAuthorities()->where(['leave_authorities.priority' => '2'])->pluck('manager_id')->first();

                        $notificationMessage = $authUser->employee->fullname." has marked this til as completed.";

                        $notificationData = [
                            'sender_id' => $tilDraft->user_id,
                            'receiver_id' => $teamHodId,
                            'label' => 'Til Completed',
                            'read_status' => '0',
                            'redirect_url' => 'leads-management/view-til/'.$tilDraft->id,
                            'message' => $notificationMessage
                        ];
                        $tilDraft->notifications()->create($notificationData);
                    }

                    if(!empty($specialEligibilityInputs) && isset($specialEligibilityInputs['special_eligibility_clause'])) {
                        $specialEligibilities = $specialEligibilityInputs['special_eligibility_clause']; //special_eligibility_clause
                        foreach ($specialEligibilities['name'] as $key => $specialEligibility) {
                            if(!empty($specialEligibility)) {
                                if(isset($specialEligibilities['id'][$key])) {
                                    $eligibilityId =  $specialEligibilities['id'][$key];
                                    $objTilSpecialEligibility = TilDraftSpecialEligibility::find($eligibilityId);
                                } else {
                                    $objTilSpecialEligibility = new TilDraftSpecialEligibility;
                                }

                                $objTilSpecialEligibility->special_eligibility = $specialEligibility;

                                $tilSpecialEligibility = $tilDraft->tilSpecialEligibility()->save($objTilSpecialEligibility);
                            }
                        }
                    }
                    
                    if(!empty($obligationTextInputs) && isset($obligationTextInputs['obligation_text'])) {
                        $obligations = $obligationTextInputs['obligation_text']; //obligation_text

                        foreach ($obligations['name'] as $key => $obligation) {

                            if(!empty($obligation)) {

                                if(isset($obligations['id'][$key])) {
                                    $obligationId =  $obligations['id'][$key];
                                    $objTilObligations = TilDraftObligation::find($obligationId);
                                } else {
                                    $objTilObligations = new TilDraftObligation;
                                }

                                $objTilObligations->obligation = $obligation;
                                $tilObligation = $tilDraft->tilObligation()->save($objTilObligations);
                            }
                        }
                    }

                    if(!empty($tilContactInputs) && isset($tilContactInputs['til_contact']['name'])) {
                        
                        $tilContacts    = $tilContactInputs['til_contact'];
                        $tilContactName = $tilContacts['name'];
                        
                        foreach ($tilContactName as $key => $contact) {
                            if(!empty($contact)) {
                            
                                if(isset($tilContacts['id'][$key])) {
                                    $contactId  =  $tilContacts['id'][$key];
                                    $objTilContact = TilDraftContact::find($contactId);
                                } else {
                                    $objTilContact = new TilDraftContact;
                                }

                                $objTilContact->name = $contact;

                                if(isset($tilContacts['designation'][$key])) {
                                    $objTilContact->designation = $tilContacts['designation'][$key];
                                }

                                if(isset($tilContacts['phone'][$key])) {
                                    $objTilContact->phone = $tilContacts['phone'][$key];
                                }

                                if(isset($tilContacts['email'][$key])) {
                                    $objTilContact->email = $tilContacts['email'][$key];
                                }

                                $tilContact = $tilDraft->tilContact()->save($objTilContact);
                            }
                        }
                    }

                    if(isset($inputs['comments']) && !empty($inputs['comments'])) {
                        $commentsInputs = new Comments;
                        $commentsInputs->user_id  = $userId;
                        $commentsInputs->comments = $inputs['comments'];
                        $comments = $tilDraft->comments()->save($commentsInputs);
                    }
                } else {
                   throw new \Exception("Error occurs please try again.", 151);
                }
                \DB::commit();
                $message = 'Til updated Successfully.';
                return redirect()->route('leads-management.list-til')->withSuccess($message);
            } catch (\PDOException $e) {
                \DB::rollBack();

                /*$message = 'Database Error: The til could not be updated.';*/
                $message = $e->getMessage();
                return redirect()->back()->withErrors($message)->withInput($inputs);
            } catch (\Exception $e) {
                \DB::rollBack();

                /*$message = 'Error code 500: internal server error.';
                if($e->getCode() == 151) {
                    $message = $e->getMessage();
                }*/
                $message = $e->getMessage();
                return redirect()->back()->withErrors($message)->withInput($inputs);
            }
        }
    }

    public function getComments(Request $request)
    {
        if($request->isMethod('GET')) {
            $commentsDir = null;
            

            $inputs = $request->except('_token');
            
            if(isset($inputs['til_id']) && !empty($inputs['til_id'])) {
                $tilDraft   = TilDraft::find($inputs['til_id']);
                if(!$tilDraft) {
                    $message = 'TIL data not found.';
                    $result = ['status' => 0, 'msg' => $message, 'data' => null, 'dir_path' => null];
                    return response()->json($result);
                }
                $comments = $tilDraft->comments()->with('userEmployee')->get()->all();

            } else {
                $lead   = Lead::find($inputs['id']); //with('comments')->
                if(!$lead) {
                    $message = 'Lead data not found.';
                    $result = ['status' => 0, 'msg' => $message, 'data' => null, 'dir_path' => null];
                    return response()->json($result);
                }
                $comments = $lead->comments()->with('userEmployee')->get()->all();

                $commentsDir = asset('public') . \Config::get('constants.uploadPaths.leadCommentPath');
            }


            if(empty($comments)) {
                $message = 'No prevoius comments were found.';
                $result  = ['status' => 0, 'msg' => $message, 'data' => null, 'dir_path' => null];
            } else {
                $message = null;
                $result  = ['status' => 1, 'msg' => $message, 'data' => $comments, 'dir_path' => $commentsDir];
            }
            return response()->json($result);
        }
    }//end of function

    /**
     * Display a listing of the resource to admin.
     *
     * @return \Illuminate\Http\Response
    */
    public function unassignUserTil(Request $request)
    {
        if($request->isMethod('post')) {

            $userId = \Auth::user()->id;
            $inputs = $request->except('_token');

            try {
                \DB::beginTransaction();
                unset($inputs['_token']);

                $res  = false;

                $tilDraft  = TilDraft::find($inputs['til_id']);
                $tilUserId = $tilDraft->user_id;

                if(!empty($tilDraft)) {
                    $tilDraftInputs = ['user_id' => null, 'tender_owner' => null];
                    $res = $tilDraft->update($tilDraftInputs);

                    $assignedInputs = $tilDraft->assignedUsers()->where(['user_id' => $tilUserId])->whereNull('wet')->first();

                    $assignedInputs->wet = date('Y-m-d H:i');
                    $assignedUser = $assignedInputs->update();
                }

                \DB::commit();

                $message = 'Error in unassigning executive, please try again later.';
                $result  = ['status' => 0, 'msg' => $message];
                if($res) {
                    $message = 'Executive unassigned successfully.';
                    $result  = ['status' => 1, 'msg' => $message];
                }
                return response()->json($result);
            } catch (\PDOException $e) {
                \DB::rollBack();

                $message = 'Database Error: unassign Executive, please try again later.';
                $result = ['status' => 0, 'msg' => $message];
                return response()->json($result);
            } catch (\Exception $e) {
                \DB::rollBack();

                $result = ['status' => 0, 'msg' => $e->getMessage()];
                return response()->json($result);
            }
        }
    }//end of function

    /**
     * Display a listing of the resource to admin.
     *
     * @return \Illuminate\Http\Response
    */
    public function unassinedTils(Request $request)
    {
        $user   = \Auth::user();
        $userId = $user->id;
        $filter = ['isactive' => 1];
        
        $userDetails = $user->employeeProfile()->first();
        $bdMember = BdTeamMember::Where('user_id', $userId)->first();//1->executive, 2->manager

        $bdUser = User::with('employee')->whereHas('employeeProfile', function (Builder $query) use($userDetails) {
            $departmentId = $userDetails->department_id; // B.D department id
            $query->where(['department_id' => $departmentId]);
        })->first();

        $teamHodId = $bdUser->leaveAuthorities()->where(['leave_authorities.priority' => '2'])->pluck('manager_id')->first();

        if((!empty($bdMember) && $bdMember->team_role_id != 2) && ($userId != $teamHodId)) {
            $message = 'Request not allowed.';
            return redirect()->back()->withError($message);
        }

        $filter = ['user_id' => 0, 'isactive' => 1];

        $departmentId = $userDetails->department_id;
        $bdEmployees  = \DB::table('bd_team_members as tm')
                        ->join('employees as e', 'tm.user_id', '=', 'e.user_id')
                        ->join('employee_profiles as ep', 'e.user_id', '=', 'ep.user_id')
                        ->where(['ep.department_id' => $departmentId, 'e.approval_status' => '1', 'e.isactive' => 1, 'ep.isactive' => 1])
                        ->when($teamHodId, function ($query, $teamHodId) {
                            return $query->where('e.id', '!=', $teamHodId);
                        })->select('e.user_id', 'e.fullname')
                        ->get()->all();

        $tilDraftList = TilDraft::with(['tilSpecialEligibility', 'tilObligation', 'tilContact'])->where($filter)->orderBy('id', 'desc')->get();
        $tilDraftList = $tilDraftList->all();

        return view('leads_management.til.unassigned_tils', compact('tilDraftList', 'bdEmployees', 'bdMember'));
    }//end of function

    public function assignTil(Request $request)
    {
        $userId = \Auth::user()->id;

        if($request->isMethod('post')) {
            $inputs = $request->except('_token');
            
            try {
                \DB::beginTransaction();

                $tilIds   = explode(',', $inputs['til_ids']);
                $assignTo = $inputs['assign_to'];

                $user = User::find($assignTo);
                $userDetails = $user->employee;
                $userName = preg_replace('/\s+/', '', $userDetails->fullname);
                $tilInputs = [];
                
                foreach ($tilIds as $key => $id) {
                    $til = TilDraft::find($id);

                    $tilInputs = ['user_id' => $assignTo, 'tender_owner' => $userName];
                    $til->update($tilInputs);

                    $assignedUsers = $til->assignedUsers()->where(['user_id' => $assignTo])->whereNull('wet')->first();
                    
                    if(empty($assignedUsers)) {
                        $assignedInputs = new AssignedUsers;
                        $assignedInputs->user_id = $inputs['assign_to'];
                        $assignedInputs->type    = 1;
                        $assignedInputs->wef     = date('Y-m-d H:i');
                        $assignedInputs->is_active = 1;

                        $assignedUser = $til->assignedUsers()->save($assignedInputs);
                    }
                }

                \DB::commit();

                $route   = 'leads-management.list-til';
                $message = 'The user id assigned to til successfully.';

                return redirect()->route($route)->withSuccess($message);
            } catch (\PDOException $e) {
                \DB::rollBack();

                return redirect()->back()->withError('Database Error: The user id could not be assigned to til.')->withInput($inputs);
            } catch (\Exception $e) {
                \DB::rollBack();
                return redirect()->back()->withError('Error code 500: internal server error.')->withInput($inputs);
            }
        }
    }

    /**
     * Converting the numbers into words.
     *
     * @return \Illuminate\Http\Response
    */
    /* public function getNumberToWords(Request $request)
    {
        if($request->isMethod('post')) {

            $userId = \Auth::user()->id;
            $inputs = $request->except('_token');
            $number = 0;

            $result = ['status' => 0, 'msg' => 'Number is not numeric', 'value' => ''];
            if(!empty($inputs['number']) && is_numeric($inputs['number'])) {

                // $number = amountInWords($inputs['number']);
                $number = numberToWords($inputs['number']);
                $result = ['status' => 1, 'msg' => 'Number is numeric', 'value' => ucwords(trim($number))];
            }

            return response()->json($result);
        }
    }//end of function*/

    public function deleteContact(Request $request)
    {
        if($request->isMethod('post')) {
            
            $userId = \Auth::user()->id;
            $inputs = $request->except('_token');

            try {
                \DB::beginTransaction();

                $res  = false;
                $contact = TilDraftContact::find($inputs['contact_id']);
                
                if(!empty($contact)) {
                    $res = $contact->delete();
                }

                \DB::commit();

                $message = 'Error in removing contact, please try again later.';
                $result = ['status' => 0, 'msg' => $message];
                if($res) {
                    $message = 'Contact removed successfully.';
                    $result = ['status' => 1, 'msg' => $message];
                }
                return response()->json($result);
            } catch (\PDOException $e) {
                \DB::rollBack();

                $message = 'Database Error: Removing record, please try again later.';
                $result = ['status' => 0, 'msg' => $message];
                return response()->json($result);
            } catch (\Exception $e) {
                \DB::rollBack();

                $result = ['status' => 0, 'msg' => $e->getMessage()];
                return response()->json($result);
            }
        }
    }//end of function

    public function deleteEligibility(Request $request)
    {
        if($request->isMethod('post')) {
            
            $userId = \Auth::user()->id;
            $inputs = $request->except('_token');

            try {
                \DB::beginTransaction();

                $res  = false;
                $eligibility = TilDraftSpecialEligibility::find($inputs['eligibility_id']);

                if(!empty($eligibility)) {
                    $res = $eligibility->delete();
                }

                \DB::commit();

                $message = 'Error in removing special eligibility, please try again later.';
                $result = ['status' => 0, 'msg' => $message];
                if($res) {
                    $message = 'Special eligibility removed successfully.';
                    $result = ['status' => 1, 'msg' => $message];
                }
                return response()->json($result);
            } catch (\PDOException $e) {
                \DB::rollBack();

                $message = 'Database Error: Removing record, please try again later.';
                $result = ['status' => 0, 'msg' => $message];
                return response()->json($result);
            } catch (\Exception $e) {
                \DB::rollBack();

                $result = ['status' => 0, 'msg' => $e->getMessage()];
                return response()->json($result);
            }
        }
    }//end of function

    public function deleteObligation(Request $request)
    {
        if($request->isMethod('post')) {
            
            $userId = \Auth::user()->id;
            $inputs = $request->except('_token');

            try {
                \DB::beginTransaction();

                $res  = false;
                $obligation = TilDraftObligation::find($inputs['obligation_id']);

                if(!empty($obligation)) {
                    $res = $obligation->delete();
                }

                \DB::commit();

                $message = 'Error in removing obligation, please try again later.';
                $result = ['status' => 0, 'msg' => $message];
                if($res) {
                    $message = 'Obligation removed successfully.';
                    $result = ['status' => 1, 'msg' => $message];
                }
                return response()->json($result);
            } catch (\PDOException $e) {
                \DB::rollBack();

                $message = 'Database Error: Removing record, please try again later.';
                $result = ['status' => 0, 'msg' => $message];
                return response()->json($result);
            } catch (\Exception $e) {
                \DB::rollBack();

                $result = ['status' => 0, 'msg' => $e->getMessage()];
                return response()->json($result);
            }
        }
    }//end of function

    public function costEstimation(Request $request, $tilDraftId = null)
    {
        $inputs = []; $noOfDesignations = 1;

        $authUser = \Auth::user();
        $userId   = $authUser->id;

        if(!$authUser->can('leads-management.view-cost-estimation')) {
            abort(403);
        }
        
        $tilDraft = TilDraft::with('costEstimationDraft')->find($tilDraftId);
        if(!$tilDraft) {
            $message = 'Invalid Request, no data found.';
            return redirect()->back()->withError($message);
        }

        if($request->isMethod('GET')) {
            $inputs = $request->all();

            try {
                $noOfDesignations = decrypt($inputs['key']) ?? $noOfDesignations;
            } catch (DecryptException $e) {
                abort(404, 'Error: in the key, try again.');
            }
        }

        $costFactorOptions     = (new CostFactorMaster)->getListCostFactors(false);
        $costFactorTypeOptions = (new CostFactorTypes)->getListCostFactorsTypes();

        if(!empty($tilDraft->costEstimationDraft)) {
            return view('leads_management.til.edit_cost_estimation_sheet', compact('tilDraft','inputs', 'noOfDesignations', 'costFactorOptions', 'costFactorTypeOptions'));

        } else {
            return view('leads_management.til.cost_estimation_sheet', compact('tilDraft','inputs', 'noOfDesignations', 'costFactorOptions', 'costFactorTypeOptions'));
        }
    }//end of function

    public function viewCostEstimation(Request $request, $tilDraftId = null)
    {
        $authUser = \Auth::user();
        $userId   = $authUser->id;

        if(!$authUser->can('leads-management.view-cost-estimation')) {
            abort(403);
        }
        
        $tilDraft = TilDraft::with('costEstimationDraft')->find($tilDraftId);
        if(!$tilDraft) {
            $message = 'Invalid Request, no data found.';
            return redirect()->back()->withError($message);
        }

        $costFactorOptions     = (new CostFactorMaster)->getListCostFactors(false);
        $costFactorTypeOptions = (new CostFactorTypes)->getListCostFactorsTypes();

        return view('leads_management.til.view_cost_estimation_sheet', compact('tilDraft', 'costFactorOptions', 'costFactorTypeOptions'));
    }//end of function

    public function saveCostEstimation(Request $request)
    {
        if($request->isMethod('POST')) {
            $inputs     = $request->except('_token');

            $tilDraftId = $inputs['til_draft_id'];
            $tilDraft   = TilDraft::with('costEstimationDraft')->find($tilDraftId);

            $costEstimationFile = $costFactorDetails = [];
            if(!empty($tilDraft->costEstimationDraft)) {

                if($tilDraft->costEstimationDraft->is_complete == 1) {
                    return redirect()->back()->withError('You are not authorize for this action, after saving it once.')->withInput($inputs);
                }

                $costEstimationData = json_decode($tilDraft->costEstimationDraft->estimation_data);
                $costFactorDetails  = $costEstimationData->cost_factor_details;
                $costEstimationFile = (array) @$costEstimationData->cost_factor_details->cost_factor_file;
            }
            
            $costInputs = $request->only('project_scope', 'cost_estimation', 'cost_factors', 'cost_factor_details', 'revenue', 'total_capital_expense', 'total_operational_expense', 'total_expense');

            try {
                \DB::beginTransaction();

                $fileNameArr  = [];

                if(isset($costInputs['cost_factor_details']['option_id'])) {
                    $costFactorDetail = $costInputs['cost_factor_details'];
                           $optionIds = ($costFactorDetail['option_id']);
                    
                    foreach ($optionIds as $opKey => $val) 
                    {
                        if(isset($costFactorDetail['cost_factor_file'][$opKey]) && !empty($costFactorDetail['cost_factor_file'][$opKey]) && !empty($val)) {

                            $file = $costFactorDetail['cost_factor_file'][$opKey];
                            
                            $fileOzName      = str_replace(' ', '', $file->getClientOriginalName());
                            $fileOzExtension = $file->getClientOriginalExtension();
                            $fileName        = time().'_'.pathinfo(strtolower($fileOzName), PATHINFO_FILENAME).'.'.$fileOzExtension;

                            $tilDocumentDir  = \Config::get('constants.uploadPaths.tilDocument');
                            if(!is_dir($tilDocumentDir)) {
                                mkdir($tilDocumentDir, 0775);
                            }

                            if(isset($costEstimationFile[$opKey]) && !empty($costEstimationFile[$opKey]) && file_exists($tilDocumentDir . $costEstimationFile[$opKey])) {
                                unlink($tilDocumentDir . $costEstimationFile[$opKey]);
                            }
                            
                            $file->move($tilDocumentDir, $fileName);
                            $fileNameArr['file'][] = $tilDocumentDir . $fileName;
                            $costInputs['cost_factor_details']['cost_factor_file'][$opKey] = $fileName;
                            $costInputs['cost_factor_details']['cost_factor_file_path'][$opKey] = $tilDocumentDir;
                        }
                    }

                    if(!empty($tilDraft->costEstimationDraft)) {
                        if(!empty($costFactorDetails->cost_factor_file)) {
                            $fileArr = (array) $costFactorDetails->cost_factor_file;
                            $pathArr = (array) $costFactorDetails->cost_factor_file_path;

                            if(!isset($costInputs['cost_factor_details']['cost_factor_file'])) {
                                $costInputs['cost_factor_details']['cost_factor_file'] = [];
                            }
                            if(!isset($costInputs['cost_factor_details']['cost_factor_file_path'])) {
                                $costInputs['cost_factor_details']['cost_factor_file_path'] = [];
                            }

                            $oldFilesArr = array_filter($fileArr);
                            $newFilesArr = $costInputs['cost_factor_details']['cost_factor_file'];
                            $filesArr    = $oldFilesArr + $newFilesArr;

                            $oldFilesPathArr = array_filter($pathArr);
                            $newFilesPathArr = $costInputs['cost_factor_details']['cost_factor_file_path'];
                            $filesPathArr    = $oldFilesPathArr + $newFilesPathArr;

                            ksort($filesArr);
                            ksort($filesPathArr);
                            
                            $costInputs['cost_factor_details']['cost_factor_file'] = $filesArr;
                            $costInputs['cost_factor_details']['cost_factor_file_path'] = $filesPathArr;
                        }
                    }
                }
                
                $isComplete = /*(!empty($inputs['is_complete'])) ? $inputs['is_complete'] :*/ 0;
                $costEstimationInputs = [
                    'til_draft_id'    => $inputs['til_draft_id'],
                    'estimation_data' => json_encode($costInputs),
                    'is_complete'     => $isComplete,
                    'isactive'        => 1,
                ];

                $findArr = ['til_draft_id' => $inputs['til_draft_id'], 'isactive' => 1];
                $costestimation = CostEstimationDraft::updateOrCreate($findArr, $costEstimationInputs);

                \DB::commit();

                if(!isset($inputs['skip_validation'])) {
                    $route   = 'leads-management.view-cost-estimation';
                    $message = 'Cost estimation saved Successfully.';
                    //route($route, $tilDraftId)
                    return redirect()->back()->withSuccess($message);
                } else {
                    $message = 'Cost estimation saved Successfully.';
                    $result = ['status' => 1, 'msg' => $message];
                    return response()->json($result);
                }
            } catch (\PDOException $e) {
                \DB::rollBack();

                if(isset($fileNameArr['file']) && !empty($fileNameArr['file']) && count($fileNameArr['file']) > 0) {
                    $this->removeFiles($fileNameArr['file']);
                }

                if(!isset($inputs['skip_validation'])) {
                    $message = 'Database Error: The cost estimation could not be saved.';
                    return redirect()->back()->withError($message)->withInput($inputs);
                } else {
                    $message = 'Database Error: The cost estimation could not be saved.';
                    $result = ['status' => 0, 'msg' => $message];
                    return response()->json($result);
                }
            } catch (\Exception $e) {
                \DB::rollBack();

                if(isset($fileNameArr['file']) && !empty($fileNameArr['file']) && count($fileNameArr['file']) > 0) {
                    $this->removeFiles($fileNameArr['file']);
                }

                if(!isset($inputs['skip_validation'])) {
                    $message = 'Error code 500: internal server error.';
                    return redirect()->back()->withError($message)->withInput($inputs);
                } else {
                    $message = 'Error code 500: internal server error.';
                    $result  = ['status' => 0, 'msg' => $message];
                    return response()->json($result);
                }
            }
        }
    }//end of function

    /**
     * Remove upload files etc.
     *
     * @param  array fileArray []
     * unlink file from server
    */
    public function removeFiles($fileArr = []) 
    {
        foreach ($fileArr as $key => $file) 
        {
            if(file_exists($file)) 
            {
                unlink($file);
            }
        }
    }//end of function

    /**
     * get cost estimation data.
     *
    */
    public function getCostEstimation ($tilDraftId = null)
    {
        $tilDraft = TilDraft::with('costEstimationDraft')->find($tilDraftId);
        if(!$tilDraft) {
            $message = 'Invalid Request, no data found.';
            $result = ['status' => 0, 'msg' => $message, 'data' => null];
            return response()->json($result);
        }

        $message = null; $result  = [];

        $costEstimation = $tilDraft->costEstimationDraft;

        if(empty($costEstimation)) {
            $message = 'No prevoius cost estimation were found.';
            $result  = ['status' => 0, 'msg' => $message, 'data' => null];
        } else {
            $message = null;
            $result  = ['status' => 1, 'msg' => $message, 'data' => $costEstimation];
        }
        return response()->json($result);
    }//end of function

    public function tilApproval(Request $request)
    {
        if($request->isMethod('post')) {

            $authUser = \Auth::user();
            $userId   = $authUser->id;
            $inputs   = $request->except('_token');
            
            if(!$inputs['til_id']) {
                $message = 'Til id not found.';
                return redirect()->back()->withError($message);
            }

            $tilDraft = TilDraft::find($inputs['til_id']);
            if(!$tilDraft) {
                $message = 'Invalid Til id provided.';
                return redirect()->back()->withError($message);
            }
            
            try {
                \DB::beginTransaction();
                /*1=> New, 2=> Open, 3=> Complete, 4=> Sent for Remarks, 5=> Sent for Approval,6=> Rejected by Hod,7 => Abandoned,8 => Closed*/
                $tilInputs = ['status' => $inputs['status']];

                if($tilDraft->update($tilInputs)) {
                    
                    if(!empty($inputs['department_id']) && count($inputs['department_id']) > 0 && $inputs['status'] == 4) {

                        $departmentIds = $inputs['department_id'];

                        foreach ($departmentIds as $key => $departmentId) {
                            if(!empty($departmentId)) {
                                $tilDratfInputs = new TilDraftInputs;

                                $tilDratfInputs->til_draft_id  = $tilDraft->id;
                                $tilDratfInputs->department_id = $departmentId;

                                if(isset($inputs['user_id'][$key]) && !empty($inputs['user_id'][$key])) {
                                    $tilDratfInputs->user_id = $inputs['user_id'][$key];
                                }

                                if(isset($inputs['hod_remarks'][$key]) && !empty($inputs['hod_remarks'][$key])) {
                                    $tilDratfInputs->hod_remarks = $inputs['hod_remarks'][$key];
                                }
                                $tilDratfInputs->isactive = 1;
                                $tilDraft->tilDraftInputs()->save($tilDratfInputs);
                            }
                        }
                    }

                    if(isset($inputs['comments']) && !empty($inputs['comments'])) {
                        $commentsInputs = new Comments;
                        $commentsInputs->user_id  = $userId;
                        $commentsInputs->comments = $inputs['comments'];
                        $comments = $tilDraft->comments()->save($commentsInputs);
                    }
                } else {
                    return redirect()->back()->withError('Error occurs please try again.');
                }

                \DB::commit();

                $messageText = 'Til approved successfully.';
                if($inputs['status'] == 3) {
                    $messageText = 'Til rejected successfully.';
                }

                if(isset($inputs['status']) && $inputs['status'] == 7) {
                    $route = 'leads-management.get-list-til';
                    return redirect()->route($route)->withSuccess($messageText);
                } else {
                    return redirect()->back()->withSuccess($messageText);
                }
            } catch (\PDOException $e) {
                \DB::rollBack();

                $messageText = 'approved.';
                if($inputs['status'] == 3) {
                    $messageText = 'rejected.';
                }

                return redirect()->back()->withError('Database Error: The TIL could not be '.$messageText.'.')->withInput($inputs);
            } catch (\Exception $e) {
                \DB::rollBack();

                return redirect()->back()->withError('Error code 500: internal server error.')->withInput($inputs);
            }
        }
    }//end of function

    public function saveTilRemarks(Request $request, $tilDraftId = null)
    {
        $authUser = \Auth::user();
        if($request->isMethod('post')) {
            $userId = $authUser->id;
            $inputs = $request->except('_token');

            if(!$tilDraftId) {
                $message = 'Til id not found.';
                return redirect()->back()->withError($message);
            }

            $tilDraft = TilDraft::find($tilDraftId);

            if(!$tilDraft) {
                $message = 'Invalid Til id provided.';
                return redirect()->back()->withError($message);
            }

            try {
                \DB::beginTransaction();

                $inputId = $inputs['draft_input_id'];
                $userRemarks = $inputs['user_remarks'];

                if(!empty($inputId) && count($inputId) > 0) {

                    $isSaved = false;
                    $teamHodId = $tilDraft->user->leaveAuthorities()->where(['leave_authorities.priority' => '2'])->pluck('manager_id')->first();

                    $notificationMessage = $authUser->employee->fullname. " has submitted his/her remarks on til with til number:" .$tilDraft->til_code;

                    foreach ($inputId as $key => $id) {
                        $tilDraftInput = TilDraftInputs::where(['id' => $id, 'til_draft_id' => $tilDraftId])->first();

                        if(isset($userRemarks[$id]) && !empty($userRemarks[$id])) {
                            $remarks = $userRemarks[$id];

                            if($tilDraftInput->update(['user_remarks' => $remarks])) {
                                $isSaved = true;
                            }
                        }
                    }
                    if($isSaved) {
                        $notificationData = [
                            'sender_id'    => $userId,
                            'receiver_id'  => $teamHodId,
                            'label'        => 'Til Remarks Submitted.',
                            'read_status'  => '0',
                            'redirect_url' => 'leads-management/view-til/'.$tilDraft->id,
                            'message'      => $notificationMessage
                        ];
                        $tilDraft->notifications()->create($notificationData);
                    }
                }

                \DB::commit();

                $messageText = 'Til remarks saved successfully.';
                return redirect()->back()->withSuccess($messageText);

            } catch (\PDOException $e) {
                \DB::rollBack();

                return redirect()->back()->withError('Database Error: The TIL remarks could not be saved.')->withInput($inputs);
            } catch (\Exception $e) {
                \DB::rollBack();

                return redirect()->back()->withError('Error code 500: internal server error.')->withInput($inputs);
            }
        }
    }

    public function markTilEditable(Request $request)
    {
        if($request->isMethod('post')) {

            $userId = \Auth::user()->id;
            $inputs = $request->except('_token');

            $tilIds = $inputs['til_ids'];

            if(empty($tilIds)) {
                $message = 'Til id not found.';
                $result = ['status' => 0, 'msg' => $message];
                if($request->ajax()) {
                    return response()->json($result);
                } else {
                    return redirect()->back()->withError($message);
                }
            }

            try {
                \DB::beginTransaction();
                /*status= 1 New,2 Open,3 Complete,4 Rejected by Hod,5 Closed,6 Abandoned*/
                $tilInputs = ['status' => 2, 'is_editable' => 1];

                if($request->ajax()) {
                    $tilIds = $tilIds;

                    $tilDraft = TilDraft::find($tilIds);
                    if(!$tilDraft) {
                        $message = 'Invalid Til id provided.';
                        $result = ['status' => 0, 'msg' => $message];
                        return response()->json($result);
                    }

                    $res = $tilDraft->update($tilInputs);
                    $tilDraft->costEstimationDraft->update(['is_editable' => 1, 'is_complete' => 0]);

                } else {
                    $tilIds = explode(',', $tilIds);

                    foreach ($tilIds as $key => $id) {
                        $til = TilDraft::find($id);

                        $res = $til->update($tilInputs);
                        $til->costEstimationDraft->update(['is_editable' => 1, 'is_complete' => 0]);
                    }
                }
                \DB::commit();

                if($res) {
                    $message = 'TIL is editable now.';
                    if($request->ajax()) {
                        $result = ['status' => 1, 'msg' => $message];
                        return response()->json($result);
                    } else {
                        return redirect()->back()->withSuccess($message);
                    }
                } else {
                    $message = 'Error in making til editable, please try again later.';
                    if($request->ajax()) {
                        $result = ['status' => 0, 'msg' => $message];
                        return response()->json($result);
                    } else {
                        return redirect()->back()->withError($message);
                    }
                }
            } catch (\PDOException $e) {
                \DB::rollBack();

                $message = 'Database Error: Could not make TIL editable, please try again later.';
                if($request->ajax()) {
                    $result = ['status' => 0, 'msg' => $message];
                    return response()->json($result);
                } else {
                    return redirect()->back()->withError($message);
                }

            } catch (\Exception $e) {
                \DB::rollBack();

                $message = 'Error code 500: internal server error.';
                if($request->ajax()) {
                    $result = ['status' => 0, 'msg' => $message];
                    return response()->json($result);
                } else {
                    return redirect()->back()->withError($message);
                }
            }
        }
    }//end of function

    public function markTilFiled(Request $request)
    {
        if($request->isMethod('post') && $request->ajax()) {

            $userId = \Auth::user()->id;
            $inputs = $request->except('_token');
            $tilId  = $inputs['til_id'];            

            if(empty($tilId)) {
                $message = 'Til id not found.';
                $result = ['status' => 0, 'msg' => $message];
                return response()->json($result);
            }

            try {
                \DB::beginTransaction();
                /*1 => 'New', 2 => 'Open', 3 => 'Complete',4 => 'Sent for Remarks', 5 => 'Sent For Approval', 6 => 'Rejected by Hod',  7 => 'Abandoned', 8 => 'Closed'*/
                $contain = [
                    'tilContact', 'tilObligation', 'tilSpecialEligibility',
                    'costEstimationDraft', 'comments', 'assignedUsers', 'tilDraftInputs'
                ];

                $tilDraft = TilDraft::with($contain)->find($tilId);
                
                if(!$tilDraft) {
                    $message = 'Invalid Til id provided.';
                    $result = ['status' => 0, 'msg' => $message];
                    return response()->json($result);
                }

                $res = false;
                $til = Til::create($tilDraft->toArray());
                if($til) {
                    $res = true;
                    if(!empty($tilDraft->tilContact->toArray())) {
                        $tilContact = $tilDraft->tilContact->toArray();
                        foreach ($tilContact as $ckey => $tcv) {
                            $til->tilContacts()->create($tcv);
                        }
                    }
                    if(!empty($tilDraft->tilObligation->toArray())) {
                        $tilObligation = $tilDraft->tilObligation->toArray();
                        foreach ($tilObligation as $okey => $ov) {
                            $til->tilObligations()->create($ov);
                        }
                    }
                    if(!empty($tilDraft->tilSpecialEligibility->toArray())) {
                        $tilSpecialEligibility = $tilDraft->tilSpecialEligibility->toArray();
                        foreach ($tilSpecialEligibility as $sekey => $sev) {
                            $til->tilSpecialEligibility()->create($sev);
                        }
                    }
                    if(!empty($tilDraft->costEstimationDraft->toArray())) {
                        $costEstimation = $tilDraft->costEstimationDraft->toArray();
                        $til->costEstimation()->create($costEstimation);
                    }
                    if(!empty($tilDraft->comments->toArray())) {
                        $comments = $tilDraft->comments->toArray();
                        foreach ($comments as $ckey => $cv) {
                            $til->comments()->create($cv);
                        }
                    }
                    if(!empty($tilDraft->assignedUsers->toArray())) {
                        $assignedUsers = $tilDraft->assignedUsers->toArray();
                        foreach ($assignedUsers as $aukey => $auv) {
                            $til->assignedUsers()->create($auv);
                        }
                    }
                    if(!empty($tilDraft->tilDraftInputs->toArray())) {
                        $tilInputs = $tilDraft->tilDraftInputs->toArray();
                        foreach ($tilInputs as $tikey => $tiv) {
                            $til->tilInputs()->create($tiv);
                        }
                    }
                    $tilDraft->update(['status' => 9]);

                    if(!empty($inputs['til_filed_date'])) {
                        $tilFiledDate = date('Y-m-d H:i a', strtotime($inputs['til_filed_date']));
                       $til->update(['til_filed_date' => $tilFiledDate]);
                    }
                }

                \DB::commit();
                if($res) {
                    $message = 'TIL is Filed successfully.';
                    $result = ['status' => 1, 'msg' => $message];
                } else {
                    $message = 'Error in marking til filed, please try again later.';
                    $result = ['status' => 0, 'msg' => $message];
                }
                return response()->json($result);
            } catch (\PDOException $e) {
                \DB::rollBack();

                $message = 'Database Error: Could not mark TIL filed, please try again later.';
                $result = ['status' => 0, 'msg' => $message];
                return response()->json($result);
            } catch (\Exception $e) {
                \DB::rollBack();

                $message = 'Error code 500: internal server error.';
                $result = ['status' => 0, 'msg' => $message];
                return response()->json($result);
            }
        } else {
            $message = 'This actions is not allowed.';
            return redirect()->back()->withError($message);
        }
    }//end of function

    public function getDepartmentWiseEmployees(Request $request)
    {
        $departmentIds = $request->department_ids;

        $data = User::whereHas('permissions', function($q) { 
                    $q->where('name', 'leads-management.view-til-remarks'); 
                })->whereHas('employee', function($q) { 
                    $q->where(['approval_status' => '1', 'isactive' => 1]); 
                })->whereHas('employeeProfile', function($q) use($departmentIds) { 
                    $q->whereIn('department_id', $departmentIds)->where('isactive', 1);
                })->with('employee')->get();
        return $data;        

    }//end of function

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function opportunityProgressStatus()
    {
        return view('leads_management.opportunity_progress_status');
    }//end of function

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function followUp()
    {
        return view('leads_management.follow_up');
    }//end of function
}