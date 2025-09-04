@extends('layouts.vertical', ['title' => 'Booking Settings'])

@section('content')
<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">Booking Settings</h4>
    </div>
    <div class="text-end">
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="javascript: void(0);">Settings</a></li>
            <li class="breadcrumb-item active">Booking</li>
        </ol>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Insurance & Damage Waiver Options</h5>
        <a href="{{ route('booking-settings.create') }}" class="btn btn-primary">Add Option</a>
    </div>
    <div class="card-body">
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th style="width:30px;"></th>
                    <th>Type</th>
                    <th>Label</th>
                    <th>Price</th>
                    <th>Enabled</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="settingsTbody">
                @forelse($options as $opt)
                    <tr data-id="{{ $opt->id }}">
                        <td class="drag-handle" style="cursor:move; text-align:center;">⇅</td>
                        <td>{{ \Illuminate\Support\Str::title(str_replace('_',' ',$opt->type)) }}</td>
                        <td>{{ $opt->label }}</td>
                        <td>{{ number_format($opt->price, 2) }}</td>
                        <td>
                            @if($opt->enabled)
                                <span class="badge bg-success">Enabled</span>
                            @else
                                <span class="badge bg-secondary">Disabled</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('booking-settings.edit', $opt->id) }}" class="btn btn-sm btn-primary">Edit</a>
                            <form action="{{ route('booking-settings.destroy', $opt->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No options defined</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Notification Settings</h5>
    </div>
    <div class="card-body">
        <form id="notifForm">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label d-block">Email Notifications</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="email_enabled" {{ ($notif->email_enabled ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="email_enabled">Enable/Disable all system emails</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label d-block">Twilio SMS Notifications</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="sms_enabled" {{ ($notif->sms_enabled ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="sms_enabled">Enable/Disable all SMS messages</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label d-block">Mobile Push (FCM)</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="fcm_enabled" {{ ($notif->fcm_enabled ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="fcm_enabled">Enable/Disable all mobile push notifications</label>
                    </div>
                </div>
            </div>
            <button type="button" id="saveNotif" class="btn btn-primary mt-3">Save</button>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Rental Booking Email Content</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('booking-settings.save-email-content') }}">
            @csrf
            <div class="mb-3">
                <label for="email_content" class="form-label">Email Paragraph Content</label>
                <textarea name="email_content" id="email_content" class="form-control" rows="5" placeholder="Enter confirmation paragraph..." required>{{ old('email_content', $emailContent) }}</textarea>
                <div class="form-text">This text appears in the booking confirmation email body.</div>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Chat Initial Message</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('booking-settings.save-chat-initial') }}">
            @csrf
            <div class="mb-3">
                <label for="chat_initial_message" class="form-label">Initial Message</label>
                <textarea name="chat_initial_message" id="chat_initial_message" class="form-control" rows="3" placeholder="Hi! A GDV team member will be with you shortly." required>{{ old('chat_initial_message', $chatInitial) }}</textarea>
                <div class="form-text">This message is auto-sent when a new chat conversation starts.</div>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Twilio SMS Templates</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('booking-settings.save-sms-templates') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="sms_booking_confirmation">Booking Confirmation SMS</label>
                <textarea name="sms_booking_confirmation" id="sms_booking_confirmation" class="form-control" rows="2" placeholder="Type the exact SMS text to send">{{ old('sms_booking_confirmation', $smsBooking) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label" for="sms_status_confirmed">Status: Confirmed</label>
                <textarea name="sms_status_confirmed" id="sms_status_confirmed" class="form-control" rows="2" placeholder="Type the exact SMS text to send">{{ old('sms_status_confirmed', $smsConfirmed) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label" for="sms_status_out_for_delivery">Status: Out For Delivery</label>
                <textarea name="sms_status_out_for_delivery" id="sms_status_out_for_delivery" class="form-control" rows="2" placeholder="Type the exact SMS text to send">{{ old('sms_status_out_for_delivery', $smsOutForDelivery) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label" for="sms_status_delivered">Status: Delivered</label>
                <textarea name="sms_status_delivered" id="sms_status_delivered" class="form-control" rows="2" placeholder="Type the exact SMS text to send">{{ old('sms_status_delivered', $smsDelivered) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label" for="sms_status_cancelled">Status: Cancelled</label>
                <textarea name="sms_status_cancelled" id="sms_status_cancelled" class="form-control" rows="2" placeholder="Type the exact SMS text to send">{{ old('sms_status_cancelled', $smsCancelled) }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
 document.addEventListener('DOMContentLoaded', function(){
    const tbody = document.getElementById('settingsTbody');
    if(!tbody) return;
    const sortable = new Sortable(tbody, {
        handle: '.drag-handle',
        animation: 150,
        onEnd: async function(){
            const rows = Array.from(tbody.querySelectorAll('tr[data-id]'));
            const orders = rows.map((row, idx) => ({ id: parseInt(row.getAttribute('data-id')), sort_order: idx }));
            try{
                await fetch('{{ route('booking-settings.reorder') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ orders })
                });
            }catch(e){ /* ignore */ }
        }
    });
 });

  // Save global notifications
  const saveBtn = document.getElementById('saveNotif');
  if (saveBtn) {
    saveBtn.addEventListener('click', async function(){
      const payload = {
        email_enabled: document.getElementById('email_enabled').checked,
        sms_enabled: document.getElementById('sms_enabled').checked,
        fcm_enabled: document.getElementById('fcm_enabled').checked,
      };
      try{
        const resp = await fetch('{{ route('booking-settings.save-notifications') }}', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
          body: JSON.stringify(payload)
        });
        const ok = resp.ok;
        const modalId = 'notifModal';
        let modal = document.getElementById(modalId);
        if(!modal){
          modal = document.createElement('div');
          modal.id = modalId;
          modal.className = 'modal fade';
          modal.tabIndex = -1;
          modal.innerHTML = `
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Notification Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <p id="notifModalMsg"></p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>`;
          document.body.appendChild(modal);
        }
        const msg = ok ? 'Settings saved successfully.' : 'Failed to save settings.';
        document.getElementById('notifModalMsg').textContent = msg;
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
      }catch(e){
        let modal = document.getElementById('notifModal');
        if(!modal){
          modal = document.createElement('div');
          modal.id = 'notifModal';
          modal.className = 'modal fade';
          modal.tabIndex = -1;
          modal.innerHTML = '<div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Notification Settings</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p id="notifModalMsg"></p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div></div></div>';
          document.body.appendChild(modal);
        }
        document.getElementById('notifModalMsg').textContent = 'Failed to save settings.';
        (new bootstrap.Modal(modal)).show();
      }
    });
  }
</script>
@endsection 