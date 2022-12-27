<?php

namespace YM\Userform\Helpers;
  
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail; 
class CustomHelper{ 
    public static function getUserId()
    { 
        return Auth::user();
    }   
    public static function dataResponse($httpCode, $message, $data = null)
    {
        $httpCode = $httpCode ?? 500;
        $message = $message ?? 'Something went wrong. Please try again or report to support team.';
        $success = ($httpCode == 201)?true:false;
        $data = $data ?? []; 
        $response = ['success' => $success,'message' => $message, 'data' => $data]; 
        return response()->json($response, $httpCode);
    }   
    public static function sendExceptionMail($exceptionMessage, $url)
    {
        $to = env('SUPPORT_MAIL_FROM_ADDRESS');
        if(!empty( Auth::user()->id)){
            $errorForUser = Auth::user()->firstname . " " . Auth::user()->lastname . " (" . Auth::user()->email . ")";
        }else{
            $errorForUser = 'Test User';
        }
        $exceptionLink = $url;
        Mail::send('mail.mail', ['exceptionMessage' => $exceptionMessage, 'errorForUser' => $errorForUser, 'exceptionLink' => $exceptionLink], function ($message) use ($to) {
            $message->to($to);
            $message->subject('Alert!! Error in web api');
        });
        return true;
    }  
    public static function getFormData($fileName){
        $filename = file_get_contents(public_path('formData/'.$fileName)); 
        $data = json_decode($filename,true);
        $formData = array();
        if(!empty($data)){
            $formData = $data;
        }
        return $formData;
    }
}
