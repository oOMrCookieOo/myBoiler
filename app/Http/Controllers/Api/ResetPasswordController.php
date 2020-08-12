<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\api\ResetsPasswords;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
   /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    protected function sendResetResponse(Request $request, $response)
    {
        return response(['message'=> trans($response)]);

    }

    protected function reset_form(Request $request, $token)
    {
     //   $errors = collect();
        return view('auth.passwords.reset-api')->with(compact('token'));

    }

    protected function sendResetFailedResponse(Request $request, $response)
    {
        return response(['error'=> trans($response)], 422);
    }
    
}
