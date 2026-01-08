<!DOCTYPE html>
<html>
<head>
    <title>Website Down Alert</title>
</head>
<body>
    <h2>Website Monitoring Alert</h2>
    <p>The website <strong>{{ $website->url }}</strong> is currently down.</p>
    
    <div style="background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #dc3545;">
        <p><strong>Details:</strong></p>
        <ul>
            <li>URL: {{ $website->url }}</li>
            <li>Last Checked: {{ $website->last_checked_at->format('Y-m-d H:i:s') }}</li>
            @if($website->last_error)
                <li>Error: {{ $website->last_error }}</li>
            @endif
        </ul>
    </div>
    
    <p>Our system will continue to monitor this website and notify you when it's back up.</p>
    
    <hr>
    <footer>
        <p style="color: #6c757d; font-size: 0.9em;">
            This is an automated message from {{ config('app.name') }}.<br>
            Please do not reply to this email.
        </p>
    </footer>
</body>
</html>