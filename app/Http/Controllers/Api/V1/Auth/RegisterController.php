<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Mail\UserVerificationMail;
use App\Models\User;
use App\Traits\SMS;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Str;
use Throwable;

class RegisterController extends Controller
{
    use SMS;

    public $select;

    public function __construct()
    {
        parent::__construct();
        $this->select = ['id', 'name', 'email', 'otp', 'avatar', 'otp_verified_at', 'last_activity_at'];
    }

    public function register(Request $request)
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
            ]);

            // Generate verification token
            $verificationToken = Str::random(64);

            // Create user
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'otp_verified_at' => null,
                'verification_token' => $verificationToken,
                'slug' => Str::random(8),
            ]);

            $user->assignRole('user');

            $verificationUrl = route('verify.email', [
                'token' => $user->verification_token,
            ]);

            Mail::to($user->email)
                ->send(new UserVerificationMail($user, $verificationUrl));
           

            DB::commit();

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'Registration successful. Please check your email.',
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();

            return Helper::jsonErrorResponse($e->errors(), 422, $e->getMessage());
        } catch (Throwable $e) {
            DB::rollBack();

            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }

    public function creatorRegister(Request $request)
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
                'category_id' => 'required|exists:categories,id',
                'short_bio' => 'required|string',
                'profile_identify' => 'required|file|mimes:jpg,jpeg,png|max:5120',
            ]);

            $profileIdentifyPath = null;
            if ($request->hasFile('profile_identify')) {
                $profileIdentifyPath = Helper::fileUpload(
                    $request->file('profile_identify'),
                    'creators/identify',
                    $validatedData['name'].'-'.time()
                );
            }

            $verificationToken = Str::random(64);

            // Create user
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'category_id' => $validatedData['category_id'],
                'short_bio' => $validatedData['short_bio'],
                'avatar' => $profileIdentifyPath,
                'otp_verified_at' => null,
                'verification_token' => $verificationToken,
                'slug' => Str::random(8),
            ]);

            $user->assignRole('creator');

            $verificationUrl = route('verify.email', [
                'token' => $user->verification_token,
            ]);

            Mail::to($user->email)
                ->send(new UserVerificationMail($user, $verificationUrl));

            DB::commit();

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'Creator registration successful. Please check your email.',
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();

            return Helper::jsonErrorResponse($e->errors(), 422, $e->getMessage());
        } catch (Throwable $e) {
            DB::rollBack();

            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }

    public function verifyEmail(Request $request)
    {

        $token = $request->input('token');
        try {
            $user = User::where('verification_token', $token)->first();

            if (! $user) {
                return redirect(Config('settings.frontend').'?error=invalid_token&message='.urlencode('Invalid verification token.'));
            }
            if ($user->otp_verified_at) {
                return redirect(Config('settings.frontend').'?error=already_verified&message='.urlencode('Email is already verified. You can login now.'));
            }

            DB::beginTransaction();

            try {
                $user->otp_verified_at = now();
                $user->otp = null; // clear token
                $user->save();
                DB::commit();

                return redirect(Config('settings.frontend').'?verified=true&message='.urlencode('Email verified successfully.'));
            } catch (Exception $e) {
                DB::rollBack();
                Log::error('Email verification process failed: '.$e->getMessage());

                return redirect(Config('settings.frontend').'?error=verification_failed&message='.urlencode('Verification failed. Please try again or contact support.'));
            }
        } catch (Exception $e) {
            Log::error('Email verification error: '.$e->getMessage());

            return redirect(Config('settings.frontend').'?error=server_error&message='.urlencode('Something went wrong. Please try again later.'));
        }
    }

    public function ResendOtp(Request $request)
    {

        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            $user = User::where('email', $request->input('email'))->first();

            if (! $user) {
                return Helper::jsonErrorResponse('User not found.', 404);
            }

            if ($user->otp_verified_at) {
                return Helper::jsonErrorResponse('Email already verified.', 409);
            }

            $newOtp = rand(1000, 9999);
            $otpExpiresAt = Carbon::now()->addMinutes(60);
            $user->otp = $newOtp;
            $user->otp_expires_at = $otpExpiresAt;
            $user->save();

            // * Send the new OTP to the user's email
            Mail::to($user->email)->send(new OtpMail($newOtp, $user, 'Verify Your Email Address'));

            return Helper::jsonResponse(true, 'A new OTP has been sent to your email.', 200);
        } catch (Exception $e) {
            return Helper::jsonErrorResponse($e->getMessage(), 200);
        }
    }
}
