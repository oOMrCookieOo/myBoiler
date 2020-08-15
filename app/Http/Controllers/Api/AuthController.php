<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Auth;
use Crypt;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use JWTAuth;
use Mail;


//use Carbon\Carbon;
//use Illuminate\Support\Facades\Storage;
//
//use Illuminate\Foundation\Auth\VerifiesEmails;
//use Illuminate\Auth\Events\Verified;


class AuthController extends Controller
{
    /**
     * @var bool
     */
    public $loginAfterSignUp = true;

    private $guard;

    public function __construct()
    {
        $this->guard = Auth::guard('api');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $input = $request->only('email', 'password');
        $token = null;

        if (!$token = JWTAuth::attempt($input)) {
            return response()->json([
                'status' => false,
                'message' => 'البريد الإلكتروني أو كلمة السر خاطئة',
            ], 401);
        }


        return response()->json([
            'status' => true,
            'token' => $token,
        ],200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function logout()
    {

        JWTAuth::parseToken()->invalidate();
        return response()->json(['status' => true, 'message' => 'تم تسجيل الخروج بنجاح '],200);
    }

    /**
     * @param RegistrationFormRequest $request
     * @return JsonResponse
     */

    public function register(Request $request)
    {

        $messages = [
            'email.required' => 'يجب عليك إدخال بريدك الإلكتروني',
            'email.regex' => ' يجب عليك إدخال بريدك الإلكتروني بشكل صحيح',
            'email.unique' => 'هذا البريد الإلكتروني محجوز من قبل ',
            'password.required' => 'يجب عليك إدخال كلمة السر الخاصة بك',
            'c_password.required'=>'يجب عليك إدخال تاكيد كلمة السر الخاصة بك',
            'c_password.same'=>'كلمه المرور لا تتوافق مع التاكيد',
        ];


        $validator = Validator::make($request->all(), [
            'name' => ['string', 'max:20'],
            'email' => 'required|string|max:255|email|regex:/(.+)@(.+)\.(.+)/i|unique:users',
            'password' => 'required|min:8', 'string',
            'c_password'=>'required|same:password'
        ], $messages);


        if ($validator->fails()) {
            $response = array($validator->messages());//kinda object
            $response = $response[0]->first();
            return response()->json(['status' => false, 'message' => $response], 400);
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();
        $user->sendApiEmailVerificationNotification();


        if ($this->loginAfterSignUp) {
            return $this->login($request);
        }

        return response()->json([
            'status' => true,
            'data' => $user
        ], 200);
    }

    public function GetUsers()
    {
        return response()->json([
            "status"=>true,
            "data"=>User::all()
        ],200) ;
    }

}
