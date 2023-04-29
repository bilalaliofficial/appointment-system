<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Traits\AuthTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Testing\Fluent\Concerns\Has;
use Illuminate\Validation\Rules\Password;

class PatientController extends Controller
{
    use AuthTrait;
    /**
     * PatientController constructor.
     */
    public function __construct()
    {
        $guard = 'patients';
        if (auth('counsellors')->check()){
            $guard = 'counsellors';
        }
        $this->middleware(['assign.guard:'.$guard,'jwt.auth'])->except(['store','login','logout']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        try {
            $patients = Patient::where('is_active',1)->get();
            if (!empty($patients)){
                $response = ['status'=>'success','data'=>$patients];
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
                'email' => ['required', 'string', 'email', 'max:255', 'unique:patients'],
                'password'=> ['required', 'confirmed',Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised()],
            ]);
            if($validator->fails()){
                return response()->json(['status'=>'error','errors'=>$validator->errors()->all()],400);
            }else{
                $data = $request->all();
                $data['password'] = Hash::make($data['password']);
                $patient = Patient::create($data);
                if ($patient){
                    $token = auth('patients')->attempt(['email'=>$patient->email,'password'=>$request->password]);
                    $accessToken = [
                        'access_token' => $token,
                        'token_type' => 'bearer',
                        'expires_in' => auth('patients')->factory()->getTTL() * 60
                    ];
                    return response()->json(['status'=>'success','message'=>'Patient Created Successfully','data'=>$patient,'authorization'=>$accessToken]);
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
            $patient = Patient::find($id);
            if (!empty($patient)){
                $response = ['status'=>'success','data'=>$patient];
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
                'email' => ['required', 'string', 'email', 'max:255', 'unique:patients,email,'.$id],
                'password'=> ['nullable', 'string', 'min:8', 'confirmed'],
                'status' => ['required']
            ]);
            if($validator->fails()){
                return response()->json(['status'=>'error','errors'=>$validator->errors()->all()],400);
            }else{
                $patient = Patient::findOrFail($id);
                $patient->name = $request->input('name');
                $patient->email = $request->input('email');
                if (!empty($request->input('password'))){
                    $patient->password = Hash::make($request->input('password'));
                }
                $patient->is_active = $request->input('status');
                if ($patient->save()){
                    return response()->json(['status'=>'success','message'=>'Patient Updated Successfully','data'=>$patient]);
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
            $patient = Patient::find($id);
            if (!empty($patient)){
                $patient->delete();
                $response = ['status'=>'success','message'=>'Patient deleted successfully'];
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
                if (! $token = auth('patients')->attempt($credentials)) {
                    return response()->json(['error' => 'Unauthorized! Email or Password is wrong'], 401);
                }

                return $this->respondWithToken($token,'patients');
            }
        }catch (\Exception $e){
            return parent::manageException($e);
        }
    }
}
