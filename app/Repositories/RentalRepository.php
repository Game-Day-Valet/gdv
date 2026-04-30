<?php


namespace App\Repositories;

use App\Models\Rental;
use App\Models\RentalStatusLog;
use Illuminate\Support\Facades\DB;

class RentalRepository implements RentalRepositoryInterface
{
    public function getAll()
    {
        // First 15 should always be most recently created
        $recentIds = Rental::orderBy('created_at', 'desc')->limit(15)->pluck('id')->map(fn($i) => (int) $i)->toArray();
        $inList = !empty($recentIds) ? implode(',', $recentIds) : 'NULL';

        return Rental::with(['user', 'tournament'])
            ->whereNull('archived_at')
            // Group A: recent ids; Group B: others
            ->orderByRaw("CASE WHEN id IN ($inList) THEN 0 ELSE 1 END ASC")
            // For recent, keep newest first
            ->orderBy('created_at', 'desc')
            // For others, respect manual order first, then recency
            ->orderByRaw("CASE WHEN sort_order IS NULL THEN 1 ELSE 0 END ASC")
            ->orderByRaw('sort_order ASC')
            ->get();
    }

    public function getPaid(array $filters = [])
    {
        $query = Rental::with(['user', 'tournament'])
            ->whereNull('archived_at')
            ->whereIn('payment_status', ['paid', 'completed']);

        $this->applyFilters($query, $filters);

        $recentIds = (clone $query)->orderBy('created_at', 'desc')->limit(15)->pluck('id')->map(fn($i) => (int) $i)->toArray();
        $inList = !empty($recentIds) ? implode(',', $recentIds) : 'NULL';

        return $query->orderByRaw("CASE WHEN id IN ($inList) THEN 0 ELSE 1 END ASC")
            ->orderBy('created_at', 'desc')
            ->orderByRaw("CASE WHEN sort_order IS NULL THEN 1 ELSE 0 END ASC")
            ->orderByRaw('sort_order ASC')
            ->get();
    }

    public function getPending(array $filters = [])
    {
        $query = Rental::with(['user', 'tournament'])
            ->whereNull('archived_at')
            ->where('payment_status', 'pending');

        $this->applyFilters($query, $filters);

        $recentIds = (clone $query)->orderBy('created_at', 'desc')->limit(15)->pluck('id')->map(fn($i) => (int) $i)->toArray();
        $inList = !empty($recentIds) ? implode(',', $recentIds) : 'NULL';

        return $query->orderByRaw("CASE WHEN id IN ($inList) THEN 0 ELSE 1 END ASC")
            ->orderBy('created_at', 'desc')
            ->orderByRaw("CASE WHEN sort_order IS NULL THEN 1 ELSE 0 END ASC")
            ->orderByRaw('sort_order ASC')
            ->get();
    }

    public function getAllPaginated($perPage = 15)
    {
        $query = Rental::with(['user', 'tournament']);
        $result = $query->paginate($perPage);

        return $result;
    }

    public function find($id)
    {
        return Rental::with('tournament')->findOrFail($id);
    }

    public function findWithRelations($id)
    {
        return Rental::with(['user', 'tournament', 'photos', 'reviews', 'statusLogs.updatedBy'])
            ->findOrFail($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Comment: Checkout integration to be added later (Stripe, Apple Pay, Google Pay)
            /*
            // Checkout logic here
            $paymentMethod = $data['payment_method'] ?? 'stripe';
            if (!in_array($paymentMethod, ['stripe', 'apple_pay', 'google_pay'])) {
                throw new \Exception('Invalid payment method');
            }
            // Integrate with Stripe/Apple Pay/Google Pay API to process payment
            $data['payment_status'] = 'completed'; // Update after successful payment
            */

            $rental = Rental::create([
                'user_id' => $data['user_id'],
                'full_name' => $data['full_name'] ?? null,
                'tournament_id' => $data['tournament_id'],
                'booking_source' => $data['booking_source'] ?? null,
                'team_name' => $data['team_name'] ?? null,
                'age_group' => $data['age_group'] ?? null,
                'team_name_with_age_group' => $data['team_name_with_age_group'] ?? null,
                'coach_name' => $data['coach_name'] ?? null,
                'phone_number' => $data['phone_number'] ?? null,
                'email' => $data['email'] ?? null,
                'field_number' => $data['field_number'] ?? null,
                'items' => $data['items'] ?? null,
                'bundles' => $data['bundles'] ?? null,
                'instructions' => $data['instructions'] ?? null,
                'drop_off_time' => $data['drop_off_time'] ?? null,
                'promo_code' => $data['promo_code'] ?? null,
                'insurance_option' => $data['insurance_option'] ?? null,
                'damage_waiver' => $data['damage_waiver'] ?? null,
                'rental_date' => $data['rental_date'] ?? null,
                'booking_days' => isset($data['booking_days']) ? (int) $data['booking_days'] : null,
                'delivery_assigned_to' => $data['delivery_assigned_to'] ?? null,
                'payment_method' => $data['payment_method'] ?? null,
                'payment_status' => $data['payment_status'] ?? 'pending',
                'total_amount' => $data['total_amount'] ?? null,
                'tax_rate' => $data['tax_rate'] ?? null,
                'tax_amount' => $data['tax_amount'] ?? null,
                'discount_amount' => $data['discount_amount'] ?? null,
                'status' => $data['status'] ?? 'pending',
                'return_instruction' => $data['return_instruction'] ?? null,
                'stripe_payment_id' => $data['stripe_payment_id'] ?? null,
            ]);

            RentalStatusLog::create([
                'rental_id' => $rental->id,
                'status' => $rental->status,
                'notes' => null,
                'image_paths' => null,
                'updated_by' => null,
            ]);
            return $rental;
        });
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $rental = Rental::findOrFail($id);
            $rental->update([
                'user_id' => $data['user_id'] ?? $rental->user_id,
                'full_name' => $data['full_name'] ?? $rental->full_name,
                'tournament_id' => $data['tournament_id'] ?? $rental->tournament_id,
                'booking_source' => array_key_exists('booking_source', $data) ? $data['booking_source'] : $rental->booking_source,
                'team_name' => $data['team_name'] ?? $rental->team_name,
                'age_group' => $data['age_group'] ?? $rental->age_group,
                'team_name_with_age_group' => $data['team_name_with_age_group'] ?? $rental->team_name_with_age_group,
                'coach_name' => $data['coach_name'] ?? $rental->coach_name,
                'phone_number' => $data['phone_number'] ?? $rental->phone_number,
                'email' => $data['email'] ?? $rental->email,
                'field_number' => $data['field_number'] ?? $rental->field_number,
                'items' => $data['items'] ?? $rental->items,
                'bundles' => $data['bundles'] ?? $rental->bundles,
                'instructions' => $data['instructions'] ?? $rental->instructions,
                'drop_off_time' => $data['drop_off_time'] ?? $rental->drop_off_time,
                'promo_code' => $data['promo_code'] ?? $rental->promo_code,
                'insurance_option' => $data['insurance_option'] ?? $rental->insurance_option,
                'damage_waiver' => $data['damage_waiver'] ?? $rental->damage_waiver,
                'rental_date' => $data['rental_date'] ?? $rental->rental_date,
                'booking_days' => array_key_exists('booking_days', $data) ? (int) $data['booking_days'] : $rental->booking_days,
                'delivery_assigned_to' => $data['delivery_assigned_to'] ?? $rental->delivery_assigned_to,
                'payment_method' => $data['payment_method'] ?? $rental->payment_method,
                'payment_status' => $data['payment_status'] ?? $rental->payment_status,
                'total_amount' => $data['total_amount'] ?? $rental->total_amount,
                'tax_rate' => array_key_exists('tax_rate', $data) ? $data['tax_rate'] : $rental->tax_rate,
                'tax_amount' => array_key_exists('tax_amount', $data) ? $data['tax_amount'] : $rental->tax_amount,
                'status' => $data['status'] ?? $rental->status,
                'return_instruction' => $data['return_instruction'] ?? $rental->return_instruction,
                'stripe_payment_id' => $data['stripe_payment_id'] ?? $rental->stripe_payment_id,
            ]);
            return $rental;
        });
    }

    public function delete($id)
    {
        $rental = Rental::findOrFail($id);
        $rental->delete();
    }

    public function updateStatus($id, $status, $notes = null, $updatedBy = null, $images = null, $estimatedDeliveryTime = null, $assignedManagerId = null)
    {
        return DB::transaction(function () use ($id, $status, $notes, $updatedBy, $images, $estimatedDeliveryTime, $assignedManagerId) {
            $rental = Rental::findOrFail($id);

            // Prepare update data
            $updateData = ['status' => $status];

            if ($estimatedDeliveryTime) {
                $updateData['estimated_delivery_time'] = $estimatedDeliveryTime;
            }

            if ($assignedManagerId) {
                $updateData['assigned_manager_id'] = $assignedManagerId;
            }

            // Update rental status and other fields
            $rental->update($updateData);

            // Handle multiple image uploads
            $imagePaths = [];
            if ($images && is_array($images)) {
                foreach ($images as $image) {
                    if ($image && $image->isValid()) {
                        $imagePath = $image->store('rental-status-logs', 'public');
                        $imagePaths[] = $imagePath;
                    }
                }
            }

            // Log the status change (without estimated_delivery_time and assigned_manager_id)
            RentalStatusLog::create([
                'rental_id' => $rental->id,
                'status' => $status,
                'notes' => $notes,
                'image_paths' => !empty($imagePaths) ? $imagePaths : null,
                'updated_by' => $updatedBy,
            ]);

            return $rental;
        });
    }

    public function updatePaymentStatus($id, $paymentStatus)
    {
        return DB::transaction(function () use ($id, $paymentStatus) {
            $rental = Rental::findOrFail($id);
            $rental->update(['payment_status' => $paymentStatus]);
            return $rental;
        });
    }

    public function getStatusLogs($rentalId)
    {
        return RentalStatusLog::where('rental_id', $rentalId)
            ->with('updatedBy')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByUser($userId)
    {
        return Rental::with('tournament')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByManager($managerId, $perPage = 15, array $filters = [])
    {
        $query = Rental::with(['user', 'tournament'])
            ->whereNull('archived_at')
            ->where('assigned_manager_id', $managerId);

        $this->applyFilters($query, $filters);

        $recentIds = (clone $query)->orderBy('created_at', 'desc')->limit(15)->pluck('id')->map(fn($i) => (int) $i)->toArray();
        $inList = !empty($recentIds) ? implode(',', $recentIds) : 'NULL';

        $query->orderByRaw("CASE WHEN id IN ($inList) THEN 0 ELSE 1 END ASC")
            ->orderBy('created_at', 'desc')
            ->orderByRaw("CASE WHEN sort_order IS NULL THEN 1 ELSE 0 END ASC")
            ->orderByRaw('sort_order ASC');

        return $query->paginate($perPage);
    }

    public function getByManagerPaid($managerId, $perPage = 15, array $filters = [])
    {
        $query = Rental::with(['user', 'tournament'])
            ->whereNull('archived_at')
            ->where('assigned_manager_id', $managerId)
            ->whereIn('payment_status', ['paid', 'completed']);

        $this->applyFilters($query, $filters);

        $recentIds = (clone $query)->orderBy('created_at', 'desc')->limit(15)->pluck('id')->map(fn($i) => (int) $i)->toArray();
        $inList = !empty($recentIds) ? implode(',', $recentIds) : 'NULL';

        $query->orderByRaw("CASE WHEN id IN ($inList) THEN 0 ELSE 1 END ASC")
            ->orderBy('created_at', 'desc')
            ->orderByRaw("CASE WHEN sort_order IS NULL THEN 1 ELSE 0 END ASC")
            ->orderByRaw('sort_order ASC');

        return $query->paginate($perPage);
    }

    public function getByManagerPending($managerId, $perPage = 15, array $filters = [])
    {
        $query = Rental::with(['user', 'tournament'])
            ->whereNull('archived_at')
            ->where('assigned_manager_id', $managerId)
            ->where('payment_status', 'pending');

        $this->applyFilters($query, $filters);

        $recentIds = (clone $query)->orderBy('created_at', 'desc')->limit(15)->pluck('id')->map(fn($i) => (int) $i)->toArray();
        $inList = !empty($recentIds) ? implode(',', $recentIds) : 'NULL';

        $query->orderByRaw("CASE WHEN id IN ($inList) THEN 0 ELSE 1 END ASC")
            ->orderBy('created_at', 'desc')
            ->orderByRaw("CASE WHEN sort_order IS NULL THEN 1 ELSE 0 END ASC")
            ->orderByRaw('sort_order ASC');

        return $query->paginate($perPage);
    }

    private function applyFilters($query, array $filters)
    {
        if (!empty($filters['sport_id'])) {
            $query->whereHas('tournament', function ($q) use ($filters) {
                $q->withTrashed()->where('sport_id', $filters['sport_id']);
            });
        }

        if (!empty($filters['location'])) {
            $query->whereHas('tournament', function ($q) use ($filters) {
                $q->withTrashed()->where('location', $filters['location']);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (!empty($filters['coach_name'])) {
            $query->where('coach_name', 'like', '%' . $filters['coach_name'] . '%');
        }

        if (!empty($filters['team_name'])) {
            $query->where('team_name_with_age_group', 'like', '%' . $filters['team_name'] . '%');
        }

        return $query;
    }
}
