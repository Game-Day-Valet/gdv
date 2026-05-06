<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error | Game Day Valet</title>
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
            text-decoration: none;
            color: white;
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 80px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .error-code {
            font-size: 120px;
            font-weight: 900;
            color: var(--primary-color);
            line-height: 1;
            margin-bottom: 20px;
            text-shadow: 4px 4px 0px rgba(220, 53, 69, 0.1);
        }

        .error-message {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 15px;
        }

        .error-description {
            font-size: 1.1rem;
            color: var(--secondary-color);
            max-width: 500px;
            margin-bottom: 35px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 6px 16px rgba(220, 53, 69, 0.25);
        }

        .btn-primary:hover {
            background: #c82333;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(220, 53, 69, 0.35);
        }

        .error-icon {
            font-size: 80px;
            color: #dee2e6;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .error-code {
                font-size: 80px;
            }
            .error-message {
                font-size: 1.8rem;
            }
            .main-container {
                padding: 60px 15px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="{{ route('rentalsystem.sports') }}" class="logo">
                <i class="fas fa-trophy"></i> Game Day Valet
            </a>
        </div>
    </header>

    <div class="main-container">
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="error-code">500</div>
        <h1 class="error-message">Internal Server Error</h1>
        <p class="error-description">
            Something went wrong on our end. We are working to fix it. Please try again later.
        </p>
        <a href="{{ route('rentalsystem.sports') }}" class="btn-primary">
            <i class="fas fa-home me-2"></i> Back to Home
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
