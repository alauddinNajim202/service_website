<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Mail\OtpMail;
use App\Mail\VerificationMail;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Throwable;

class ResetPasswordController extends Controller
{
    public $select;

    public function __construct()
    {
        parent::__construct();

        $this->select = [
            'id',
            'name',
            'email',
            'avatar'
        ];
    }

    /**
     * Send password reset verification email
     */
    public function forgotPassword(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                    'code'    => 422,
                ], 422);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return Helper::jsonErrorResponse('User not found', 404);
            }

            $verificationUrl = URL::temporarySignedRoute(
                'verify.email.link',
                Carbon::now()->addHour(),
                [
                    'user' => $user->id,
                ]
            );

            Mail::to($user->email)->send(
                new VerificationMail($user, $verificationUrl)
            );

            return Helper::jsonResponse(
                true,
                'Verification link sent to your email.',
                200
            );

        } catch (Throwable $e) {

            return Helper::jsonErrorResponse(
                config('app.debug')
                    ? $e->getMessage()
                    : 'Internal server error',
                500
            );
        }
    }

    /**
     * Verify OTP and generate reset token
     */
    public function MakeOtpToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|digits:4',
        ]);

        try {

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return Helper::jsonErrorResponse('User not found', 404);
            }

            if (
                !$user->otp_expires_at ||
                Carbon::parse($user->otp_expires_at)->isPast()
            ) {
                return Helper::jsonErrorResponse(
                    'OTP has expired.',
                    400
                );
            }

            if ($user->otp !== $request->otp) {
                return Helper::jsonErrorResponse(
                    'Invalid OTP.',
                    400
                );
            }

            $token = Str::random(60);

            $user->update([
                'otp' => null,
                'otp_expires_at' => null,
                'reset_password_token' => $token,
                'reset_password_token_expire_at' => Carbon::now()->addHour(),
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'OTP verified successfully.',
                'code'    => 200,
                'token'   => $token,
            ]);

        } catch (Throwable $e) {

            return Helper::jsonErrorResponse(
                config('app.debug')
                    ? $e->getMessage()
                    : 'Internal server error',
                500
            );
        }
    }

    /**
     * Reset password using token
     */
    public function ResetPassword(Request $request)
    {
        $request->validate([
   
            'token'    => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        try {

            $user = User::where('reset_password_token', $request->token)->first();

            if (!$user) {
                return Helper::jsonErrorResponse('User not found', 404);
            }

            if (
                !empty($user->reset_password_token) &&
                $user->reset_password_token === $request->token &&
                Carbon::parse($user->reset_password_token_expire_at)->isFuture()
            ) {
                $user->password = Hash::make($request->password);
                $user->reset_password_token = null;
                $user->reset_password_token_expire_at = null;
                $user->save();

                return Helper::jsonResponse(true, 'Password reset successfully.', 200);
            }

            return Helper::jsonErrorResponse('Invalid Token', 419);
        } catch (Exception $e) {
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }


    /**
     * Verify email link and generate reset token
     */
    public function verifyEmailLink(Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired verification link.');
        }

        $user = User::findOrFail($request->user);

        $token = Str::random(60);

        $user->update([
            'reset_password_token' => $token,
            'reset_password_token_expire_at' => Carbon::now()->addHour(),
        ]);

        return redirect(
            "https://vuqia.net/auth/reset-password?token={$token}"
        );
    }
}
