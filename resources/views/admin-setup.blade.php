<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup - LUGAIT IMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
        }
        .setup-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #343a40;
            font-weight: 700;
        }
        .alert {
            border-radius: 5px;
        }
        .user-details {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
        }
        .user-details h3 {
            margin-bottom: 15px;
            color: #343a40;
        }
        .detail-row {
            display: flex;
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: 600;
            width: 150px;
        }
        .login-link {
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-container">
            <div class="header">
                <h1>LUGAIT IMS Admin Setup</h1>
                <p class="text-muted">Inventory Management System</p>
            </div>
            
            @if($status == 'success')
                <div class="alert alert-success">
                    <strong>Success!</strong> {{ $message }}
                </div>
            @else
                <div class="alert alert-danger">
                    <strong>Error!</strong> {{ $message }}
                </div>
            @endif
            
            @if($user)
                <div class="user-details">
                    <h3>Admin User Details</h3>
                    <div class="detail-row">
                        <div class="detail-label">Full Name:</div>
                        <div>{{ $user->fullname }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Username:</div>
                        <div>{{ $user->username }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Email:</div>
                        <div>{{ $user->email }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Role:</div>
                        <div>Administrator</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Department:</div>
                        <div>{{ $user->department ? $user->department->department_name : 'N/A' }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Position:</div>
                        <div>{{ $user->position ? $user->position->position : 'N/A' }}</div>
                    </div>
                    
                    @if($status == 'success')
                        <div class="alert alert-info mt-3">
                            <strong>Note:</strong> The default password is <code>password</code>. Please change it after logging in.
                        </div>
                    @endif
                </div>
            @endif
            
            <div class="login-link">
                <a href="{{ route('user.loginPage') }}" class="btn btn-primary">Go to Login Page</a>
            </div>
        </div>
    </div>
</body>
</html>
