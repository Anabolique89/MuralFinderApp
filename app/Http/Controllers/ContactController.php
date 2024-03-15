<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\ApiBaseController;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mail;

class ContactController extends ApiBaseController
{
    public function contactUs(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'content' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        try {
            $message = Message::create($request->all());
            $this->sendEmailToAdmin($message);
            return $this->sendSuccess($message, 'Message sent successfully!');

        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return $this->sendError("Internal server error, please try again".$e->getMessage());
        }



    }

    private function sendEmailToAdmin(Message $message)
    {
        $adminEmail = env('ADMIN_EMAIL');
        Mail::to($adminEmail)->send(new \App\Mail\MessageReceived($message));
    }



}
