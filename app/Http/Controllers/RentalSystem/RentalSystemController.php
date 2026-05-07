<?php

namespace App\Http\Controllers\RentalSystem;

use App\Events\RentalBookingCreated;
use App\Http\Controllers\Controller;
use App\Services\AirtableService;
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
use App\Models\Coupon;
use App\Repositories\SportRepositoryInterface;
use App\Repositories\TournamentRepositoryInterface;
use App\Repositories\ItemRepositoryInterface;
use App\Repositories\BundleRepositoryInterface;
use App\Repositories\RentalRepositoryInterface;
use App\Repositories\PrivacyPolicyRepositoryInterface;
use App\Repositories\TermsConditionRepositoryInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RentalSystemController extends Controller
{
    protected $referralService;
    protected $sports;
    protected $tournaments;
    protected $items;
    protected $bundles;
    protected $rentals;
    protected $privacyPolicies;
    protected $termsConditions;
    protected $airtable;


    public function __construct(
        ReferralService $referralService,
        SportRepositoryInterface $sports,
        TournamentRepositoryInterface $tournaments,
        ItemRepositoryInterface $items,

        BundleRepositoryInterface $bundles,
        RentalRepositoryInterface $rentals,
        PrivacyPolicyRepositoryInterface $privacyPolicies,
        TermsConditionRepositoryInterface $termsConditions,
        AirtableService $airtable
    ) {
        $this->referralService = $referralService;
        $this->sports = $sports;
        $this->tournaments = $tournaments;
        $this->items = $items;
        $this->bundles = $bundles;
        $this->rentals = $rentals;
        $this->privacyPolicies = $privacyPolicies;
        $this->termsConditions = $termsConditions;
        $this->airtable = $airtable;
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
            'terms' => 'nullable|accepted',
            'sms_consent' => 'nullable|accepted',
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
                'sms_consent' => (bool) $request->boolean('sms_consent'),
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
        if (Auth::check()) {
            // Already signed in on rental site; do not show sign-in page again
            $intended = Session::pull('url.intended');
            if ($intended) {
                return redirect()->to($intended);
            }
            return redirect()->route('rentalsystem.sports')->with('info', 'You are already signed in.');
        }
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

        Session::put('reset_email', $user->email);
        Session::flash('success', 'Password reset code sent to your email. Enter the code to continue.');
        return redirect()->route('rentalsystem.reset-password.code', ['email' => $user->email]);
    }

    public function showSports()
    {
        $sports = $this->sports->getAllActive();
        // attach tournaments_count attribute for display
        if ($sports) {
            foreach ($sports as $s) {
                try {
                    // Count only ACTIVE tournaments for this sport
                    if (method_exists($s, 'tournaments')) {
                        $count = $s->tournaments()->where('status', \App\Enums\TournamentStatus::ACTIVE->value)->count();
                        $s->setAttribute('tournaments_count', $count);
                    } else {
                        // Fallback via repository if relation not present
                        $all = $this->sports->getTournamentsBySport($s->id);
                        $count = collect($all)->filter(function ($t) {
                            $status = is_array($t) ? ($t['status'] ?? null) : ($t->status ?? null);
                            return $status === \App\Enums\TournamentStatus::ACTIVE->value || $status === 'active' || $status === 1 || $status === true || $status === 'ACTIVE';
                        })->count();
                        $s->setAttribute('tournaments_count', $count);
                    }
                } catch (\Throwable $e) {
                    $s->setAttribute('tournaments_count', 0);
                }
            }
        }
        return view('rentalsystem.sports', compact('sports'));
    }

    public function showResetCode(Request $request)
    {
        $email = $request->query('email', Session::get('reset_email'));
        if (!$email) {
            return redirect()->route('rentalsystem.forgot-password')->with('error', 'Please enter your email to receive the reset code.');
        }
        return view('rentalsystem.reset-code', ['email' => $email]);
    }

    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'code' => 'required|string|digits:6',
        ]);

        $email = $request->input('email');
        $code = $request->input('code');

        $record = PasswordResetToken::where('email', $email)->where('token', $code)->first();
        if (!$record) {
            return back()->withErrors(['code' => 'Invalid code.'])->withInput();
        }
        // Token valid for 60 minutes
        if (\Carbon\Carbon::parse($record->created_at)->lt(now()->subMinutes(60))) {
            return back()->withErrors(['code' => 'Code has expired. Please request a new one.'])->withInput();
        }

        Session::put('reset_email', $email);
        Session::put('reset_verified', true);
        return redirect()->route('rentalsystem.reset-password.new');
    }

    public function showResetPassword()
    {
        $email = Session::get('reset_email');
        $verified = Session::get('reset_verified');
        if (!$email || !$verified) {
            return redirect()->route('rentalsystem.forgot-password')->with('error', 'Please verify the code sent to your email first.');
        }
        return view('rentalsystem.reset-password', ['email' => $email]);
    }

    public function privacyPolicy()
    {
        $policies = collect($this->privacyPolicies->getAll() ?? [])->filter(function ($it) {
            return (bool) (is_array($it) ? ($it['status'] ?? true) : ($it->status ?? true)) === true;
        });
        return view('rentalsystem.privacy-policy', ['policies' => $policies]);
    }

    public function terms()
    {
        $terms = collect($this->termsConditions->getAll() ?? [])->filter(function ($it) {
            return (bool) (is_array($it) ? ($it['status'] ?? true) : ($it->status ?? true)) === true;
        });
        return view('rentalsystem.terms', ['terms' => $terms]);
    }

    public function resetPassword(Request $request)
    {
        $email = Session::get('reset_email');
        $verified = Session::get('reset_verified');
        if (!$email || !$verified) {
            return redirect()->route('rentalsystem.forgot-password')->with('error', 'Please verify the code sent to your email first.');
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect()->route('rentalsystem.forgot-password')->with('error', 'User not found.');
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        // Cleanup reset token and session
        PasswordResetToken::where('email', $email)->delete();
        Session::forget(['reset_email', 'reset_verified']);

        return redirect()->route('rentalsystem.signin')->with('success', 'Password updated successfully. Please sign in.');
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

        if (!$tournament || $tournament->status->value !== \App\Enums\TournamentStatus::ACTIVE->value) {
            abort(404, 'Tournament not found or is currently inactive');
        }

        // Get sport information
        $sport = $this->sports->find($tournament->sport_id ?? 1);

        return view('rentalsystem.tournament-details', compact('tournament', 'sport'));
    }

    public function showRentalBooking($tournamentId)
    {
        // Fetch tournament with associated items/bundles and apply per-tournament override pricing
        $tournament = $this->tournaments->find($tournamentId);

        if (!$tournament || $tournament->status->value !== \App\Enums\TournamentStatus::ACTIVE->value) {
            abort(404, 'Tournament booking is currently closed or inactive');
        }

        $availableItems = collect($tournament->items ?? [])->map(function ($item) {
            $override = $item->pivot?->price;
            $item->effective_price = $override !== null ? (float) $override : (float) ($item->price ?? 0);
            return $item;
        });

        $availableBundles = collect($tournament->bundles ?? [])->map(function ($bundle) {
            $override = $bundle->pivot?->price;
            $bundle->effective_price = $override !== null ? (float) $override : (float) ($bundle->price ?? 0);
            return $bundle;
        });

        $testimonialQuoteRow = \App\Models\BookingOption::where('type', 'testimonial_quote')->latest('updated_at')->first();
        $supportPhoneRow = \App\Models\BookingOption::where('type', 'support_phone_number')->latest('updated_at')->first();

        $testimonialQuote = (object) [
            'testimonial_quote' => Cache::get('booking_testimonial_quote', $testimonialQuoteRow?->testimonial_quote),
            'testimonial_author' => Cache::get('booking_testimonial_author', $testimonialQuoteRow?->testimonial_author),
        ];
        $supportPhoneNumber = (object) [
            'support_phone_number' => Cache::get('booking_support_phone_number', $supportPhoneRow?->support_phone_number),
        ];

        return view('rentalsystem.layouts.rental-booking-preview', [
            'tournament' => $tournament,
            'tournamentId' => $tournamentId,
            'availableItems' => $availableItems,
            'availableBundles' => $availableBundles,
            'testimonialQuote' => $testimonialQuote,
            'supportPhoneNumber' => $supportPhoneNumber,
            'isPreviewMode' => false,
        ]);
    }

    public function showRentalBookingPreview($tournamentId)
    {
        $tournament = $this->tournaments->find($tournamentId);

        if (!$tournament || $tournament->status->value !== \App\Enums\TournamentStatus::ACTIVE->value) {
            abort(404, 'Tournament preview is not available for inactive tournaments');
        }

        $availableItems = collect($tournament->items ?? [])->map(function ($item) {
            $override = $item->pivot?->price;
            $item->effective_price = $override !== null ? (float) $override : (float) ($item->price ?? 0);
            return $item;
        });

        $availableBundles = collect($tournament->bundles ?? [])->map(function ($bundle) {
            $override = $bundle->pivot?->price;
            $bundle->effective_price = $override !== null ? (float) $override : (float) ($bundle->price ?? 0);
            return $bundle;
        });

        // Fetch testimonial and contact settings
        $testimonialQuoteRow = \App\Models\BookingOption::where('type', 'testimonial_quote')->latest('updated_at')->first();
        $supportPhoneRow = \App\Models\BookingOption::where('type', 'support_phone_number')->latest('updated_at')->first();

        $testimonialQuote = (object) [
            'testimonial_quote' => Cache::get('booking_testimonial_quote', $testimonialQuoteRow?->testimonial_quote),
            'testimonial_author' => Cache::get('booking_testimonial_author', $testimonialQuoteRow?->testimonial_author),
        ];
        $supportPhoneNumber = (object) [
            'support_phone_number' => Cache::get('booking_support_phone_number', $supportPhoneRow?->support_phone_number),
        ];

        return view('rentalsystem.layouts.rental-booking-preview', [
            'tournament' => $tournament,
            'tournamentId' => $tournamentId,
            'availableItems' => $availableItems,
            'availableBundles' => $availableBundles,
            'testimonialQuote' => $testimonialQuote,
            'supportPhoneNumber' => $supportPhoneNumber,
            'isPreviewMode' => true,
        ]);
    }

    // public function createRental(Request $request)
    // {
    //     // Anonymous booking allowed; no auth required

    //     $validator = Validator::make($request->all(), [
    //         'tournament_id' => 'required|integer',
    //         'full_name' => 'nullable|string|max:255',
    //         'team_name_with_age_group' => 'nullable|string|max:255',
    //         'coach_name' => 'nullable|string|max:255',
    //         'phone_number' => 'nullable|string|max:30',
    //         'email' => 'nullable|email',
    //         'booking_days' => 'nullable|integer|min:1|max:7',
    //         // 'field_number' removed
    //         // drop_off_date/time removed
    //         'items' => 'nullable|array',
    //         'bundles' => 'nullable|array',
    //         'insurance_option' => 'nullable',
    //         'damage_waiver' => 'nullable',
    //         'payment_method' => 'required|in:stripe',
    //     ]);

    //     if ($validator->fails()) {
    //         return back()->withErrors($validator)->withInput();
    //     }

    //     // Additional validation: At least one item or bundle must be selected
    //     $itemsInput = (array) $request->input('items', []);
    //     $bundlesInput = (array) $request->input('bundles', []);

    //     $hasItems = false;
    //     $hasBundles = false;

    //     foreach ($itemsInput as $itemId => $qty) {
    //         if ((int) $qty > 0) {
    //             $hasItems = true;
    //             break;
    //         }
    //     }

    //     foreach ($bundlesInput as $bundleId => $qty) {
    //         if ((int) $qty > 0) {
    //             $hasBundles = true;
    //             break;
    //         }
    //     }

    //     if (!$hasItems && !$hasBundles) {
    //         return back()->withErrors(['items' => 'Please select at least one item or bundle.'])->withInput();
    //     }

    //     $user = Auth::user();

    //     // Tournament-specific allowed items/bundles and prices (override if set)
    //     $tournament = $this->tournaments->find((int) $request->input('tournament_id'));
    //     $itemPrices = [];
    //     foreach (($tournament->items ?? []) as $it) {
    //         $itemPrices[$it->id] = (float) ($it->pivot?->price ?? $it->price ?? 0);
    //     }
    //     $bundlePrices = [];
    //     foreach (($tournament->bundles ?? []) as $bd) {
    //         $bundlePrices[$bd->id] = (float) ($bd->pivot?->price ?? $bd->price ?? 0);
    //     }

    //     // Normalize selections
    //     $itemsInput = (array) $request->input('items', []);
    //     $bundlesInput = (array) $request->input('bundles', []);
    //     $selectedItems = [];
    //     $selectedBundles = [];
    //     $itemsSubtotal = 0.0;
    //     $bundlesSubtotal = 0.0;

    //     // Process items - store as [{"item_id":"1","quantity":3}] using tournament-specific allowed list
    //     foreach ($itemsInput as $itemId => $qty) {
    //         $quantity = max(0, (int) $qty);
    //         if ($quantity > 0) {
    //             // Only include if associated with tournament
    //             if (array_key_exists($itemId, $itemPrices)) {
    //                 $price = (float) $itemPrices[$itemId];
    //                 $itemsSubtotal += $price * $quantity;
    //                 $selectedItems[] = [
    //                     'item_id' => (string) $itemId,
    //                     'quantity' => $quantity
    //                 ];
    //             }
    //         }
    //     }

    //     // Process bundles - store as [{"bundle_id":"ID","quantity":N}]
    //     foreach ($bundlesInput as $bundleId => $qty) {
    //         $quantity = max(0, (int) $qty);
    //         if ($quantity > 0) {
    //             if (array_key_exists($bundleId, $bundlePrices)) {
    //                 $price = (float) $bundlePrices[$bundleId];
    //                 $bundlesSubtotal += $price * $quantity;
    //                 $selectedBundles[] = [
    //                     'bundle_id' => (string) $bundleId,
    //                     'quantity' => $quantity,
    //                 ];
    //             }
    //         }
    //     }

    //     $insurance = $request->input('insurance_option');
    //     $insuranceAmount = 0.0;
    //     if (is_numeric($insurance)) {
    //         $insuranceAmount = (float) $insurance;
    //     }
    //     // Sum multiple waiver options if provided as damage_waiver_options[] values
    //     $waiverAmount = 0.0;
    //     foreach ((array) $request->input('damage_waiver_options', []) as $waiverVal) {
    //         $waiverAmount += (float) $waiverVal;
    //     }

    //     $total = $itemsSubtotal + $bundlesSubtotal + $insuranceAmount + $waiverAmount;

    //     // Get the discounted total from frontend (if coupon was applied)
    //     $frontendTotal = (float) $request->input('total_amount', 0);


    //     // Use frontend total if it's provided and reasonable, otherwise use calculated total (pre-tax)
    //     $subTotalBeforeTax = ($frontendTotal > 0 && $frontendTotal <= $total) ? $frontendTotal : $total;

    //     // Calculate tax for website flow using tournament tax_rate
    //     $taxRate = (float) ($tournament->tax_rate ?? 0);
    //     $taxAmount = round($subTotalBeforeTax * ($taxRate / 100), 2);
    //     $finalTotal = $subTotalBeforeTax + $taxAmount;

    //     $dropOffDateTime = null;

    //     // Create rental record with pending payment
    //     $rental = $this->rentals->create([
    //         'user_id' => $user->id ?? null,
    //         'booking_source' => 'website',
    //         'full_name' => $request->input('full_name') ?: null,
    //         'tournament_id' => (int) $request->input('tournament_id'),
    //         'team_name_with_age_group' => $request->input('team_name_with_age_group') ?: null,
    //         'coach_name' => $request->input('coach_name') ?: null,
    //         'phone_number' => $request->input('phone_number') ?: ($user->contact_number ?? null),
    //         'email' => $request->input('email') ?: ($user->email ?? null),
    //         'booking_days' => (int) $request->input('booking_days'),
    //         'items' => !empty($selectedItems) ? $selectedItems : null,
    //         'bundles' => !empty($selectedBundles) ? $selectedBundles : null,
    //         'drop_off_time' => $dropOffDateTime,
    //         'insurance_option' => $insuranceAmount > 0 ? $insuranceAmount : null,
    //         'damage_waiver' => $waiverAmount > 0 ? $waiverAmount : null,
    //         'payment_method' => 'stripe',
    //         'payment_status' => 'pending',
    //         'total_amount' => $finalTotal,
    //         'tax_rate' => $taxRate,
    //         'tax_amount' => $taxAmount,
    //         'status' => 'pending',
    //     ]);
    //     Log::info('Created rental booking', ['rental' => $rental, 'total_amount' => $finalTotal]);


    //     // Do NOT send confirmation on creation. Email/SMS will be sent on payment completed (Stripe webhook or success fallback)

    //     // Start Stripe Checkout session
    //     try {
    //         Stripe::setApiKey(config('services.stripe.secret'));
    //         $session = StripeCheckoutSession::create([
    //             'mode' => 'payment',
    //             'payment_method_types' => ['card'],
    //             'line_items' => [[
    //                 'price_data' => [
    //                     'currency' => 'usd',
    //                     'product_data' => ['name' => 'Tournament Rental Booking'],
    //                     'unit_amount' => (int) round($finalTotal * 100),
    //                 ],
    //                 'quantity' => 1,
    //             ]],
    //             'success_url' => route('rentalsystem.checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
    //             'cancel_url' => route('rentalsystem.checkout.cancel', ['rental' => $rental->id]),
    //             'metadata' => [
    //                 'rental_id' => (string) $rental->id,
    //                 'user_id' => (string) ($user->id ?? ''),
    //                 'booking_source' => 'website',
    //             ],
    //         ]);
    //         return redirect($session->url);
    //     } catch (\Throwable $e) {
    //         return redirect()->route('rentalsystem.rental-booking', $request->input('tournament_id'))
    //             ->withErrors(['error' => 'Stripe error: ' . $e->getMessage()]);
    //     }
    // }


    public function createRental(Request $request)
    {
        // Anonymous booking allowed; no auth required
        $expectsJson = $request->expectsJson() || $request->ajax();

        $validator = Validator::make($request->all(), [
            'tournament_id' => 'required|integer',
            'full_name' => 'required|string|max:255',
            'team_name' => 'nullable|string|max:255',
            'age_group' => 'nullable|string|max:255',
            'coach_name' => 'nullable|string|max:255',
            'phone_number' => 'required|string|max:30',
            'email' => 'nullable|email',
            'booking_days' => 'nullable|integer|min:1|max:7',
            'items' => 'nullable|array',
            'bundles' => 'nullable|array',
            'insurance_option' => 'nullable',
            'damage_waiver_options' => 'nullable|array',
            'promo_code' => 'nullable|string|max:100',
            'payment_method' => 'required|in:stripe',
        ]);

        if ($validator->fails()) {
            if ($expectsJson) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }
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
            if ($expectsJson) {
                return response()->json([
                    'message' => 'Please select at least one item or bundle.',
                    'errors' => ['items' => ['Please select at least one item or bundle.']],
                ], 422);
            }
            return back()->withErrors(['items' => 'Please select at least one item or bundle.'])->withInput();
        }

        $user = Auth::user();

        // Tournament-specific allowed items/bundles and prices (override if set)
        $tournament = $this->tournaments->find((int) $request->input('tournament_id'));

        if (!$tournament || $tournament->status->value !== \App\Enums\TournamentStatus::ACTIVE->value) {
            if ($expectsJson) {
                return response()->json([
                    'message' => 'This tournament is no longer accepting bookings.',
                ], 422);
            }
            return back()->withErrors(['tournament_id' => 'This tournament is no longer accepting bookings.'])->withInput();
        }

        $itemPrices = [];
        foreach (($tournament->items ?? []) as $it) {
            $itemPrices[$it->id] = (float) ($it->pivot?->price ?? $it->price ?? 0);
        }
        $bundlePrices = [];
        foreach (($tournament->bundles ?? []) as $bd) {
            $bundlePrices[$bd->id] = (float) ($bd->pivot?->price ?? $bd->price ?? 0);
        }

        // Normalize selections
        $itemsInput = (array) $request->input('items', []);
        $bundlesInput = (array) $request->input('bundles', []);
        $selectedItems = [];
        $selectedBundles = [];
        $itemsSubtotal = 0.0;
        $bundlesSubtotal = 0.0;

        // Process items - store as [{"item_id":"1","quantity":3}] using tournament-specific allowed list
        foreach ($itemsInput as $itemId => $qty) {
            $quantity = max(0, (int) $qty);
            if ($quantity > 0) {
                if (array_key_exists($itemId, $itemPrices)) {
                    $price = (float) $itemPrices[$itemId];
                    $itemsSubtotal += $price * $quantity;
                    $selectedItems[] = [
                        'item_id' => (string) $itemId,
                        'quantity' => $quantity
                    ];
                }
            }
        }

        // Process bundles - store as [{"bundle_id":"ID","quantity":N}]
        foreach ($bundlesInput as $bundleId => $qty) {
            $quantity = max(0, (int) $qty);
            if ($quantity > 0) {
                if (array_key_exists($bundleId, $bundlePrices)) {
                    $price = (float) $bundlePrices[$bundleId];
                    $bundlesSubtotal += $price * $quantity;
                    $selectedBundles[] = [
                        'bundle_id' => (string) $bundleId,
                        'quantity' => $quantity,
                    ];
                }
            }
        }

        // --- START: New Backend Pricing Logic ---

        // Step 1: Calculate Items & Bundles Subtotal (Taxable Base)
        $itemsAndBundlesSubtotal = $itemsSubtotal + $bundlesSubtotal;

        // Step 2: Calculate Non-Taxable Fees (Waiver & Insurance)
        $insurance = $request->input('insurance_option');
        $insuranceAmount = 0.0;
        if (is_numeric($insurance)) {
            $insuranceAmount = (float) $insurance;
        }

        $waiverAmount = 0.0;
        foreach ((array) $request->input('damage_waiver_options', []) as $waiverVal) {
            $waiverAmount += (float) $waiverVal;
        }
        $totalFees = $insuranceAmount + $waiverAmount; // Yeh non-taxable hain

        // Step 3: Calculate Discount (Backend-Only, with full validation)
        $promoCode = $request->input('promo_code');
        $discountAmount = 0.0;
        $coupon = null;

        if ($promoCode) {
            // Complete validation: check code, active status, and expiry
            $coupon = Coupon::where('code', $promoCode)
                // ->where(function ($query) {
                //     $query->whereNull('expires_at')
                //         ->orWhere('expires_at', '>', now());
                // })
                ->first();
        }

        // Discount SIRF tab apply hoga jab coupon valid milega
        if ($coupon) {
            if ($coupon->type === 'percent') {
                $discountAmount = $itemsAndBundlesSubtotal * ($coupon->value / 100);
            } elseif ($coupon->type === 'fixed') {
                $discountAmount = (float) $coupon->value;
            }

            // Ensure discount isn't more than the subtotal
            $discountAmount = min($itemsAndBundlesSubtotal, $discountAmount);
        }
        // Agar coupon ghalat tha, to $coupon null hoga aur $discountAmount 0.0 rahega

        // Step 4: Calculate Taxable Amount (Discounted Subtotal)
        $taxableAmount = $itemsAndBundlesSubtotal - $discountAmount;

        // Step 5: Calculate Tax (Sirf Taxable Amount par)
        $taxRate = (float) ($tournament->tax_rate ?? 0);
        $taxAmount = round($taxableAmount * ($taxRate / 100), 2);


        // Step 6: Calculate Final Total
        // Final = (Discounted Subtotal) + Tax + (Non-Taxable Fees)

        $finalTotal = $taxableAmount + $taxAmount + $totalFees;

        // --- END: New Backend Pricing Logic ---

        $dropOffDateTime = null;

        // Create rental record with pending payment
        $rental = $this->rentals->create([
            'user_id' => $user->id ?? null,
            'booking_source' => 'website',
            'full_name' => $request->input('full_name') ?: null,
            'tournament_id' => (int) $request->input('tournament_id'),
            'team_name' => $request->input('team_name') ?: null,
            'age_group' => $request->input('age_group') ?: null,
            'team_name_with_age_group' => trim(($request->input('team_name') ?? '') . ' ' . ($request->input('age_group') ?? '')) ?: null,
            'coach_name' => $request->input('coach_name') ?: null,
            'phone_number' => $request->input('phone_number') ?: ($user->contact_number ?? null),
            'email' => $request->input('email') ?: ($user->email ?? null),
            'booking_days' => (int) $request->input('booking_days'),
            'items' => !empty($selectedItems) ? $selectedItems : null,
            'bundles' => !empty($selectedBundles) ? $selectedBundles : null,
            'drop_off_time' => $dropOffDateTime,
            'insurance_option' => $insuranceAmount > 0 ? $insuranceAmount : null,
            'damage_waiver' => $waiverAmount > 0 ? $waiverAmount : null,
            'payment_method' => 'stripe',
            'payment_status' => 'pending',

            // --- Updated & New Values ---
            'total_amount' => $finalTotal,      // Secure backend total
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,         // Secure backend tax

            // (Database migration mein yeh columns add karein)
            'discount_amount' => $discountAmount > 0 ? $discountAmount : null,
            'promo_code' => $coupon ? $promoCode : null, // Sirf valid code save karein
            // ------------------------

            'status' => 'pending',
        ]);

        Log::info('Created rental booking', ['rental' => $rental, 'total_amount' => $finalTotal]);

        try {
            $this->airtable->updateOrInsertRental($rental);
        } catch (\Throwable $e) {
            Log::error('Airtable sync failed on createRental', ['error' => $e->getMessage()]);
        }

        // Do NOT send confirmation on creation. Email/SMS will be sent on payment completed (Stripe webhook or success fallback)

        // Start Stripe Checkout session
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = StripeCheckoutSession::create([
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => ['name' => 'Tournament Rental Booking'],
                            'unit_amount' => (int) round($finalTotal * 100), // Final backend total
                        ],
                        'quantity' => 1,
                    ]
                ],
                'success_url' => route('rentalsystem.checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('rentalsystem.checkout.cancel', ['rental' => $rental->id]),
                'metadata' => [
                    'rental_id' => (string) $rental->id,
                    'user_id' => (string) ($user->id ?? ''),
                    'booking_source' => 'website',
                ],
            ]);
            if ($expectsJson) {
                return response()->json([
                    'success' => true,
                    'checkout_url' => $session->url,
                ]);
            }
            return redirect($session->url);
        } catch (\Throwable $e) {
            if ($expectsJson) {
                return response()->json([
                    'message' => 'Stripe error: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->route('rentalsystem.rental-booking', $request->input('tournament_id'))
                ->withErrors(['error' => 'Stripe error: ' . $e->getMessage()]);
        }
    }

    public function checkoutSuccess(Request $request)
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return redirect()->route('rentalsystem.sports')->with('error', 'Missing Stripe session.');
        }
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = \Stripe\Checkout\Session::retrieve($sessionId);
            $rentalId = $session->metadata->rental_id ?? null;
            if ($session->payment_status === 'paid' && $rentalId) {
                $rental = $this->rentals->update($rentalId, [
                    'payment_status' => 'completed',
                    'stripe_payment_id' => $session->payment_intent ?? null
                ]);

                try {
                    $this->airtable->updateOrInsertRental($rental);
                } catch (\Throwable $e) {
                    Log::error('Airtable sync failed on checkoutSuccess', ['error' => $e->getMessage()]);
                }

                try {
                    // After payment: send all booking confirmations (email/SMS/FCM) via existing event + notification
                    $user = auth()->user();
                    if ($user) {
                        $user->notify(new \App\Notifications\RentalBookingConfirmationNotification($rental, $user));
                    }
                    event(new \App\Events\RentalBookingCreated($rental));
                } catch (\Throwable $e) { /* log silently */
                }
                $rental->load('tournament');

                // Fire Purchase event server-side via Meta Conversions API
                try {
                    (new \App\Services\MetaPixelService())->trackPurchase([
                        'value' => $rental->total_amount,
                        'order_id' => (string) $rental->id,
                        'content_name' => optional($rental->tournament)->name ?? 'Tournament Rental',
                        'email' => $rental->email ?? optional($rental->user)->email,
                        'phone' => $rental->phone_number ?? optional($rental->user)->contact_number,
                        'ip' => request()->ip(),
                        'url' => request()->url(),
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Meta CAPI error', ['error' => $e->getMessage()]);
                }

                return view('rentalsystem.checkout-success', ['rental' => $rental]);
            }
            return redirect()->route('rentalsystem.sports')->with('info', 'Payment not completed yet.');
        } catch (\Throwable $e) {
            return redirect()->route('rentalsystem.sports')->with('error', 'Stripe confirmation failed: ' . $e->getMessage());
        }
    }

    public function checkoutCancel(Request $request)
    {
        $rentalId = $request->query('rental');
        return redirect()->route('rentalsystem.sports')->with('info', 'Checkout canceled.' . ($rentalId ? ' Rental #' . $rentalId . ' remains pending.' : ''));
    }

    public function showProfile()
    {
        if (!Auth::check()) {
            return redirect()->route('rentalsystem.signin');
        }
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('rentalsystem.signin')->with('error', 'Please sign in to view your profile.');
        }
        try {
            $collection = $this->rentals->getByUser($user->id) ?? collect();
        } catch (\Throwable $e) {
            Log::warning('Profile rentals load failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            $collection = collect();
        }
        $rentals = [];
        foreach (is_iterable($collection) ? $collection : [] as $r) {
            $itemsArray = [];
            $bundlesArray = [];

            // Process items - get actual item names from database
            if (is_array($r->items)) {
                foreach ($r->items as $item) {
                    if (is_array($item) && isset($item['item_id']) && isset($item['quantity'])) {
                        $itemModel = null;
                        try {
                            $itemModel = $this->items->find($item['item_id']);
                        } catch (\Throwable $e) {
                            $itemModel = null;
                        }
                        if ($itemModel) {
                            $itemsArray[] = [
                                'name' => $itemModel->name,
                                'quantity' => (int) $item['quantity']
                            ];
                        }
                    }
                }
            }

            // Process bundles - support new structure with quantities and fallback to legacy array of IDs
            if (is_array($r->bundles)) {
                foreach ($r->bundles as $b) {
                    if (is_array($b) && isset($b['bundle_id'])) {
                        $bundleId = $b['bundle_id'];
                        $qty = isset($b['quantity']) ? (int) $b['quantity'] : 1;
                        $bundle = null;
                        try {
                            $bundle = $this->bundles->find($bundleId);
                        } catch (\Throwable $e) {
                            $bundle = null;
                        }
                        if ($bundle) {
                            $bundlesArray[] = [
                                'name' => $bundle->name,
                                'quantity' => $qty,
                            ];
                        }
                    } elseif (is_numeric($b)) {
                        $bundle = null;
                        try {
                            $bundle = $this->bundles->find($b);
                        } catch (\Throwable $e) {
                            $bundle = null;
                        }
                        if ($bundle) {
                            $bundlesArray[] = [
                                'name' => $bundle->name,
                                'quantity' => 1,
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

    public function updateNotifications(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'type' => 'required|string|in:fcm,email,sms',
            'enabled' => 'required|boolean',
        ]);

        $user = Auth::user();
        $map = [
            'fcm' => 'fcm_notification',
            'email' => 'email_notification',
            'sms' => 'text_notification',
        ];
        $field = $map[$request->input('type')];
        $user->{$field} = (bool) $request->boolean('enabled');
        $user->save();

        return response()->json([
            'message' => ucfirst($request->input('type')) . ' preference updated',
            'enabled' => (bool) $user->{$field},
        ]);
    }

    public function logout()
    {
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();
        return redirect()->route('rentalsystem.signin')->with('success', 'You have been logged out successfully.');
    }

    public function deleteAccountWeb(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        try {
            // Revoke tokens if any (mobile/web API tokens)
            try {
                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }
            } catch (\Throwable $e) { /* ignore */
            }

            // Clean simple relations if available
            try {
                if (method_exists($user, 'cart_items')) {
                    $user->cart_items()->delete();
                }
            } catch (\Throwable $e) { /* ignore */
            }

            // Delete the user
            $userId = $user->id;
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $user->delete();

            return response()->json(['success' => true, 'redirect' => route('rentalsystem.signin')]);
        } catch (\Throwable $e) {
            Log::error('Delete account (web) failed', ['user_id' => $user->id ?? null, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Unable to delete account. Please try again.'], 500);
        }
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
                    'sms_consent' => true,
                ]);

                $user->assignRole(Role::USER->value);
                $this->referralService->generateCode($user->id);
                $isNewUser = true;
            } else {
                if (!$user->google_id) {
                    $user->google_id = $googleId;
                }
                if (!$user->sms_consent) {
                    $user->sms_consent = true;
                }
                $user->save();
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
