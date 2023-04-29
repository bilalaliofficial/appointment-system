<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AppointmentController extends Controller
{
    public function __construct()
    {
        $guard = 'counsellors';
        if (auth('patients')->check()){
            $guard = 'patients';
        }
        $this->middleware(['assign.guard:'.$guard,'jwt.auth']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        try {
            $appointments = QueryBuilder::for(Appointment::class)->allowedFilters([
                    AllowedFilter::exact('patient_id'),
                    AllowedFilter::exact('counsellor_id'),
                    AllowedFilter::scope('date_after'),
                    AllowedFilter::scope('date_before'),
                ])
                ->allowedSorts(['appointment_date','created_at','updated_at'])
                ->where('is_active',1)->orderBy('id','desc')->get();
            if (!empty($appointments)){
                $response = ['status'=>'success','data'=>$appointments];
                $code = 200;
            }else{
                $response = ['status'=>'error','message'=>'No Record Found'];
                $code = 404;
            }
            return response()->json($response,$code);
        }catch (Exception $e){
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
                'patient'  => ['required'],
                'counsellor' => ['required'],
                'appointment_date'=> ['required','date_format:Y-m-d H:i:s'],
            ]);
            if($validator->fails()){
                return response()->json(['status'=>'error','errors'=>$validator->errors()->all()],400);
            }else{
                if (Appointment::where(['patient_id'=>$request->input('patient'),'is_active'=>1])->count() > 0){
                    return response()->json(['status'=>'error','message'=>'Patient can have just only one active appointment'],400);
                }
                if (Appointment::where(['counsellor_id'=>$request->input('counsellor'),'is_active'=>1])->count() > 0){
                    return response()->json(['status'=>'error','message'=>'Counsellor can have just only one active appointment'],400);
                }
                $appointment = new Appointment();
                $appointment->patient_id = $request->input('patient');
                $appointment->counsellor_id = $request->input('counsellor');
                $appointment->appointment_date = $request->input('appointment_date');
                if ($appointment->save()){
                    return response()->json(['status'=>'success','message'=>'Appointment Created Successfully','data'=>$appointment]);
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
            $appointment = Appointment::find($id);
            if (!empty($appointment)){
                $response = ['status'=>'success','data'=>$appointment];
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
                'patient'  => ['required'],
                'counsellor' => ['required'],
                'appointment_date'=> ['required','date_format:Y-m-d H:i:s'],
            ]);
            if($validator->fails()){
                return response()->json(['status'=>'error','errors'=>$validator->errors()->all()],400);
            }else{
                $appointment = Appointment::findOrFail($id);
                if (Appointment::where(['patient_id'=>$request->input('patient'),'is_active'=>1])->whereNotIn('id',[$id])->count() > 1){
                    return response()->json(['status'=>'error','message'=>'Patient can have just only one active appointment'],400);
                }
                if (Appointment::where(['counsellor_id'=>$request->input('counsellor'),'is_active'=>1])->whereNotIn('id',[$id])->count() > 1){
                    return response()->json(['status'=>'error','message'=>'Counsellor can have just only one active appointment'],400);
                }

                $appointment->patient_id = $request->input('patient');
                $appointment->counsellor_id = $request->input('counsellor');
                $appointment->appointment_date = $request->input('appointment_date');
                if ($appointment->save()){
                    return response()->json(['status'=>'success','message'=>'Appointment Updated Successfully','data'=>$appointment]);
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
     * @return Response
     */
    public function destroy($id)
    {
        try {
            $appointment = Appointment::find($id);
            if (!empty($appointment)){
                $appointment->delete();
                $response = ['status'=>'success','message'=>'Appointment deleted successfully'];
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
}
