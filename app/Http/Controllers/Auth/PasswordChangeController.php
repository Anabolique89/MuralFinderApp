<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Base\ApiBaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PasswordChangeController extends ApiBaseController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);


        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        if (!Hash::check($request->current_password, $request->user()->password)) {
            return $this->sendError('Current password is incorrect', 400);
        }

        $request->user()->password = Hash::make($request->new_password);
        $request->user()->save();

        return $this->sendSuccess(null, 'Password changed successfully');


    }
}
