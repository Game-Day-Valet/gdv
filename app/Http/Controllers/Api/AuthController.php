<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\PasswordResetRequest;
use App\Http\Requests\PasswordResetConfirmRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyResetCodeRequest;
use App\Mail\PasswordResetEmail;
use App\Mail\VerifyEmailOTP;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\PasswordResetToken;
use App\Models\ReferralCode;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Resources\UserResource;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    protected $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'address' => $request->input('address') ?? null,
        ]);

        $otp = rand(100000, 999999);

        DB::table('email_verifications')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'otp_code' => $otp,
                'expires_at' => now()->addMinutes(60),
                'updated_at' => now(),
            ]
        );

        Mail::to($user->email)->send(new VerifyEmailOTP($user, $otp));

        $user->assignRole(Role::USER->value);

        $referralCode = $request->input('referral_code');
        if ($referralCode) {
            $this->referralService->trackReferral($referralCode, $user->id);
        }

        $generatedCode = $this->referralService->generateCode($user->id);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful. Please verify your email.',
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function getReferralCode(Request $request)
    {
        $user = $request->user();
        $code = ReferralCode::where('user_id', $user->id)->latest()->value('code');
        $code = $code ?: $this->referralService->generateCode($user->id);
        return response()->json(['referral_code' => $code, 'link' => route('register.referal', ['referralCode' => $code])]);
    }

    public function validateToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $token = $request->input('token');
        try {
            $user = \Laravel\Sanctum\PersonalAccessToken::findToken($token)?->tokenable;
            if ($user) {
                return response()->json(['valid' => true]);
            }
            return response()->json(['valid' => false]);
        } catch (\Throwable $e) {
            return response()->json(['valid' => false]);
        }
    }

    public function checkReferralCode(Request $request)
    {
        $request->validate([
            'referral_code' => 'required|string',
        ]);

        $referralCode = $request->input('referral_code');
        $referral = ReferralCode::where('code', $referralCode)->first();

        if ($referral) {
            return response()->json(['message' => 'Referral code is valid', 'is_valid' => true], 200);
        }

        return response()->json(['message' => 'Invalid referral code', 'is_valid' => false], 400);
    }

    public function previewVerifyEmail()
    {
        // Create a dummy user for preview
        $user = new \stdClass();
        $user->name = 'John Doe';
        $otp = '123456';

        return view('emails.verify', [
            'name' => $user->name,
            'otp' => $otp,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email is already verified.'], 409);
        }

        $record = DB::table('email_verifications')
            ->where('user_id', $user->id)
            ->where('otp_code', $request->input('otp'))
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        $user->markEmailAsVerified();
        DB::table('email_verifications')->where('user_id', $user->id)->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Email verified successfully.',
            'token' => $token,
            'user' => new UserResource($user)
        ]);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->input('email'))->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'credentials' => ['Invalid email or password.'],
            ]);
        }

        // 🚫 Email not verified
        if (!$user->hasVerifiedEmail()) {
            $otp = rand(100000, 999999);

            DB::table('email_verifications')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'otp_code' => $otp,
                    'expires_at' => now()->addMinutes(60),
                    'updated_at' => now(),
                ]
            );

            Mail::to($user->email)->send(new VerifyEmailOTP($user, $otp));

            throw ValidationException::withMessages([
                'email' => ['Your email is not verified. A new OTP has been sent.'],
            ]);
        }

        // Store FCM token if provided
        if ($request->has('fcm_token')) {
            $user->update(['fcm_token' => $request->input('fcm_token')]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => new UserResource($user),
            'token' => $token,
            'fcm_token' => $user->fcm_token,
        ], 200);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        return response()->json([
            'message' => 'User details retrieved successfully',
            'user' => new UserResource($user),
            'token' => $request->bearerToken(),
            'fcm_token' => $user->fcm_token,
        ], 200);
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Revoke all tokens first
        try {
            $user->tokens()->delete();
        } catch (\Throwable $e) {
            // proceed even if token revocation fails
        }

        // Clean up simple relations if applicable
        if (method_exists($user, 'cart_items')) {
            $user->cart_items()->delete();
        }

        // Delete the user account
        $user->delete();

        return response()->json(['message' => 'Account deleted successfully'], 200);
    }

    public function passwordResetRequest(PasswordResetRequest $request)
    {
        $user = User::where('email', $request->input('email'))->first();
        $code = rand(100000, 999999);

        PasswordResetToken::updateOrCreate(
            ['email' => $user->email],
            [
                'token' => $code,
                'created_at' => now(),
            ]
        );

        Mail::to($user->email)->send(new PasswordResetEmail($user, $code));

        return response()->json([
            'message' => 'Password reset code sent to your email.',
        ], 200);
    }

    // public function passwordResetConfirm(PasswordResetConfirmRequest $request)
    // {
    //     $token = PasswordResetToken::where('email', $request->input('email'))
    //         ->where('token', $request->input('code'))
    //         ->where('created_at', '>=', now()->subMinutes(60))
    //         ->first();

    //     if (!$token) {
    //         throw ValidationException::withMessages([
    //             'code' => ['The reset code is invalid or has expired.'],
    //         ]);
    //     }

    //     $user = User::where('email', $request->input('email'))->first();
    //     $user->password = Hash::make($request->input('password'));
    //     $user->save();

    //     PasswordResetToken::where('email', $request->input('email'))->delete();

    //     return response()->json([
    //         'message' => 'Password reset successfully.',
    //     ], 200);
    // }

    public function verifyResetCode(VerifyResetCodeRequest $request)
    {
        $token = PasswordResetToken::where('email', $request->input('email'))
            ->where('token', $request->input('code'))
            ->where('created_at', '>=', now()->subMinutes(60))
            ->first();

        if (!$token) {
            throw ValidationException::withMessages([
                'code' => ['The reset code is invalid or has expired.'],
            ]);
        }

        return response()->json([
            'message' => 'Reset code verified successfully.',
        ], 200);
    }

    /**
     * Reset the password after code verification
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $token = PasswordResetToken::where('email', $request->input('email'))
            ->where('token', $request->input('code'))
            ->where('created_at', '>=', now()->subMinutes(60))
            ->first();

        if (!$token) {
            throw ValidationException::withMessages([
                'code' => ['The reset code is invalid or has expired.'],
            ]);
        }

        $user = User::where('email', $request->input('email'))->first();
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        PasswordResetToken::where('email', $request->input('email'))->delete();

        return response()->json([
            'message' => 'Password reset successfully.',
        ], 200);
    }

    public function googleLogin(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
            'fcm_token' => 'nullable|string',
        ]);

        $clientId = config('services.google.client_id');
        $idToken = $request->input('id_token');

        $payload = $this->verifyGoogleIdToken($idToken, $clientId);

        $email = $payload['email'];
        $name = $payload['name'] ?? 'No Name';
        $googleId = $payload['sub'];

        $user = User::where('email', $email)->first();
        $isNewUser = false;

        if (!$user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'google_id' => $googleId,
                'password' => Hash::make(Str::random(20)),
                'email_verified_at' => now(),
            ]);

            $user->assignRole(Role::USER->value);

            $generatedCode = $this->referralService->generateCode($user->id);
            $user->save();

            $isNewUser = true;
        } else {
            if (!$user->google_id) {
                $user->google_id = $googleId;
                $user->save();
            }
        }

        // Store FCM token if provided
        if ($request->has('fcm_token')) {
            $user->update(['fcm_token' => $request->input('fcm_token')]);
        }
        Log::info($user);

        $token = $user->createToken('google')->plainTextToken;

        return response()->json([
            'message' => $isNewUser
                ? 'Google login successful.'
                : 'Google login successful.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token,
            'fcm_token' => $user->fcm_token,
        ]);
    }



    public function verifyGoogleIdToken(string $idToken, string $clientId): array
    {
        $jwk = cache()->remember('google_jwk_raw', now()->addHours(24), function () {
            $jwkUrl = 'https://www.googleapis.com/oauth2/v3/certs';
            return Http::get($jwkUrl)->json();
        });

        $keys = JWK::parseKeySet($jwk);

        try {
            $decoded = JWT::decode($idToken, $keys);
            $payload = (array) $decoded;

            if ($payload['aud'] !== $clientId) {
                throw new \Exception('Invalid audience');
            }

            if (!in_array($payload['iss'], ['https://accounts.google.com', 'accounts.google.com'])) {
                throw new \Exception('Invalid issuer');
            }

            return $payload;
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'google' => ['Google Verification Failed.'],
            ]);
        }
    }

    public function appleSignIn(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
            'fcm_token' => 'nullable|string',
        ]);


        $clientId = config('services.apple.client_id');
        $idToken = $request->input('id_token');

        $payload = $this->verifyAppleIdToken($idToken, $clientId);

        $email = $payload['email'] ?? null;
        $name = $payload['name'] ?? 'No Name'; // Apple doesn't always provide name
        $appleId = $payload['sub'];

        // Try to find by email first (if present), else by apple_id
        $user = $email ? User::where('email', $email)->first() : User::where('apple_id', $appleId)->first();
        $isNewUser = false;

        if (!$user && $email) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'apple_id' => $appleId,
                'password' => Hash::make(Str::random(20)),
                'email_verified_at' => now(),
            ]);

            $user->assignRole(Role::USER->value);

            $generatedCode = $this->referralService->generateCode($user->id);
            $user->save();

            $isNewUser = true;
        } elseif ($user && !$user->apple_id) {
            $user->apple_id = $appleId;
            $user->save();
        } elseif (!$user) {
            // If Apple did not share email, create a placeholder email to satisfy DB constraints
            $placeholderEmail = 'apple_' . $appleId . '@apple.local';
            $user = User::create([
                'name' => $name,
                'email' => $placeholderEmail,
                'apple_id' => $appleId,
                'password' => Hash::make(Str::random(20)),
                'email_verified_at' => now(),
            ]);

            $user->assignRole(Role::USER->value);

            $generatedCode = $this->referralService->generateCode($user->id);
            $user->save();

            $isNewUser = true;
        }

        // Store FCM token if provided
        if ($request->has('fcm_token')) {
            $user->update(['fcm_token' => $request->input('fcm_token')]);
        }
        Log::info($user);

        $token = $user->createToken('apple')->plainTextToken;

        return response()->json([
            'message' => 'Apple login successful.',
            'user' => new UserResource($user),
            'token' => $token,
            'fcm_token' => $user->fcm_token,
        ]);
    }

    public function verifyAppleIdToken(string $idToken, string $clientId): array
    {
        $jwk = cache()->remember('apple_jwk_raw', now()->addHours(24), function () {
            $jwkUrl = 'https://appleid.apple.com/auth/keys';
            return Http::get($jwkUrl)->json();
        });

        $keys = JWK::parseKeySet($jwk);

        try {
            // Apple uses RS256
            $decoded = JWT::decode($idToken, $keys);

            $payload = (array) $decoded;

            // aud can be array or string depending on app setup; support both
            $aud = $payload['aud'] ?? null;
            $audValid = is_array($aud) ? in_array($clientId, $aud, true) : ($aud === $clientId);
            if (!$audValid) {
                throw new \Exception('Invalid audience');
            }

            if ($payload['iss'] !== 'https://appleid.apple.com') {
                throw new \Exception('Invalid issuer');
            }

            return $payload;
        } catch (\Throwable $e) {
            Log::error('Apple sign-in verification failed', [
                'error' => $e->getMessage(),
            ]);
            throw ValidationException::withMessages([
                'apple' => ['Apple Verification Failed.'],
            ]);
        }
    }

    public function index(Request $request)
    {
        $limit = $request->query('limit', 15);
        $users = User::paginate($limit);

        return response()->json($users);
    }
}
