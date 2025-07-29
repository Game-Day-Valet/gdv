<?php

namespace App\Repositories;

interface CouponRepositoryInterface
{
    public function getAllAvailable();
    public function getAll();
    public function find($id);
    public function create(array $data, array $items);
    public function update($id, array $data, array $items);
    public function delete($id);
}
