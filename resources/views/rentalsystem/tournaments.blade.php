<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Tournaments - Game Day Valet</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- App favicon -->
    <link rel="shortcut icon" href="/images/logo-sm.png">
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
        @media (min-width:860px){ .list { grid-template-columns:1fr 1fr 1fr; } }

        .card { background:#fff; border:1.6px solid var(--border-color); border-radius:18px; overflow:hidden; box-shadow:0 1px 0 rgba(0,0,0,.02); transform: translateY(10px); opacity: 0; animation: fadeUp .5s ease forwards; }
        .media { position:relative; background:#f3f4f6; width:100%; }
        /* 16:9 ratio fallback for broader browser support */
        .media::before { content:""; display:block; padding-top:56.25%; }
        .media > img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; display:block; }
        .book {
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

        /* Modal Styling */
        .modal-content {
            border-radius: 18px;
            border: 2px solid var(--border-color);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .modal-header {
            border-radius: 18px 18px 0 0;
        }

        .modal-body {
            border-radius: 0 0 18px 18px;
        }

        .btn-close-white {
            filter: brightness(0) invert(1);
        }

        /* subtle appear animation */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-trophy"></i> Game Day Valet
            </div>
            <div class="user-menu"></div>
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
                        <div class="button-group" style="
                            position: absolute; 
                            bottom: 14px; 
                            left: 10px; 
                            right: 10px; 
                            display: flex; 
                            justify-content: space-between; 
                            align-items: center; 
                            width: auto; 
                            padding: 0 10px;
                        ">
                            <a href="{{ route('rentalsystem.tournament.details', $id) }}" class="btn btn-outline-light" style="
                                padding: 8px 16px; 
                                border-radius: 8px; 
                                border: 2px solid white; 
                                color: white; 
                                font-weight: 700; 
                                font-size: 0.9rem; 
                                text-decoration: none; 
                                background: rgba(0,0,0,0.4); 
                                backdrop-filter: blur(10px); 
                                transition: all 0.3s ease; 
                                white-space: nowrap; 
                                min-width: 80px; 
                                text-align: center;
                            " onmouseover="this.style.background='rgba(255,255,255,0.2)'; this.style.borderColor='rgba(255,255,255,0.8)';" 
                               onmouseout="this.style.background='rgba(0,0,0,0.4)'; this.style.borderColor='white';">
                                <i class="fas fa-info-circle"></i> Details
                            </a>
                            <button class="book" onclick="handleBooking({{ $id }})" style="
                                padding: 8px 16px; 
                                border-radius: 8px; 
                                border: none; 
                                color: #fff; 
                                font-weight: 700; 
                                font-size: 0.9rem; 
                                background: var(--primary-color); 
                                box-shadow: 0 4px 12px rgba(220, 53, 69, 0.25); 
                                white-space: nowrap; 
                                min-width: 80px; 
                                text-align: center; 
                                cursor: pointer;
                            ">Book Now</button>
                        </div>
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

    <!-- Login Required Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom: 2px solid var(--primary-color); background: var(--primary-color); color: white;">
                    <h5 class="modal-title" id="loginModalLabel" style="font-weight: 800; font-size: 1.2rem;">
                        <i class="fas fa-lock"></i> Login Required
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center" style="padding: 30px 20px;">
                    <div style="margin-bottom: 25px;">
                        <i class="fas fa-user-lock" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 15px;"></i>
                        <h6 style="font-weight: 700; color: var(--dark-color); margin-bottom: 10px;">Access Required</h6>
                        <p style="color: var(--secondary-color); margin: 0; line-height: 1.5;">
                            You need to login or create an account to book this tournament.
                        </p>
                    </div>
                    <div class="d-flex flex-column gap-3" style="max-width: 250px; margin: 0 auto;">
                        <a href="{{ route('rentalsystem.signin') }}" class="btn btn-primary" style="
                            background: var(--primary-color); 
                            border: none; 
                            padding: 12px 24px; 
                            border-radius: 12px; 
                            font-weight: 700; 
                            font-size: 1rem;
                            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.25);
                        ">
                            <i class="fas fa-sign-in-alt"></i> Sign In
                        </a>
                        <a href="{{ route('rentalsystem.signup') }}" class="btn btn-outline-primary" style="
                            border: 2px solid var(--primary-color); 
                            color: var(--primary-color); 
                            background: transparent; 
                            padding: 12px 24px; 
                            border-radius: 12px; 
                            font-weight: 700; 
                            font-size: 1rem;
                            transition: all 0.3s ease;
                        " onmouseover="this.style.background='var(--primary-color)'; this.style.color='white';" 
                           onmouseout="this.style.background='transparent'; this.style.color='var(--primary-color)';">
                            <i class="fas fa-user-plus"></i> Sign Up
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function handleBooking(tournamentId) {
            // Always go to booking page; login will be enforced on Confirm Booking
            window.location.href = "{{ route('rentalsystem.rental-booking', ':id') }}".replace(':id', tournamentId);
        }
    </script>
</body>
</html>