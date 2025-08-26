<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - Game Day Valet</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="/images/logo-sm.png">
    <style>
        :root {
            --primary: #c94c4c;
            --ink: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
            --bg: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--ink);
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
        }

        .navbar {
            border-bottom: 1px solid var(--border);
            background: #fff;
        }

        .navbar .inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            font-weight: 800;
            letter-spacing: -.3px;
            color: var(--ink);
            text-decoration: none;
            font-size: 18px;
        }

        .nav {
            display: flex;
            gap: 18px;
        }

        .nav a {
            color: var(--muted);
            text-decoration: none;
            font-weight: 600;
        }

        .nav a:hover {
            color: var(--ink);
        }

        .page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 48px 20px;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 56px;
            align-items: center;
        }

        @media (max-width: 960px) {
            .page {
                grid-template-columns: 1fr;
                padding: 28px 16px;
                gap: 28px;
            }
        }

        .hero h1 {
            font-size: 42px;
            line-height: 1.1;
            margin: 0 0 12px;
            font-weight: 900;
            letter-spacing: -0.6px;
        }

        .hero p {
            color: var(--muted);
            font-size: 16px;
            max-width: 580px;
        }

        .hero-illustration {
            margin-top: 24px;
            border: 1px dashed var(--border);
            border-radius: 16px;
            padding: 28px;
            color: var(--muted);
        }

        .hero-illustration img {
            border-radius: 16px;
            height: 300px;
            object-fit: cover;
        }

        .card {
            width: 100%;
            max-width: 520px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 8px 32px rgba(2, 8, 23, .06);
            padding: 24px;
        }

        .card h2 {
            margin: 0 0 6px;
            font-size: 24px;
            font-weight: 800;
        }

        .card .sub {
            margin: 0 0 18px;
            color: var(--muted);
        }

        .label {
            display: block;
            font-size: 12px;
            letter-spacing: .14em;
            color: #94a3b8;
            margin: 12px 0 8px;
            font-weight: 800;
        }

        .input {
            width: 100%;
            border: 1.6px solid var(--border);
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 15px;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgb(201 76 76 / 12%);
        }

        .btn-primary {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 0;
            border-radius: 12px;
            padding: 14px;
            font-weight: 800;
            color: #fff;
            background: var(--primary);
            cursor: pointer;
            box-shadow: 0 14px 28px rgba(201, 76, 76, .18);
        }
    </style>
</head>

<body>
    <header class="navbar">
        <div class="inner">
            <a class="brand" href="#">Game Day Valet</a>
            <nav class="nav">
                <a href="{{ route('rentalsystem.signup') }}">Create account</a>
                <a href="{{ route('rentalsystem.signin') }}">Sign in</a>
            </nav>
        </div>
    </header>

    <main class="page">
        <section class="hero">
            <h1>Set a new password</h1>
            <p>Enter your new password below to secure your account.</p>
            <div class="hero-illustration">
                <img src="{{ asset('images/main-thumbnail1.jpg') }}" width="100%" alt="">
            </div>
        </section>

        <section>
            <div class="card">
                <h2>New password</h2>
                <p class="sub">Choose a strong password</p>

                @if(session('success'))
                <div style="background:#ecfdf5;border:1px solid #10b98133;color:#065f46;padding:10px 12px;border-radius:10px;margin-bottom:10px;">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                <div style="background:#fef2f2;border:1px solid #ef444433;color:#991b1b;padding:10px 12px;border-radius:10px;margin-bottom:10px;">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                <div style="background:#fef2f2;border:1px solid #ef444433;color:#991b1b;padding:10px 12px;border-radius:10px;margin-bottom:10px;">
                    <ul style="margin:0; padding-left:18px;">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('rentalsystem.reset-password.submit') }}">
                    @csrf
                    <label class="label" for="password">NEW PASSWORD</label>
                    <input class="input @error('password') is-invalid @enderror" type="password" id="password" name="password" required>
                    @error('password')<div style="color:#b91c1c;font-size:12px;margin-top:6px;">{{ $message }}</div>@enderror

                    <label class="label" for="password_confirmation">CONFIRM PASSWORD</label>
                    <input class="input" type="password" id="password_confirmation" name="password_confirmation" required>

                    <div style="height:12px"></div>
                    <button class="btn-primary" type="submit">Update password</button>
                </form>
            </div>
        </section>
    </main>
</body>

</html>