<?php 
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Client;

trait LoginTrait{
    private $client;
	public function __construct(){
		$this->client = Client::find(1);
    }
    
    public function issueToken(Request $request,$grantType,$scope,$user){

        $params=[
            'grant_type'=>$grantType,
            'client_id'=>$this->client->id,
            'client_secret'=>$this->client->secret,
            'scope'=>$scope,
        ];


        if($grantType!=='social'){
            $params['username']=$request->contact;
            $params['password'] = $request->password;
        }
        

        $request->request->add($params);

        // return $params;
        $token=Request::create('oauth/token','POST');


        $data = Route::dispatch($token);
        $json = (array) json_decode($data->getContent());
        if(isset($json['access_token']) && array_key_exists('access_token',$json)){
            $obj = new \stdClass;
            $obj->id=$user->id;
            $obj->name=$user->name;
            $obj->role=$user->role->role;
            $obj->contact = $user->contact;
            $obj->address = $user->address;
            $json['user']=$obj;
            $data->setContent(json_encode($json));
            return response()->json($json); 
         }
         return $data;
    }
   
}