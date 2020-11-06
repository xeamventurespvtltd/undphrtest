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
use stdClass;
use Validator;

class MastertableController extends Controller
{
    /*
        Get the list of companies from the database & show it on a page
    */
    function listCompanies()
    {   
		$companies = Company::with('creator.employee:id,user_id,fullname')
        					->with('approval.approver.employee:id,user_id,fullname')
        					->orderBy('created_at','DESC')
        					->get();	 
				       
        return view('mastertables.list_companies')->with(['companies'=>$companies]);

    }//end of function

    /*
        Perform the respective action if user performs add/edit/approve/activate/deactivate a company
    */
    function companyAction($action,$company_id = null)
    {
    	$user = Auth::user();

    	if(!empty($company_id)){
    		$company = Company::find($company_id);
    	}

        if($action == "add"){

            $data['action'] = $action;

            return view('mastertables.add_company_form')->with(['data'=>$data]);

        }elseif($action == "edit"){

            $data['company'] = $company;
            $data['action'] = $action;

            return view('mastertables.edit_company_form')->with(['data'=>$data]);

        }elseif($action == "approve"){

            $company->approval()->create(['approver_id'=>$user->id]);
            $company->update(['approval_status'=>'1']);

            return redirect("mastertables/companies");

        }elseif($action == "activate") {
           
            $company->update(['isactive'=>1]);
            
            return redirect("mastertables/companies");

        }elseif($action == "deactivate") {

			$company->update(['isactive'=>0]);
            
            return redirect("mastertables/companies");

        }

    }//end of function 

    /*
        Ajax request to check whether the given company parameters are unique while adding/editing
        the company
    */
    function checkUniqueCompany(Request $request)
    {
    	$result = 	[
                    	'company_name' => 1,
                    	'company_phone' => 1,
                    	'company_pf_acc' => 1,
                    	'tan_no' => 1,
                    	'company_email' => 1
                    ];

        if(!empty($request->company_name)){
            $company = Company::where(['name' => $request->company_name])->first();

            if(!empty($company)){
                $result['company_name'] = 0;
            }

        }else{
            $result['company_name'] = 2;

        }

        if(!empty($request->company_phone)){
            $company = Company::where(['phone' => $request->company_phone])->first();

            if(!empty($company)){
                $result['company_phone'] = 0;
            }

        }else{
            $result['company_phone'] = 2;

        }

        if(!empty($request->company_pf_acc)){
        	$company = Company::where(['pf_account_number' => $request->company_pf_acc])->first();
          
            if(!empty($company)){
                $result['company_pf_acc'] = 0;
            }

        }else{
            $result['company_pf_acc'] = 2;

        }

        if(!empty($request->company_email)){
        	$company = Company::where(['email' => $request->company_email])->first();

            if(!empty($company)){
                $result['company_email'] = 0;
            }

        }else{
            $result['company_email'] = 2;

        }

        if(!empty($request->tan_no)){
        	$company = Company::where(['tan_number' => $request->tan_no])->first();
            
            if(!empty($company)){
                $result['tan_no'] = 0;
            }

        }else{
            $result['tan_no'] = 2;

        }

        return $result;
        
    }//end of function

    /*
        Ajax request to check whether the given ESI number is unique 
    */
    function checkUniqueEsiRegistration(Request $request)
    {
    	$result = ['esi_number' => 1];

        if(!empty($request->esi_number)){
            $esi = EsiRegistration::where(['esi_number' => $request->esi_number])->first();

            if(!empty($esi)){
                $result['esi_number'] = 0;
            }

        }else{
            $result['esi_number'] = 2;

        }

        return $result;

    }//end of function

    /*
        Ajax request to check whether the given PT Registration certificate number/PTO circle number are unique 
    */
    function checkUniquePtRegistration(Request $request)
    {
    	$result = ['certificate_number' => 1, 'pto_circle_number' => 1];

        if(!empty($request->certificate_number)){
            $pt = PtRegistration::where(['certificate_number' => $request->certificate_number])->first();

            if(!empty($pt)){
                $result['certificate_number'] = 0;
            }
        }

        if(!empty($request->pto_circle_number)){
			$pt = PtRegistration::where(['pto_circle_number' => $request->pto_circle_number])->first();

            if(!empty($pt)){
                $result['pto_circle_number'] = 0;
            }
        }

        return $result;

    }//end of function

    /*
        Save new company details to the database 
    */
    function createCompany(Request $request)
    {
    	$request->validate([
            'company_name' => 'bail|required|max:40',
            'company_address' => 'required',
            'company_phone_number' => 'bail|required|min:10',
            'company_email' => 'bail|required|email',
            'pf_account_number' => 'required',
            'tan_number' => 'required'
        ]);

        $user = Auth::user();

        $company_data = [   
		                    'name' => $request->company_name,
		                    'address' => $request->company_address,
		                    'phone' => $request->company_phone_number,
		                    'email' => $request->company_email,
		                    'website' => $request->company_website,
		                    'creator_id' => $user->id,   
		                    'tan_number' => $request->tan_number,
		                    'pf_account_number' => $request->pf_account_number,
		                    'responsible_person' => $request->responsible_person,
		                    'phone_extension' => $request->company_phone_extension,
		                    'dbf_file_code' => $request->dbf_file_code,
		                    'extension' => $request->extension,
		                    'approval_status' => '0',
		                ];

        $company = Company::create($company_data);             

        $approver = User::where('id','!=',1)
        				->permission('approve-company')
        				->first();

        if(!empty($approver)){
        	$notification_data = [
        							 'sender_id' => $user->id,
        							 'receiver_id' => $approver->id,
        							 'label' => 'Company Created',
        							 'read_status' => '0'
        						 ]; 

        	$notification_data['message'] = 'Please verify and approve the details of '.$company->name.' company.';	

        	$company->notifications()->create($notification_data);		 
        }

        return redirect('mastertables/companies');	
               
    }//end of function

    /*
        Update company details in the database & keep a log as well
    */
    function editCompany(Request $request)
    {
        $request->validate([
            'company_name' => 'bail|required|max:40',
            'company_address' => 'required',
            'company_phone_number' => 'bail|required|min:10',
            'company_email' => 'bail|required|email',
            'pf_account_number' => 'required',
            'tan_number' => 'required'
        ]);

        $user = Auth::user();
        $company = Company::find($request->company_id);
        
        $log = Log::where(['name'=>'Company-Updated'])->first();
        $log_data = [
                        'log_id' => $log->id,
                        'data' => $company->toJson()
                    ];

        $username = $user->employee->fullname;          
        $log_data['message'] = $log->name. " by ".$username."(".$user->id.").";         
        $company->logDetails()->create($log_data);

        $company_data = [   
                            'name' => $request->company_name,
                            'address' => $request->company_address,
                            'phone' => $request->company_phone_number,
                            'email' => $request->company_email,
                            'website' => $request->company_website,
                            'creator_id' => $user->id,   
                            'tan_number' => $request->tan_number,
                            'pf_account_number' => $request->pf_account_number,
                            'responsible_person' => $request->responsible_person,
                            'phone_extension' => $request->company_phone_extension,
                            'dbf_file_code' => $request->dbf_file_code,
                            'extension' => $request->extension
                        ];

        $company->update($company_data);

        return redirect('mastertables/companies');

    }//end of function

    /*
        Ajax request to get company details & show them in a modal on companies list page
    */
    function additionalCompanyInfo(Request $request)
    {
    	$company = Company::find($request->company_id);
        $view = View::make('mastertables.additional_company_info', ['data' => $company]);

        $contents = $view->render();
        return $contents;

    }//end of function

    /*
        Ajax request to get company details & show them in a modal on companies list page
    */
    function listEsiRegistrations($company_id)
    {
    	$company = Company::find($company_id);
    	$esi_registrations = $company->esiRegistrations()
    								 ->with('location:id,name')
                                     ->orderBy('created_at','DESC')
    								 ->get();

        return view('mastertables.list_esi_registrations')->with(['esi_registrations'=>$esi_registrations,'company'=>$company]);

    }//end of function

    /*
        Edit/Activate/Deactivate an ESI registration in the database
    */
    function esiRegistrationAction($action,$esi_registration_id)
    {
        $user = Auth::user();

        if(!empty($esi_registration_id)){
            $esi_registration = EsiRegistration::where(['id'=>$esi_registration_id])
                                                ->with(['company:id,name'])
                                                ->with('location.state:id,name')
                                                ->first();
        }

        if($action == "edit"){

            $data['companies'] = Company::where(['isactive'=>1])->get();
            $data['action'] = $action;
            $data['states'] = State::where(['isactive'=>1])->get();
            $data['company_id'] = $esi_registration->company->id;
            $data['esi_registration'] = $esi_registration;

            return view('mastertables.esi_registration_form')->with(['data'=>$data]);

        }elseif($action == "activate") {
           
            $esi_registration->update(['isactive'=>1]);
            
            return redirect("mastertables/company-esi-registrations/$esi_registration->company_id");

        }elseif($action == "deactivate") {

            $esi_registration->update(['isactive'=>0]);
            
            return redirect("mastertables/company-esi-registrations/$esi_registration->company_id");

        }

    }//end of function

    /*
        Show the add ESI registration form with required details 
    */
    function addEsiRegistration($company_id)
    {
        $data['companies'] = Company::where(['isactive'=>1])->get();
        //$data['locations'] = Location::where(['isactive'=>1,'has_esi'=>1])->get();
        $data['states'] = State::where(['isactive'=>1])->get();
        $data['action'] = "add";
        $data['company_id'] = $company_id;

        return view('mastertables.esi_registration_form')->with(['data'=>$data]);
    
    }//end of function

    /*
        Add a new ESI Registration or update an existing one in the database 
    */
    function saveEsiRegistration(Request $request)
    {
        if($request->action == "add"){
            $request->validate([
                'company_id' => 'required',
                'esi_number' => 'required|unique:esi_registrations,esi_number',
                'esi_address' => 'required',
                'location_id' => 'required'
            ]);

            $check_unique = EsiRegistration::where(['company_id'=>$request->company_id,'location_id'=>$request->location_id])->first();

            if(!empty($check_unique)){
                return redirect()->back()->with('save_error',"ESI registration already exists at the given location.");
            }

            $esi_data = [
                            'company_id' => $request->company_id,
                            'location_id' => $request->location_id,
                            'local_office' => $request->esi_local_office,
                            'esi_number' => $request->esi_number,
                            'address' => $request->esi_address
                        ];

            $esi = EsiRegistration::create($esi_data);            

        }else{
            $request->validate([
                'company_id' => 'required',
                'esi_number' => 'required',
                'esi_address' => 'required',
                'location_id' => 'required'
            ]);

            $esi = EsiRegistration::find($request->esi_registration_id);

            $esi_data = [
                            'company_id' => $request->company_id,
                            'location_id' => $request->location_id,
                            'local_office' => $request->esi_local_office,
                            'esi_number' => $request->esi_number,
                            'address' => $request->esi_address
                        ];

            $esi->update($esi_data);            
        }

        return redirect("mastertables/company-esi-registrations/$esi->company_id");
        
    }//end of function

    /*
        Get PT Registrations from the database & show them in a list 
    */
    function listPtRegistrations($company_id)
    {
        $company = Company::find($company_id);
        $pt_registrations = $company->ptRegistrations()
                                     ->with('state:id,name')
                                     ->orderBy('created_at','DESC')
                                     ->get();

        return view('mastertables.list_pt_registrations')->with(['pt_registrations'=>$pt_registrations,'company'=>$company]);

    }//end of function

    /*
        Perform specific action for edit/activate/deactivate a PT registration 
    */
    function ptRegistrationAction($action,$pt_registration_id)
    {
        $user = Auth::user();

        if(!empty($pt_registration_id)){
            $pt_registration = PtRegistration::where(['id'=>$pt_registration_id])
                                                ->with(['company:id,name'])
                                                ->first();
        }

        if($action == "edit"){

            $data['companies'] = Company::where(['isactive'=>1])->get();
            $data['states'] = State::where(['isactive'=>1,'has_pt'=>1])->get();
            $data['action'] = $action;
            $data['company_id'] = $pt_registration->company->id;
            $data['pt_registration'] = $pt_registration;

            return view('mastertables.pt_registration_form')->with(['data'=>$data]);

        }elseif($action == "activate") {
           
            $pt_registration->update(['isactive'=>1]);
            
            return redirect("mastertables/company-pt-registrations/$pt_registration->company_id");

        }elseif($action == "deactivate") {

            $pt_registration->update(['isactive'=>0]);
            
            return redirect("mastertables/company-pt-registrations/$pt_registration->company_id");

        }

    }//end of function

    /*
        Show create PT Registration form with necessary details  
    */
    function addPtRegistration($company_id)
    {
        $data['companies'] = Company::where(['isactive'=>1])->get();
        $data['states'] = State::where(['isactive'=>1,'has_pt'=>1])->get();
        $data['action'] = "add";
        $data['company_id'] = $company_id;

        return view('mastertables.pt_registration_form')->with(['data'=>$data]);
    
    }//end of function

    /*
        Add a new PT Registration to the database or update an existing one  
    */
    function savePtRegistration(Request $request)
    {
        if($request->action == "add"){
            $request->validate([
                'company_id' => 'required',
                'certificate_number' => 'required|unique:pt_registrations,certificate_number',
                'address' => 'required',
                'state_id' => 'required'
            ]);

            $check_unique = PtRegistration::where(['company_id'=>$request->company_id,'state_id'=>$request->state_id])->first();

            if(!empty($check_unique)){
                return redirect()->back()->with('save_error',"PT registration already exists at the given state.");
            }

            $pt_data =  [
                            'company_id' => $request->company_id,
                            'state_id' => $request->state_id,
                            'certificate_number' => $request->certificate_number,
                            'address' => $request->address,
                            'pto_circle_number' => $request->pto_circle_number,
                            'return_period' => $request->return_period 
                        ];

            $pt = PtRegistration::create($pt_data);            

        }else{
            $request->validate([
                'company_id' => 'required',
                'certificate_number' => 'required',
                'address' => 'required',
                'state_id' => 'required'
            ]);

            $pt = PtRegistration::find($request->pt_registration_id);

            $pt_data =  [
                            'company_id' => $request->company_id,
                            'state_id' => $request->state_id,
                            'certificate_number' => $request->certificate_number,
                            'address' => $request->address,
                            'pto_circle_number' => $request->pto_circle_number,
                            'return_period' => $request->return_period 
                        ];

            $pt->update($pt_data);            
        }

        return redirect("mastertables/company-pt-registrations/$pt->company_id");
        
    }//end of function

    /*
        Ajax request to get a company's TAN No. & PF No.  
    */
    function companyTanPf(Request $request)
    {
        $company = Company::find($request->company_id);
        
        if(!empty($company)){
            $result['pf_no'] = $company->pf_account_number;
            $result['tan_no'] = $company->tan_number;
        }else{
            $result['pf_no'] = "";
            $result['tan_no'] = "";
        }

        return $result;
        
    }//end of function

    /*
        Ajax request to get a company's PT Registration data  
    */
    function companyPtCertificateNo(Request $request)
    {
        $state_ids = $request->state_ids;
        $company_id = $request->company_id;

        $result = [];

        foreach ($state_ids as $key => $value) {
            $data['state'] = State::find($value); 
            $data['locations'] = $data['state']->locations()->where(['isactive'=>1])->get();
            $data['pt_data'] = PtRegistration::where(['company_id'=>$company_id,'state_id'=>$value])
                                            ->first();

            $result[] = $data;           
        }            

        return $result;
    }//end of function

    /*
        Ajax request to get a company's ESI Registration data  
    */
    function companyEsiNo(Request $request)
    {
        $location_ids = $request->location_ids;
        $company_id = $request->company_id;

        $result = [];

        foreach ($location_ids as $key => $value) {
            $data['location'] = Location::find($value); 
            $data['esi_data'] = EsiRegistration::where(['company_id'=>$company_id,'location_id'=>$value])->first();

            $result[] = $data;           
        }            

        return $result;
    }//end of function

    /*
        Get list of projects from the database & show them on a page  
    */
    function listProjects()
    {
        $projects = Project::with('creator.employee:id,user_id,fullname')
                        ->with('approval.approver.employee:id,user_id,fullname')
                        ->with('company:id,name')
                        ->orderBy('created_at','DESC')
                        ->get();        

        session(['last_inserted_project' => 0,'last_tabname' => '']);

        return view('mastertables.list_projects')->with(['projects'=>$projects]);

    }//end of function

    /*
        Ajax request to check whether mobile number & email are unique while adding project contact in the table  
    */
    function checkUniqueProjectContact(Request $request)
    {
        $result = [
                    'mobile_number' => 1,
                    'email' => 1
                  ];

        if(!empty($request->mobile_number)){
            $contact = ProjectContact::where(['mobile_number' => $request->mobile_number,'project_id' => $request->project_id])->first();

            if(!empty($contact)){
                $result['mobile_number'] = 0;
            }
        }else{
            $result['mobile_number'] = 2;
        } 

        if(!empty($request->email)){
            $contact = ProjectContact::where(['email' => $request->email,'project_id' => $request->project_id])->first();

            if(!empty($contact)){
                $result['email'] = 0;
            }
        }else{
            $result['email'] = 2;
        }   

        return $result; 

    }//end of function

    /*
        Ajax request to check whether mobile number & email are unique while editing project contact 
    */
    function checkUniqueEditProjectContact()
    {
        $result = [
                    'mobile_number' => 1,
                    'email' => 1
                  ];

        if(!empty($request->mobile_number)){
            $contact = ProjectContact::where(['mobile_number' => $request->mobile_number,'project_id' => $request->project_id])->first();

            if(!empty($contact) && ($contact->id != $request->contact_id)){
                $result['mobile_number'] = 0;
            }
        }else{
            $result['mobile_number'] = 2;
        } 

        if(!empty($request->email)){
            $contact = ProjectContact::where(['email' => $request->email,'project_id' => $request->project_id])->first();

            if(!empty($contact) && ($contact->id != $request->contact_id)){
                $result['email'] = 0;
            }
        }else{
            $result['email'] = 2;
        }   

        return $result; 

    }//end of function

    /*
        Add new project contact in the database 
    */
    function createProjectContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required',
            'mobileNo' => 'bail|required',
            'role' => 'bail|required',
            'email' => 'bail|required'
        ]);

        if($validator->fails()) {
            return redirect()->back()
                    ->withErrors($validator,'contact')
                    ->withInput();

        }else{

            if(empty($request->projectId) || $request->projectId == 0){
                return redirect()->back()->with('projectError','Please fill the project details form and then fill the contacts form.');
            }else{

                $project = Project::find($request->projectId);

                $data = [
                            'name'  => $request->name,
                            'mobile_number' => $request->mobileNo,
                            'role' => $request->role,
                            'email' => $request->email
                        ];

                $contact = $project->projectContacts()->create($data);

                if($request->action == 'add'){
                    session(['last_inserted_project' => $request->projectId,'last_tabname' => 'contactDetailsTab']);

                    return redirect("mastertables/projects/add");
                }else{
                    session(['last_inserted_project' => 0,'last_tabname' => 'contactDetailsTab']);

                    return redirect("mastertables/projects/edit/$request->projectId");
                }
            }
        }

    }//end of function

    /*
        Update existing project contact in the database 
    */
    function editProjectContact(Request $request)
    {
        $data = [
                    'name'  => $request->name,
                    'mobile_number' => $request->mobileNo,
                    'role' => $request->role,
                    'email' => $request->email
                ];

        $contact = ProjectContact::find($request->contactId);
        $contact->update($data);
        session(['last_inserted_project' => 0,'last_tabname' => 'contactDetailsTab']);

        return redirect("mastertables/projects/edit/$request->projectId");    

    }//end of function

    /*
        Perform appropriate action in response to add/edit/activate/deactivate/approve a project 
    */
    function projectAction($action, $project_id = null)
    {
        if(!empty($project_id)){
            $project = Project::find($project_id);
        }

        $user = Auth::user();

        if($action == 'add') {
            $data['action'] = $action;
            $data['companies'] = Company::where(['isactive'=>1,'approval_status'=>'1'])->get();
            $data['locations'] = Location::where(['isactive'=>1])->get();
            $data['states'] = State::where(['isactive'=>1])->get();
            $data['employees'] = Employee::where(['isactive'=>1,'approval_status'=>'1'])->get();
            $data['salary_structures'] = SalaryStructure::where(['isactive'=>1])->get();
            $data['salary_cycles'] = SalaryCycle::where(['isactive'=>1])->get();

            $last_inserted_project = session('last_inserted_project');

            if(!empty($last_inserted_project) || $last_inserted_project == 0){
                $data['contacts'] = ProjectContact::where(['project_id'=>$last_inserted_project])->get();

            }else{
                $data['contacts'] = collect();

            }

            $data['project'] = new stdClass();
            $data['project']->employees = [];  //selected responsible persons
            $data['project']->proj_states = [];  
            $data['project']->proj_locations = [];  
            $data['project']->proj_allState = [];  

            $data['proj_documents'] = [];

            return view('mastertables.project_form')->with(['data'=>$data]);
        
        }elseif($action == 'edit'){
            $data['action'] = $action;
            $data['companies'] = Company::where(['isactive'=>1,'approval_status'=>'1'])->get();
            
            $data['states'] = State::where(['isactive'=>1])->get();
            $data['employees'] = Employee::where(['isactive'=>1,'approval_status'=>'1'])->get();
            $data['salary_structures'] = SalaryStructure::where(['isactive'=>1])->get();
            $data['salary_cycles'] = SalaryCycle::where(['isactive'=>1])->get();
            
            $project->employees = $project->projectResponsiblePersons()->pluck('user_id')->toArray();
            $project->proj_states = $project->states()->pluck('state_id')->toArray();
            $project->proj_locations = $project->locations()->pluck('location_id')->toArray();

            $location_states = DB::table('location_project as lp')
                               ->join('locations as l','lp.location_id','=','l.id')
                               ->whereIn('lp.location_id',$project->proj_locations)
                               ->groupBy('l.state_id')
                               ->pluck('l.state_id')->toArray(); 

            $project->proj_allState = $location_states;                   

            $data['locations'] = Location::whereIn('state_id',$location_states)->get();

            $proj_documents = [];
            $counter = 0;
            $project_documents = $project->documents()->orderBy('document_id')->get();
            foreach ($project_documents as $document) {
                $proj_documents[$counter]['document_id'] = $document->pivot->document_id;
                $proj_documents[$counter]['name'] = $document->pivot->name;
                ++$counter;
            }


            $data['proj_documents'] = $proj_documents;
            
            $data['project'] = $project;
            $data['contacts'] = $project->projectContacts()->get();

            return view('mastertables.project_form')->with(['data'=>$data]);

        }elseif($action == 'approve'){
            $project->approval()->create(['approver_id'=>$user->id]);
            $project->approval_status = '1';
            $project->save();

            return redirect()->back();

        }elseif($action == 'activate'){
            $project->isactive = 1;
            $project->save();

            return redirect()->back();

        }elseif($action == 'deactivate'){
            $project->isactive = 0;
            $project->save();

            return redirect()->back();
            
        }
    }//end of function

    /*
        Save new project in the database or update an existing one & send notification to the concerned user  
    */
    function saveProject(Request $request)
    {
        $user = Auth::user();

        if($request->action == 'add'){
            $request->validate([
                'projectName' => 'bail|required|unique:projects,name',
                'projectAddress' => 'required',
                'companyId' => 'required'
            ]);

            $project_data = [   
                        'company_id' => $request->companyId,
                        'name' => $request->projectName,
                        'address' => $request->projectAddress,
                        'salary_structure_id' => $request->salaryStructureId,
                        'salary_cycle_id' => $request->salaryCycleId,
                        'number_of_resources' => $request->noOfResources,
                        'type' => $request->projectType,
                        'tenure_years' => $request->tenureYears,
                        'tenure_months' => $request->tenureMonths,
                        'creator_id' => $user->id,    
                        'approval_status' => '0' 
                    ];

            $project = Project::create($project_data);

            session(['last_inserted_project' => $project->id,'last_tabname' => 'contactDetailsTab']);

            $state_ids = $request->stateId; 
            $project->states()->sync($state_ids);  

            if(!empty($request->locationId)){
                $location_ids = $request->locationId;
                $project->locations()->sync($location_ids);
            }
             
            foreach($request->employeeIds as $key => $value) {
                $project->projectResponsiblePersons()->create(['user_id'=>$value]);
            }

            $document_category = DocumentCategory::where(['name'=>'Project'])->first();
            $documents = $document_category->documents()->orderBy('id')->get();

            ////////////////////////////////////////////

            if($request->hasFile('projectAgreement')) {
                $file = time().'.'.$request->file('projectAgreement')->getClientOriginalExtension();
                $request->file('projectAgreement')->move(config('constants.uploadPaths.uploadProjectDocument'), $file);
            
                $document_data['name'] = $file;
            }else{
                $document_data['name'] = "";
            } 

            $project->documents()->attach($documents[0],$document_data);

            ////////////////////////////////////////////// 

            if($request->hasFile('agreementFile')) {
                $file = time().'.'.$request->file('agreementFile')->getClientOriginalExtension();
                $request->file('agreementFile')->move(config('constants.uploadPaths.uploadProjectDocument'), $file);
            
                $document_data['name'] = $file;
            }else{
                $document_data['name'] = "";
            } 

            $project->documents()->attach($documents[1],$document_data);

            // ////////////////////////////////////////////// 

            if($request->hasFile('loiFile')) {
                $file = time().'.'.$request->file('loiFile')->getClientOriginalExtension();
                $request->file('loiFile')->move(config('constants.uploadPaths.uploadProjectDocument'), $file);
            
                $document_data['name'] = $file;
            }else{
                $document_data['name'] = "";
            } 

            $project->documents()->attach($documents[2],$document_data);

            // ////////////////////////////////////////////// 

            if($request->hasFile('offerLetterFile')) {
                $file = time().'.'.$request->file('offerLetterFile')->getClientOriginalExtension();
                $request->file('offerLetterFile')->move(config('constants.uploadPaths.uploadProjectDocument'), $file);
            
                $document_data['name'] = $file;
            }else{
                $document_data['name'] = "";
            } 

            $project->documents()->attach($documents[3],$document_data);

            // ////////////////////////////////////////////// 

            if($request->hasFile('employeeContract1File')) {
                $file = time().'.'.$request->file('employeeContract1File')->getClientOriginalExtension();
                $request->file('employeeContract1File')->move(config('constants.uploadPaths.uploadProjectDocument'), $file);
            
                $document_data['name'] = $file;
            }else{
                $document_data['name'] = "";
            } 

            $project->documents()->attach($documents[4],$document_data); 

            // ////////////////////////////////////////////// 

            if($request->hasFile('employeeContract2File')) {
                $file = time().'.'.$request->file('employeeContract2File')->getClientOriginalExtension();
                $request->file('employeeContract2File')->move(config('constants.uploadPaths.uploadProjectDocument'), $file);
            
                $document_data['name'] = $file;
            }else{
                $document_data['name'] = "";
            } 

            $project->documents()->attach($documents[5],$document_data);

            // ////////////////////////////////////////////// 

            if($request->hasFile('employeeContract3File')) {
                $file = time().'.'.$request->file('employeeContract3File')->getClientOriginalExtension();
                $request->file('employeeContract3File')->move(config('constants.uploadPaths.uploadProjectDocument'), $file);
            
                $document_data['name'] = $file;
            }else{
                $document_data['name'] = "";
            } 

            $project->documents()->attach($documents[6],$document_data); 

            $approver = User::where('id','!=',1)
                        ->permission('approve-project')
                        ->first();

            if(!empty($approver)){
                $notification_data = [
                                         'sender_id' => $user->id,
                                         'receiver_id' => $approver->id,
                                         'label' => 'Project Created',
                                         'read_status' => '0'
                                     ]; 

                $notification_data['message'] = 'Please verify and approve the details of '.$project->name.' project.'; 

                $project->notifications()->create($notification_data);       
            }             
                
            return redirect("mastertables/projects/add");

        }else{  //if $action == 'edit'

            $request->validate([
                'projectName' => 'required',
                'projectAddress' => 'required',
                'companyId' => 'required'
            ]);

            $project = Project::where('name',$request->projectName)->first();

            if(!empty($project) && $project->id != $request->projectId){
                return redirect()->back()->with('projectError','The project name you have provided already exists.');

            }else{
                $project = Project::find($request->projectId);

                $old_project_data = new Project();
                $old_project_data->project = $project;
                $old_project_data->states = $project->states()->pluck('state_id');
                $old_project_data->locations = $project->locations()->pluck('location_id');
                $old_project_data->responsible_persons = $project->projectResponsiblePersons()->pluck('user_id');

                $log = Log::where(['name'=>'Project-Updated'])->first();
                $log_data = [
                                'log_id' => $log->id,
                                'data' => $old_project_data->toJson()
                            ];

                $username = $user->employee->fullname;          
                $log_data['message'] = $log->name. " by ".$username."(".$user->id.").";         
                $project->logDetails()->create($log_data);
                
                session(['last_inserted_project' => 0,'last_tabname' => '']);

                $project_data = [    
                        'company_id' => $request->companyId,
                        'name' => $request->projectName,
                        'address' => $request->projectAddress,
                        'salary_structure_id' => $request->salaryStructureId,
                        'salary_cycle_id' => $request->salaryCycleId,
                        'number_of_resources' => $request->noOfResources,
                        'type' => $request->projectType,
                        'tenure_years' => $request->tenureYears,
                        'tenure_months' => $request->tenureMonths   
                    ];

                $project->update($project_data);   

                $state_ids = $request->stateId; 
                $project->states()->sync($state_ids); 

                if(!empty($request->locationId)){
                    $location_ids = $request->locationId;
                }else{
                    $location_ids = [];
                }
                $project->locations()->sync($location_ids);

                $selected_persons = $request->employeeIds;
                $saved_persons = $project->projectResponsiblePersons()->pluck('user_id')->toArray(); 

                if(!empty($selected_persons) && !empty($saved_persons)){
                    foreach ($selected_persons as $key => $value) {
                        if(!in_array($value,$saved_persons)){
                            $employee_data['user_id'] = $value; 
                            
                            $project->projectResponsiblePersons()
                                    ->create($employee_data);
                        }
                    }

                    foreach ($saved_persons as $key => $value) {
                        if(!in_array($value,$selected_persons)){
                            $project->projectResponsiblePersons()
                                    ->where(['user_id'=>$value])
                                    ->delete();
                        }
                    }
                }

                $document_category = DocumentCategory::where(['name'=>'Project'])->first();
                $documents = $document_category->documents()->orderBy('id')->get();

                ////////////////////////////////////////////

                if($request->hasFile('projectAgreement')) {
                    $file = time().'.'.$request->file('projectAgreement')->getClientOriginalExtension();
                    $request->file('projectAgreement')->move(config('constants.uploadPaths.uploadProjectDocument'), $file);
                
                    $document_data['name'] = $file;
                    //$project->documents()->detach($documents[0]);
                    //$project->documents()->attach($documents[0],$document_data);

                    DB::table('document_project')
                    ->where(['project_id'=>$project->id,'document_id'=>$documents[0]->id])
                    ->update($document_data);
                } 
                
                ////////////////////////////////////////////// 

                if($request->hasFile('agreementFile')) {
                    $file = time().'.'.$request->file('agreementFile')->getClientOriginalExtension();
                    $request->file('agreementFile')->move(config('constants.uploadPaths.uploadProjectDocument'), $file);
                
                    $document_data['name'] = $file;
                    //$project->documents()->detach($documents[1]);
                    //$project->documents()->attach($documents[1],$document_data);

                    DB::table('document_project')
                    ->where(['project_id'=>$project->id,'document_id'=>$documents[1]->id])
                    ->update($document_data);
                }                

                // ////////////////////////////////////////////// 

                if($request->hasFile('loiFile')) {
                    $file = time().'.'.$request->file('loiFile')->getClientOriginalExtension();
                    $request->file('loiFile')->move(config('constants.uploadPaths.uploadProjectDocument'), $file);
                
                    $document_data['name'] = $file;
                    //$project->documents()->detach($documents[2]);
                    //$project->documents()->attach($documents[2],$document_data);

                    DB::table('document_project')
                    ->where(['project_id'=>$project->id,'document_id'=>$documents[2]->id])
                    ->update($document_data);
                } 

                // ////////////////////////////////////////////// 

                if($request->hasFile('offerLetterFile')) {
                    $file = time().'.'.$request->file('offerLetterFile')->getClientOriginalExtension();
                    $request->file('offerLetterFile')->move(config('constants.uploadPaths.uploadProjectDocument'), $file);
                
                    $document_data['name'] = $file;
                    //$project->documents()->detach($documents[3]);
                    //$project->documents()->attach($documents[3],$document_data);

                    DB::table('document_project')
                    ->where(['project_id'=>$project->id,'document_id'=>$documents[3]->id])
                    ->update($document_data);
                }                

                // ////////////////////////////////////////////// 

                if($request->hasFile('employeeContract1File')) {
                    $file = time().'.'.$request->file('employeeContract1File')->getClientOriginalExtension();
                    $request->file('employeeContract1File')->move(config('constants.uploadPaths.uploadProjectDocument'), $file);
                
                    $document_data['name'] = $file;
                    //$project->documents()->detach($documents[4]);
                    //$project->documents()->attach($documents[4],$document_data);

                    DB::table('document_project')
                    ->where(['project_id'=>$project->id,'document_id'=>$documents[4]->id])
                    ->update($document_data);
                }                

                // ////////////////////////////////////////////// 

                if($request->hasFile('employeeContract2File')) {
                    $file = time().'.'.$request->file('employeeContract2File')->getClientOriginalExtension();
                    $request->file('employeeContract2File')->move(config('constants.uploadPaths.uploadProjectDocument'), $file);
                
                    $document_data['name'] = $file;
                    // $project->documents()->detach($documents[5]);
                    // $project->documents()->attach($documents[5],$document_data);

                    DB::table('document_project')
                    ->where(['project_id'=>$project->id,'document_id'=>$documents[5]->id])
                    ->update($document_data);
                } 

                // ////////////////////////////////////////////// 

                if($request->hasFile('employeeContract3File')) {
                    $file = time().'.'.$request->file('employeeContract3File')->getClientOriginalExtension();
                    $request->file('employeeContract3File')->move(config('constants.uploadPaths.uploadProjectDocument'), $file);
                
                    $document_data['name'] = $file;
                    //$project->documents()->detach($documents[6]);
                    //$project->documents()->attach($documents[6],$document_data);

                    DB::table('document_project')
                    ->where(['project_id'=>$project->id,'document_id'=>$documents[6]->id])
                    ->update($document_data);
                } 

                $notification_data = [
                                         'sender_id' => $user->id,
                                         'receiver_id' => $project->creator_id,
                                         'label' => 'Project Updated',
                                         'read_status' => '0'
                                     ]; 

                $notification_data['message'] = 'Please see the updated details of '.$project->name.' project.'; 

                $project->notifications()->create($notification_data);

                return redirect("mastertables/projects/edit/$request->projectId");    
            }

        }//end of edit else

    }//end of function

    /*
        Ajax request to show project information in a modal  
    */
    function additionalProjectInfo(Request $request)
    {
        $project = Project::where(['id'=>$request->project_id])
                            ->with('salaryStructure:id,name')
                            ->with('salaryCycle:id,name')
                            ->with('company:id,name,pf_account_number')
                            ->with('projectResponsiblePersons.user.employee:id,user_id,fullname')
                            ->with('projectContacts')
                            ->first();                                        

        $data['project'] = $project;
        $data['documents'] = $project->documents()->orderBy('document_id')->get();

        ////////////////////////////////////////////////////////////////

        $state_ids = $project->states()->pluck('state_id')->toArray();
        $company_id = $project->company_id;

        $states = [];

        foreach ($state_ids as $key => $value) {
            $data2['state'] = State::find($value); 

            $data2['pt_data'] = PtRegistration::where(['company_id'=>$company_id,'state_id'=>$value])
                                            ->first();

            $states[] = $data2;           
        }

        $data['states'] = $states;

        /////////////////////////////////////////////////////////////////

        $location_ids = $project->locations()->pluck('location_id')->toArray();
        $locations = [];

        foreach ($location_ids as $key => $value) {
            $data3['location'] = Location::find($value); 

            $data3['esi_data'] = EsiRegistration::where(['company_id'=>$company_id,'location_id'=>$value])->first();

            $locations[] = $data3;           
        }

        $data['locations'] = $locations;

        $view = View::make('mastertables.additional_project_info', ['data' => $data]);
        $contents = $view->render();

        return $contents;
    }//end of function

    /*
        List all the states in database, add & edit them  
    */    
    function states(Request $request)
    {
        $data['action']='';
        $data['error']=[];
        $data['id']=0;
        if($request->btn_submit){
            if($request->btn_submit=='Add'){
                $validatedData = $request->validate([
                    'name' => 'required|max:255',
                ]);
                if(State::where('name', $request->name)->get()->count()==0){
                    $stObj=new State;
                    $stObj->name=$request->name;
                    $stObj->has_pt=$request->has_pt;
                    $stObj->save();
                    if($stObj->id){
                        $data['save_success'] = "State added successfully.";
                    }
                }
            }
            elseif($request->btn_submit=='Update'){

                $validatedData = $request->validate([
                    'name' => 'required|max:255',
                ]);
                if(State::where('name', $request->name)->where('id', '!=', $request->id)->get()->count()==0){
                    State::where('id', $request->id)
                        ->update([
                            'name'=>$request->name,
                            'has_pt'=>$request->has_pt
                        ]);

                    $data['save_success'] = "State updated successfully.";    
                }
            }
        }
        if($request->id){
            $data['state']=State::where('id', $request->id)->first();
            $data['id']=$request->id;
        }
        $data['countries']=Country::orderBy('name', 'asc')->get();
        $data['states']=State::where('country_id', 1)->orderBy('name', 'asc')->get();
        return view('mastertables.states')->with(['data'=>$data]);
    }//end of function

    /*
        List all the cities in database, add & edit them  
    */
    function cities(Request $request)
    {
        $data['action']='';
        $data['error']=[];
        $data['id']=$data['state_id']=0;
        if($request->btn_submit){
            if($request->btn_submit=='Add'){
                $validatedData = $request->validate([
                    'name' => 'required|max:255',
                ]);
                if(City::where('name', $request->name)->where('state_id', $request->state_id)->get()->count()==0){
                    $stObj=new City;
                    $stObj->name=$request->name;
                    $stObj->state_id=$request->state_id;
                    $stObj->save();
                    if($stObj->id){
                        $data['save_success'] = "City added successfully.";
                    }
                }
            }
            elseif($request->btn_submit=='Update'){

                $validatedData = $request->validate([
                    'name' => 'required|max:255',
                ]);
                if(City::where('name', $request->name)->where('state_id', $request->state_id)->where('id', '!=', $request->id)->get()->count()==0){
                    City::where('id', $request->id)
                        ->update([
                            'name'=>$request->name,
                            'state_id'=>$request->state_id
                        ]);

                    $data['save_success'] = "City updated successfully.";    
                }
            }
        }
        
        $data['countries']=Country::orderBy('name', 'asc')->get();
        $data['states']=State::where('country_id', 1)->orderBy('name', 'asc')->get();
        if($request->state_id){
            $data['state_id']=$request->state_id;
            $data['state']=State::where('id', $request->state_id)->first();
            $data['cities']=City::where('state_id', $request->state_id)->orderBy('name', 'asc')->get();
            if($request->id){
                $data['city']=City::where('id', $request->id)->first();
            }
        }
        
        return view('mastertables.cities')->with(['data'=>$data]);
    }//end of function

    /*
        List all the locations in database, add & edit them  
    */
    function locations(Request $request)
    {
        $data['action']='';
        $data['error']=[];
        $data['id']=$data['state_id']=0;
        if($request->btn_submit){
            if($request->btn_submit=='Add'){
                $validatedData = $request->validate([
                    'name' => 'required|max:255',
                ]);
                if(Location::where('name', $request->name)->where('state_id', $request->state_id)->get()->count()==0){
                    $stObj=new Location;
                    $stObj->name=$request->name;
                    $stObj->state_id=$request->state_id;
                    $stObj->save();
                    if($stObj->id){
                        $data['save_success'] = "Location added successfully.";
                    }
                }
            }
            elseif($request->btn_submit=='Update'){

                $validatedData = $request->validate([
                    'name' => 'required|max:255',
                ]);
                if(Location::where('name', $request->name)->where('state_id', $request->state_id)->where('id', '!=', $request->id)->get()->count()==0){
                    Location::where('id', $request->id)
                        ->update([
                            'name'=>$request->name,
                            'state_id'=>$request->state_id
                        ]);

                    $data['save_success'] = "Location updated successfully.";    
                }
            }
        }
        
        $data['countries']=Country::orderBy('name', 'asc')->get();
        $data['states']=State::where('country_id', 1)->orderBy('name', 'asc')->get();
        if($request->state_id){
            $data['state_id']=$request->state_id;
            $data['state']=State::where('id', $request->state_id)->first();
            if($request->id){
                $data['location']=Location::where('id', $request->id)->first();
            }
            $data['locations']=Location::where('isactive', 1)->where('state_id', $request->state_id)->get();
        }
        
        return view('mastertables.locations')->with(['data'=>$data]);
    }//end of function

    /*
        Ajax request to get states wise locations  
    */
    function statesWiseLocations(Request $request)
    {
        $state_ids = $request->state_ids;

        $result['locations'] = Location::whereIn('state_id',$state_ids)
                                        ->where('isactive',1)    
                                        ->get();

        return $result;

    }//end of function

    // function listLeaveAuthorities($department_id = 0)
    // {
    //     $departments = Department::where(['isactive'=>1])->select('id','name')->get();
    //     $department = Department::find($department_id);

    //     $leave_authorities = LeaveAuthority::where(['department_id'=>$department_id])
    //                           ->with(['department:id,name'])
    //                           ->with(['project:id,name'])  
    //                           ->with(['user.employee:id,user_id,fullname'])
    //                           ->get();                        

    //     return view('mastertables.list_leave_authorities')->with(['data'=>$leave_authorities,'departments'=>$departments,'department'=>$department]);
                
    // }//end of function

    // function leaveAuthorityAction($action, $leave_authority_id)
    // {
    //     if(!empty($leave_authority_id)){
    //         $leave_authority = LeaveAuthority::where(['id'=>$leave_authority_id])
    //                           ->with(['department:id,name'])
    //                           ->with(['project:id,name'])  
    //                           ->with(['user.employee:id,user_id,fullname'])
    //                           ->first();
    //     }

    //     if($action == 'add'){
    //         $data['action'] = $action;
    //         $data['departments'] = Department::where(['isactive'=>1])->select('id','name')->get();
    //         $data['projects'] = Project::where(['isactive'=>1,'approval_status'=>'1'])->select('id','name')->get();
    //         $data['employees'] = Employee::where(['isactive'=>1,'approval_status'=>'1'])->select('user_id','fullname')->get();

    //         return view('mastertables.add_leave_authorities_form')->with(['data'=>$data]);

    //     }elseif($action == 'edit'){
    //         $data['action'] = $action;

    //     }

    // }//end of function

    function checkUniqueLeaveAuthority(Request $request)
    {
        $check_data = [
                        'department_id' => 0,
                        'project_id' => 0,
                        'priority' => '0',
                        'sub_level' => '1',
                        'isactive' => 1
                     ];

        if(!empty($request->departmentId)){
            $check_data['department_id'] = $request->departmentId;
        }

        if(!empty($request->projectId)){
            $check_data['project_id'] = $request->projectId;
        }    

        if(!empty($request->priority)){
            $check_data['priority'] = $request->priority;
        }    

        $main_authority = LeaveAuthority::where($check_data)->first();
        $check_data['sub_level'] = '2';
        $sub_authority = LeaveAuthority::where($check_data)->first();

        if(!empty($main_authority) && empty($sub_authority)){
            $result['allow_submit'] = "no";
            $result['message'] = '1';    //only main authority exists

        }elseif(!empty($main_authority) && !empty($sub_authority)){
            $result['allow_submit'] = "no";
            $result['message'] = '2';  //both main & sub authority exists

        }elseif(empty($main_authority)){
            $result['allow_submit'] = "yes";

        }

        return $result;   

    }//end of function

}//end of class
