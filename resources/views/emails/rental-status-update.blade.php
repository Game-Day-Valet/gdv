<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Status Update - Game Day Valet</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background-color: #ffffff; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,.1); }
        .email-header { background-color: #C94C4C; padding: 40px 30px 20px; text-align: center; border-bottom: 1px solid #f0f0f0; }
        .logo { width: 250px; height: 160px; margin-bottom: 30px; }
        .header-title { color: #fff; font-size: 24px; font-weight: 700; margin-bottom: 10px; }
        .email-content { padding: 40px 30px; }
        .welcome-text { font-size: 18px; color: #333; margin-bottom: 20px; font-weight: 600; }
        .main-text { font-size: 16px; color: #666; margin-bottom: 25px; line-height: 1.8; white-space: pre-line; }
        .booking-details { background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #e9ecef; }
        .section-title { font-size: 16px; font-weight: 600; color: #333; margin-bottom: 15px; text-align: center; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e9ecef; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { font-weight: 600; color: #495057; min-width: 120px; }
        .detail-value { color: #333; text-align: right; flex: 1; }
        .footer-text { font-size: 14px; color: #999; margin-top: 25px; padding-top: 20px; border-top: 1px solid #f0f0f0; }
        @media (max-width: 600px) {
            .email-container { margin: 10px; border-radius: 6px; }
            .email-header { padding: 30px 20px 15px; }
            .email-content { padding: 30px 20px; }
        }
    </style>
    </head>
<body>
    <div class="email-container">
        <div class="email-header">
            <!-- <img src="{{ asset('images/logo.svg') }}" alt="Game Day Valet" class="logo"> -->
            <img src="https://drive.google.com/uc?export=download&id=1k0Ud895vW8x-xSzLKUZmtBFcU3I6j6P2" alt="Game Day Valet" class="logo">
            <h1 class="header-title">Rental Status Update: {{ $status_label ?? 'Updated' }}</h1>
        </div>

        <div class="email-content">
            <h2 class="welcome-text">Hello {{ $user->name ?? 'Customer' }},</h2>

            <p class="main-text">
                {{ $email_content !== '' ? $email_content : ("Your rental #" . ($rental->id ?? '') . " is now " . ($status_label ?? 'updated') . ".") }}
            </p>

            <div class="booking-details">
                <h3 class="section-title">Booking Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Rental #:</span>
                    <span class="detail-value">{{ $rental->id ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tournament:</span>
                    <span class="detail-value">{{ $tournament->name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Sport:</span>
                    <span class="detail-value">{{ $sport->name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Team Name:</span>
                    <span class="detail-value">{{ $rental->team_name_with_age_group ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">{{ $status_label ?? 'Updated' }}</span>
                </div>
                @if(!empty($rental->estimated_delivery_time))
                <div class="detail-row">
                    <span class="detail-label">Estimated Delivery:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($rental->estimated_delivery_time)->format('d M Y H:i') }}</span>
                </div>
                @endif
            </div>

            <div class="footer-text">
                <p>Best regards,<br><strong>Game Day Valet Team</strong></p>
            </div>
        </div>
    </div>
</body>
</html>


