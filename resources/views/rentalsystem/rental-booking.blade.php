<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Booking - Game Day Valet</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
<!-- App favicon -->
<link rel="shortcut icon" href="/images/logo-sm.png">
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
                                <label class="label">TEAM NAME <span class="text-danger">*</span></label>
                                <input class="input" type="text" name="team_name" placeholder="Team name" required>
                            </div>
                            <div>
                                <label class="label">COACH NAME <span class="text-danger">*</span></label>
                                <input class="input" type="text" name="coach_name" placeholder="Coach name" required>
                            </div>
                        </div>
                        <div class="row-two">
                            <div>
                                <label class="label">FIELD NUMBER <span class="text-danger">*</span></label>
                                <input class="input" type="text" name="field_number" placeholder="Field number" required>
                            </div>
                            <div></div>
                        </div>
                    </div>

                    <div class="card" style="margin-top:18px;">
                        <div class="section-title">ITEMS <span class="text-danger">*</span></div>
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
                                        <input type="hidden" name="items[{{ $it->id }}]" value="0" class="item-input">
                                    </div>
                                </div>
                            @empty
                                <div class="meta">No items available</div>
                            @endforelse
                        </div>
                        <div class="text-danger small mt-2" id="items-error" style="display: none;">Please select at least one item or bundle</div>
                    </div>

                    <div class="card" style="margin-top:18px;">
                        <div class="section-title">BUNDLES <span class="text-danger">*</span></div>
                        <div class="items-grid">
                            @forelse(($availableBundles ?? []) as $bd)
                                <div class="item-row" data-type="bundle" data-id="{{ $bd->id }}" data-price="{{ (float)($bd->price ?? 0) }}">
                                    <div class="item-meta">
                                        <div class="item-title">{{ $bd->name }}</div>
                                        <div class="item-price">${{ number_format((float)($bd->price ?? 0), 2) }}</div>
                                    </div>
                                    <div class="qty">
                                        <label style="display:flex;gap:10px;align-items:center;margin:0;">
                                            <input type="checkbox" name="bundles[{{ $bd->id }}]" value="1" class="bundle-input">
                                            <span>Select</span>
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="meta">No bundles available</div>
                            @endforelse
                        </div>
                        <div class="text-danger small mt-2" id="bundles-error" style="display: none;">Please select at least one item or bundle</div>
                    </div>

                    <!-- Promo Code - moved here between Bundles and Drop-Off -->
                    <div class="card" style="margin-top:18px;">
                        <div class="section-title">PROMO CODE</div>
                        <div class="row-two">
                            <div>
                                <label class="label">ENTER CODE</label>
                                <input type="text" class="input" id="promo_code" name="promo_code" placeholder="Enter code">
                            </div>
                            <div style="display:flex; align-items:flex-end;">
                                <button type="button" id="validateCouponBtn" class="btn-primary" style="width:auto; padding:12px 18px;">Apply</button>
                            </div>
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
                        <input type="hidden" name="total_amount" id="total_amount_input" value="0">
                        <div class="sum-row" id="discountRow" style="display:none; color: var(--primary-color); font-weight:700;">
                            <span>Discount</span><span id="discountAmount">-$0.00</span>
                        </div>
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
            
            // Calculate items subtotal
            document.querySelectorAll('.item-row[data-type="item"]').forEach(row => {
                const price = parseFloat(row.getAttribute('data-price')) || 0;
                const qty = parseInt(row.querySelector('.qty-val').textContent) || 0;
                const line = price * qty;
                itemsSubtotal += line;
            });
            
            // Calculate bundles subtotal (checkboxes)
            document.querySelectorAll('.item-row[data-type="bundle"]').forEach(row => {
                const price = parseFloat(row.getAttribute('data-price')) || 0;
                const checkbox = row.querySelector('input[type="checkbox"]');
                if (checkbox && checkbox.checked) {
                    bundlesSubtotal += price;
                }
            });
            
            // Fix insurance calculation - handle "none" value properly
            const insuranceInput = document.querySelector('input[name="insurance_option"]:checked');
            let insuranceVal = 0;
            if (insuranceInput && insuranceInput.value !== 'none') {
                insuranceVal = parseFloat(insuranceInput.value) || 0;
            }
            
            const waiverVal = document.querySelector('input[name="damage_waiver"]:checked') ? 20 : 0;
            let total = itemsSubtotal + bundlesSubtotal + insuranceVal + waiverVal;

            // Apply promo discount if available
            const discountValue = parseFloat(window.__appliedDiscount || 0) || 0;
            const discountType = window.__appliedDiscountType || null; // 'fixed' or 'percent'
            let discountApplied = 0;
            if (discountType === 'percent') {
                discountApplied = Math.min(total, (total * (discountValue / 100)));
            } else if (discountType === 'fixed') {
                discountApplied = Math.min(total, discountValue);
            }
            total = total - discountApplied;
            
            document.getElementById('itemsSubtotal').textContent = formatUSD(itemsSubtotal);
            document.getElementById('bundlesSubtotal').textContent = formatUSD(bundlesSubtotal);
            document.getElementById('insuranceAmount').textContent = formatUSD(insuranceVal);
            document.getElementById('waiverAmount').textContent = formatUSD(waiverVal);
            const discountRow = document.getElementById('discountRow');
            const discountAmount = document.getElementById('discountAmount');
            if (discountApplied > 0) {
                discountRow.style.display = 'flex';
                discountAmount.textContent = `-${formatUSD(discountApplied).replace('$','')}`;
            } else {
                discountRow.style.display = 'none';
            }
            document.getElementById('totalAmount').textContent = formatUSD(total);
            // write final total to hidden input for server/stripe
            const totalInput = document.getElementById('total_amount_input');
            if (totalInput) totalInput.value = total.toFixed(2);
        }

        // Event listeners for items (quantity buttons)
        document.querySelectorAll('.item-row[data-type="item"]').forEach(row => {
            const dec = row.querySelector('.qty-dec');
            const inc = row.querySelector('.qty-inc');
            const val = row.querySelector('.qty-val');
            const input = row.querySelector('input[type="hidden"]');
            dec.addEventListener('click', () => { 
                let q = Math.max(0, (parseInt(val.textContent)||0) - 1); 
                val.textContent = q; 
                input.value = q; 
                recalc(); 
                validateForm();
            });
            inc.addEventListener('click', () => { 
                let q = (parseInt(val.textContent)||0) + 1; 
                val.textContent = q; 
                input.value = q; 
                recalc(); 
                validateForm();
            });
        });

        // Event listeners for bundles (checkboxes)
        document.querySelectorAll('.item-row[data-type="bundle"] input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                recalc();
                validateForm();
            });
        });

        // Event listeners for insurance and waiver
        document.querySelectorAll('input[name="insurance_option"]').forEach(r => r.addEventListener('change', recalc));
        const waiver = document.querySelector('input[name="damage_waiver"]');
        if (waiver) waiver.addEventListener('change', recalc);

        // Form validation
        function validateForm() {
            let hasItems = false;
            let hasBundles = false;
            let isValid = true;

            // Check if any items are selected with quantity > 0
            document.querySelectorAll('.item-row[data-type="item"]').forEach(row => {
                const qty = parseInt(row.querySelector('.qty-val').textContent) || 0;
                if (qty > 0) hasItems = true;
            });

            // Check if any bundles are selected
            document.querySelectorAll('.item-row[data-type="bundle"] input[type="checkbox"]').forEach(checkbox => {
                if (checkbox.checked) hasBundles = true;
            });

            // Show/hide error messages
            const itemsError = document.getElementById('items-error');
            const bundlesError = document.getElementById('bundles-error');
            
            if (!hasItems && !hasBundles) {
                itemsError.style.display = 'block';
                bundlesError.style.display = 'block';
                isValid = false;
            } else {
                itemsError.style.display = 'none';
                bundlesError.style.display = 'none';
            }

            return isValid;
        }

        // Form submission validation
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                showModal('Please select at least one item or bundle before proceeding.');
                return false;
            }
            // ensure promo_code input remains set
        });

        // Validate coupon via API and apply discount on frontend only
        document.getElementById('validateCouponBtn').addEventListener('click', async function() {
            // Require at least one item or bundle selected before validating
            let hasItems = false, hasBundles = false;
            document.querySelectorAll('.item-row[data-type="item"]').forEach(row => {
                const qty = parseInt(row.querySelector('.qty-val').textContent) || 0;
                if (qty > 0) hasItems = true;
            });
            document.querySelectorAll('.item-row[data-type="bundle"] input[type="checkbox"]').forEach(chk => {
                if (chk.checked) hasBundles = true;
            });
            if (!hasItems && !hasBundles) {
                // show existing errors
                document.getElementById('items-error').style.display = 'block';
                document.getElementById('bundles-error').style.display = 'block';
                showModal('Please select at least one item or bundle before validating a promo code.');
                return;
            }
            const code = (document.getElementById('promo_code').value || '').trim();
            if (!code) { showModal('Please enter a promo code.'); return; }
            try {
                const resp = await fetch('/api/coupon/validate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ promo_code: code, user_id: {{ auth()->check() ? auth()->id() : 'null' }} })
                });
                const data = await resp.json();
                if (resp.ok && data && data.success) {
                    // discount type: fixed/percent; value: number
                    const type = data.data?.discount_type;
                    const value = parseFloat(data.data?.discount_value || 0);
                    if ((type === 'fixed' || type === 'percent') && value > 0) {
                        window.__appliedDiscountType = type;
                        window.__appliedDiscount = value;
                        showModal('Coupon applied successfully.');
                        recalc();
                    } else {
                        showModal('Invalid discount returned.');
                    }
                } else {
                    window.__appliedDiscountType = null;
                    window.__appliedDiscount = 0;
                    const msg = data?.errors?.promo_code?.[0] || data?.message || 'Coupon validation failed';
                    showModal(msg);
                    recalc();
                }
            } catch (err) {
                showModal('Unable to validate coupon. Please try again.');
            }
        });

        // Simple custom modal
        const modalEl = document.createElement('div');
        modalEl.id = 'appModal';
        modalEl.style.cssText = 'position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.5);z-index:9999;';
        modalEl.innerHTML = `
            <div style="background:#fff;border-radius:14px;min-width:280px;max-width:420px;padding:18px;border:1px solid var(--border-color);box-shadow:0 20px 40px rgba(0,0,0,.18);">
                <div style="font-weight:800;font-size:16px;margin-bottom:8px;color:var(--dark-color);">Notice</div>
                <div id="appModalBody" style="color:var(--secondary-color);line-height:1.5;"></div>
                <div style="display:flex;justify-content:flex-end;margin-top:16px;">
                    <button id="appModalOk" class="btn-primary" style="width:auto;padding:10px 16px;">OK</button>
                </div>
            </div>`;
        document.body.appendChild(modalEl);
        function showModal(message){
            document.getElementById('appModalBody').textContent = message;
            modalEl.style.display = 'flex';
        }
        document.getElementById('appModalOk').addEventListener('click', ()=>{ modalEl.style.display='none'; });
        modalEl.addEventListener('click', (e)=>{ if(e.target === modalEl){ modalEl.style.display='none'; } });
 
        recalc();
        validateForm();
    </script>
</body>
</html> 