<?php

namespace App\Http\Controllers\RentalSystem;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PasswordResetToken;
use App\Mail\VerifyEmailOTP;
use App\Mail\PasswordResetEmail;
use App\Services\ReferralService;
use App\Enums\Role;
use App\Repositories\SportRepositoryInterface;
use App\Repositories\TournamentRepositoryInterface;
use App\Repositories\ItemRepositoryInterface;
use App\Repositories\BundleRepositoryInterface;
use App\Repositories\RentalRepositoryInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;

class RentalSystemController extends Controller
{
    protected $referralService;
    protected $sports;
    protected $tournaments;
    protected $items;
    protected $bundles;
    protected $rentals;

    public function __construct(
        ReferralService $referralService,
        SportRepositoryInterface $sports,
        TournamentRepositoryInterface $tournaments,
        ItemRepositoryInterface $items,
        BundleRepositoryInterface $bundles,
        RentalRepositoryInterface $rentals,
    ) {
        $this->referralService = $referralService;
        $this->sports = $sports;
        $this->tournaments = $tournaments;
        $this->items = $items;
        $this->bundles = $bundles;
        $this->rentals = $rentals;
    }

    public function showSignup()
    {
        return view('rentalsystem.signup');
    }

    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'referral_code' => 'nullable|string|max:255',
            'terms' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
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

            if ($request->filled('referral_code')) {
                $this->referralService->trackReferral($request->input('referral_code'), $user->id);
            }
            $this->referralService->generateCode($user->id);

            DB::commit();

            Session::put('pending_email', $user->email);
            Session::flash('info', 'We sent a 6-digit verification code to your email. Enter it below to verify your account.');
            return redirect()->route('rentalsystem.email-verification');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['email' => 'An error occurred. Please try again.'])->withInput();
        }
    }

    public function showSignin(Request $request)
    {
        if ($request->filled('redirect')) {
            Session::put('url.intended', $request->query('redirect'));
        }
        return view('rentalsystem.signin');
    }

    public function signin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::where('email', $request->input('email'))->first();
        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
        }

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

            Session::put('pending_email', $user->email);
            Session::flash('info', 'Your email is not verified. A new OTP has been sent.');
            return redirect()->route('rentalsystem.email-verification');
        }

        Auth::login($user);
        Session::flash('success', 'Welcome back!');
        $intended = Session::pull('url.intended');
        if ($intended) {
            return redirect()->to($intended);
        }
        return redirect()->route('rentalsystem.sports');
    }

    public function showEmailVerification()
    {
        return view('rentalsystem.email-verification');
    }

    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'otp' => 'required|string|digits:6',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::where('email', $request->input('email'))->first();
        if (!$user) {
            return back()->withErrors(['email' => 'User not found.'])->withInput();
        }
        if ($user->hasVerifiedEmail()) {
            Session::flash('success', 'Email already verified. Please sign in.');
            return redirect()->route('rentalsystem.signin');
        }

        $record = DB::table('email_verifications')
            ->where('user_id', $user->id)
            ->where('otp_code', $request->input('otp'))
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.'])->withInput();
        }

        $user->markEmailAsVerified();
        DB::table('email_verifications')->where('user_id', $user->id)->delete();

        Session::forget('pending_email');
        Session::flash('success', 'Email verified successfully. Please sign in to continue.');
        return redirect()->route('rentalsystem.signin');
    }

    public function showForgotPassword()
    {
        return view('rentalsystem.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::where('email', $request->input('email'))->first();
        if (!$user) {
            return back()->withErrors(['email' => 'We could not find a user with that email address.'])->withInput();
        }

        $code = rand(100000, 999999);
        PasswordResetToken::updateOrCreate(
            ['email' => $user->email],
            [
                'token' => $code,
                'created_at' => now(),
            ]
        );
        Mail::to($user->email)->send(new PasswordResetEmail($user, $code));

        Session::flash('success', 'Password reset code sent to your email.');
        return redirect()->route('rentalsystem.signin');
    }

    public function showSports()
    {
        $sports = $this->sports->getAllActive();
        // attach tournaments_count attribute for display
        if ($sports) {
            foreach ($sports as $s) {
                try {
                    $s->setAttribute('tournaments_count', method_exists($s, 'tournaments') ? $s->tournaments()->count() : 0);
                } catch (\Throwable $e) {
                    $s->setAttribute('tournaments_count', 0);
                }
            }
        }
        return view('rentalsystem.sports', compact('sports'));
    }

    public function showTournaments($sportId)
    {
        $search = request()->query('search');
        // if repository exposes sport tournaments only, we filter after; otherwise use sport->getTournamentsBySport
        $tournaments = $this->sports->getTournamentsBySport($sportId);
        if ($search) {
            $q = strtolower($search);
            $tournaments = collect($tournaments)->filter(function ($t) use ($q) {
                $name = strtolower(is_array($t) ? ($t['name'] ?? '') : ($t->name ?? ''));
                $loc = strtolower(is_array($t) ? ($t['location'] ?? '') : ($t->location ?? ''));
                return str_contains($name, $q) || str_contains($loc, $q);
            })->values();
        }
        return view('rentalsystem.tournaments', compact('tournaments', 'sportId', 'search'));
    }

    public function showTournamentDetails($tournamentId)
    {
        // Get tournament details
        $tournament = $this->tournaments->find($tournamentId);
        
        if (!$tournament) {
            abort(404, 'Tournament not found');
        }

        // Get sport information
        $sport = $this->sports->find($tournament->sport_id ?? 1);
        
        return view('rentalsystem.tournament-details', compact('tournament', 'sport'));
    }

    public function showRentalBooking($tournamentId)
    {
        $availableItems = $this->items->getAllAvailable();
        $availableBundles = $this->bundles->getAllAvailable();
        return view('rentalsystem.rental-booking', [
            'tournament' => ['id' => $tournamentId],
            'tournamentId' => $tournamentId,
            'availableItems' => $availableItems,
            'availableBundles' => $availableBundles,
        ]);
    }

    public function createRental(Request $request)
    {
        if (!Auth::check()) {
            session(['url.intended' => url()->previous()]);
            return back()->with('login_required', true);
        }

        $validator = Validator::make($request->all(), [
            'tournament_id' => 'required|integer',
            'team_name' => 'nullable|string|max:255',
            'coach_name' => 'nullable|string|max:255',
            // 'field_number' removed
            // drop_off_date/time removed
            'items' => 'nullable|array',
            'bundles' => 'nullable|array',
            'insurance_option' => 'nullable',
            'damage_waiver' => 'nullable',
            'payment_method' => 'required|in:stripe',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Additional validation: At least one item or bundle must be selected
        $itemsInput = (array) $request->input('items', []);
        $bundlesInput = (array) $request->input('bundles', []);
        
        $hasItems = false;
        $hasBundles = false;
        
        foreach ($itemsInput as $itemId => $qty) {
            if ((int) $qty > 0) {
                $hasItems = true;
                break;
            }
        }
        
        foreach ($bundlesInput as $bundleId => $qty) {
            if ((int) $qty > 0) {
                $hasBundles = true;
                break;
            }
        }
        
        if (!$hasItems && !$hasBundles) {
            return back()->withErrors(['items' => 'Please select at least one item or bundle.'])->withInput();
        }

        $user = Auth::user();

        // Normalize selections
        $itemsInput = (array) $request->input('items', []);
        $bundlesInput = (array) $request->input('bundles', []);
        $selectedItems = [];
        $selectedBundles = [];
        $itemsSubtotal = 0.0;
        $bundlesSubtotal = 0.0;

        // Process items - store as [{"item_id":"1","quantity":3}]
        foreach ($itemsInput as $itemId => $qty) {
            $quantity = max(0, (int) $qty);
            if ($quantity > 0) {
                $item = $this->items->find($itemId);
                if ($item) {
                    $price = (float) ($item->price ?? 0);
                    $itemsSubtotal += $price * $quantity;
                    $selectedItems[] = [
                        'item_id' => (string) $itemId,
                        'quantity' => $quantity
                    ];
                }
            }
        }

        // Process bundles - store as [10,4] (just IDs, no quantities)
        foreach ($bundlesInput as $bundleId => $qty) {
            $quantity = max(0, (int) $qty);
            if ($quantity > 0) {
                $bundle = $this->bundles->find($bundleId);
                if ($bundle) {
                    $price = (float) ($bundle->price ?? 0);
                    $bundlesSubtotal += $price * $quantity;
                    // Store just the bundle ID, no quantity needed
                    $selectedBundles[] = (int) $bundleId;
                }
            }
        }

        $insurance = $request->input('insurance_option');
        $insuranceAmount = 0.0;
        if (is_numeric($insurance)) {
            $insuranceAmount = (float) $insurance;
        }
        // Sum multiple waiver options if provided as damage_waiver_options[] values
        $waiverAmount = 0.0;
        foreach ((array) $request->input('damage_waiver_options', []) as $waiverVal) {
            $waiverAmount += (float) $waiverVal;
        }

        $total = $itemsSubtotal + $bundlesSubtotal + $insuranceAmount + $waiverAmount;

        $dropOffDateTime = null;

        // Create rental record with pending payment
        $rental = $this->rentals->create([
            'user_id' => $user->id,
            'tournament_id' => (int) $request->input('tournament_id'),
            'team_name' => $request->input('team_name') ?: null,
            'coach_name' => $request->input('coach_name') ?: null,
            'items' => !empty($selectedItems) ? $selectedItems : null,
            'bundles' => !empty($selectedBundles) ? $selectedBundles : null,
            'drop_off_time' => $dropOffDateTime,
            'insurance_option' => $insuranceAmount > 0 ? $insuranceAmount : null,
            'damage_waiver' => $waiverAmount > 0 ? $waiverAmount : null,
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
            'total_amount' => $total,
            'status' => 'pending',
        ]);

        // Start Stripe Checkout session
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = StripeCheckoutSession::create([
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => ['name' => 'Tournament Rental Booking'],
                        'unit_amount' => (int) round($total * 100),
                    ],
                    'quantity' => 1,
                ]],
                'success_url' => route('rentalsystem.checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('rentalsystem.checkout.cancel', ['rental' => $rental->id]),
                'metadata' => [
                    'rental_id' => (string) $rental->id,
                    'user_id' => (string) $user->id,
                ],
            ]);
            return redirect($session->url);
        } catch (\Throwable $e) {
            return redirect()->route('rentalsystem.rental-booking', $request->input('tournament_id'))
                ->withErrors(['error' => 'Stripe error: ' . $e->getMessage()]);
        }
    }

    public function checkoutSuccess(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('rentalsystem.signin');
        }
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return redirect()->route('rentalsystem.sports')->with('error', 'Missing Stripe session.');
        }
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = \Stripe\Checkout\Session::retrieve($sessionId);
            $rentalId = $session->metadata->rental_id ?? null;
            if ($session->payment_status === 'paid' && $rentalId) {
                $this->rentals->update($rentalId, ['payment_status' => 'completed', 'status' => 'confirmed']);
                return redirect()->route('rentalsystem.profile')->with('success', 'Booking confirmed. Payment completed.');
            }
            return redirect()->route('rentalsystem.profile')->with('info', 'Payment not completed yet.');
        } catch (\Throwable $e) {
            return redirect()->route('rentalsystem.profile')->with('error', 'Stripe confirmation failed: ' . $e->getMessage());
        }
    }

    public function checkoutCancel(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('rentalsystem.signin');
        }
        $rentalId = $request->query('rental');
        return redirect()->route('rentalsystem.profile')->with('info', 'Checkout canceled.' . ($rentalId ? ' Rental #' . $rentalId . ' remains pending.' : ''));
    }

    public function showProfile()
    {
        if (!Auth::check()) {
            return redirect()->route('rentalsystem.signin');
        }
        $user = Auth::user();
        $collection = $this->rentals->getByUser($user->id);
        $rentals = [];
        foreach ($collection as $r) {
            $itemsArray = [];
            $bundlesArray = [];

            // Process items - get actual item names from database
            if (is_array($r->items)) {
                foreach ($r->items as $item) {
                    if (is_array($item) && isset($item['item_id']) && isset($item['quantity'])) {
                        $itemModel = $this->items->find($item['item_id']);
                        if ($itemModel) {
                            $itemsArray[] = [
                                'name' => $itemModel->name,
                                'quantity' => (int) $item['quantity']
                            ];
                        }
                    }
                }
            }

            // Process bundles - get actual bundle names from database
            if (is_array($r->bundles)) {
                foreach ($r->bundles as $bundleId) {
                    if (is_numeric($bundleId)) {
                        $bundle = $this->bundles->find($bundleId);
                        if ($bundle) {
                            $bundlesArray[] = [
                                'name' => $bundle->name,
                                'quantity' => 1 // Bundles don't have quantities
                            ];
                        }
                    }
                }
            }

            $rentals[] = [
                'tournament_name' => optional($r->tournament)->name ?? 'Tournament',
                'status' => $r->status ?? 'pending',
                'total_amount' => $r->total_amount ?? 0,
                'created_at' => $r->created_at,
                'items' => $itemsArray,
                'bundles' => $bundlesArray,
            ];
        }
        return view('rentalsystem.profile', compact('user', 'rentals'));
    }

    public function updateProfile(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('rentalsystem.signin');
        }
        $request->validate(['name' => 'nullable|string|max:255', 'contact_number' => 'nullable|string|max:20', 'address' => 'nullable|string|max:255']);
        $user = Auth::user();
        foreach (['name', 'contact_number', 'address'] as $f) {
            if ($request->filled($f)) {
                $user->{$f} = $request->{$f};
            }
        }
        $user->save();
        Session::flash('success', 'Profile updated successfully!');
        return redirect()->route('rentalsystem.profile');
    }

    public function logout()
    {
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();
        return redirect()->route('rentalsystem.signin')->with('success', 'You have been logged out successfully.');
    }

    public function resendVerificationCode(Request $request)
    {
        $email = $request->query('email', Session::get('pending_email'));
        if (!$email) {
            return redirect()->route('rentalsystem.email-verification')->with('error', 'Email not provided');
        }
        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect()->route('rentalsystem.email-verification')->with('error', 'User not found');
        }
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
        Session::put('pending_email', $user->email);
        return redirect()->route('rentalsystem.email-verification')->with('info', 'A new verification code has been sent.');
    }

    public function googleLogin(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        $clientId = config('services.google.client_id');
        $idToken = $request->input('id_token');

        try {
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
                $this->referralService->generateCode($user->id);
                $isNewUser = true;
            } else {
                if (!$user->google_id) {
                    $user->google_id = $googleId;
                    $user->save();
                }
            }

            Auth::login($user);
            Session::flash('success', $isNewUser ? 'Welcome! Your account has been created successfully.' : 'Welcome back!');
            $intended = Session::pull('url.intended');
            if ($intended) {
                return redirect()->to($intended);
            }
            return redirect()->route('rentalsystem.sports');

        } catch (\Exception $e) {
            return back()->withErrors(['google' => 'Google authentication failed. Please try again.'])->withInput();
        }
    }

    private function verifyGoogleIdToken(string $idToken, string $clientId): array
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
}
