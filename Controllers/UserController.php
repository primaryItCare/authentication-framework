<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;  
use App\Models\StateModel; 
use App\Models\CountryModel; 
use App\Helpers\Helper; 
use Illuminate\Support\Facades\Mail; 
use DB; 
use Validator;  
use Auth; 
use Hash;  

class UserController extends Controller
{
    public function index()
    { 
        $formData = Helper::getFormData('user.txt');
        return view('admin.user.list',compact('formData'));
    } 
    public function create(Request $request)
    {
        if($request->ajax()){
            try{
                $httpCode = 500;
                $isView = false;                 
                $user = []; 
                $country = CountryModel::get()->pluck('country_name','id');  
                $formData = Helper::getFormData('user.txt');
                return view('admin.user.form',compact('user','country','isView','formData'));
            }catch(Exception $e){
                Helper::sendExceptionMail($e->getMessage(), $request->url());
            }  
        }else{
           return abort(404);
        }
    }
    public function getAjaxList(\App\Http\Requests\FilterList $request){  
        if($request->ajax()){
            try {   
                $recordSet = User::where('user_id',Auth::user()->id);
                if (!empty($request->search['value'])) { 
                    $search = $request->search['value'];
                    $recordSet->where('form_filed','LIKE','%'.$search.'%');
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
                $users = $recordSet->offset($request->start)->limit($request->length)->get(); 
                $data = [];   
                $formData = Helper::getFormData('user.txt');
                foreach ($users as $key => $user) { 
                    $action = ''; 
                    $action .= '<i class="pointer userEdit" data-id="'.encrypt($user->id).'" title="Edit"><img src="'.asset('assets/image/edit.svg').'" alt="Edit"></i>';
                    $action .= '<i class="pointer userView" data-id="'.encrypt($user->id).'" title="View"><img src="'.asset('assets/image/icon-eye-green.svg').'" alt="View"></i>';
                    $action .= '<i class="pointer userDelete" data-id="'.encrypt($user->id).'" title="Delete"><img src="'.asset('assets/image/delete.svg').'" alt="Delete"></i>';  

                    $usrFiled = [];
                    if(!empty($user->form_filed)){
                        $usrFiled = json_decode($user->form_filed);

                    }

                    foreach($formData as $fkey=>$item){
                        $data[$key]['No'] = $key+1;
                        if($item['is_listing']){ 
                            if($fkey == 'country'){
                                $value = CountryModel::where('id',$usrFiled->$fkey)->first()->country_name;
                            }else if($fkey == 'name'){
                                $value = $user->fullname;
                            }else if($fkey == 'state'){
                                $value = StateModel::where('id',$usrFiled->$fkey)->first()->name;
                            }else{
                                $value = $usrFiled->$fkey ?? '';
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
                Helper::sendExceptionMail($e->getMessage(), $request->url()); 
            }  
        }else{
           return abort(404);
        } 
    }
 
    public function userStore(Request $request)
    { 
        if($request->ajax()){
            $httpCode = null;
            $message = null;
            $data = $request->all();  
            $validator = $this->validator($data); 
            if ($validator->fails())
                return Helper::dataResponse(200, $validator->errors()->first()); 
            $isUpdate = false;
            if($request->input('g-recaptcha-response') && !empty($request->input('g-recaptcha-response'))){ 
                $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.env('CAPTCHA_SECRET').'&response='.$request->input('g-recaptcha-response'));  
                $responseData = json_decode($verifyResponse);  
                if($responseData->success){ 
                    if(!empty($data['user_id'])){ 
                        $isUpdate = true;
                        $httpCode = 201;
                        $message = "User update successfully.";
                    }else{ 
                        $httpCode = 201;
                        $message = "User create successfully.";
                    }
                    $data = $this->addUserDb($data,$isUpdate);
                }else{
                    $httpCode = 200;
                    $message = 'Robot verification failed, please try again.';
                }
            }else{
                $httpCode = 200;
                $message = 'Please check the reCAPTCHA checkbox.';
            }
            return Helper::dataResponse($httpCode, $message,$data);  
        }else{
           return abort(404);
        }
    }

    public function addUserDb($data,$isUpdate){
        try{ 
            DB::beginTransaction();
            $id = '';
            if($isUpdate){ 
                $id = $data['user_id'];
                $user = User::where('id',$id)->first();  
            }else{  
                $user = new User();
                $user->user_id = Auth::user()->id;
                $user->password = !empty($data['password'])?bcrypt($data['password']):'0';
            }
            $user->first_name = $data['first_name'] ?? '';
            $user->last_name = $data['last_name'] ?? '';
            $user->email = $data['email'] ?? '';
            $user->mobile_no = $data['mobile_no'] ?? '';
            $user->country_id = $data['country'] ?? '';
            $user->state_id = $data['state'] ?? '';
            $user->form_filed = json_encode($data);
            $user->save();
            if(empty($id)){
                $user->assignRole('Organization');
                $to = $data['email'];
                Mail::send('mail.create_account', ['user' => $user], function ($message) use ($to) {
                    $message->to($to);
                    $message->subject('Welcome to '.env('APP_NAME'));
                });
            } 
            DB::commit();
            return true;
        }catch(Exception $e){
            DB::rollback();
            Helper::sendExceptionMail($e->getMessage(), $request->url());
        }
    }  
    public function edit(Request $request,$id)
    {
        if($request->ajax()){
            try{ 
                $isView = false;
                $user = User::where('id',decrypt($id))->first(); 
                $country = CountryModel::get()->pluck('country_name','id');    
                $formData = Helper::getFormData('user.txt');
                return view('admin.user.form',compact('user','isView','country','formData'));
            }catch(Exception $e){
                Helper::sendExceptionMail($e->getMessage(), $request->url());
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
                $user = User::where('id',decrypt($id))->first();     
                $formData = Helper::getFormData('user.txt');
                $country = CountryModel::get()->pluck('country_name','id');  
                return view('admin.user.form',compact('user','isView','country','formData')); 
            }catch(Exception $e){
                Helper::sendExceptionMail($e->getMessage(), $request->url());
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
                    return Helper::dataResponse(200, $validator->errors()->first()); 
                } 
                $id = $request->input('id');  
                $organization = User::where('id',decrypt($id))->first(); 
                if($organization->id){
                    $organization->delete();
                    $httpCode = 201;
                    $message = 'Organization Delete successfully';
                }
                DB::commit();
                return Helper::dataResponse($httpCode, $message);
            }catch(Exception $e){
                DB::rollback();
                Helper::sendExceptionMail($e->getMessage(), $request->url());
            }  
        }else{
           return abort(404);
        } 
    }
    protected function validator(array $data){
        $message = []; 
        $emailUnique = 'unique:App\Models\User,email';
        if($data['user_id']){
            $emailUnique ='unique:App\Models\User,email,' . $data['user_id'];
        }
         $rules = [ 
            'email'   =>  'required|max:255|email|' . $emailUnique, 
        ]; 
        return Validator::make($data, $rules, $message);
    }  
    public function checkUserEmail(Request $request){  
        try {  
            $email = $request->input('email');
            $id = $request->input('id');
            $httpCode = 500;
            $message = '';   
            $recordSet = User::where('email', 'like', '%' . $email . '%');
            if(!empty($id)){
                $recordSet->where('id','!=',$id);  
            }
            $allredyName = $recordSet->count();  
            $httpCode = 201;
            $user['success'] = false;
            if($allredyName){
                $user['success'] = true;
                $user['message'] = 'Please enter different email id.';
            }
            return Helper::dataResponse($httpCode, $message,$user);
        } catch (\Exception $e) { 
            return response()->json([
                'message' => $e->getMessage(),
            ]); 
        }  
    }
    protected function validatorId(array $data){
        $message = [];
        $rules = [
            'id' => 'required',
        ]; 
        return Validator::make($data, $rules, $message);
    } 
    public function resetPassword(Request $request){
        if($request->ajax()){
            try { 
                $httpCode = null;
                $message = null;
                $data = $request->all();  
                $validator = Validator::make($request->all(),[  
                    'password' => 'required',
                    'confirm_password' => 'required',
                ]);
                if ($validator->fails()) {
                    return Helper::dataResponse(200, $validator->errors()->first());
                }    
                $userId = $data['user_id'];
                $user = User::find(decrypt($userId));
                $user->password = bcrypt($data['password']); 
                if($user->save()) {
                    $httpCode = 201;
                    $message = "Password reset successfully.";
                }else{
                    $httpCode = 200;
                    $message = "Please try again.";
                }
                return Helper::dataResponse($httpCode, $message,$data);
            } catch (\Exception $e) {
                DB::rollback();
                Helper::sendExceptionMail($e->getMessage(), $request->url());
            }
        }else{
           return abort(404);
        } 
    } 
    public function profile(){
        try {   
            $user = Auth::user(); 
            return view('admin.user.profile',compact('user'));
        } catch (\Exception $e) { 
            Helper::sendExceptionMail($e->getMessage(), $request->url()); 
        }
    }
    public function updateProfile(Request $request){
        try {   
            $data = $request->input();
             
            if((!empty($data['old_password'])) || (!empty($data['new_password'])) || (!empty($data['confirm_password']))){

                $validator = Validator::make($request->all(),[  
                    'old_password' => 'required',
                    'new_password' => 'min:6|required_with:confirm_password|same:confirm_password', 
                    'confirm_password' => 'required|min:8',
                ]);
                if ($validator->fails()) {
                    return back()
                            ->withErrors($validator)
                            ->withInput();
                }  
            }else{                
                $validator = Validator::make($request->all(),[ 
                    'first_name' => 'required',  
                    'last_name' => 'required',  
                    'mobile_no' => 'required|numeric|digits:10',  
                ]);
                if ($validator->fails()) {
                    return back()
                            ->withErrors($validator)
                            ->withInput();
                } 
            }            
            if ($validator->fails()) {
                return back()
                        ->withErrors($validator)
                        ->withInput();
            }  
            if($request->input('g-recaptcha-response') && !empty($request->input('g-recaptcha-response'))){ 
                $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.env('CAPTCHA_SECRET').'&response='.$request->input('g-recaptcha-response'));  
                $responseData = json_decode($verifyResponse);  
                if($responseData->success){

                    $user  = User::findOrFail(auth()->user()->id); 
                    if((!empty($data['old_password'])) && (!empty($data['new_password'])) && (!empty($data['confirm_password']))){
                        if (Hash::check($request->get('old_password'), Auth::user()->password)) {
                            if($data['new_password']==$data['confirm_password']){
                                $user->password = Hash::make($data['new_password']); 
                                $user->save(); 
                                return redirect()->route('user.profile')->with('success', 'Password Update successfully');
                            } else{
                                return redirect()->route('user.profile')->with('error','New and Confirm Password Must be Same');
                            }
                        }else{
                            return redirect()->route('user.profile')->with('error','Old Password doesnt Match');
                        }
                    }else{ 
                        $user->first_name = $data['first_name'] ?? '';
                        $user->last_name = $data['last_name'] ?? '';
                        $user->email = $data['email'] ?? '';
                        $user->mobile_no = $data['mobile_no'] ?? '';
                        $user->country_id = $data['country'] ?? '';
                        $user->state_id = $data['state'] ?? '';
                        $user->form_filed = json_encode($data);
                        $user->save(); 
                        return redirect()->route('user.profile')->with('success',"Profile successfully updated.");
                    }
                }else{  
                    return redirect()->route('user.profile')->with('error','Robot verification failed, please try again.');
                }
            }else{  
                return redirect()->route('user.profile')->with('error','Please check the reCAPTCHA checkbox.');
            }
        } catch (\Exception $e) { 
            Helper::sendExceptionMail($e->getMessage(), $request->url()); 
        }
    }
    public function getAllStateByCountry(Request $request){ 
        if($request->ajax()){
            try {   
                $httpCode = 201;
                $message = 'State fetch successfully';
                $countryId = $request->input('country_id');
                $data['state'] = StateModel::where('country_id',$countryId)->get()->pluck('name','id');
                return Helper::dataResponse($httpCode, $message,$data);
            } catch (\Exception $e) { 
                Helper::sendExceptionMail($e->getMessage(), $request->url());
            }  
        }else{
           return abort(404);
        } 
    }
}
