<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Submission</title>
    <style>
        :root { --bg:#f5f5f5; --card:#fff; --text:#111; --muted:#5f5f5f; --accent:#b88a44; --line:#e8e8e8; }
        * { box-sizing: border-box; }
        body { margin:0; padding:18px; background:transparent; color:var(--text); font-family:Poppins, "Segoe UI", Arial, sans-serif; }
        .shell { max-width:680px; margin:0 auto; background:transparent; border:1px solid var(--line); border-radius:14px; overflow:hidden; }
        .head { padding:18px 22px; border-bottom:1px solid var(--line); }
        .brand { margin:0; font-size:11px; text-transform:uppercase; letter-spacing:.08em; color:var(--accent); font-weight:600; }
        .title { margin:8px 0 0; font-size:20px; line-height:1.3; }
        .body { padding:22px; }
        .grid { width:100%; border-collapse:collapse; font-size:14px; }
        .grid td { padding:8px 0; border-bottom:1px solid #f0f0f0; vertical-align:top; }
        .grid td.label { width:130px; color:var(--muted); font-weight:500; }
        .message { margin-top:16px; border:1px solid var(--line); border-radius:10px; padding:14px; background:#fcfcfc; }
        .message p { margin:0; white-space:pre-wrap; line-height:1.7; }
        .sub { margin:6px 0 0; color:var(--muted); font-size:13px; }
        .foot { padding:14px 22px; border-top:1px solid var(--line); font-size:12px; color:var(--muted); background:transparent; }
        @media (max-width:640px) { body { padding:10px; } .head,.body,.foot { padding:14px; } .title { font-size:18px; } .grid td.label { width:110px; } }
    </style>
</head>
<body>
    <div class="shell">
        @include('emails.partials.header', [
            'title' => 'New Contact Form Submission',
            'subtitle' => $payload['subject'],
        ])
        <div class="body">
            <table class="grid">
                <tr><td class="label">Name</td><td>{{ $payload['name'] }}</td></tr>
                <tr><td class="label">Email</td><td>{{ $payload['email'] }}</td></tr>
                <tr><td class="label">Phone</td><td>{{ $payload['phone'] ?? 'N/A' }}</td></tr>
                <tr><td class="label">Subject</td><td>{{ $payload['subject'] }}</td></tr>
                <tr><td class="label">Submitted</td><td>{{ $submittedAt->format('M d, Y h:i A') }}</td></tr>
            </table>

            <div class="message">
                <p>{{ $payload['message'] }}</p>
            </div>
        </div>
        <div class="foot">
            Contact message received from the public website form.
        </div>
    </div>
</body>
</html>
