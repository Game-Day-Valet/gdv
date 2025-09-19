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

    public function folder($tournamentId)
    {
        $rentals = Rental::with(['user','tournament'])
            ->where('tournament_id', $tournamentId)
            ->whereNotNull('archived_at')
            ->orderBy('created_at','desc')
            ->get();
        return view('rental_archive.folder', compact('rentals'));
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


