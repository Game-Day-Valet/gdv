<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\SportStatus;
use App\Repositories\SportRepositoryInterface;
use App\Http\Requests\SportRequest;

class SportController extends Controller
{
    protected $sportRepository;

    public function __construct(SportRepositoryInterface $sportRepository)
    {
        $this->sportRepository = $sportRepository;
    }

    public function index()
    {
        $sports = $this->sportRepository->getAllActive();
        return view('sport_management.index', compact('sports'));
    }

    public function create()
    {
        $statuses = SportStatus::cases();
        return view('sport_management.create', compact('statuses'));
    }

    public function store(SportRequest $request)
    {
        $this->sportRepository->create($request->validated());
        return redirect()->route('sport-management.index')->with('success', 'Sport created successfully.');
    }

    public function edit($id)
    {
        $sport = $this->sportRepository->find($id);
        $statuses = SportStatus::cases();
        return view('sport_management.edit', compact('sport', 'statuses'));
    }

    public function update(SportRequest $request, $id)
    {
        $this->sportRepository->update($id, $request->validated());
        return redirect()->route('sport-management.index')->with('success', 'Sport updated successfully.');
    }

    public function destroy($id)
    {
        // Only super_admin can delete sports
        if (!auth()->user()->can('super_admin')) {
            abort(403, 'This action is unauthorized.');
        }

        try {
            $this->sportRepository->delete($id);
            return redirect()->route('sport-management.index')->with('success', 'Sport deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('sport-management.index')->with('error', 'Failed to delete sport: ' . $e->getMessage());
        }
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|integer|exists:sports,id',
            'orders.*.sort_order' => 'required|integer|min:0',
        ]);
        \DB::transaction(function() use ($data) {
            foreach ($data['orders'] as $o) {
                \App\Models\Sport::where('id', $o['id'])->update(['sort_order' => (int) $o['sort_order']]);
            }
        });
        return response()->json(['success' => true]);
    }
}
