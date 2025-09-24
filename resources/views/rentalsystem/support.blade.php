<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - Game Day Valet</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary:#dc3545; --border:#e5e7eb; --text:#111827; --muted:#6b7280; }
        body { font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; margin:0; color:var(--text); background:#fff; }
        .header { background: var(--primary); color:#fff; padding:22px 0; box-shadow:0 2px 10px rgba(0,0,0,.08); }
        .wrap { max-width: 960px; margin: 0 auto; padding: 0 20px; }
        .brand { font-weight: 800; font-size: 22px; }
        .main { max-width: 960px; margin: 0 auto; padding: 30px 20px 60px; }
        .hero { background: #f8f9fa; border:1px solid var(--border); border-radius:16px; padding:24px; margin-bottom:20px; }
        h1 { margin:0 0 6px; font-weight: 800; font-size: 32px; }
        p.lead { margin:0; color:var(--muted); }
        .grid { display:grid; grid-template-columns: 1fr 1fr; gap:16px; }
        .card { border:1.6px solid var(--border); border-radius:14px; padding:18px; }
        .card h3 { margin:0 0 8px; }
        .card p { margin:6px 0; color:#374151; }
        .chip { display:inline-flex; align-items:center; gap:8px; border:1px solid var(--border); padding:8px 12px; border-radius:999px; font-weight:600; }
        .cta { margin-top:22px; display:flex; gap:12px; flex-wrap:wrap; }
        .btn { background: var(--primary); color:#fff; padding:12px 18px; border-radius:10px; text-decoration:none; font-weight:700; }
        .btn-outline { background:#fff; color: var(--primary); border:2px solid var(--primary); }
        @media (max-width: 720px){ .grid { grid-template-columns: 1fr; } h1{ font-size:26px; } }
    </style>
</head>
<body>
    <header class="header">
        <div class="wrap" style="display:flex;align-items:center;justify-content:space-between;">
            <div class="brand"><i class="fas fa-life-ring"></i> Game Day Valet</div>
            <div>
                <a href="{{ route('rentalsystem.sports') }}" style="color:#fff;text-decoration:none;font-weight:600;">Home</a>
            </div>
        </div>
    </header>
    <main class="main">
        <section class="hero">
            <h1>Support</h1>
            <p class="lead">Get help with your booking, payments, or product questions.</p>
        </section>
        <section class="grid">
            <div class="card">
                <h3><i class="fas fa-envelope"></i> Email</h3>
                <p>Our support team replies within 1 business day.</p>
                <div class="chip"><i class="fas fa-paper-plane"></i> support@gamedayvaletrentals.com</div>
            </div>
            <div class="card">
                <h3><i class="fas fa-phone"></i> Phone</h3>
                <p>Call us Mon–Fri, 9:00 AM – 5:00 PM (EST).</p>
                <div class="chip"><i class="fas fa-headset"></i> +1 (555) 123-4567</div>
            </div>
            <div class="card">
                <h3><i class="fas fa-comment-dots"></i> Live Chat</h3>
                <p>Start a chat from the website and a team member will assist you.</p>
                <div class="chip"><i class="fas fa-bolt"></i> Typical reply: under 5 minutes</div>
            </div>
            <div class="card">
                <h3><i class="fas fa-map-marker-alt"></i> Mailing Address</h3>
                <p>Game Day Valet<br>123 Stadium Way<br>Charlotte, NC 28202</p>
            </div>
        </section>
        <div class="cta">
            <a href="mailto:support@gamedayvaletrentals.com" class="btn"><i class="fas fa-paper-plane"></i> Email Support</a>
            <a href="tel:+15551234567" class="btn btn-outline"><i class="fas fa-phone"></i> Call Us</a>
        </div>
    </main>
</body>
</html>


