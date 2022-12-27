<?php

namespace YM\Userform\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use YM\Userform\Models\{OrganizationModel,CountryModel,StateModel};
use App\Models\User;  
use YM\Userform\Helpers\CustomHelper; 
use DB;
use Validator;
use Illuminate\Support\Facades\Auth;

class OrganizationController extends Controller
{
    public function index(){
                $user = CustomHelper::getUserId();
                dd($user);
        $formData = CustomHelper::getFormData('organization.txt'); 
        return view('organization::organization-list',compact('formData'));
    } 
    public function create(Request $request){
        if($request->ajax()){
            try{
                $isView = false;                 
                $organization = []; 
    			$country = CountryModel::get()->pluck('country_name','id');  
                $formData = CustomHelper::getFormData('organization.txt');
                return view('organization::organization-form',compact('organization','country','isView','formData'));
            }catch(Exception $e){
                CustomHelper::sendExceptionMail($e->getMessage(), $request->url());
            }  
        }else{
           return abort(404);
        }
    }
    public function getAjaxList(\BK\Userform\Requests\FilterList $request){  
        if($request->ajax()){
            try { 
                $recordSet = OrganizationModel::where('user_id',Auth::user()->id);
                if (!empty($request->search['value'])) { 
                    $search = $request->search['value'];
                    $recordSet->where('name','LIKE','%'.$search.'%');
                    $recordSet->orWhere('form_filed','LIKE','%'.$search.'%');
                }
                $validated = $request->validated();
                if (isset($validated['order'][0])) { 
                    switch ($validated['order'][0]['column']) {
                        case '1':
                            $recordSet->orderBY('name', $validated['order'][0]['dir']);
                            break;
                    }
                }else{
                    $recordSet->orderBy('id','DESC');
                }
                $recordsTotal = $recordSet->count();
                $organizations = $recordSet->offset($request->start)->limit($request->length)->get(); 
                $formData = CustomHelper::getFormData('organization.txt');
                $data = [];  
                foreach ($organizations as $key => $organization) { 
                    $action = ''; 
                    $action .= '<i class="pointer organizationEdit" data-id="'.encrypt($organization->id).'" title="Edit"><img src="'.asset('assets/image/edit.svg').'" alt="Edit"></i>';
                    $action .= '<i class="pointer organizationView" data-id="'.encrypt($organization->id).'" title="View"><img src="'.asset('assets/image/icon-eye-green.svg').'" alt="View"></i>';
                    $action .= '<i class="pointer organizationDelete" data-id="'.encrypt($organization->id).'" title="Delete"><img src="'.asset('assets/image/delete.svg').'" alt="Delete"></i>'; 

                    $orgFiled = [];
                    if(!empty($organization->form_filed)){
                        $orgFiled = json_decode($organization->form_filed);
                    } 
                    foreach($formData as $fkey=>$item){
                        $data[$key]['No'] = $key+1;
                        if($item['is_listing']){
                            $value = $orgFiled->$fkey;
                            if($fkey == 'country'){
                                $value = CountryModel::where('id',$orgFiled->$fkey)->first()->country_name;
                            }
                            if($fkey == 'state'){
                                $value = StateModel::where('id',$orgFiled->$fkey)->first()->name;
                            }
                            $data[$key][str_replace(" ","",$item['label_name'])] = $value;
                        }
                        $data[$key]['Action'] = $action;
                    } 
                }   
                return response()->json([
                    'draw' => $request->draw,
                    'recordsTotal' => $recordsTotal,
                    'recordsFiltered' => $recordsTotal,
                    'data' => $data,
                ]);
            } catch (\Exception $e) { 
                dd($e->getMessage()); 
            }  
        }else{
           return abort(404);
        } 
    }
 
    public function organizationStore(Request $request)
    { 
        if($request->ajax()){
            $httpCode = null;
            $message = null;
            $data = $request->all();  
            $validator = $this->validator($data); 
            if ($validator->fails())
                return CustomHelper::dataResponse(200, $validator->errors()->first()); 
            $isUpdate = false;
            if($request->input('g-recaptcha-response') && !empty($request->input('g-recaptcha-response'))){ 
                $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.env('CAPTCHA_SECRET').'&response='.$request->input('g-recaptcha-response'));  
                $responseData = json_decode($verifyResponse);  
                if($responseData->success){ 
                    if(!empty($data['org_id'])){ 
                        $isUpdate = true;
                        $httpCode = 201;
                        $message = "Organization update successfully.";
                    }else{ 
                        $httpCode = 201;
                        $message = "Organization create successfully.";
                    }
                    $isAdded = $this->addOrganizationDb($data,$isUpdate);

                    if($isAdded == 'allredy_org_added'){
                        $httpCode = 200;
                        $message = 'The organization already exists and the organization admin has already been notified.';
                    }
                }else{
                    $httpCode = 200;
                    $message = 'Robot verification failed, please try again.';
                }
            }else{
                $httpCode = 200;
                $message = 'Please check the reCAPTCHA checkbox.';
            }
            return CustomHelper::dataResponse($httpCode, $message,$data);  
        }else{
           return abort(404);
        }
    }

    public function addOrganizationDb($data,$isUpdate){
        try{ 
            DB::beginTransaction(); 
            $isNewOrg = true;
            if($isNewOrg){
                if($isUpdate){
                    $organization = OrganizationModel::where('id',$data['org_id'])->first();  
                }else{  
                	$organization = new OrganizationModel();
                	$organization->user_id = Auth::user()->id;
                }
                $organization->name = $data['name'];
                $organization->form_filed = json_encode($data);
                $organization->save(); 
                DB::commit();
                return 'added';
            }else{
                return 'allredy_org_added';
            }
        }catch(Exception $e){
            DB::rollback();
            CustomHelper::sendExceptionMail($e->getMessage(), $request->url());
        }
    }  
    public function edit(Request $request,$id)
    {
        if($request->ajax()){
            try{ 
                $isView = false;
                $organization = OrganizationModel::where('id',decrypt($id))->first(); 
    			$country = CountryModel::get()->pluck('country_name','id');    
                $formData = CustomHelper::getFormData('organization.txt');
                return view('organization::organization-form',compact('organization','isView','country','formData'));
            }catch(Exception $e){
                CustomHelper::sendExceptionMail($e->getMessage(), $request->url());
            }  
        }else{
           return abort(404);
        }
    }  
    public function view(Request $request,$id)
    {
        if($request->ajax()){
            try{ 
                $isView = 'disabled';
                $organization = OrganizationModel::where('id',decrypt($id))->first(); 
                $formData = CustomHelper::getFormData('organization.txt');
    			$country = CountryModel::get()->pluck('country_name','id');  
                return view('organization::organization-form',compact('organization','isView','country','formData')); 
            }catch(Exception $e){
                CustomHelper::sendExceptionMail($e->getMessage(), $request->url());
            }  
        }else{
           return abort(404);
        }
    }   
    public function destroy(Request $request)
    {
        if($request->ajax()){
            try{ 
                DB::beginTransaction();
                $httpCode = 500;
                $message = null;
                $validator = Validator::make($request->all(),[  
                    'id' => 'required',
                ]);
                if ($validator->fails()) {
                    return CustomHelper::dataResponse(200, $validator->errors()->first()); 
                } 
                $id = $request->input('id');  
                $organization = OrganizationModel::where('id',decrypt($id))->first(); 
                if($organization->id){
                    $organization->delete();
                    $httpCode = 201;
                    $message = 'Organization Delete successfully';
                }
                DB::commit();
                return CustomHelper::dataResponse($httpCode, $message);
            }catch(Exception $e){
                DB::rollback();
                CustomHelper::sendExceptionMail($e->getMessage(), $request->url());
            }  
        }else{
           return abort(404);
        } 
    }
    protected function validator(array $data){
        $message = []; 
        $rules = [
            'name' => 'required',  
        ]; 
        return Validator::make($data, $rules, $message);
    }  
}
