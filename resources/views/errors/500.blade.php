<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error - LUGAIT IMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
        }
        .error-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        .error-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #343a40;
        }
        .error-message {
            color: #6c757d;
            margin-bottom: 30px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            padding: 10px 20px;
            font-weight: 500;
        }
        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <div class="error-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
                ⚠️
            </div>
            <h1 class="error-title">Server Error</h1>
            <p class="error-message">
                We're sorry, but something went wrong on our end. Our team has been notified and is working to fix the issue.
            </p>
            <p class="error-message">
                Please try again later or contact support if the problem persists.
            </p>
            <div class="mt-4">
                <a href="{{ route('user.loginPage') }}" class="btn btn-primary">Return to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
