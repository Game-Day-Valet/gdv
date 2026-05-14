<?php

namespace App\Repositories;

interface TournamentRepositoryInterface
{
    public function getAllActive($search = null);
    public function find($id);
    public function findBySlug($slug);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function getTodaysTournaments();
    public function updateSortOrders(array $orders);
}
