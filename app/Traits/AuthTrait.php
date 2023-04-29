<?php


namespace App\Traits;


trait AuthTrait
{
    protected function respondWithToken($token,$guard)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth($guard)->factory()->getTTL() * 60
        ]);
    }

    public function refresh($guard)
    {
        return $this->respondWithToken(auth($guard)->refresh(),$guard);
    }

    public function logout()
    {
        auth(request()->segment(2))->logout();
        return response()->json(['status'=>'success','message'=>'Successfully logged out']);
    }
}
