<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tournament->name }} - Tournament Details</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #dc3545;
            --secondary-color: #6b7280;
            --dark-color: #111827;
            --light-gray: #f8f9fa;
            --border-color: #e5e7eb;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            color: var(--dark-color);
        }

        /* Header */
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

        /* Hero Section */
        .hero-section {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .hero-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            display: block;
        }
        .hero-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
            padding: 40px 30px 30px;
        }
        .hero-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0 0 10px 0;
        }
        .hero-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        @media (max-width: 768px) {
            .content-grid { grid-template-columns: 1fr; gap: 30px; }
        }

        /* Info Cards */
        .info-card {
            background: white;
            border: 2px solid var(--border-color);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .info-card h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .info-card h3 i {
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        /* Info Items */
        .info-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid var(--light-gray);
        }
        .info-item:last-child { border-bottom: none; }
        .info-icon {
            width: 40px;
            height: 40px;
            background: var(--light-gray);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        .info-content h4 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0 0 4px 0;
            color: var(--dark-color);
        }
        .info-content p {
            font-size: 0.9rem;
            margin: 0;
            color: var(--secondary-color);
        }

        /* Action Card */
        .action-card {
            background: linear-gradient(135deg, var(--primary-color), #c82333);
            color: white;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            position: sticky;
            top: 20px;
        }
        .action-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .action-card p {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .btn-primary-custom {
            background: white;
            color: var(--primary-color);
            border: none;
            padding: 15px 25px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            color: var(--primary-color);
        }
        .btn-outline-custom {
            background: transparent;
            color: white;
            border: 2px solid white;
            padding: 15px 25px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn-outline-custom:hover {
            background: white;
            color: var(--primary-color);
        }

        /* Breadcrumb */
        .breadcrumb-nav {
            margin-bottom: 30px;
        }
        .breadcrumb-nav a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        .breadcrumb-nav a:hover {
            text-decoration: underline;
        }
        .breadcrumb-nav .separator {
            margin: 0 10px;
            color: var(--secondary-color);
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-active { background: var(--success-color); color: white; }
        .status-upcoming { background: var(--warning-color); color: var(--dark-color); }
        .status-completed { background: var(--secondary-color); color: white; }
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
        <!-- Breadcrumb Navigation -->
        <div class="breadcrumb-nav">
            <a href="{{ route('rentalsystem.sports') }}">Sports</a>
            <span class="separator">/</span>
            <a href="{{ route('rentalsystem.tournaments', $tournament->sport_id ?? 1) }}">{{ $sport->name ?? 'Sport' }}</a>
            <span class="separator">/</span>
            <span>{{ $tournament->name }}</span>
        </div>

        <!-- Hero Section -->
        <div class="hero-section">
            <img src="{{ $tournament->image ? asset('storage/' . $tournament->image) : 'https://images.unsplash.com/photo-1522778119026-d647f0596c20?q=80&w=1200&auto=format&fit=crop' }}" 
                 alt="{{ $tournament->name }}" class="hero-image">
            <div class="hero-overlay">
                <h1 class="hero-title">{{ $tournament->name }}</h1>
                <p class="hero-subtitle">
                    <i class="fas fa-map-marker-alt"></i> {{ $tournament->location ?? 'Location TBA' }}
                    @if($tournament->start_date)
                        <span style="margin-left: 20px;">
                            <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($tournament->start_date)->format('d M Y') }}
                        </span>
                    @endif
                </p>
            </div>
        </div>

        <div class="content-grid">
            <!-- Left Column - Tournament Information -->
            <div class="left-column">
                <!-- Basic Information -->
                <div class="info-card">
                    <h3><i class="fas fa-info-circle"></i> Tournament Information</h3>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="info-content">
                            <h4>Tournament Name</h4>
                            <p>{{ $tournament->name }}</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <h4>Location</h4>
                            <p>{{ $tournament->location ?? 'Location to be announced' }}</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="info-content">
                            <h4>Start Date</h4>
                            <p>{{ $tournament->start_date ? \Carbon\Carbon::parse($tournament->start_date)->format('d M Y, l') : 'Start date to be announced' }}</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="info-content">
                            <h4>End Date</h4>
                            <p>{{ $tournament->end_date ? \Carbon\Carbon::parse($tournament->end_date)->format('d M Y, l') : 'End date to be announced' }}</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="info-content">
                            <h4>Sport</h4>
                            <p>{{ $sport->name ?? 'Sport' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Tournament Duration -->
                @if($tournament->start_date && $tournament->end_date)
                <div class="info-card">
                    <h3><i class="fas fa-clock"></i> Tournament Duration</h3>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="info-content">
                            <h4>Duration</h4>
                            @php
                                $startDate = \Carbon\Carbon::parse($tournament->start_date);
                                $endDate = \Carbon\Carbon::parse($tournament->end_date);
                                $duration = $startDate->diffInDays($endDate) + 1;
                            @endphp
                            <p>{{ $duration }} day{{ $duration > 1 ? 's' : '' }} ({{ $startDate->format('d M Y') }} to {{ $endDate->format('d M Y') }})</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Additional Details -->
                @if($tournament->description || $tournament->rules || $tournament->prize_pool)
                <div class="info-card">
                    <h3><i class="fas fa-file-alt"></i> Additional Details</h3>
                    @if($tournament->description)
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-align-left"></i>
                        </div>
                        <div class="info-content">
                            <h4>Description</h4>
                            <p>{{ $tournament->description }}</p>
                        </div>
                    </div>
                    @endif
                    @if($tournament->rules)
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-gavel"></i>
                        </div>
                        <div class="info-content">
                            <h4>Rules</h4>
                            <p>{{ $tournament->rules }}</p>
                        </div>
                    </div>
                    @endif
                    @if($tournament->prize_pool)
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="info-content">
                            <h4>Prize Pool</h4>
                            <p>{{ $tournament->prize_pool }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Contact Information - Hidden/Commented -->
                {{-- 
                <div class="info-card">
                    <h3><i class="fas fa-phone"></i> Contact Information</h3>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <h4>Email</h4>
                            <p>{{ $tournament->contact_email ?? 'contact@example.com' }}</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="info-content">
                            <h4>Phone</h4>
                            <p>{{ $tournament->contact_phone ?? '+1 (555) 123-4567' }}</p>
                        </div>
                    </div>
                </div>
                --}}
            </div>

            <!-- Right Column - Action Card -->
            <div class="right-column">
                <div class="action-card">
                    <h3><i class="fas fa-rocket"></i> Ready to Book?</h3>
                    <p>Get your equipment ready for this exciting tournament. Book now and secure your rental items!</p>
                    
                    <div class="action-buttons">
                        @if(Auth::check())
                            <a href="{{ route('rentalsystem.rental-booking', $tournament->id) }}" class="btn-primary-custom">
                                <i class="fas fa-shopping-cart"></i> Book Equipment Now
                            </a>
                        @else
                            <a href="{{ route('rentalsystem.signin') }}" class="btn-primary-custom">
                                <i class="fas fa-sign-in-alt"></i> Sign In to Book
                            </a>
                        @endif
                        <a href="{{ route('rentalsystem.tournaments', $tournament->sport_id ?? 1) }}" class="btn-outline-custom">
                            <i class="fas fa-arrow-left"></i> Back to Tournaments
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 