<?php

namespace App\Http\Controllers;

use App\Models\Counsellor;
use App\Traits\AuthTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Testing\Fluent\Concerns\Has;
use Illuminate\Validation\Rules\Password;

class CounsellorController extends Controller
{
    use AuthTrait;
    /**
     * CounsellorController constructor.
     */
    public function __construct()
    {
        $this->middleware(['assign.guard:patients','assign.guard:counsellors',  'jwt.auth'])->except(['store','login','logout']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        try {
            $counsellors = Counsellor::where('is_active',1)->get();
            if (!empty($counsellors)){
                $response = ['status'=>'success','data'=>$counsellors];
                $code = 200;
            }else{
                $response = ['status'=>'error','message'=>'No Record Found'];
                $code = 404;
            }
            return response()->json($response,$code);
        }catch (\Exception $e){
            return parent::manageException($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        try{
            $validator=Validator::make($request->all(),[
                'name'  => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:counsellors'],
                'password'=> ['required', 'confirmed',Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised()],
            ]);
            if($validator->fails()){
                return response()->json(['status'=>'error','errors'=>$validator->errors()->all()],400);
            }else{
                $data = $request->all();
                $data['password'] = Hash::make($data['password']);
                $counsellor = Counsellor::create($data);
                if ($counsellor){
                    $token = auth('counsellors')->attempt(['email'=>$counsellor->email,'password'=>$request->password]);
                    $accessToken = [
                        'access_token' => $token,
                        'token_type' => 'bearer',
                        'expires_in' => auth('counsellors')->factory()->getTTL() * 60
                    ];
                    return response()->json(['status'=>'success','message'=>'Counsellor Created Successfully','data'=>$counsellor,'authorization'=>$accessToken]);
                }else{
                    return response()->json(['status'=>'error','message'=>'Something went wrong!']);
                }
            }
        }catch (\Exception $e){
            return parent::manageException($e);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        try {
            $counsellor = Counsellor::find($id);
            if (!empty($counsellor)){
                $response = ['status'=>'success','data'=>$counsellor];
                $code = 200;
            }else{
                $response = ['status'=>'error','message'=>'No Record Found'];
                $code = 404;
            }
            return response()->json($response,$code);
        }catch (\Exception $e){
            return parent::manageException($e);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        try{
            $validator=Validator::make($request->all(),[
                'name'  => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:counsellors,email,'.$id],
                'password'=> ['nullable', 'string', 'min:8', 'confirmed'],
                'status' => ['required']
            ]);
            if($validator->fails()){
                return response()->json(['status'=>'error','errors'=>$validator->errors()->all()],400);
            }else{
                $counsellor = Counsellor::findOrFail($id);
                $counsellor->name = $request->input('name');
                $counsellor->email = $request->input('email');
                if (!empty($request->input('password'))){
                    $counsellor->password = Hash::make($request->input('password'));
                }
                $counsellor->is_active = $request->input('status');
                if ($counsellor->save()){
                    return response()->json(['status'=>'success','message'=>'Counsellor Updated Successfully','data'=>$counsellor]);
                }else{
                    return response()->json(['status'=>'error','message'=>'Something went wrong!']);
                }
            }
        }catch (\Exception $e){
            return parent::manageException($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        try {
            $counsellor = Counsellor::find($id);
            if (!empty($counsellor)){
                $counsellor->delete();
                $response = ['status'=>'success','message'=>'Counsellor deleted successfully'];
                $code = 200;
            }else{
                $response = ['status'=>'error','message'=>'No Record Found'];
                $code = 404;
            }
            return response()->json($response,$code);
        }catch (\Exception $e){
            return parent::manageException($e);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator=Validator::make($request->all(),[
                'email' => ['required', 'string', 'email'],
                'password'=> ['required', 'string', 'min:8'],
            ]);
            if($validator->fails()){
                return response()->json(['status'=>'error','errors'=>$validator->errors()->all()],400);
            }else{
                $credentials = $request->only('email','password');
                $credentials = array_merge($credentials,['is_active'=>true]);
                if (! $token = auth('counsellors')->attempt($credentials)) {
                    return response()->json(['error' => 'Unauthorized! Email or Password is wrong'], 401);
                }

                return $this->respondWithToken($token,'counsellors');
            }
        }catch (\Exception $e){
            return parent::manageException($e);
        }
    }
}
