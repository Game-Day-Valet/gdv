<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions - Game Day Valet</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary-color:#dc3545; --secondary:#6b7280; --border:#e5e7eb; }
        body { font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; margin:0; color:#111827; background:#ffffff; }
        .header { background: var(--primary-color); color:#fff; padding:20px 0; box-shadow:0 2px 10px rgba(0,0,0,.1); }
        .header-content { max-width: 1000px; margin:0 auto; padding:0 20px; display:flex; align-items:center; justify-content:space-between; }
        .logo { font-size:1.5rem; font-weight:800; }
        .main { max-width: 1000px; margin: 0 auto; padding: 30px 20px 60px; }
        .hero { background:#f8f9fa; border:1px solid var(--border); border-radius:14px; padding:24px; margin-bottom:22px; }
        .title { font-size: 2rem; margin: 0 0 6px; font-weight: 800; }
        .subtitle { color:var(--secondary); margin:0; }
        .card { border:1.6px solid var(--border); border-radius:14px; padding:22px; }
        .content { line-height:1.8; color:#374151; }
        .content h2, .content h3 { color:#111827; margin-top:1.25em; }
        .content p { margin: 0.75em 0; }
        .footer { margin-top:40px; text-align:center; color:var(--secondary); font-size:.9rem; }
        @media (max-width: 680px){ .title{ font-size:1.6rem; } }
    </style>
    </head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo"><i class="fas fa-file-contract"></i> Game Day Valet</div>
            <div>
                <a href="{{ route('rentalsystem.sports') }}" style="color:#fff;text-decoration:none;font-weight:600;">
                    <i class="fas fa-home"></i> Home
                </a>
            </div>
        </div>
    </header>
    <main class="main">
        <section class="hero">
            <h1 class="title">Terms & Conditions</h1>
            <p class="subtitle">Please review the terms that govern your use of our services.</p>
        </section>
        <section class="card">
            <div class="content">
                @php $has = false; @endphp
                @if(isset($terms) && count($terms) > 0)
                    @foreach($terms as $it)
                        @php
                            $title = is_array($it) ? ($it['title'] ?? '') : ($it->title ?? '');
                            $desc = is_array($it) ? ($it['description'] ?? '') : ($it->description ?? '');
                        @endphp
                        @if($title || $desc)
                            @php $has = true; @endphp
                            @if(!empty($title))
                                <h2>{{ $title }}</h2>
                            @endif
                            {!! $desc !!}
                        @endif
                    @endforeach
                @endif
                @unless($has)
                    <p>No terms and conditions available.</p>
                @endunless
            </div>
        </section>
        <div class="footer">© {{ date('Y') }} Game Day Valet</div>
    </main>
</body>
</html>


