<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Http\Controllers\API\Logintrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\AuthenticatesUsers;


use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class UserRegisterController extends Controller
{
    use LoginTrait;
    
    public function userRegister(Request $request,$id=null){
        $this->validate($request,[
            'name'=>'required|string|max:50',
            'contact'=>'required|digits:10',
            'password'=>'required|min:6|confirmed',
        ]);
        $contactCheckDatabase = User::where('contact','=',$request->contact)->first();
        if($contactCheckDatabase){
           return response()->json(['message'=>'Contact Number Already Registered'],400);
        }
        $userObj = new User;
        $userObj->name = ucwords($request->name);
        $userObj->contact = $request->contact;
        $userObj->password = Hash::make($request->password);
        $userObj->address = $request->address;
        $userObj->status = 'active';
        if($request->user()->role->role=='superadmin'){
            $userObj->role_id = 2;

        }else{
            $userObj->role_id = 3;
            $userObj->user_id = $request->user()->id;
        }
        
        if($userObj->save()){
            return response()->json([
                'message'=>'User Created Successfully',
                'success'=>true,
                'id'=>$userObj->id,
            ],201);
        }else{
            return response()->json([
                'message' => 'Internal Server Error',
                'success' => false,
            ], 500);
        }

    }

    public function login(Request $request){
        $this->validate($request,[
            'contact'=>'required|digits:10',
            'password'=>'required|min:6',
        ]);
        $user = User::where('contact','=',$request->contact)->where('status','=','active')->first();
        if(!$user){
            return response()->json([
                'message'=>'The User Credential Were Incorrect',
                'success'=>false,
            ],401);
        }
        return $this->issueToken($request, 'password',$user->role->role,$user);
    }

     public function logout(Request $request){
        $accessToken = Auth::user()->token();
        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update(['revoked' => true]);
        $accessToken->revoke();
        return response()->json(['message'=>'Logged Out','success'=>true], 200);
    }

    
    public function customerRegister(Request $request){
       return $this->userRegister($request,$request->user()->id);
    }
    
    
    public function changePassword(Request $request){
        $user = User::find($request->user()->id);

        if (!(Hash::check($request->current_password,$request->user()->password))) {
            // The passwords matches
            return response()->json(["message"=>"Your current password does not matches with the password you provided. Please try again."],400);
        }

        if(strcmp($request->current_password, $request->new_password) == 0){
            //Current password and new password are same
            return  response()->json(["message"=>"New Password cannot be same as your current password. Please choose a different password."],400);
        }
        $this->validate($request,[
            'current_password'=>'required',
            'new_password'=>'required|string|min:6|confirmed'
        ]);

        $user->password = Hash::make($request->new_password);
        if($user->save()){
            return response()->json([
                'message'=>'Password Changed Successfully.'
            ],200);
        }else{
              return response()->json([
                'message'=>'Intental Server Error.'
            ],500);
        }


    }
    public function listOfUser(Request $request){
        $user = User::where('user_id','=',$request->user()->id)->get();
        return response()->json($user);
    }

    public function setNewPassword(Request $request){
        $this->validate($request,[
            'access_token'=>'required|string',
            'password'=>'required|string|min:6|confirmed'
        ]);
         $client = new Client();
        try{
            $res = $client->request('GET', "https://graph.accountkit.com/v1.3/me/?access_token=$request->access_token");
            $result = json_decode($res->getBody());
            $user = User::where('contact','=',$result->phone->national_number)->first();
            if(!$user){
                return response()->json(['message'=>"Account Not Found!"],400);
            }else{
                $user->password = Hash::make($request->password);
                $user->save();
                return response()->json(['message'=>"New Password Set Successfully"],200);
            }
        }catch(Exception $e){
            return response()->json(['message'=>"Something Went Wrong"],500);
        }
        
    }

    public function userVerified(Request $request){
        $client = new Client();
        try{
            $res = $client->request('GET', "https://graph.accountkit.com/v1.3/me/?access_token=$request->access_token");
            $result = json_decode($res->getBody());
            $userVerification = User::where('contact','=',$result->phone->national_number)->first();
            if(!$userVerification){
                return response()->json(['message'=>"Account Not Found!"],400);
            }else{
                return respon()->json(['message'=>"Accound Found"],200);
            }
        }catch(Exception $e){
             return response()->json(['message'=>"Server Error"],500);
        }
    }


}
