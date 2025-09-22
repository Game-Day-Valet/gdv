@extends('layouts.vertical', ['title' => 'Archived Rentals'])

@section('content')
<style>
.folder-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s ease;
    height: 100%;
    display: block;
}
.folder-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
    text-decoration: none;
    color: inherit;
}
.folder-icon {
    font-size: 48px;
    color: #3b82f6;
    margin-bottom: 15px;
}
.folder-title {
    font-size: 18px;
    font-weight: 600;
    color: #111827;
    margin-bottom: 8px;
}
.folder-info {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 5px;
}
.folder-count {
    background: #eef2ff;
    color: #4338ca;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    display: inline-block;
    margin-top: 10px;
}
</style>

<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
  <div class="flex-grow-1">
    <h4 class="fs-18 fw-semibold m-0">Archived Rentals</h4>
    <div class="meta">Folders grouped by tournament. Clean, simple, and fast to scan.</div>
  </div>
</div>

<div class="row g-4">
@forelse($folders as $tournamentName => $list)
  @php
    $first = optional($list)->first();
    $tournament = optional($first)->tournament; // may be null if deleted
    $sport = optional($tournament)->sport; // may be null if deleted
    $sportName = optional($sport)->name;
    $start = optional($tournament)->start_date;
    $end = optional($tournament)->end_date;
    $lastArchived = $list->max('archived_at');
    $folderUrl = ($first && $first->tournament_id)
        ? route('rental-archive.folder', $first->tournament_id)
        : '#';
    $folderTitle = $tournamentName ?: (optional($tournament)->name ?? 'Deleted Tournament');
  @endphp
  <div class="col-xl-4 col-lg-6">
    <a class="folder-card" href="{{ $folderUrl }}">
      <div class="text-center">
        <div class="folder-icon">
          <i class="fas fa-folder"></i>
        </div>
        <div class="folder-title">{{ $folderTitle }}</div>
        @if($sportName)
          <div class="folder-info"><i class="fas fa-trophy"></i> {{ $sportName }}</div>
        @endif
        @if($start || $end)
          <div class="folder-info">
            <i class="fas fa-calendar"></i>
            {{ $start ? \Carbon\Carbon::parse($start)->format('d M Y') : '' }}
            @if($end) - {{ \Carbon\Carbon::parse($end)->format('d M Y') }} @endif
          </div>
        @endif
        @if($lastArchived)
          <div class="folder-info"><i class="fas fa-clock"></i> Last archived {{ \Carbon\Carbon::parse($lastArchived)->diffForHumans() }}</div>
        @endif
        <div class="folder-count">{{ $list->count() }} archived bookings</div>
      </div>
    </a>
  </div>
@empty
  <div class="col-12"><div class="alert alert-info">No archived bookings yet.</div></div>
@endforelse
</div>
@endsection


