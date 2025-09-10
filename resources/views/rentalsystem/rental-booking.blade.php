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

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            color: var(--dark-color);
            background: #ffffff;
        }

        /* Header copied from sports/tournaments */
        .header {
            background-color: var(--primary-color);
            color: #fff;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logo { justify-self: start; }
        .header-center { text-align:center; font-weight:900; letter-spacing:.4px; justify-self:center; font-size:18px; }
        .user-menu { justify-self: end; }

        /* Right side policy card */
        .policy-card { background:#f9fafb; border:1px solid var(--border-color); border-radius:16px; padding:14px 16px; }
        .policy-card .policy-title { font-weight:800; text-align:center; margin-bottom:8px; }
        .policy-card ul { list-style:none; padding-left:0; margin:8px 0 0; }
        .policy-card li { display:flex; align-items:center; gap:8px; color:var(--secondary-color); margin:6px 0; }
        .policy-card li i { color:var(--primary-color); }

        .user-name {
            color: #fff;
            font-weight: 500;
        }

        .nav-btn {
            background: rgba(255, 255, 255, .2);
            border: 1px solid rgba(255, 255, 255, .3);
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            transition: .2s;
        }

        .nav-btn:hover {
            background: rgba(255, 255, 255, .3);
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 20px;
        }

        .page-title h1 {
            font-size: 2.0rem;
            font-weight: 800;
            margin: 0 0 6px;
        }

        .page-title p {
            color: var(--secondary-color);
            margin: 0;
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(0, .8fr);
            gap: 24px;
            align-items: start;
        }

        @media (max-width: 980px) {
            .layout {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: #fff;
            border: 1.6px solid var(--border-color);
            border-radius: 16px;
            padding: 18px;
        }

        .section-title {
            font-weight: 900;
            letter-spacing: .08em;
            font-size: 14px;
            color: var(--dark-color);
            margin: 6px 0 12px;
        }

        .row-two {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 14px;
        }

        @media (max-width: 680px) {
            .row-two {
                grid-template-columns: 1fr;
            }
        }

        .label {
            display: block;
            font-size: 12px;
            letter-spacing: .14em;
            color: #94a3b8;
            margin: 12px 0 8px;
            font-weight: 800;
        }

        .input,
        .select {
            width: 100%;
            display: block;
            border: 1.6px solid var(--border-color);
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 15px;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .input:focus,
        .select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(220, 53, 69, .15);
        }

        .items-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        @media (min-width:800px) {
            .items-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .item-row {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            flex-wrap: nowrap;
        }
        @media (max-width: 680px) { .item-row { flex-wrap: wrap; } }

        .item-meta {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-width: 0;
        }

        .item-title {
            font-weight: 700;
        }
        .rs-thumb { width:48px; height:48px; object-fit:cover; border-radius:8px; margin-right:10px; vertical-align:middle; cursor:pointer; }

        .item-price {
            color: var(--primary-color);
            font-weight: 800;
        }

        .qty {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 0 0 auto;
        }

        .qty button {
            width: 34px;
            height: 34px;
            border: 1px solid var(--border-color);
            background: #fff;
            border-radius: 8px;
            cursor: pointer;
        }

        .summary {
            position: sticky;
            top: 20px;
            height: fit-content;
        }

        @media (max-width: 980px) {
            .summary {
                position: static;
            }
        }

        .sum-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 6px 0;
        }

        .sum-total {
            border-top: 2px solid var(--primary-color);
            padding-top: 12px;
            margin-top: 6px;
            font-weight: 900;
            color: var(--primary-color);
            font-size: 18px;
        }

        .btn-primary {
            width: 100%;
            border: 0;
            border-radius: 12px;
            padding: 14px;
            background: var(--primary-color);
            color: #fff;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 10px 24px rgba(220, 53, 69, .24);
        }

        .btn-primary:hover {
            filter: brightness(.95);
        }

        /* Bundles new UI */
        .bundle-section { background: linear-gradient(180deg, #ffe08a 0%, #ffb835 100%); border-radius: 16px; border: 1px solid #ffd166; padding: 16px; }
        .bundle-header { display:flex; align-items:center; gap:10px; font-weight:900; color:#7a4b00; font-size:22px; margin-bottom:12px; }
        .bundle-header i { color:#ffb300; }
        .bundle-row { display:flex; align-items:center; justify-content:space-between; background: rgba(255,255,255,0.25); border-radius:14px; padding:14px; border:1px solid rgba(255,255,255,0.4); margin-bottom:12px; }
        .bundle-left { display:flex; align-items:center; gap:16px; min-width:0; }
        .bundle-thumb { width:96px; height:96px; background:#fff; border-radius:18px; display:flex; align-items:center; justify-content:center; border:1px solid #fff; box-shadow:0 3px 8px rgba(0,0,0,0.05); overflow:hidden; flex:0 0 auto; }
        .bundle-thumb img.rs-thumb { width:90px; height:90px; border-radius:12px; margin:0; }
        .bundle-info h3 { margin:0; font-size:28px; font-weight:800; color:#ffffff; text-shadow: 0 1px 0 rgba(0,0,0,.08); }
        .bundle-desc { color:#fff; opacity:.9; font-size:14px; line-height:1.4; max-height: 2.8em; overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; }
        .bundle-sub { margin-top:6px; color:#fff; opacity:.95; font-size:16px; }
        .bundle-price { color:#fff; font-weight:800; font-size:22px; margin-top:8px; }
        .bundle-qty { display:flex; align-items:center; gap:12px; background: rgba(255,255,255,0.2); border:2px solid #fff; border-radius:14px; padding:10px 14px; }
        .bundle-qty button { width:40px; height:40px; border:0; background:transparent; color:#fff; font-size:28px; line-height:1; cursor:pointer; }
        .bundle-qty .qty-val { font-size:28px; color:#fff; width:36px; text-align:center; }
    </style>
</head>

<body>
    <header class="header">
        <div class="header-content">
            <div class="logo"><i class="fas fa-trophy"></i> Rental System</div>
            <div class="header-center">Game Day Valet (GDV)</div>
            <div class="user-menu">
                @if(Auth::check())
                <span class="user-name">{{ Auth::user()->name }}</span>
                <a href="{{ route('rentalsystem.profile') }}" class="nav-btn"><i class="fas fa-user"></i> Profile</a>
                <a href="{{ route('rentalsystem.logout') }}" class="nav-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                @else
                <a href="{{ route('rentalsystem.signin') }}" class="nav-btn"><i class="fas fa-sign-in-alt"></i> Sign In</a>
                <a href="{{ route('rentalsystem.signup') }}" class="nav-btn"><i class="fas fa-user-plus"></i> Sign Up</a>
                @endif
            </div>
        </div>
    </header>

    <div class="main-container">
        <div class="page-title">
            <h1>Rental Booking</h1>
            <p>Fill details, choose items/bundles, and confirm your booking</p>
        </div>

        @if(isset($tournament) && $tournament)
        <div class="tournament-info" style="text-align: center; margin-bottom: 30px; padding: 20px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 16px; border: 2px solid #dee2e6;">
            <h3 style="margin: 0; color: #dc3545; font-size: 1.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; display:inline-flex; align-items:center; gap:10px;">
                @if(!empty($tournament->image))
                    <img id="tournamentImg" src="{{ asset('storage/'.$tournament->image) }}" data-fullurl="{{ asset('storage/'.$tournament->image) }}" alt="{{ $tournament->name }}" style="width:60px;height:60px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6; margin-right: 8px; cursor:pointer;">
                @else
                    <i class="fas fa-trophy" style="color: #ffc107;"></i>
                @endif
                <span>{{ $tournament->name ?? 'Tournament' }}</span>
            </h3>
        </div>
        @endif

        <form id="bookingForm" method="POST" action="{{ route('rentalsystem.rental.create') }}">
            @csrf
            <input type="hidden" name="tournament_id" value="{{ $tournamentId }}">

            <!-- Hidden field for discounted total -->
            <input type="hidden" name="total_amount" id="total_amount_input" value="0">

            <div class="layout">
                <div>
                    <div class="card">
                        <div class="row-two">
                            <div>
                                <label class="label">TEAM NAME WITH AGE GROUP <span class="text-danger">*</span></label>
                                <input class="input" type="text" name="team_name_with_age_group" placeholder="Team name with age group" required>
                            </div>
                            <div>
                                <label class="label">COACH NAME <span class="text-danger">*</span></label>
                                <input class="input" type="text" name="coach_name" placeholder="Coach name" required>
                            </div>
                        </div>
                        <div class="row-two">
                            <div>
                                <label class="label">PHONE NUMBER <span class="text-danger">*</span></label>
                                <input class="input" type="tel" name="phone_number" placeholder="e.g., +1 555 123 4567" value="{{ Auth::check() ? (Auth::user()->contact_number ?? '') : '' }}" required>
                                <label style="display:flex;gap:10px;align-items:flex-start;margin-top:10px;">
                                    <input id="sms_opt_in" type="checkbox" name="sms_opt_in" required>
                                    <span>I agree to receive important text notifications about my rental (confirmations, delivery updates, event-day notices). Msg & data rates may apply. Reply STOP to opt out.</span>
                                </label>
                            </div>
                            <div>
                                <label class="label">EMAIL <span class="text-danger">*</span></label>
                                <input class="input" type="email" name="email" placeholder="you@example.com" value="{{ Auth::check() ? (Auth::user()->email ?? '') : '' }}" required>
                                <small class="text-muted">Booking confirmation and updates will be emailed here.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Bundles first, highlighted as best value -->
                    <div class="bundle-section" style="margin-top:18px;">
                        <div class="bundle-header"><i class="fas fa-star"></i> Best Value Bundles</div>
                        @forelse(($availableBundles ?? []) as $bd)
                            <div class="bundle-row" data-type="bundle" data-id="{{ $bd->id }}" data-price="{{ (float)($bd->effective_price ?? $bd->price ?? 0) }}">
                                <div class="bundle-left">
                                    <div class="bundle-thumb">
                                        @if(!empty($bd->image))
                                            <img class="rs-thumb" src="{{ asset('storage/'.$bd->image) }}" data-full="{{ asset('storage/'.$bd->image) }}" alt="{{ $bd->name }}">
                                        @endif
                                    </div>
                                    <div class="bundle-info">
                                        <h3>{{ $bd->name }}</h3>
                                        @if(!empty($bd->description))
                                            <div class="bundle-desc">{!! strip_tags($bd->description) !!}</div>
                                        @endif
                                        <div class="bundle-sub">
                                            @php
                                                $itemsDesc = [];
                                                $bundleItems = $bd->items ?? [];
                                            @endphp
                                            @if(!empty($bundleItems))
                                                @foreach($bundleItems as $bi)
                                                    @php
                                                        $iname = is_array($bi) ? ($bi['name'] ?? '') : ($bi->name ?? '');
                                                        $iqty = 1;
                                                        if (is_object($bi) && isset($bi->pivot)) { $iqty = $bi->pivot->quantity ?? 1; }
                                                        elseif (is_array($bi)) { $iqty = (int)($bi['quantity'] ?? 1); }
                                                        if(!empty($iname)) { $itemsDesc[] = $iqty . ' x ' . $iname; }
                                                    @endphp
                                                @endforeach
                                                Total Items: {{ implode(', ', $itemsDesc) }}
                                            @else
                                                Total Items: 1 x {{ $bd->name }}
                                            @endif
                                        </div>
                                        <div class="bundle-price">${{ number_format((float)($bd->effective_price ?? $bd->price ?? 0), 2) }}</div>
                                    </div>
                                </div>
                                <div class="bundle-qty">
                                    <button type="button" class="qty-dec">−</button>
                                    <span class="qty-val">0</span>
                                    <button type="button" class="qty-inc">+</button>
                                    <input type="hidden" name="bundles[{{ $bd->id }}]" value="0" class="bundle-input">
                                </div>
                            </div>
                        @empty
                            <div class="meta">No bundles available</div>
                        @endforelse
                        <div class="text-danger small mt-2" id="bundles-error" style="display: none;">Please select at least one item or bundle</div>
                    </div>

                    <div class="card" style="margin-top:18px;">
                        <div class="section-title">Or choose items individually</div>
                        <div style="color:#b91c1c;font-weight:800;margin:-6px 0 10px;font-size:18px;">**Prices are for the full tournament weekend**</div>
                        <div class="items-grid">
                            @forelse(($availableItems ?? []) as $it)
                            <div class="item-row" data-type="item" data-id="{{ $it->id }}" data-price="{{ (float)($it->effective_price ?? $it->price ?? 0) }}">
                                <div class="item-meta">
                                    <div class="item-title">
                                        @if(!empty($it->image_url))
                                        <img class="rs-thumb" src="{{ $it->image_url }}" data-full="{{ $it->image_url }}" alt="{{ $it->name }}">
                                        @endif
                                        {{ $it->name }}
                                    </div>
                                    <div class="item-price">${{ number_format((float)($it->effective_price ?? $it->price ?? 0), 2) }}</div>
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

                    <div class="card" style="margin-top:18px;" id="insuranceCard">
                        <div class="section-title">INSURANCE OPTION</div>
                        <div data-insurance-container>
                            <label style="display:flex;gap:10px;align-items:center;">
                                <input type="radio" name="insurance_option" value="none" checked> None
                            </label>
                        </div>
                    </div>

                    <div class="card" style="margin-top:18px;">
                        <div class="section-title">DAMAGE WAIVER</div>
                        <div id="waiverSubtitle" class="text-muted" style="margin-top:4px; display:none; font-size:12px;"></div>
                        <div data-waiver-container>
                            <label style="display:flex;gap:10px;align-items:center;">
                                <input type="checkbox" name="damage_waiver_options[]" value="20" data-price="20" id="waiver_20"> Add damage waiver — $20.00
                            </label>
                        </div>
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
                        <div class="sum-row" id="discountRow" style="display:none; color: var(--primary-color); font-weight:700;">
                            <span>Discount</span><span id="discountAmount">-$0.00</span>
                        </div>
                        <div class="sum-total sum-row"><span>Total</span><span id="totalAmount">$0.00</span></div>
                        <div style="height:10px"></div>
                        <button type="submit" class="btn-primary">Confirm Booking</button>
                    </div>
                    <div class="policy-card" style="margin-top:12px;">
                        <div class="policy-title">Policies</div>
                        <p class="small" style="margin:0;color:#6b7280;">By placing your order you agree to Game Day Valet’s terms.</p>
                        <ul>
                            <li><i class="fas fa-file-contract"></i><a href="{{ route('rentalsystem.terms') }}" target="_blank">Terms & Conditions</a></li>
                            <li><i class="fas fa-shield-alt"></i><a href="{{ route('rentalsystem.privacy-policy') }}" target="_blank">Privacy Policy</a></li>
                        </ul>
                        <div class="small" style="margin-top:10px;padding-top:10px;border-top:1px solid var(--border-color);color:#6b7280;line-height:1.5;font-size:12.5px;">
                            By entering your mobile number and placing your order, you agree to receive important text notifications from Game Day Valet (GDV) about your rental (booking confirmations, delivery updates, and event-day notices). Msg & data rates may apply. Reply STOP to opt out, HELP for help. See our <a href="{{ route('rentalsystem.terms') }}" target="_blank">Terms</a>
                            &amp; <a href="{{ route('rentalsystem.privacy-policy') }}" target="_blank">Privacy Policy</a>. Consent to receive text notifications is not required to complete your booking.
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div id="imageModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:10000;align-items:center;justify-content:center;">
        <div style="position:relative;max-width:90vw;max-height:90vh;">
            <button id="imageModalClose" aria-label="Close" style="position:absolute;right:-10px;top:-10px;background:#fff;border:1px solid #ddd;border-radius:50%;width:32px;height:32px;cursor:pointer;font-weight:900;">×</button>
            <img id="imageModalImg" src="" alt="Tournament image" style="max-width:90vw;max-height:90vh;border-radius:12px;border:2px solid #fff;box-shadow:0 20px 60px rgba(0,0,0,.35);">
        </div>
    </div>

    <script>
        function formatUSD(v) {
            return `$${Number(v).toFixed(2)}`;
        }

        // Booking settings loaded from API
        let bookingSettings = {
            insurance: [],
            waivers: []
        };

        async function loadBookingSettings() {
            try {
                const resp = await fetch('/api/settings/booking');
                const data = await resp.json();
                bookingSettings.insurance = data.insurance_options || [];
                bookingSettings.waivers = data.damage_waiver_options || [];
                renderInsurance();
                renderWaivers();
                recalc();
            } catch (e) {
                /* ignore, keep defaults */ }
        }

        function renderInsurance() {
            const container = document.querySelector('[data-insurance-container]');
            if (!container) return;
            const card = document.getElementById('insuranceCard');
            // If no options, hide entire card and do not render radios
            if (!bookingSettings.insurance || bookingSettings.insurance.length === 0) {
                if (card) card.style.display = 'none';
                container.innerHTML = '';
                return;
            }
            if (card) card.style.display = '';
            container.innerHTML = '';
            // Static none first
            const none = document.createElement('label');
            none.style.cssText = 'display:flex;gap:10px;align-items:center;';
            none.innerHTML = '<input type="radio" name="insurance_option" value="none" checked> None';
            container.appendChild(none);
            bookingSettings.insurance.forEach(opt => {
                const lbl = document.createElement('label');
                lbl.style.cssText = 'display:flex;gap:10px;align-items:center;';
                lbl.innerHTML = `<input type="radio" name="insurance_option" value="${opt.price}"> ${opt.label} — ${formatUSD(opt.price)}`;
                container.appendChild(lbl);
            });
            container.querySelectorAll('input[name="insurance_option"]').forEach(r => r.addEventListener('change', recalc));
        }

        function renderWaivers() {
            const container = document.querySelector('[data-waiver-container]');
            if (!container) return;
            container.innerHTML = '';
            // Update subtitle under the section title with combined descriptions (if any)
            const subtitleEl = document.getElementById('waiverSubtitle');
            const descs = (bookingSettings.waivers || []).map(o => (o.description || '').trim()).filter(Boolean);
            if (subtitleEl) {
                if (descs.length > 0) {
                    // Show unique descriptions joined with line breaks
                    const unique = Array.from(new Set(descs));
                    subtitleEl.innerHTML = unique.map(d => `<span>${d}</span>`).join('<br>');
                    subtitleEl.style.display = '';
                } else {
                    subtitleEl.style.display = 'none';
                    subtitleEl.textContent = '';
                }
            }
            bookingSettings.waivers.forEach((opt, idx) => {
                const id = `waiver_${opt.id}`;
                const wrapper = document.createElement('div');
                wrapper.style.cssText = 'display:block;padding:8px 0;';

                const row = document.createElement('div');
                row.style.cssText = 'display:flex;gap:10px;align-items:center;';
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'damage_waiver_options[]';
                checkbox.value = opt.price;
                checkbox.setAttribute('data-price', opt.price);
                checkbox.id = id;
                const titleSpan = document.createElement('span');
                titleSpan.style.cssText = 'font-weight:600;';
                titleSpan.textContent = `${opt.label} — ${formatUSD(opt.price)}`;
                row.appendChild(checkbox);
                row.appendChild(titleSpan);
                wrapper.appendChild(row);

                container.appendChild(wrapper);
            });
            container.querySelectorAll('input[type="checkbox"]').forEach(c => c.addEventListener('change', recalc));
        }

        function recalc() {
            let itemsSubtotal = 0,
                bundlesSubtotal = 0;

            // Calculate items subtotal
            document.querySelectorAll('.item-row[data-type="item"]').forEach(row => {
                const price = parseFloat(row.getAttribute('data-price')) || 0;
                const qty = parseInt(row.querySelector('.qty-val').textContent) || 0;
                const line = price * qty;
                itemsSubtotal += line;
            });

            // Calculate bundles subtotal using quantity (supports old and new bundle rows)
            document.querySelectorAll('.item-row[data-type="bundle"], .bundle-row[data-type="bundle"]').forEach(row => {
                const price = parseFloat(row.getAttribute('data-price')) || 0;
                const qty = parseInt(row.querySelector('.qty-val').textContent) || 0;
                bundlesSubtotal += price * qty;
            });

            // Insurance from dynamic radios
            const insuranceInput = document.querySelector('input[name="insurance_option"]:checked');
            let insuranceVal = 0;
            if (insuranceInput && insuranceInput.value !== 'none') {
                insuranceVal = parseFloat(insuranceInput.value) || 0;
            }

            // Sum all selected waiver checkboxes
            let waiverVal = 0;
            document.querySelectorAll('input[name="damage_waiver_options[]"]:checked').forEach(c => {
                waiverVal += parseFloat(c.getAttribute('data-price')) || 0;
            });
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
                let q = Math.max(0, (parseInt(val.textContent) || 0) - 1);
                val.textContent = q;
                input.value = q;
                recalc();
                validateForm();
            });
            inc.addEventListener('click', () => {
                let q = (parseInt(val.textContent) || 0) + 1;
                val.textContent = q;
                input.value = q;
                recalc();
                validateForm();
            });
        });

        // Event listeners for bundles (quantity buttons) — supports new bundle-row UI
        document.querySelectorAll('.item-row[data-type="bundle"], .bundle-row[data-type="bundle"]').forEach(row => {
            const dec = row.querySelector('.qty-dec');
            const inc = row.querySelector('.qty-inc');
            const val = row.querySelector('.qty-val');
            const input = row.querySelector('input[type="hidden"]');
            dec.addEventListener('click', () => {
                let q = Math.max(0, (parseInt(val.textContent) || 0) - 1);
                val.textContent = q;
                input.value = q;
                recalc();
                validateForm();
            });
            inc.addEventListener('click', () => {
                let q = (parseInt(val.textContent) || 0) + 1;
                val.textContent = q;
                input.value = q;
                recalc();
                validateForm();
            });
        });

        // dynamic listeners bound in render

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

            // Check if any bundles are selected (supports new bundle-row UI)
            document.querySelectorAll('.item-row[data-type="bundle"], .bundle-row[data-type="bundle"]').forEach(row => {
                const qty = parseInt(row.querySelector('.qty-val').textContent) || 0;
                if (qty > 0) hasBundles = true;
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

        // Form submission validation with login check
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            if (!validateForm()) {
                showModal('Please select at least one item or bundle before proceeding.');
                return false;
            }
            const smsOptIn = document.getElementById('sms_opt_in');
            if (smsOptIn && !smsOptIn.checked) {
                showModal('Please agree to receive important text notifications to proceed.');
                return false;
            }
            // Check if user is logged in
            @if(!Auth::check())
            // Show login modal if not logged in
            const loginModal = document.createElement('div');
            loginModal.style.cssText = 'position:fixed;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.5);z-index:10000;';
            const redirectUrl = encodeURIComponent(window.location.href);
            loginModal.innerHTML = `
            <div style="background:#fff;border-radius:14px;min-width:300px;max-width:460px;padding:20px;border:1px solid var(--border-color);box-shadow:0 20px 40px rgba(0,0,0,.18);">
                <div style="font-weight:800;font-size:16px;margin-bottom:8px;color:var(--dark-color);">Login Required</div>
                <div style="color:var(--secondary-color);line-height:1.5;">Please sign in to confirm your booking.</div>
                <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
                    <a href="{{ route('rentalsystem.signin') }}?redirect=${redirectUrl}" class="btn-primary" style="width:auto;padding:10px 16px;text-decoration:none;">Sign In</a>
                    <button id="dismissLoginNow" class="btn-primary" style="width:auto;padding:10px 16px;background:#6b7280;">Cancel</button>
                </div>
            </div>`;
            document.body.appendChild(loginModal);
            document.getElementById('dismissLoginNow').addEventListener('click', () => {
                loginModal.remove();
            });
            @else
            // If logged in, submit the form
            this.submit();
            @endif
        });

        // Validate coupon via API and apply discount on frontend only
        document.getElementById('validateCouponBtn').addEventListener('click', async function() {
            // Require at least one item or bundle selected before validating
            let hasItems = false,
                hasBundles = false;
            document.querySelectorAll('.item-row[data-type="item"]').forEach(row => {
                const qty = parseInt(row.querySelector('.qty-val').textContent) || 0;
                if (qty > 0) hasItems = true;
            });
            document.querySelectorAll('.item-row[data-type="bundle"], .bundle-row[data-type="bundle"]').forEach(row => {
                const qty = parseInt(row.querySelector('.qty-val').textContent) || 0;
                if (qty > 0) hasBundles = true;
            });
            if (!hasItems && !hasBundles) {
                // show existing errors
                document.getElementById('items-error').style.display = 'block';
                document.getElementById('bundles-error').style.display = 'block';
                showModal('Please select at least one item or bundle before validating a promo code.');
                return;
            }
            const code = (document.getElementById('promo_code').value || '').trim();
            if (!code) {
                showModal('Please enter a promo code.');
                return;
            }
            try {
                const resp = await fetch('/api/coupon/validate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        promo_code: code,
                        user_id: @json(Auth::check() ? Auth::id() : null)
                    })
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

        function showModal(message) {
            document.getElementById('appModalBody').textContent = message;
            modalEl.style.display = 'flex';
        }
        document.getElementById('appModalOk').addEventListener('click', () => {
            modalEl.style.display = 'none';
        });
        modalEl.addEventListener('click', (e) => {
            if (e.target === modalEl) {
                modalEl.style.display = 'none';
            }
        });

        loadBookingSettings();
        // Image modal for tournament and item/bundle images
        (function(){
            const img = document.getElementById('tournamentImg');
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('imageModalImg');
            const closeBtn = document.getElementById('imageModalClose');
            if(img && modal && modalImg){
                img.addEventListener('click', function(){
                    const fullUrl = img.getAttribute('data-fullurl') || img.src;
                    modalImg.src = fullUrl;
                    modal.style.display = 'flex';
                });
                const hide = ()=>{ modal.style.display='none'; modalImg.src=''; };
                modal.addEventListener('click', function(e){ if(e.target === modal) hide(); });
                if(closeBtn){ closeBtn.addEventListener('click', hide); }
            }
            // Click-to-zoom for item and bundle thumbnails
            document.querySelectorAll('.rs-thumb').forEach(function(el){
                el.addEventListener('click', function(){
                    const full = el.getAttribute('data-full') || el.src;
                    modalImg.src = full;
                    modal.style.display = 'flex';
                });
            });
        })();
        validateForm();
    </script>
</body>

</html>