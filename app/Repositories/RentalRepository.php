<?php


namespace App\Repositories;

use App\Models\Rental;
use App\Models\RentalStatusLog;
use Illuminate\Support\Facades\DB;

class RentalRepository implements RentalRepositoryInterface
{
    public function getAll()
    {
        return Rental::with('tournament')->get();
    }

    public function getAllPaginated($perPage = 15)
    {
        $query = Rental::with(['user', 'tournament'])
            ->orderBy('created_at', 'desc');
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
                'tournament_id' => $data['tournament_id'],
                'team_name' => $data['team_name'],
                'coach_name' => $data['coach_name'],
                'field_number' => $data['field_number'] ?? null,
                'items' => $data['items'] ?? null,
                'bundles' => $data['bundles'] ?? null,
                'instructions' => $data['instructions'] ?? null,
                'drop_off_time' => $data['drop_off_time'] ?? null,
                'promo_code' => $data['promo_code'] ?? null,
                'insurance_option' => $data['insurance_option'] ?? null,
                'damage_waiver' => $data['damage_waiver'] ?? null,
                'rental_date' => $data['rental_date'] ?? null,
                'delivery_assigned_to' => $data['delivery_assigned_to'] ?? null,
                'payment_method' => $data['payment_method'] ?? null,
                'payment_status' => $data['payment_status'] ?? 'pending',
                'total_amount' => $data['total_amount'] ?? null,
                'status' => $data['status'] ?? 'pending',
                'return_instruction' => $data['return_instruction'] ?? null,
            ]);

            RentalStatusLog::create([
                'rental_id' => $rental->id,
                'status' => $rental->status,
                'notes' => null,
                'image_paths' =>  null,
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
                'tournament_id' => $data['tournament_id'] ?? $rental->tournament_id,
                'team_name' => $data['team_name'] ?? $rental->team_name,
                'coach_name' => $data['coach_name'] ?? $rental->coach_name,
                'field_number' => $data['field_number'] ?? $rental->field_number,
                'items' => $data['items'] ?? $rental->items,
                'bundles' => $data['bundles'] ?? $rental->bundles,
                'instructions' => $data['instructions'] ?? $rental->instructions,
                'drop_off_time' => $data['drop_off_time'] ?? $rental->drop_off_time,
                'promo_code' => $data['promo_code'] ?? $rental->promo_code,
                'insurance_option' => $data['insurance_option'] ?? $rental->insurance_option,
                'damage_waiver' => $data['damage_waiver'] ?? $rental->damage_waiver,
                'rental_date' => $data['rental_date'] ?? $rental->rental_date,
                'delivery_assigned_to' => $data['delivery_assigned_to'] ?? $rental->delivery_assigned_to,
                'payment_method' => $data['payment_method'] ?? $rental->payment_method,
                'payment_status' => $data['payment_status'] ?? $rental->payment_status,
                'total_amount' => $data['total_amount'] ?? $rental->total_amount,
                'status' => $data['status'] ?? $rental->status,
                'return_instruction' => $data['return_instruction'] ?? $rental->return_instruction,
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

    public function getByManager($managerId, $perPage = 15)
    {
        $query = Rental::with(['user', 'tournament'])
            ->where('assigned_manager_id', $managerId)
            ->orderBy('created_at', 'desc');
 
        $result = $query->paginate($perPage);
        
        return $result;
    }
}
