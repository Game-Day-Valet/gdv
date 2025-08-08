<?php

namespace App\Repositories;

interface RentalRepositoryInterface
{
    public function getAll();
    public function getAllPaginated($perPage = 15);
    public function find($id);
    public function findWithRelations($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function updateStatus($id, $status, $notes = null, $updatedBy = null, $image = null);
    public function updatePaymentStatus($id, $paymentStatus);
    public function getStatusLogs($rentalId);
}
