<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Request</title>
    <style>
        :root { --bg:#f5f5f5; --card:#fff; --text:#111; --muted:#5f5f5f; --accent:#111; --line:#d9d9d9; }
        * { box-sizing: border-box; }
        body { margin:0; padding:18px; background:transparent; color:var(--text); font-family:Poppins, "Segoe UI", Arial, sans-serif; }
        .shell { max-width:680px; margin:0 auto; background:transparent; border:1px solid var(--line); border-radius:14px; overflow:hidden; }
        .head { padding:18px 22px; border-bottom:1px solid var(--line); }
        .brand { margin:0; font-size:11px; text-transform:uppercase; letter-spacing:.08em; color:var(--accent); font-weight:600; }
        .title { margin:8px 0 0; font-size:20px; line-height:1.3; }
        .body { padding:22px; font-size:14px; line-height:1.7; }
        .details { margin:16px 0; border:1px solid var(--line); border-radius:10px; padding:12px 14px; background:#fcfcfc; }
        .details p { margin:0 0 8px; }
        .details p:last-child { margin-bottom:0; }
        .btn { display:inline-block; margin-top:18px; background:#111111; color:#ffffff !important; text-decoration:none; padding:11px 16px; border-radius:8px; font-weight:600; }
        .foot { padding:14px 22px; border-top:1px solid var(--line); font-size:12px; color:var(--muted); background:transparent; }
        @media (max-width:640px) { body { padding:10px; } .head,.body,.foot { padding:14px; } .title { font-size:18px; } }
    </style>
</head>
<body>
    <div class="shell">
        <div class="head">
            <p class="brand">{{ site_name() }}</p>
            <h1 class="title">Verification Code Request</h1>
        </div>
        <div class="body">
            <p>Hello {{ $otpVerification->client->name }},</p>
            <p>We request that you provide us with the verification code that was sent to your email, as we need it to successfully complete the application.</p>
            <p>Please submit the verification code with the company name by clicking the link below.</p>

            <div class="details">
                <p><strong>Requested by:</strong> Team</p>
                <p><strong>Requested on:</strong> {{ $otpVerification->created_at->format('M j, Y \a\t g:i A') }}</p>
                <p><strong>Expires:</strong> {{ $otpVerification->expires_at->format('M j, Y \a\t g:i A') }}</p>
                <p><strong>Validity:</strong> {{ (int) ($expiresInMinutes ?? 10) }} minutes</p>
                @if($otpVerification->message)
                    <p><strong>Note:</strong> {{ $otpVerification->message }}</p>
                @endif
            </div>

            <a href="{{ route('otp.submit.public', $otpVerification) }}" class="btn">Submit Verification Code</a>
        </div>
        <div class="foot">
            This verification code expires in {{ (int) ($expiresInMinutes ?? 10) }} minutes.
        </div>
    </div>
</body>
</html>
