<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\JWTException;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function manageException($exception)
    {
        if ($exception instanceof ModelNotFoundException){
            return response()->json(['status'=>'error','message'=>$exception->getMessage()],404);
        }elseif ($exception instanceof ValidationException){
            return response()->json(['status'=>'error','message'=>$exception->getMessage()],404);
        }elseif ($exception instanceof QueryException) {
            return response()->json(['status'=>'error','message'=>'Something went wrong. Please contact support!'],404);
        } elseif ($exception instanceof JWTException) {
            return response()->json(['status'=>'error','message'=>'Something went wrong. Please contact support!'], 401);
        } elseif ($exception instanceof \Exception) {
            return response()->json(['status'=>'error','message'=>$exception->getMessage()], 400);
        } else {
            return response()->json(['status'=>'error','message'=>'Something went wrong. Please contact support!'], 400);
        }
    }
}
