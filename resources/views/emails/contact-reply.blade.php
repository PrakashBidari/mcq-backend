<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Response to Your Contact Message</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            color: #1f2937;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .email-header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .email-header p {
            color: rgba(255, 255, 255, 0.9);
            margin: 10px 0 0;
            font-size: 16px;
        }
        .email-body {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        .original-message {
            background-color: #f9fafb;
            border-left: 4px solid #d1d5db;
            padding: 15px 20px;
            border-radius: 6px;
            color: #4b5563;
            font-size: 14px;
            line-height: 1.6;
            white-space: pre-wrap;
        }
        .reply-message {
            background-color: #f3e8ff;
            border-left: 4px solid #7c3aed;
            padding: 20px;
            border-radius: 6px;
            color: #1f2937;
            font-size: 15px;
            line-height: 1.7;
            white-space: pre-wrap;
        }
        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 5px 0;
            font-size: 14px;
            color: #6b7280;
        }
        .button {
            display: inline-block;
            background-color: #7c3aed;
            color: #ffffff;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
            font-size: 15px;
        }
        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>MCQ Hub Support</h1>
            <p>Response to Your Message</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <p class="greeting">Hi {{ $contactMessage->name }},</p>

            <p style="color: #4b5563; line-height: 1.6; margin-bottom: 20px;">
                Thank you for reaching out to us! We've received your message and our team has prepared a response for you.
            </p>

            <!-- Admin Reply -->
            <div class="section">
                <div class="section-title">Our Response</div>
                <div class="reply-message">{{ $contactMessage->admin_reply }}</div>
            </div>

            <div class="divider"></div>

            <!-- Original Message -->
            <div class="section">
                <div class="section-title">Your Original Message</div>
                <div class="original-message">{{ $contactMessage->message }}</div>
            </div>

            <!-- Call to Action -->
            <div style="text-align: center;">
                <p style="color: #6b7280; font-size: 14px; margin-bottom: 10px;">
                    Have more questions? Feel free to reply to this email or contact us directly.
                </p>
                <a href="mailto:{{ config('mail.from.address') }}" class="button">Contact Us Again</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>MCQ Hub</strong></p>
            <p>Empowering learners worldwide</p>
            <p style="margin-top: 15px; font-size: 12px; color: #9ca3af;">
                This email was sent because you contacted us through our website.
            </p>
        </div>
    </div>
</body>
</html>
