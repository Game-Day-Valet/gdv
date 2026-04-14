<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentalArchiveController extends Controller
{
    public function index()
    {
        // Group archived rentals by tournament
        $folders = Rental::with('tournament')
            ->whereNotNull('archived_at')
            ->get()
            ->groupBy(function($r){ return optional($r->tournament)->name ?: 'Unknown Tournament'; });

        return view('rental_archive.index', compact('folders'));
    }

    public function folder(Request $request, $tournamentId)
    {
        $query = Rental::with(['user', 'tournament'])
            ->where('tournament_id', $tournamentId)
            ->whereNotNull('archived_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('sport_id')) {
            $query->whereHas('tournament', function ($q) use ($request) {
                $q->withTrashed()->where('sport_id', $request->sport_id);
            });
        }

        if ($request->filled('location')) {
            $query->whereHas('tournament', function ($q) use ($request) {
                $q->withTrashed()->where('location', $request->location);
            });
        }

        if ($request->filled('coach_name')) {
            $query->where('coach_name', 'like', '%' . $request->coach_name . '%');
        }

        if ($request->filled('team_name')) {
            $query->where('team_name_with_age_group', 'like', '%' . $request->team_name . '%');
        }

        $rentals = $query->orderBy('created_at', 'desc')->get();

        $sports = \App\Models\Sport::all();
        $locations = \App\Models\Tournament::withoutGlobalScopes()->whereNotNull('location')->distinct()->pluck('location');

        return view('rental_archive.folder', compact('rentals', 'sports', 'locations'));
    }

    public function archive(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:rentals,id']);
        DB::table('rentals')->whereIn('id', $data['ids'])->update(['archived_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function unarchive(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:rentals,id']);
        DB::table('rentals')->whereIn('id', $data['ids'])->update(['archived_at' => null]);
        return response()->json(['success' => true]);
    }
}


