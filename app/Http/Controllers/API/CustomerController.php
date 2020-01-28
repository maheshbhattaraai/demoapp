<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Account;


class CustomerController extends Controller
{
    public function store(Request $request,$id){
        $this->validate($request,[
            'title'=>'required|string|max:30',
            'amount'=>'required',
            'amounttype'=>'required|in:cr,dr',
            'date'=>'required',
            'remarks'=>'required|string|max:30'
        ]);

        $accountObj = new Account;
        try{
            $accountObj->receipt_no = $request->title;
            if($request->amounttype==='dr'){
                $accountObj->dr_admin = $request->amount;
                $accountObj->cr_admin = 0;
                $accountObj->cr_user = $request->amount;
                $accountObj->dr_user = 0;
            }
            else if($request->amounttype==='cr'){
                $accountObj->dr_admin = 0;
                $accountObj->cr_admin = $request->amount;
                $accountObj->cr_user = 0;
                $accountObj->dr_user = $request->amount;
            }
            $accountObj->date = $request->date;
            $accountObj->remarks = $request->remarks;
            $accountObj->user_id = $id;
            $accountObj->save();
            return response()->json([
                'message'=>'Successfully Saved',
            ],201);

        }catch(Exception $e){
             return response()->json([
                
                'message'=>'Internal Server Error',
            ],500);
        }
    }

    public function getuserDetailForAdmin(Request $request,$id){
       $accountDetail = Account::where('user_id','=',$id)->get();
       $allData= [];
       foreach ($accountDetail as $key => $value) {
       	   $tempArray['id'] = $value->id;
           $tempArray['title'] = $value->receipt_no;
           if($value->dr_admin>0){
               $tempArray['dr_amount'] = $value->dr_admin;
               $tempArray['cr_amount'] = '0';
           }else if($value->cr_admin>0){
                $tempArray['cr_amount'] = $value->cr_admin;
                $tempArray['dr_amount'] = '0';
           }else{
             $tempArray['dr_amount'] = '0';
               $tempArray['cr_amount'] = '0';
           }
           $tempArray['date'] = $value->date;
           $tempArray['remarks'] = $value->remarks;

           $allData[] = (object)$tempArray;
       }
       return response()->json($allData,200);

    }

     public function getuserDetailForUser(Request $request){
       $accountDetail = Account::where('user_id','=',$request->user()->id)->get();
       $allData= [];
       foreach ($accountDetail as $key => $value) {
       	   $tempArray['id'] = $value->id;
           $tempArray['title'] = $value->receipt_no;
           if($value->dr_admin>0){
               $tempArray['dr_amount'] = '0';
               $tempArray['cr_amount'] = $value->dr_admin;
           }else if($value->cr_admin>0){
                $tempArray['cr_amount'] = '0';
                $tempArray['dr_amount'] = $value->cr_admin;
           }
           $tempArray['date'] = $value->date;
           $tempArray['remarks'] = $value->remarks;

           $allData[] = (object)$tempArray;
       }
       return response()->json($allData,200);
    }

    public function removeAccountData($id){
      $accountDetail = Account::find($id);
      if(!$accountDetail){
        return response()->json(['message'=>'Data Not Found'],404);
      }
      try{
        $accountDetail->delete();
        return response()->json(['message'=>'Deleted Successfully'],200);
      }catch(Exception $e){
        return response()->json(['message'=>'Internal Server Error'],500);
      }
    }

    public function update(Request $request,$id){
        $this->validate($request,[
            'title'=>'required|string|max:30',
            'amount'=>'required',
            'amounttype'=>'required|in:cr,dr',
            'date'=>'required',
            'remarks'=>'required|string|max:30'
        ]);

        $accountObj =  Account::find($id);
        if(!$accountObj){
          return response()->json(['msg'=>"Data Not Found"],404)
        }
        try{
            $accountObj->receipt_no = $request->title;
            if($request->amounttype==='dr'){
                $accountObj->dr_admin = $request->amount;
                $accountObj->cr_admin = 0;
                $accountObj->cr_user = $request->amount;
                $accountObj->dr_user = 0;
            }
            else if($request->amounttype==='cr'){
                $accountObj->dr_admin = 0;
                $accountObj->cr_admin = $request->amount;
                $accountObj->cr_user = 0;
                $accountObj->dr_user = $request->amount;
            }
            $accountObj->date = $request->date;
            $accountObj->remarks = $request->remarks;
            $accountObj->save();
            return response()->json([
                'id'=>$accountObj->id,
                'message'=>'Successfully Updated',
            ],200);

        }catch(Exception $e){
             return response()->json([
                'message'=>'Internal Server Error',
            ],500);
        }
    }
}
