<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Successful</title>
    <style>
        :root { --bg:#f5f5f5; --card:#fff; --text:#111; --muted:#5f5f5f; --accent:#b88a44; --line:#e8e8e8; }
        * { box-sizing: border-box; }
        body { margin:0; padding:18px; background:transparent; color:var(--text); font-family:Poppins, "Segoe UI", Arial, sans-serif; }
        .shell { max-width:680px; margin:0 auto; background:transparent; border:1px solid var(--line); border-radius:14px; overflow:hidden; }
        .head { padding:18px 22px; border-bottom:1px solid var(--line); }
        .brand { margin:0; font-size:11px; text-transform:uppercase; letter-spacing:.08em; color:var(--accent); font-weight:600; }
        .title { margin:8px 0 0; font-size:20px; line-height:1.3; }
        .body { padding:22px; font-size:14px; line-height:1.7; }
        .meta { margin:14px 0; border:1px solid var(--line); border-radius:10px; padding:12px 14px; background:#fcfcfc; }
        .meta p { margin:0 0 6px; }
        .meta p:last-child { margin-bottom:0; }
        .btn { display:inline-block; margin-top:16px; background:#111111; color:#ffffff !important; text-decoration:none; padding:11px 16px; border-radius:8px; font-weight:600; }
        .foot { padding:14px 22px; border-top:1px solid var(--line); font-size:12px; color:var(--muted); background:transparent; }
        @media (max-width:640px) { body { padding:10px; } .head,.body,.foot { padding:14px; } .title { font-size:18px; } }
    </style>
</head>
<body>
    <div class="shell">
        <div class="head">
            <p class="brand">{{ site_name() }}</p>
            <h1 class="title">Password Reset Successful</h1>
        </div>
        <div class="body">
            <p>Hello {{ $user->name }},</p>
            <p>Your account password was changed successfully.</p>

            <div class="meta">
                <p><strong>Account:</strong> {{ $user->email }}</p>
                <p><strong>Updated:</strong> {{ now()->format('M j, Y \a\t g:i A') }}</p>
                <p><strong>IP:</strong> {{ request()->ip() ?? 'Unknown' }}</p>
            </div>

            <a href="{{ route('login') }}" class="btn">Sign In</a>
        </div>
        <div class="foot">
            If this was not you, contact support immediately.
        </div>
    </div>
</body>
</html>
