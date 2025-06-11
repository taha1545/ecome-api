<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Our Ecommerce Platform</title>
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
            background-color: #FF4433;
            color: white;
            padding: 30px;
            text-align: center;
        }

        .email-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }

        .email-body {
            padding: 30px;
        }

        .welcome-message {
            font-size: 18px;
            margin-bottom: 25px;
            color: #333;
        }

        .user-name {
            font-weight: 600;
            color: #FF4433;
        }

        .feature-list {
            margin: 25px 0;
            padding: 0;
            list-style-type: none;
        }

        .feature-item {
            margin-bottom: 15px;
            padding-left: 25px;
            position: relative;
        }

        .feature-item:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #FF4433;
            font-weight: bold;
        }

        .cta-button {
            display: inline-block;
            background-color: #FF4433;
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 4px;
            font-weight: 600;
            margin: 20px 0;
        }

        .email-footer {
            background-color: #f5f5f5;
            padding: 20px;
            text-align: center;
            color: #777;
            font-size: 14px;
        }

        .social-links {
            margin: 15px 0;
        }

        .social-link {
            display: inline-block;
            margin: 0 10px;
            color: #555;
            text-decoration: none;
        }

        @media only screen and (max-width: 600px) {
            .email-header {
                padding: 20px;
            }

            .email-header h1 {
                font-size: 24px;
            }

            .email-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Welcome to Our Ecommerce Platform</h1>
        </div>

        <div class="email-body">
            <p class="welcome-message">
                Hello <span class="user-name">{{ $user->name }}</span>,
            </p>

            <p>Thank you for joining our ecommerce platform! We're excited to have you as part of our community.</p>

            <p>Here are a few things you can do with your new account:</p>

            <ul class="feature-list">
                <li class="feature-item">Browse our extensive product catalog</li>
                <li class="feature-item">Save your favorite products</li>
                <li class="feature-item">Track your orders in real-time</li>
                <li class="feature-item">Manage your profile and addresses</li>
                <li class="feature-item">Get exclusive deals and promotions</li>
            </ul>

            <p>Ready to start shopping?</p>

            <a href="{{ url('/') }}" class="cta-button">Explore Our Products</a>

            <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>

            <p>Happy shopping!</p>
        </div>

        <div class="email-footer">
            <p>© {{ date('Y') }} Ecommerce Platform. All rights reserved.</p>

            <div class="social-links">
                <a href="#" class="social-link">Facebook</a>
                <a href="#" class="social-link">Twitter</a>
                <a href="#" class="social-link">Instagram</a>
            </div>

            <p>You received this email because you signed up for an account on our platform.</p>
        </div>
    </div>
</body>
</html>