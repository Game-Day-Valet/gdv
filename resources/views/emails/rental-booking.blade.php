<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - Game Day Valet</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #ffffff;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            background-color: #C94C4C;
            padding: 40px 30px 20px;
            text-align: center;
            border-bottom: 1px solid #f0f0f0;
        }

        .logo {
            width: 250px;
            height: 160px;
            margin-bottom: 30px;
        }

        .header-title {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .header-subtitle {
            color: #ffffff;
            font-size: 16px;
        }

        .email-content {
            padding: 40px 30px;
        }

        .welcome-text {
            font-size: 20px;
            color: #333;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .verification-text {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.8;
        }

        .verification-button {
            display: inline-block;
            background-color: #C94C4C;
            color: #ffffff;
            text-decoration: none;
            padding: 16px 48px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-align: center;
            margin: 25px 0;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .verification-button:hover {
            background-color: #c82333;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }

        .verification-image {
            text-align: center;
            margin: 35px 0;
        }

        .verification-image img {
            max-width: 180px;
            height: auto;
        }

        .footer-text {
            font-size: 14px;
            color: #999;
            margin-top: 35px;
            padding-top: 25px;
            border-top: 1px solid #f0f0f0;
        }

        .app-name {
            color: #C94C4C;
            font-weight: 600;
        }

        .security-note {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 25px;
            border-left: 4px solid #C94C4C;
        }

        .security-note p {
            font-size: 14px;
            color: #666;
            margin: 0;
        }

        .booking-details {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin: 25px 0;
            border: 1px solid #e9ecef;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #495057;
            min-width: 120px;
        }

        .detail-value {
            color: #333;
            text-align: right;
            flex: 1;
        }

        .items-section {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 8px;
            margin: 25px 0;
            border: 1px solid #e9ecef;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f8f9fa;
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .total-section {
            background-color: #C94C4C;
            color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
            text-align: center;
        }

        .total-amount {
            font-size: 24px;
            font-weight: 700;
        }

        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 6px;
            }

            .email-header {
                padding: 30px 20px 15px;
            }

            .email-content {
                padding: 30px 20px;
            }

            .header-title {
                font-size: 24px;
            }

            .welcome-text {
                font-size: 18px;
            }

            .verification-button {
                padding: 14px 40px;
                font-size: 15px;
            }

            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                text-align: left;
            }

            .detail-value {
                text-align: left;
                margin-top: 5px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header Section -->
        <div class="email-header">
            <img src="{{ asset('images/logo.svg') }}" alt="Game Day Valet" class="logo">
            <h1 class="header-title">Your rental booking has been confirmed!</h1>
            <p class="header-subtitle">Thank you for choosing Game Day Valet</p>
        </div>

        <!-- Content Section -->
        <div class="email-content">
            <h2 class="welcome-text">Hello {{ $user->name ?? 'User' }}!</h2>

            <p class="verification-text">
                {{ $email_content ?? "Great news! Your rental booking has been confirmed successfully. We're excited to provide you with the equipment you need for your upcoming event." }}
            </p>

            <!-- Confirmation Image -->
            <div class="verification-image">
                <img src="{{ asset('images/svg/confirmation-email.svg') }}" alt="Booking Confirmation">
            </div>

            <!-- Booking Details -->
            <div class="booking-details">
                <h3 class="section-title">Booking Details</h3>

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
                    <span class="detail-value">{{ $rental->team_name ?? 'N/A' }}</span>
                </div>

            </div>

            @if(!empty($rental->items) && is_array($rental->items))
            <div class="items-section">
                <h3 class="section-title">Rented Items</h3>
                @foreach($rental->items as $item)
                @if(is_array($item) && isset($item['item_id']) && isset($item['quantity']))
                <div class="item-row">
                    <span>Item #{{ $item['item_id'] }}</span>
                    <span>Qty: {{ $item['quantity'] }}</span>
                </div>
                @endif
                @endforeach
            </div>
            @endif

            @if(!empty($rental->bundles) && is_array($rental->bundles))
            <div class="items-section">
                <h3 class="section-title">Rented Bundles</h3>
                @foreach($rental->bundles as $bundleId)
                @if(is_numeric($bundleId))
                <div class="item-row">
                    <span>Bundle #{{ $bundleId }}</span>
                    <span>Qty: 1</span>
                </div>
                @endif
                @endforeach
            </div>
            @endif

            <!-- Total Amount -->
            <div class="total-section">
                <div class="total-amount">Total Amount: ${{ number_format($rental->total_amount ?? 0, 2) }}</div>
                @if($rental->insurance_option)
                <div style="margin-top: 10px; font-size: 16px;">Insurance: ${{ number_format($rental->insurance_option, 2) }}</div>
                @endif
                @if($rental->damage_waiver)
                <div style="margin-top: 5px; font-size: 16px;">Damage Waiver: ${{ number_format($rental->damage_waiver, 2) }}</div>
                @endif
            </div>


            <!-- Security Note -->
            <div class="security-note">
                <p><strong>Important:</strong> Please keep this booking reference number for your records. If you have any questions about your booking, please contact our support team.</p>
            </div>

            <!-- Footer -->
            <div class="footer-text">
                <p>Best regards,<br>
                    <span class="app-name">Game Day Valet Team</span>
                </p>

                <p style="margin-top: 15px; font-size: 12px; color: #999;">
                    If you have any questions, please contact our support team.
                </p>
            </div>
        </div>
    </div>
</body>

</html>