<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Rental System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- App favicon -->
    <link rel="shortcut icon" href="/images/logo-sm.png">
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

        .profile-image-upload {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .current-profile-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border-color);
            flex-shrink: 0;
        }

        .profile-image-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--light-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--border-color);
            flex-shrink: 0;
        }

        .profile-image-placeholder i {
            color: var(--secondary-color);
            font-size: 1.5rem;
        }

        .file-input-container {
            flex: 1;
        }

        .file-input-container small {
            display: block;
            margin-top: 5px;
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
                        @if($user->profile_image)
                            <img src="{{ asset('images/profile_images/' . $user->profile_image) }}"
                                 alt="Profile Image"
                                 style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                        @else
                            <i class="fas fa-user"></i>
                        @endif
                    </div>
                    <h3 class="profile-name">{{ $user->name ?? 'User Name' }}</h3>
                    <p class="profile-email">{{ $user->email ?? 'user@example.com' }}</p>
                </div>

                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-label">Total Rentals</span>
                        <span class="stat-value">{{ is_array($rentals) ? count($rentals) : 0 }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Active Rentals</span>
                        <span class="stat-value">{{ count(array_filter($rentals, fn($r) => is_scalar($r['status'] ?? '') && ($r['status'] ?? '') === 'active')) }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Completed</span>
                        <span class="stat-value">{{ count(array_filter($rentals, fn($r) => is_scalar($r['status'] ?? '') && ($r['status'] ?? '') === 'completed')) }}</span>
                    </div>
                </div>
            </div>

            <div class="profile-content">
                <div class="content-tabs">
                    <button class="tab-button active" onclick="showTab('profile')">Profile</button>
                    <button class="tab-button" onclick="showTab('rentals')">Rental History</button>
                    <button class="tab-button" onclick="showTab('settings')">Settings</button>
                </div>

                <div id="profile-tab" class="tab-content active">
                    <h3>Edit Profile</h3>
                    <form action="{{ route('rentalsystem.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label class="form-label">Profile Image</label>
                            <div class="profile-image-upload">
                                @if($user->profile_image)
                                    <img src="{{ asset('images/profile_images/' . $user->profile_image) }}"
                                         alt="Current Profile Image"
                                         class="current-profile-image">
                                @else
                                    <div class="profile-image-placeholder">
                                        <i class="fas fa-user"></i>
                                    </div>
                                @endif
                                <div class="file-input-container">
                                    <input type="file" class="form-control" name="profile_image"
                                           accept="image/*">
                                    <small class="text-muted">Max size: 2MB. Supported formats: JPG, PNG, GIF</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="name"
                                   value="{{ $user->name ?? '' }}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email"
                                   value="{{ $user->email ?? '' }}" readonly>
                            <small class="text-muted">Email cannot be changed</small>
                        </div>

                                                <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="contact_number"
                                   value="{{ $user->contact_number ?? '' }}" required>
                        </div>

                        <button type="submit" class="btn-primary">Update Profile</button>
                    </form>
                </div>

                <div id="settings-tab" class="tab-content">
                    <h3>Settings</h3>
                    <div class="settings-card" style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px;">
                        <div class="setting-row" style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                            <div style="display:flex;gap:12px;align-items:center;">
                                <i class="fas fa-bell" style="color:#ef4444;"></i>
                                <div>
                                    <div style="font-weight:600;color:#111827;">Push Notifications (FCM)</div>
                                    <div style="font-size:13px;color:#6b7280;">Receive app push alerts for booking and status updates.</div>
                                </div>
                            </div>
                            <label class="switch">
                                <input id="toggleFcm" type="checkbox" {{ $user->fcm_notification ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                        <div class="setting-row" style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                            <div style="display:flex;gap:12px;align-items:center;">
                                <i class="fas fa-envelope" style="color:#ef4444;"></i>
                                <div>
                                    <div style="font-weight:600;color:#111827;">Email Notifications</div>
                                    <div style="font-size:13px;color:#6b7280;">Get booking confirmations and status updates by email.</div>
                                </div>
                            </div>
                            <label class="switch">
                                <input id="toggleEmail" type="checkbox" {{ ($user->email_notification ?? true) ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                        <div class="setting-row" style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;">
                            <div style="display:flex;gap:12px;align-items:center;">
                                <i class="fas fa-sms" style="color:#ef4444;"></i>
                                <div>
                                    <div style="font-weight:600;color:#111827;">SMS Notifications</div>
                                    <div style="font-size:13px;color:#6b7280;">Receive important updates by text message.</div>
                                </div>
                            </div>
                            <label class="switch">
                                <input id="toggleSms" type="checkbox" {{ ($user->text_notification ?? true) ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
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
                                        <h4 class="rental-title">{{ is_scalar($rental['tournament_name']) ? $rental['tournament_name'] : 'Tournament' }}</h4>
                                    </div>
                                    <span class="rental-status status-{{ is_scalar($rental['status']) ? strtolower($rental['status'] ?? 'pending') : 'pending' }}">
                                        {{ is_scalar($rental['status']) ? $rental['status'] : 'Pending' }}
                                    </span>
                                </div>

                                <div class="rental-details">
                                    @if(isset($rental['rental_date']) && $rental['rental_date'])
                                        <div class="detail-item">
                                            <i class="fas fa-calendar-alt detail-icon"></i>
                                            <span>Rental Date: {{ \Carbon\Carbon::parse($rental['rental_date'])->format('M d, Y') }}</span>
                                        </div>
                                    @endif

                                    @if(isset($rental['total_amount']) && is_numeric($rental['total_amount']))
                                        <div class="detail-item">
                                            <i class="fas fa-dollar-sign detail-icon"></i>
                                            <span>Total: ${{ $rental['total_amount'] }}</span>
                                        </div>
                                    @endif

                                    @if(isset($rental['created_at']) && $rental['created_at'])
                                        <div class="detail-item">
                                            <i class="fas fa-clock detail-icon"></i>
                                            <span>Booked: {{ \Carbon\Carbon::parse($rental['created_at'])->format('M d, Y') }}</span>
                                        </div>
                                    @endif
                                </div>

                                @if(isset($rental['items']) && !empty($rental['items']) && is_array($rental['items']))
                                    <div class="rental-items">
                                        <div class="rental-items-title">Rented Items:</div>
                                        <div class="item-list">
                                            @foreach($rental['items'] as $item)
                                                @if(is_array($item) && isset($item['name']) && isset($item['quantity']))
                                                    <span class="item-tag">
                                                        {{ $item['name'] ?? 'Item' }} x{{ is_scalar($item['quantity']) ? $item['quantity'] : 1 }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if(isset($rental['bundles']) && !empty($rental['bundles']) && is_array($rental['bundles']))
                                    <div class="rental-items">
                                        <div class="rental-items-title">Rented Bundles:</div>
                                        <div class="item-list">
                                            @foreach($rental['bundles'] as $bundle)
                                                @if(is_array($bundle) && isset($bundle['name']))
                                                    <span class="item-tag">
                                                        {{ $bundle['name'] ?? 'Bundle' }}
                                                    </span>
                                                @endif
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
            const target = document.getElementById(tabName + '-tab');
            if (target) { target.classList.add('active'); }

            // Add active class to clicked button
            event.target.classList.add('active');
        }

        // Profile image preview functionality
        document.addEventListener('DOMContentLoaded', function() {
            const profileImageInput = document.querySelector('input[name="profile_image"]');
            if (profileImageInput) {
                profileImageInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const currentImage = document.querySelector('.current-profile-image');
                            const placeholder = document.querySelector('.profile-image-placeholder');

                            if (currentImage) {
                                currentImage.src = e.target.result;
                            } else if (placeholder) {
                                // Replace placeholder with image
                                const newImage = document.createElement('img');
                                newImage.src = e.target.result;
                                newImage.alt = 'Profile Image Preview';
                                newImage.className = 'current-profile-image';
                                placeholder.parentNode.replaceChild(newImage, placeholder);
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });

        // Simple switch styles
        (function(){
            const style = document.createElement('style');
            style.textContent = `
            .switch{position:relative;display:inline-block;width:52px;height:28px}
            .switch input{opacity:0;width:0;height:0}
            .slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#d1d5db;transition:.2s;border-radius:999px}
            .slider:before{position:absolute;content:"";height:22px;width:22px;left:3px;top:3px;background:white;transition:.2s;border-radius:999px;box-shadow:0 1px 2px rgba(0,0,0,.2)}
            input:checked + .slider{background:#ef4444}
            input:checked + .slider:before{transform:translateX(24px)}
            `;
            document.head.appendChild(style);
        })();

        // Toggle handlers → hit a light web endpoint that updates the user flags
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        async function setPref(type, enabled){
            try{
                const resp = await fetch('{{ route('rentalsystem.profile.notifications') }}',{
                    method:'POST',
                    headers:{'X-CSRF-TOKEN': csrf,'Accept':'application/json','Content-Type':'application/json'},
                    body: JSON.stringify({type, enabled})
                });
                if(!resp.ok){ throw new Error('Failed'); }
            }catch(e){ /* revert on failure */ return false; }
            return true;
        }
        const fcm = document.getElementById('toggleFcm');
        const email = document.getElementById('toggleEmail');
        const sms = document.getElementById('toggleSms');
        if(fcm){ fcm.addEventListener('change', async ()=>{ const ok = await setPref('fcm', fcm.checked); if(!ok){ fcm.checked = !fcm.checked; } }); }
        if(email){ email.addEventListener('change', async ()=>{ const ok = await setPref('email', email.checked); if(!ok){ email.checked = !email.checked; } }); }
        if(sms){ sms.addEventListener('change', async ()=>{ const ok = await setPref('sms', sms.checked); if(!ok){ sms.checked = !sms.checked; } }); }
    </script>
</body>
</html>
