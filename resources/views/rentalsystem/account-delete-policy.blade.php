<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Delete Policy - Rental System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary:#dc3545; --ink:#0f172a; --muted:#64748b; --border:#e5e7eb; }
        body { margin:0; font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; color:var(--ink); }
        .header { background:var(--primary); color:#fff; }
        .header .inner { max-width:1000px; margin:0 auto; padding:16px 20px; display:flex; align-items:center; justify-content:space-between; }
        .brand { font-weight:800; text-decoration:none; color:#fff; }
        .container { max-width:1000px; margin:0 auto; padding:28px 20px 60px; }
        .card { background:#fff; border:1px solid var(--border); border-radius:14px; padding:20px; }
        h1 { margin:0 0 10px; font-size:28px; font-weight:900; }
        h2 { margin-top:22px; font-size:20px; }
        p { color:var(--muted); line-height:1.7; }
        ul { color:var(--muted); line-height:1.7; }
        .muted { color:var(--muted); }
        .back { color:#fff; text-decoration:none; font-weight:600; }
    </style>
    </head>
<body>
    <header class="header">
        <div class="inner">
            <a href="{{ route('rentalsystem.sports') }}" class="brand"><i class="fas fa-trophy"></i> Rental System</a>
            <a href="{{ route('rentalsystem.profile') }}" class="back">Back to Profile</a>
        </div>
    </header>
    <main class="container">
        <div class="card">
            <h1>Delete My Account – Rental System</h1>
            <p class="muted">If you would like to delete your account and associated data from Rental System, please follow the steps below:</p>

            <h2>How to request account deletion</h2>
            <ul>
                <li>Open the Rental System app/website.</li>
                <li>Go to Profile &gt; Settings &gt; Delete Account.</li>
                <li>Confirm the deletion request.</li>
                <li>Alternatively, you can email us at <strong>support@rentalsystem.com</strong> with the subject line “Delete My Account”.</li>
            </ul>

            <h2>What data will be deleted</h2>
            <ul>
                <li>Your profile information (name, email, phone).</li>
                <li>Your login credentials.</li>
                <li>Order history and saved preferences.</li>
            </ul>

            <h2>What data may be retained</h2>
            <ul>
                <li>Transaction data related to payments (kept for 3 years to comply with financial/legal obligations via Stripe).</li>
                <li>Any data required by law for fraud prevention or dispute resolution.</li>
            </ul>

            <p class="muted">Once your account is deleted, you will no longer be able to log in or access any of your data.</p>
        </div>
    </main>
</body>
</html>


