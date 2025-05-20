<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - LUGAIT IMS</title>
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
            color: #17a2b8;
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
                <i class="bi bi-question-circle-fill"></i>
                🔍
            </div>
            <h1 class="error-title">Page Not Found</h1>
            <p class="error-message">
                The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
            </p>
            <div class="mt-4">
                <a href="{{ route('user.loginPage') }}" class="btn btn-primary">Return to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
