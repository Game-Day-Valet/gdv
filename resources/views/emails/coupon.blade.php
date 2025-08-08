<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Special Offer - {{ $coupon->code }}</title>
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

        .offer-text {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.8;
        }

        .coupon-section {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 8px;
            margin: 30px 0;
            text-align: center;
            border: 2px dashed #C94C4C;
        }

        .coupon-code {
            font-size: 32px;
            font-weight: 900;
            color: #C94C4C;
            letter-spacing: 4px;
            margin: 20px 0;
            background-color: #ffffff;
            padding: 15px 30px;
            border-radius: 6px;
            display: inline-block;
            border: 2px solid #C94C4C;
        }

        .coupon-details {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin: 25px 0;
            border-left: 4px solid #C94C4C;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 12px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #495057;
            font-size: 14px;
        }

        .detail-value {
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
        }

        .instructions {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
        }

        .instructions h3 {
            color: #856404;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .instructions ol {
            margin: 0;
            padding-left: 20px;
            color: #856404;
        }

        .instructions li {
            margin: 6px 0;
            font-size: 14px;
        }

        .expiry-warning {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin: 25px 0;
            text-align: center;
            font-weight: 600;
            font-size: 14px;
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

            .coupon-code {
                font-size: 24px;
                letter-spacing: 2px;
                padding: 12px 20px;
            }

            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header Section -->
        <div class="email-header">
            <img src="{{ asset('images/logo.svg') }}" alt="Game Day Valet" class="logo">
            <h1 class="header-title">Special Offer Just for You!</h1>
            <p class="header-subtitle">Exclusive discount on your next purchase</p>
        </div>

        <!-- Content Section -->
        <div class="email-content">
            <h2 class="welcome-text">Hello {{ $name }}!</h2>

            <p class="offer-text">
                Thank you for being a valued customer of <span class="app-name">Game Day Valet</span>.
                We're excited to offer you an exclusive discount on your next purchase!
            </p>

            <!-- Coupon Section -->
            <div class="coupon-section">
                <h3 style="margin-bottom: 20px; color: #C94C4C; font-size: 20px;">Your Exclusive Coupon</h3>
                <div class="coupon-code">{{ $coupon->code }}</div>
                <p style="color: #666; font-size: 14px; margin-top: 15px;">Copy this code and use it at checkout!</p>
            </div>

            <!-- Coupon Details -->
            <div class="coupon-details">
                <div class="detail-row">
                    <span class="detail-label">Discount Type:</span>
                    <span class="detail-value">{{ ucfirst($coupon->type) }} Discount</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Discount Value:</span>
                    <span class="detail-value">
                        @if($coupon->type === 'fixed')
                            ${{ number_format($coupon->value, 2) }} OFF
                        @else
                            {{ $coupon->value }}% OFF
                        @endif
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Valid From:</span>
                    <span class="detail-value">{{ $coupon->starts_at ? $coupon->starts_at->format('M d, Y') : 'No start date' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Expires On:</span>
                    <span class="detail-value">{{ $coupon->expires_at ? $coupon->expires_at->format('M d, Y') : 'No expiration' }}</span>
                </div>
                @if($coupon->max_uses)
                <div class="detail-row">
                    <span class="detail-label">Usage Limit:</span>
                    <span class="detail-value">{{ $coupon->max_uses }} uses ({{ $coupon->used }} used)</span>
                </div>
                @endif
            </div>

            <!-- Instructions -->
            <div class="instructions">
                <h3>How to use your coupon:</h3>
                <ol>
                    <li>Browse our products and services</li>
                    <li>Add your desired items to the cart</li>
                    <li>At checkout, enter the coupon code: <strong>{{ $coupon->code }}</strong></li>
                    <li>Enjoy your exclusive discount!</li>
                </ol>
            </div>

            @if($coupon->expires_at)
            <div class="expiry-warning">
                ⏰ <strong>Limited Time Offer!</strong> This exclusive discount expires on {{ $coupon->expires_at->format('M d, Y') }}
            </div>
            @endif

            <!-- Footer -->
            <div class="footer-text">
                <p>Best regards,<br>
                    <span class="app-name">Game Day Valet Team</span>
                </p>

                <p style="margin-top: 15px; font-size: 12px; color: #999;">
                    If you have any questions about this offer, please contact our support team.
                </p>
            </div>
        </div>
    </div>
</body>

</html>
