<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Successfully Booked - Game Day Valet</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="/images/logo-sm.png">
    
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
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=1364397922375498&ev=PageView&noscript=1"
    /></noscript>
    <!-- End Facebook Pixel Global Init -->
    <style>
        :root {
            --primary-color: #dc3545;
            --secondary-color: #6b7280;
            --dark-color: #111827;
            --border-color: #e5e7eb;
            --success-color: #16a34a;
            --bg: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            background: var(--bg);
            color: var(--dark-color);
        }

        .header {
            background: var(--primary-color);
            color: #fff;
            padding: 18px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
        }

        .header-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            font-weight: 900;
            letter-spacing: .02em;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 34px 18px;
        }

        .success-card {
            border: 1.6px solid var(--border-color);
            border-radius: 16px;
            padding: 22px;
            background: #fff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .06);
        }

        .title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 26px;
            font-weight: 900;
            margin: 4px 0 6px;
        }

        .title .icon {
            color: var(--success-color);
            font-size: 26px;
        }

        .subtitle {
            color: var(--secondary-color);
            font-size: 15px;
            margin-bottom: 16px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .metric {
            border: 1.6px solid var(--border-color);
            border-radius: 12px;
            padding: 14px;
            background: #fff;
        }

        .label {
            color: #6b7280;
            font-size: 12px;
            letter-spacing: .14em;
            font-weight: 800;
        }

        .value {
            margin-top: 6px;
            font-size: 18px;
            font-weight: 800;
            color: var(--dark-color);
            word-break: break-word;
        }

        .actions {
            display: flex;
            gap: 12px;
            margin-top: 18px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            padding: 12px 16px;
            border-radius: 12px;
            font-weight: 900;
            box-shadow: 0 10px 24px rgba(220, 53, 69, .18);
        }

        .btn-primary {
            background: var(--primary-color);
            color: #fff;
            border: 0;
        }

        .btn-outline {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            box-shadow: none;
        }

        @media (max-width: 980px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .title {
                font-size: 22px;
            }
        }
    </style>
    <script>
        // Optional soft redirect after 15s
        // setTimeout(function(){ window.location.href = '{{ route('rentalsystem.sports') }}'; }, 150000);
    </script>
    <!-- Facebook Pixel Purchase Event -->
    <script>
    @if(isset($rental) && (float)($rental->total_amount ?? 0) > 0)
    window.addEventListener('load', function() {
        if (typeof fbq === 'function') {
            fbq('track', 'Purchase', {
                value: {{ (float)$rental->total_amount }},
                currency: 'USD',
                content_ids: ['{{ $rental->id }}'],
                content_type: 'product',
                content_name: '{{ addslashes(optional($rental->tournament)->name ?? "Tournament Rental") }}'
            });
        }
    });
    @endif
    </script>
    <!-- End Facebook Pixel Purchase Event -->
    <meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">
</head>

<body>
    <header class="header">
        <div class="header-inner">
            <div class="logo"><i class="fas fa-trophy"></i> Game Day Valet</div>
            <div></div>
        </div>
    </header>

    <main class="container">
        <section class="success-card">
            <div class="title"><i class="fa-solid fa-circle-check icon"></i> <span>Rentals Booked Successfully</span></div>
            <div class="subtitle">Thank you! Your payment was successful and your rental booking has been placed.</div>

            <div class="grid">
                <div class="metric">
                    <div class="label">RENTAL #</div>
                    <div class="value">GDV-{{ str_pad($rental->id ?? 0, 6, '0', STR_PAD_LEFT) }}</div>
                </div>
                <div class="metric">
                    <div class="label">TOURNAMENT</div>
                    <div class="value">{{ optional($rental->tournament)->name ?? 'Tournament' }}</div>
                </div>
                <div class="metric">
                    <div class="label">TOTAL PAID</div>
                    <div class="value">${{ number_format((float)($rental->total_amount ?? 0), 2) }}</div>
                </div>
            </div>

            <div class="actions">
                <a class="btn btn-primary" href="{{ route('rentalsystem.sports') }}"><i class="fa-solid fa-arrow-left"></i> Back to Sports</a>
                <a class="btn btn-outline" href="{{ route('rentalsystem.tournament.details', optional($rental->tournament)->id ?? 1) }}">View Tournament</a>
            </div>
        </section>
    </main>
</body>

</html>