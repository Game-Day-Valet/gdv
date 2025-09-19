@extends('layouts.vertical', ['title' => 'Archived Folder'])

@section('content')
<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
  <div class="flex-grow-1">
    <h4 class="fs-18 fw-semibold m-0">Archived Rentals - Folder</h4>
  </div>
  <div class="text-end">
    <button id="unarchiveSelected" class="btn btn-success">Restore Selected</button>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th style="width:28px"><input type="checkbox" id="checkAll"></th>
            <th>User</th>
            <th>Tournament</th>
            <th>Tournament Dates</th>
            <th>Coach</th>
            <th>Team</th>
            <th>Payment Status</th>
            <th>Status</th>
            <th>Booking Date</th>
            <th>Total</th>
            <th>Archived At</th>
          </tr>
        </thead>
        <tbody>
          @foreach($rentals as $r)
          @php
            $tStart = optional($r->tournament)->start_date;
            $tEnd = optional($r->tournament)->end_date;
            $fmt = function($val){
              if(!$val) return null;
              if($val instanceof \Carbon\Carbon) return $val->format('d M Y');
              try { return \Carbon\Carbon::parse($val)->format('d M Y'); } catch (\Throwable $e) { return null; }
            };
            $paymentStatusClass = match($r->payment_status) {
                'completed' => 'badge bg-success',
                'pending' => 'badge bg-warning', 
                'paid' => 'badge bg-primary',
                default => 'badge bg-secondary'
            };
            $statusClass = match($r->status) {
              'delivered' => 'badge bg-success',
              'confirmed' => 'badge bg-info',
              'out_for_delivery' => 'badge bg-primary',
              'cancelled' => 'badge bg-danger',
              'pending' => 'badge bg-warning',
              default => 'badge bg-secondary'
            };
          @endphp
          <tr>
            <td><input type="checkbox" class="rowCheck" value="{{ $r->id }}"></td>
            <td>{{ optional($r->user)->name ?? 'N/A' }}<br><small class="text-muted">{{ optional($r->user)->email ?? 'N/A' }}</small></td>
            <td>{{ optional($r->tournament)->name ?? 'N/A' }}</td>
            <td>
              @php($s = $fmt($tStart))
              @php($e = $fmt($tEnd))
              @if($s || $e)
                {{ $s }}@if($e) - {{ $e }}@endif
              @else
                N/A
              @endif
            </td>
            <td>{{ $r->coach_name ?? 'N/A' }}</td>
            <td>{{ $r->team_name_with_age_group ?? 'N/A' }}</td>
            <td>
              <span class="{{ $paymentStatusClass }}">
                {{ ucfirst($r->payment_status ?? 'pending') }}
              </span>
            </td>
            <td>
              <span class="{{ $statusClass }}">
                {{ ucfirst(str_replace('_', ' ', $r->status ?? 'pending')) }}
              </span>
            </td>
            <td>{{ optional($r->created_at)->format('d M Y') }}</td>
            <td>${{ number_format($r->total_amount ?? 0, 2) }}</td>
            <td>
              @php($arch = $r->archived_at)
              @if($arch instanceof \Carbon\Carbon)
                {{ $arch->format('d M Y, h:i A') }}
              @elseif(!empty($arch))
                {{ (function($a){ try { return \Carbon\Carbon::parse($a)->format('d M Y, h:i A'); } catch (\Throwable $e) { return 'N/A'; } })($arch) }}
              @else
                N/A
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
const checkAll = document.getElementById('checkAll');
const btn = document.getElementById('unarchiveSelected');
checkAll?.addEventListener('change', ()=>{
  document.querySelectorAll('.rowCheck').forEach(cb=>cb.checked = checkAll.checked);
});
btn?.addEventListener('click', async ()=>{
  const ids = Array.from(document.querySelectorAll('.rowCheck:checked')).map(cb=>parseInt(cb.value));
  if(ids.length===0){ return; }
  // confirm modal
  const ok = await confirmAction('Are you sure you want to restore selected bookings?');
  if(!ok) return;
  try{
    const res = await fetch('{{ route('rental-archive.unarchive') }}', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body: JSON.stringify({ids}) });
    if(res.ok){ location.reload(); }
  }catch(e){ alert('Failed to restore'); }
});

async function confirmAction(msg){
  return new Promise(resolve=>{
    const wrap = document.createElement('div');
    wrap.style.cssText='position:fixed;inset:0;background:rgba(17,24,39,.55);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;z-index:10550;';
    wrap.innerHTML = `
    <div style="background:#fff;border-radius:16px;max-width:460px;width:92%;padding:22px 20px;border:1px solid #e5e7eb;box-shadow:0 18px 40px rgba(17,24,39,.18);">
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
        <div style="width:40px;height:40px;border-radius:10px;background:#fef3c7;display:flex;align-items:center;justify-content:center;color:#b45309;">
          <i class="fas fa-folder-open"></i>
        </div>
        <div style="font-weight:700;font-size:16px;">Please confirm</div>
      </div>
      <div style="color:#6b7280;margin-bottom:14px;">${msg}</div>
      <div style="display:flex;gap:10px;justify-content:flex-end;">
        <button id="cCancel" class="btn btn-light" style="border:1px solid #e5e7eb;">Cancel</button>
        <button id="cOk" class="btn btn-primary" style="background-color:#3b82f6;border-color:#3b82f6;">Confirm</button>
      </div>
    </div>`;
    document.body.appendChild(wrap);
    wrap.querySelector('#cCancel').addEventListener('click', ()=>{ wrap.remove(); resolve(false); });
    wrap.querySelector('#cOk').addEventListener('click', ()=>{ wrap.remove(); resolve(true); });
  });
}
</script>
@endsection


