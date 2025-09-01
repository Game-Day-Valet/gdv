<?php

namespace App\Http\Controllers;

use App\Http\Requests\CouponRequest;
use App\Http\Requests\CouponUpdateRequest;
use App\Repositories\CouponRepositoryInterface;
use App\Mail\CouponEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\CouponSendBatch;
use App\Jobs\SendCouponBatchJob;

class CouponManagementController extends Controller
{
    protected $couponRepository;

    public function __construct(CouponRepositoryInterface $couponRepository)
    {
        $this->couponRepository = $couponRepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $coupons = $this->couponRepository->getAll();
        return view('coupon_management.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('coupon_management.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CouponRequest $request)
    {
        try {
            $coupon = $this->couponRepository->create($request->validated(), []);

            return redirect()->route('coupon-management.index')
                ->with('success', value: 'Coupon created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create coupon. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $coupon = $this->couponRepository->find($id);
        return view('coupon_management.edit', compact('coupon'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CouponUpdateRequest $request, string $id)
    {
        try {
            $coupon = $this->couponRepository->update($id, $request->validated(), []);

            return redirect()->route('coupon-management.index')
                ->with('success', 'Coupon updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update coupon. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->couponRepository->delete($id);
            return redirect()->route('coupon-management.index')
                ->with('success', 'Coupon deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to delete coupon. Please try again.');
        }
    }

    /**
     * Send coupon to all customers via email.
     */
    public function send(string $id)
    {
        try {
            // Debug: Log the request
            $user = Auth::user();
            Log::channel('daily')->info('Coupon send request', [
                'id' => $id,
                'user_id' => Auth::id(),
                'user_roles' => $user ? $user->roles->pluck('name') : [],
                'user_permissions' => $user ? $user->permissions->pluck('name') : [],
                'has_super_admin_permission' => $user ? $user->hasPermissionTo('super_admin') : false,
                'has_super_admin_role' => $user ? $user->hasRole('super_admin') : false,
                'is_ajax' => request()->ajax(),
                'headers' => request()->headers->all(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Validate coupon exists before queuing
            $coupon = $this->couponRepository->find($id);
            if (!$coupon) {
                Log::warning('Coupon not found', ['coupon_id' => $id]);
                return response()->json(['success' => false, 'message' => 'Coupon not found.'], 404);
            }

            // Create batch log entry and dispatch background job
            $batch = CouponSendBatch::create([
                'coupon_id' => (int) $id,
                'initiated_by' => (int) Auth::id(),
                'status' => 'queued',
                'message' => 'Queued for sending',
            ]);

            SendCouponBatchJob::dispatch($batch->id);

            return response()->json([
                'success' => true,
                'message' => 'Coupon email sending queued. You will see results shortly.',
                'batch_id' => $batch->id,
                'status_url' => route('coupon-management.send-status', $batch->id),
                'logs_url' => route('coupon-management.logs'),
            ]);
            } catch (\Exception $e) {
            Log::error('Coupon send endpoint failed', [
                'coupon_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
                return response()->json(['success' => false, 'message' => 'Failed to send coupon. Please try again.'], 500);
            }
    }

    public function sendStatus(int $batchId)
    {
        $batch = CouponSendBatch::find($batchId);
        if (!$batch) {
            return response()->json(['success' => false, 'message' => 'Batch not found'], 404);
        }
        return response()->json([
            'success' => true,
            'status' => $batch->status,
            'sent_count' => (int) $batch->sent_count,
            'failed_count' => (int) $batch->failed_count,
            'total_recipients' => (int) $batch->total_recipients,
            'message' => $batch->message,
            'finished_at' => optional($batch->finished_at)->toDateTimeString(),
        ]);
    }

    public function logs()
    {
        $batches = CouponSendBatch::with('coupon')->latest()->paginate(25);
        return view('coupon_management.logs', compact('batches'));
    }

    /**
     * Preview the coupon email template.
     */
    public function preview(string $id)
    {
        try {
            $coupon = $this->couponRepository->find($id);

            if (!$coupon) {
                return redirect()->back()->with('error', 'Coupon not found.');
            }

            $user = User::where('email_verified_at', '!=', null)->first();

            if (!$user) {
                return redirect()->back()->with('error', 'No verified users found.');
            }

            return view('emails.coupon', [
                'name' => $user->name,
                'coupon' => $coupon,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to preview coupon email.');
        }
    }
}
