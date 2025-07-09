<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account - Wash Luxuria</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .app-info {
            text-align: center;
            margin-bottom: 20px;
        }
        .app-info img {
            width: 80px;
            margin-bottom: 10px;
        }
        .app-info h2 {
            margin: 0;
            font-size: 1.5em;
            color: #2c3e50;
        }
        .app-info p {
            margin: 0;
            color: #555;
        }
        .header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 2.2em;
            margin-bottom: 10px;
        }
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        .content { padding: 40px; }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .warning-box h3 {
            color: #856404;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .warning-box h3::before {
            content: "⚠️";
            margin-right: 10px;
            font-size: 1.2em;
        }
        .data-list {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .data-list h4 {
            color: #495057;
            margin-bottom: 15px;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
        }
        .data-list ul { list-style: none; padding: 0; }
        .data-list li {
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .data-list li:last-child { border-bottom: none; }
        .data-list .status {
            background: #dc3545;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
        }
        .form-group { margin-bottom: 25px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }
        .form-group input[type="email"],
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        .form-group input[type="email"]:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 25px;
        }
        .checkbox-group input[type="checkbox"] {
            margin-top: 4px;
            transform: scale(1.2);
        }
        .checkbox-group label {
            margin-bottom: 0;
            font-weight: normal;
            color: #dc3545;
        }
        .btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
            margin-top: 15px;
        }
        .btn-secondary:hover { background: #5a6268; }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .contact-info {
            background: #e8f4fd;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
            text-align: center;
        }
        .contact-info h4 { color: #2c3e50; margin-bottom: 15px; }
        .contact-info p { margin-bottom: 10px; color: #495057; }
        @media (max-width: 768px) {
            .container { margin: 10px; border-radius: 10px; }
            .header { padding: 20px; }
            .header h1 { font-size: 1.8em; }
            .content { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="app-info">
            <img src="/logo.png" alt="Wash Luxuria">
            <h2>Wash Luxuria - Car Wash App</h2>
            <p>Developer: Wash Luxuria</p>
            <p style="color:#555; margin-top:10px;">
                This page allows users of the Wash Luxuria app to request deletion of their account and all associated data, in compliance with Google Play policies.
            </p>
        </div>
        <div class="header">
            <h1>Delete your Wash Luxuria Account</h1>
            <p>Request to delete your account and all associated data</p>
        </div>
        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="warning-box">
                <h3>Important Notice</h3>
                <p>Once you delete your account, all your associated data will be permanently deleted and cannot be recovered. Please make sure to save any important information before proceeding.</p>
            </div>
            <div class="data-list">
                <h4>Data that will be deleted:</h4>
                <ul>
                    <li>
                        <span>Personal account information</span>
                        <span class="status">Will be deleted</span>
                    </li>
                    <li>
                        <span>Email and phone number</span>
                        <span class="status">Will be deleted</span>
                    </li>
                    <li>
                        <span>Registered car information</span>
                        <span class="status">Will be deleted</span>
                    </li>
                    <li>
                        <span>Order and service history</span>
                        <span class="status">Will be deleted</span>
                    </li>
                    <li>
                        <span>Saved addresses</span>
                        <span class="status">Will be deleted</span>
                    </li>
                    <li>
                        <span>Service preferences</span>
                        <span class="status">Will be deleted</span>
                    </li>
                </ul>
            </div>
            <form method="POST" action="{{ route('delete.account.request') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Email associated with your account:</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required 
                           placeholder="Enter your email address">
                </div>
                <div class="form-group">
                    <label for="reason">Reason for deletion (optional):</label>
                    <textarea id="reason" name="reason" placeholder="Tell us why you want to delete your account...">{{ old('reason') }}</textarea>
                </div>
                <div class="checkbox-group">
                    <input type="checkbox" id="confirmation" name="confirmation" required>
                    <label for="confirmation">
                        I confirm that I want to permanently delete my account and all associated data. I understand that this action cannot be undone.
                    </label>
                </div>
                <button type="submit" class="btn btn-danger">
                    Request Account Deletion
                </button>
            </form>
            <a href="/" class="btn btn-secondary">
                Back to Home
            </a>
            <div class="contact-info">
                <h4>Need Help?</h4>
                <p>If you have any questions about account deletion, you can contact us:</p>
                <p><strong>Email:</strong> info@washluxuria.com</p>
                <p><strong>Phone:</strong> +971502711549</p>
            </div>
        </div>
    </div>
</body>
</html> 