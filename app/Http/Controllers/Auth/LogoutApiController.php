<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Base\ApiBaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogoutApiController extends ApiBaseController
{
    public function __invoke(Request $request){

        try {
        $request->user()->currentAccessToken()->delete(); 
        return $this->sendSuccess(null, "Logout Successful");
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            return $this->sendError($e->getMessage());
        }
    }
}
