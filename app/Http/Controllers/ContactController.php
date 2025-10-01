<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\ApiBaseController;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail as FacadesMail;
use Illuminate\Support\Facades\Validator;
use Mail;

class ContactController extends ApiBaseController
{
    public function contactUs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        try {
            $contactMessage = ContactMessage::create($request->all());
            $this->sendEmailToAdmin($contactMessage);
            return $this->sendSuccess($contactMessage, 'Message sent successfully!');

        } catch (\Exception $e) {
            Log::error('ContactController.contactUs() error: ' . $e->getMessage());
            return $this->sendError("Internal server error, please try again");
        }
    }

    private function sendEmailToAdmin(ContactMessage $contactMessage)
    {
        $adminEmail = $this->getAdminEmail();

        if (!$adminEmail) {
            Log::info('No admin email found, skipping email notification for contact message: ' . $contactMessage->id);
            return;
        }

        try {
            FacadesMail::to($adminEmail)->send(new \App\Mail\ContactMessageReceived($contactMessage));
            Log::info('Contact message email sent successfully to: ' . $adminEmail);
        } catch (\Exception $e) {
            Log::error('Failed to send contact message email: ' . $e->getMessage());
            // Don't throw the error, just log it so the contact form still works
        }
    }

    private function getAdminEmail()
    {
        // First, try to get admin email from config
        $adminEmail = config('mail.admin_email');
        if ($adminEmail) {
            return $adminEmail;
        }

        // If no admin email in config, try to find a user with admin role
        try {
            $adminUser = \App\Models\User::where('role', 'admin')->first();
            if ($adminUser && $adminUser->email) {
                return $adminUser->email;
            }
        } catch (\Exception $e) {
            Log::warning('Could not find admin user: ' . $e->getMessage());
        }

        // Fallback to mail from address from config
        $mailFromAddress = config('mail.from.address');
        if ($mailFromAddress) {
            return $mailFromAddress;
        }

        return null;
    }



}
