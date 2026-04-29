{{--
    Tournament Order Page — Game Day Valet
    Route: /tournaments/{tournamentId}/rental
    Controller: RentalSystem\RentalSystemController@showRentalBooking
    Submits to: rentalsystem.rental.create (creates Stripe Checkout session)

    Variables passed from controller:
      $tournament        Tournament model (name, image, start_date, end_date, location, sport, description, tax_rate)
      $tournamentId      tournament id
      $availableBundles  Collection of bundles with effective_price
      $availableItems    Collection of items with effective_price (rendered as "add-ons" / a la carte)
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserve Your Setup — {{ $tournament->name ?? 'Tournament' }} | Game Day Valet</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Reserve your sideline setup for {{ $tournament->name ?? 'this tournament' }}. Delivered to your field. One price covers the entire weekend.">

    <link rel="shortcut icon" href="/images/logo-sm.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --gdv-black: #0a0a0a;
            --gdv-black-2: #141414;
            --gdv-black-3: #1c1c1c;
            --gdv-red: #d92231;
            --gdv-red-bright: #ef3445;
            --gdv-gold: #f5b942;
            --gdv-gold-bright: #ffc857;
            --gdv-white: #ffffff;
            --gdv-text: #f4f4f4;
            --gdv-muted: #9ca3af;
            --gdv-border: #2a2a2a;
            --gdv-success: #22c55e;
            --shadow-strong: 0 20px 60px rgba(0, 0, 0, 0.6);
            --shadow-card: 0 8px 24px rgba(0, 0, 0, 0.35);
        }

        *, *::before, *::after { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            margin: 0;
            background: var(--gdv-black);
            color: var(--gdv-text);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        .display { font-family: 'Anton', 'Inter', sans-serif; letter-spacing: 0.01em; }

        a { color: inherit; }

        /* ==================== TOP BAR ==================== */
        .topbar {
            background: var(--gdv-black);
            border-bottom: 1px solid var(--gdv-border);
            padding: 14px 24px;
        }
        .topbar-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--gdv-white);
            background: var(--gdv-white);
            padding: 6px 14px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            transition: transform 0.15s ease;
        }
        .brand:hover { transform: translateY(-1px); }
        .brand img {
            height: 36px;
            width: auto;
            display: block;
        }
        .brand-fallback {
            font-family: 'Anton', sans-serif;
            font-size: 22px;
            color: var(--gdv-black);
            line-height: 1;
            letter-spacing: 0.02em;
        }
        .brand-fallback span { color: var(--gdv-red); }
        .topbar-tag {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--gdv-white);
        }
        .topbar-tag .accent { color: var(--gdv-gold); }
        .topbar-phone {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--gdv-white);
            text-decoration: none;
        }
        .topbar-phone i { color: var(--gdv-gold); font-size: 18px; }
        .topbar-phone .num { font-weight: 800; font-size: 16px; }
        .topbar-phone .sub { font-size: 11px; color: var(--gdv-muted); display: block; }

        /* ==================== HERO ==================== */
        .hero {
            position: relative;
            background: var(--gdv-black);
            overflow: hidden;
            padding-bottom: 40px;
        }
        .hero-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            min-height: 520px;
        }
        .hero-left {
            padding: 56px 48px 56px 24px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .hero-eyebrow {
            display: inline-block;
            font-size: 14px;
            font-weight: 800;
            color: var(--gdv-red);
            text-transform: uppercase;
            letter-spacing: 0.18em;
            margin-bottom: 8px;
        }
        .hero-title {
            font-family: 'Anton', sans-serif;
            font-size: clamp(48px, 7vw, 88px);
            line-height: 0.92;
            color: var(--gdv-white);
            margin: 0 0 18px;
            text-transform: uppercase;
            letter-spacing: 0.01em;
        }
        .hero-meta {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .hero-date-pill {
            background: var(--gdv-red);
            color: var(--gdv-white);
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 800;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .hero-sport {
            font-size: 15px;
            font-weight: 700;
            color: var(--gdv-white);
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .hero-sport::before { content: '•'; margin-right: 14px; color: var(--gdv-muted); }

        .hero-pitch {
            font-size: 32px;
            line-height: 1.15;
            color: var(--gdv-white);
            font-weight: 600;
            margin: 0 0 8px;
        }
        .hero-pitch .em {
            font-family: 'Anton', sans-serif;
            font-size: 64px;
            color: var(--gdv-gold);
            display: inline-block;
            line-height: 1;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .hero-divider {
            width: 60px;
            height: 3px;
            background: var(--gdv-red);
            margin: 22px 0;
        }

        .hero-bullets {
            list-style: none;
            padding: 0;
            margin: 0 0 28px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .hero-bullets li {
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 16px;
            font-weight: 500;
            color: var(--gdv-text);
        }
        .hero-bullets i {
            color: var(--gdv-gold);
            font-size: 18px;
            width: 28px;
            text-align: center;
            flex-shrink: 0;
        }

        .hero-urgency {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            background: linear-gradient(90deg, rgba(217, 34, 49, 0.15) 0%, rgba(20, 20, 20, 0.95) 100%);
            border: 1.5px solid var(--gdv-red);
            border-radius: 12px;
            padding: 14px 20px;
            margin-bottom: 22px;
            max-width: fit-content;
            box-shadow: 0 4px 16px rgba(217, 34, 49, 0.25);
        }
        .hero-urgency .fire { font-size: 28px; line-height: 1; flex-shrink: 0; }
        .hero-urgency .urgency-text { display: flex; flex-direction: column; gap: 2px; }
        .hero-urgency .count {
            font-family: 'Anton', sans-serif;
            font-size: 20px;
            color: var(--gdv-white);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            line-height: 1.1;
        }
        .hero-urgency .count strong { color: var(--gdv-red); font-weight: 400; }
        .hero-urgency .label {
            font-size: 13px;
            font-weight: 800;
            color: var(--gdv-white);
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .hero-urgency.sold-out {
            background: linear-gradient(90deg, rgba(108, 117, 125, 0.2) 0%, rgba(20, 20, 20, 0.95) 100%);
            border-color: var(--gdv-muted);
            box-shadow: none;
        }
        .hero-urgency.sold-out .count strong { color: var(--gdv-muted); }

        .hero-cta {
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            background: var(--gdv-gold);
            color: var(--gdv-black);
            padding: 18px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-family: 'Anton', sans-serif;
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            max-width: 340px;
            box-shadow: 0 6px 20px rgba(245, 185, 66, 0.25);
        }
        .hero-cta:hover {
            background: var(--gdv-gold-bright);
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(245, 185, 66, 0.4);
        }
        .hero-cta i { font-size: 18px; }
        .hero-cta-meta {
            margin-top: 12px;
            font-size: 12px;
            color: var(--gdv-muted);
        }
        .hero-cta-meta i { color: var(--gdv-gold); margin-right: 6px; }

        .hero-right {
            position: relative;
            background-color: var(--gdv-black-2);
            background-size: cover;
            background-position: center;
            background-image: linear-gradient(90deg, rgba(10,10,10,0.95) 0%, rgba(10,10,10,0.5) 25%, rgba(10,10,10,0.1) 55%, rgba(10,10,10,0) 100%)
                @if(!empty($tournament->image)), url('{{ asset('storage/' . $tournament->image) }}') @else, url('{{ asset('images/gdv-hero-default.png') }}') @endif
                ;
            overflow: hidden;
        }
        .hero-right::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, transparent 70%, rgba(0,0,0,0.5) 100%);
            pointer-events: none;
        }

        /* ==================== ORANGE STRIPE ==================== */
        .stripe-bar {
            background: var(--gdv-gold);
            color: var(--gdv-black);
            padding: 16px 24px;
            text-align: center;
        }
        .stripe-bar-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-family: 'Anton', sans-serif;
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .stripe-bar-inner .accent { color: var(--gdv-red); }
        .stripe-bar-inner i { font-size: 22px; }

        /* ==================== TIERS (BUNDLES) ==================== */
        .tiers-section {
            background: var(--gdv-black);
            padding: 56px 24px;
        }
        .tiers-wrap {
            max-width: 1200px;
            margin: 0 auto;
        }
        .tiers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
        }
        .tier {
            background: var(--gdv-white);
            border-radius: 14px;
            padding: 28px 24px 24px;
            position: relative;
            display: flex;
            flex-direction: column;
            color: var(--gdv-black);
            box-shadow: var(--shadow-card);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .tier.featured {
            border: 3px solid var(--gdv-gold);
            transform: translateY(-12px);
            box-shadow: 0 0 0 1px var(--gdv-gold), 0 24px 60px rgba(245, 185, 66, 0.35), 0 0 80px -10px rgba(245, 185, 66, 0.4);
        }
        .tier:hover { transform: translateY(-6px); cursor: pointer; }
        .tier.featured:hover { transform: translateY(-16px); }

        .tier-badge {
            position: absolute;
            top: -16px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--gdv-red);
            color: var(--gdv-white);
            padding: 6px 18px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .tier-badge i { color: var(--gdv-gold); }

        .tier-name {
            font-family: 'Anton', sans-serif;
            font-size: 32px;
            color: var(--gdv-black);
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin: 0 0 8px;
        }
        .tier-desc {
            text-align: center;
            color: #555;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 18px;
            min-height: 21px;
        }
        .tier-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(180deg, #fafafa 0%, #f0f0f0 100%);
            border-radius: 8px;
            margin-bottom: 18px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #d1d5db;
            font-size: 48px;
        }
        .tier-image i { color: #d1d5db; }

        .tier-price {
            text-align: center;
            font-family: 'Anton', sans-serif;
            font-size: 56px;
            color: var(--gdv-black);
            line-height: 1;
            margin-bottom: 6px;
            letter-spacing: -0.01em;
        }
        .tier-price-sub {
            text-align: center;
            font-size: 13px;
            font-weight: 800;
            color: var(--gdv-red);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 10px;
        }
        .tier-perday {
            text-align: center;
            background: var(--gdv-black);
            color: var(--gdv-white);
            font-size: 13px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 20px;
            display: inline-block;
            margin: 0 auto 18px;
            align-self: center;
            letter-spacing: 0.02em;
        }
        .tier-cta {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-family: 'Anton', sans-serif;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            cursor: pointer;
            margin-top: auto;
            transition: all 0.2s ease;
            background: var(--gdv-gold);
            color: var(--gdv-black);
        }
        .tier-cta:hover { background: var(--gdv-gold-bright); }
        .tier.featured .tier-cta {
            background: var(--gdv-red);
            color: var(--gdv-white);
        }
        .tier.featured .tier-cta:hover { background: var(--gdv-red-bright); }
        .tier.is-selected {
            outline: 3px solid var(--gdv-success);
            outline-offset: 2px;
        }

        .tiers-empty {
            text-align: center;
            padding: 60px 20px;
            color: var(--gdv-muted);
            background: var(--gdv-black-2);
            border: 1px dashed var(--gdv-border);
            border-radius: 12px;
        }

        /* ==================== HOW IT WORKS ==================== */
        .how-section {
            background: var(--gdv-black);
            padding: 0 24px 56px;
        }
        .how-wrap {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--gdv-black-2);
            border: 1px solid var(--gdv-border);
            border-radius: 16px;
            padding: 32px;
        }
        .how-title {
            text-align: center;
            font-size: 13px;
            font-weight: 800;
            color: var(--gdv-white);
            text-transform: uppercase;
            letter-spacing: 0.2em;
            margin: 0 0 28px;
            position: relative;
        }
        .how-title::before, .how-title::after {
            content: '';
            display: inline-block;
            width: 40px;
            height: 1px;
            background: var(--gdv-border);
            vertical-align: middle;
            margin: 0 16px;
        }
        .how-steps {
            display: grid;
            grid-template-columns: 1fr auto 1fr auto 1fr auto 1fr;
            align-items: center;
            gap: 12px;
        }
        .how-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 12px;
        }
        .how-step .icon {
            font-size: 32px;
            color: var(--gdv-gold);
        }
        .how-step .text {
            font-size: 13px;
            font-weight: 600;
            color: var(--gdv-text);
            line-height: 1.4;
        }
        .how-step .text strong { display: block; color: var(--gdv-white); }
        .how-arrow {
            color: var(--gdv-gold);
            font-size: 18px;
            opacity: 0.7;
        }

        /* ==================== ORDER FORM ==================== */
        .order-section {
            background: var(--gdv-black);
            padding: 0 24px 56px;
        }
        .order-wrap {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 24px;
            align-items: start;
        }
        .order-main { min-width: 0; }
        .order-sidebar {
            display: flex;
            flex-direction: column;
            gap: 14px;
            min-width: 0;
        }
        .order-card {
            background: var(--gdv-black-2);
            border: 1px solid var(--gdv-border);
            border-radius: 16px;
            padding: 28px;
        }
        .order-card-title {
            font-family: 'Anton', sans-serif;
            font-size: 22px;
            color: var(--gdv-white);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin: 0 0 20px;
        }
        .field { margin-bottom: 14px; }
        .field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        @media (max-width: 600px) { .field-row { grid-template-columns: 1fr; } }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
            background: var(--gdv-black-3);
            border: 1px solid var(--gdv-border);
            border-radius: 8px;
            transition: border-color 0.15s ease;
        }
        .input-wrap:focus-within { border-color: var(--gdv-gold); }
        .input-wrap i {
            color: var(--gdv-muted);
            padding: 0 14px;
            font-size: 15px;
        }
        .input-wrap input,
        .input-wrap textarea,
        .input-wrap select {
            flex: 1;
            background: transparent;
            border: none;
            color: var(--gdv-white);
            font-family: inherit;
            font-size: 15px;
            padding: 14px 14px 14px 0;
            outline: none;
            min-width: 0;
        }
        .input-wrap input::placeholder,
        .input-wrap textarea::placeholder { color: var(--gdv-muted); }

        .summary-card {
            background: var(--gdv-black-2);
            border: 1px solid var(--gdv-border);
            border-radius: 16px;
            padding: 28px;
            position: sticky;
            top: 24px;
            display: flex;
            flex-direction: column;
        }
        .summary-label {
            font-size: 12px;
            font-weight: 800;
            color: var(--gdv-muted);
            text-transform: uppercase;
            letter-spacing: 0.15em;
            margin: 0 0 6px;
        }
        .summary-tier-name {
            font-family: 'Anton', sans-serif;
            font-size: 22px;
            color: var(--gdv-white);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin: 0 0 14px;
        }
        .summary-tier-name .tier-tag { color: var(--gdv-red); }
        .summary-divider { height: 1px; background: var(--gdv-border); margin: 14px 0; }

        .summary-line {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 8px;
            color: var(--gdv-text);
        }
        .summary-line .muted { color: var(--gdv-muted); }
        .summary-line.total {
            font-family: 'Anton', sans-serif;
            font-size: 28px;
            color: var(--gdv-white);
            text-transform: uppercase;
            margin-top: 8px;
            align-items: baseline;
        }
        .summary-line.total .accent { color: var(--gdv-red); font-size: 14px; letter-spacing: 0.08em; }

        .pay-btn {
            width: 100%;
            padding: 18px;
            background: var(--gdv-gold);
            color: var(--gdv-black);
            border: none;
            border-radius: 10px;
            font-family: 'Anton', sans-serif;
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .apple-pay-btn {
            width: 100%;
            padding: 14px;
            background: var(--gdv-white);
            color: var(--gdv-black);
            border: none;
            border-radius: 10px;
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', sans-serif;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 16px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .apple-pay-btn:hover:not(:disabled) {
            background: #f5f5f5;
            transform: translateY(-1px);
        }
        .apple-pay-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .apple-pay-btn .apple-pay-inner {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 17px;
        }
        .apple-pay-btn .apple-logo {
            font-size: 17px;
            line-height: 1;
        }
        .pay-btn:hover:not(:disabled) {
            background: var(--gdv-gold-bright);
            transform: translateY(-1px);
        }
        .pay-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .pay-btn i { font-size: 16px; }

        .pay-meta {
            text-align: center;
            font-size: 12px;
            color: var(--gdv-muted);
            margin-top: 10px;
        }
        .pay-meta i { color: var(--gdv-success); margin-right: 4px; }

        .text-followup {
            margin-top: 18px;
            background: rgba(245, 185, 66, 0.08);
            border: 1px solid rgba(245, 185, 66, 0.2);
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 13px;
            color: var(--gdv-text);
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .text-followup i { color: var(--gdv-gold); margin-top: 2px; }

        /* ==================== ADD-ONS ==================== */
        .addons-card {
            background: var(--gdv-black-2);
            border: 1px solid var(--gdv-border);
            border-radius: 16px;
            padding: 28px;
            margin-top: 18px;
        }
        .utility-card {
            padding: 14px 16px;
            border-radius: 12px;
            margin-top: 0;
        }
        .utility-card .order-card-title {
            font-size: 16px;
            margin-bottom: 8px;
        }
        .promo-row {
            display: flex;
            gap: 10px;
            align-items: stretch;
            flex-wrap: wrap;
        }
        .promo-row .field {
            margin-bottom: 0;
            flex: 1;
            min-width: 170px;
        }
        .promo-row .input-wrap { height: 100%; }
        .promo-apply-btn {
            margin-top: 0;
            font-size: 13px;
            padding: 10px 20px;
            width: auto;
            border-radius: 8px;
        }
        .waiver-subtitle {
            margin-top: 2px;
            display: none;
            font-size: 12px;
            color: var(--gdv-muted);
        }
        .waiver-options {
            display: flex;
            flex-direction: column;
            gap: 8px;
            color: var(--gdv-white);
            margin-top: 6px;
        }
        .addons-list { display: flex; flex-direction: column; gap: 10px; }
        .addon-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 72px auto;
            align-items: start;
            gap: 16px;
            padding: 12px 14px;
            background: var(--gdv-black-3);
            border: 1px solid var(--gdv-border);
            border-radius: 10px;
        }
        .addon-name {
            min-width: 0;
            font-weight: 600;
            color: var(--gdv-white);
            font-size: 15px;
        }
        .addon-name small {
            display: block;
            margin-top: 2px;
            color: var(--gdv-muted);
            font-weight: 400;
            font-size: 12px;
            line-height: 1.35;
        }
        .addon-price {
            font-weight: 800;
            color: var(--gdv-gold);
            font-size: 15px;
            text-align: right;
            align-self: start;
            padding-top: 2px;
            white-space: nowrap;
        }
        .qty-ctrl {
            display: flex;
            align-items: center;
            background: var(--gdv-black);
            border: 1px solid var(--gdv-border);
            border-radius: 6px;
            overflow: hidden;
            justify-self: end;
            align-self: start;
        }
        .qty-ctrl button {
            background: transparent;
            border: none;
            color: var(--gdv-white);
            width: 32px;
            height: 32px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
        }
        .qty-ctrl button:hover { background: var(--gdv-black-3); color: var(--gdv-gold); }
        .qty-ctrl input {
            width: 36px;
            background: transparent;
            border: none;
            color: var(--gdv-white);
            text-align: center;
            font-weight: 700;
            font-size: 14px;
            outline: none;
            -moz-appearance: textfield;
        }
        .qty-ctrl input::-webkit-outer-spin-button,
        .qty-ctrl input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

        /* ==================== SOCIAL PROOF ==================== */
        .proof {
            background: var(--gdv-black-2);
            border-top: 1px solid var(--gdv-border);
            border-bottom: 1px solid var(--gdv-border);
            padding: 24px;
        }
        .proof-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 24px;
            align-items: center;
        }
        .proof-stars { color: var(--gdv-gold); font-size: 16px; letter-spacing: 2px; }
        .proof-rating {
            font-family: 'Anton', sans-serif;
            font-size: 22px;
            color: var(--gdv-white);
            margin-left: 8px;
        }
        .proof-quote {
            font-size: 14px;
            color: var(--gdv-text);
            margin: 6px 0 0;
            line-height: 1.4;
        }
        .proof-quote .author {
            display: block;
            color: var(--gdv-red);
            font-weight: 700;
            font-size: 12px;
            margin-top: 4px;
        }
        .proof-stat {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 8px;
        }
        .proof-stat i {
            font-size: 28px;
            color: var(--gdv-gold);
            background: rgba(245, 185, 66, 0.1);
            border: 1px solid rgba(245, 185, 66, 0.25);
            border-radius: 50%;
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .proof-stat .label {
            font-size: 12px;
            font-weight: 700;
            color: var(--gdv-text);
            line-height: 1.2;
        }

        /* ==================== STICKY FOOTER ==================== */
        .footer-cta {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--gdv-gold);
            color: var(--gdv-black);
            padding: 16px 24px;
            z-index: 50;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.4);
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }
        .footer-cta.is-visible {
            transform: translateY(0);
        }
        .footer-cta-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .footer-cta-text {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .footer-cta-text i { font-size: 24px; }
        .footer-cta-text .head {
            font-family: 'Anton', sans-serif;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            display: block;
            line-height: 1.1;
        }
        .footer-cta-text .sub { font-size: 12px; font-weight: 500; }
        .footer-cta-btn {
            background: var(--gdv-black);
            color: var(--gdv-white);
            border: none;
            padding: 14px 24px;
            border-radius: 8px;
            font-family: 'Anton', sans-serif;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            transition: background 0.15s ease;
        }
        .footer-cta-btn:hover { background: var(--gdv-black-3); }

        /* ==================== ALERTS ==================== */
        .alert {
            background: rgba(217, 34, 49, 0.1);
            border: 1px solid var(--gdv-red);
            color: var(--gdv-text);
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .alert ul { margin: 6px 0 0 18px; padding: 0; }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 980px) {
            .hero-grid { grid-template-columns: 1fr; min-height: auto; }
            .hero-right { display: none; }
            .hero-left {
                padding: 40px 24px;
                position: relative;
                @if(!empty($tournament->image))
                    background-image: linear-gradient(180deg, rgba(10,10,10,0.7) 0%, rgba(10,10,10,0.9) 60%, var(--gdv-black) 100%), url('{{ asset('storage/' . $tournament->image) }}');
                    background-size: cover;
                    background-position: center;
                @endif
            }
            .hero-title { font-size: clamp(40px, 10vw, 64px); }
            .hero-pitch { font-size: 22px; }
            .hero-pitch .em { font-size: 36px; }
            .order-wrap { grid-template-columns: 1fr; }
            .summary-card { position: static; }
            .order-sidebar { gap: 12px; }
            .how-steps { grid-template-columns: 1fr; gap: 20px; }
            .how-arrow { display: none; }
            .proof-inner { grid-template-columns: 1fr; gap: 16px; text-align: center; }
            .proof-stat { flex-direction: row; justify-content: center; }
        }
        @media (max-width: 700px) {
            .topbar-inner {
                display: grid;
                grid-template-columns: auto 1fr;
                gap: 12px;
                align-items: center;
            }
            .topbar-tag { display: none; }
            .topbar-phone {
                justify-self: end;
                flex-direction: row;
                align-items: center;
                gap: 8px;
            }
            .topbar-phone .num { font-size: 14px; white-space: nowrap; }
            .topbar-phone .sub { display: none; }
            .stripe-bar-inner { font-size: 15px; line-height: 1.3; flex-wrap: wrap; }
            .footer-cta-text .sub { display: none; }
            .footer-cta-text .head { font-size: 14px; line-height: 1.2; }
            .footer-cta-btn { padding: 12px 14px; font-size: 13px; white-space: nowrap; }
            .field-row { grid-template-columns: 1fr; }
            .tiers-section { padding: 40px 16px 60px; }
            .order-section { padding: 0 16px 60px; }
            .how-section { padding: 0 16px 40px; }
            .order-card, .summary-card, .addons-card { padding: 20px; }
            .utility-card { padding: 12px 14px; }
            .promo-row { gap: 8px; }
            .promo-row .field { min-width: 100%; }
            .promo-apply-btn { width: 100%; }
            .addon-row {
                grid-template-columns: minmax(0, 1fr) auto;
                grid-template-areas:
                    'name name'
                    'price qty';
                gap: 10px 12px;
            }
            .addon-name { grid-area: name; }
            .addon-price {
                grid-area: price;
                text-align: left;
                padding-top: 0;
            }
            .qty-ctrl {
                grid-area: qty;
                justify-self: end;
            }
            .hero-cta { font-size: 18px; padding: 16px 20px; max-width: 100%; width: 100%; justify-content: center; }
        }
        @media (max-width: 380px) {
            .topbar { padding: 12px 14px; }
            .brand img { height: 30px; }
            .topbar-phone .num { font-size: 13px; }
            .hero-left { padding: 32px 18px; }
            .tier-name { font-size: 28px; }
            .tier-price { font-size: 36px; }
        }
        /* No body padding needed - sticky footer slides in only after hero */
    </style>
<!-- Facebook Pixel Global Init -->
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '1364397922375498');
    fbq('track', 'PageView');
    fbq('track', 'ViewContent', {
        content_name: '{{ addslashes($tournament->name ?? "Tournament Booking") }}',
        content_ids: ['{{ $tournament->id ?? "" }}'],
        content_type: 'product',
        currency: 'USD'
    });
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=1364397922375498&ev=PageView&noscript=1"
    /></noscript>
    <!-- End Facebook Pixel Global Init -->
</head>

<body>
    <div style="background: #d92231; color: white; text-align: center; padding: 12px; font-weight: 800; font-size: 16px; position: sticky; top: 0; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.3); text-transform: uppercase; letter-spacing: 0.1em;">
        <i class="fas fa-exclamation-triangle" style="color: #f5b942; margin-right: 8px;"></i> PREVIEW MODE: This is a template preview. Booking and payments are disabled.
    </div>

    <div style="background: #141414; border-bottom: 1px solid rgba(217, 34, 49, 0.4); color: #f4f4f4; text-align: center; padding: 10px 24px; font-size: 14px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; letter-spacing: 0.03em;">
        <i class="fas fa-fire" style="color: #ef3445; font-size: 16px;"></i>
        <span>Limited setups available for this weekend. <strong>Most tournaments sell out.</strong></span>
    </div>

    {{-- ==================== TOP BAR ==================== --}}
    <div class="topbar">
        <div class="topbar-inner">
            <a href="{{ route('rentalsystem.sports') }}" class="brand">
                @if(file_exists(public_path('images/gdv-logo.png')))
                    <img src="{{ asset('images/gdv-logo.png') }}" alt="Game Day Valet">
                @else
                    <span class="brand-fallback">GD<span>V</span></span>
                @endif
            </a>
            <div class="topbar-tag">WHERE THE FANS ARE THE <span class="accent">MVP</span></div>
            <a href="tel:+14704288440" class="topbar-phone">
                <i class="fas fa-phone"></i>
                <div>
                    <span class="num">(470) 428-8440</span>
                    <span class="sub">Text us anytime</span>
                </div>
            </a>
        </div>
    </div>

    {{-- ==================== HERO ==================== --}}
    <section class="hero">
        <div class="hero-grid">
            <div class="hero-left">
                @if(!empty($tournament->location))
                    <span class="hero-eyebrow">{{ $tournament->location }}</span>
                @endif
                <h1 class="hero-title">{{ $tournament->name ?? 'Tournament' }}</h1>

                <div class="hero-meta">
                    @if(!empty($tournament->start_date))
                        <span class="hero-date-pill">
                            {{ \Carbon\Carbon::parse($tournament->start_date)->format('M j') }}@if(!empty($tournament->end_date) && $tournament->end_date != $tournament->start_date)–{{ \Carbon\Carbon::parse($tournament->end_date)->format('j') }}@endif
                        </span>
                    @endif
                    @if(!empty($tournament->sport->name))
                        <span class="hero-sport">{{ $tournament->sport->name }}</span>
                    @endif
                </div>

                <p class="hero-pitch">Your sideline setup is<br><span class="em">already there</span><br>when you arrive.</p>
                <p style="font-size: 16px; color: var(--gdv-muted); margin: 0 0 12px; font-weight: 500; line-height: 1.4;">Perfect for families traveling in and staying in hotels.</p>

                <ul class="hero-bullets">
                    <li><i class="fas fa-truck-fast"></i> Delivered to your first field</li>
                    <li><i class="fas fa-campground"></i> Set up anywhere. Use all day</li>
                    <li><i class="fas fa-comments"></i> Text us when you’re done — we pick it up</li>
                </ul>

                @if(isset($tournament->setups_remaining) && $tournament->setups_remaining !== null && $tournament->setups_remaining > 0)
                    <div class="hero-urgency">
                        <span class="fire">🔥</span>
                        <div class="urgency-text">
                            <span class="count"><strong>ONLY {{ $tournament->setups_remaining }} {{ $tournament->setups_remaining == 1 ? 'SETUP' : 'SETUPS' }} LEFT</strong></span>
                            <span class="label">FOR THIS WEEKEND!</span>
                        </div>
                    </div>
                @elseif(isset($tournament->setups_remaining) && $tournament->setups_remaining === 0)
                    <div class="hero-urgency sold-out">
                        <span class="fire">⚠️</span>
                        <div class="urgency-text">
                            <span class="count"><strong>SOLD OUT</strong></span>
                            <span class="label">FOR THIS WEEKEND</span>
                        </div>
                    </div>
                @endif

                <a href="#tiers" class="hero-cta">
                    <span>Reserve My Setup</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
                <div class="hero-cta-meta" style="font-size: 14px;">
                    Reserve now. We’ll text you to confirm your field and game time.
                </div>
            </div>
            <div class="hero-right" role="img" aria-label="Game Day Valet sideline setup"></div>
        </div>
    </section>

    {{-- ==================== ORANGE STRIPE ==================== --}}
    <div class="stripe-bar">
        <div class="stripe-bar-inner">
            <i class="fas fa-trophy"></i>
            <span>One price. Covers the <span class="accent">entire tournament weekend.</span></span>
        </div>
    </div>

    {{-- ==================== ORDER FORM (wraps tiers + customer + summary) ==================== --}}
    <form id="bookingForm" method="POST" action="{{ route('rentalsystem.rental.create') }}">
        @csrf
        <input type="hidden" name="tournament_id" value="{{ $tournamentId }}">
        <input type="hidden" name="payment_method" value="stripe">
        <input type="hidden" name="total_amount" id="total_amount_input" value="0">
        <input type="hidden" name="tax_rate" id="tax_rate_input" value="{{ isset($tournament) ? (float) ($tournament->tax_rate ?? 0) : 0 }}">
        <input type="hidden" name="tax_amount" id="tax_amount_input" value="0">

        {{-- ==================== TIER CARDS ==================== --}}
        <section class="tiers-section" id="tiers">
            <div class="tiers-wrap">
                @if(count($availableBundles) > 0)
                    @php
                        $bundleCount = count($availableBundles);
                        $featuredIndex = $bundleCount > 1 ? (int) floor($bundleCount / 2) : 0;
                    @endphp

                    <div style="text-align: center; margin-bottom: 40px; background: var(--gdv-black-2); border: 1px solid var(--gdv-border); padding: 24px; border-radius: 16px; max-width: 650px; margin-left: auto; margin-right: auto; box-shadow: var(--shadow-card);">
                        <div style="color: var(--gdv-gold); font-size: 18px; margin-bottom: 10px; letter-spacing: 2px;">★★★★★</div>
                        <p style="font-size: 18px; font-style: italic; color: var(--gdv-white); margin: 0 0 10px; line-height: 1.5; font-weight: 500;">“Everything was waiting for us. Super easy and worth every penny.”</p>
                        <span style="color: var(--gdv-red); font-weight: 800; font-size: 13px; text-transform: uppercase; letter-spacing: 0.1em;">— Baseball Mom</span>
                    </div>

                    <div class="tiers-grid">
                        @foreach($availableBundles as $idx => $bd)
                            <div class="tier {{ $idx === $featuredIndex ? 'featured' : '' }}" data-bundle-id="{{ $bd->id }}" data-bundle-price="{{ (float) $bd->effective_price }}" data-bundle-name="{{ $bd->name }}">
                                @if($idx === $featuredIndex)
                                    <div class="tier-badge"><i class="fas fa-star"></i> Most Popular</div>
                                @endif
                                <h3 class="tier-name">{{ $bd->name }}</h3>
                                <div class="tier-desc">{{ \Illuminate\Support\Str::limit(strip_tags($bd->description ?? ''), 60) }}</div>
                                <div class="tier-image" @if(!empty($bd->image)) style="background-image: url('{{ asset('storage/' . $bd->image) }}');" @endif>
                                    @if(empty($bd->image))<i class="fas fa-box-open"></i>@endif
                                </div>
                                <div class="tier-price">${{ number_format((float) $bd->effective_price, 0) }}</div>
                                <div class="tier-price-sub">Total (All Weekend)</div>
                                @if(!empty($tournament->start_date) && !empty($tournament->end_date))
                                    @php
                                        $days = max(1, \Carbon\Carbon::parse($tournament->start_date)->diffInDays(\Carbon\Carbon::parse($tournament->end_date)) + 1);
                                        $perDay = (float) $bd->effective_price / $days;
                                    @endphp
                                    <div class="tier-perday">Less than ${{ number_format(ceil($perDay), 0) }}/day!</div>
                                @endif
                                <button type="button" class="tier-cta" data-select-bundle="{{ $bd->id }}">
                                    Reserve My Setup
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="tiers-empty">
                        <i class="fas fa-box-open" style="font-size:32px;margin-bottom:12px;display:block;"></i>
                        No setups available for this tournament yet. Please check back soon.
                    </div>
                @endif

                {{-- Hidden bundle quantity inputs (one per bundle, value 0 or 1) --}}
                @foreach($availableBundles as $bd)
                    <input type="hidden" name="bundles[{{ $bd->id }}]" id="bundle_qty_{{ $bd->id }}" value="0">
                @endforeach
            </div>
        </section>

        {{-- ==================== HOW IT WORKS ==================== --}}
        <section class="how-section">
            <div class="how-wrap">
                <h2 class="how-title">How It Works</h2>
                <div class="how-steps">
                    <div class="how-step">
                        <i class="fas fa-truck-fast icon"></i>
                        <div class="text"><strong>Delivered</strong>before Game 1</div>
                    </div>
                    <div class="how-arrow"><i class="fas fa-arrow-right"></i></div>
                    <div class="how-step">
                        <i class="fas fa-map-location-dot icon"></i>
                        <div class="text"><strong>Move</strong>field-to-field</div>
                    </div>
                    <div class="how-arrow"><i class="fas fa-arrow-right"></i></div>
                    <div class="how-step">
                        <i class="fas fa-comment-dots icon"></i>
                        <div class="text"><strong>Text us</strong>when done</div>
                    </div>
                    <div class="how-arrow"><i class="fas fa-arrow-right"></i></div>
                    <div class="how-step">
                        <i class="fas fa-truck icon"></i>
                        <div class="text"><strong>We pick up</strong>after your last game</div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ==================== ORDER FORM ==================== --}}
        <section class="order-section" id="order">
            <div class="order-wrap">
                <div class="order-main">
                    @if($errors->any())
                        <div class="alert">
                            <strong>Please fix the following:</strong>
                            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert">{{ session('error') }}</div>
                    @endif

                    <div class="order-card">
                        <h2 class="order-card-title" style="margin-bottom: 8px;">Lock In Your Setup</h2>
                        <p style="font-size: 15px; color: var(--gdv-muted); margin: 0 0 20px; font-weight: 500;">Takes 30 seconds. We’ll text you to confirm details.</p>

                        <div class="field-row">
                            <div class="field">
                                <div class="input-wrap">
                                    <i class="fas fa-user"></i>
                                    <input type="text" name="full_name" placeholder="Full Name" value="{{ old('full_name') }}" required>
                                </div>
                            </div>
                            <div class="field">
                                <div class="input-wrap">
                                    <i class="fas fa-phone"></i>
                                    <input type="tel" name="phone_number" placeholder="Phone Number" value="{{ old('phone_number') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <div class="input-wrap">
                                <i class="fas fa-people-group"></i>
                                <input type="text" name="team_name_with_age_group" placeholder="Team Name + Age Group" value="{{ old('team_name_with_age_group') }}" required>
                            </div>
                        </div>

                                                <div class="field">
                            <div class="input-wrap">
                                <i class="fas fa-user-tie"></i>
                                <input type="text" name="coach_name" placeholder="Coach Name" value="{{ old('coach_name') }}" required>
                            </div>
                        </div>
                        <div class="field">
                            <div class="input-wrap">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" placeholder="Email (optional)" value="{{ old('email') }}">
                            </div>
                        </div>

                        <label style="display:flex;gap:10px;align-items:flex-start;margin-top:14px;font-size:13px;color:var(--gdv-muted);line-height:1.5;">
                            <input type="checkbox" name="sms_opt_in" id="sms_opt_in" required style="margin-top:3px;flex-shrink:0;">
                            <span>I agree to receive text notifications about my rental (booking confirmations, delivery updates, event-day notices). Msg & data rates may apply. Reply STOP to opt out.</span>
                        </label>

                        <div style="margin-top:16px;padding:12px 14px;background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.2);border-radius:8px;font-size:13px;color:var(--gdv-text);display:flex;align-items:center;gap:10px;">
                            <i class="fas fa-shield-halved" style="color:var(--gdv-success);"></i>
                            <span>Secure & encrypted checkout</span>
                        </div>
                    </div>

                    {{-- Optional Add-Ons (à la carte items) --}}
                    @if(count($availableItems) > 0)
                        <div class="addons-card">
                            <h2 class="order-card-title">Add Optional Items <span style="font-size:13px;color:var(--gdv-muted);font-family:Inter,sans-serif;font-weight:500;letter-spacing:0;text-transform:none;">(skip if not needed)</span></h2>
                            <div class="addons-list">
                                @foreach($availableItems as $it)
                                    <div class="addon-row" data-item-id="{{ $it->id }}" data-item-price="{{ (float) $it->effective_price }}">
                                        <div class="addon-name">
                                            {{ $it->name }}
                                            @if(!empty($it->description))
                                                <small>{{ \Illuminate\Support\Str::limit(strip_tags($it->description), 60) }}</small>
                                            @endif
                                        </div>
                                        <div class="addon-price">${{ number_format((float) $it->effective_price, 0) }}</div>
                                        <div class="qty-ctrl">
                                            <button type="button" class="qty-dec" aria-label="Decrease">−</button>
                                            <input type="number" name="items[{{ $it->id }}]" value="0" min="0" max="20" readonly>
                                            <button type="button" class="qty-inc" aria-label="Increase">+</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="order-sidebar">
                    <div class="addons-card" id="insuranceCard" style="margin-top:0;">
                        <h2 class="order-card-title">Insurance Option</h2>
                        <div data-insurance-container style="display:flex;flex-direction:column;gap:12px;color:var(--gdv-white);">
                            <label style="display:flex;gap:10px;align-items:center;">
                                <input type="radio" name="insurance_option" value="none" checked> None
                            </label>
                        </div>
                    </div>

                    <div class="addons-card utility-card" id="promoCard">
                        <h2 class="order-card-title">Promo Code</h2>
                        <div class="promo-row">
                            <div class="field">
                                <div class="input-wrap">
                                    <i class="fas fa-tag"></i>
                                    <input type="text" id="promo_code" name="promo_code" placeholder="Enter code">
                                </div>
                            </div>
                            <button type="button" id="validateCouponBtn" class="pay-btn promo-apply-btn">Apply</button>
                        </div>
                    </div>

                    <div class="addons-card utility-card" id="waiverCard">
                        <h2 class="order-card-title">Damage Waiver</h2>
                        <div id="waiverSubtitle" class="waiver-subtitle"></div>
                        <div data-waiver-container class="waiver-options">
                            <label style="display:flex;gap:10px;align-items:center;">
                                <input type="checkbox" name="damage_waiver_options[]" value="20" data-price="20" id="waiver_20"> Add damage waiver — $20.00
                            </label>
                        </div>
                    </div>
                {{-- Order summary / Stripe submit --}}
                    <div>
                    <div class="summary-card">
                        <div class="summary-label">Your Order</div>
                        <h3 class="summary-tier-name" id="selectedTierName"><span class="tier-tag">No setup</span> selected</h3>

                        <div class="summary-divider"></div>
                        <div class="summary-line"><span class="muted">Setup</span><span id="bundleSubtotal">$0</span></div>
                        <div class="summary-line"><span class="muted">Add-ons</span><span id="itemsSubtotal">$0</span></div>
                        <div class="summary-line"><span class="muted">Insurance</span><span id="insuranceAmount">$0</span></div>
                        <div class="summary-line"><span class="muted">Damage Waiver</span><span id="waiverAmount">$0</span></div>
                        <div class="summary-line" id="discountRow" style="display:none; color: var(--gdv-success); font-weight:700;">
                            <span>Discount</span><span id="discountAmount">-$0</span>
                        </div>
                        @if(isset($tournament) && (float) ($tournament->tax_rate ?? 0) > 0)
                            <div class="summary-line" id="taxRow"><span class="muted">Tax</span><span id="taxAmount">$0</span></div>
                        @endif
                        <div class="summary-divider"></div>
                        <div class="summary-line total"><span>Total</span><span id="totalAmount">$0 <span class="accent">ALL WEEKEND</span></span></div>

                        <button type="button" class="apple-pay-btn" id="applePayBtn" disabled aria-label="Pay with Apple Pay">
                            <span class="apple-pay-inner"><span class="apple-logo">&#63743;</span> Pay</span>
                        </button>

                        <button type="submit" class="pay-btn" id="payBtn" disabled>
                            <i class="fas fa-lock"></i>
                            <span id="payBtnText">Pay Now</span>
                        </button>
                        <div class="pay-meta">
                            <i class="fas fa-shield-halved"></i>Takes 30 seconds · Secured by Stripe
                        </div>

                        <div class="text-followup">
                            <i class="fas fa-comment-dots"></i>
                            <span>We’ll text you for team & game time after booking.</span>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </section>
    </form>

    {{-- ==================== SOCIAL PROOF ==================== --}}
    <section class="proof">
        <div class="proof-inner">
            <div>
                <div>
                    <span class="proof-stars">★★★★★</span>
                    <span class="proof-rating">5.0</span>
                </div>
                <p class="proof-quote">
                    “Everything was waiting for us. Super easy and worth every penny!”
                    <span class="author">— Baseball Mom</span>
                </p>
            </div>
            <div class="proof-stat">
                <i class="fas fa-shield-halved"></i>
                <span class="label">Fully Insured</span>
            </div>
            <div class="proof-stat">
                <i class="fas fa-thumbs-up"></i>
                <span class="label">5-Star Service</span>
            </div>
            <div class="proof-stat">
                <i class="fas fa-people-group"></i>
                <span class="label">Trusted by 100+ Families</span>
            </div>
        </div>
    </section>

    {{-- ==================== STICKY FOOTER ==================== --}}
    <div class="footer-cta">
        <div class="footer-cta-inner">
            <div class="footer-cta-text">
                <i class="fas fa-calendar-check"></i>
                <div>
                    <span class="head">Reserve Your Setup for {{ \Illuminate\Support\Str::limit($tournament->name ?? 'this tournament', 24) }}</span>
                    <span class="sub">Spots are limited — don’t miss out!</span>
                </div>
            </div>
            <a href="#tiers" class="footer-cta-btn">
                Reserve My Setup <i class="fas fa-chevron-right"></i>
            </a>
        </div>
    </div>

    {{-- ==================== SCRIPTS ==================== --}}
    <script>
        (function () {
            'use strict';

            const TAX_RATE = parseFloat(document.getElementById('tax_rate_input').value || '0') / 100;
            const fmt = n => '$' + (Math.round(n * 100) / 100).toFixed(2).replace(/\.00$/, '');

            // ===== Bundle (tier) selection =====
            const tierCards = document.querySelectorAll('.tier');
            const bundleQtyInputs = {};
            document.querySelectorAll('input[name^="bundles["]').forEach(el => {
                const m = el.name.match(/bundles\[(\d+)\]/);
                if (m) bundleQtyInputs[m[1]] = el;
            });

            let selectedBundle = null;

            function selectTier(card) {
                const id = card.dataset.bundleId;
                const price = parseFloat(card.dataset.bundlePrice || '0');
                const name = card.dataset.bundleName;

                // Reset all bundle quantities to 0
                Object.values(bundleQtyInputs).forEach(inp => inp.value = '0');
                document.querySelectorAll('.tier').forEach(t => t.classList.remove('is-selected'));

                // Set this one to 1
                if (bundleQtyInputs[id]) bundleQtyInputs[id].value = '1';
                card.classList.add('is-selected');

                selectedBundle = { id, price, name };
                recalc();

                // Smooth scroll to the order form
                document.getElementById('order').scrollIntoView({ behavior: 'smooth', block: 'start' });

                // Auto-focus first form field after scroll completes
                setTimeout(() => {
                    const firstField = document.querySelector('input[name="full_name"]');
                    if (firstField) {
                        try {
                            firstField.focus({ preventScroll: true });
                        } catch (_) {
                            firstField.focus();
                        }
                    }
                }, 700);
            }

            tierCards.forEach(card => {
                const btn = card.querySelector('.tier-cta');
                if (btn) btn.addEventListener('click', (e) => { e.stopPropagation(); selectTier(card); });
                // Whole card is clickable
                card.addEventListener('click', () => selectTier(card));
                // Keyboard accessible
                card.setAttribute('tabindex', '0');
                card.setAttribute('role', 'button');
                card.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        selectTier(card);
                    }
                });
            });

            // ===== Add-on quantity controls =====
            document.querySelectorAll('.addon-row').forEach(row => {
                const dec = row.querySelector('.qty-dec');
                const inc = row.querySelector('.qty-inc');
                const input = row.querySelector('input[type=number]');
                dec.addEventListener('click', () => {
                    input.value = Math.max(0, (parseInt(input.value, 10) || 0) - 1);
                    recalc();
                });
                inc.addEventListener('click', () => {
                    const max = parseInt(input.max, 10) || 20;
                    input.value = Math.min(max, (parseInt(input.value, 10) || 0) + 1);
                    recalc();
                });
            });

            // ===== Recalc totals =====
            function recalc() {
                let bundleSubtotal = selectedBundle ? selectedBundle.price : 0;

                let itemsSubtotal = 0;
                document.querySelectorAll('.addon-row').forEach(row => {
                    const qty = parseInt(row.querySelector('input[type=number]').value, 10) || 0;
                    const price = parseFloat(row.dataset.itemPrice || '0');
                    itemsSubtotal += qty * price;
                });

                const subtotal = bundleSubtotal + itemsSubtotal;
                                const itemsAndBundlesSubtotal = bundleSubtotal + itemsSubtotal;
                
                const insuranceInput = document.querySelector('input[name="insurance_option"]:checked');
                let insuranceVal = 0;
                if (insuranceInput && insuranceInput.value !== 'none') {
                    insuranceVal = parseFloat(insuranceInput.value) || 0;
                }

                let waiverVal = 0;
                document.querySelectorAll('input[name="damage_waiver_options[]"]:checked').forEach(c => {
                    waiverVal += parseFloat(c.getAttribute('data-price')) || 0;
                });
                
                const totalFees = insuranceVal + waiverVal;
                
                const discountValue = parseFloat(window.__appliedDiscount || 0) || 0;
                const discountType = window.__appliedDiscountType || null;
                let discountApplied = 0;

                if (discountType === 'percent') {
                    discountApplied = Math.min(itemsAndBundlesSubtotal, (itemsAndBundlesSubtotal * (discountValue / 100)));
                } else if (discountType === 'fixed') {
                    discountApplied = Math.min(itemsAndBundlesSubtotal, discountValue);
                }

                const taxableAmount = Math.max(0, itemsAndBundlesSubtotal - discountApplied);
                const tax = taxableAmount * TAX_RATE;
                const total = taxableAmount + totalFees + tax;
                
                document.getElementById('insuranceAmount').textContent = fmt(insuranceVal);
                document.getElementById('waiverAmount').textContent = fmt(waiverVal);
                const discountRow = document.getElementById('discountRow');
                const discountAmount = document.getElementById('discountAmount');
                if (discountApplied > 0) {
                    discountRow.style.display = 'flex';
                    discountAmount.textContent = `-${fmt(discountApplied).replace('$', '')}`;
                } else {
                    discountRow.style.display = 'none';
                }

                // Update DOM
                document.getElementById('bundleSubtotal').textContent = fmt(bundleSubtotal);
                document.getElementById('itemsSubtotal').textContent = fmt(itemsSubtotal);
                const taxEl = document.getElementById('taxAmount');
                if (taxEl) taxEl.textContent = fmt(tax);
                document.getElementById('totalAmount').innerHTML = fmt(total) + ' <span class="accent">ALL WEEKEND</span>';

                // Hidden inputs for backend
                document.getElementById('total_amount_input').value = total.toFixed(2);
                document.getElementById('tax_amount_input').value = tax.toFixed(2);

                // Selected tier label
                const tierLabel = document.getElementById('selectedTierName');
                if (selectedBundle) {
                    tierLabel.innerHTML = '<span class="tier-tag">' + escapeHtml(selectedBundle.name) + '</span> Setup';
                } else {
                    tierLabel.innerHTML = '<span class="tier-tag">No setup</span> selected';
                }

                // Enable/disable buttons + dynamic Pay Now text
                const payBtn = document.getElementById('payBtn');
                const applePayBtn = document.getElementById('applePayBtn');
                const payBtnText = document.getElementById('payBtnText');
                payBtn.disabled = !selectedBundle;
                if (applePayBtn) applePayBtn.disabled = !selectedBundle;
                if (payBtnText) {
                    payBtnText.textContent = selectedBundle ? ('Preview Mode (' + fmt(total) + ')') : 'Preview Mode';
                }

                // Update sticky footer text dynamically
                const footerHead = document.querySelector('.footer-cta-text .head');
                const footerBtn = document.querySelector('.footer-cta-btn');
                if (selectedBundle && footerHead && footerBtn) {
                    footerHead.textContent = selectedBundle.name + ' Setup — ' + fmt(total);
                    footerBtn.innerHTML = 'Pay Now <i class="fas fa-chevron-right"></i>';
                }
            }

            function escapeHtml(s) {
                return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
            }

            const applePayBtn = document.getElementById('applePayBtn');
            if (applePayBtn) {
                applePayBtn.addEventListener('click', () => {
                    if (!applePayBtn.disabled) {
                        alert('This is a template preview. Booking is disabled.');
                    }
                });
            }

            function setBookingSubmitLoading(isLoading) {
                const payBtnText = document.getElementById('payBtnText');
                const btn = document.getElementById('payBtn');
                if (!btn) return;
                if (isLoading) {
                    btn.disabled = true;
                    btn.setAttribute('aria-busy', 'true');
                    if (payBtnText) payBtnText.textContent = 'Loading...';
                } else {
                    btn.disabled = false;
                    btn.removeAttribute('aria-busy');
                    recalc(); // will restore the text
                }
            }

            document.getElementById('bookingForm').addEventListener('submit', async function (e) {
                e.preventDefault();
                alert('This is a template preview. Booking is disabled.');
                return false;
                
                const smsOptIn = document.getElementById('sms_opt_in');
                if (smsOptIn && !smsOptIn.checked) {
                    alert('Please agree to receive important text notifications to proceed.');
                    return false;
                }

                setBookingSubmitLoading(true);

                try {
                    const formData = new FormData(this);
                    const tokenE = document.querySelector('meta[name="csrf-token"]');
                    const resp = await fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': tokenE ? tokenE.getAttribute('content') : ''
                        },
                        body: formData
                    });

                    const data = await resp.json().catch(() => null);

                    if (!resp.ok) {
                        const msg = data?.message || 'Unable to start checkout. Please try again.';
                        alert(msg);
                        return;
                    }

                    const checkoutUrl = data?.checkout_url;
                    if (!checkoutUrl) {
                        alert('Stripe link cannot be generated. Try again.');
                        return;
                    }

                    window.location.href = checkoutUrl;
                } catch (err) {
                    alert('Unable to start checkout. Please try again.');
                } finally {
                    setBookingSubmitLoading(false);
                }
            });

            // ===== Sticky footer scroll behavior =====
            // Show footer only after user scrolls past the hero, hide near the order section
            const footerCta = document.querySelector('.footer-cta');
            const tiersSection = document.getElementById('tiers');
            const orderSection = document.getElementById('order');

            function updateFooterVisibility() {
                if (!footerCta || !tiersSection) return;
                const tiersTop = tiersSection.getBoundingClientRect().top;
                const orderTop = orderSection ? orderSection.getBoundingClientRect().top : Infinity;
                const viewportH = window.innerHeight;
                // Show when tiers are near/in view AND order form isn't in view yet
                const shouldShow = tiersTop < viewportH * 0.5 && orderTop > viewportH * 0.3;
                footerCta.classList.toggle('is-visible', shouldShow);
            }

            window.addEventListener('scroll', updateFooterVisibility, { passive: true });
            window.addEventListener('resize', updateFooterVisibility, { passive: true });
            updateFooterVisibility();

                    // Added from original file
        let bookingSettings = {
            insurance: [],
            waivers: []
        };

        async function loadBookingSettings() {
            try {
                const resp = await fetch('/api/settings/booking');
                if (!resp.ok) return;
                const contentType = resp.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) return;
                const data = await resp.json();
                bookingSettings.insurance = data.insurance_options || [];
                bookingSettings.waivers = data.damage_waiver_options || [];
                renderInsurance();
                renderWaivers();
                recalc();
            } catch (e) {
                // ignore
            }
        }

        function renderInsurance() {
            const container = document.querySelector('[data-insurance-container]');
            if (!container) return;
            const card = document.getElementById('insuranceCard');
            if (!bookingSettings.insurance || bookingSettings.insurance.length === 0) {
                if (card) card.style.display = 'none';
                container.innerHTML = '';
                return;
            }
            if (card) card.style.display = '';
            container.innerHTML = '';
            
            const none = document.createElement('label');
            none.style.cssText = 'display:flex;gap:10px;align-items:center;';
            none.innerHTML = '<input type="radio" name="insurance_option" value="none" checked> None';
            container.appendChild(none);
            bookingSettings.insurance.forEach(opt => {
                const lbl = document.createElement('label');
                lbl.style.cssText = 'display:flex;gap:10px;align-items:center;';
                lbl.innerHTML = `<input type="radio" name="insurance_option" value="${opt.price}"> ${opt.label} — $${Number(opt.price).toFixed(2)}`;
                container.appendChild(lbl);
            });
            container.querySelectorAll('input[name="insurance_option"]').forEach(r => r.addEventListener('change', recalc));
        }

        function renderWaivers() {
            const container = document.querySelector('[data-waiver-container]');
            if (!container) return;
            container.innerHTML = '';
            const subtitleEl = document.getElementById('waiverSubtitle');
            const descs = (bookingSettings.waivers || []).map(o => (o.description || '').trim()).filter(Boolean);
            if (subtitleEl) {
                if (descs.length > 0) {
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
                titleSpan.textContent = `${opt.label} — $${Number(opt.price).toFixed(2)}`;
                row.appendChild(checkbox);
                row.appendChild(titleSpan);
                wrapper.appendChild(row);

                container.appendChild(wrapper);
            });
            container.querySelectorAll('input[type="checkbox"]').forEach(c => c.addEventListener('change', recalc));
        }

        const valCoupon = document.getElementById('validateCouponBtn');
        if (valCoupon) {
            valCoupon.addEventListener('click', async function () {
                let hasItems = false,
                    hasBundles = false;
                document.querySelectorAll('.addon-row').forEach(row => {
                    const qty = parseInt(row.querySelector('input[type=number]').value) || 0;
                    if (qty > 0) hasItems = true;
                });
                if(selectedBundle) hasBundles = true;
                
                if (!hasItems && !hasBundles) {
                    alert('Please select at least one item or bundle before validating a promo code.');
                    return;
                }
                const code = (document.getElementById('promo_code').value || '').trim();
                if (!code) {
                    alert('Please enter a promo code.');
                    return;
                }
                const tokenE = document.querySelector('meta[name="csrf-token"]');
                try {
                    const resp = await fetch('/api/coupon/validate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': tokenE ? tokenE.getAttribute('content') : ''
                        },
                        body: JSON.stringify({
                            promo_code: code,
                            user_id: null
                        })
                    });
                    const contentType = resp.headers.get('content-type') || '';
                    const data = contentType.includes('application/json') ? await resp.json() : null;
                    if (resp.ok && data && data.success) {
                        const type = data.data?.discount_type;
                        const value = parseFloat(data.data?.discount_value || 0);
                        if ((type === 'fixed' || type === 'percent') && value > 0) {
                            window.__appliedDiscountType = type;
                            window.__appliedDiscount = value;
                            alert('Coupon applied successfully.');
                            recalc();
                        } else {
                            alert('Invalid discount returned.');
                        }
                    } else {
                        window.__appliedDiscountType = null;
                        window.__appliedDiscount = 0;
                        const msg = data?.errors?.promo_code?.[0] || data?.message || 'Coupon validation failed';
                        alert(msg);
                        recalc();
                    }
                } catch (err) {
                    alert('Unable to validate coupon. Please try again.');
                }
            });
        }

        const fbqOrigBtn = document.getElementById('payBtn');
        if (fbqOrigBtn) {
            fbqOrigBtn.addEventListener('click', function() {
                if (typeof fbq === 'function') {
                    const total = parseFloat(document.getElementById('total_amount_input')?.value || 0);
                    fbq('track', 'InitiateCheckout', {
                        content_name: 'Tournament Booking',
                        content_ids: [document.querySelector('input[name="tournament_id"]')?.value || ''],
                        content_type: 'product',
                        value: total,
                        currency: 'USD'
                    });
                }
            });
        }
        
        loadBookingSettings();
            recalc();
        })();
    </script>
</body>
</html>
