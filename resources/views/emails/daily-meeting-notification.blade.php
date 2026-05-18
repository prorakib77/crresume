<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daily Meeting</title>
    <style>
        :root { --bg:#f5f5f5; --card:#fff; --text:#111; --muted:#5f5f5f; --accent:#b88a44; --line:#e8e8e8; }
        * { box-sizing: border-box; }
        body { margin:0; padding:18px; background:transparent; color:var(--text); font-family:Poppins, "Segoe UI", Arial, sans-serif; }
        .shell { max-width:680px; margin:0 auto; background:transparent; border:1px solid var(--line); border-radius:14px; overflow:hidden; }
        .head { padding:18px 22px; border-bottom:1px solid var(--line); }
        .brand { margin:0; font-size:11px; text-transform:uppercase; letter-spacing:.08em; color:var(--accent); font-weight:600; }
        .title { margin:8px 0 0; font-size:20px; line-height:1.3; }
        .body { padding:22px; font-size:14px; line-height:1.7; }
        .grid { width:100%; border-collapse:collapse; margin:14px 0; }
        .grid td { padding:8px 0; border-bottom:1px solid #f0f0f0; vertical-align:top; }
        .grid td.label { width:110px; color:var(--muted); font-weight:500; }
        .btn { display:inline-block; margin-top:16px; background:#111111; color:#ffffff !important; text-decoration:none; padding:11px 16px; border-radius:8px; font-weight:600; }
        .foot { padding:14px 22px; border-top:1px solid var(--line); font-size:12px; color:var(--muted); background:transparent; }
        @media (max-width:640px) { body { padding:10px; } .head,.body,.foot { padding:14px; } .title { font-size:18px; } .grid td.label { width:95px; } }
    </style>
</head>
<body>
    <div class="shell">
        <div class="head">
            <p class="brand">{{ site_name() }}</p>
            <h1 class="title">Daily Agent Meeting</h1>
        </div>
        <div class="body">
            <p>Hello {{ $agent->name }},</p>
            <p>Your meeting schedule is ready.</p>

            <table class="grid">
                <tr><td class="label">Date</td><td>{{ $meeting->date->format('l, F j, Y') }}</td></tr>
                <tr><td class="label">Time</td><td>{{ $meeting->start_time->format('g:i A') }} - {{ $meeting->end_time->format('g:i A') }}</td></tr>
                <tr><td class="label">Duration</td><td>{{ $meeting->getDurationInHours() }} hours</td></tr>
                <tr><td class="label">Title</td><td>{{ $meeting->title }}</td></tr>
            </table>

            <a href="{{ $meeting->meet_link }}" class="btn">Join Meeting</a>
        </div>
        <div class="foot">
            Use the same meeting link during the scheduled session.
        </div>
    </div>
</body>
</html>
