<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Rental System</title>
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

        .nav-btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .nav-btn:hover {
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

        .profile-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .profile-sidebar {
            background: white;
            border: 2px solid var(--border-color);
            border-radius: 16px;
            padding: 30px;
            height: fit-content;
        }

        .profile-avatar {
            text-align: center;
            margin-bottom: 30px;
        }

        .avatar-circle {
            width: 100px;
            height: 100px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 2.5rem;
            color: white;
        }

        .profile-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 5px;
        }

        .profile-email {
            color: var(--secondary-color);
            font-size: 0.9rem;
        }

        .profile-stats {
            margin-top: 30px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-label {
            color: var(--secondary-color);
            font-size: 0.9rem;
        }

        .stat-value {
            color: var(--primary-color);
            font-weight: 600;
        }

        .profile-content {
            background: white;
            border: 2px solid var(--border-color);
            border-radius: 16px;
            padding: 30px;
        }

        .content-tabs {
            display: flex;
            border-bottom: 2px solid var(--border-color);
            margin-bottom: 30px;
        }

        .tab-button {
            background: none;
            border: none;
            padding: 15px 25px;
            color: var(--secondary-color);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }

        .tab-button.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }

        .tab-button:hover {
            color: var(--primary-color);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-color);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        .rental-item {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .rental-item:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.1);
        }

        .rental-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .rental-title {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 5px;
        }

        .rental-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-completed {
            background: #f8d7da;
            color: #721c24;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .rental-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--secondary-color);
            font-size: 0.9rem;
        }

        .detail-icon {
            color: var(--primary-color);
            width: 16px;
        }

        .rental-items {
            background: var(--light-gray);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .rental-items-title {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 10px;
        }

        .item-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .item-tag {
            background: var(--primary-color);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
        }

        .no-rentals {
            text-align: center;
            padding: 40px 20px;
            color: var(--secondary-color);
        }

        .no-rentals i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--border-color);
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

            .profile-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .profile-sidebar, .profile-content {
                padding: 20px;
            }

            .content-tabs {
                flex-direction: column;
            }

            .tab-button {
                text-align: left;
                border-bottom: none;
                border-left: 3px solid transparent;
            }

            .tab-button.active {
                border-left-color: var(--primary-color);
                border-bottom-color: transparent;
            }
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
                <a href="{{ route('rentalsystem.sports') }}" class="nav-btn">
                    <i class="fas fa-sports"></i> Sports
                </a>
                <a href="{{ route('rentalsystem.logout') }}" class="nav-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <div class="main-container">
        <div class="page-title">
            <h1>My Profile</h1>
            <p>Manage your account and view rental history</p>
        </div>

        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <div class="avatar-circle">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3 class="profile-name">{{ $user['name'] ?? 'User Name' }}</h3>
                    <p class="profile-email">{{ $user['email'] ?? 'user@example.com' }}</p>
                </div>

                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-label">Total Rentals</span>
                        <span class="stat-value">{{ count($rentals) }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Active Rentals</span>
                        <span class="stat-value">{{ count(array_filter($rentals, fn($r) => ($r['status'] ?? '') === 'active')) }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Completed</span>
                        <span class="stat-value">{{ count(array_filter($rentals, fn($r) => ($r['status'] ?? '') === 'completed')) }}</span>
                    </div>
                </div>
            </div>

            <div class="profile-content">
                <div class="content-tabs">
                    <button class="tab-button active" onclick="showTab('profile')">Profile</button>
                    <button class="tab-button" onclick="showTab('rentals')">Rental History</button>
                </div>

                <div id="profile-tab" class="tab-content active">
                    <h3>Edit Profile</h3>
                    <form action="{{ route('rentalsystem.profile.update') }}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="name" 
                                   value="{{ $user['name'] ?? '' }}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" 
                                   value="{{ $user['email'] ?? '' }}" readonly>
                            <small class="text-muted">Email cannot be changed</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="phone" 
                                   value="{{ $user['phone'] ?? '' }}" required>
                        </div>

                        <button type="submit" class="btn-primary">Update Profile</button>
                    </form>
                </div>

                <div id="rentals-tab" class="tab-content">
                    <h3>Rental History</h3>
                    
                    @if(empty($rentals))
                        <div class="no-rentals">
                            <i class="fas fa-box-open"></i>
                            <h4>No Rentals Yet</h4>
                            <p>You haven't made any rentals yet. Start by exploring sports and tournaments!</p>
                            <a href="{{ route('rentalsystem.sports') }}" class="btn-primary">
                                Browse Sports
                            </a>
                        </div>
                    @else
                        @foreach($rentals as $rental)
                            <div class="rental-item">
                                <div class="rental-header">
                                    <div>
                                        <h4 class="rental-title">{{ $rental['tournament_name'] ?? 'Tournament' }}</h4>
                                    </div>
                                    <span class="rental-status status-{{ strtolower($rental['status'] ?? 'pending') }}">
                                        {{ $rental['status'] ?? 'Pending' }}
                                    </span>
                                </div>

                                <div class="rental-details">
                                    @if(isset($rental['start_date']))
                                        <div class="detail-item">
                                            <i class="fas fa-calendar-alt detail-icon"></i>
                                            <span>Start: {{ \Carbon\Carbon::parse($rental['start_date'])->format('M d, Y') }}</span>
                                        </div>
                                    @endif

                                    @if(isset($rental['end_date']))
                                        <div class="detail-item">
                                            <i class="fas fa-calendar-check detail-icon"></i>
                                            <span>End: {{ \Carbon\Carbon::parse($rental['end_date'])->format('M d, Y') }}</span>
                                        </div>
                                    @endif

                                    @if(isset($rental['total_amount']))
                                        <div class="detail-item">
                                            <i class="fas fa-dollar-sign detail-icon"></i>
                                            <span>Total: ${{ $rental['total_amount'] }}</span>
                                        </div>
                                    @endif

                                    @if(isset($rental['created_at']))
                                        <div class="detail-item">
                                            <i class="fas fa-clock detail-icon"></i>
                                            <span>Booked: {{ \Carbon\Carbon::parse($rental['created_at'])->format('M d, Y') }}</span>
                                        </div>
                                    @endif
                                </div>

                                @if(isset($rental['items']) && !empty($rental['items']))
                                    <div class="rental-items">
                                        <div class="rental-items-title">Rented Items:</div>
                                        <div class="item-list">
                                            @foreach($rental['items'] as $item)
                                                <span class="item-tag">
                                                    {{ $item['name'] ?? 'Item' }} x{{ $item['quantity'] ?? 1 }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));

            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => button.classList.remove('active'));

            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');

            // Add active class to clicked button
            event.target.classList.add('active');
        }
    </script>
</body>
</html> 