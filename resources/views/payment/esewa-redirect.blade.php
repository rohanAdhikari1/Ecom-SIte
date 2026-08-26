<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Redirecting to eSewa…</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f4f1ea;
            font-family: system-ui, -apple-system, sans-serif;
            color: #262233;
        }
        .card {
            text-align: center;
            padding: 2.5rem;
        }
        .spinner {
            width: 44px;
            height: 44px;
            margin: 0 auto 1.25rem;
            border: 3px solid #4f46e522;
            border-top-color: #4f46e5;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        p { margin: 0.25rem 0; }
        .muted { color: #26223399; font-size: 0.875rem; }
    </style>
</head>
<body>
    <div class="card">
        <div class="spinner"></div>
        <p>Redirecting you to eSewa to complete your payment…</p>
        <p class="muted">Please do not close this window.</p>
    </div>

    <form id="esewa-form" action="{{ $formUrl }}" method="POST">
        @foreach ($payload as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
    </form>

    <script>document.getElementById('esewa-form').submit();</script>
</body>
</html>
