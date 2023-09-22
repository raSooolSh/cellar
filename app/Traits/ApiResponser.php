<?php

namespace App\Traits;

trait ApiResponser{
    public function successResponse($data,$code,$message=null){
        return response()->json([
            'status'=>'success',
            'message'=>$message,
            'data'=>$data,
        ],$code);
    }

    public function errorResponse($message,$code){
        return response()->json([
            'status'=>'error',
            'message'=>$message,
            'data'=>[],
        ],$code);
    }
}