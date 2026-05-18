<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Request</title>
    <style>
        :root { --bg:#f5f5f5; --card:#fff; --text:#111; --muted:#5f5f5f; --accent:#b88a44; --line:#e8e8e8; }
        * { box-sizing: border-box; }
        body { margin:0; padding:18px; background:transparent; color:var(--text); font-family:Poppins, "Segoe UI", Arial, sans-serif; }
        .shell { max-width:680px; margin:0 auto; background:transparent; border:1px solid var(--line); border-radius:14px; overflow:hidden; }
        .head { padding:18px 22px; border-bottom:1px solid var(--line); }
        .brand { margin:0; font-size:11px; text-transform:uppercase; letter-spacing:.08em; color:var(--accent); font-weight:600; }
        .title { margin:8px 0 0; font-size:20px; line-height:1.3; }
        .body { padding:22px; font-size:14px; line-height:1.7; }
        .summary { margin:16px 0; border:1px solid var(--line); border-radius:10px; overflow:hidden; }
        .row { display:flex; justify-content:space-between; gap:12px; padding:12px 14px; border-bottom:1px solid #f0f0f0; }
        .row:last-child { border-bottom:none; }
        .label { color:var(--muted); }
        .value { font-weight:600; text-align:right; }
        .amount { font-size:24px; font-weight:700; }
        .note { margin:14px 0 0; border:1px solid var(--line); border-radius:10px; padding:12px; background:#fcfcfc; white-space:pre-line; }
        .btn { display:inline-block; margin-top:18px; background:#111111; color:#ffffff !important; text-decoration:none; padding:11px 16px; border-radius:8px; font-weight:600; }
        .foot { padding:14px 22px; border-top:1px solid var(--line); font-size:12px; color:var(--muted); background:transparent; }
        @media (max-width:640px) { body { padding:10px; } .head,.body,.foot { padding:14px; } .title { font-size:18px; } .row { flex-direction:column; } .value { text-align:left; } }
    </style>
</head>
<body>
    <div class="shell">
        <div class="head">
            <p class="brand">{{ site_name() }}</p>
            <h1 class="title">Payment Request</h1>
        </div>
        <div class="body">
            <p>Hello {{ $clientName }},</p>
            <p>A payment request has been added to your account.</p>

            <div class="summary">
                <div class="row"><span class="label">Payment ID</span><span class="value">{{ $paymentRequest->display_reference }}</span></div>
                <div class="row"><span class="label">Status</span><span class="value">{{ $paymentRequest->getDisplayStatusLabel() }}</span></div>
                <div class="row"><span class="label">Amount Due</span><span class="value amount">${{ $amount }}</span></div>
            </div>

            @if($paymentRequest->note)
                <div class="note">{{ $paymentRequest->note }}</div>
            @endif

            @if(!empty($paymentLink))
                <a href="{{ $paymentLink }}" class="btn" style="margin-right:10px;">Pay Now</a>
            @endif
            <a href="{{ $loginUrl }}" class="btn">Open Dashboard</a>
        </div>
        <div class="foot">
            Review the request and mark it as paid after payment is completed.
        </div>
    </div>
</body>
</html>
