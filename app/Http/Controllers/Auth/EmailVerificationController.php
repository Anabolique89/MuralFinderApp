<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Base\ApiBaseController;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EmailVerificationController extends ApiBaseController
{
    public function __invoke(Request $request)
    {
        try {
            $user = User::find($request->id);

            if ($user->markEmailAsVerified()) {
                event(new Verified($user));

                $user->email_verified_at = Carbon::now();
                $user->save();
            }

            $profile = new Profile();
            $profile->user_id = $user->id;
            $profile->save();


            // TODO: Send Activation success email
            // return $this->sendSuccess($user, "Email succesfuly verified, please login");
            // Assuming you have FRONTEND_URL set in your .env file
            return redirect()->intended(env('FRONTEND_URL') . '/login?verified=1');
        } catch (\Illuminate\Database\QueryException $e) {
            logger()->error($e->getMessage());
            return $this->sendError($e->getMessage());

        } catch (\Illuminate\Validation\ValidationException $e) {
            logger()->error($e->getMessage());
            return redirect()->intended(
                config('app.frontend_url') . '/login?verified=0'
            );



        } catch (\Exception $e) {
            // Log the error.
            logger()->error($e->getMessage());

            return redirect()->intended(
                env('FRONTEND_URL') . '/login?verified=0'
            );
        }
    }
}