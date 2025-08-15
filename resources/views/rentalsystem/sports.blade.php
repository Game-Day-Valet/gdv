<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports - Rental System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #dc3545;
            --secondary-color: #6c757d;
            --dark-color: #343a40;
            --light-gray: #f8f9fa;
            --border-color: #dee2e6;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

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

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-name {
            color: white;
            font-weight: 500;
        }

        .logout-btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
            color: white;
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 10px;
        }

        .page-title p {
            font-size: 1.1rem;
            color: var(--secondary-color);
        }

        .sports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .sport-card {
            background: white;
            border: 2px solid var(--border-color);
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .sport-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(220, 53, 69, 0.15);
        }

        .sport-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: var(--light-gray);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--primary-color);
        }

        .sport-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 10px;
        }

        .sport-description {
            color: var(--secondary-color);
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .tournament-count {
            background: var(--primary-color);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .no-sports {
            text-align: center;
            padding: 60px 20px;
            color: var(--secondary-color);
        }

        .no-sports i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: var(--border-color);
        }

        .loading {
            text-align: center;
            padding: 60px 20px;
        }

        .spinner {
            border: 4px solid var(--border-color);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .main-container {
                padding: 20px 15px;
            }

            .page-title h1 {
                font-size: 2rem;
            }

            .sports-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .sport-card {
                padding: 20px;
            }
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
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-trophy"></i> Rental System
            </div>
            <div class="user-menu">
                @if(Auth::check())
                    <span class="user-name">{{ Auth::user()->name }}</span>
                    <a href="{{ route('rentalsystem.profile') }}" class="logout-btn">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <a href="{{ route('rentalsystem.logout') }}" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                @else
                    <a href="{{ route('rentalsystem.signin') }}" class="logout-btn">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </a>
                    <a href="{{ route('rentalsystem.signup') }}" class="logout-btn">
                        <i class="fas fa-user-plus"></i> Sign Up
                    </a>
                @endif
            </div>
        </div>
    </header>

    <div class="main-container">
        <div class="page-title">
            <h1>Sports</h1>
            <p>Choose a sport to view available tournaments</p>
        </div>

        <div class="sports-grid">
            @forelse($sports as $sport)
                <a href="{{ route('rentalsystem.tournaments', $sport->id) }}" class="sport-card">
                    <div class="sport-icon">
                        @php($name = strtolower($sport->name ?? ''))
                        @switch($name)
                            @case('cricket')
                                <i class="fa-solid fa-baseball-bat-ball"></i>
                                @break
                            @case('football')
                                <i class="fa-regular fa-futbol"></i>
                                @break
                            @case('golf')
                                <i class="fa-solid fa-golf-ball-tee"></i>
                                @break
                            @case('hockey')
                                <i class="fa-solid fa-hockey-puck"></i>
                                @break
                            @case('baseball')
                                <i class="fa-solid fa-baseball"></i>
                                @break
                            @default
                                <i class="fa-solid fa-medal"></i>
                        @endswitch
                    </div>
                    <h3 class="sport-name">{{ $sport->name }}</h3>
                    <p class="sport-description">
                        {{ $sport->description ?? 'Explore tournaments and book equipment for ' . strtolower($sport->name) }}
                    </p>
                    <div class="tournament-count">
                        {{ $sport->tournaments_count ?? 0 }} Tournaments
                    </div>
                </a>
            @empty
                <div class="no-sports">
                    <i class="fas fa-sports"></i>
                    <h3>No Sports Available</h3>
                    <p>There are currently no sports available. Please check back later.</p>
                </div>
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
                            You need to login or create an account to access this feature.
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
</body>
</html> 