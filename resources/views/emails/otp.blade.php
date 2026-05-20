<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .container {
            background-color: #ffffff;
            max-width: 600px;
            margin: 0 auto;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            color: #7c3aed;
            margin-bottom: 30px;
        }
        .otp-box {
            background-color: #f9fafb;
            border: 2px dashed #7c3aed;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            margin: 30px 0;
        }
        .otp-code {
            font-size: 36px;
            font-weight: bold;
            color: #7c3aed;
            letter-spacing: 8px;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>MCQ App</h1>
            <h2>Email Verification</h2>
        </div>

        <p>Hello {{ $name }},</p>

        <p>Thank you for registering with MCQ App! Please use the following code to verify your email address:</p>

        <div class="otp-box">
            <div class="otp-code">{{ $otp }}</div>
        </div>

        <p><strong>This code will expire in 10 minutes.</strong></p>

        <p>If you didn't request this code, please ignore this email.</p>

        <div class="footer">
            <p>&copy; 2026 MCQ App. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
