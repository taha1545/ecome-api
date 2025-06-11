<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your One-Time Password (OTP)</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .email-header {
            background-color: #4A6FFF;
            color: white;
            padding: 25px;
            text-align: center;
        }

        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .email-body {
            padding: 30px;
            text-align: center;
        }

        .message {
            font-size: 16px;
            margin-bottom: 25px;
            color: #555;
        }

        .otp-container {
            margin: 30px auto;
            max-width: 320px;
            padding: 20px;
            background-color: #f7f9fc;
            border-radius: 6px;
            border: 1px solid #e1e5eb;
        }

        .otp-code {
            font-family: 'Courier New', monospace;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 5px;
            color: #4A6FFF;
            margin: 10px 0;
        }

        .expiry-notice {
            font-size: 14px;
            color: #777;
            margin-top: 15px;
            font-style: italic;
        }

        .security-notice {
            margin-top: 30px;
            padding: 15px;
            background-color: #fff8e1;
            border-left: 4px solid #ffc107;
            text-align: left;
            font-size: 14px;
            color: #666;
        }

        .email-footer {
            background-color: #f5f5f5;
            padding: 20px;
            text-align: center;
            color: #777;
            font-size: 14px;
        }

        @media only screen and (max-width: 600px) {
            .email-header {
                padding: 20px;
            }

            .email-header h1 {
                font-size: 22px;
            }

            .email-body {
                padding: 20px;
            }

            .otp-code {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Your One-Time Password</h1>
        </div>

        <div class="email-body">
            <p class="message">We received a request to access your account. Please use the following code to verify your identity:</p>

            <div class="otp-container">
                <div class="otp-code">{{ $otp }}</div>
                <p class="expiry-notice">This code will expire in 15 minutes</p>
            </div>

            <p>If you didn't request this code, you can safely ignore this email. Someone might have typed your email address by mistake.</p>

            <div class="security-notice">
                <strong>Security Tip:</strong> We will never ask you to share this code with anyone. If someone asks for your code, they may be trying to scam you.
            </div>
        </div>

        <div class="email-footer">
            <p>© {{ date('Y') }} Ecommerce Platform. All rights reserved.</p>
            <p>This is an automated message, please do not reply.</p>
        </div>
    </div>
</body>
</html>