<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Game Day Valet</title>
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

        .link-text {
            word-break: break-all;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            font-size: 14px;
            color: #666;
            border: 1px solid #e9ecef;
        }

        .sign-in-link {
            color: #C94C4C;
            text-decoration: none;
            font-weight: 600;
        }

        .sign-in-link:hover {
            text-decoration: underline;
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
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header Section -->
        <div class="email-header">
            <!-- <img src="{{ asset('images/gdv-logo.png') }}" alt="Game Day Valet" class="logo"> -->
            <img src="https://drive.google.com/uc?export=download&id=1k0Ud895vW8x-xSzLKUZmtBFcU3I6j6P2" alt="Game Day Valet" class="logo">
            <h1 class="header-title">Your account has been created successfully.</h1>
            <p class="header-subtitle">Please verify your email address to continue</p>
        </div>

        <!-- Content Section -->
        <div class="email-content">
            <h2 class="welcome-text">Hello {{ $name }}!</h2>

            <p class="verification-text">
                Thank you for registering with <span class="app-name">Game Day Valet</span>.
                To complete your registration and access your account, please use the 6-digit verification code below:
            </p>

            <!-- Verification Image -->
            <div class="verification-image">
                <!-- <img src="{{ asset('images/svg/confirmation-email.svg') }}" alt="Email Verification"> -->
                <!-- <img src="https://drive.google.com/file/d/1R3Kvu8bhqyXs6ISnG92HhPw40KxWKd4s" alt="Booking Confirmation"> -->
            </div>

            <!-- OTP Code Display -->
            <div style="text-align: center; margin: 30px 0;">
                <div style="display: inline-block; background: #f5f5f5; padding: 20px 40px; font-size: 28px; font-weight: bold; letter-spacing: 6px; border-radius: 8px;">
                    {{ $otp }}
                </div>
            </div>

            <!-- Security Note -->
            <div class="security-note">
                <p><strong>Security Note:</strong> This code will expire in 60 minutes. If you did not create this account, you can safely ignore this email.</p>
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