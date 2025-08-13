<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tournaments - Game Day Valet</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #dc3545; /* match sports page */
            --secondary-color: #6b7280;
            --dark-color: #111827;
            --light-gray: #f8f9fa;
            --border-color: #e5e7eb;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            color: var(--dark-color);
        }

        /* Header copied from sports page */
        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo { font-size: 1.5rem; font-weight: 700; }
        .user-menu { display: flex; align-items: center; gap: 20px; }
        .user-name { color: white; font-weight: 500; }
        .nav-btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .nav-btn:hover { background: rgba(255,255,255,0.3); color: white; }

        .main-container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
        .page-title { text-align: center; margin-bottom: 20px; }
        .page-title h1 { font-size: 2.0rem; font-weight: 800; color: var(--dark-color); margin-bottom: 6px; }
        .page-title p { font-size: 1rem; color: var(--secondary-color); }

        .toolbar { display:grid; grid-template-columns:1fr auto; gap:14px; align-items:center; margin:16px 0 24px; }
        .search-form { display:flex; gap:10px; }
        .search-form input[type="text"] { width:100%; border:1.6px solid var(--border-color); border-radius:14px; padding:12px 14px; font-size:15px; }
        .search-form button { border:0; padding:12px 16px; border-radius:12px; background:var(--primary-color); color:#fff; font-weight:800; cursor:pointer; }

        .list { display:grid; gap:22px; grid-template-columns:1fr; }
        @media (min-width:860px){ .list { grid-template-columns:1fr 1fr; } }

        .card { background:#fff; border:1.6px solid var(--border-color); border-radius:18px; overflow:hidden; box-shadow:0 1px 0 rgba(0,0,0,.02); }
        .media { position:relative; aspect-ratio: 16/9; background:#f3f4f6; }
        .media img { width:100%; height:100%; object-fit:cover; display:block; }
        .book {
            position:absolute; left:50%; bottom:14px; transform:translateX(-50%);
            padding:10px 18px; border-radius:10px; border:none; color:#fff; font-weight:800;
            background: var(--primary-color); box-shadow: 0 6px 16px rgba(220, 53, 69, 0.25);
        }
        .book:hover { filter: brightness(0.95); }
        .body { padding:12px 14px 16px; }
        .title-row { display:flex; align-items:center; justify-content:space-between; gap:10px; }
        .title { font-weight:800; font-size:16px; }
        .meta { color:var(--secondary-color); font-size:13px; margin-top:2px; }

        @media (max-width: 768px) {
            .header-content { flex-direction: column; gap: 15px; text-align: center; }
            .main-container { padding: 20px 15px; }
            .list { grid-template-columns: 1fr; gap: 20px; }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-trophy"></i> Rental System
            </div>
            <div class="user-menu">
                <span class="user-name">{{ session('user.name', 'User') }}</span>
                <a href="{{ route('rentalsystem.profile') }}" class="nav-btn"><i class="fas fa-user"></i> Profile</a>
                <a href="{{ route('rentalsystem.logout') }}" class="nav-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </header>

    <div class="main-container">
        <div class="page-title">
            <h1>Available Tournaments</h1>
            <p>Browse and book your rental for a tournament</p>
        </div>

        <div class="toolbar">
            <form class="search-form" method="GET" action="{{ route('rentalsystem.tournaments', $sportId) }}">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search tournament by name or location">
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
            <div></div>
        </div>

        <div class="list">
            @forelse($tournaments as $t)
                @php
                    $isArr = is_array($t);
                    $id = $isArr ? ($t['id'] ?? null) : ($t->id ?? null);
                    $name = $isArr ? ($t['name'] ?? '') : ($t->name ?? '');
                    $location = $isArr ? ($t['location'] ?? '') : ($t->location ?? '');
                    $img = $isArr ? ($t['image'] ?? null) : ($t->image ?? null);
                    $imageUrl = $img ? (Str::startsWith($img, ['http://','https://','/']) ? $img : asset('storage/'.$img)) : 'https://images.unsplash.com/photo-1522778119026-d647f0596c20?q=80&w=1200&auto=format&fit=crop';
                @endphp
                <div class="card">
                    <div class="media">
                        <img src="{{ $imageUrl }}" alt="tournament">
                        <a class="book" href="{{ route('rentalsystem.rental-booking', $id) }}">Book Now</a>
                    </div>
                    <div class="body">
                        <div class="title-row">
                            <div class="title">{{ $name }}</div>
                        </div>
                        <div class="meta">{{ $location ?: 'Location TBA' }}</div>
                    </div>
                </div>
            @empty
                <p>No tournaments available.</p>
            @endforelse
        </div>
    </div>
</body>
</html> 