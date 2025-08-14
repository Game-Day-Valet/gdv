<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Booking - Game Day Valet</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #dc3545;
            --secondary-color: #6b7280;
            --dark-color: #111827;
            --light-gray: #f8f9fa;
            --border-color: #e5e7eb;
        }

        *, *::before, *::after { box-sizing: border-box; }
        body { font-family:'Inter', system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; margin:0; color:var(--dark-color); background:#ffffff; }

        /* Header copied from sports/tournaments */
        .header { background-color: var(--primary-color); color:#fff; padding:20px 0; box-shadow:0 2px 10px rgba(0,0,0,.1); }
        .header-content { max-width:1200px; margin:0 auto; padding:0 20px; display:flex; justify-content:space-between; align-items:center; }
        .logo { font-size:1.5rem; font-weight:700; }
        .user-menu { display:flex; align-items:center; gap:20px; }
        .user-name { color:#fff; font-weight:500; }
        .nav-btn { background:rgba(255,255,255,.2); border:1px solid rgba(255,255,255,.3); color:#fff; padding:8px 16px; border-radius:8px; text-decoration:none; transition:.2s; }
        .nav-btn:hover { background:rgba(255,255,255,.3); }

        .main-container { max-width:1200px; margin:0 auto; padding:40px 20px; }
        .page-title { text-align:center; margin-bottom:20px; }
        .page-title h1 { font-size:2.0rem; font-weight:800; margin:0 0 6px; }
        .page-title p { color:var(--secondary-color); margin:0; }

        .layout { display:grid; grid-template-columns:minmax(0,1.2fr) minmax(0,.8fr); gap:24px; align-items:start; }
        @media (max-width: 980px) { .layout { grid-template-columns:1fr; } }

        .card { background:#fff; border:1.6px solid var(--border-color); border-radius:16px; padding:18px; }
        .section-title { font-weight:900; letter-spacing:.08em; font-size:14px; color:var(--dark-color); margin:6px 0 12px; }
        .row-two { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:14px; }
        @media (max-width: 680px) { .row-two { grid-template-columns:1fr; } }
        .label { display:block; font-size:12px; letter-spacing:.14em; color:#94a3b8; margin:12px 0 8px; font-weight:800; }
        .input, .select { width:100%; display:block; border:1.6px solid var(--border-color); border-radius:12px; padding:12px 14px; font-size:15px; outline:none; transition:border-color .2s, box-shadow .2s; }
        .input:focus, .select:focus { border-color:var(--primary-color); box-shadow:0 0 0 3px rgba(220,53,69,.15); }

        .items-grid { display:grid; grid-template-columns:1fr; gap:12px; }
        @media (min-width:800px){ .items-grid { grid-template-columns:1fr 1fr; } }
        .item-row { border:1px solid var(--border-color); border-radius:12px; padding:12px; display:flex; justify-content:space-between; align-items:center; gap:14px; flex-wrap:wrap; }
        .item-meta { display:flex; flex-direction:column; flex:1 1 auto; min-width:0; }
        .item-title { font-weight:700; }
        .item-price { color:var(--primary-color); font-weight:800; }
        .qty { display:flex; align-items:center; gap:8px; flex:0 0 auto; }
        .qty button { width:34px; height:34px; border:1px solid var(--border-color); background:#fff; border-radius:8px; cursor:pointer; }

        .summary { position:sticky; top:20px; height:fit-content; }
        @media (max-width: 980px) { .summary { position:static; } }
        .sum-row { display:flex; justify-content:space-between; align-items:center; margin:6px 0; }
        .sum-total { border-top:2px solid var(--primary-color); padding-top:12px; margin-top:6px; font-weight:900; color:var(--primary-color); font-size:18px; }
        .btn-primary { width:100%; border:0; border-radius:12px; padding:14px; background:var(--primary-color); color:#fff; font-weight:900; cursor:pointer; box-shadow:0 10px 24px rgba(220,53,69,.24); }
        .btn-primary:hover { filter:brightness(.95); }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo"><i class="fas fa-trophy"></i> Rental System</div>
            <div class="user-menu">
                <span class="user-name">{{ session('user.name', 'User') }}</span>
                <a href="{{ route('rentalsystem.profile') }}" class="nav-btn"><i class="fas fa-user"></i> Profile</a>
                <a href="{{ route('rentalsystem.logout') }}" class="nav-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </header>

    <div class="main-container">
        <div class="page-title">
            <h1>Rental Booking</h1>
            <p>Fill details, choose items/bundles, and confirm your booking</p>
        </div>

        <form id="bookingForm" method="POST" action="{{ route('rentalsystem.rental.create') }}">
            @csrf
            <input type="hidden" name="tournament_id" value="{{ $tournamentId }}">

            <div class="layout">
                <div>
                    <div class="card">
                        <div class="row-two">
                            <div>
                                <label class="label">TEAM NAME</label>
                                <input class="input" type="text" name="team_name" placeholder="Team name" required>
                            </div>
                            <div>
                                <label class="label">COACH NAME</label>
                                <input class="input" type="text" name="coach_name" placeholder="Coach name" required>
                            </div>
                        </div>
                        <div class="row-two">
                            <div>
                                <label class="label">FIELD NUMBER</label>
                                <input class="input" type="text" name="field_number" placeholder="Field number">
                            </div>
                            <div></div>
                        </div>
                    </div>

                    <div class="card" style="margin-top:18px;">
                        <div class="section-title">ITEMS</div>
                        <div class="items-grid">
                            @forelse(($availableItems ?? []) as $it)
                                <div class="item-row" data-type="item" data-id="{{ $it->id }}" data-price="{{ (float)($it->price ?? 0) }}">
                                    <div class="item-meta">
                                        <div class="item-title">{{ $it->name }}</div>
                                        <div class="item-price">${{ number_format((float)($it->price ?? 0), 2) }}</div>
                                    </div>
                                    <div class="qty">
                                        <button type="button" class="qty-dec">-</button>
                                        <span class="qty-val">0</span>
                                        <button type="button" class="qty-inc">+</button>
                                        <input type="hidden" name="items[{{ $it->id }}]" value="0">
                                    </div>
                                </div>
                            @empty
                                <div class="meta">No items available</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="card" style="margin-top:18px;">
                        <div class="section-title">BUNDLES</div>
                        <div class="items-grid">
                            @forelse(($availableBundles ?? []) as $bd)
                                <div class="item-row" data-type="bundle" data-id="{{ $bd->id }}" data-price="{{ (float)($bd->price ?? 0) }}">
                                    <div class="item-meta">
                                        <div class="item-title">{{ $bd->name }}</div>
                                        <div class="item-price">${{ number_format((float)($bd->price ?? 0), 2) }}</div>
                                    </div>
                                    <div class="qty">
                                        <button type="button" class="qty-dec">-</button>
                                        <span class="qty-val">0</span>
                                        <button type="button" class="qty-inc">+</button>
                                        <input type="hidden" name="bundles[{{ $bd->id }}]" value="0">
                                    </div>
                                </div>
                            @empty
                                <div class="meta">No bundles available</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="card" style="margin-top:18px;">
                        <div class="section-title">DROP-OFF</div>
                        <div class="row-two">
                            <div>
                                <label class="label">DATE</label>
                                <input class="input" type="date" name="drop_off_date" required>
                            </div>
                            <div>
                                <label class="label">TIME</label>
                                <input class="input" type="time" name="drop_off_time" required>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="margin-top:18px;">
                        <div class="section-title">INSURANCE OPTION</div>
                        <label style="display:flex;gap:10px;align-items:center;">
                            <input type="radio" name="insurance_option" value="none" checked> None
                        </label>
                        <label style="display:flex;gap:10px;align-items:center;">
                            <input type="radio" name="insurance_option" value="29"> 3‑day warranty — $29.00
                        </label>
                        <label style="display:flex;gap:10px;align-items:center;">
                            <input type="radio" name="insurance_option" value="49"> 7‑day warranty — $49.00 (recommended)
                        </label>
                    </div>

                    <div class="card" style="margin-top:18px;">
                        <div class="section-title">DAMAGE WAIVER</div>
                        <label style="display:flex;gap:10px;align-items:center;">
                            <input type="checkbox" name="damage_waiver" value="20"> Add damage waiver — $20.00
                        </label>
                    </div>

                    <div class="card" style="margin-top:18px;">
                        <div class="section-title">PAYMENT METHOD</div>
                        <label style="display:flex;gap:10px;align-items:center;">
                            <input type="radio" name="payment_method" value="stripe" checked> Stripe
                        </label>
                    </div>
                </div>

                <div class="summary">
                    <div class="card">
                        <div class="section-title" style="text-align:center;">Summary</div>
                        <div class="sum-row"><span>Items Subtotal</span><span id="itemsSubtotal">$0.00</span></div>
                        <div class="sum-row"><span>Bundles Subtotal</span><span id="bundlesSubtotal">$0.00</span></div>
                        <div class="sum-row"><span>Insurance</span><span id="insuranceAmount">$0.00</span></div>
                        <div class="sum-row"><span>Damage Waiver</span><span id="waiverAmount">$0.00</span></div>
                        <div class="sum-total sum-row"><span>Total</span><span id="totalAmount">$0.00</span></div>
                        <div style="height:10px"></div>
                        <button type="submit" class="btn-primary">Confirm Booking</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function formatUSD(v){ return `$${Number(v).toFixed(2)}`; }

        function recalc(){
            let itemsSubtotal = 0, bundlesSubtotal = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const price = parseFloat(row.getAttribute('data-price')) || 0;
                const qty = parseInt(row.querySelector('.qty-val').textContent) || 0;
                const type = row.getAttribute('data-type');
                const line = price * qty;
                if (type === 'item') itemsSubtotal += line; else bundlesSubtotal += line;
            });
            const insuranceVal = parseFloat(document.querySelector('input[name="insurance_option"]:checked')?.value || 0);
            const waiverVal = document.querySelector('input[name="damage_waiver"]:checked') ? 20 : 0;
            const total = itemsSubtotal + bundlesSubtotal + insuranceVal + waiverVal;
            document.getElementById('itemsSubtotal').textContent = formatUSD(itemsSubtotal);
            document.getElementById('bundlesSubtotal').textContent = formatUSD(bundlesSubtotal);
            document.getElementById('insuranceAmount').textContent = formatUSD(insuranceVal);
            document.getElementById('waiverAmount').textContent = formatUSD(waiverVal);
            document.getElementById('totalAmount').textContent = formatUSD(total);
        }

        document.querySelectorAll('.item-row').forEach(row => {
            const dec = row.querySelector('.qty-dec');
            const inc = row.querySelector('.qty-inc');
            const val = row.querySelector('.qty-val');
            const input = row.querySelector('input[type="hidden"]');
            dec.addEventListener('click', () => { let q = Math.max(0, (parseInt(val.textContent)||0) - 1); val.textContent = q; input.value = q; recalc(); });
            inc.addEventListener('click', () => { let q = (parseInt(val.textContent)||0) + 1; val.textContent = q; input.value = q; recalc(); });
        });

        document.querySelectorAll('input[name="insurance_option"]').forEach(r => r.addEventListener('change', recalc));
        const waiver = document.querySelector('input[name="damage_waiver"]');
        if (waiver) waiver.addEventListener('change', recalc);

        recalc();
    </script>
</body>
</html> 