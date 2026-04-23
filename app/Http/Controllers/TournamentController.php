<?php

namespace App\Http\Controllers;

use App\Enums\ItemStatus;
use Illuminate\Http\Request;
use App\Enums\TournamentStatus;
use App\Repositories\TournamentRepositoryInterface;
use App\Http\Requests\TournamentRequest;
use App\Models\Bundle;
use App\Models\Item;
use App\Models\Sport;

class TournamentController extends Controller
{
    protected $tournamentRepository;
    protected $airtableService;

    public function __construct(TournamentRepositoryInterface $tournamentRepository, \App\Services\AirtableService $airtableService)
    {
        $this->tournamentRepository = $tournamentRepository;
        $this->airtableService = $airtableService;
    }

    public function index(Request $request)
    {
        $sportId = $request->get('sport_id');
        $status = $request->get('status');
        $location = $request->get('location');

        $query = \App\Models\Tournament::with('sport');

        if ($sportId) {
            $query->where('sport_id', $sportId);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($location) {
            $query->where('location', 'like', "%$location%");
        }

        // Custom sort: Active first, then Completed, then Inactive
        // We use orderByRaw to ensure status priority
        $tournaments = $query->orderByRaw("CASE 
                                WHEN status = 'active' THEN 1 
                                WHEN status = 'completed' THEN 2 
                                ELSE 3 
                             END ASC")
                             ->get();
                             
        $sports = Sport::all();

        return view('tournament_management.index', compact('tournaments', 'sports'));
    }

    public function reorder(Request $request)
    {
        if (!auth()->user()->can('super_admin')) {
            abort(403, 'This action is unauthorized.');
        }
        $data = $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|integer|exists:tournaments,id',
            'orders.*.sort_order' => 'required|integer|min:0',
        ]);
        $this->tournamentRepository->updateSortOrders($data['orders']);
        return response()->json(['success' => true]);
    }

    public function create()
    {
        $statuses = TournamentStatus::cases();
        $sports = Sport::where('status', 'active')->get();
        $items = Item::where('status', ItemStatus::AVAILABLE->value)->orderBy('name')->get();
        $bundles = Bundle::where('status', ItemStatus::AVAILABLE->value)->orderBy('name')->get();
        return view('tournament_management.create', compact('statuses', 'sports', 'items', 'bundles'));
    }

    public function store(TournamentRequest $request)
    {
        $this->tournamentRepository->create($request->validated());
        return redirect()->route('tournament-management.index')->with('success', 'Tournament created successfully.');
    }

    public function edit($id)
    {
        $tournament = $this->tournamentRepository->find($id);
        $statuses = TournamentStatus::cases();
        $sports = Sport::where('status', 'active')->get();
        $items = Item::where('status', ItemStatus::AVAILABLE->value)->orderBy('name')->get();
        $bundles = Bundle::where('status', ItemStatus::AVAILABLE->value)->orderBy('name')->get();
        return view('tournament_management.edit', compact('tournament', 'statuses', 'sports', 'items', 'bundles'));
    }

    public function update(TournamentRequest $request, $id)
    {
        // Check if game date or time was changed by comparing old and new values.
        $tournamentBeforeUpdate = $this->tournamentRepository->find($id);
        $oldGameDate = $tournamentBeforeUpdate->game_date;
        $oldGameTime = $tournamentBeforeUpdate->game_time;

        $this->tournamentRepository->update($id, $request->validated());

        $tournamentAfterUpdate = $this->tournamentRepository->find($id);

        // If the game_date or game_time changed, sync to Airtable
        if ($oldGameDate != $tournamentAfterUpdate->game_date || $oldGameTime != $tournamentAfterUpdate->game_time) {
            $this->airtableService->syncTournamentGamesToAirtable($tournamentAfterUpdate);
        }

        return redirect()->route('tournament-management.index')->with('success', 'Tournament updated successfully.');
    }

    public function destroy($id)
    {
        // Only super_admin can delete tournaments
        if (!auth()->user()->can('super_admin')) {
            abort(403, 'This action is unauthorized.');
        }

        try {
            $this->tournamentRepository->delete($id);
            return redirect()->route('tournament-management.index')->with('success', 'Tournament deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('tournament-management.index')->with('error', 'Failed to delete tournament: ' . $e->getMessage());
        }
    }
}
