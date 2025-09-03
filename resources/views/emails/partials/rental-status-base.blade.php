<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Rental Status Update' }}</title>
    @include('emails.partials.styles')
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="{{ asset('images/gdv-logo.png') }}" alt="Game Day Valet" class="logo">
            <h1 class="header-title">{{ $title ?? 'Rental Status Update' }}</h1>
            <p class="header-subtitle">Thank you for choosing Game Day Valet</p>
        </div>
        <div class="email-content">
            <h2 class="welcome-text">Hello {{ $user->name ?? 'Customer' }}!</h2>
            <p class="verification-text">{{ $email_content ?? '' }}</p>
            @include('emails.partials.rental-details')
        </div>
    </div>
</body>
</html>


